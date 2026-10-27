<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Food extends Model
{
    protected $guarded = [];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function sizes()
    {
        return $this->hasMany(Size::class);
    }

    public function toppings()
    {
        return $this->hasMany(Topping::class);
    }

    public function reviews()
    {
        return $this->hasManyThrough(OrderReview::class, OrderItem::class, 'food_id', 'order_id', 'id', 'order_id');
    }
}