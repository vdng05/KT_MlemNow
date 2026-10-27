<?php

namespace App\Decorators;

class SizeDecorator extends FoodDecorator
{
    protected $sizeName;
    protected $extraPrice;

    public function __construct(FoodItem $foodItem, $sizeName, $extraPrice)
    {
        parent::__construct($foodItem);
        $this->sizeName = $sizeName;
        $this->extraPrice = $extraPrice;
    }

    public function getPrice(): float
    {
        return $this->foodItem->getPrice() + $this->extraPrice;
    }

    public function getDescription(): string
    {
        return $this->foodItem->getDescription() . ' (Size: ' . $this->sizeName . ')';
    }
}