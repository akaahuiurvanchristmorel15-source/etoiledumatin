<?php

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
