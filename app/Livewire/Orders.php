<?php

namespace App\Livewire;

use App\Helpers\QgisExporter;
use App\Models\Order;
use App\Models\OrderEmail;
use App\Models\OrderInternalDetail;
use App\Models\OrderPhoneNumber;
use App\Models\Project;

use Livewire\Attributes\Computed;
use Mediconesystems\LivewireDatatables\Column;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class Orders extends LivewireDatatable
{

  public $hideable = 'select';
  public $exportable = false;
  //public $complex = true;
  public $qgisActive = true;
  public $visibleSelected = [];

  public $buttonsSlot = 'order.component.list_buttons';


  public function builder(): Builder
  {
    return Order::query()
      ->join('batches', 'batches.id', '=', 'orders.batch_id')
      ->leftJoin('projects', 'projects.id', '=', 'batches.project_id')
      ->join('order_internal_details', 'orders.id', '=', 'order_internal_details.order_id')
      ->leftJoin(DB::raw('(SELECT order_id, MAX(id) AS latest_appointment FROM appointments WHERE status=1 GROUP BY order_id) AS latest_appointments'), function ($join) {
        $join->on('orders.id', '=', 'latest_appointments.order_id');
      })
      ->leftJoin('appointments', function ($join) {
        $join->on('latest_appointments.order_id', '=', 'appointments.order_id')
          ->on('latest_appointments.latest_appointment', '=', 'appointments.id');
      })
      ->leftJoin(DB::raw('(SELECT order_id, MAX(id) AS latest_p_number FROM order_phone_numbers WHERE is_valid=true GROUP BY order_id) AS latest_phone_numbers'), function ($join) {
        $join->on('orders.id', '=', 'latest_phone_numbers.order_id');
      })
      ->leftJoin('order_phone_numbers', function ($join) {
        $join->on('latest_phone_numbers.order_id', '=', 'order_phone_numbers.order_id')
          ->on('latest_phone_numbers.latest_p_number', '=', 'order_phone_numbers.id');
      })
      ->leftJoin(DB::raw('(SELECT order_id, MAX(id) AS latest_email FROM order_emails WHERE is_valid=true GROUP BY order_id) AS latest_emails'), function ($join) {
        $join->on('orders.id', '=', 'latest_emails.order_id');
      })
      ->leftJoin('order_emails', function ($join) {
        $join->on('latest_emails.order_id', '=', 'order_emails.order_id')
          ->on('latest_emails.latest_email', '=', 'order_emails.id');
      });
  }

  public function columns(): array|Model
  {
    return [
      Column::checkbox(),
      Column::name('projects.client_name')
        ->label(__('project.client_name'))
        ->filterOn('projects.client_name')
        ->hide()
        ->filterable($this->client),
      Column::name('projects.sub_client_name')
        ->label(__('project.sub_client_name'))
        ->filterOn('projects.sub_client_name')
        ->hide()
        ->filterable($this->subclient),
      Column::name('projects.name')
        ->label(__('order.project'))
        ->filterOn('projects.name')
        ->filterable($this->projectname),
      Column::callback('client_status', function ($c_status) {
        if ($c_status == 1 || $c_status == '2ssoe') {
          $image_url = asset('assets/img/connection_statuses/connection.png');
        } else if ($c_status == 3) {
          $image_url = asset('assets/img/connection_statuses/connection-orange.png');
        } else {
          $image_url = asset('assets/img/connection_statuses/no_connection.png');
        }
        //request()->getQueryString();
        return '<img style="max-width:30px;" src="' . $image_url . '" title="' . __('order.clientStatus-' . $c_status) . '"/>';

      })
        ->contentAlignCenter()
        ->label(__('order.client_status'))
        ->extraCollection(function ($collection) {
          return $collection->pluck('client_status')->mapWithKeys(function ($val) {
            return [$val => __('order.clientStatus-' . $val)];
          })->toArray();
        })
        ->filterable($this->clientstatus, 'filterClientStatus'),
      Column::callback('internal_status', function ($internal_status) {
        switch ($internal_status) {
          case 0:
            $image_url = asset('assets/img/connection_statuses/no_connection.png');
            break;
          case 1:
            $image_url = asset('assets/img/connection_statuses/open.png');
            break;
          case 2:
            $image_url = asset('assets/img/connection_statuses/appointment_done.png');
            break;
          case 3:
            $image_url = asset('assets/img/connection_statuses/incomplete_documentation.png');
            break;
          case 4:
            $image_url = asset('assets/img/connection_statuses/documentation_done.png');
            break;
          case 5:
            $image_url = asset('assets/img/connection_statuses/delayed.png');
            break;
          case 6:
            $image_url = asset('assets/img/connection_statuses/explain.png');
            break;
          default:
            $image_url = asset('assets/img/connection_statuses/no_connection.png');
            break;
        }
        return '<img style="max-width:30px;" src="' . $image_url . '" title="' . Order::getInternalStatus($internal_status) . '"/>';
      })
        ->label(__('order.internal_status'))
        ->contentAlignCenter()
        ->filterable($this->internalstatus(), 'InternalFilters')
        ->extraCollection(function ($collection) {
          return $collection->pluck('internal_status')->mapWithKeys(function ($val) {
            return [$val => Order::getInternalStatus($val)];
          })->toArray();
        })
        ->exportCallback(function ($value) {
          return Order::getInternalStatus($value);
        }),
      Column::name('org_order_id')
        ->label(__('order.org_order_id'))
        ->defaultSort('asc')
        ->filterable(),
      Column::name('zipcode')
        ->label(__('order.zipcode'))
        ->filterable($this->zipcode),
      Column::name('city')
        ->label(__('order.city'))
        ->filterable($this->city),
      Column::name('district')
        ->label(__('order.district'))
        ->filterable($this->district),
      Column::name('street')
        ->label(__('order.street'))
        ->filterable($this->street),
      Column::name('housenumber')
        ->label(__('order.housenumber'))
        ->filterable($this->hnumber)
        ->sortBy(DB::raw('housenumber::numeric')),
      Column::name('housenumber_suffix')
        ->label(__('order.housenumberSuffix'))
        ->filterable($this->housesuffix),
      Column::name('orders.name')
        ->label(__('order.name'))
        ->filterable($this->cname),
      Column::name('order_phone_numbers.phone')
        ->label(__('order.phone'))
        ->filterable($this->phone),
      Column::name('order_emails.email')
        ->label(__('order.email'))
        ->filterable($this->email),
      Column::callback('order_internal_details.empty_pdf', function ($empty_pdf) {
        return $empty_pdf ? '<span class="badge bg-success">' . __('Yes') . '</span>' : '<span class="badge bg-danger">' . __('No') . '</span>';
      })
        ->label(__('order.emptyPdf'))
        ->filterOn('order_internal_details.empty_pdf')
        ->hide()
        ->extraCollection(function ($collection) {
          return $collection->pluck('order_internal_details.empty_pdf')->map(function ($val) {
            return $val ? __('Yes') : __('No');
          })->unique()->toArray();
        })
        ->filterable($this->emptypdf, 'filterEmptyPdf'),
      Column::callback('order_internal_details.final_pdf', function ($final_pdf) {
        return $final_pdf ? '<span class="badge bg-success">' . __('Yes') . '</span>' : '<span class="badge bg-danger">' . __('No') . '</span>';
      })
        ->label(__('order.finalPdf'))
        ->filterOn('order_internal_details.final_pdf')
        ->extraCollection(function ($collection) {
          return $collection->pluck('order_internal_details.final_pdf')->map(function ($val) {
            return $val ? __('Yes') : __('No');
          })->unique()->toArray();
        })
        ->hide()
        ->filterable($this->finalpdf, 'filterFinalPdf'),
      DateColumn::name('appointments.from')
        ->label(__('appointment.appointmentAt'))
        ->format('d.m.Y H:i')
        ->filterable(),
      DateColumn::name('order_internal_details.completed_at')
        ->format('d.m.Y')
        ->label(__('order.completedAt'))
        ->filterable(),
      Column::callback('orders.electricity_status', function ($eStatus) {
        return !$eStatus ? '<span class="badge bg-danger">' . __('order.internalStatusNoConnection') . '</span>' : ($eStatus == 1 ? '<span class="badge bg-success">' . __('order.electricityNewConnection') . '</span>' : '<span class="badge bg-warning">' . __('order.electricityOldConnection') . '</span>');
      })
        ->extraCollection(function ($collection) {
          return $collection->pluck('orders.electricity_status')->mapWithKeys(function ($val) {
            return [$val => Order::getElectricityStatus($val)];
          })->toArray();
        })
        ->label(__('order.electricityStatus'))
        ->filterable($this->electricitystatus, 'electricityFilter'),
      Column::callback('orders.monument_protection', function ($protected) {
        return !$protected ? '<span class="badge bg-danger">' . __('No') . '</span>' : '<span class="badge bg-success">' . __('Yes') . '</span>';
      })
        ->extraCollection(function ($collection) {
          return $collection->pluck('orders.monument_protection')->map(function ($val) {
            return $val ? __('Yes') : __('No');
          })->unique()->toArray();
        })
        ->label(__('order.monument_protection'))
        ->filterable($this->monument, 'filterMonument'),

      Column::name('id')
        ->label(__('common.id'))
        ->hide()
        ->filterable(),
    ];
  }

  /**
   * Order the table by a given column index starting from 0.
   *
   * @param  int  $index  which column to sort by
   * @param  string|null  $direction  needs to be 'asc' or 'desc'. set to null to toggle the current direction.
   * @return void
   */
  public function sorter($index, $direction = null)
  {
    if (!in_array($direction, [null, 'asc', 'desc'])) {
      throw new \Exception("Invalid direction $direction given in sort() method. Allowed values: asc, desc.");
    }

    if ($this->sort === (int) $index) {
      if ($direction === null) { // toggle direction
        $this->direction = !$this->direction;
      } else {
        $this->direction = $direction === 'asc' ? true : false;
      }
    } else {
      $this->sort = (int) $index;
    }
    if ($direction !== null) {
      $this->direction = $direction === 'asc' ? true : false;
    }
    $this->setPage(1);

    session()->put([
      $this->sessionStorageKey() . '_sort' => $this->sort,
      $this->sessionStorageKey() . '_direction' => $this->direction,
    ]);
  }

  public function massActionOptionHandler(): BinaryFileResponse|bool
  {
    if ($this->massActionOption == 'empty_pdf' || $this->massActionOption == 'final_pdf') {
      return $this->handlePdfDownloads();
    }
    return true;
  }
  public function qgisExport()
  {
    $exporter = new QgisExporter($this->selected);
    $exporter->export();
  }



  public function buildActions(): array
  {
    return [
      ['group' => 'pdf', 'value' => 'empty_pdf', 'label' => __('order.downloadInitialPdf')],
      ['group' => 'pdf', 'value' => 'final_pdf', 'label' => __('order.downloadFinalPdf')],
    ];
  }
  public function getMassActionsProperty(): array
  {
    $actions = collect($this->buildActions());

    $duplicates = $actions->pluck('value')->duplicates();
    //dd($duplicates);
    if ($duplicates->count()) {
      throw new RuntimeException('Duplicate Mass Action(s): ' . implode(', ', $duplicates->toArray()));
    }

    return $actions->toArray();
  }

  #[Computed]
  public function client()
  {
    return $this->builder()->groupBy('projects.client_name')->orderBy('projects.client_name')->pluck('projects.client_name');
  }

  #[Computed]
  public function subclient()
  {
    return $this->builder()->groupBy('projects.sub_client_name')->orderBy('projects.sub_client_name')->pluck('projects.sub_client_name');
  }

  #[Computed]
  public function projectname()
  {
    return $this->builder()->groupBy('projects.name')->orderBy('projects.name')->pluck('projects.name')->toArray();
  }

  #[Computed]
  public function zipcode()
  {
    return $this->builder()->groupBy('zipcode')->orderBy('zipcode')->pluck('zipcode');
  }
  #[Computed]
  public function city()
  {
    return $this->builder()->groupBy('city')->orderBy('city')->pluck('city');
  }
  #[Computed]
  public function district()
  {
    return $this->builder()->groupBy('district')->orderBy('district')->pluck('district');
  }

  #[Computed]
  public function street()
  {
    return $this->builder()->groupBy('street')->orderBy('street')->pluck('street');
  }
  #[Computed]
  public function hnumber()
  {
    return $this->builder()->groupBy('housenumber')->orderBy('housenumber')->pluck('housenumber');
  }
  #[Computed]
  public function housesuffix()
  {
    return $this->builder()->groupBy('housenumber_suffix')->orderBy('housenumber_suffix')->pluck('housenumber_suffix');
  }
  #[Computed]
  public function cname()
  {
    return $this->builder()->groupBy('orders.name')->orderBy('orders.name')->pluck('orders.name');
  }
  #[Computed]
  public function email()
  {
    return OrderEmail::select(DB::raw('email, MAX(id) AS latest_email'))
      ->groupBy('email')
      ->where('is_valid', true)
      ->orderBy('email')
      ->pluck('email')
      ->unique()->toArray();
  }
  #[Computed]
  public function phone()
  {
    return OrderPhoneNumber::select(DB::raw('phone, MAX(id) AS latest_phone'))
      ->groupBy('phone')
      ->where('is_valid', true)
      ->orderBy('phone')
      ->pluck('phone')
      ->unique()->toArray();
  }
  #[Computed]
  public function clientstatus()
  {
    return $this->builder()->groupBy('client_status')->orderBy('client_status')->pluck('client_status')->map(function ($val) {
      return [$val => __('order.clientStatus-' . $val)];
    })->flatten()->toArray();
  }
  #[Computed]
  public function internalstatus()
  {
    return $this->builder()->groupBy('internal_status')->orderBy('internal_status')->pluck('internal_status')->map(function ($val) {
      return [$val => Order::getInternalStatus($val)];
    })->flatten();
  }
  #[Computed]
  public function electricitystatus()
  {
    return $this->builder()->groupBy('electricity_status')->orderBy('electricity_status')->pluck('electricity_status')->map(function ($val) {
      return [$val => Order::getElectricityStatus($val)];
    })->flatten()->toArray();
  }
  #[computed]
  public function finalpdf()
  {
    $finalPdf = [];
    if ($this->builder()->whereNull('order_internal_details.final_pdf')->exists()) {
      $finalPdf[] = __('NO');
    }
    if ($this->builder()->whereNotNull('order_internal_details.final_pdf')->exists()) {
      $finalPdf[] = __('Yes');
    }
    return $finalPdf;
  }

  #[computed]
  public function emptypdf()
  {
    $emptyPdf = [];
    if ($this->builder()->whereNull('order_internal_details.empty_pdf')->exists()) {
      $emptyPdf[] = __('NO');
    }
    if ($this->builder()->whereNotNull('order_internal_details.empty_pdf')->exists()) {
      $emptyPdf[] = __('Yes');
    }
    return $emptyPdf;
  }

  #[computed]
  public function monument()
  {

    return $this->builder()->groupBy('orders.monument_protection')->pluck('orders.monument_protection')->map(function ($val) {
      return $val ? __('YES') : __('NO');
    })->toArray();
  }
  public function handlePdfDownloads(): BinaryFileResponse|bool
  {
    $pdfs = OrderInternalDetail::whereIn('order_id', $this->selected)
      ->whereNotNull($this->massActionOption)
      ->pluck($this->massActionOption);
    if (!$pdfs->count()) {
      return false;
    }

    if ($this->massActionOption == 'final_pdf') {
      $pdfs = $pdfs->filter(fn($pdf) => $pdf)->map(function ($pdf) {
        $pdf = json_decode($pdf);
        return $pdf[count($pdf) - 1]->file;
      });
    }
    $zipPath = tempnam(sys_get_temp_dir(), 'download_files_') . '.zip';

    $zip = new ZipArchive;

    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
      // Add each file to the zip archive
      foreach ($pdfs as $pdf) {
        // Ensure the file exists before adding it to the zip archive
        if (file_exists($pdf)) {

          // Add the file to the zip archive with a name based on the original filename
          $zip->addFile($pdf, basename($pdf));
        }
      }
      // Close the zip archive
      if ($zip->close()) {
        return response()->download($zipPath, $this->massActionOption . '.zip')->deleteFileAfterSend();
      }

    }

    return false;
  }
}
