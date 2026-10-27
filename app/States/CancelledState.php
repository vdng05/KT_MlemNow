<?php

namespace App\States;

class CancelledState implements OrderState
{
    public function getStatus(): string { return 'cancelled'; }
    public function getLabel(): string { return 'Đã hủy'; }
}