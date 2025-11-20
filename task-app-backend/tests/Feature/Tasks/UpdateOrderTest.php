<?php

use App\Models\User;
use App\Models\Task;
use Tests\ApiEndpoints;

beforeEach(function () {
    // Create a user
    $this->user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('test1234'),
    ]);

    $this->date = now()->format('Y-m-d');

    // Create tasks for the user with incremental priority
    $this->tasks = collect();
    for ($i = 1; $i <= 3; $i++) {
        $this->tasks->push(Task::factory()->for($this->user)->create([
            'created_at' => $this->date,
            'description' => 'Task ' . $i,
            'priority' => $i,
        ]));
    }

    // Create another user and tasks
    $this->otherUser = User::factory()->create();

    $this->otherUserTasks = collect();
    for ($i = 1; $i <= 2; $i++) {
        $this->otherUserTasks->push(Task::factory()->for($this->otherUser)->create([
            'created_at' => $this->date,
            'description' => 'Task ' . $i,
            'priority' => $i,
        ]));
    }
});

describe('Failed authentication', function () {
    test('fails when unauthenticated', function () {
        $response = $this->withHeader('Authorization', 'Bearer invalid_token')
            ->patchJson(ApiEndpoints::TASKS_ORDER, [
                'ordered_ids' => $this->tasks->pluck('id')->toArray(),
                'date' => $this->date,
            ]);

        $response->assertStatus(401);
    });
});

describe('Failed validation scenarios', function () {
    test('fails when ordered_ids is missing', function () {
        $response = $this->actingAs($this->user)
            ->patchJson(ApiEndpoints::TASKS_ORDER, [
                'date' => $this->date,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('ordered_ids');
    });

    test('fails when ordered_ids is not an array', function () {
        $response = $this->actingAs($this->user)
            ->patchJson(ApiEndpoints::TASKS_ORDER, [
                'ordered_ids' => 'not-an-array',
                'date' => $this->date,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('ordered_ids');
    });

    test('fails when ordered_ids contains invalid task IDs', function () {
        $response = $this->actingAs($this->user)
            ->patchJson(ApiEndpoints::TASKS_ORDER, [
                'ordered_ids' => [999, 1000],
                'date' => $this->date,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('ordered_ids.0');
    });

    test('fails when date is missing or invalid', function () {
        $response = $this->actingAs($this->user)
            ->patchJson(ApiEndpoints::TASKS_ORDER, [
                'ordered_ids' => $this->tasks->pluck('id')->toArray(),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('date');

        $response = $this->actingAs($this->user)
            ->patchJson(ApiEndpoints::TASKS_ORDER, [
                'ordered_ids' => $this->tasks->pluck('id')->toArray(),
                'date' => 'invalid-date',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('date');
    });
});

describe('Successful scenarios', function () {
    test('successfully reorders tasks', function () {
        $orderedIds = $this->tasks->reverse()->pluck('id')->toArray();

        $response = $this->actingAs($this->user)
            ->patchJson(ApiEndpoints::TASKS_ORDER, [
                'ordered_ids' => $orderedIds,
                'date' => $this->date,
            ]);

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');

        // Verify priorities in DB
        foreach ($orderedIds as $index => $taskId) {
            $this->assertDatabaseHas('tasks', [
                'id' => $taskId,
                'priority' => $index + 1,
            ]);
        }
    });

    test('tasks not in ordered_ids are appended at the end', function () {
        $partialOrder = [$this->tasks[0]->id];

        $response = $this->actingAs($this->user)
            ->patchJson(ApiEndpoints::TASKS_ORDER, [
                'ordered_ids' => $partialOrder,
                'date' => $this->date,
            ]);

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');

        // First task should have priority 1
        $this->assertDatabaseHas('tasks', [
            'id' => $partialOrder[0],
            'priority' => 1,
        ]);

        // The rest get priorities 2 and 3
        $remaining = $this->tasks->pluck('id')->diff($partialOrder)->values();
        foreach ($remaining as $index => $taskId) {
            $this->assertDatabaseHas('tasks', [
                'id' => $taskId,
                'priority' => $index + 2,
            ]);
        }
    });
});
