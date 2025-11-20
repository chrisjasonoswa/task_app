<?php

use App\Models\User;
use App\Models\Task;
use Tests\ApiEndpoints;

beforeEach(function () {
    // Create a user and a task
    $this->user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('test1234'),
    ]);

    $this->task = $this->user->tasks()->create([
        'description' => 'Task to delete',
        'is_completed' => false,
        'priority' => 1
    ]);

    // Create another user and their task
    $this->otherUser = User::factory()->create([
        'email' => 'test2@example.com',
        'password' => bcrypt('test1234'),
    ]);

    $this->otherUserTask = $this->otherUser->tasks()->create([
        'description' => 'Other user task',
        'is_completed' => false,
        'priority' => 1
    ]);
});

describe('Failed authentication scenario', function () {
    test('fails when user is unauthenticated', function () {
        $response = $this->withHeader('Authorization', 'Bearer invalid_token')
            ->deleteJson(ApiEndpoints::TASKS . '/' . $this->task->id);
        $response->assertStatus(401);
    });
});

describe('Other failed scenarios', function () {
    test('fails when task does not exist', function () {
        $nonExistentTaskId = 999999;

        $response = $this->actingAs($this->user)
            ->deleteJson(ApiEndpoints::TASKS . '/' . $nonExistentTaskId);

        $response->assertStatus(404);
    });

    test('fails when user tries to delete a task belonging to another user', function () {
        $response = $this->actingAs($this->user)
            ->deleteJson(ApiEndpoints::TASKS . '/' . $this->otherUserTask->id);

        $response->assertStatus(404);
    });
});

describe('Successful scenarios', function () {
    test('successfully deletes the task', function () {
        $response = $this->actingAs($this->user)
            ->deleteJson(ApiEndpoints::TASKS . '/' . $this->task->id);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('tasks', [
            'id' => $this->task->id,
        ]);
    });
});
