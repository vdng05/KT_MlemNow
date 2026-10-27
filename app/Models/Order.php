<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\States\PendingState;

class Order extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function review()
    {
        return $this->hasOne(OrderReview::class);
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function statusHistory()
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    // Gắn State Pattern vào Model để lấy nhãn trạng thái trực quan
    public function getStateLabel()
    {
        $state = match($this->status) {
            'pending' => new PendingState(),
            'confirmed' => new \App\States\ConfirmedState(),
            'preparing' => new \App\States\PreparingState(),
            'delivering' => new \App\States\DeliveringState(),
            'completed' => new \App\States\CompletedState(),
            'cancelled' => new \App\States\CancelledState(),
            default => new PendingState(),
        };
        return $state->getLabel();
    }
}