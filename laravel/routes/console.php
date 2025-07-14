<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
*/

/*
Schedule::command('my:run-dca --id=1')->everyTwoHours(13)->withoutOverlapping(2);

Schedule::command('my:run-dca --id=1')->hourly()->withoutOverlapping(2);

Schedule::command('my:run-dca --id=1')->everySixHours()->withoutOverlapping(2);

Schedule::command('my:run-dca --id=1')->dailyAt('13:00')->withoutOverlapping(2);
*/

Schedule::command('my:run-dca --schevery=everyMinute')->everyMinute()->withoutOverlapping(2);
Schedule::command('my:run-dca --schevery=everyFiveMinutes')->everyFiveMinutes()->withoutOverlapping(2);
Schedule::command('my:run-dca --schevery=everyTenMinutes')->everyTenMinutes()->withoutOverlapping(2);
Schedule::command('my:run-dca --schevery=everyFifteenMinutes')->everyFifteenMinutes()->withoutOverlapping(2);
Schedule::command('my:run-dca --schevery=everyThirtyMinutes')->everyThirtyMinutes()->withoutOverlapping(2);
//Schedule::command('my:run-dca --schevery=hourly')->hourly()->withoutOverlapping(2);
Schedule::command('my:run-dca --schevery=hourly')->hourlyAt(1)->withoutOverlapping(2);
Schedule::command('my:run-dca --schevery=everyOddHour')->everyOddHour(2)->withoutOverlapping(2);
Schedule::command('my:run-dca --schevery=everyTwoHours')->everyTwoHours(3)->withoutOverlapping(2);
Schedule::command('my:run-dca --schevery=everyThreeHours')->everyThreeHours(4)->withoutOverlapping(2);
Schedule::command('my:run-dca --schevery=everyFourHours')->everyFourHours(5)->withoutOverlapping(2);
Schedule::command('my:run-dca --schevery=everySixHours')->everySixHours(6)->withoutOverlapping(2);
//Schedule::command('my:run-dca --schevery=daily')->daily()->withoutOverlapping(2);
Schedule::command('my:run-dca --schevery=daily')->dailyAt('00:07')->withoutOverlapping(2);
Schedule::command('my:run-dca --schevery=weekly')->weekly()->withoutOverlapping(2);
Schedule::command('my:run-dca --schevery=monthly')->monthly()->withoutOverlapping(2);

// Extra Buys Commands
Schedule::command('my:check-extra-buys')->hourly()->withoutOverlapping(2);
Schedule::command('my:reset-extra-buys-counters')->everyMinute()->withoutOverlapping(1);
