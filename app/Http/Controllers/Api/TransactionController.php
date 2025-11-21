<?php

namespace App\Http\Controllers\Api;

use App\Events\TransactionCompleted;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $transactions = Transaction::where('sender_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->with(['sender:id,name,email', 'receiver:id,name,email'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'balance' => $user->balance,
            'transactions' => $transactions
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $sender = $request->user();

        if ($request->receiver_id == $sender->id) {
            return response()->json(['message' => 'Cannot transfer to self.'], 422);
        }

        $amount = $request->amount;
        $commissionRate = config('wallet.commission_rate');
        $commissionFee = $amount * $commissionRate;
        $totalDeduction = $amount + $commissionFee;

        // Atomic transaction with locking
        try {
            DB::transaction(function () use ($sender, $request, $amount, $commissionFee, $totalDeduction) {
                // Lock users in consistent order (by ID) to prevent deadlocks
                $firstId = min($sender->id, $request->receiver_id);
                $secondId = max($sender->id, $request->receiver_id);

                // Lock both records
                $users = User::whereIn('id', [$firstId, $secondId])->lockForUpdate()->get()->keyBy('id');

                $sender = $users[$sender->id];
                $receiver = $users[$request->receiver_id];

                if ($sender->balance < $totalDeduction) {
                    throw new \Exception('Insufficient balance.');
                }

                // Deduct from sender
                $sender->balance -= $totalDeduction;
                $sender->save();

                // Add to receiver
                $receiver->balance += $amount;
                $receiver->save();

                // Record transaction
                $transaction = Transaction::create([
                    'sender_id' => $sender->id,
                    'receiver_id' => $receiver->id,
                    'amount' => $amount,
                    'commission_fee' => $commissionFee,
                ]);

                // Load relationships for broadcasting
                $transaction->load(['sender:id,name,email', 'receiver:id,name,email']);

                // Broadcast event
                broadcast(new TransactionCompleted($transaction, $sender->balance, $receiver->balance));
            });

            return response()->json(['message' => 'Transfer successful'], 201);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
