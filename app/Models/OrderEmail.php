<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderEmail extends Model
{
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'order_id',
    'email',
    'is_valid',
    'source',
    'unvalid_by'
  ];

  /**
   * Get the order that owns the OrderEmail
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function order(): BelongsTo
  {
    return $this->belongsTo(Order::class);
  }


}
