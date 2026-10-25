<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('kipk:dokumentasi-pengingat')
    ->dailyAt('00:00')
    ->timezone(config('app.timezone'))
    ->withoutOverlapping();

Schedule::command('kipk:backup')
    ->dailyAt('01:15')
    ->timezone(config('app.timezone'))
    ->withoutOverlapping();
