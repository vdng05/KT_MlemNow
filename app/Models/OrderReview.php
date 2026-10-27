<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderReview extends Model
{
    protected $fillable = ['order_id', 'user_id', 'rating', 'comment', 'images'];

    protected $casts = ['images' => 'array'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}