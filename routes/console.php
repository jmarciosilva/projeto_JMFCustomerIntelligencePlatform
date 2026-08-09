<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('metrics:aggregate-daily')->dailyAt('01:00');
Schedule::command('intelligence:compute')->dailyAt('02:00');
Schedule::command('intelligence:compute-segments')->dailyAt('02:30');
Schedule::command('intelligence:analyze-trends')->dailyAt('03:00');
Schedule::command('intelligence:detect-opportunities')->dailyAt('03:30');
Schedule::command('intelligence:generate-recommendations')->dailyAt('04:00');

// Fase 23 — Trend Intelligence
Schedule::command('trends:collect')->dailyAt('05:00');

// Fase 24 — Trend Score
Schedule::command('trends:calculate-scores')->dailyAt('05:30');
