<?php

use App\Models\User;
use Tests\ApiEndpoints;

beforeEach(function () {
    // Create a user and a task
    $this->user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('test1234'),
    ]);

    $this->task = $this->user->tasks()->create([
        'description' => 'Initial task',
        'is_completed' => false,
        'priority' => 1
    ]);

    // Create another user and a task
    $this->otherUser = User::factory()->create([
        'email' => 'test2@example.com',
        'password' => bcrypt('test1234'),
    ]);

    $this->otherUserTask = $this->otherUser->tasks()->create([
        'description' => 'Initial task',
        'is_completed' => false,
        'priority' => 1
    ]);
});


describe('Failed authentication scenario', function () {
    test('fails when user is unauthenticated', function () {
        $response = $this->withHeader('Authorization', 'Bearer invalid_token')
            ->patchJson(ApiEndpoints::TASKS . '/' . $this->task->id, [
                'description' => 'Update attempt',
            ]);

        $response->assertStatus(401);
    });
});


describe('Failed validation scenarios', function () {
    test('fails when description is longer than 255 characters', function () {
        $longDescription = str_repeat('a', 256);

        $response = $this->actingAs($this->user)
            ->patchJson(ApiEndpoints::TASKS . '/' . $this->task->id, [
                'description' => $longDescription,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('description');
    });

    test('fails when is_completed is not boolean', function () {
        $response = $this->actingAs($this->user)
            ->patchJson(ApiEndpoints::TASKS . '/' . $this->task->id, [
                'is_completed' => 'not_boolean',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('is_completed');
    });
});


describe('Other Failed scenarios', function () {
    test('fails when task does not exist', function () {
        $nonExistentTaskId = 999999;

        $response = $this->actingAs($this->user)
            ->patchJson(ApiEndpoints::TASKS . '/' . $nonExistentTaskId, [
                'description' => 'Trying to update non-existent task',
            ]);

        $response->assertStatus(404);
    });

    test('fails when user tries to update a task that belongs to another user', function () {
        $otherUserTask = $this->otherUserTask;
        $response = $this->actingAs($this->user)
            ->patchJson(ApiEndpoints::TASKS . '/' . $otherUserTask->id, [
                'description' => 'Attempted update',
            ]);

        $response->assertStatus(404);
    });
});


describe('Successful scenarios', function () {
    test('successfully updates the description', function () {
        $response = $this->actingAs($this->user)
            ->patchJson(ApiEndpoints::TASKS . '/' . $this->task->id, [
                'description' => 'Updated task description',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.description', 'Updated task description');

        $this->assertDatabaseHas('tasks', [
            'id' => $this->task->id,
            'description' => 'Updated task description',
        ]);
    });

    test('successfully updates is_completed', function () {
        $response = $this->actingAs($this->user)
            ->patchJson(ApiEndpoints::TASKS . '/' . $this->task->id, [
                'is_completed' => true,
            ]);
        
        
        $response->assertStatus(200)
            ->assertJsonPath('data.isCompleted', true);

        $this->assertDatabaseHas('tasks', [
            'id' => $this->task->id,
            'is_completed' => true,
        ]);
    });

});
