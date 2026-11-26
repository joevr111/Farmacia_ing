<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PwaConfigurationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_authenticated_layout_exposes_the_install_manifest_and_worker(): void
    {
        $role = Role::create(['name' => 'Administrador', 'description' => 'Rol de prueba']);
        $user = User::factory()->create([
            'role_id' => $role->id,
            'dni' => '12345678',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard.index'))
            ->assertOk()
            ->assertSee('manifest.webmanifest')
            ->assertSee('service-worker.js');
    }

    public function test_manifest_has_install_icons_and_worker_only_handles_public_static_assets(): void
    {
        $manifest = json_decode(
            file_get_contents(public_path('manifest.webmanifest')),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );
        $worker = file_get_contents(public_path('service-worker.js'));

        $this->assertSame('/', $manifest['start_url']);
        $this->assertSame('standalone', $manifest['display']);
        $this->assertSame(['192x192', '512x512'], array_column($manifest['icons'], 'sizes'));
        $this->assertSame(['image/png', 'image/png'], array_column($manifest['icons'], 'type'));
        foreach ($manifest['icons'] as $icon) {
            $this->assertFileExists(public_path(ltrim($icon['src'], '/')));
        }

        $this->assertStringContainsString('!STATIC_ASSETS.includes(requestUrl.pathname)', $worker);
        $this->assertStringNotContainsString('caches.put(', $worker);
        $this->assertStringNotContainsString('/sales', $worker);
        $this->assertStringNotContainsString('/reports', $worker);
    }
}
