<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('import:sirene --type=unites --diff --file=/var/www/html/storage/app/imports/unites_diff.csv')
    ->dailyAt('02:00')
    ->withoutOverlapping();

Schedule::command('import:sirene --type=etablissements --diff --file=/var/www/html/storage/app/imports/etablissements_diff.csv')
    ->dailyAt('03:00')
    ->withoutOverlapping();

Schedule::command('import:cog --communes=/var/www/html/storage/app/imports/cog_communes.csv --geofla=/var/www/html/storage/app/imports/cog_geofla.csv')
    ->weeklyOn(1, '04:00')
    ->withoutOverlapping();
