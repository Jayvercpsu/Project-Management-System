<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Models\Comment;
use App\Models\Task;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    use ApiResponse;

    public function index(Request $request, Task $task): JsonResponse
    {
        if ($this->userCannotAccessTask($request->user(), $task)) {
            return $this->errorResponse('You can only view comments for tasks assigned to you.', 403);
        }

        $comments = $task->comments()
            ->with('user:id,name,email,role')
            ->latest('id')
            ->paginate(10);

        return $this->successResponse($comments, 'Comments retrieved successfully.');
    }

    public function store(StoreCommentRequest $request, Task $task): JsonResponse
    {
        if ($this->userCannotAccessTask($request->user(), $task)) {
            return $this->errorResponse('You can only comment on tasks assigned to you.', 403);
        }

        $comment = Comment::query()->create([
            'body' => $request->string('body')->value(),
            'task_id' => $task->id,
            'user_id' => $request->user()->id,
        ]);

        return $this->successResponse($comment->load('user:id,name,email,role'), 'Comment added successfully.', 201);
    }

    private function userCannotAccessTask($user, Task $task): bool
    {
        return $user && $user->role === UserRole::USER && $task->assigned_to !== $user->id;
    }
}
