<?php

use App\Models\User;
use Tests\ApiEndpoints;
use Carbon\Carbon;

beforeEach(function () {
    // Create user
    $this->user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => 'test1234',
    ]);
    
    // Create 5 tasks for today
    for ($i = 0; $i < 5; $i++) {
        $this->user->tasks()->create([
            'description' => 'Task ' . ($i + 1),
            'priority' => $i + 1
        ]);
    }
    // Create 5 tasks for tomorrow
    for ($i = 5; $i < 10; $i++) {
        $this->user->tasks()->create([
            'description' => 'Task ' . ($i + 1),
            'priority' => $i + 1,
            'created_at' => now()->addDays($i),
        ]);
    }

    // Create user2
    $this->user2 = User::factory()->create([
        'email' => 'user2@example.com',
        'password' => 'test1234',
    ]);

    // Create 2 tasks for user 2
    for ($i = 0; $i < 2; $i++) {
        $this->user2->tasks()->create([
            'description' => 'Task ' . ($i + 1),
            'priority' => $i + 1
        ]);
    }

});


describe('Failed authentication scenario', function () {
    test('fails when user is unauthenticated', function () {
        $response = $this->withHeader('Authorization', 'Bearer invalid_token')
            ->getJson(ApiEndpoints::TASKS);
        $response->assertStatus(401);
    });
});


describe('Failed validation scenarios', function () {
    test('fails when invalid date format is provided', function () {
        $response = $this->actingAs($this->user)
            ->getJson(ApiEndpoints::TASKS . "?date=2025-24-32");
        $response->assertStatus(422);
    });

});

describe('Successful scenarios', function () {
    test('succeeds and filters tasks by a valid date', function () {
        $today = Carbon::now()->format('Y-m-d');
        $response = $this->actingAs($this->user)
            ->getJson(ApiEndpoints::TASKS . "?date=$today");
        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    });

    test('succeeds and returns all tasks when date is not provided', function () {
        $response = $this->actingAs($this->user)
            ->getJson(ApiEndpoints::TASKS);
        $response->assertStatus(200)
            ->assertJsonCount(10, 'data');
    });

    test('succeeds and filter tasks by valid date and search', function () {
        $today = Carbon::now()->format('Y-m-d');
        $response = $this->actingAs($this->user2)
            ->getJson(ApiEndpoints::TASKS . "?date=$today&search=Task 1");
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    });
});



