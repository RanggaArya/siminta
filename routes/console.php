<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\SendPeminjamanH3Reminder;
use App\Console\Commands\UpdateExpiredPerangkat;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(SendPeminjamanH3Reminder::class)
    ->everyMinute()
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping();

Schedule::command(UpdateExpiredPerangkat::class)
    ->everyMinute()
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping();
