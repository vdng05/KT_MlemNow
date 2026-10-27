<?php

namespace App\Decorators;

interface FoodItem
{
    public function getPrice(): float;
    public function getDescription(): string;
}