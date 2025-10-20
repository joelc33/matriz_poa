<?php

namespace matriz\Console\Commands;

use Illuminate\Console\Command;

use matriz\Http\Controllers\Reporte\distribucionController;

class generarReporteDistribucion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'matriz:reporteDistribucion';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generar Reporte de Distribucion';

    /**
     * Create a new command instance.
     *
     * @return void
     */

    protected $reporte;

    public function __construct(distribucionController $reporte)
    {
        parent::__construct();
        $this->reporte = $reporte;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
        $libro = $this->reporte->libro();
    }
}
