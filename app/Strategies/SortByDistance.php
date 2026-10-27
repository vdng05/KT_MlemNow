<?php

namespace App\Strategies;

use Illuminate\Database\Eloquent\Builder;

class SortByDistance implements SortStrategy
{
    public function sort(Builder $query): Builder
    {
        // Tự động lấy tên bảng hiện tại (dù là 'food' hay 'foods')
        $table = $query->getModel()->getTable();

        return $query->join('restaurants', "$table.restaurant_id", '=', 'restaurants.id')
                     ->orderBy('restaurants.distance', 'asc')
                     ->select("$table.*");
    }
}