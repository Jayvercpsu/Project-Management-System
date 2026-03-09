<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_assigned_user_can_update_task_status(): void
    {
        $admin = User::factory()->admin()->create();
        $assignee = User::factory()->standardUser()->create();
        $project = Project::factory()->create(['created_by' => $admin->id]);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'assigned_to' => $assignee->id,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($assignee);

        $response = $this->putJson('/api/tasks/'.$task->id, [
            'status' => 'done',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'done');

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'done',
        ]);
    }

    public function test_assigned_user_cannot_reassign_task(): void
    {
        $admin = User::factory()->admin()->create();
        $assignee = User::factory()->standardUser()->create();
        $otherUser = User::factory()->standardUser()->create();
        $project = Project::factory()->create(['created_by' => $admin->id]);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'assigned_to' => $assignee->id,
        ]);

        Sanctum::actingAs($assignee);

        $response = $this->putJson('/api/tasks/'.$task->id, [
            'assigned_to' => $otherUser->id,
        ]);

        $response
            ->assertForbidden()
            ->assertJsonPath('success', false);
    }
}
