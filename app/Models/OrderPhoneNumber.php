<?php

namespace App\Models;

use App\Helpers\Helpers;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderPhoneNumber extends Model
{
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'order_id',
    'phone',
    'is_valid',
    'source',
    'unvalid_by'
  ];

  /**
   * Get the order that owns the OrderPhoneNumber
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function order(): BelongsTo
  {
    return $this->belongsTo(Order::class);
  }


  public function Phone(): Attribute
  {
    return Attribute::set(
      function ($val) {
        $num = Helpers::checkPhoneForCountryCode($val);

        throw_if($num == null);
        return $num;

      }


    );
  }

  /**
   * Get a new query builder for the model's table.
   *
   * @param  bool  $excludeDeleted
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public function newQuery($excludeDeleted = true)
  {
    $builder = parent::newQuery($excludeDeleted);

    // Override where method to automatically apply mutators
    $builder->macro('whereMutated', function ($column, $operator = null, $value = null, $boolean = 'and') use ($builder) {
      // If the column has a mutator, apply it to the value
      if (method_exists($this, 'getAttribute') && method_exists($this, 'setAttribute')) {
        $value = $this->getAttribute($column);
      }

      return $builder->where($column, $operator, $value, $boolean);
    });

    return $builder;
  }

}
