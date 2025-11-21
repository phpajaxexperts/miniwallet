<?php

namespace App\Events;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransactionCompleted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $transaction;
    public $senderBalance;
    public $receiverBalance;

    /**
     * Create a new event instance.
     */
    public function __construct(Transaction $transaction, $senderBalance, $receiverBalance)
    {
        $this->transaction = $transaction;
        $this->senderBalance = $senderBalance;
        $this->receiverBalance = $receiverBalance;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->transaction->sender_id),
            new PrivateChannel('user.' . $this->transaction->receiver_id),
        ];
    }

    public function broadcastWith()
    {
        return [
            'transaction' => $this->transaction,
            'sender_balance' => $this->senderBalance,
            'receiver_balance' => $this->receiverBalance,
        ];
    }

    public function broadcastAs()
    {
        return 'TransactionCompleted';
    }
}
