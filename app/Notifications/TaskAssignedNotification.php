<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Task $task)
    {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Task Assigned: '.$this->task->title)
            ->greeting('Hello '.$notifiable->name.',')
            ->line('A new task has been assigned to you in the Project Management System.')
            ->line('Task: '.$this->task->title)
            ->line('Project: '.($this->task->project?->title ?? 'N/A'))
            ->line('Status: '.$this->task->status->value)
            ->line('Due date: '.($this->task->due_date?->toDateString() ?? 'N/A'))
            ->line('Please review and update the task as needed.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'title' => $this->task->title,
        ];
    }
}
