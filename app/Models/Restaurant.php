<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    protected $guarded = [];

    public function foods()
    {
        return $this->hasMany(Food::class);
    }
}