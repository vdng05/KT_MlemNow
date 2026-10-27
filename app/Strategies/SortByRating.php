<?php

namespace App\Strategies;

use Illuminate\Database\Eloquent\Builder;

class SortByRating implements SortStrategy
{
    public function sort(Builder $query): Builder
    {
        // Tự động lấy tên bảng hiện tại
        $table = $query->getModel()->getTable();

        return $query->join('restaurants', "$table.restaurant_id", '=', 'restaurants.id')
                     ->orderBy('restaurants.rating', 'desc')
                     ->select("$table.*");
    }
}