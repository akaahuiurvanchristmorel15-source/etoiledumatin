<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class AccountSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_registration_creates_an_unverified_customer_and_sends_a_confirmation_email(): void
    {
        Notification::fake();

        $this->post(route('register'), $this->registrationData())
            ->assertRedirect(route('verification.notice'));

        $user = User::where('email', 'nouveau.lecteur@test.ci')->firstOrFail();
        $this->assertSame('customer', $user->role);
        $this->assertFalse($user->hasVerifiedEmail());
        $this->assertAuthenticatedAs($user);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_registration_cannot_grant_an_admin_role(): void
    {
        $this->post(route('register'), $this->registrationData(['role' => 'super_admin']));

        $this->assertSame('customer', User::where('email', 'nouveau.lecteur@test.ci')->firstOrFail()->role);
    }

    public function test_registration_normalises_the_email_address(): void
    {
        $this->post(route('register'), $this->registrationData(['email' => '  Nouveau.Lecteur@TEST.ci ']));

        $this->assertDatabaseHas('users', ['email' => 'nouveau.lecteur@test.ci']);
    }

    public function test_registration_rejects_bots_filling_the_hidden_field(): void
    {
        $this->post(route('register'), $this->registrationData(['website' => 'https://spam.example']))
            ->assertSessionHasErrors('website');

        $this->assertDatabaseMissing('users', ['email' => 'nouveau.lecteur@test.ci']);
    }

    public function test_registration_rejects_weak_passwords_and_requires_terms(): void
    {
        $this->post(route('register'), $this->registrationData(['password' => 'motdepasse', 'password_confirmation' => 'motdepasse', 'terms' => null]))
            ->assertSessionHasErrors(['password', 'terms']);

        $this->assertGuest();
    }

    public function test_registration_validates_the_phone_number(): void
    {
        $this->post(route('register'), $this->registrationData(['phone' => 'appelle-moi']))
            ->assertSessionHasErrors('phone');
    }

    public function test_login_is_locked_after_too_many_failed_attempts(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('login'), ['email' => 'lecteur@gmail.com', 'password' => 'mauvais-mot-de-passe']);
        }

        $this->post(route('login'), ['email' => 'lecteur@gmail.com', 'password' => 'Lecteur@123456'])
            ->assertSessionHasErrors('email');

        $this->assertStringStartsWith('Trop de tentatives de connexion.', session('errors')->first('email'));

        $this->assertGuest();
        $this->assertDatabaseHas('audit_logs', ['action' => 'login_locked']);
    }

    public function test_login_is_case_insensitive_on_the_email(): void
    {
        $this->post(route('login'), ['email' => 'LECTEUR@gmail.com', 'password' => 'Lecteur@123456'])
            ->assertRedirect(route('library.index'));

        $this->assertAuthenticated();
    }

    public function test_signed_in_users_are_redirected_away_from_login_and_register(): void
    {
        $customer = User::where('role', 'customer')->firstOrFail();

        $this->actingAs($customer)->get(route('login'))->assertRedirect(route('library.index'));
        $this->actingAs($customer)->get(route('register'))->assertRedirect(route('library.index'));
    }

    public function test_email_verification_link_confirms_the_address(): void
    {
        $user = User::factory()->unverified()->create();
        $url = URL::temporarySignedRoute('verification.verify', now()->addHour(), ['id' => $user->id, 'hash' => sha1($user->email)]);

        $this->actingAs($user)->get($url)->assertRedirect(route('library.index'));

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_password_reset_does_not_reveal_whether_an_account_exists(): void
    {
        Notification::fake();
        $customer = User::where('role', 'customer')->firstOrFail();

        $this->post(route('password.email'), ['email' => $customer->email])->assertRedirect();
        $messageForKnownEmail = session('success');

        $this->post(route('password.email'), ['email' => 'inconnu@test.ci'])->assertRedirect();

        $this->assertNotNull($messageForKnownEmail);
        $this->assertSame($messageForKnownEmail, session('success'));
        Notification::assertSentTo($customer, ResetPassword::class);
    }

    public function test_password_can_be_reset_with_a_valid_token(): void
    {
        $customer = User::where('role', 'customer')->firstOrFail();
        $token = Password::createToken($customer);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $customer->email,
            'password' => 'NouveauSecret2026',
            'password_confirmation' => 'NouveauSecret2026',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('NouveauSecret2026', $customer->fresh()->password));
    }

    public function test_password_reset_with_an_invalid_token_fails(): void
    {
        $customer = User::where('role', 'customer')->firstOrFail();

        $this->post(route('password.update'), [
            'token' => 'faux-jeton',
            'email' => $customer->email,
            'password' => 'NouveauSecret2026',
            'password_confirmation' => 'NouveauSecret2026',
        ])->assertSessionHasErrors('email');
    }

    public function test_google_login_is_hidden_and_disabled_without_credentials(): void
    {
        config(['services.google.client_id' => null, 'services.google.client_secret' => null]);

        $this->get(route('login'))->assertDontSee('Continuer avec Google');
        $this->get(route('auth.google'))->assertNotFound();
    }

    public function test_google_login_creates_a_verified_customer_account(): void
    {
        $this->enableGoogle();
        $this->fakeGoogleUser('google-123', 'Awa.Traore@gmail.com', 'Awa Traoré');

        $this->get(route('login'))->assertSee('Continuer avec Google');
        $this->get(route('auth.google.callback'))->assertRedirect(route('library.index'));

        $user = User::where('email', 'awa.traore@gmail.com')->firstOrFail();
        $this->assertSame('google-123', $user->google_id);
        $this->assertSame('customer', $user->role);
        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertAuthenticatedAs($user);
    }

    public function test_google_login_links_an_existing_account_with_the_same_email(): void
    {
        $this->enableGoogle();
        $customer = User::where('role', 'customer')->firstOrFail();
        $this->fakeGoogleUser('google-456', $customer->email, $customer->name);

        $this->get(route('auth.google.callback'))->assertRedirect(route('library.index'));

        $this->assertSame('google-456', $customer->fresh()->google_id);
        $this->assertSame(1, User::where('email', $customer->email)->count());
    }

    public function test_google_login_refuses_unverified_google_emails(): void
    {
        $this->enableGoogle();
        $this->fakeGoogleUser('google-789', 'pas.verifie@gmail.com', 'Pas Vérifié', verified: false);

        $this->get(route('auth.google.callback'))->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'pas.verifie@gmail.com']);
    }

    public function test_responses_carry_security_headers(): void
    {
        $this->get(route('home'))
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_checkout_is_refused_when_stock_dropped_after_adding_to_cart(): void
    {
        $book = Book::active()->firstOrFail();
        $book->update(['is_physical_available' => true, 'stock_physical' => 3]);
        $this->post(route('cart.add'), ['book_id' => $book->id, 'format' => 'physical', 'quantity' => 3]);
        $book->update(['stock_physical' => 1]);

        $this->post(route('checkout.process'), [
            'name' => 'Koffi Yao',
            'email' => 'koffi.yao@test.ci',
            'phone' => '+225 07 00 00 00 01',
            'delivery_zone' => 'abidjan',
            'address_line' => 'Cocody',
            'city' => 'Abidjan',
            'country' => "Côte d'Ivoire",
            'payment_channel' => 'paystack_momo',
        ])->assertRedirect(route('cart.index'))->assertSessionHas('error');

        $this->assertDatabaseMissing('orders', ['customer_email' => 'koffi.yao@test.ci']);
    }

    public function test_reviews_reject_bots_and_are_rate_limited(): void
    {
        $book = Book::active()->firstOrFail();
        $review = ['customer_name' => 'Lecteur', 'rating' => 5, 'comment' => 'Un livre remarquable.'];

        $this->post(route('books.review', $book), [...$review, 'website' => 'spam'])->assertSessionHasErrors('website');

        for ($attempt = 0; $attempt < 2; $attempt++) {
            $this->post(route('books.review', $book), $review)->assertRedirect();
        }

        $this->post(route('books.review', $book), $review)->assertStatus(429);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function registrationData(array $overrides = []): array
    {
        return [
            'name' => 'Nouveau Lecteur',
            'email' => 'nouveau.lecteur@test.ci',
            'phone' => '+225 07 12 34 56 78',
            'password' => 'Lecture2026',
            'password_confirmation' => 'Lecture2026',
            'terms' => '1',
            ...$overrides,
        ];
    }

    private function enableGoogle(): void
    {
        config(['services.google.client_id' => 'test-client-id', 'services.google.client_secret' => 'test-client-secret']);
    }

    private function fakeGoogleUser(string $id, string $email, string $name, bool $verified = true): void
    {
        $googleUser = (new SocialiteUser)
            ->setRaw(['sub' => $id, 'email' => $email, 'email_verified' => $verified])
            ->map(['id' => $id, 'name' => $name, 'email' => $email]);

        $provider = Mockery::mock();
        $provider->shouldReceive('user')->andReturn($googleUser);
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }
}
