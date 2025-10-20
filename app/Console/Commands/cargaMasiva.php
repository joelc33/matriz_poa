<?php

namespace matriz\Console\Commands;
//*******agregar esta linea******//
use matriz\Models\Ac\tab_ac;
use matriz\Models\Ac\t49_ac_planes;
use matriz\Models\Ac\t50_ac_localizacion;
use matriz\Models\Ac\tab_ac_responsable;
use matriz\Models\Ac\tab_ac_ae;
use matriz\Models\Ac\tab_ac_ae_partida;
use matriz\Models\Ac\t56_ac_ae_fuente;
use matriz\Models\Ac\tab_meta_fisica;
use matriz\Models\Ac\tab_meta_financiera;
use matriz\Models\Ac\tab_partida_importar;
use DB;
//*******************************//
use Illuminate\Console\Command;

class cargaMasiva extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'matriz:cargaMasiva { ejercicio : Ejercicio a cargar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para carga masiva de partidas';

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

        DB::beginTransaction();
        try {

            $tab_partida_importar = tab_partida_importar::select('id', 'ejercicio_fiscal', 'codigo_ejecutor', 'descripcion_ejecutor', 
            'codigo_ac', 'descripcion_ac', 'codigo_ae', 'descripcion_ae', 'codigo_partida', 
            'descripcion_partida', 'monto_partida', 'in_cargado', 'created_at', 'updated_at', 
            'id_accion_centralizada', 'nu_ac', 'id_accion')
            ->where('ejercicio_fiscal', '=', $ejercicio)
            ->where('in_ejecutar', '=', true)
            ->orderby('id','ASC')
            ->get();

            foreach ($tab_partida_importar as $lista){

                $codigo_ac = substr($lista->codigo_ac, -1);

                $update = tab_partida_importar::where('ejercicio_fiscal', $ejercicio)
                ->where('id', $lista->id)
                ->update(['nu_ac' => $codigo_ac, 'in_cargado' => false]);

                DB::commit();

                $this->info('AC: '.$lista->codigo_ac.' actualizado.');

            }

            foreach ($tab_partida_importar as $lista){

                $tab_ac = tab_ac::where('id_ejercicio', '=', $lista->ejercicio_fiscal)
                ->where('id_accion', '=', $lista->nu_ac)
                ->where('id_ejecutor', '=', trim($lista->codigo_ejecutor))
                ->first();

                if(empty($tab_ac)){
                    $this->info('Registro: '.$lista->id.' error.');
                }

                $update = tab_partida_importar::where('id', $lista->id)
                ->update(['id_accion_centralizada' => $tab_ac->id]);

                DB::commit();

                $this->info('AC: '.$lista->codigo_ac.' actualizado.');

            }

            $tab_co_ae = tab_partida_importar::select('tab_partida_importar.id', 'id_accion_centralizada', 't02.id as co_ae' )
            ->join('mantenimiento.tab_ac_ae_predefinida as t02', function ($j) {
                $j->on('t02.id_padre','=','tab_partida_importar.nu_ac')
                  ->on('t02.nu_numero','=','tab_partida_importar.codigo_ae');
            })
            ->where('ejercicio_fiscal', '=', $ejercicio)
            ->where('in_ejecutar', '=', true)
            ->orderBy('id_accion_centralizada','ASC')
            ->get();

            foreach ($tab_co_ae as $lista){

                $update = tab_partida_importar::where('id', $lista->id)
                ->update(['id_accion' => $lista->co_ae]);

                DB::commit();

                $this->info('AC: '.$lista->id_accion_centralizada.' actualizado.');

            }

            foreach ($tab_partida_importar as $lista_ac){

                $borrar_ac_ae_partida = tab_ac_ae_partida::where('id_accion_centralizada', '=', $lista_ac->id_accion_centralizada )
                ->where('id_accion', '=', $lista_ac->id_accion )->delete();

                DB::commit();

                $this->info('AC: '.$lista_ac->id_accion_centralizada.' Accion: '.$lista_ac->id_accion.' limpiado.');

            }

            $tab_partida_importar = tab_partida_importar::select( 'ejercicio_fiscal', 'id_accion_centralizada', 'id_accion', 'codigo_partida',  'descripcion_partida', 
            DB::raw('sum(monto_partida) as monto_partida') )
            ->where('ejercicio_fiscal', '=', $ejercicio)
            ->where('in_ejecutar', '=', true)
            ->groupBy('ejercicio_fiscal')
            ->groupBy('id_accion_centralizada')
            ->groupBy('id_accion')
            ->groupBy('codigo_partida')
            ->groupBy('descripcion_partida')
            ->orderBy('id_accion_centralizada','ASC')
            ->get();

            //dd($tab_partida_importar);

            foreach ($tab_partida_importar as $lista_ac_ae_partida){

                $replica_ac_ae_partida = new tab_ac_ae_partida;
                $replica_ac_ae_partida->id_accion_centralizada = $lista_ac_ae_partida->id_accion_centralizada;
                $replica_ac_ae_partida->id_accion = $lista_ac_ae_partida->id_accion;
                $replica_ac_ae_partida->co_partida = trim($lista_ac_ae_partida->codigo_partida);
                $replica_ac_ae_partida->monto = $lista_ac_ae_partida->monto_partida;
                $replica_ac_ae_partida->id_tab_ejercicio_fiscal = $lista_ac_ae_partida->ejercicio_fiscal;
                $replica_ac_ae_partida->de_denominacion = $lista_ac_ae_partida->descripcion_partida;
                $replica_ac_ae_partida->edo_reg = true;
                $replica_ac_ae_partida->save();

                $update = tab_partida_importar::where('id_accion_centralizada', $lista_ac_ae_partida->id_accion_centralizada)
                ->where('id_accion', $lista_ac_ae_partida->id_accion)
                ->where('codigo_partida', $lista_ac_ae_partida->codigo_partida)
                ->update(['in_cargado' => true]);

                DB::commit();

                $this->info('AC: '.$lista_ac_ae_partida->id_accion_centralizada.' Accion: '.$lista_ac_ae_partida->id_accion.' cargado.');

            }

        }catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();
            $this->info(utf8_encode( $e->getMessage()));
        }
    }
}
