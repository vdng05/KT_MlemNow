<?php

namespace App\States;

interface OrderState
{
    public function getStatus(): string;
    public function getLabel(): string;
}