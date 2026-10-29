<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RolesAndTeamTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->admin = User::where('role', 'super_admin')->firstOrFail();
    }

    public function test_existing_roles_keep_their_previous_rights(): void
    {
        $manager = User::where('role', 'manager')->firstOrFail();
        $logistics = User::where('role', 'logistics')->firstOrFail();

        $this->assertTrue($manager->can('manage-catalog'));
        $this->assertTrue($manager->can('manage-reviews'));
        $this->assertFalse($manager->can('manage-orders'));
        $this->assertTrue($logistics->can('manage-orders'));
        $this->assertFalse($logistics->can('manage-team'));
        $this->assertTrue($this->admin->can('manage-team'));
    }

    public function test_store_admin_creates_a_custom_role_and_assigns_it(): void
    {
        $this->actingAs($this->admin)->get(route('admin.settings.roles'))->assertOk()->assertSee('Nouveau rôle');

        $this->actingAs($this->admin)
            ->post(route('admin.settings.roles.store'), ['name' => 'Rédaction', 'description' => 'Actualités seulement', 'permissions' => ['manage-news']])
            ->assertSessionHas('success');

        $role = Role::where('name', 'Rédaction')->firstOrFail();
        $this->assertSame(['manage-news'], $role->permissions);

        $writer = User::factory()->create(['email' => 'redaction@nsoromma.ci']);
        $writer->forceFill(['role' => $role->slug])->save();

        $this->actingAs($writer)->get(route('admin.news.index'))->assertOk();
        $this->actingAs($writer)->get(route('admin.books.index'))->assertForbidden();
        $this->actingAs($writer)->get(route('admin.orders.index'))->assertForbidden();
        $this->actingAs($writer)->get(route('admin.dashboard'))->assertOk()->assertSee('Actualités')->assertDontSee(route('admin.orders.index'), false);
    }

    public function test_role_permissions_can_be_changed_and_take_effect_immediately(): void
    {
        $logistics = User::where('role', 'logistics')->firstOrFail();
        $role = Role::where('slug', 'logistics')->firstOrFail();

        $this->actingAs($this->admin)
            ->put(route('admin.settings.roles.update', $role), ['name' => 'Logistique', 'permissions' => ['manage-orders', 'manage-stock']])
            ->assertSessionHas('success');

        $this->actingAs($logistics->fresh())->get(route('admin.stock.index'))->assertOk();
    }

    public function test_roles_in_use_and_the_super_admin_role_are_protected(): void
    {
        $manager = Role::where('slug', 'manager')->firstOrFail();
        $superAdmin = Role::where('slug', Role::SUPER_ADMIN)->firstOrFail();

        $this->actingAs($this->admin)->delete(route('admin.settings.roles.destroy', $manager))->assertSessionHas('error');
        $this->assertModelExists($manager);

        $this->actingAs($this->admin)->put(route('admin.settings.roles.update', $superAdmin), ['name' => 'X', 'permissions' => []])->assertForbidden();
        $this->actingAs($this->admin)->delete(route('admin.settings.roles.destroy', $superAdmin))->assertForbidden();

        $unused = Role::factory()->create();
        $this->actingAs($this->admin)->delete(route('admin.settings.roles.destroy', $unused))->assertSessionHas('success');
        $this->assertModelMissing($unused);
    }

    public function test_an_admin_cannot_remove_team_management_from_their_own_role(): void
    {
        $adminRole = Role::where('slug', 'admin')->firstOrFail();
        $teamLead = User::factory()->create();
        $teamLead->forceFill(['role' => 'admin'])->save();

        $this->actingAs($teamLead)
            ->put(route('admin.settings.roles.update', $adminRole), ['name' => 'Administrateur', 'permissions' => ['manage-orders']])
            ->assertSessionHas('error');

        $this->assertContains('manage-team', $adminRole->fresh()->permissions);
    }

    public function test_only_team_managers_reach_roles_and_team_pages(): void
    {
        $manager = User::where('role', 'manager')->firstOrFail();

        $this->actingAs($manager)->get(route('admin.settings.roles'))->assertForbidden();
        $this->actingAs($manager)->post(route('admin.settings.roles.store'), ['name' => 'Pirate', 'permissions' => ['manage-team']])->assertForbidden();
        $this->actingAs($manager)->get(route('admin.settings.team'))->assertForbidden();
    }

    public function test_adding_a_new_member_creates_a_ready_to_use_account(): void
    {
        Notification::fake();

        $this->actingAs($this->admin)
            ->post(route('admin.settings.team.store'), [
                'name' => 'Awa Traoré',
                'email' => '  Awa.Traore@Nsoromma.ci ',
                'role' => 'manager',
                'password' => 'Provisoire2026',
                'send_invite' => '1',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $member = User::where('email', 'awa.traore@nsoromma.ci')->firstOrFail();
        $this->assertSame('manager', $member->role);
        $this->assertNotNull($member->email_verified_at);
        Notification::assertSentTo($member, ResetPassword::class);

        // Le membre peut se connecter à l'administration avec son mot de passe provisoire
        auth()->logout();
        $this->post(route('login'), ['email' => 'awa.traore@nsoromma.ci', 'password' => 'Provisoire2026'])->assertRedirect(route('admin.dashboard'));
    }

    public function test_adding_an_existing_reader_promotes_their_account_whatever_the_capitals(): void
    {
        $reader = User::where('role', 'customer')->firstOrFail();
        $passwordHash = $reader->password;

        $this->actingAs($this->admin)
            ->post(route('admin.settings.team.store'), ['email' => mb_strtoupper($reader->email), 'role' => 'logistics'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertSame(1, User::whereRaw('lower(email) = ?', [mb_strtolower($reader->email)])->count());
        $this->assertSame('logistics', $reader->fresh()->role);
        $this->assertSame($passwordHash, $reader->fresh()->password);
    }

    public function test_adding_member_errors_are_explained_in_french(): void
    {
        $manager = User::where('role', 'manager')->firstOrFail();

        $this->actingAs($this->admin)
            ->post(route('admin.settings.team.store'), ['email' => $manager->email, 'role' => 'logistics'])
            ->assertSessionHasErrorsIn('createMember', ['email']);

        $this->actingAs($this->admin)
            ->post(route('admin.settings.team.store'), ['email' => 'nouveau@nsoromma.ci', 'role' => 'logistics'])
            ->assertSessionHasErrorsIn('createMember', ['name', 'password']);

        $this->actingAs($this->admin)
            ->post(route('admin.settings.team.store'), ['name' => 'X', 'email' => 'pas-une-adresse', 'role' => 'super_admin', 'password' => 'Provisoire2026'])
            ->assertSessionHasErrorsIn('createMember', ['email', 'role']);

        $this->assertSame('Le champ e-mail doit être une adresse e-mail valide.', session('errors')->getBag('createMember')->first('email'));
    }

    public function test_team_page_lists_custom_roles(): void
    {
        Role::factory()->create(['name' => 'Service client']);

        $this->actingAs($this->admin)
            ->get(route('admin.settings.team'))
            ->assertOk()
            ->assertSee('Service client')
            ->assertSee('Générer');
    }
}
