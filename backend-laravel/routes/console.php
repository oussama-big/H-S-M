<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
use Illuminate\Support\Facades\Schedule;

// Exécuter la commande tous les jours à 9h00 du matin
Schedule::command('app:send-rdv-reminders')->dailyAt('09:00');