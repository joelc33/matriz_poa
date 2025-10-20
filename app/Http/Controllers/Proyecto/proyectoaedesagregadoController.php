<?php

namespace matriz\Http\Controllers\Proyecto;

//*******agregar esta linea******//
use matriz\Models\Proyecto\tab_proyecto_ae_partida_desagregado;
use matriz\Models\Proyecto\tab_proyecto_ae;
use View;
use Validator;
use Input;
use Response;
use DB;
use Session;
use PHPExcel_IOFactory;
use PHPExcel;
use PHPExcel_Writer_Excel2007;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Border;
use PHPExcel_Style_Fill;
use PHPExcel_Cell_DataType;
use Maatwebsite\Excel\Facades\Excel;
//*******************************//
use Illuminate\Http\Request;

use matriz\Http\Requests;
use matriz\Http\Controllers\Controller;

class proyectoaedesagregadoController extends Controller
{
    protected $tab_proyecto_ae_partida_desagregado;

    public function __construct(tab_proyecto_ae_partida_desagregado $tab_proyecto_ae_partida_desagregado)
    {
        $this->middleware('auth');
        $this->tab_proyecto_ae_partida_desagregado = $tab_proyecto_ae_partida_desagregado;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function storeLista()
    {
        try {
            $start  = Input::get('start', 0);
            $limit  = Input::get('limit', 30);
            $variable = Input::get('variable');
            $ae = Input::get('ae');

            $tab_proyecto_ae_partida_desagregado = $this->tab_proyecto_ae_partida_desagregado
            ->select(
                'id',
                'co_partida',
                'id_tab_ejercicio_fiscal',
                'de_denominacion',
                'nu_aplicacion',
                'mo_partida',
                'in_activo'
            )
            ->where('id_tab_proyecto_ae', '=', $ae);

            if (Input::get("BuscarBy")=="true") {

                if($variable!="") {
                    $tab_proyecto_ae_partida_desagregado->where('de_denominacion', 'ILIKE', "%$variable%");
                }

                $response['success']  = 'true';
                $response['total'] = $tab_proyecto_ae_partida_desagregado->count();
                $tab_proyecto_ae_partida_desagregado->skip($start)->take($limit);
                $response['data']  = $tab_proyecto_ae_partida_desagregado->orderby('id', 'ASC')->get()->toArray();
            } else {
                $response['success']  = 'true';
                $response['total'] = $tab_proyecto_ae_partida_desagregado->count();
                $tab_proyecto_ae_partida_desagregado->skip($start)->take($limit);
                $response['data']  = $tab_proyecto_ae_partida_desagregado->orderby('id', 'ASC')->get()->toArray();
            }

            return Response::json($response, 200);
        } catch (\Illuminate\Database\QueryException $e) {
            return Response::json(array('success' => false, 'message' => utf8_encode($e->getMessage())), 200);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function editar()
    {
        $data = tab_proyecto_ae::select(
            'co_proyecto_acc_espec',
            'tx_codigo',
            'id_proyecto',
            'descripcion',
            'co_unidades_medida',
            'meta',
            'ponderacion',
            'bien_servicio',
            'total',
            'fec_inicio',
            'fec_termino',
            'co_ejecutores',
            'fecha_creacion',
            'fecha_actualizacion',
            'edo_reg',
            'tx_objetivo_institucional',
            'id_padre',
            'in_definitivo'
        )
        ->where('co_proyecto_acc_espec', '=', Input::get('codigo'))
        ->first();
        return View::make('proyecto.ae.desagregado.editar')->with('data', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function lista()
    {
        $data = tab_proyecto_ae::select(
            'co_proyecto_acc_espec',
            'tx_codigo',
            'id_proyecto',
            'descripcion',
            'co_unidades_medida',
            'meta',
            'ponderacion',
            'bien_servicio',
            'total',
            'fec_inicio',
            'fec_termino',
            'co_ejecutores',
            'fecha_creacion',
            'fecha_actualizacion',
            'edo_reg',
            'tx_objetivo_institucional',
            'id_padre',
            'in_definitivo'
        )
        ->where('co_proyecto_acc_espec', '=', Input::get('codigo'))
        ->first();
        return View::make('proyecto.ae.desagregado.lista')->with('data', $data);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function procesarDesagregado()
    {

        $ae = Input::get('ae');

        $file = Input::file('archivo');

        $validator = Validator::make(
            ['file'      => $file, 'extension' => strtolower($file->getClientOriginalExtension()),],
            ['file'=> 'required', 'extension' => 'required|in:xls,xlsx', ]
        );

        if ($validator->fails()) {
            $data = json_encode(array('success' => false, 'msg' => $validator->getMessageBag()->toArray()));
            $response = Response::make($data);
            $response->header('Content-Type', 'text/html');
            return $response;
        } else {
            try {
                //*************Inicio de Carga Masiva*************//
                $path = Input::file('archivo')->getRealPath();

                $data = Excel::load($path, function ($reader) { })->get();

                //dd($data);

                if(!empty($data) && $data->count()) {
                    foreach ($data as $key => $value) {

                        $insert[] = [
                          'ae' => $ae,
                          'pa' => $value->pa,
                          'ge' => $value->ge,
                          'es' => $value->es,
                          'se' => $value->se,
                          'sse' => $value->sse,
                          'partida' => $value->pa.$value->ge.$value->es.$value->se.$value->sse,
                          'aplicacion' => $value->aplicacion,
                          'denominacion' => $value->denominacion,
                          'monto' => $value->monto
                           ];
                    }

                    //dd($insert);

                    if(!empty($insert)) {
                        DB::beginTransaction();

                        //Actualizar todos los registros a false
                        $borrar_ac_ae_partida = tab_proyecto_ae_partida_desagregado::where('id_tab_proyecto_ae', '=', $ae)
                        ->delete();

                        try {
                            $i=0;
                            $acumulado=0;

                            $consulta_ac_ae = tab_proyecto_ae::select('total as monto')
                            ->where('co_proyecto_acc_espec', '=', $ae)
                            ->first();

                            foreach ($insert as $key => $valor) {
                                $i++;
                                $validarDetalle = Validator::make($valor, tab_proyecto_ae_partida_desagregado::$validarDesagregado);
                                if ($validarDetalle->fails()) {

                                    DB::rollback();

                                    $mensaje_error = array_merge_recursive($validarDetalle->getMessageBag()->toArray(), array('linea' => 'Ubicado en linea N°: '.$i ));
                                    $data = json_encode(array('success' => false, 'msg' => $mensaje_error));
                                    $response = Response::make($data);
                                    $response->header('Content-Type', 'text/html');
                                    return $response;

                                } else {

                                    $acumulado = $acumulado + floatval($valor['monto']);

                                    $partidaCrear = $valor['pa'].$valor['ge'].$valor['es'].$valor['se'].$valor['sse'];

                                    /*if (tab_partidas::where('id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))
                                    ->where('co_partida', '=', $partidaCrear)
                                    ->exists()) {

                                    }else{

                                      $tabla = new tab_partidas;
                                      $tabla->id_tab_ejercicio_fiscal = Session::get('ejercicio');
                                      $tabla->co_partida = $partidaCrear;
                                      $tabla->tx_nombre = $valor['denominacion'];
                                      $tabla->ace_mov = TRUE;
                                      $tabla->in_activo = TRUE;
                                      $tabla->save();

                                    }*/

                                    $partida = new tab_proyecto_ae_partida_desagregado();
                                    $partida->id_tab_proyecto_ae = $valor['ae'];
                                    $partida->id_tab_ejercicio_fiscal = Session::get('ejercicio');
                                    $partida->nu_aplicacion = $valor['aplicacion'];
                                    $partida->de_denominacion = $valor['denominacion'];
                                    $partida->co_partida = $partidaCrear;
                                    $partida->mo_partida = floatval($valor['monto']);
                                    $partida->in_activo = true;
                                    $partida->save();

                                }
                            }

                            if($acumulado<>$consulta_ac_ae->monto) {

                                DB::rollback();

                                $mensaje_ae_partida = array(
                                  'in'=>'*El monto de la Accion Especifica, no Coincide con el total de sus partidas. <br>Monto Accion Esp.: '.number_format($consulta_ac_ae->monto, 2, ',', '.').''.'<br>Monto Partidas: '.number_format($acumulado, 2, ',', '.').''.'<br>Diferencia: '.number_format(($consulta_ac_ae->monto - $acumulado), 2, ',', '.').''
                                );

                                $data = json_encode(array('success' => false, 'msg' => $mensaje_ae_partida));
                                $response = Response::make($data);
                                $response->header('Content-Type', 'text/html');
                                return $response;

                            }

                            DB::commit();

                            $data = json_encode(array('success' => true, 'msg' => 'Archivo procesado exitosamente!'));
                            $response = Response::make($data);
                            $response->header('Content-Type', 'text/html');
                            return $response;

                        } catch (\Illuminate\Database\QueryException $e) {
                            DB::rollback();

                            $data = json_encode(array('success' => false, 'msg' => array('ERROR ('.$e->getCode().'):'=> $e->getMessage())));
                            $response = Response::make($data);
                            $response->header('Content-Type', 'text/html');
                            return $response;
                        }
                    }
                } else {

                    $data = json_encode(array('success' => false, 'msg' => array('ERROR (error):'=> 'El Archivo no contiene registros')));
                    $response = Response::make($data);
                    $response->header('Content-Type', 'text/html');
                    return $response;

                }

            } catch (\Exception $e) {
                $data = json_encode(array('success' => false, 'msg' => array('ERROR ('.$e->getCode().'):'=> $e->getMessage())));
                $response = Response::make($data);
                $response->header('Content-Type', 'text/html');
                return $response;
            }
        }
    }

}
