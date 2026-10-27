<?php

namespace App\States;

class CompletedState implements OrderState
{
    public function getStatus(): string { return 'completed'; }
    public function getLabel(): string { return 'Đã giao'; }
}