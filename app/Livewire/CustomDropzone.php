<?php

namespace App\Livewire;

use App\Models\Project;
use App\Rules\CompletedOrdersExcel;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class CustomDropzone extends Component
{
  use WithFileUploads;

  #[Modelable]
  public array $files;

  #[Locked]
  public array $rules;

  #[Locked]
  public string $uuid;

  public $upload;

  public string $error;

  public bool $multiple;

  public bool $isExcel;

  public $project;

  public function rules(): array
  {
    $field = $this->multiple ? 'upload.*' : 'upload';
    //return null;
    return [
      $field => [...$this->rules],
    ];
  }

  public function mount(array $rules = [], bool $multiple = false, $excel = false, $project = ''): void
  {
    $this->uuid = Str::uuid();
    $this->multiple = $multiple;
    $this->rules = $rules;
    $this->isExcel = $excel;
    if ($project) {
      $this->project = Project::find($project);
    }
  }

  public function updatedUpload(): void
  {
    $this->reset('error');

    if (!$this->customValidate()) {
      return;
    }
    $this->upload = $this->multiple
      ? $this->upload
      : [$this->upload];

    foreach ($this->upload as $upload) {

      $this->handleUpload($upload);
    }

    $this->reset('upload');
  }




  /**
   * Handle the uploaded file and dispatch an event with file details.
   */
  public function handleUpload(TemporaryUploadedFile $file): void
  {
    $this->dispatch("{$this->uuid}:fileAdded", [
      'tmpFilename' => $file->getFilename(),
      'name' => $file->getClientOriginalName(),
      'extension' => $file->extension(),
      'path' => $file->path(),
      'temporaryUrl' => $file->isPreviewable() ? $file->temporaryUrl() : null,
      'size' => $file->getSize(),
    ]);
  }

  /**
   * Handle the file added event.
   */
  #[On('{uuid}:fileAdded')]
  public function onFileAdded(array $file): void
  {
    $this->files[] = $file;
  }

  /**
   * Handle the file removal event.
   */
  #[On('{uuid}:fileRemoved')]
  public function onFileRemoved(string $tmpFilename): void
  {
    $this->files = array_filter($this->files, function ($file) use ($tmpFilename) {
      $isNotTmpFilename = $file['tmpFilename'] !== $tmpFilename;

      if (!$isNotTmpFilename) {
        unlink($file['path']);
      }

      return $isNotTmpFilename;
    });
  }

  /**
   * Handle the upload error event.
   */
  #[On('{uuid}:uploadError')]
  public function onUploadError(string $error): void
  {
    $this->error = $error;
  }

  /**
   * Retrieve the MIME types from the rules.
   */
  #[Computed]
  public function mimes(): string
  {
    return collect($this->rules)
      ->filter(fn($rule) => is_string($rule) ? str_starts_with($rule, 'mimes:') : false)
      ->flatMap(fn($rule) => explode(',', substr($rule, strpos($rule, ':') + 1)))
      ->unique()
      ->values()
      ->join(', ');
  }

  /**
   * Get the accepted file extensions based on MIME types.
   */
  #[Computed]
  public function accept(): ?string
  {
    return !empty($this->mimes) ? collect(explode(', ', $this->mimes))->map(fn($mime) => '.' . $mime)->implode(',') : null;
  }

  /**
   * Get the maximum file size in a human-readable format.
   */
  #[Computed]
  public function maxFileSize(): ?string
  {
    return collect($this->rules)
      ->filter(fn($rule) => is_string($rule) ? str_starts_with($rule, 'max:') : false)
      ->flatMap(fn($rule) => explode(',', substr($rule, strpos($rule, ':') + 1)))
      ->unique()
      ->values()
      ->first();
  }
  /**
   * Get the minimum file size in a human-readable format.
   */
  #[Computed]
  public function minFileSize(): ?string
  {
    return collect($this->rules)
      ->filter(fn($rule) => is_string($rule) ? str_starts_with($rule, 'min:') : false)
      ->flatMap(fn($rule) => explode(',', substr($rule, strpos($rule, ':') + 1)))
      ->unique()
      ->values()
      ->first();
  }

  /**
   * Checks if the provided MIME type corresponds to an image.
   */
  public function isImageMime($mime): bool
  {
    return in_array($mime, ['png', 'gif', 'bmp', 'svg', 'jpeg', 'jpg']);
  }
  public function render(): View
  {
    return view('livewire.custom-dropzone');
  }

  public function customValidate()
  {
    try {
      //$this->validate();
      if ($this->isExcel) {

        //check if file exists or not
        if (file_exists(storage_path('app/uploads/' . $this->upload[0]->getClientOriginalName()))) {
          $this->dispatch("{$this->uuid}:uploadError", __('order.uploadExcelFileAlreadyExistsError'));
          return false;
        }
        $file_name = str_replace('.' . $this->upload[0]->extension(), '', $this->upload[0]->getClientOriginalName());
        $pattern = '/^\d{2}\.\d{2}\.\d{4},\s.*$/';
        if (preg_match($pattern, $file_name) !== 1) {
          $this->dispatch("{$this->uuid}:uploadError", __('order.uploadExcelFileNameIssue'));
          return false;
        }
        $pr_name = explode(', ', $file_name);
        if ($this->project->name != trim($pr_name[1])) {
          $this->dispatch("{$this->uuid}:uploadError", __('order.uploadExcelFilProjectNameNOtMatchedWithSelected'));
          return false;
        }

      }
    } catch (ValidationException $e) {
      // If the upload validation fails, we trigger the following event
      $this->dispatch("{$this->uuid}:uploadError", $e->getMessage());

      return false;
    }

    return true;
  }
}
