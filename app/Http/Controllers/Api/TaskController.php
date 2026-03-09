<?php

namespace App\Http\Controllers\Api;

use App\Enums\TaskStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Project;
use App\Models\Task;
use App\Services\TaskAssignmentService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TaskController extends Controller
{
    use ApiResponse;

    public function index(Request $request, Project $project): JsonResponse
    {
        $user = $request->user();

        $tasks = $project->tasks()
            ->with('assignee:id,name,email,role')
            ->filterByStatus($request->query('status'))
            ->searchByTitle($request->query('search'))
            ->when($user && $user->role === UserRole::USER, fn ($query) => $query->where('assigned_to', $user->id))
            ->orderByDesc('id')
            ->paginate(10);

        return $this->successResponse($tasks, 'Tasks retrieved successfully.');
    }

    public function show(Request $request, Task $task): JsonResponse
    {
        if ($this->userCannotAccessTask($request->user(), $task)) {
            return $this->errorResponse('You can only access tasks assigned to you.', 403);
        }

        $task->load(['project', 'assignee:id,name,email,role', 'comments.user:id,name,email,role']);

        return $this->successResponse($task, 'Task retrieved successfully.');
    }

    public function store(StoreTaskRequest $request, Project $project, TaskAssignmentService $assignmentService): JsonResponse
    {
        $validated = $request->validated();
        $assigneeId = (int) $validated['assigned_to'];
        unset($validated['assigned_to']);

        $task = Task::query()->create([
            ...$validated,
            'project_id' => $project->id,
            'status' => $validated['status'] ?? TaskStatus::PENDING->value,
        ]);

        try {
            $task = $assignmentService->assign($task, $assigneeId);
        } catch (ValidationException $exception) {
            return $this->errorResponse('Validation failed.', 422, $exception->errors());
        }

        return $this->successResponse($task->load(['project', 'assignee:id,name,email,role']), 'Task created successfully.', 201);
    }

    public function update(UpdateTaskRequest $request, Task $task, TaskAssignmentService $assignmentService): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        if ($user->role === UserRole::MANAGER) {
            $assigneeId = $validated['assigned_to'] ?? null;
            unset($validated['assigned_to']);

            $task->update($validated);

            if ($assigneeId !== null) {
                try {
                    $task = $assignmentService->assign($task, (int) $assigneeId);
                } catch (ValidationException $exception) {
                    return $this->errorResponse('Validation failed.', 422, $exception->errors());
                }
            }

            return $this->successResponse($task->fresh()->load(['project', 'assignee:id,name,email,role']), 'Task updated successfully.');
        }

        if ($user->role === UserRole::USER && $task->assigned_to === $user->id) {
            if (array_diff(array_keys($validated), ['status']) !== []) {
                return $this->errorResponse('Assigned users can only update task status.', 403);
            }

            $task->update(['status' => $validated['status'] ?? $task->status]);

            return $this->successResponse($task->fresh()->load(['project', 'assignee:id,name,email,role']), 'Task updated successfully.');
        }

        return $this->errorResponse('You do not have permission to update this task.', 403);
    }

    public function destroy(Task $task): JsonResponse
    {
        $task->delete();

        return $this->successResponse(null, 'Task deleted successfully.');
    }

    private function userCannotAccessTask($user, Task $task): bool
    {
        return $user && $user->role === UserRole::USER && $task->assigned_to !== $user->id;
    }
}
