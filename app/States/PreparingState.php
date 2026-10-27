<?php

namespace App\States;

class PreparingState implements OrderState
{
    public function getStatus(): string { return 'preparing'; }
    public function getLabel(): string { return 'Đang chuẩn bị'; }
}