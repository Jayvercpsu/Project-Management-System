<?php

namespace App\Jobs;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendTaskAssignedNotificationJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $taskId,
        public int $assigneeId,
    ) {
    }

    public function handle(): void
    {
        $task = Task::query()->with('project')->find($this->taskId);
        $assignee = User::query()->find($this->assigneeId);

        if (! $task || ! $assignee) {
            return;
        }

        $assignee->notify(new TaskAssignedNotification($task));
    }
}
