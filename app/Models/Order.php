<?php

namespace App\Models;

use App\Helpers\Helpers;
use Clickbar\Magellan\Database\Eloquent\HasPostgisColumns;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\PhoneCall\App\Models\PhoneCall;
use OwenIt\Auditing\Contracts\Auditable;
use Wildside\Userstamps\Userstamps;

class Order extends Model implements Auditable
{
  use HasFactory, HasPostgisColumns, \OwenIt\Auditing\Auditable, Userstamps, SoftDeletes, CascadeSoftDeletes;

  //protected $cascadeDeletes = ['appointment', 'internalDetail'];
  protected $fillable = [
    'batch_id',
    'name',
    'org_order_id',
    'latitude',
    'longitude',
    'city',
    'zipcode',
    'district',
    'street',
    'housenumber',
    'location',
    'housenumber_suffix',
    'is_private_customer',
    'is_commercial_customer',
    'order_data',
    'created_by',
    'updated_by',
    'deleted_by',
    'client_status',
    'internal_status',
    'electricity_status',
    'monument_protection'
  ];

  protected $casts = [
    'order_data' => 'array',
  ];
  protected array $postgisColumns = [
    'location' => [
      'type' => 'geometry',
      'srid' => 4258,
    ],
  ];

  protected static function boot()
  {
    parent::boot();

    // Listen for the 'updated' event
    static::updated(function ($order) {
      // Update the corresponding allowed_users records
      self::updateDataInQgisLayer($order);
    });
  }

  public static function updateDataInQgisLayer($order)
  {
    //get all the users
    User::where('status', true)->pluck('name')->each(function ($n) use ($order) {
      $tableName = str($n)->snake() . '_' . 'qgis';
      //dd(Schema::hasTable($tableName), $tableName, $n, $order);
      if (Schema::hasTable($tableName)) {

        DB::table($tableName)->where('order_id', $order->id)->update(['internal_status' => $order->internal_status]);
      }
    });
  }

  public static function getInternalStatus($status = false)
  {
    $statuses = [
      0 => __('order.internalStatusNoConnection'),
      1 => __('order.internalStatusOpen'),
      2 => __('order.internalStatusAppointmentDone'),
      3 => __('order.internalStatusIncompleteDocs'),
      4 => __('order.internalStatusDocsDone'),
      5 => __('order.internalStatusDelayed'),
      6 => __('order.internalStatusExceptionalCase')
    ];
    if ($status !== false) {
      return $statuses[$status] ?? '';
    }
    return $statuses;

  }

  public static function getElectricityStatus($status = false)
  {
    $statuses = [
      0 => __('order.internalStatusNoConnection'),
      1 => __('order.electricityNewConnection'),
      2 => __('order.electricityOldConnection'),
    ];
    if ($status !== false) {
      return $statuses[$status] ?? '';
    }
    return $statuses;
  }

  public function getClientStatus($status = false)
  {
    $statuses = Order::groupBy('client_status')->orderBy('client_status')->pluck('client_status', 'client_status')->mapWithKeys(function ($val) {
      return [$val => __('order.clientStatus-' . $val)];
    })->toArray();
    if ($status !== false) {
      return $statuses[$status] ?? '';
    }
    return $statuses;
  }


  /**
   * Get all of the delays for the Order
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function delays(): HasMany
  {
    return $this->hasMany(DelayedOrder::class);
  }

  /**
   * Get all of the phone for the Order
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function phone(): HasMany
  {
    return $this->hasMany(OrderPhoneNumber::class);
  }

  /**
   * Get all of the phone for the Order
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function email(): HasMany
  {
    return $this->hasMany(OrderEmail::class);
  }

  /**
   * The phoneCalls that belong to the Order
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
   */
  public function phoneCalls(): BelongsToMany
  {
    return $this->belongsToMany(PhoneCall::class, 'order_phones');
  }

  /**
   * Get all of the communications for the Order
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function communications(): HasMany
  {
    return $this->hasMany(Communication::class);
  }

  public function generateTags($batch = null): array
  {

    $tags = [];
    if ($this->isDirty('client_status')) {
      $tags[] = $this->client_status . '-order-client-status';
    }
    return $tags;
  }

  public function address()
  {
    return $this->street . ' ' . $this->housenumber . ($this->housenumber_suffix ? ' ' . $this->housenumber_suffix : '') . ', ' . $this->district . ', ' . $this->zipcode . ' ' . $this->city;
  }

  public function batch()
  {
    return $this->belongsTo(Batch::class);
  }

  public function customer()
  {
    return $this->belongsTo(Customer::class);
  }

  public function appointment()
  {
    return $this->hasMany(Appointment::class);
  }

  public function internalDetail()
  {
    return $this->hasOne(OrderInternalDetail::class);
  }

  public function getLatestAppointment()
  {

    return $this->appointment()->where('status', '!=', 3)->latest()->first();
  }


  public function createdBy()
  {
    return $this->belongsTo(User::class, 'created_by');
  }

  public function updatedBy()
  {
    return $this->belongsTo(User::class, 'updated_by');
  }

  public function deletedBy()
  {
    return $this->belongsTo(User::class, 'deleted_by');
  }

  //filter scopes
  public function scopeFilterEmptyPdf($query, $value)
  {
    if ($value == __('Yes')) {
      $query->whereNotNull('order_internal_details.empty_pdf');
    } else if ($value == __('No')) {
      $query->whereNull('order_internal_details.empty_pdf');
    }
  }
  public function scopeFilterFinalPdf($query, $value)
  {
    if ($value == __('Yes')) {
      $query->whereNotNull('order_internal_details.final_pdf');
    } else if ($value == __('No')) {
      $query->whereNull('order_internal_details.final_pdf');
    }
  }

  public function scopeInternalFilters($query, $value)
  {
    $internal_status = array_search($value, self::getInternalStatus());
    $query->where('internal_status', $internal_status);
  }

  public function scopeElectricityFilter($query, $value)
  {
    $internal_status = array_search($value, self::getElectricityStatus());
    $query->where('electricity_status', $internal_status);
  }

  public function scopefilterClientStatus($query, $value)
  {

    $client_status = array_search($value, self::getClientStatus());
    $query->where('client_status', $client_status);
  }

  public function scopefilterMonument($query, $value)
  {

    $query->where('orders.monument_protection', '=', $value == __('Yes'));

  }
}
