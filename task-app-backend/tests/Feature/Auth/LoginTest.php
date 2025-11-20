<?php

use App\Models\User;
use Tests\ApiEndpoints;



describe('Failed validation scenarios', function () {
    test('fails with invalid email format', function () {
        $response = $this->postJson(ApiEndpoints::AUTH_LOGIN, [
            'email' => 'invalid_email_format.com',
            'password' => 'test1234',
        ]);

        $response->assertStatus(422);
    });

    test('fails with invalid password format', function () {
        $response = $this->postJson(ApiEndpoints::AUTH_LOGIN, [
            'email' => 'test@example.com',
            'password' => 't1',
        ]);

        $response->assertStatus(422);
    });
});

describe('Other Failed scenarios', function () {
    test('fails with non-existent email', function () {
        $response = $this->postJson(ApiEndpoints::AUTH_LOGIN, [
            'email' => 'invalid@example.com',
            'password' => 'test1234',
        ]);

        $response->assertStatus(401);
    });
});



describe('Successful scenarios', function () {
    test('succeeds with valid credentials', function () {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'test1234',
        ]);

        $response = $this->postJson(ApiEndpoints::AUTH_LOGIN, [
            'email' => 'test@example.com',
            'password' => 'test1234',
        ]);

        $response->assertStatus(200);
    });
});
