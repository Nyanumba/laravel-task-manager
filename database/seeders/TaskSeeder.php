<?php

namespace Database\Seeders;

use App\Models\Task;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $today = Carbon::today();

        $tasks = [
            ['title'=>'Set up CI/CD pipeline',    'due_date'=>$today->toDateString(),              'priority'=>'high',   'status'=>'pending'],
            ['title'=>'Write unit tests',          'due_date'=>$today->addDay()->toDateString(),    'priority'=>'high',   'status'=>'in_progress'],
            ['title'=>'Deploy to staging',         'due_date'=>$today->addDays(2)->toDateString(),  'priority'=>'high',   'status'=>'pending'],
            ['title'=>'Code review for PR #42',    'due_date'=>$today->addDays(3)->toDateString(),  'priority'=>'medium', 'status'=>'pending'],
            ['title'=>'Update API documentation',  'due_date'=>$today->addDays(4)->toDateString(),  'priority'=>'medium', 'status'=>'in_progress'],
            ['title'=>'Fix login page bug',        'due_date'=>$today->addDays(5)->toDateString(),  'priority'=>'medium', 'status'=>'done'],
            ['title'=>'Refactor database queries', 'due_date'=>$today->addDays(6)->toDateString(),  'priority'=>'low',    'status'=>'pending'],
            ['title'=>'Update README',             'due_date'=>$today->addDays(7)->toDateString(),  'priority'=>'low',    'status'=>'done'],
        ];

        foreach ($tasks as $task) {
            Task::firstOrCreate(
                ['title' => $task['title'], 'due_date' => $task['due_date']],
                $task
            );
        }

        $this->command->info('Tasks seeded: ' . count($tasks) . ' records.');
    }
}