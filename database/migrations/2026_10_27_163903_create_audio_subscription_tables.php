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
        Schema::table('books', function (Blueprint $table) {
            $table->json('audio_plan_prices')->nullable()->after('price_audio'); // {"30": 1500, ...}
        });

        Schema::create('book_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position')->default(1);
            $table->string('title');
            $table->text('summary')->nullable();
            $table->timestamps();

            $table->index(['book_id', 'position']);
        });

        Schema::create('audio_tracks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_part_id')->constrained()->cascadeOnDelete();
            $table->string('language', 20);
            $table->string('path');
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->timestamps();

            $table->unique(['book_part_id', 'language']);
        });

        Schema::create('audio_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_part_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('access_days');
            $table->timestamp('starts_at');
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['user_id', 'book_part_id', 'expires_at']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('book_part_id')->nullable()->after('book_id')->constrained()->nullOnDelete();
            $table->unsignedInteger('access_days')->nullable()->after('format');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('book_part_id');
            $table->dropColumn('access_days');
        });

        Schema::dropIfExists('audio_subscriptions');
        Schema::dropIfExists('audio_tracks');
        Schema::dropIfExists('book_parts');

        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn('audio_plan_prices');
        });
    }
};
