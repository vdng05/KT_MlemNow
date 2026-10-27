<?php

namespace App\Strategies;

use Illuminate\Database\Eloquent\Builder;

class SortRestaurantByDistance implements RestaurantSortStrategy
{
    public function sort(Builder $query): Builder
    {
        return $query->orderBy('distance', 'asc');
    }
}