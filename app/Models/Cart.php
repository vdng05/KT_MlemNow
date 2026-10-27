<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\CartItem;
use App\Models\User;

class Cart extends Model {
    protected $guarded = [];

    public function items() {
        return $this->hasMany(CartItem::class);
    }
    public function user() {
        return $this->belongsTo(User::class);
    }
}