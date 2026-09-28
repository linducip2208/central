<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('mbg:expiry-check')->dailyAt('06:00');
Schedule::command('mbg:low-stock-check')->hourly();
Schedule::command('mbg:run-scheduled-reports')->everyFifteenMinutes();
Schedule::command('queue:work --stop-when-empty --max-time=300')->everyFiveMinutes()->withoutOverlapping();
