<?php

namespace App\Strategies;

use Illuminate\Database\Eloquent\Builder;

class SortRestaurantByRating implements RestaurantSortStrategy
{
    public function sort(Builder $query): Builder
    {
        return $query->orderBy('rating', 'desc');
    }
}