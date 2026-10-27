<?php

namespace App\Decorators;

use App\Models\Food;

class BaseFood implements FoodItem
{
    protected $food;

    public function __construct(Food $food)
    {
        $this->food = $food;
    }

    public function getPrice(): float
    {
        return $this->food->sale_price ?? $this->food->base_price;
    }

    public function getDescription(): string
    {
        return $this->food->name;
    }
}