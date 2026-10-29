<?php

use App\Models\AuditLog;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Hébergement mutualisé sans worker permanent : la tâche cron « schedule:run » vide la file chaque minute
// (génération des voix de synthèse notamment).
Schedule::command('queue:work --stop-when-empty --timeout=1800 --max-time=3000')
    ->everyMinute()
    ->withoutOverlapping(60)
    ->runInBackground();

// Journal de sécurité purgé au-delà de 12 mois, comme l'annonce la politique de confidentialité
Schedule::command('model:prune', ['--model' => [AuditLog::class]])->daily();
