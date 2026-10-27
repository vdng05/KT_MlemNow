<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Cart;
use App\Models\Food;

class CartItem extends Model {
    protected $guarded = [];

    public function cart() {
        return $this->belongsTo(Cart::class);
    }
    public function food() {
        return $this->belongsTo(Food::class);
    }
}