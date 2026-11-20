<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use App\Services\CacheSessionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAuthSessionTest extends TestCase
{
    use RefreshDatabase;

    protected function createAdmin(array $attributes = []): Admin
    {
        return Admin::create(array_merge([
            'name' => 'Admin User',
            'email' => 'admin@glowhaus.test',
            'password' => Hash::make('secret123'),
        ], $attributes));
    }

    public function test_admin_login_uses_session_and_not_cache_session(): void
    {
        $admin = $this->createAdmin([
            'email' => 'admin@glowhaus.test',
            'password' => Hash::make('secret123'),
        ]);

        $response = $this->post(route('admin.login.post'), [
            'email' => 'admin@glowhaus.test',
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertTrue(auth('admin')->check());
        $this->assertEquals($admin->id, auth('admin')->id());

        // Verify admin_cache_session was NOT created
        $cookies = $response->headers->getCookies();
        $adminCacheCookie = null;
        foreach ($cookies as $cookie) {
            if ($cookie->getName() === 'admin_cache_session') {
                $adminCacheCookie = $cookie;
                break;
            }
        }

        // If present, it must be expired/null (forget cookie), never a valid session token
        if ($adminCacheCookie !== null) {
            $this->assertTrue($adminCacheCookie->getExpiresTime() < time() || empty($adminCacheCookie->getValue()));
        }

        // Verify no cache_session:admin:* key exists
        $cacheService = app(CacheSessionService::class);
        $this->assertNull($cacheService->getSession('some_token', 'admin'));
    }

    public function test_admin_logout_terminates_session(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'admin')->post(route('admin.logout'));

        $response->assertRedirect(route('admin.login'));
        $this->assertFalse(auth('admin')->check());
    }

    public function test_admin_panel_does_not_authenticate_via_cache_session_cookie(): void
    {
        $admin = $this->createAdmin();

        // Even if an admin_cache_session cookie is sent
        $response = $this->withUnencryptedCookie('admin_cache_session', 'dummy_token')
            ->get(route('admin.dashboard'));

        // Admin must be redirected to login because cache-based login is disabled for admin
        $response->assertRedirect(route('admin.login'));
        $this->assertFalse(auth('admin')->check());
    }

    public function test_user_panel_still_supports_cache_session_login(): void
    {
        $user = User::factory()->create([
            'email' => 'customer@glowhaus.test',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post(route('login.post'), [
            'email' => 'customer@glowhaus.test',
            'password' => 'password123',
        ]);

        $response->assertRedirect();
        $this->assertTrue(auth('web')->check());

        // For user, user_cache_session cookie is set
        $cookies = $response->headers->getCookies();
        $userCacheCookie = null;
        foreach ($cookies as $cookie) {
            if ($cookie->getName() === 'user_cache_session') {
                $userCacheCookie = $cookie;
                break;
            }
        }

        $this->assertNotNull($userCacheCookie, 'user_cache_session cookie should be set for user login');
        $this->assertNotEmpty($userCacheCookie->getValue());
    }
}
