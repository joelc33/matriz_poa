<?php

namespace matriz\Console\Commands;
//*******agregar esta linea******//
use matriz\Models\Ac\tab_ac;
use matriz\Models\Ac\tab_ac_ae;
use matriz\Models\Ac\tab_ac_ae_partida;
use DB;
//*******************************//
use Illuminate\Console\Command;

class montoAcAe extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'matriz:montoAcAe { ejercicio : Ejercicio a replicar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para replicar monto de Ac Ae';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $ejercicio = $this->argument('ejercicio');

        $tab_ac_ae_partida = tab_ac_ae_partida::select( 'id_accion_centralizada', 'id_accion',
        DB::raw('sum(monto) as monto_ac_ae') )
        ->where('id_tab_ejercicio_fiscal', '=', $ejercicio)
        ->groupBy('id_accion_centralizada')
        ->groupBy('id_accion')
        ->orderBy('id_accion_centralizada','ASC')
        ->get();

        $contador = 0;

        foreach ($tab_ac_ae_partida as $lista){

            $contador = $contador + 1;

            $update = tab_ac_ae::where('id_accion_centralizada', $lista->id_accion_centralizada)
            ->where('id_accion', $lista->id_accion)
            ->update(['monto' => $lista->monto_ac_ae, 'monto_calc' => $lista->monto_ac_ae]);

            DB::commit();

            $this->info($contador.' .- Accion Centralizada: '.$lista->id_accion_centralizada.' AE: '.$lista->id_accion.' actualizado.');

        }

        $tab_ac_partida = tab_ac_ae_partida::select( 'id_accion_centralizada', 
        DB::raw('sum(monto) as monto_ac') )
        ->where('id_tab_ejercicio_fiscal', '=', $ejercicio)
        ->groupBy('id_accion_centralizada')
        ->orderBy('id_accion_centralizada','ASC')
        ->get();

        $contador = 0;

        foreach ($tab_ac_partida as $lista_ac){

            $contador = $contador + 1;

            $update_ac = tab_ac::where('id', $lista_ac->id_accion_centralizada)
            ->update(['monto' => $lista_ac->monto_ac, 'monto_calc' => $lista_ac->monto_ac]);

            DB::commit();

            $this->info($contador.' .- Accion Centralizada: '.$lista_ac->id_accion_centralizada.' actualizado.');

        }

        DB::beginTransaction();
        try {

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();
            $this->info(utf8_encode( $e->getMessage()));
        }
    }
}
