<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 60)->unique(); // valeur enregistrée dans users.role
            $table->string('name', 60)->unique();
            $table->string('description')->nullable();
            $table->json('permissions')->nullable();
            $table->boolean('is_system')->default(false); // super administrateur : ni modifiable ni supprimable
            $table->timestamps();
        });

        // Rôles existants, avec les droits qu'ils avaient jusqu'ici
        $now = now();
        DB::table('roles')->insert([
            [
                'slug' => 'super_admin',
                'name' => 'Super administrateur',
                'description' => 'Tous les droits, y compris sur les administrateurs. Non modifiable.',
                'permissions' => json_encode([]),
                'is_system' => true,
            ],
            [
                'slug' => 'admin',
                'name' => 'Administrateur',
                'description' => 'Gère toute la boutique et l\'équipe.',
                'permissions' => json_encode(['manage-catalog', 'manage-stock', 'manage-news', 'manage-reviews', 'manage-orders', 'manage-store', 'manage-team']),
                'is_system' => false,
            ],
            [
                'slug' => 'manager',
                'name' => 'Gestionnaire catalogue',
                'description' => 'Livres, stock, actualités et avis.',
                'permissions' => json_encode(['manage-catalog', 'manage-stock', 'manage-news', 'manage-reviews']),
                'is_system' => false,
            ],
            [
                'slug' => 'logistics',
                'name' => 'Logistique',
                'description' => 'Commandes et expéditions.',
                'permissions' => json_encode(['manage-orders']),
                'is_system' => false,
            ],
        ]);

        DB::table('roles')->update(['created_at' => $now, 'updated_at' => $now]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
