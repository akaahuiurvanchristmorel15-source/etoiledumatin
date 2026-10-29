<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SqlInjectionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Charges classiques : contournement de condition, requête empilée, UNION et commentaire.
     *
     * @var array<int, string>
     */
    private const PAYLOADS = [
        "' OR '1'='1",
        "' OR 1=1 --",
        "'; DROP TABLE books; --",
        '" OR ""="',
        "' UNION SELECT email, password, 1, 1, 1, 1 FROM users --",
        '1 AND SLEEP(5)',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        // Les nombreuses requêtes de ces tests dépasseraient les limites de débit (10 suivis de commande par minute)
        $this->withoutMiddleware(ThrottleRequests::class);
    }

    public function test_public_search_and_filters_treat_payloads_as_plain_text(): void
    {
        $booksBefore = Book::count();

        foreach (self::PAYLOADS as $payload) {
            foreach (['search', 'author', 'language', 'category', 'price_min', 'sort'] as $parameter) {
                $this->get(route('catalog.index', [$parameter => $payload]))->assertOk();
            }

            $this->getJson(route('search.suggestions', ['q' => $payload]))
                ->assertOk()
                ->assertJsonCount(0, 'books');

            // Rejeté par la validation (trop long) ou recherché comme texte : jamais de commande trouvée
            $lookup = $this->get(route('orders.lookup', ['order_number' => $payload, 'contact' => $payload]));
            $lookup->isRedirect() ? $lookup->assertSessionHasErrors() : $lookup->assertOk()->assertViewHas('order', null);
        }

        $this->assertSame($booksBefore, Book::count());
        $this->assertTrue(Schema::hasTable('books'));
        $this->assertTrue(Schema::hasTable('users'));
    }

    public function test_payload_in_catalog_search_matches_nothing(): void
    {
        $this->get(route('catalog.index', ['search' => "' OR '1'='1"]))
            ->assertOk()
            ->assertViewHas('books', fn ($books) => $books->total() === 0);
    }

    public function test_admin_lists_treat_payloads_as_plain_text(): void
    {
        $admin = User::where('role', 'super_admin')->firstOrFail();
        $ordersBefore = Order::count();

        foreach (self::PAYLOADS as $payload) {
            $this->actingAs($admin)->get(route('admin.books.index', ['search' => $payload, 'category_id' => $payload]))->assertOk();
            $this->actingAs($admin)->get(route('admin.orders.index', ['search' => $payload, 'status' => $payload, 'shipping_status' => $payload]))->assertOk();
            $this->actingAs($admin)->get(route('admin.stock.index', ['search' => $payload, 'level' => $payload]))->assertOk();
            $this->actingAs($admin)->get(route('admin.news.index', ['search' => $payload, 'type' => $payload]))->assertOk();
        }

        $this->assertSame($ordersBefore, Order::count());
    }

    public function test_array_query_parameters_are_ignored_instead_of_crashing(): void
    {
        $admin = User::where('role', 'super_admin')->firstOrFail();

        $this->get('/catalogue?search[]=x&author[]=x&category[$ne]=x&price_min[]=1&sort[]=author')->assertOk();
        $this->get('/actualites?type[]=event')->assertOk();
        $this->actingAs($admin)->get('/admin/livres?search[]=x&category_id[]=1')->assertOk();
        $this->actingAs($admin)->get('/admin/commandes?search[]=x&status[]=paid')->assertOk();
    }

    /**
     * Garde-fou : toute requête SQL brute doit passer ses valeurs en paramètres liés (« ? »),
     * jamais par concaténation ou interpolation de variables dans la chaîne SQL.
     */
    public function test_raw_sql_never_interpolates_variables(): void
    {
        $offending = [];

        foreach (File::allFiles(app_path()) as $file) {
            preg_match_all(
                '/(?:whereRaw|orWhereRaw|havingRaw|orHavingRaw|orderByRaw|selectRaw|groupByRaw|fromRaw|DB::(?:raw|select|selectOne|statement|unprepared|insert|update|delete|affectingStatement))\(\s*(\'(?:[^\'\\\\]|\\\\.)*\'(?=\s*[,)])|[^,)]*)/',
                $file->getContents(),
                $calls
            );

            foreach ($calls[1] as $firstArgument) {
                $isPlainLiteral = preg_match('/^\'[^\']*\'$/', trim($firstArgument)) === 1;

                if (! $isPlainLiteral) {
                    $offending[] = $file->getRelativePathname().' : '.trim($firstArgument);
                }
            }
        }

        $this->assertSame([], $offending, "Requête SQL brute construite avec une variable :\n".implode("\n", $offending));
    }
}
