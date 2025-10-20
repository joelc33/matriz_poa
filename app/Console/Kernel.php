<?php

namespace matriz\Console;

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
        Commands\Inspire::class,
        \matriz\Console\Commands\compilarPassword::class,
        \matriz\Console\Commands\replicarEjercicio::class,
        \matriz\Console\Commands\cargaMasiva::class,
        \matriz\Console\Commands\actualizarMeta::class,
        \matriz\Console\Commands\montoAcAe::class,
        \matriz\Console\Commands\generarReporteDistribucion::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('inspire')
                 ->hourly();
    }
}
