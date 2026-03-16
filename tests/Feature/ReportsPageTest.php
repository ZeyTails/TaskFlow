<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_open_reports_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('reports'))
            ->assertOk()
            ->assertSee('Rapports d activite');
    }

    public function test_authenticated_user_can_export_reports_csv(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('reports.export'))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}
