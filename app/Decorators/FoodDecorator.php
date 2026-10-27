<?php

namespace App\Decorators;

abstract class FoodDecorator implements FoodItem
{
    protected $foodItem;

    public function __construct(FoodItem $foodItem)
    {
        $this->foodItem = $foodItem;
    }

    abstract public function getPrice(): float;
    abstract public function getDescription(): string;
}