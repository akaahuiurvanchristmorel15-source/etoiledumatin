<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceModeTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->admin = User::where('role', 'super_admin')->firstOrFail();
    }

    public function test_admin_can_enable_maintenance_from_settings(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.settings.maintenance'))
            ->assertOk()
            ->assertSee('Activer la maintenance');

        $this->actingAs($this->admin)
            ->put(route('admin.settings.maintenance.update'), ['enabled' => '1', 'message' => 'Retour vers 14 h.'])
            ->assertSessionHas('success');

        $this->assertSame('1', Setting::get('maintenance.enabled'));
        $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertOk()->assertSee('Mode maintenance actif');
    }

    public function test_visitors_see_the_coming_soon_page_during_maintenance(): void
    {
        Setting::put('maintenance.enabled', '1');
        Setting::put('maintenance.message', 'Retour vers 14 h.');

        foreach ([route('home'), route('catalog.index'), route('cart.index'), route('login'), route('register')] as $url) {
            $this->get($url)
                ->assertStatus(503)
                ->assertHeader('Retry-After')
                ->assertSee('Le site sera bientôt disponible')
                ->assertSee('Retour vers 14 h.');
        }

        $this->post(route('cart.add'), ['book_id' => 1, 'format' => 'pdf'])->assertStatus(503);

        $reader = User::where('role', 'customer')->firstOrFail();
        $this->actingAs($reader)->get(route('library.index'))->assertStatus(503);
    }

    public function test_admin_login_stays_open_during_maintenance(): void
    {
        Setting::put('maintenance.enabled', '1');
        $this->admin->forceFill(['password' => 'Provisoire2026'])->save();

        $this->get(route('admin.login'))->assertOk();
        $this->get(route('password.request'))->assertOk();

        $this->post(route('login'), ['email' => $this->admin->email, 'password' => 'Provisoire2026'])
            ->assertRedirect(route('admin.dashboard'));

        // L'équipe connectée continue de voir le site pour vérifier les modifications
        $this->get(route('home'))->assertOk();
    }

    public function test_paystack_webhook_is_not_blocked(): void
    {
        Setting::put('maintenance.enabled', '1');

        $this->assertNotSame(503, $this->postJson(route('paystack.webhook'), [])->status());
    }

    public function test_disabling_maintenance_reopens_the_site(): void
    {
        Setting::put('maintenance.enabled', '1');

        $this->actingAs($this->admin)
            ->put(route('admin.settings.maintenance.update'), ['enabled' => '0'])
            ->assertSessionHas('success');

        auth()->logout();
        $this->get(route('home'))->assertOk();
    }

    public function test_only_store_managers_can_toggle_maintenance(): void
    {
        $logistics = User::where('role', 'logistics')->firstOrFail();

        $this->actingAs($logistics)->get(route('admin.settings.maintenance'))->assertForbidden();
        $this->actingAs($logistics)->put(route('admin.settings.maintenance.update'), ['enabled' => '1'])->assertForbidden();
        $this->assertNull(Setting::get('maintenance.enabled'));
    }
}
