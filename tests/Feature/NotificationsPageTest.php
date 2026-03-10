<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NotificationsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_open_notifications_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('notifications'))
            ->assertOk();
    }

    public function test_notifications_page_shows_pending_workspace_invitations_for_user_email(): void
    {
        $user = User::factory()->create([
            'email' => 'target@example.com',
        ]);
        $owner = User::factory()->create();

        $workspace = Workspace::factory()->create([
            'owner_id' => $owner->id,
        ]);

        $workspace->members()->attach($owner->id, [
            'role' => Workspace::ROLE_OWNER,
            'status' => Workspace::MEMBER_STATUS_ACTIVE,
        ]);

        DB::table('workspace_invitations')->insert([
            'workspace_id' => $workspace->id,
            'email' => 'target@example.com',
            'role' => Workspace::ROLE_MEMBER,
            'job_title' => 'Backend',
            'token' => 'token-'.str()->random(32),
            'expires_at' => now()->addDays(3),
            'invited_by' => $owner->id,
            'last_sent_at' => now(),
            'reminders_count' => 0,
            'accepted_at' => null,
            'cancelled_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('notifications'))
            ->assertOk()
            ->assertSee($workspace->name)
            ->assertSee('target@example.com');
    }
}
