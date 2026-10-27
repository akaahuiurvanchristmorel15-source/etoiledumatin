<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\Order;
use App\Models\Review;
use App\Models\Setting;
use App\Models\User;
use App\Services\PdfProtectionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_manager_manages_catalog_but_not_orders_or_store_settings(): void
    {
        $manager = $this->userWithRole('manager');

        $this->actingAs($manager)->get(route('admin.books.index'))->assertOk();
        $this->actingAs($manager)->get(route('admin.categories.index'))->assertOk();
        $this->actingAs($manager)->get(route('admin.reviews.index'))->assertOk();
        $this->actingAs($manager)->get(route('admin.orders.index'))->assertForbidden();
        $this->actingAs($manager)->get(route('admin.settings.shipping'))->assertForbidden();
        $this->actingAs($manager)->get(route('admin.settings.team'))->assertForbidden();
    }

    public function test_logistics_manages_orders_but_not_catalog(): void
    {
        $logistics = $this->userWithRole('logistics');

        $this->actingAs($logistics)->get(route('admin.orders.index'))->assertOk();
        $this->actingAs($logistics)->get(route('admin.books.index'))->assertForbidden();
        $this->actingAs($logistics)->get(route('admin.dashboard'))->assertOk()->assertDontSee(route('admin.books.create'));
    }

    public function test_every_staff_member_can_open_their_settings(): void
    {
        foreach (['super_admin', 'manager', 'logistics'] as $role) {
            $this->actingAs($this->userWithRole($role))->get(route('admin.settings.profile'))->assertOk();
        }
    }

    public function test_admin_pages_render(): void
    {
        $admin = $this->userWithRole('super_admin');
        $order = Order::firstOrFail();

        foreach ([
            route('admin.dashboard'),
            route('admin.books.create'),
            route('admin.categories.create'),
            route('admin.categories.edit', Category::firstOrFail()),
            route('admin.orders.show', $order),
            route('admin.reviews.index'),
            route('admin.settings.shipping'),
            route('admin.settings.team'),
        ] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_admin_flash_messages_use_the_shared_toasts(): void
    {
        $review = Review::where('is_approved', true)->firstOrFail();

        $this->actingAs($this->userWithRole('manager'))
            ->followingRedirects()
            ->from(route('admin.reviews.index'))
            ->post(route('admin.reviews.hide', $review))
            ->assertOk()
            ->assertSee('class="toast toast-success"', false)
            ->assertSee('css/shell.css', false);
    }

    public function test_book_created_with_visibility_unchecked_stays_hidden(): void
    {
        Storage::fake('public');
        Storage::fake('local');

        $this->actingAs($this->userWithRole('manager'))
            ->post(route('admin.books.store'), $this->bookPayload([
                'cover_file' => UploadedFile::fake()->image('cover.jpg'),
                'pdf_file' => UploadedFile::fake()->create('livre.pdf', 200, 'application/pdf'),
            ]))
            ->assertRedirect(route('admin.books.index'));

        $book = Book::where('title', 'Les Soleils des indépendances')->firstOrFail();

        $this->assertFalse($book->is_active);
        $this->assertFalse($book->is_audio_available);
        $this->assertTrue($book->is_pdf_available);
        $this->assertStringStartsWith('storage/covers/', $book->cover_image);
        Storage::disk('public')->assertExists(str_replace('storage/', '', $book->cover_image));
        Storage::disk('local')->assertExists($book->pdf_path);
    }

    public function test_replacing_a_cover_deletes_the_previous_file(): void
    {
        Storage::fake('public');
        $book = Book::firstOrFail();
        $oldPath = UploadedFile::fake()->image('old.jpg')->store('covers', 'public');
        $book->update(['cover_image' => 'storage/'.$oldPath]);

        $this->actingAs($this->userWithRole('super_admin'))
            ->put(route('admin.books.update', $book), $this->bookPayload([
                'category_id' => $book->category_id,
                'is_active' => '1',
                'cover_file' => UploadedFile::fake()->image('new.jpg'),
            ]))
            ->assertRedirect(route('admin.books.edit', $book));

        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists(str_replace('storage/', '', $book->fresh()->cover_image));
        $this->assertTrue($book->fresh()->is_active);
    }

    public function test_page_count_is_read_from_the_uploaded_pdf(): void
    {
        Storage::fake('local');

        $pdf = new Fpdi;
        foreach (range(1, 7) as $page) {
            $pdf->AddPage();
        }
        $pdfPath = tempnam(sys_get_temp_dir(), 'livre');
        $pdf->Output('F', $pdfPath);

        $this->actingAs($this->userWithRole('manager'))
            ->post(route('admin.books.store'), $this->bookPayload([
                'pages_count' => 999,
                'pdf_file' => new UploadedFile($pdfPath, 'livre.pdf', 'application/pdf', null, true),
            ]))
            ->assertRedirect(route('admin.books.index'));

        $this->assertSame(7, Book::where('title', 'Les Soleils des indépendances')->firstOrFail()->pages_count);
    }

    public function test_page_count_is_read_from_a_pdf_with_compressed_object_streams(): void
    {
        $pages = collect(range(10, 21))->map(fn (int $number) => "{$number} 0 R")->implode(' ');
        $objects = "<< /Type /Catalog /Pages 2 0 R >>\n<< /Type /Pages /Kids [{$pages}] /Count 12 >>\n";
        $stream = gzcompress("1 0 2 34 \n".$objects);
        $pdfPath = tempnam(sys_get_temp_dir(), 'livre');
        file_put_contents($pdfPath, "%PDF-1.5\n3 0 obj\n<< /Type /ObjStm /N 2 /First 10 /Filter /FlateDecode /Length ".strlen($stream)." >>\nstream\n{$stream}\nendstream\nendobj\n%%EOF\n");

        $this->assertSame(12, app(PdfProtectionService::class)->countPages($pdfPath));
    }

    public function test_book_form_reports_validation_errors(): void
    {
        $this->actingAs($this->userWithRole('super_admin'))
            ->from(route('admin.books.create'))
            ->post(route('admin.books.store'), ['title' => ''])
            ->assertRedirect(route('admin.books.create'))
            ->assertSessionHasErrors(['title', 'author', 'category_id', 'description']);
    }

    public function test_categories_can_be_created_updated_and_deleted(): void
    {
        $manager = $this->userWithRole('manager');

        $this->actingAs($manager)
            ->post(route('admin.categories.store'), ['name' => 'Poésie africaine', 'display_order' => 3, 'is_active' => '1'])
            ->assertRedirect(route('admin.categories.index'));

        $category = Category::where('name', 'Poésie africaine')->firstOrFail();
        $this->assertSame('poesie-africaine', $category->slug);

        $this->actingAs($manager)
            ->put(route('admin.categories.update', $category), ['name' => 'Poésie', 'display_order' => 1])
            ->assertRedirect(route('admin.categories.index'));

        $category->refresh();
        $this->assertSame('Poésie', $category->name);
        $this->assertSame('poesie', $category->slug);
        $this->assertFalse($category->is_active);

        $this->actingAs($manager)->delete(route('admin.categories.destroy', $category))->assertRedirect(route('admin.categories.index'));
        $this->assertModelMissing($category);
    }

    public function test_category_containing_books_cannot_be_deleted(): void
    {
        $category = Book::firstOrFail()->category;

        $this->actingAs($this->userWithRole('manager'))
            ->delete(route('admin.categories.destroy', $category))
            ->assertSessionHas('error');

        $this->assertModelExists($category);
    }

    public function test_approved_review_can_be_hidden(): void
    {
        $review = Review::where('is_approved', true)->firstOrFail();

        $this->actingAs($this->userWithRole('manager'))
            ->post(route('admin.reviews.hide', $review))
            ->assertRedirect();

        $this->assertFalse($review->fresh()->is_approved);
    }

    public function test_shipping_status_update_records_shipping_date(): void
    {
        $order = Order::whereHas('shippingAddress')->firstOrFail();

        $this->actingAs($this->userWithRole('logistics'))
            ->post(route('admin.orders.update-shipping', $order), [
                'status' => 'shipped',
                'carrier_name' => 'Coursier interne',
                'tracking_number' => 'TRK-001',
            ])
            ->assertRedirect();

        $shipping = $order->shippingAddress->fresh();
        $this->assertSame('shipped', $shipping->status);
        $this->assertSame('TRK-001', $shipping->tracking_number);
        $this->assertNotNull($shipping->shipped_at);
    }

    public function test_staff_member_can_update_profile_and_password(): void
    {
        $manager = $this->userWithRole('manager');

        $this->actingAs($manager)
            ->put(route('admin.settings.profile.update'), ['name' => 'Aya Koffi', 'email' => 'aya@letoiledumatin.ci', 'phone' => '0700000000'])
            ->assertSessionHasNoErrors();

        $this->assertSame('aya@letoiledumatin.ci', $manager->fresh()->email);

        $this->actingAs($manager)
            ->put(route('admin.settings.password.update'), ['current_password' => 'mauvais', 'password' => 'NouveauMdp2026', 'password_confirmation' => 'NouveauMdp2026'])
            ->assertSessionHasErrorsIn('password', 'current_password');

        $this->actingAs($manager)
            ->put(route('admin.settings.password.update'), ['current_password' => 'Catalogue@123456', 'password' => 'NouveauMdp2026', 'password_confirmation' => 'NouveauMdp2026'])
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('NouveauMdp2026', $manager->fresh()->password));
    }

    public function test_admin_can_update_shipping_fees(): void
    {
        $zones = collect(config('shipping.zones'))
            ->map(fn (array $zone, string $key) => ['price' => $key === 'abidjan' ? 2000 : $zone['price'], 'delay' => $zone['delay']])
            ->all();

        $this->actingAs($this->userWithRole('super_admin'))
            ->put(route('admin.settings.shipping.update'), ['zones' => $zones])
            ->assertSessionHasNoErrors();

        $this->assertSame('2000', Setting::get('shipping.abidjan.price'));
    }

    public function test_admin_manages_team_members(): void
    {
        $admin = $this->userWithRole('super_admin');

        $this->actingAs($admin)
            ->post(route('admin.settings.team.store'), ['name' => 'Koné Ibrahim', 'email' => 'ibrahim@letoiledumatin.ci', 'role' => 'logistics', 'password' => 'Provisoire2026'])
            ->assertSessionHasNoErrors();

        $member = User::where('email', 'ibrahim@letoiledumatin.ci')->firstOrFail();
        $this->assertSame('logistics', $member->role);

        $this->actingAs($admin)->put(route('admin.settings.team.update', $member), ['role' => 'manager'])->assertSessionHasNoErrors();
        $this->assertSame('manager', $member->fresh()->role);

        $this->actingAs($admin)->delete(route('admin.settings.team.destroy', $member))->assertRedirect();
        $this->assertSame('customer', $member->fresh()->role);
    }

    public function test_admin_cannot_change_their_own_role_or_promote_to_super_admin(): void
    {
        $admin = $this->userWithRole('super_admin');
        $manager = $this->userWithRole('manager');

        $this->actingAs($admin)->put(route('admin.settings.team.update', $admin), ['role' => 'manager'])->assertForbidden();
        $this->actingAs($admin)->put(route('admin.settings.team.update', $manager), ['role' => 'super_admin'])->assertSessionHasErrors('role');
    }

    private function userWithRole(string $role): User
    {
        return User::where('role', $role)->firstOrFail();
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function bookPayload(array $overrides = []): array
    {
        return [
            'category_id' => Category::firstOrFail()->id,
            'title' => 'Les Soleils des indépendances',
            'author' => 'Ahmadou Kourouma',
            'language' => 'Français',
            'description' => 'Roman majeur de la littérature ivoirienne.',
            'weight_grams' => 300,
            'price_pdf' => 3000,
            'price_physical' => 7000,
            'stock_physical' => 12,
            'is_pdf_available' => '1',
            'is_physical_available' => '1',
            ...$overrides,
        ];
    }
}
