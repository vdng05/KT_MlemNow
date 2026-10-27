<?php

namespace App\Decorators;

class ToppingDecorator extends FoodDecorator
{
    protected $toppingName;
    protected $extraPrice;

    public function __construct(FoodItem $foodItem, $toppingName, $extraPrice)
    {
        parent::__construct($foodItem);
        $this->toppingName = $toppingName;
        $this->extraPrice = $extraPrice;
    }

    public function getPrice(): float
    {
        return $this->foodItem->getPrice() + $this->extraPrice;
    }

    public function getDescription(): string
    {
        return $this->foodItem->getDescription() . ' + ' . $this->toppingName;
    }
}