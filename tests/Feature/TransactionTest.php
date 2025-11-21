<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use Laravel\Sanctum\Sanctum;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_transfer_money()
    {
        $sender = User::factory()->create(['balance' => 1000]);
        $receiver = User::factory()->create(['balance' => 0]);

        Sanctum::actingAs($sender);

        $response = $this->postJson('/api/transactions', [
            'receiver_id' => $receiver->id,
            'amount' => 100,
        ]);

        $response->assertStatus(201);

        $sender->refresh();
        $receiver->refresh();

        // 100 + 1.5 (1.5% fee) = 101.5 deducted
        $this->assertEquals(898.50, $sender->balance);
        $this->assertEquals(100.00, $receiver->balance);

        $this->assertDatabaseHas('transactions', [
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'amount' => 100,
            'commission_fee' => 1.50,
        ]);
    }

    public function test_user_cannot_transfer_insufficient_funds()
    {
        $sender = User::factory()->create(['balance' => 50]);
        $receiver = User::factory()->create(['balance' => 0]);

        Sanctum::actingAs($sender);

        $response = $this->postJson('/api/transactions', [
            'receiver_id' => $receiver->id,
            'amount' => 100,
        ]);

        $response->assertStatus(400);

        $sender->refresh();
        $this->assertEquals(50, $sender->balance);
    }

    public function test_user_cannot_transfer_to_self()
    {
        $sender = User::factory()->create(['balance' => 1000]);

        Sanctum::actingAs($sender);

        $response = $this->postJson('/api/transactions', [
            'receiver_id' => $sender->id,
            'amount' => 100,
        ]);

        $response->assertStatus(422);
    }
}
