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
