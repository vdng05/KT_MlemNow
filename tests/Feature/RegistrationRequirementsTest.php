<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationRequirementsTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_rejects_values_outside_the_specification(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'An 2',
            'email' => 'an@example.net',
            'phone' => '091234567',
            'password' => 'weakpass',
            'password_confirmation' => 'weakpass',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'phone', 'password']);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_registration_accepts_values_matching_the_specification(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Nguyễn Văn An',
            'email' => 'an@example.com',
            'phone' => '0912345678',
            'password' => 'Strong!234',
            'password_confirmation' => 'Strong!234',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertDatabaseHas('users', [
            'name' => 'Nguyễn Văn An',
            'email' => 'an@example.com',
            'phone' => '0912345678',
        ]);
        $this->assertAuthenticatedAs(User::first());
    }
}