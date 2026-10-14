<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Catégories
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Livres
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('author');
            $table->string('publisher')->nullable();
            $table->string('language')->default('Français');
            $table->integer('pages_count')->nullable();
            $table->integer('published_year')->nullable();
            $table->string('isbn')->nullable();
            $table->text('description');
            $table->text('excerpt')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('pdf_path')->nullable();
            $table->integer('weight_grams')->default(350);
            $table->unsignedInteger('price_pdf')->default(0);
            $table->unsignedInteger('price_physical')->default(0);
            $table->unsignedInteger('stock_physical')->default(0);
            $table->boolean('is_pdf_available')->default(true);
            $table->boolean('is_physical_available')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Modifier la table users pour ajouter le rôle et le téléphone
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('customer')->after('password'); // super_admin, manager, logistics, customer
            $table->string('phone')->nullable()->after('email');
            $table->string('address')->nullable()->after('phone');
            $table->string('city')->nullable()->after('address');
            $table->string('country')->default('Côte d\'Ivoire')->after('city');
        });

        // 4. Commandes
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone');
            $table->unsignedInteger('subtotal_pdf')->default(0);
            $table->unsignedInteger('subtotal_physical')->default(0);
            $table->unsignedInteger('shipping_cost')->default(0);
            $table->unsignedInteger('total_amount');
            $table->string('currency', 10)->default('XOF');
            $table->string('payment_status', 30)->default('pending'); // pending, paid, failed, refunded
            $table->string('payment_method', 50)->default('paystack'); // paystack_momo, paystack_card, paystack_sandbox
            $table->string('payment_reference')->nullable()->unique();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 5. Articles de commande
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->string('format', 20); // pdf, physical
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedInteger('unit_price');
            $table->unsignedInteger('total_price');
            $table->timestamps();
        });

        // 6. Expédition & Adresses de livraison
        Schema::create('shipping_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('recipient_name');
            $table->string('phone');
            $table->string('delivery_zone'); // abidjan, interior, uemoa, international
            $table->string('address_line');
            $table->string('city');
            $table->string('country')->default('Côte d\'Ivoire');
            $table->text('instructions')->nullable();
            $table->string('status', 30)->default('pending'); // pending, processing, shipped, delivered, cancelled
            $table->string('carrier_name')->nullable();
            $table->string('tracking_number')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });

        // 7. Accès & Téléchargements sécurisés de PDF
        Schema::create('purchased_downloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('download_token', 64)->unique();
            $table->unsignedInteger('download_count')->default(0);
            $table->unsignedInteger('max_downloads')->default(15);
            $table->timestamp('last_downloaded_at')->nullable();
            $table->string('last_ip_address', 45)->nullable();
            $table->timestamps();
        });

        // 8. Avis clients & notations
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name');
            $table->unsignedTinyInteger('rating'); // 1 à 5
            $table->text('comment');
            $table->boolean('is_approved')->default(false);
            $table->timestamps();
        });

        // 9. Journal d'audit (sécurité et traçabilité)
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action'); // e.g. order_paid, status_updated, pdf_downloaded, book_edited
            $table->json('details')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('purchased_downloads');
        Schema::dropIfExists('shipping_addresses');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'phone', 'address', 'city', 'country']);
        });
        Schema::dropIfExists('books');
        Schema::dropIfExists('categories');
    }
};
