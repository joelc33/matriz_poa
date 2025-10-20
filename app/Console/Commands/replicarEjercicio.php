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
use DB;
//*******************************//
use Illuminate\Console\Command;

class replicarEjercicio extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'matriz:replicarEjercicio { ejercicio : Ejercicio a replicar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para replicar ejercicio fiscal';

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

            $ejercicio_replica = $ejercicio + 1;

            $borrar_ac = tab_ac::where('id_ejercicio', '=', $ejercicio_replica )->delete();
  
            $tab_ac = tab_ac::select( 'id', 'id_ejecutor', 'id_ejercicio', 'id_accion', 'id_subsector', 'id_estatus', 
            'sit_presupuesto', 'codigo_new_etapa', 'descripcion', 'monto', 'monto_calc', 
            'fecha_inicio', 'fecha_fin', 'edo_reg', 'fecha_creacion', 'fecha_actualizacion', 
            'inst_mision', 'inst_vision', 'inst_objetivos', 'nu_po_beneficiar', 'nu_em_previsto', 
            'tx_re_esperado', 'tx_pr_objetivo', 'id_tab_ejecutor')
            ->where('edo_reg', '=', TRUE)
            ->where('id_ejercicio', '=', $ejercicio )
            ->orderby('id','ASC')
            ->get();
    
            $contador = 1;

            $fecha_ini = $ejercicio_replica.'-01-01';
            $fecha_fin = $ejercicio_replica.'-12-31';
    
            foreach ($tab_ac as $lista_ac){
    
                $replica_tab_ac = new tab_ac;
                $replica_tab_ac->id_ejecutor = $lista_ac->id_ejecutor;
                $replica_tab_ac->id_ejercicio = $ejercicio_replica;
                $replica_tab_ac->id_accion = $lista_ac->id_accion;
                $replica_tab_ac->id_subsector = $lista_ac->id_subsector;
                $replica_tab_ac->id_estatus = 1;
                $replica_tab_ac->sit_presupuesto = $lista_ac->sit_presupuesto;
                $replica_tab_ac->codigo_new_etapa = $lista_ac->codigo_new_etapa;
                $replica_tab_ac->descripcion = $lista_ac->descripcion;
                $replica_tab_ac->monto = $lista_ac->monto;
                $replica_tab_ac->monto_calc = $lista_ac->monto_calc;
                $replica_tab_ac->fecha_inicio = $fecha_ini;
                $replica_tab_ac->fecha_fin = $fecha_fin;
                $replica_tab_ac->edo_reg = true;
                //$replica_tab_ac->fecha_creacion = $lista_ac->fecha_creacion;
                //$replica_tab_ac->fecha_actualizacion = $lista_ac->fecha_actualizacion;
                $replica_tab_ac->inst_mision = $lista_ac->inst_mision;
                $replica_tab_ac->inst_vision = $lista_ac->inst_vision;
                $replica_tab_ac->inst_objetivos = $lista_ac->inst_objetivos;
                $replica_tab_ac->nu_po_beneficiar = $lista_ac->nu_po_beneficiar;
                $replica_tab_ac->nu_em_previsto = $lista_ac->nu_em_previsto;
                $replica_tab_ac->tx_re_esperado = $lista_ac->tx_re_esperado;
                $replica_tab_ac->tx_pr_objetivo = $lista_ac->tx_pr_objetivo;
                $replica_tab_ac->id_tab_ejecutor = $lista_ac->id_tab_ejecutor;
                $replica_tab_ac->save();

                $t49_ac_planes = t49_ac_planes::select( 'id_accion_centralizada', 'fecha_creacion', 'fecha_actualizacion', 
                'co_area_estrategica', 'co_ambito_estado', 'co_objetivo_estado', 'co_macroproblema', 
                'co_nodos', 'co_objetivo_historico', 'co_objetivo_nacional', 'co_objetivo_estrategico', 
                'co_objetivo_general')
                ->where('id_accion_centralizada', '=', $lista_ac->id )
                ->orderby('id_accion_centralizada','ASC')
                ->get();

                foreach ($t49_ac_planes as $lista_ac_planes){
                    $replica_t49_ac_planes = new t49_ac_planes;
                    $replica_t49_ac_planes->id_accion_centralizada = $replica_tab_ac->id;
                    $replica_t49_ac_planes->co_area_estrategica = $lista_ac_planes->co_area_estrategica;
                    $replica_t49_ac_planes->co_ambito_estado = $lista_ac_planes->co_ambito_estado;
                    $replica_t49_ac_planes->co_objetivo_estado = $lista_ac_planes->co_objetivo_estado;
                    $replica_t49_ac_planes->co_macroproblema = $lista_ac_planes->co_macroproblema;
                    $replica_t49_ac_planes->co_nodos = $lista_ac_planes->co_nodos;
                    $replica_t49_ac_planes->co_objetivo_historico = $lista_ac_planes->co_objetivo_historico;
                    $replica_t49_ac_planes->co_objetivo_nacional = $lista_ac_planes->co_objetivo_nacional;
                    $replica_t49_ac_planes->co_objetivo_estrategico = $lista_ac_planes->co_objetivo_estrategico;
                    $replica_t49_ac_planes->co_objetivo_general = $lista_ac_planes->co_objetivo_general;
                    $replica_t49_ac_planes->save();
                }

                $t50_ac_localizacion = t50_ac_localizacion::select( 'id_accion_centralizada', 'co_municipio', 'co_parroquia', 'fecha_creacion')
                ->where('id_accion_centralizada', '=', $lista_ac->id )
                ->orderby('id_accion_centralizada','ASC')
                ->get();

                foreach ($t50_ac_localizacion as $lista_ac_localizacion){
                    $replica_t50_ac_localizacion = new t50_ac_localizacion;
                    $replica_t50_ac_localizacion->id_accion_centralizada = $replica_tab_ac->id;
                    $replica_t50_ac_localizacion->co_municipio = $lista_ac_localizacion->co_municipio;
                    $replica_t50_ac_localizacion->co_parroquia = $lista_ac_localizacion->co_parroquia;
                    $replica_t50_ac_localizacion->save();
                }

                $tab_ac_responsable = tab_ac_responsable::select( 'id_accion_centralizada', 'realizador_nombres', 'realizador_cedula', 
                'realizador_cargo', 'realizador_correo', 'realizador_telefono', 'realizador_unidad', 
                'registrador_nombres', 'registrador_cedula', 'registrador_cargo', 'registrador_correo', 
                'registrador_telefono', 'registrador_unidad', 'autorizador_nombres', 
                'autorizador_cedula', 'autorizador_cargo', 'autorizador_correo', 'autorizador_telefono', 
                'autorizador_unidad', 'fecha_creacion', 'fecha_actualizacion')
                ->where('id_accion_centralizada', '=', $lista_ac->id )
                ->orderby('id_accion_centralizada','ASC')
                ->get();

                foreach ($tab_ac_responsable as $lista_ac_responsable){
                    $replica_ac_responsable = new tab_ac_responsable;
                    $replica_ac_responsable->id_accion_centralizada = $replica_tab_ac->id;
                    $replica_ac_responsable->realizador_nombres = $lista_ac_responsable->realizador_nombres;
                    $replica_ac_responsable->realizador_cedula = $lista_ac_responsable->realizador_cedula;
                    $replica_ac_responsable->realizador_cargo = $lista_ac_responsable->realizador_cargo;
                    $replica_ac_responsable->realizador_correo = $lista_ac_responsable->realizador_correo;
                    $replica_ac_responsable->realizador_telefono = $lista_ac_responsable->realizador_telefono;
                    $replica_ac_responsable->realizador_unidad = $lista_ac_responsable->realizador_unidad;
                    $replica_ac_responsable->registrador_nombres = $lista_ac_responsable->registrador_nombres;
                    $replica_ac_responsable->registrador_cedula = $lista_ac_responsable->registrador_cedula;
                    $replica_ac_responsable->registrador_cargo = $lista_ac_responsable->registrador_cargo;
                    $replica_ac_responsable->registrador_correo = $lista_ac_responsable->registrador_correo;
                    $replica_ac_responsable->registrador_telefono = $lista_ac_responsable->registrador_telefono;
                    $replica_ac_responsable->registrador_unidad = $lista_ac_responsable->registrador_unidad;
                    $replica_ac_responsable->autorizador_nombres = $lista_ac_responsable->autorizador_nombres;
                    $replica_ac_responsable->autorizador_cedula = $lista_ac_responsable->autorizador_cedula;
                    $replica_ac_responsable->autorizador_cargo = $lista_ac_responsable->autorizador_cargo;
                    $replica_ac_responsable->autorizador_correo = $lista_ac_responsable->autorizador_correo;
                    $replica_ac_responsable->autorizador_telefono = $lista_ac_responsable->autorizador_telefono;
                    $replica_ac_responsable->autorizador_unidad = $lista_ac_responsable->autorizador_unidad;
                    $replica_ac_responsable->save();
                }

                $tab_ac_ae = tab_ac_ae::select( 'id_accion_centralizada', 'id_accion', 'id_ejecutor', 'bien_servicio', 
                'id_unidad_medida', 'meta', 'ponderacion', 'id_tipo_fondo', 'monto', 'monto_calc', 
                'fecha_inicio', 'fecha_fin', 'edo_reg', 'fecha_creacion', 'fecha_actualizacion', 
                'objetivo_institucional', 'id_tab_ejecutor', 'in_definitivo')
                ->where('id_accion_centralizada', '=', $lista_ac->id )
                ->orderby('id_accion_centralizada','ASC')
                ->get();

                foreach ($tab_ac_ae as $lista_ac_ae){
                    $replica_ac_ae = new tab_ac_ae;
                    $replica_ac_ae->id_accion_centralizada = $replica_tab_ac->id;
                    $replica_ac_ae->id_accion = $lista_ac_ae->id_accion;
                    $replica_ac_ae->id_ejecutor = $lista_ac_ae->id_ejecutor;
                    $replica_ac_ae->bien_servicio = $lista_ac_ae->bien_servicio;
                    $replica_ac_ae->id_unidad_medida = $lista_ac_ae->id_unidad_medida;
                    $replica_ac_ae->meta = $lista_ac_ae->meta;
                    $replica_ac_ae->ponderacion = $lista_ac_ae->ponderacion;
                    $replica_ac_ae->id_tipo_fondo = $lista_ac_ae->id_tipo_fondo;
                    $replica_ac_ae->monto = $lista_ac_ae->monto;
                    $replica_ac_ae->monto_calc = $lista_ac_ae->monto_calc;
                    $replica_ac_ae->fecha_inicio = $fecha_ini;
                    $replica_ac_ae->fecha_fin = $fecha_fin;
                    $replica_ac_ae->edo_reg = $lista_ac_ae->edo_reg;
                    //$replica_ac_ae->fecha_creacion = $lista_ac_ae->fecha_creacion;
                    //$replica_ac_ae->fecha_actualizacion = $lista_ac_ae->fecha_actualizacion;
                    $replica_ac_ae->objetivo_institucional = $lista_ac_ae->objetivo_institucional;
                    $replica_ac_ae->id_tab_ejecutor = $lista_ac_ae->id_tab_ejecutor;
                    $replica_ac_ae->in_definitivo = $lista_ac_ae->in_definitivo;
                    $replica_ac_ae->save();

                    $t56_ac_ae_fuente = t56_ac_ae_fuente::select( 'id_ac', 'id_ae', 'id_tipo_fondo', 'monto')
                    ->where('id_ac', '=', $lista_ac->id )
                    ->where('id_ae', '=', $lista_ac_ae->id_accion )
                    ->orderby('id_ac','ASC')
                    ->get();

                    foreach ($t56_ac_ae_fuente as $lista_ac_ae_fuente){
                        $replica_ac_ae_fuente = new t56_ac_ae_fuente;
                        $replica_ac_ae_fuente->id_ac = $replica_tab_ac->id;
                        $replica_ac_ae_fuente->id_ae = $lista_ac_ae_fuente->id_ae;
                        $replica_ac_ae_fuente->id_tipo_fondo = $lista_ac_ae_fuente->id_tipo_fondo;
                        $replica_ac_ae_fuente->monto = $lista_ac_ae_fuente->monto;
                        $replica_ac_ae_fuente->save();
                    }

                    $tab_ac_ae_partida = tab_ac_ae_partida::select( 'id_accion_centralizada', 'id_accion', 'co_partida', 'monto', 'edo_reg', 
                    'fecha_creacion', 'fecha_actualizacion', 'id_tab_ejercicio_fiscal', 
                    'nu_aplicacion', 'de_denominacion')
                    ->where('id_accion_centralizada', '=', $lista_ac->id )
                    ->where('id_accion', '=', $lista_ac_ae->id_accion )
                    ->orderby('id_accion_centralizada','ASC')
                    ->get();

                    foreach ($tab_ac_ae_partida as $lista_ac_ae_partida){
                        $replica_ac_ae_partida = new tab_ac_ae_partida;
                        $replica_ac_ae_partida->id_accion_centralizada = $replica_tab_ac->id;
                        $replica_ac_ae_partida->id_accion = $lista_ac_ae_partida->id_accion;
                        $replica_ac_ae_partida->co_partida = $lista_ac_ae_partida->co_partida;
                        $replica_ac_ae_partida->monto = $lista_ac_ae_partida->monto;
                        $replica_ac_ae_partida->edo_reg = $lista_ac_ae_partida->edo_reg;
                        //$replica_ac_ae_partida->fecha_creacion = $lista_ac_ae_partida->fecha_creacion;
                        //$replica_ac_ae_partida->fecha_actualizacion = $lista_ac_ae_partida->fecha_actualizacion;
                        $replica_ac_ae_partida->id_tab_ejercicio_fiscal = $ejercicio_replica;
                        $replica_ac_ae_partida->nu_aplicacion = $lista_ac_ae_partida->nu_aplicacion;
                        $replica_ac_ae_partida->de_denominacion = $lista_ac_ae_partida->de_denominacion;
                        $replica_ac_ae_partida->save();
                    }

                    $tab_meta_fisica = tab_meta_fisica::select( 'co_metas', 'id_accion_centralizada', 'co_ac_acc_espec', 'codigo', 'nb_meta', 
                    'co_unidades_medida', 'tx_prog_anual', 'fecha_inicio', 'fecha_fin', 'nb_responsable', 
                    'fecha_creacion', 'fecha_actualizacion', 'edo_reg')
                    ->where('id_accion_centralizada', '=', $lista_ac->id )
                    ->where('co_ac_acc_espec', '=', $lista_ac_ae->id_accion )
                    ->orderby('co_metas','ASC')
                    ->get();

                    foreach ($tab_meta_fisica as $lista_meta_fisica){
                        $replica_meta_fisica = new tab_meta_fisica;
                        $replica_meta_fisica->id_accion_centralizada = $replica_tab_ac->id;
                        $replica_meta_fisica->co_ac_acc_espec = $lista_meta_fisica->co_ac_acc_espec;
                        $replica_meta_fisica->codigo = $lista_meta_fisica->codigo;
                        $replica_meta_fisica->nb_meta = $lista_meta_fisica->nb_meta;
                        $replica_meta_fisica->co_unidades_medida = $lista_meta_fisica->co_unidades_medida;
                        $replica_meta_fisica->tx_prog_anual = $lista_meta_fisica->tx_prog_anual;
                        $replica_meta_fisica->fecha_inicio = $fecha_ini;
                        $replica_meta_fisica->fecha_fin = $fecha_fin;
                        $replica_meta_fisica->nb_responsable = $lista_meta_fisica->nb_responsable;
                        //$replica_meta_fisica->fecha_creacion = $lista_meta_fisica->fecha_creacion;
                        //$replica_meta_fisica->fecha_actualizacion = $lista_meta_fisica->fecha_actualizacion;
                        $replica_meta_fisica->edo_reg = $lista_meta_fisica->edo_reg;
                        $replica_meta_fisica->save();

                        $tab_meta_financiera = tab_meta_financiera::select( 'co_metas_detalle', 'co_metas', 'co_municipio', 'co_parroquia', 'mo_presupuesto', 
                        'co_partida', 'co_fuente', 'fecha_creacion', 'fecha_actualizacion', 'edo_reg')
                        ->where('co_metas', '=', $lista_meta_fisica->co_metas )
                        ->orderby('co_metas_detalle','ASC')
                        ->get();

                        foreach ($tab_meta_financiera as $lista_meta_financiera){
                            $replica_meta_financiera = new tab_meta_financiera;
                            $replica_meta_financiera->co_metas = $replica_meta_fisica->co_metas;
                            $replica_meta_financiera->co_municipio = $lista_meta_financiera->co_municipio;
                            $replica_meta_financiera->co_parroquia = $lista_meta_financiera->co_parroquia;
                            $replica_meta_financiera->mo_presupuesto = $lista_meta_financiera->mo_presupuesto;
                            $replica_meta_financiera->co_partida = $lista_meta_financiera->co_partida;
                            $replica_meta_financiera->co_fuente = $lista_meta_financiera->co_fuente;
                            //$replica_meta_financiera->fecha_creacion = $lista_meta_financiera->fecha_creacion;
                            //$replica_meta_financiera->fecha_actualizacion = $lista_meta_financiera->fecha_actualizacion;
                            $replica_meta_financiera->edo_reg = $lista_meta_financiera->edo_reg;
                            $replica_meta_financiera->save();

                        }

                    }

                }

                DB::commit();
    
                $this->info($contador.'- AC: '.$lista_ac->id.' replicado.');
    
                $contador = $contador+1;
    
            }
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();
            $this->info(utf8_encode( $e->getMessage()));
        }
    }
}
