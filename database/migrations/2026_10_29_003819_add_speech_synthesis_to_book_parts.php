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
        Schema::table('book_parts', function (Blueprint $table) {
            $table->json('texts')->nullable()->after('summary'); // {"fr": "...", "en": "..."}
        });

        Schema::create('audio_syntheses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_part_id')->constrained()->cascadeOnDelete();
            $table->string('language', 20);
            $table->string('status', 20)->default('pending'); // pending, processing, done, failed
            $table->text('error')->nullable();
            $table->timestamps();

            $table->unique(['book_part_id', 'language']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audio_syntheses');

        Schema::table('book_parts', function (Blueprint $table) {
            $table->dropColumn('texts');
        });
    }
};
