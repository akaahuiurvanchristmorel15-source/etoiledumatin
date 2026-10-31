<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminTwoFactorController;
use App\Models\User;
use App\Notifications\AdminLoginCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminTwoFactorTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        config(['auth.admin_two_factor' => true]);
        Notification::fake();

        $this->admin = User::where('role', 'super_admin')->firstOrFail();
        $this->admin->forceFill(['password' => 'Provisoire2026'])->save();
    }

    public function test_staff_password_alone_does_not_open_the_admin(): void
    {
        $this->post(route('login'), ['email' => $this->admin->email, 'password' => 'Provisoire2026'])
            ->assertRedirect(route('admin.two-factor'));

        $this->assertGuest();
        Notification::assertSentTo($this->admin, AdminLoginCode::class);

        $this->get(route('admin.two-factor'))->assertOk()->assertSee('Code de connexion');
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
    }

    public function test_the_emailed_code_completes_the_login(): void
    {
        $code = $this->loginAndCatchCode();

        $this->post(route('admin.two-factor.verify'), ['code' => substr($code, 0, 3).' '.substr($code, 3)])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($this->admin);
    }

    public function test_wrong_codes_are_refused_then_the_request_is_cancelled(): void
    {
        $code = $this->loginAndCatchCode();
        $wrongCode = $code === '000000' ? '111111' : '000000';

        for ($attempt = 1; $attempt < AdminTwoFactorController::MAX_ATTEMPTS; $attempt++) {
            $this->post(route('admin.two-factor.verify'), ['code' => $wrongCode])->assertSessionHasErrors('code');
        }

        $this->post(route('admin.two-factor.verify'), ['code' => $wrongCode])->assertRedirect(route('admin.login'));

        // Même le bon code ne sert plus : il faut se reconnecter
        $this->post(route('admin.two-factor.verify'), ['code' => $code])->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }

    public function test_an_expired_code_is_refused(): void
    {
        $code = $this->loginAndCatchCode();

        $this->travel(AdminTwoFactorController::CODE_VALID_MINUTES + 1)->minutes();

        $this->post(route('admin.two-factor.verify'), ['code' => $code])->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_a_trusted_device_skips_the_code_until_the_password_changes(): void
    {
        $code = $this->loginAndCatchCode();
        $response = $this->post(route('admin.two-factor.verify'), ['code' => $code, 'trust_device' => '1']);
        $deviceCookie = $response->getCookie(AdminTwoFactorController::TRUSTED_DEVICE_COOKIE)->getValue();

        auth()->logout();

        $this->withCookie(AdminTwoFactorController::TRUSTED_DEVICE_COOKIE, $deviceCookie)
            ->post(route('login'), ['email' => $this->admin->email, 'password' => 'Provisoire2026'])
            ->assertRedirect(route('admin.dashboard'));

        auth()->logout();
        $this->admin->forceFill(['password' => 'Nouveau2026Mdp'])->save();

        $this->withCookie(AdminTwoFactorController::TRUSTED_DEVICE_COOKIE, $deviceCookie)
            ->post(route('login'), ['email' => $this->admin->email, 'password' => 'Nouveau2026Mdp'])
            ->assertRedirect(route('admin.two-factor'));
    }

    public function test_readers_log_in_without_a_code(): void
    {
        $reader = User::where('role', 'customer')->firstOrFail();
        $reader->forceFill(['password' => 'Lecteur2026'])->save();

        $this->post(route('login'), ['email' => $reader->email, 'password' => 'Lecteur2026'])
            ->assertRedirect(route('library.index'));

        $this->assertAuthenticatedAs($reader);
        Notification::assertNothingSent();
    }

    public function test_the_code_page_requires_a_pending_login(): void
    {
        $this->get(route('admin.two-factor'))->assertRedirect(route('admin.login'));
    }

    private function loginAndCatchCode(): string
    {
        $this->post(route('login'), ['email' => $this->admin->email, 'password' => 'Provisoire2026']);

        $code = '';
        Notification::assertSentTo($this->admin, AdminLoginCode::class, function (AdminLoginCode $notification) use (&$code): bool {
            $code = $notification->code;

            return true;
        });

        return $code;
    }
}
