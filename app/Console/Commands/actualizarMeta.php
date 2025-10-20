<?php

namespace matriz\Console\Commands;
//*******agregar esta linea******//
use matriz\Models\Ac\tab_meta_financiera;
use DB;
//*******************************//
use Illuminate\Console\Command;

class actualizarMeta extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'matriz:actualizarMeta { ejercicio : Ejercicio a cargar } { fuenteOrigen : Fuente origen } { fuenteDestino : Fuente destino }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para actualizar meta financiera';

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
        $fuenteOrigen = $this->argument('fuenteOrigen');
        $fuenteDestino = $this->argument('fuenteDestino');

        DB::beginTransaction();
        try {

            $tab_meta_financiera = tab_meta_financiera::select('co_metas_detalle', 'mo_presupuesto', 
            'co_partida', 'co_fuente')
            ->join('public.t69_metas_ac as t69', 'public.t70_metas_ac_detalle.co_metas', '=', 't69.co_metas')
            ->join('public.t46_acciones_centralizadas as t46', 't69.id_accion_centralizada', '=', 't46.id')
            ->where('id_ejercicio', '=', $ejercicio)
            ->where('co_fuente', '=', $fuenteOrigen)
            ->orderby('co_metas_detalle','ASC')
            ->get();

            $contador = 0;

            foreach ($tab_meta_financiera as $lista){

                $contador = $contador + 1;

                $update = tab_meta_financiera::where('co_metas_detalle', $lista->co_metas_detalle)
                ->update(['co_fuente' => $fuenteDestino]);

                DB::commit();

                $this->info($contador.' .- Meta Financiera: '.$lista->co_metas_detalle.' Fuente origen: '.$fuenteOrigen.' a Fuente Destino: '.$fuenteDestino.' actualizado.');

            }

        }catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();
            $this->info(utf8_encode( $e->getMessage()));
        }

    }
}
