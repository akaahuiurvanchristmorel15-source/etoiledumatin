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
            $table->unsignedInteger('price_audio')->default(0)->after('price_physical');
            $table->boolean('is_audio_available')->default(false)->after('is_physical_available');
            $table->string('audio_path')->nullable()->after('pdf_path');
            $table->string('audio_sample_path')->nullable()->after('audio_path');
            $table->string('audio_duration', 50)->nullable()->after('audio_sample_path');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedInteger('subtotal_audio')->default(0)->after('subtotal_physical');
        });

        Schema::table('purchased_downloads', function (Blueprint $table) {
            $table->string('format', 20)->default('pdf')->after('book_id'); // 'pdf' ou 'audio'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchased_downloads', function (Blueprint $table) {
            $table->dropColumn('format');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('subtotal_audio');
        });

        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn([
                'price_audio',
                'is_audio_available',
                'audio_path',
                'audio_sample_path',
                'audio_duration',
            ]);
        });
    }
};
