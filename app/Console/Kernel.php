<?php

namespace App\Console;

use App\Jobs\ClearFans48;
use App\Jobs\GatherTask;
use App\Jobs\RecordWxAllData;
use App\Jobs\ReplyContentView;
use App\Jobs\UpApiUser;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('queue:restart')->hourlyAt(56)->description('Restart queue worker daemons after their current job');
        $schedule->job(new ClearFans48())->hourlyAt(42)->description('Clear away 48h fans list');

        $schedule->job(new UpApiUser())->dailyAt('01:00')->description('Timing update user(api) data');
        $schedule->job(new RecordWxAllData())->dailyAt('01:05')->description('Record wx all data');
        $schedule->job(new ReplyContentView())->dailyAt('01:10')->description('Up reply content view');

        $schedule->job(new GatherTask('migrate'))->dailyAt('01:30')->description('migrate data to es');
        $schedule->job(new GatherTask('publish'))->dailyAt('02:00')->description('publish gather task');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
