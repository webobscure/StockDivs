<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('stocks:update-quotes')->everyTenMinutes()->withoutOverlapping();
Schedule::command('alerts:check')->everyTenMinutes()->withoutOverlapping();
Schedule::command('stocks:update-dividends')->dailyAt('02:10')->withoutOverlapping();
Schedule::command('exchange-rates:update')->dailyAt('02:30')->withoutOverlapping();
