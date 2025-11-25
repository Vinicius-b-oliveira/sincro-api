<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Group;
use App\Models\Transaction;
use App\Enums\TransactionType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BalanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_get_personal_balance()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Create personal transactions (no group)
        Transaction::factory()->create([
            'user_id' => $user->id,
            'group_id' => null,
            'type' => TransactionType::INCOME,
            'amount' => 1000.00,
            'transaction_date' => now()->startOfMonth(), // Current month
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'group_id' => null,
            'type' => TransactionType::EXPENSE,
            'amount' => 250.00,
            'transaction_date' => now()->startOfMonth(), // Current month
        ]);

        // Old transaction (different month)
        Transaction::factory()->create([
            'user_id' => $user->id,
            'group_id' => null,
            'type' => TransactionType::INCOME,
            'amount' => 500.00,
            'transaction_date' => now()->subMonth(),
        ]);

        $response = $this->getJson('/api/v1/balance');

        $response->assertStatus(200)
            ->assertJson([
                'total_balance' => 1250.00, // (1000 + 500) - 250
                'period_income' => 1000.00, // Only current month income
                'period_expenses' => 250.00, // Only current month expenses
            ]);
    }

    public function test_user_can_get_group_balance()
    {
        $user = User::factory()->create();
        $group = Group::factory()->create(['owner_id' => $user->id]);

        // Add user as member to the group
        $group->members()->attach($user->id, ['role' => 'admin']);

        Sanctum::actingAs($user);

        // Create group transactions
        Transaction::factory()->create([
            'user_id' => $user->id,
            'group_id' => $group->id,
            'type' => TransactionType::INCOME,
            'amount' => 2000.00,
            'transaction_date' => now()->startOfMonth(),
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'group_id' => $group->id,
            'type' => TransactionType::EXPENSE,
            'amount' => 500.00,
            'transaction_date' => now()->startOfMonth(),
        ]);

        // Personal transaction (should not be included in group balance)
        Transaction::factory()->create([
            'user_id' => $user->id,
            'group_id' => null,
            'type' => TransactionType::INCOME,
            'amount' => 1000.00,
            'transaction_date' => now()->startOfMonth(),
        ]);

        $response = $this->getJson("/api/v1/balance?group_id={$group->id}");

        $response->assertStatus(200)
            ->assertJson([
                'total_balance' => 1500.00, // 2000 - 500 (only group transactions)
                'period_income' => 2000.00,
                'period_expenses' => 500.00,
            ]);
    }

    public function test_unauthorized_user_cannot_access_balance()
    {
        $response = $this->getJson('/api/v1/balance');

        $response->assertStatus(401);
    }

    public function test_user_cannot_access_unauthorized_group_balance()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $group = Group::factory()->create(['owner_id' => $otherUser->id]);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/balance?group_id={$group->id}");

        $response->assertStatus(403);
    }
}