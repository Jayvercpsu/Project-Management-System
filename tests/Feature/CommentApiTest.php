<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CommentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_add_comment_to_assigned_task(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->standardUser()->create();
        $project = Project::factory()->create(['created_by' => $admin->id]);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'assigned_to' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/tasks/'.$task->id.'/comments', [
            'body' => 'I have started working on this task.',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.body', 'I have started working on this task.');

        $this->assertDatabaseHas('comments', [
            'task_id' => $task->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_user_cannot_add_comment_to_unassigned_task(): void
    {
        $admin = User::factory()->admin()->create();
        $assignee = User::factory()->standardUser()->create();
        $otherUser = User::factory()->standardUser()->create();
        $project = Project::factory()->create(['created_by' => $admin->id]);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'assigned_to' => $assignee->id,
        ]);

        Sanctum::actingAs($otherUser);

        $response = $this->postJson('/api/tasks/'.$task->id.'/comments', [
            'body' => 'This should fail',
        ]);

        $response
            ->assertForbidden()
            ->assertJsonPath('success', false);
    }
}
