<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('mbg:expiry-check')->dailyAt('06:00');
Schedule::command('mbg:low-stock-check')->hourly();
Schedule::command('mbg:run-scheduled-reports')->everyFifteenMinutes();
Schedule::command('mbg:escalate')->twiceDaily(7, 17);
Schedule::command('mbg:backup --keep=7')->dailyAt('02:00');
Schedule::command('mbg:cleanup --days=90')->weeklyOn(0, '03:00');
Schedule::command('queue:work --stop-when-empty --max-time=300')->everyFiveMinutes()->withoutOverlapping();
