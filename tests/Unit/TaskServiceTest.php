<?php

namespace Tests\Unit;

use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskServiceTest extends TestCase
{
    use RefreshDatabase;

    private TaskService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TaskService();
    }

    private function makeTask(array $overrides = []): Task
    {
        return Task::create(array_merge([
            'title'    => 'Unit Test Task ' . uniqid(),
            'due_date' => today()->addDay()->toDateString(),
            'priority' => Task::PRIORITY_MEDIUM,
            'status'   => Task::STATUS_PENDING,
        ], $overrides));
    }

    // -------------------------------------------------------
    // Model constants
    // -------------------------------------------------------

    public function test_model_has_correct_status_constants(): void
    {
        $this->assertEquals('pending',     Task::STATUS_PENDING);
        $this->assertEquals('in_progress', Task::STATUS_IN_PROGRESS);
        $this->assertEquals('done',        Task::STATUS_DONE);
    }

    public function test_model_has_correct_priority_constants(): void
    {
        $this->assertEquals('low',    Task::PRIORITY_LOW);
        $this->assertEquals('medium', Task::PRIORITY_MEDIUM);
        $this->assertEquals('high',   Task::PRIORITY_HIGH);
    }

    // -------------------------------------------------------
    // Task::nextStatus()
    // -------------------------------------------------------

    public function test_pending_next_status_is_in_progress(): void
    {
        $task = $this->makeTask(['status' => Task::STATUS_PENDING]);
        $this->assertEquals(Task::STATUS_IN_PROGRESS, $task->nextStatus());
    }

    public function test_in_progress_next_status_is_done(): void
    {
        $task = $this->makeTask(['status' => Task::STATUS_IN_PROGRESS]);
        $this->assertEquals(Task::STATUS_DONE, $task->nextStatus());
    }

    public function test_done_has_no_next_status(): void
    {
        $task = $this->makeTask(['status' => Task::STATUS_DONE]);
        $this->assertNull($task->nextStatus());
    }

    // -------------------------------------------------------
    // Task::canTransitionTo()
    // -------------------------------------------------------

    public function test_can_transition_pending_to_in_progress(): void
    {
        $task = $this->makeTask(['status' => Task::STATUS_PENDING]);
        $this->assertTrue($task->canTransitionTo(Task::STATUS_IN_PROGRESS));
    }

    public function test_cannot_skip_pending_to_done(): void
    {
        $task = $this->makeTask(['status' => Task::STATUS_PENDING]);
        $this->assertFalse($task->canTransitionTo(Task::STATUS_DONE));
    }

    public function test_cannot_revert_in_progress_to_pending(): void
    {
        $task = $this->makeTask(['status' => Task::STATUS_IN_PROGRESS]);
        $this->assertFalse($task->canTransitionTo(Task::STATUS_PENDING));
    }

    // -------------------------------------------------------
    // Task::isDeletable()
    // -------------------------------------------------------

    public function test_pending_task_is_not_deletable(): void
    {
        $this->assertFalse($this->makeTask(['status' => Task::STATUS_PENDING])->isDeletable());
    }

    public function test_in_progress_task_is_not_deletable(): void
    {
        $this->assertFalse($this->makeTask(['status' => Task::STATUS_IN_PROGRESS])->isDeletable());
    }

    public function test_done_task_is_deletable(): void
    {
        $this->assertTrue($this->makeTask(['status' => Task::STATUS_DONE])->isDeletable());
    }

    // -------------------------------------------------------
    // TaskService::create()
    // -------------------------------------------------------

    public function test_service_creates_task_with_pending_status(): void
    {
        $task = $this->service->create([
            'title'    => 'Service created task',
            'due_date' => today()->addDay()->toDateString(),
            'priority' => 'high',
        ]);

        $this->assertEquals('pending', $task->status);
        $this->assertDatabaseHas('tasks', ['title' => 'Service created task']);
    }

    // -------------------------------------------------------
    // TaskService::advanceStatus()
    // -------------------------------------------------------

    public function test_service_advances_status_correctly(): void
    {
        $task    = $this->makeTask(['status' => Task::STATUS_PENDING]);
        $updated = $this->service->advanceStatus($task, Task::STATUS_IN_PROGRESS);

        $this->assertEquals(Task::STATUS_IN_PROGRESS, $updated->status);
    }

    public function test_service_throws_on_skip_transition(): void
    {
        $task = $this->makeTask(['status' => Task::STATUS_PENDING]);

        $this->expectException(\InvalidArgumentException::class);
        $this->service->advanceStatus($task, Task::STATUS_DONE);
    }

    public function test_service_throws_when_already_done(): void
    {
        $task = $this->makeTask(['status' => Task::STATUS_DONE]);

        $this->expectException(\InvalidArgumentException::class);
        $this->service->advanceStatus($task, Task::STATUS_DONE);
    }

    // -------------------------------------------------------
    // TaskService::delete()
    // -------------------------------------------------------

    public function test_service_deletes_done_task(): void
    {
        $task = $this->makeTask(['status' => Task::STATUS_DONE]);
        $id   = $task->id;

        $this->service->delete($task);

        $this->assertDatabaseMissing('tasks', ['id' => $id]);
    }

    public function test_service_throws_when_deleting_pending_task(): void
    {
        $task = $this->makeTask(['status' => Task::STATUS_PENDING]);

        $this->expectException(\RuntimeException::class);
        $this->service->delete($task);
    }

    // -------------------------------------------------------
    // TaskService::dailyReport()
    // -------------------------------------------------------

    public function test_daily_report_has_all_priority_status_combinations(): void
    {
        $report = $this->service->dailyReport(today()->toDateString());

        foreach (Task::allPriorities() as $p) {
            foreach (Task::allStatuses() as $s) {
                $this->assertArrayHasKey($s, $report['summary'][$p]);
            }
        }
    }

    public function test_daily_report_counts_correctly(): void
    {
        $date = today()->addDays(2)->toDateString();
        $this->makeTask(['priority' => 'high', 'status' => 'pending',     'due_date' => $date]);
        $this->makeTask(['priority' => 'high', 'status' => 'pending',     'due_date' => $date]);
        $this->makeTask(['priority' => 'low',  'status' => 'in_progress', 'due_date' => $date]);

        $report = $this->service->dailyReport($date);

        $this->assertEquals(2, $report['summary']['high']['pending']);
        $this->assertEquals(1, $report['summary']['low']['in_progress']);
        $this->assertEquals(0, $report['summary']['medium']['done']);
    }

    public function test_daily_report_only_counts_matching_date(): void
    {
        $targetDate = today()->addDays(3)->toDateString();
        $otherDate  = today()->addDays(10)->toDateString();

        $this->makeTask(['due_date' => $targetDate, 'priority' => 'high']);
        $this->makeTask(['due_date' => $otherDate,  'priority' => 'high']);

        $report = $this->service->dailyReport($targetDate);

        // Only 1 task on target date, not 2
        $total = array_sum(array_map(
            fn($statuses) => array_sum($statuses),
            $report['summary']
        ));

        $this->assertEquals(1, $total);
    }
}