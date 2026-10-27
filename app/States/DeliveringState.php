<?php

namespace App\States;

class DeliveringState implements OrderState
{
    public function getStatus(): string { return 'delivering'; }
    public function getLabel(): string { return 'Đang giao'; }
}