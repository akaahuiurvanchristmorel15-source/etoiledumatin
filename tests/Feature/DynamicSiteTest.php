<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DynamicSiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_pages_expose_live_regions_and_cart_badges_to_update_in_place(): void
    {
        $book = Book::active()->firstOrFail();

        $this->get(route('catalog.index'))
            ->assertOk()
            ->assertSee('data-live="catalog"', false)
            ->assertSee('data-live-sync="cart-desktop"', false)
            ->assertSee('data-toast-stack', false)
            ->assertSee('js/live.js', false);

        $this->get(route('catalog.show', $book->slug))
            ->assertOk()
            ->assertSee('data-live="book"', false)
            ->assertSee('data-live-stay data-fly-to-cart', false)
            ->assertSee('js/motion.js', false);

        $this->get(route('cart.index'))->assertOk()->assertSee('data-live="cart"', false);
    }

    public function test_pages_offer_a_day_and_night_toggle_applied_before_display(): void
    {
        $html = $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringContainsString('data-theme-toggle', $html);
        $this->assertStringContainsString('css/theme-dark.css', $html);
        $this->assertStringContainsString('<meta name="color-scheme" content="light dark">', $html);

        // Le thème est choisi dans <head>, avant le rendu de la page, pour éviter un flash clair
        $this->assertLessThan(strpos($html, '<body'), strpos($html, 'window.toggleTheme'));
    }

    public function test_store_admin_edits_announcement_footer_and_home_titles(): void
    {
        $admin = User::where('role', 'super_admin')->firstOrFail();

        $this->actingAs($admin)->get(route('admin.settings.content'))->assertOk()->assertSee('Texte du bandeau');

        $this->actingAs($admin)
            ->put(route('admin.settings.content.update'), [
                'announcement_text' => 'Livraison offerte à Abidjan ce week-end',
                'announcement_link' => 'https://nsoromma.ci/catalogue',
                'home_collections_title' => 'Nos étagères',
                'home_news_title' => 'Agenda culturel',
                'support_whatsapp' => '2250102030405',
                'contact_email' => 'contact@nsoromma.ci',
                'instagram_url' => 'https://instagram.com/nsoromma',
            ])
            ->assertSessionHas('success');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Livraison offerte à Abidjan ce week-end')
            ->assertSee('Nos étagères')
            ->assertSee('https://wa.me/2250102030405', false)
            ->assertSee('mailto:contact@nsoromma.ci', false)
            ->assertSee('https://instagram.com/nsoromma', false);
    }

    public function test_content_settings_are_validated_and_restricted_to_store_admins(): void
    {
        $admin = User::where('role', 'super_admin')->firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.settings.content.update'), [
                'home_collections_title' => '',
                'home_news_title' => 'Actualités',
                'support_whatsapp' => '+225 07',
                'facebook_url' => 'javascript:alert(1)',
            ])
            ->assertSessionHasErrors(['home_collections_title', 'support_whatsapp', 'facebook_url']);

        $this->actingAs(User::where('role', 'manager')->firstOrFail())
            ->get(route('admin.settings.content'))
            ->assertForbidden();
    }

    public function test_announcement_is_hidden_when_empty(): void
    {
        Setting::put('content.announcement_text', '');

        $this->get(route('home'))->assertOk()->assertDontSee('site-announcement"', false);
    }

    public function test_dashboard_refreshes_itself(): void
    {
        $this->actingAs(User::where('role', 'super_admin')->firstOrFail())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('data-live="dashboard" data-live-poll="30"', false)
            ->assertSee('Nouvelle commande payée');
    }

    public function test_new_paid_orders_are_reported_to_order_managers_only(): void
    {
        $order = Order::where('payment_status', 'paid')->firstOrFail();
        $order->update(['paid_at' => now()->subSeconds(10)]);
        $logistics = User::where('role', 'logistics')->firstOrFail();

        $this->actingAs($logistics)
            ->getJson(route('admin.notifications.orders', ['since' => now()->subMinute()->toIso8601String()]))
            ->assertOk()
            ->assertJsonPath('orders.0.number', $order->order_number)
            ->assertJsonStructure(['checked_at', 'orders' => [['number', 'customer', 'total', 'url']]]);

        $this->actingAs($logistics)
            ->getJson(route('admin.notifications.orders', ['since' => now()->toIso8601String()]))
            ->assertOk()
            ->assertJsonCount(0, 'orders');

        $this->actingAs(User::where('role', 'manager')->firstOrFail())
            ->getJson(route('admin.notifications.orders', ['since' => now()->subMinute()->toIso8601String()]))
            ->assertForbidden();
    }
}
