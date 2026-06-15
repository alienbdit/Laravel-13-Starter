<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_page_requires_authentication(): void
    {
        $this->get('/settings/security')->assertRedirect('/login');
    }

    public function test_security_page_renders_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/settings/security')->assertOk();
    }

    public function test_security_page_shows_2fa_disabled_by_default(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/settings/security')
            ->assertSee('Disabled')
            ->assertSee('Two-Factor Authentication');
    }
}
