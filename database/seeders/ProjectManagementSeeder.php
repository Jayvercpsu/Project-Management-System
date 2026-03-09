<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectManagementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admins = collect([
            ['name' => 'Admin One', 'email' => 'admin1@pms.test', 'phone' => '09111111111', 'role' => UserRole::ADMIN],
            ['name' => 'Admin Two', 'email' => 'admin2@pms.test', 'phone' => '09111111112', 'role' => UserRole::ADMIN],
            ['name' => 'Admin Three', 'email' => 'admin3@pms.test', 'phone' => '09111111113', 'role' => UserRole::ADMIN],
        ])->map(fn (array $data) => User::factory()->create($data));

        $managers = collect([
            ['name' => 'Manager One', 'email' => 'manager1@pms.test', 'phone' => '09222222221', 'role' => UserRole::MANAGER],
            ['name' => 'Manager Two', 'email' => 'manager2@pms.test', 'phone' => '09222222222', 'role' => UserRole::MANAGER],
            ['name' => 'Manager Three', 'email' => 'manager3@pms.test', 'phone' => '09222222223', 'role' => UserRole::MANAGER],
        ])->map(fn (array $data) => User::factory()->create($data));

        $users = collect([
            ['name' => 'User One', 'email' => 'user1@pms.test', 'phone' => '09333333331', 'role' => UserRole::USER],
            ['name' => 'User Two', 'email' => 'user2@pms.test', 'phone' => '09333333332', 'role' => UserRole::USER],
            ['name' => 'User Three', 'email' => 'user3@pms.test', 'phone' => '09333333333', 'role' => UserRole::USER],
            ['name' => 'User Four', 'email' => 'user4@pms.test', 'phone' => '09333333334', 'role' => UserRole::USER],
            ['name' => 'User Five', 'email' => 'user5@pms.test', 'phone' => '09333333335', 'role' => UserRole::USER],
        ])->map(fn (array $data) => User::factory()->create($data));

        $assignees = $managers->concat($users);

        $projects = Project::factory()->count(5)->make()->each(function (Project $project, int $index) use ($admins): void {
            $project->created_by = $admins[$index % $admins->count()]->id;
            $project->save();
        });

        $tasks = collect();

        foreach (range(1, 10) as $iteration) {
            $tasks->push(Task::factory()->create([
                'project_id' => $projects->random()->id,
                'assigned_to' => $assignees->random()->id,
            ]));
        }

        foreach (range(1, 10) as $iteration) {
            Comment::factory()->create([
                'task_id' => $tasks->random()->id,
                'user_id' => $assignees->concat($admins)->random()->id,
            ]);
        }
    }
}
