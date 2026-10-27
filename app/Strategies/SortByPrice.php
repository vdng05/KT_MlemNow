<?php

namespace App\Strategies;

use Illuminate\Database\Eloquent\Builder;

class SortByPrice implements SortStrategy
{
    public function sort(Builder $query): Builder
    {
        return $query->orderBy('base_price', 'asc');
    }
}