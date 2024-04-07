<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderInternalDetail extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = ['order_id', 'completed_at', 'empty_pdf', 'final_pdf', 'created_by', 'updated_by', 'deleted_by'];

  public function order()
  {
    return $this->belongsTo(Order::class);
  }
}
