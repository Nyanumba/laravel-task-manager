<?php

namespace Tests\Feature;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    private function makeTask(array $overrides = []): Task
    {
        return Task::create(array_merge([
            'title'    => 'Test Task ' . uniqid(),
            'due_date' => today()->addDay()->toDateString(),
            'priority' => Task::PRIORITY_MEDIUM,
            'status'   => Task::STATUS_PENDING,
        ], $overrides));
    }

    // -------------------------------------------------------
    // POST /api/tasks
    // -------------------------------------------------------

    public function test_can_create_a_task(): void
    {
        $response = $this->postJson('/api/tasks', [
            'title'    => 'Deploy to production',
            'due_date' => today()->addDays(3)->toDateString(),
            'priority' => 'high',
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.title', 'Deploy to production')
                 ->assertJsonPath('data.status', 'pending')
                 ->assertJsonPath('data.priority', 'high');

        $this->assertDatabaseHas('tasks', ['title' => 'Deploy to production']);
    }

    public function test_cannot_create_task_with_past_due_date(): void
    {
        $response = $this->postJson('/api/tasks', [
            'title'    => 'Old task',
            'due_date' => today()->subDay()->toDateString(),
            'priority' => 'low',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['due_date']);
    }

    public function test_cannot_create_duplicate_title_on_same_due_date(): void
    {
        $date = today()->addDays(2)->toDateString();
        $this->makeTask(['title' => 'Unique Title', 'due_date' => $date]);

        $response = $this->postJson('/api/tasks', [
            'title'    => 'Unique Title',
            'due_date' => $date,
            'priority' => 'low',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['title']);
    }

    public function test_same_title_on_different_due_dates_is_allowed(): void
    {
        $this->makeTask([
            'title'    => 'Recurring Task',
            'due_date' => today()->addDay()->toDateString(),
        ]);

        $response = $this->postJson('/api/tasks', [
            'title'    => 'Recurring Task',
            'due_date' => today()->addDays(5)->toDateString(),
            'priority' => 'medium',
        ]);

        $response->assertStatus(201);
    }

    public function test_cannot_create_task_with_invalid_priority(): void
    {
        $response = $this->postJson('/api/tasks', [
            'title'    => 'Bad priority',
            'due_date' => today()->addDay()->toDateString(),
            'priority' => 'urgent',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['priority']);
    }

    public function test_create_requires_all_fields(): void
    {
        $response = $this->postJson('/api/tasks', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['title', 'due_date', 'priority']);
    }

    public function test_status_is_always_pending_on_creation(): void
    {
        $response = $this->postJson('/api/tasks', [
            'title'    => 'Auto pending task',
            'due_date' => today()->addDay()->toDateString(),
            'priority' => 'low',
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.status', 'pending');
    }

    // -------------------------------------------------------
    // GET /api/tasks
    // -------------------------------------------------------

    public function test_list_returns_empty_message_when_no_tasks(): void
    {
        $response = $this->getJson('/api/tasks');

        $response->assertStatus(200)
                 ->assertJson(['message' => 'No tasks found.', 'data' => []]);
    }

    public function test_list_returns_all_tasks(): void
    {
        $this->makeTask(['priority' => 'low']);
        $this->makeTask(['priority' => 'high']);
        $this->makeTask(['priority' => 'medium']);

        $response = $this->getJson('/api/tasks');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    public function test_list_sorted_high_priority_first(): void
    {
        $date = today()->addDay()->toDateString();
        $this->makeTask(['priority' => 'low',    'due_date' => $date]);
        $this->makeTask(['priority' => 'medium', 'due_date' => $date]);
        $this->makeTask(['priority' => 'high',   'due_date' => $date]);

        $data = $this->getJson('/api/tasks')->json('data');

        $this->assertEquals('high',   $data[0]['priority']);
        $this->assertEquals('medium', $data[1]['priority']);
        $this->assertEquals('low',    $data[2]['priority']);
    }

    public function test_list_filter_by_status(): void
    {
        $this->makeTask(['status' => Task::STATUS_PENDING]);
        $this->makeTask(['status' => Task::STATUS_IN_PROGRESS]);
        $this->makeTask(['status' => Task::STATUS_DONE]);

        $response = $this->getJson('/api/tasks?status=pending');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('pending', $response->json('data.0.status'));
    }

    public function test_list_rejects_invalid_status_filter(): void
    {
        $this->getJson('/api/tasks?status=invalid')->assertStatus(422);
    }

    // -------------------------------------------------------
    // PATCH /api/tasks/{id}/status
    // -------------------------------------------------------

    public function test_can_advance_pending_to_in_progress(): void
    {
        $task = $this->makeTask(['status' => Task::STATUS_PENDING]);

        $this->patchJson("/api/tasks/{$task->id}/status", ['status' => 'in_progress'])
             ->assertStatus(200)
             ->assertJsonPath('data.status', 'in_progress');
    }

    public function test_can_advance_in_progress_to_done(): void
    {
        $task = $this->makeTask(['status' => Task::STATUS_IN_PROGRESS]);

        $this->patchJson("/api/tasks/{$task->id}/status", ['status' => 'done'])
             ->assertStatus(200)
             ->assertJsonPath('data.status', 'done');
    }

    public function test_cannot_skip_from_pending_to_done(): void
    {
        $task = $this->makeTask(['status' => Task::STATUS_PENDING]);

        $this->patchJson("/api/tasks/{$task->id}/status", ['status' => 'done'])
             ->assertStatus(422);
    }

    public function test_cannot_revert_status(): void
    {
        $task = $this->makeTask(['status' => Task::STATUS_IN_PROGRESS]);

        $this->patchJson("/api/tasks/{$task->id}/status", ['status' => 'pending'])
             ->assertStatus(422);
    }

    public function test_cannot_advance_beyond_done(): void
    {
        $task = $this->makeTask(['status' => Task::STATUS_DONE]);

        $this->patchJson("/api/tasks/{$task->id}/status", ['status' => 'done'])
             ->assertStatus(422);
    }

    public function test_update_status_returns_404_for_missing_task(): void
    {
        $this->patchJson('/api/tasks/9999/status', ['status' => 'in_progress'])
             ->assertStatus(404);
    }

    // -------------------------------------------------------
    // DELETE /api/tasks/{id}
    // -------------------------------------------------------

    public function test_can_delete_done_task(): void
    {
        $task = $this->makeTask(['status' => Task::STATUS_DONE]);

        $this->deleteJson("/api/tasks/{$task->id}")->assertStatus(200);
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_cannot_delete_pending_task(): void
    {
        $task = $this->makeTask(['status' => Task::STATUS_PENDING]);

        $this->deleteJson("/api/tasks/{$task->id}")->assertStatus(403);
        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }

    public function test_cannot_delete_in_progress_task(): void
    {
        $task = $this->makeTask(['status' => Task::STATUS_IN_PROGRESS]);

        $this->deleteJson("/api/tasks/{$task->id}")->assertStatus(403);
    }

    public function test_delete_returns_404_for_missing_task(): void
    {
        $this->deleteJson('/api/tasks/9999')->assertStatus(404);
    }

    // -------------------------------------------------------
    // GET /api/tasks/report
    // -------------------------------------------------------

    public function test_report_returns_correct_structure(): void
    {
        $date = today()->addDay()->toDateString();
        $this->makeTask(['priority' => 'high',   'status' => Task::STATUS_PENDING,     'due_date' => $date]);
        $this->makeTask(['priority' => 'high',   'status' => Task::STATUS_IN_PROGRESS, 'due_date' => $date]);
        $this->makeTask(['priority' => 'medium', 'status' => Task::STATUS_DONE,        'due_date' => $date]);

        $response = $this->getJson("/api/tasks/report?date={$date}");

        $response->assertStatus(200)
                 ->assertJsonStructure(['date', 'summary' => [
                     'high'   => ['pending', 'in_progress', 'done'],
                     'medium' => ['pending', 'in_progress', 'done'],
                     'low'    => ['pending', 'in_progress', 'done'],
                 ]]);

        $this->assertEquals(1, $response->json('summary.high.pending'));
        $this->assertEquals(1, $response->json('summary.high.in_progress'));
        $this->assertEquals(0, $response->json('summary.high.done'));
        $this->assertEquals(1, $response->json('summary.medium.done'));
    }

    public function test_report_zero_fills_all_combinations(): void
    {
        $response = $this->getJson('/api/tasks/report?date=' . today()->toDateString());

        $response->assertStatus(200);

        foreach (['high', 'medium', 'low'] as $p) {
            foreach (['pending', 'in_progress', 'done'] as $s) {
                $this->assertEquals(0, $response->json("summary.{$p}.{$s}"));
            }
        }
    }

    public function test_report_requires_date_parameter(): void
    {
        $this->getJson('/api/tasks/report')->assertStatus(422);
    }

    public function test_report_requires_valid_date_format(): void
    {
        $this->getJson('/api/tasks/report?date=not-a-date')->assertStatus(422);
    }
}