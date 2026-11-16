<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use App\Notifications\SecurityAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_pages_send_the_security_headers(): void
    {
        $this->get(route('catalog.index'))
            ->assertOk()
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Cross-Origin-Resource-Policy', 'same-site')
            ->assertHeader('X-Permitted-Cross-Domain-Policies', 'none')
            ->assertHeaderMissing('X-Powered-By');
    }

    public function test_personal_pages_are_never_cached(): void
    {
        $this->actingAs($this->reader())->get(route('library.index'))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_other_sites_get_no_cross_origin_access(): void
    {
        $this->call('OPTIONS', route('search.suggestions'), [], [], [], [
            'HTTP_ORIGIN' => 'https://site-pirate.example',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'GET',
        ])->assertHeaderMissing('Access-Control-Allow-Origin');

        $this->get(route('search.suggestions', ['q' => 'livre']), ['Origin' => 'https://site-pirate.example'])
            ->assertHeaderMissing('Access-Control-Allow-Origin');
    }

    public function test_passwords_are_stored_hashed(): void
    {
        $this->post(route('register'), [
            'name' => 'Awa Koné',
            'email' => 'awa.kone@test.ci',
            'phone' => '+225 07 00 00 00 01',
            'password' => 'Lecture2026!',
            'password_confirmation' => 'Lecture2026!',
            'terms' => '1',
        ]);

        $stored = User::where('email', 'awa.kone@test.ci')->firstOrFail()->getAttributes()['password'];

        $this->assertNotSame('Lecture2026!', $stored);
        $this->assertTrue(Hash::isHashed($stored));
        $this->assertFalse(Hash::needsRehash($stored));
    }

    public function test_password_reset_links_expire_after_30_minutes(): void
    {
        $reader = $this->reader();
        $token = Password::createToken($reader);

        $this->travel(31)->minutes();

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $reader->email,
            'password' => 'NouveauSecret2026',
            'password_confirmation' => 'NouveauSecret2026',
        ])->assertSessionHasErrors('email');

        $this->assertFalse(Hash::check('NouveauSecret2026', $reader->fresh()->password));
    }

    public function test_reviews_are_displayed_as_plain_text(): void
    {
        $book = Book::active()->firstOrFail();

        Review::create([
            'book_id' => $book->id,
            'customer_name' => 'Pirate',
            'rating' => 5,
            'comment' => '<script>alert("xss")</script><img src=x onerror=alert(1)>',
            'is_approved' => true,
        ]);

        $this->get(route('catalog.show', $book->slug))
            ->assertOk()
            ->assertDontSee('<script>alert("xss")</script>', false)
            ->assertDontSee('<img src=x onerror=alert(1)>', false)
            ->assertSee('&lt;script&gt;', false);
    }

    public function test_checkout_rejects_a_malformed_phone_number(): void
    {
        $book = Book::where('is_pdf_available', true)->firstOrFail();
        $this->post(route('cart.add'), ['book_id' => $book->id, 'format' => 'pdf']);

        $this->post(route('checkout.process'), [
            'name' => 'Awa Koné',
            'email' => 'awa.kone@test.ci',
            'phone' => '<script>',
            'payment_channel' => 'paystack_momo',
        ])->assertSessionHasErrors('phone');
    }

    public function test_staff_accounts_lock_after_three_wrong_passwords(): void
    {
        for ($attempt = 0; $attempt < 3; $attempt++) {
            $this->post(route('login'), ['email' => 'catalogue@letoiledumatin.ci', 'password' => 'mauvais-mot-de-passe']);
        }

        $this->post(route('login'), ['email' => 'catalogue@letoiledumatin.ci', 'password' => 'Catalogue@123456'])
            ->assertSessionHasErrors('email');

        $this->assertStringStartsWith('Trop de tentatives de connexion.', session('errors')->first('email'));
        $this->assertGuest();
    }

    public function test_idle_staff_are_logged_out_of_the_administration(): void
    {
        $manager = User::where('role', 'manager')->firstOrFail();

        $this->actingAs($manager)->get(route('admin.dashboard'))->assertOk();

        $this->travel(31)->minutes();

        $this->actingAs($manager)->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
        $this->assertDatabaseHas('audit_logs', ['user_id' => $manager->id, 'action' => 'admin_idle_logout']);
    }

    public function test_administration_can_be_restricted_to_listed_addresses(): void
    {
        config(['admin.allowed_ips' => ['203.0.113.10', '198.51.100.0/24']]);
        $manager = User::where('role', 'manager')->firstOrFail();

        $this->actingAs($manager)->withServerVariables(['REMOTE_ADDR' => '192.0.2.50'])
            ->get(route('admin.dashboard'))
            ->assertForbidden();
        $this->assertDatabaseHas('audit_logs', ['action' => 'admin_ip_blocked', 'ip_address' => '192.0.2.50']);

        $this->actingAs($manager)->withServerVariables(['REMOTE_ADDR' => '198.51.100.7'])
            ->get(route('admin.dashboard'))
            ->assertOk();
    }

    public function test_customers_trying_the_administration_are_recorded(): void
    {
        $this->actingAs($this->reader())->get(route('admin.dashboard'))->assertForbidden();

        $this->assertDatabaseHas('audit_logs', ['user_id' => $this->reader()->id, 'action' => 'admin_access_denied']);
    }

    public function test_security_log_is_reserved_to_team_managers(): void
    {
        AuditLog::create(['action' => 'login_failed', 'details' => ['email' => 'cible@test.ci'], 'ip_address' => '192.0.2.99', 'created_at' => now()]);

        $this->actingAs(User::where('role', 'super_admin')->firstOrFail())
            ->get(route('admin.security.index'))
            ->assertOk()
            ->assertSee('Journal de sécurité')
            ->assertSee('192.0.2.99');

        $this->actingAs(User::where('role', 'logistics')->firstOrFail())
            ->get(route('admin.security.index'))
            ->assertForbidden();
    }

    public function test_super_admins_are_emailed_when_an_address_hammers_the_login(): void
    {
        Notification::fake();

        foreach (range(1, 10) as $attempt) {
            AuditLog::create(['action' => 'login_failed', 'details' => ['email' => 'cible@test.ci'], 'ip_address' => '192.0.2.77', 'created_at' => now()]);
        }

        $this->artisan('security:alert')->assertSuccessful();

        Notification::assertSentTo(
            User::where('role', 'super_admin')->get(),
            SecurityAlert::class,
            fn (SecurityAlert $alert) => in_array('10 connexions échouées depuis l\'adresse 192.0.2.77.', $alert->findings, true)
        );

        // Rien de nouveau depuis le dernier passage : pas de second e-mail
        Notification::fake();
        $this->artisan('security:alert')->assertSuccessful();
        Notification::assertNothingSent();
    }

    private function reader(): User
    {
        return User::where('email', 'lecteur@gmail.com')->firstOrFail();
    }
}
