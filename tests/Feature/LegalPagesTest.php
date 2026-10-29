<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_legal_pages_are_public_and_linked_from_every_page(): void
    {
        foreach (['legal.notice' => 'Mentions légales', 'legal.privacy' => 'Politique de confidentialité', 'legal.terms' => 'Conditions générales de vente'] as $route => $title) {
            $this->get(route($route))->assertOk()->assertSee($title);
        }

        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('legal.notice'), false)
            ->assertSee(route('legal.privacy'), false)
            ->assertSee(route('legal.terms'), false);

        $this->get(route('register'))->assertOk()->assertSee(route('legal.privacy'), false);
    }

    public function test_missing_company_details_are_flagged_until_filled_in_admin(): void
    {
        $this->get(route('legal.notice'))->assertSee('[à compléter : n° rccm]');

        Setting::put('content.legal_rccm', 'CI-ABJ-2026-B-12345');
        Setting::put('content.legal_artci', 'Récépissé n° 2026-001');

        $this->get(route('legal.notice'))
            ->assertSee('CI-ABJ-2026-B-12345')
            ->assertSee('Récépissé n° 2026-001')
            ->assertDontSee('[à compléter : n° rccm]');
    }

    public function test_terms_list_the_current_shipping_zones(): void
    {
        $response = $this->get(route('legal.terms'))->assertOk();

        foreach (config('shipping.zones') as $zone) {
            $response->assertSee($zone['name'])->assertSee($zone['delay']);
        }
    }

    public function test_legal_pages_are_in_the_sitemap(): void
    {
        $this->get(route('seo.sitemap'))
            ->assertOk()
            ->assertSee(route('legal.privacy'), false)
            ->assertSee(route('legal.terms'), false);
    }

    public function test_security_log_is_pruned_after_the_announced_retention(): void
    {
        $old = AuditLog::create(['action' => 'login', 'ip_address' => '10.0.0.1', 'created_at' => now()->subMonths(AuditLog::RETENTION_MONTHS)->subDay()]);
        $recent = AuditLog::create(['action' => 'login', 'ip_address' => '10.0.0.2', 'created_at' => now()->subMonths(AuditLog::RETENTION_MONTHS - 1)]);

        $this->artisan('model:prune', ['--model' => [AuditLog::class]])->assertSuccessful();

        $this->assertModelMissing($old);
        $this->assertModelExists($recent);
    }
}
