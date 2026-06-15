<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AccountSettingsTest extends TestCase
{
    use RefreshDatabase;

    public static function settingsPages(): array
    {
        return [
            'account' => ['/settings/account'],
            'notifications' => ['/settings/notifications'],
            'connections' => ['/settings/connections'],
        ];
    }

    #[DataProvider('settingsPages')]
    public function test_settings_page_requires_authentication(string $url): void
    {
        $this->get($url)->assertRedirect('/login');
    }

    #[DataProvider('settingsPages')]
    public function test_settings_page_renders_for_authenticated_user(string $url): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get($url)->assertOk();
    }

    public function test_account_page_shows_current_user_details(): void
    {
        $user = User::factory()->create([
            'name' => 'janedoe',
            'email' => 'jane@example.com',
        ]);

        $this->actingAs($user)
            ->get('/settings/account')
            ->assertSee('value="janedoe"', false)
            ->assertSee('value="jane@example.com"', false);
    }
}
