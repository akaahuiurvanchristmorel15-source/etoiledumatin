<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Quota d'écoute de chaque abonnement : null = sans limite (abonnements souscrits avant les quotas).
     */
    public function up(): void
    {
        Schema::table('audio_subscriptions', function (Blueprint $table) {
            $table->unsignedInteger('listening_seconds')->nullable()->after('expires_at');
            $table->unsignedInteger('listened_seconds')->default(0)->after('listening_seconds');
        });
    }

    public function down(): void
    {
        Schema::table('audio_subscriptions', function (Blueprint $table) {
            $table->dropColumn(['listening_seconds', 'listened_seconds']);
        });
    }
};
