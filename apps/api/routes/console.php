<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Pindahkan last_used_at kunci API dari cache ke database setiap jam.
Schedule::command('api-key:flush-usage')->hourly();
