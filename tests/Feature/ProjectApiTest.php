<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProjectApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_project(): void
    {
        $admin = User::factory()->admin()->create();
        Sanctum::actingAs($admin);

        $payload = [
            'title' => 'Backend Assessment Project',
            'description' => 'Project for API skill test',
            'start_date' => '2026-03-09',
            'end_date' => '2026-04-09',
        ];

        $response = $this->postJson('/api/projects', $payload);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', 'Backend Assessment Project');

        $this->assertDatabaseHas('projects', [
            'title' => 'Backend Assessment Project',
            'created_by' => $admin->id,
        ]);
    }

    public function test_non_admin_cannot_create_project(): void
    {
        $manager = User::factory()->manager()->create();
        Sanctum::actingAs($manager);

        $response = $this->postJson('/api/projects', [
            'title' => 'Unauthorized Project',
            'start_date' => '2026-03-09',
            'end_date' => '2026-04-09',
        ]);

        $response
            ->assertForbidden()
            ->assertJsonPath('success', false);
    }
}
