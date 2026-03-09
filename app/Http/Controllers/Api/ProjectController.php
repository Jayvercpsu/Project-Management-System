<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProjectController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $search = $request->query('search');
        $cacheKey = 'projects:list:'.md5(json_encode([
            'user_id' => $user?->id,
            'role' => $user?->role?->value,
            'search' => $search,
            'page' => $request->query('page', 1),
        ]));

        $projects = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($user, $search) {
            $query = Project::query()
                ->with(['creator:id,name,email,role'])
                ->withCount('tasks')
                ->searchByTitle($search)
                ->orderByDesc('id');

            if ($user && $user->role === UserRole::USER) {
                $query->whereHas('tasks', function ($taskQuery) use ($user) {
                    $taskQuery->where('assigned_to', $user->id);
                });
            }

            return $query->paginate(10);
        });

        return $this->successResponse($projects, 'Projects retrieved successfully.');
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $project = Project::query()->create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        Cache::flush();

        return $this->successResponse($project->load('creator:id,name,email,role'), 'Project created successfully.', 201);
    }

    public function show(Request $request, Project $project): JsonResponse
    {
        $user = $request->user();

        if ($user && $user->role === UserRole::USER) {
            $hasTask = $project->tasks()->where('assigned_to', $user->id)->exists();

            if (! $hasTask) {
                return $this->errorResponse('You can only access projects with tasks assigned to you.', 403);
            }
        }

        $project->load([
            'creator:id,name,email,role',
            'tasks' => fn ($query) => $query->with('assignee:id,name,email,role')->orderByDesc('id'),
        ]);

        return $this->successResponse($project, 'Project retrieved successfully.');
    }

    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        $project->update($request->validated());
        Cache::flush();

        return $this->successResponse($project->fresh()->load('creator:id,name,email,role'), 'Project updated successfully.');
    }

    public function destroy(Project $project): JsonResponse
    {
        $project->delete();
        Cache::flush();

        return $this->successResponse(null, 'Project deleted successfully.');
    }
}
