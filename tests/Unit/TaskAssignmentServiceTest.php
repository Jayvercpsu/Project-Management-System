<?php

namespace Tests\Unit;

use App\Jobs\SendTaskAssignedNotificationJob;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskAssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TaskAssignmentServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_assigns_task_and_dispatches_notification_job(): void
    {
        Queue::fake();

        $admin = User::factory()->admin()->create();
        $assignee = User::factory()->standardUser()->create();
        $project = Project::factory()->create(['created_by' => $admin->id]);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'assigned_to' => null,
        ]);

        $service = app(TaskAssignmentService::class);
        $service->assign($task, $assignee->id);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'assigned_to' => $assignee->id,
        ]);

        Queue::assertPushed(SendTaskAssignedNotificationJob::class);
    }

    public function test_it_rejects_assigning_tasks_to_admin(): void
    {
        $this->expectException(ValidationException::class);

        $admin = User::factory()->admin()->create();
        $project = Project::factory()->create(['created_by' => $admin->id]);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'assigned_to' => null,
        ]);

        $service = app(TaskAssignmentService::class);
        $service->assign($task, $admin->id);
    }
}
