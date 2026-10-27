<?php

namespace App\States;

class ConfirmedState implements OrderState
{
    public function getStatus(): string { return 'confirmed'; }
    public function getLabel(): string { return 'Đã xác nhận'; }
}