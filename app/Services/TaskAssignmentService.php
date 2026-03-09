<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Jobs\SendTaskAssignedNotificationJob;
use App\Models\Task;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class TaskAssignmentService
{
    /**
     * @throws ValidationException
     */
    public function assign(Task $task, int $assigneeId): Task
    {
        $assignee = User::query()->find($assigneeId);

        if (! $assignee) {
            throw ValidationException::withMessages([
                'assigned_to' => ['Selected assignee does not exist.'],
            ]);
        }

        if ($assignee->role === UserRole::ADMIN) {
            throw ValidationException::withMessages([
                'assigned_to' => ['Tasks cannot be assigned to admin users.'],
            ]);
        }

        $task->assigned_to = $assignee->id;
        $task->save();

        SendTaskAssignedNotificationJob::dispatch($task->id, $assignee->id);

        return $task->refresh();
    }
}
