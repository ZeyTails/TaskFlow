<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\UserNotification;
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

    public function test_user_can_accept_workspace_invitation_from_notifications(): void
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

        $invitationId = DB::table('workspace_invitations')->insertGetId([
            'workspace_id' => $workspace->id,
            'email' => 'target@example.com',
            'role' => Workspace::ROLE_VIEWER,
            'job_title' => 'QA',
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
            ->post(route('notifications.invitations.accept', $invitationId))
            ->assertRedirect(route('notifications'));

        $this->assertDatabaseHas('workspace_user', [
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
            'role' => Workspace::ROLE_VIEWER,
            'job_title' => 'QA',
            'status' => Workspace::MEMBER_STATUS_ACTIVE,
        ]);

        $invitation = DB::table('workspace_invitations')->find($invitationId);

        $this->assertNotNull($invitation->accepted_at);
        $this->assertNull($invitation->cancelled_at);
    }

    public function test_user_can_decline_workspace_invitation_from_notifications(): void
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

        $invitationId = DB::table('workspace_invitations')->insertGetId([
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
            ->post(route('notifications.invitations.decline', $invitationId))
            ->assertRedirect(route('notifications'));

        $this->assertDatabaseMissing('workspace_user', [
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
        ]);

        $invitation = DB::table('workspace_invitations')->find($invitationId);

        $this->assertNull($invitation->accepted_at);
        $this->assertNotNull($invitation->cancelled_at);
    }

    public function test_notifications_page_shows_task_alerts_for_current_user(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create([
            'owner_id' => $user->id,
        ]);
        $task = Task::factory()->create();

        UserNotification::create([
            'user_id' => $user->id,
            'workspace_id' => $workspace->id,
            'task_id' => $task->id,
            'type' => 'task_updated',
            'title' => 'Tache modifiee',
            'body' => 'Une tache a ete modifiee.',
        ]);

        $this->actingAs($user)
            ->get(route('notifications'))
            ->assertOk()
            ->assertSee('Tache modifiee')
            ->assertSee('Une tache a ete modifiee.');
    }

    public function test_user_can_mark_task_alert_as_read(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create([
            'owner_id' => $user->id,
        ]);
        $task = Task::factory()->create();

        $notification = UserNotification::create([
            'user_id' => $user->id,
            'workspace_id' => $workspace->id,
            'task_id' => $task->id,
            'type' => 'task_updated',
            'title' => 'Tache modifiee',
            'body' => 'Une tache a ete modifiee.',
        ]);

        $this->actingAs($user)
            ->post(route('notifications.tasks.read', $notification))
            ->assertRedirect(route('notifications'));

        $this->assertDatabaseMissing('user_notifications', [
            'id' => $notification->id,
            'read_at' => null,
        ]);
    }
}
