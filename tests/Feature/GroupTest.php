<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_group_without_initial_members()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/groups', [
            'name' => 'Test Group',
            'description' => 'Test Description',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['data' => ['id', 'name', 'description', 'owner']]);

        $this->assertDatabaseHas('groups', [
            'name' => 'Test Group',
            'owner_id' => $user->id,
        ]);
    }

    public function test_can_create_group_with_initial_members()
    {
        $user = User::factory()->create();
        $emails = ['alice@example.com', 'bob@example.com'];

        $response = $this->actingAs($user)->postJson('/api/v1/groups', [
            'name' => 'Trip Group',
            'description' => 'Trip Description',
            'initial_members' => $emails,
        ]);

        $response->assertCreated();

        $group = $user->ownedGroups()->first();

        $this->assertDatabaseHas('groups', [
            'id' => $group->id,
            'name' => 'Trip Group',
        ]);

        foreach ($emails as $email) {
            $this->assertDatabaseHas('invitations', [
                'group_id' => $group->id,
                'inviter_id' => $user->id,
                'email' => $email,
                'status' => 'pending',
            ]);
        }
    }

    public function test_cannot_create_group_with_invalid_emails()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/groups', [
            'name' => 'Invalid Group',
            'initial_members' => ['invalid-email', 'valid@example.com'],
        ]);

        $response->assertUnprocessable();

        $content = $response->json();
        $this->assertArrayHasKey('initial_members.0', $content['data']['errors']);

        $this->assertDatabaseMissing('groups', ['name' => 'Invalid Group']);
    }
}
