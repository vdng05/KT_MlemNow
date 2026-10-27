<?php

namespace App\Strategies;

use Illuminate\Database\Eloquent\Builder;

interface RestaurantSortStrategy
{
    public function sort(Builder $query): Builder;
}