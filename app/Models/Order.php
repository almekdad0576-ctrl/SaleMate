<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    protected $table = 'orders';
    protected $primaryKey = 'order_id';
    public $incrementing = true;
    use SoftDeletes;

    protected $fillable = ['order_date', 'order_type', 'total_amount', 'total_price', 'status', 'user_id'];

        public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
        {
            return $this->belongsTo(User::class, 'user_id');
        }

    public function orderItems(): HasMany
    {
            return $this->hasMany(OrderItem::class, 'order_id');
    }
}
