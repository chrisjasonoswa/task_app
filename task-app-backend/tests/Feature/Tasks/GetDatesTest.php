<?php

use App\Models\User;
use Tests\ApiEndpoints;

beforeEach(function () {
    // Create user
    $this->user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => 'test1234',
    ]);
    
    // Create 5 tasks 
    for ($i = 0; $i < 5; $i++) {
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
            'priority' => $i + 1,
            'created_at' => now()->addDays($i),
        ]);
    }

});


describe('Failed authentication scenario', function () {
    test('fails when user is unauthenticated', function () {
        $response = $this->withHeader('Authorization', 'Bearer invalid_token')
            ->getJson(ApiEndpoints::TASKS_DATES);

        $response->assertStatus(401);
    });
});


describe('Successful scenarios', function () {
    test('succeeds with authorized user and returns correct task dates', function () {
        $response = $this->actingAs($this->user)
            ->getJson(ApiEndpoints::TASKS_DATES);

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');

        $response = $this->actingAs($this->user2)
            ->getJson(ApiEndpoints::TASKS_DATES);

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    });
});

