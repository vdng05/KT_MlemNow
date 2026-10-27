<?php

namespace App\States;

class PendingState implements OrderState
{
    public function getStatus(): string { return 'pending'; }
    public function getLabel(): string { return 'Chờ xác nhận'; }
}