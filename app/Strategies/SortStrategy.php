<?php

namespace App\Strategies;

use Illuminate\Database\Eloquent\Builder;

interface SortStrategy
{
    // Hàm nhận vào một query và trả về query đã được sắp xếp
    public function sort(Builder $query): Builder;
}