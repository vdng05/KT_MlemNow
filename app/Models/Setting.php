<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    //
    protected $fillable = ['key', 'value'];
    public $timestamps = false; // Bảng setting đơn giản không cần created_at/updated_at
}
