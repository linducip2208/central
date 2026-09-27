<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('mbg:expiry-check')->dailyAt('06:00');
Schedule::command('mbg:low-stock-check')->hourly();
