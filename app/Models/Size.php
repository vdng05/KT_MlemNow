<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Size extends Model
{
    protected $guarded = [];

    public function food()
    {
        return $this->belongsTo(Food::class);
    }
}