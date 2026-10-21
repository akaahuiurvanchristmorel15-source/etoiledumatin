<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Book;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PurchasedDownload;
use App\Models\Review;
use App\Models\ShippingAddress;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Création des Utilisateurs
        $admin = User::forceCreate([
            'name' => 'Directeur NSOROMMA',
            'email' => 'admin@letoiledumatin.ci',
            'password' => Hash::make('Admin@123456'),
            'role' => 'super_admin',
            'email_verified_at' => now(),
            'phone' => '+225 07 00 00 00 01',
            'address' => 'Plateau, Avenue Chardy',
            'city' => 'Abidjan',
            'country' => 'Côte d\'Ivoire',
        ]);

        $manager = User::forceCreate([
            'name' => 'Responsable Éditorial',
            'email' => 'catalogue@letoiledumatin.ci',
            'password' => Hash::make('Catalogue@123456'),
            'role' => 'manager',
            'email_verified_at' => now(),
            'phone' => '+225 07 00 00 00 02',
            'address' => 'Cocody Angré',
            'city' => 'Abidjan',
            'country' => 'Côte d\'Ivoire',
        ]);

        $logistics = User::forceCreate([
            'name' => 'Agent Logistique & Expéditions',
            'email' => 'logistique@letoiledumatin.ci',
            'password' => Hash::make('Logistique@123456'),
            'role' => 'logistics',
            'email_verified_at' => now(),
            'phone' => '+225 07 00 00 00 03',
            'address' => 'Treichville Zone 3',
            'city' => 'Abidjan',
            'country' => 'Côte d\'Ivoire',
        ]);

        $client = User::forceCreate([
            'name' => 'Kouamé Jean-Philippe',
            'email' => 'lecteur@gmail.com',
            'password' => Hash::make('Lecteur@123456'),
            'role' => 'customer',
            'email_verified_at' => now(),
            'phone' => '+225 05 55 44 33 22',
            'address' => 'Riviera Palmeraie, Rue Ministre',
            'city' => 'Abidjan',
            'country' => 'Côte d\'Ivoire',
        ]);

        // 2. Création des 4 Familles de Catégories
        $catSpiritualite = Category::create([
            'name' => 'Spiritualité africaine',
            'slug' => 'spiritualite-africaine',
            'description' => 'Traditions initiatiques, cosmogonies ancestrales, sagesses des aînés et ésotérisme africain.',
            'icon' => 'sparkles',
            'display_order' => 1,
            'is_active' => true,
        ]);

        $catPhilosophie = Category::create([
            'name' => 'Philosophie',
            'slug' => 'philosophie',
            'description' => 'Courants de pensée africains, épistémologie, ontologie bantu et essais philosophiques contemporains.',
            'icon' => 'book-open',
            'display_order' => 2,
            'is_active' => true,
        ]);

        $catReligion = Category::create([
            'name' => 'Religion',
            'slug' => 'religion',
            'description' => 'Christianisme africain, théologie de la libération, islam en Afrique de l\'Ouest et religions traditionnelles.',
            'icon' => 'shield',
            'display_order' => 3,
            'is_active' => true,
        ]);

        $catPolitique = Category::create([
            'name' => 'Politique & Panafricanisme',
            'slug' => 'politique-panafricanisme',
            'description' => 'Souveraineté, décolonisation intellectuelle, géopolitique africaine et grands manifestes panafricains.',
            'icon' => 'globe',
            'display_order' => 4,
            'is_active' => true,
        ]);

        // 3. Création des Livres du Catalogue
        $booksData = [
            [
                'category_id' => $catPolitique->id,
                'title' => 'Nations nègres et culture : De l\'antiquité nègre égyptienne aux problèmes culturels de l\'Afrique noire d\'aujourd\'hui',
                'slug' => 'nations-negres-et-culture',
                'author' => 'Cheikh Anta Diop',
                'publisher' => 'Présence Africaine',
                'language' => 'Français',
                'pages_count' => 564,
                'published_year' => 1954,
                'isbn' => '978-2708706880',
                'description' => 'L\'œuvre maîtresse qui a révolutionné la recherche historique africaine. Cheikh Anta Diop y démontre scientifiquement la filiation historique et linguistique entre l\'Égypte ancienne et les civilisations d\'Afrique subsaharienne, restituant au monde noir sa place fondamentale dans l\'histoire humaine.',
                'excerpt' => '« L\'histoire est la mémoire du peuple. Sans mémoire, l\'homme est condamné à errer sans repère. L\'Africain doit se réapproprier son passé pour mieux appréhender son présent et bâtir son futur. »',
                'price_pdf' => 4500,
                'price_physical' => 12000,
                'price_audio' => 3500,
                'stock_physical' => 25,
                'is_pdf_available' => true,
                'is_physical_available' => true,
                'is_audio_available' => true,
                'audio_duration' => '12h 45m',
                'is_featured' => true,
                'weight_grams' => 650,
            ],
            [
                'category_id' => $catSpiritualite->id,
                'title' => 'L\'Étrange Destin de Wangrin ou les roueries d\'un interprète africain',
                'slug' => 'l-etrange-destin-de-wangrin',
                'author' => 'Amadou Hampâté Bâ',
                'publisher' => 'Union Générale d\'Éditions',
                'language' => 'Français',
                'pages_count' => 448,
                'published_year' => 1973,
                'isbn' => '978-2264024848',
                'description' => 'Un chef-d\'œuvre de la tradition orale transcrite par l\'un des plus grands sages africains. Ce récit initiatique explore le destin fascinant de Wangrin, guidé par les mystères du pacte avec son dieu tutélaire Gongoloma-Sooké et confronté aux bouleversements de la période coloniale.',
                'excerpt' => '« En Afrique, un vieillard qui meurt est une bibliothèque qui brûle. Chaque parole d\'ancien recèle une clé pour ouvrir les portes du visible et de l\'invisible. »',
                'price_pdf' => 3500,
                'price_physical' => 8500,
                'price_audio' => 2800,
                'stock_physical' => 18,
                'is_pdf_available' => true,
                'is_physical_available' => true,
                'is_audio_available' => true,
                'audio_duration' => '8h 20m',
                'is_featured' => true,
                'weight_grams' => 420,
            ],
            [
                'category_id' => $catPolitique->id,
                'title' => 'Les Damnés de la terre',
                'slug' => 'les-damnes-de-la-terre',
                'author' => 'Frantz Fanon',
                'publisher' => 'Éditions Maspero',
                'language' => 'Français',
                'pages_count' => 312,
                'published_year' => 1961,
                'isbn' => '978-2707142818',
                'description' => 'Analyse clinique et politique de la décolonisation. Frantz Fanon décortique les mécanismes psychologiques de l\'oppression coloniale, les pièges du néo-colonialisme et la nécessité impérieuse de créer un « Homme Neuf » ancré dans la dignité et la libération des peuples.',
                'excerpt' => '« Chaque génération doit dans une relative opacité découvrir sa mission, la remplir ou la trahir. »',
                'price_pdf' => 4000,
                'price_physical' => 9500,
                'stock_physical' => 12,
                'is_pdf_available' => true,
                'is_physical_available' => true,
                'is_featured' => true,
                'weight_grams' => 380,
            ],
            [
                'category_id' => $catPhilosophie->id,
                'title' => 'Essai sur la problématique philosophique dans l\'Afrique actuelle',
                'slug' => 'essai-problematique-philosophique-afrique',
                'author' => 'Marcien Towa',
                'publisher' => 'Éditions CLE',
                'language' => 'Français',
                'pages_count' => 128,
                'published_year' => 1971,
                'isbn' => '978-2723500050',
                'description' => 'Un classique de la philosophie africaine critique. Marcien Towa s\'interroge avec rigueur sur la nature de la pensée africaine, rejetant l\'ethnophilosophie pour appeler à une véritable audace spéculative et à la maîtrise de la science contemporaine.',
                'excerpt' => '« La philosophie ne commence qu\'avec la décision de soumettre l\'héritage philosophique et culturel à une critique sans complaisance. »',
                'price_pdf' => 2500,
                'price_physical' => 6000,
                'stock_physical' => 8,
                'is_pdf_available' => true,
                'is_physical_available' => true,
                'is_featured' => false,
                'weight_grams' => 200,
            ],
            [
                'category_id' => $catReligion->id,
                'title' => 'Ma foi d\'Africain : Pour une théologie sous l\'arbre à palabre',
                'slug' => 'ma-foi-d-africain',
                'author' => 'Jean-Marc Ela',
                'publisher' => 'Karthala',
                'language' => 'Français',
                'pages_count' => 260,
                'published_year' => 1985,
                'isbn' => '978-2865371303',
                'description' => 'Le sociologue et théologien camerounais Jean-Marc Ela pose les jalons d\'une spiritualité incarnée dans les réalités vécues des populations du continent. Un appel vibrant à libérer la foi des carcans occidentaux et à écouter la voix des opprimés.',
                'excerpt' => '« Dieu ne se révèle pas dans l\'abstraction des dogmes importés, mais dans le cri de l\'affamé et le combat pour la justice des hommes et des femmes de notre terroir. »',
                'price_pdf' => 3000,
                'price_physical' => 7500,
                'stock_physical' => 14,
                'is_pdf_available' => true,
                'is_physical_available' => true,
                'is_featured' => false,
                'weight_grams' => 310,
            ],
            [
                'category_id' => $catSpiritualite->id,
                'title' => 'Dieu d\'eau : Entretiens avec Ogotemmêli',
                'slug' => 'dieu-d-eau-entretiens-ogotemmeli',
                'author' => 'Marcel Griaule & Ogotemmêli',
                'publisher' => 'Fayard',
                'language' => 'Français',
                'pages_count' => 224,
                'published_year' => 1948,
                'isbn' => '978-2213594675',
                'description' => 'La révélation au monde de la prodigieuse cosmogonie Dogon (Mali). Pendant 33 jours, le vieux chasseur aveugle Ogotemmêli expose au chercheur la genèse de l\'univers, le rôle du Nommo, la création de la parole, le tissage et la structure ésotérique du sanctuaire cosmique.',
                'excerpt' => '« La parole est entrée dans l\'homme par les oreilles, elle s\'est logée dans le cœur avant de jaillir par la bouche comme l\'eau féconde la terre desséchée. »',
                'price_pdf' => 3800,
                'price_physical' => 9000,
                'price_audio' => 3000,
                'stock_physical' => 20,
                'is_pdf_available' => true,
                'is_physical_available' => true,
                'is_audio_available' => true,
                'audio_duration' => '6h 15m',
                'is_featured' => true,
                'weight_grams' => 290,
            ],
            [
                'category_id' => $catPolitique->id,
                'title' => 'L\'Afrique doit s\'unir',
                'slug' => 'l-afrique-doit-s-unir',
                'author' => 'Kwame Nkrumah',
                'publisher' => 'Présence Africaine',
                'language' => 'Français',
                'pages_count' => 256,
                'published_year' => 1963,
                'isbn' => '978-2708705791',
                'description' => 'Le manifeste fondateur des États-Unis d\'Afrique par le père de l\'indépendance ghanéenne. Kwame Nkrumah y démontre avec une clairvoyance prophétique que la balkanisation du continent le condamne à l\'impuissance économique et politique face aux appétits néocoloniaux.',
                'excerpt' => '« Divisés, nous sommes vulnérables ; unis, l\'Afrique peut devenir l\'une des plus grandes puissances de ce monde pour la paix et le progrès humain. »',
                'price_pdf' => 3200,
                'price_physical' => 8000,
                'stock_physical' => 30,
                'is_pdf_available' => true,
                'is_physical_available' => true,
                'is_featured' => true,
                'weight_grams' => 340,
            ],
            [
                'category_id' => $catSpiritualite->id,
                'title' => 'Vie et enseignement de Tierno Bokar, le sage de Bandiagara',
                'slug' => 'tierno-bokar-sage-bandiagara',
                'author' => 'Amadou Hampâté Bâ',
                'publisher' => 'Seuil',
                'language' => 'Français',
                'pages_count' => 256,
                'published_year' => 1980,
                'isbn' => '978-2020056571',
                'description' => 'L\'émouvant portrait de Tierno Bokar, maître soufi peul et apôtre de la tolérance universelle. Un texte d\'une élévation spirituelle rare sur l\'amour du prochain, le pardon et la lumière divine présente en tout être vivant.',
                'excerpt' => '« Il y a trois vérités : ma vérité, ta vérité, et la vérité. Souviens-toi que l\'arc-en-ciel doit sa beauté aux nuances variées de ses couleurs. »',
                'price_pdf' => 3000,
                'price_physical' => 7000,
                'stock_physical' => 15,
                'is_pdf_available' => true,
                'is_physical_available' => true,
                'is_featured' => false,
                'weight_grams' => 280,
            ],
        ];

        $createdBooks = [];
        foreach ($booksData as $data) {
            $createdBooks[] = Book::create($data);
        }

        // 4. Création d'avis clients élogieux
        $reviewComments = [
            ['rating' => 5, 'name' => 'Dr. Koffi Assi', 'comment' => 'Une réédition indispensable ! La qualité de la maquette numérique et la fluidité de lecture sont exceptionnelles. Merci pour ce travail de préservation.'],
            ['rating' => 5, 'name' => 'Aïcha Traoré', 'comment' => 'J\'ai acheté la version PDF téléchargée en 1 minute après mon paiement Mobile Money. L\'exemplaire sécurisé avec filigrane est très soigné.'],
            ['rating' => 4, 'name' => 'Serge N\'Dri', 'comment' => 'Livre physique reçu à Abidjan en 24h par coursier. Emballage protecteur de qualité, livre impeccable.'],
        ];

        foreach ($createdBooks as $book) {
            foreach ($reviewComments as $rev) {
                Review::create([
                    'book_id' => $book->id,
                    'user_id' => $client->id,
                    'customer_name' => $rev['name'],
                    'rating' => $rev['rating'],
                    'comment' => $rev['comment'],
                    'is_approved' => true,
                ]);
            }
        }

        // 5. Création d'une commande initiale pour le client test
        $firstBook = $createdBooks[0]; // Nations nègres et culture
        $secondBook = $createdBooks[1]; // Wangrin

        $order = Order::create([
            'order_number' => 'EDM-2026-DEMO1',
            'user_id' => $client->id,
            'customer_name' => $client->name,
            'customer_email' => $client->email,
            'customer_phone' => $client->phone,
            'subtotal_pdf' => $firstBook->price_pdf,
            'subtotal_physical' => $secondBook->price_physical,
            'shipping_cost' => 1500,
            'total_amount' => $firstBook->price_pdf + $secondBook->price_physical + 1500,
            'currency' => 'XOF',
            'payment_status' => 'paid',
            'payment_method' => 'paystack_momo',
            'payment_reference' => 'PAY_DEMO_INIT_001',
            'paid_at' => now()->subDays(1),
            'notes' => 'Commande mixte de démonstration pré-configurée.',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'book_id' => $firstBook->id,
            'format' => 'pdf',
            'quantity' => 1,
            'unit_price' => $firstBook->price_pdf,
            'total_price' => $firstBook->price_pdf,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'book_id' => $secondBook->id,
            'format' => 'physical',
            'quantity' => 1,
            'unit_price' => $secondBook->price_physical,
            'total_price' => $secondBook->price_physical,
        ]);

        // Adresse de livraison pour la partie physique
        ShippingAddress::create([
            'order_id' => $order->id,
            'recipient_name' => $client->name,
            'phone' => $client->phone,
            'delivery_zone' => 'abidjan',
            'address_line' => 'Riviera Palmeraie, Rue Ministre, Villa 45',
            'city' => 'Abidjan',
            'country' => 'Côte d\'Ivoire',
            'instructions' => 'Appeler le gardien à l\'arrivée.',
            'status' => 'processing',
            'carrier_name' => 'Coursier Express Abidjan',
            'tracking_number' => 'CX-ABJ-89421',
        ]);

        // Fichiers disponibles pour téléchargement dans la bibliothèque
        PurchasedDownload::create([
            'user_id' => $client->id,
            'order_id' => $order->id,
            'book_id' => $firstBook->id,
            'format' => 'pdf',
            'customer_name' => $client->name,
            'customer_email' => $client->email,
            'download_token' => Str::random(40),
            'download_count' => 1,
            'max_downloads' => 15,
            'last_downloaded_at' => now()->subHours(6),
            'last_ip_address' => '127.0.0.1',
        ]);

        PurchasedDownload::create([
            'user_id' => $client->id,
            'order_id' => $order->id,
            'book_id' => $secondBook->id,
            'format' => 'audio',
            'customer_name' => $client->name,
            'customer_email' => $client->email,
            'download_token' => Str::random(40),
            'download_count' => 0,
            'max_downloads' => 15,
        ]);

        // Audit Log initial
        AuditLog::log($admin->id, 'system_seeded', [
            'books_count' => count($booksData),
            'environment' => 'development',
        ]);
    }
}
