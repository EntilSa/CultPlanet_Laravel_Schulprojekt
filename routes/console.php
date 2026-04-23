<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// standard laravel beispiel-command
Artisan::command('inspire', function () {
    $this->comment(\Illuminate\Foundation\Inspiring::quote());
})->purpose('Display an inspiring quote');

// auktionen jede minute prüfen: geplante aktivieren + abgelaufene schließen
// lokal manuell ausführen: php artisan auctions:close
// auf einem echten server würde der cron-job "php artisan schedule:run" jede minute laufen
Schedule::command('auctions:close')->everyMinute();
