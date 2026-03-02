<?php

namespace Tests\Feature\Authorization;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class WorkspacePolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_workspace_member_roles_have_expected_permissions(): void
    {
        [$owner, $member, $viewer, $workspace] = $this->workspaceWithMembers();

        $this->assertTrue(Gate::forUser($owner)->allows('manageMembers', $workspace));
        $this->assertFalse(Gate::forUser($member)->allows('manageMembers', $workspace));
        $this->assertFalse(Gate::forUser($viewer)->allows('manageMembers', $workspace));

        $this->assertTrue(Gate::forUser($owner)->allows('update', $workspace));
        $this->assertFalse(Gate::forUser($member)->allows('update', $workspace));
        $this->assertFalse(Gate::forUser($viewer)->allows('update', $workspace));
    }

    public function test_non_member_cannot_view_workspace(): void
    {
        [, , , $workspace] = $this->workspaceWithMembers();
        $outsider = User::factory()->create();

        $this->assertFalse(Gate::forUser($outsider)->allows('view', $workspace));
    }

    private function workspaceWithMembers(): array
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $viewer = User::factory()->create();

        $workspace = Workspace::factory()->create([
            'owner_id' => $owner->id,
        ]);

        $workspace->members()->attach([
            $owner->id => ['role' => Workspace::ROLE_OWNER],
            $member->id => ['role' => Workspace::ROLE_MEMBER],
            $viewer->id => ['role' => Workspace::ROLE_VIEWER],
        ]);

        return [$owner, $member, $viewer, $workspace];
    }
}
