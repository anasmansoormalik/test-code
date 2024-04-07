<?php

namespace App\Livewire;

use App\Imports\CompletedOrders;
use App\Models\Project;
use App\Services\HandleOrderPdf;
use Illuminate\Http\File;
use Illuminate\Support\Facades\File as FacadesFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use ZipArchive;

class OrderUploads extends Component
{

  #[Validate('required')]
  public array $uploadFiles = [];
  #[Validate('required')]
  public $uproject = '';
  #[Validate('required')]
  public $utype = '';

  public $c_rules = [];

  public function render()
  {

    return view('livewire.order-uploads');
  }

  public function updatingUtype()
  {
    $this->c_rules = [];
  }

  public function updatedUtype()
  {

    if (!$this->uproject || $this->utype == '') {
      $this->utype = '';
      return;
    }
    if ($this->utype == 'excel') {
      $this->c_rules = ['required', 'mimes:xlsx,xls'];
      return;
    }
    if ($this->uproject) {
      $proj = Project::find($this->uproject);
      if ($this->utype == 'empty_pdf') {
        $this->c_rules = ['mimes:pdf,zip', 'max:' . $proj->empty_pdf_size];
      } else {
        $this->c_rules = ['mimes:pdf,zip', 'min:' . $proj->final_pdf_size];
      }
      return;
    } else {
      $this->c_rules = [];
    }
  }

  public function submit(): void
  {
    $this->validate();
    $pathsArray = [];
    $pdfResponses = [];
    $excelIgnores = [];
    $excelDones = [];
    try {
      foreach ($this->uploadFiles as $ufile) {
        $uploaded_path = $this->saveFile($ufile);

        if ($uploaded_path == false) {
          $pdfResponses[] = ['status' => false, 'code' => 'ORDER_FINAL_PDF_LIMIT', 'file' => $ufile['name']];
          continue;
        }
        if (Str::endsWith($ufile['name'], ['.xlsx', '.xls'])) {
          $import = new CompletedOrders();
          Excel::import($import, storage_path('app/' . $uploaded_path));
          $excelIgnores[] = $import->getIgnores();
          $excelDones[] = $import->getDones();
        } elseif (Str::endsWith($ufile['name'], '.pdf')) {
          $pdf = new HandleOrderPdf($ufile['name'], storage_path('app/' . $uploaded_path), $this->utype);
          $pdfResponses[] = $pdf->process();
        } elseif (Str::endsWith($ufile['name'], '.zip')) {
          $zip = new ZipArchive;
          $zip->open(storage_path('app/' . $uploaded_path));
          for ($i = 0; $i < $zip->numFiles; $i++) {
            $fileName = $zip->getNameIndex($i);
            $fileContents = $zip->getFromIndex($i);
            $storedFilePath = storage_path('app/uploads/');
            if ($this->utype == 'final_pdf') {
              $fileName = $this->saveFileWithIncrementedVersion($fileName);
            }
            file_put_contents($storedFilePath . $fileName, $fileContents);
            //$uploaded_path = Storage::putFileAs($storedFilePath, $fileContents, $fileName);
            $pdf = new HandleOrderPdf($fileName, $storedFilePath . $fileName, $this->utype);
            $pdfResponses[] = $pdf->process();
          }
          $zip->close();
        }
        $pathsArray[] = $ufile['path'];
      }
      FacadesFile::delete($pathsArray);
      $this->uploadFiles = [];
      if (count($pdfResponses) > 0) {
        $counts = collect($pdfResponses)->countBy(function ($item) {
          return $item['code'];
        });
        $orderNotFound = "<ul class='alert-list'>" . collect($pdfResponses)->filter(fn($item) => $item['code'] == 'ORDER_NOT_FOUND')->pluck('file')->map(fn($item) => "<li>$item</li>")->implode('') . "</ul>";
        $docLimitOrders = "<ul class='alert-list'>" . collect($pdfResponses)->filter(fn($item) => $item['code'] == 'ORDER_FINAL_PDF_LIMIT')->pluck('file')->map(fn($item) => "<li>$item</li>")->implode('') . "</ul>";
        $orderSuccess = "<ul class='alert-list'>" . collect($pdfResponses)->filter(fn($item) => $item['code'] == 'ORDER_UPDATE_SUCCESS')->pluck('file')->map(fn($item) => "<li>$item</li>")->implode('') . "</ul>";
        $this->dispatch('file-uploaded', ['success' => __('order.filesPdfUploaded', ['OK' => $orderSuccess, 'nf' => $orderNotFound, 'lo' => $docLimitOrders, 'OKC' => $counts['ORDER_UPDATE_SUCCESS'] ?? 0, 'nfc' => $counts['ORDER_NOT_FOUND'] ?? 0, 'loc' => $counts['ORDER_FINAL_PDF_LIMIT'] ?? 0])]);
        $this->resetc();
        return;
      }
      if (count($excelIgnores) > 0) {
        $excelIgnores_count = count($excelIgnores[0]);
        $excelDones_count = count($excelDones[0]);

        $excelIgnores = "<ul class='alert-list'>" . collect($excelIgnores)->flatten()->map(fn($item) => "<li>$item</li>")->implode('') . "</ul>";
        $excelDones = "<ul class='alert-list'>" . collect($excelDones)->flatten()->map(fn($item) => "<li>$item</li>")->implode('') . "</ul>";
        $this->dispatch('file-uploaded', ['success' => __('order.filesExcelUploaded', ['OK' => $excelDones, 'nf' => $excelIgnores, 'OKC' => $excelDones_count, 'nfc' => $excelIgnores_count])]);
        $this->resetc();
        return;
      }
      $this->dispatch('file-uploaded', ['success' => __('order.filesUploaded')]);
      $this->resetc();
    } catch (\Throwable $th) {
      FacadesFile::delete(storage_path('app/' . $uploaded_path));
      $this->resetc();
      $this->dispatch('file-upload-error', ['success' => $th->getMessage()]);
    }

  }

  public function resetc()
  {
    foreach ($this->uploadFiles as $ufile) {
      FacadesFile::delete($ufile['path']);
    }
    $this->uploadFiles = [];
    $this->uproject = '';
    $this->utype = '';
    $this->c_rules = [];
  }

  public function saveFile($ufile)
  {
    //check if any existing
    if ($this->utype == 'final_pdf') {

      $fileName = $this->saveFileWithIncrementedVersion($ufile['name']);

    } else {
      $fileName = $ufile['name'];
    }
    return Storage::putFileAs('uploads', new File($ufile['path']), $fileName);
  }

  public function saveFileWithIncrementedVersion($originalFileName)
  {
    $path = storage_path('app/uploads/'); // You can change this to the appropriate directory
    $baseName = pathinfo($originalFileName, PATHINFO_FILENAME);
    $extension = pathinfo($originalFileName, PATHINFO_EXTENSION);
    $maxAttempts = 3; // Set a reasonable maximum number of attempts

    for ($version = 1; $version <= $maxAttempts; $version++) {
      $newFileName = $baseName . '_v' . $version . '.' . $extension;

      // Check if a file with the new name already exists
      if (!file_exists($path . DIRECTORY_SEPARATOR . $newFileName)) {

        // Return the new file name with the version
        return $newFileName;
      }
    }

    // If it reaches here, it means the maximum number of attempts was reached
    return false;
  }
}
