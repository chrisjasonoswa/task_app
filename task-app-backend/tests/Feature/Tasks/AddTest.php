<?php

use App\Models\User;
use Tests\ApiEndpoints;

beforeEach(function () {
    // Create user with no task
    $this->user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => 'test1234',
    ]);
});


describe('Failed validation scenarios', function () {
    test('fails when description is not provided', function () {
        $response = $this->actingAs($this->user)
            ->postJson(ApiEndpoints::TASKS);
        $response->assertStatus(422)
            ->assertJsonValidationErrors('description');
    });
});

describe('Failed authentication scenario', function () {
    test('fails when user is unauthenticated', function () {
        $response = $this->withHeader('Authorization', 'Bearer invalid_token')
            ->getJson(ApiEndpoints::TASKS_DATES);

        $response->assertStatus(401);
    });
});


describe('Successful scenarios', function () {
    test('successfully creates a task with description', function () {
        $response = $this->actingAs($this->user)
            ->postJson(ApiEndpoints::TASKS, [
                'description' => 'My new task'
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.description', 'My new task');

        $this->assertDatabaseHas('tasks', [
            'description' => 'My new task',
            'user_id' => $this->user->id
        ]);
    });
});






