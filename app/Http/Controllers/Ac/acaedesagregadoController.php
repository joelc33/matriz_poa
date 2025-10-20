<?php

namespace matriz\Http\Controllers\Ac;

//*******agregar esta linea******//
use matriz\Models\Ac\tab_ac_es_partida_desagregado;
use matriz\Models\Mantenimiento\tab_ac_ae_partida as mmt_ac_ae_partida;
use matriz\Models\Mantenimiento\tab_ac_ae_predefinida;
use matriz\Models\Ac\tab_ac_ae;
use matriz\Models\Ac\tab_ac;
use matriz\Models\Mantenimiento\tab_partidas;
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

class acaedesagregadoController extends Controller
{
    protected $tab_ac_es_partida_desagregado;

    public function __construct(tab_ac_es_partida_desagregado $tab_ac_es_partida_desagregado)
    {
        $this->middleware('auth');
        $this->tab_ac_es_partida_desagregado = $tab_ac_es_partida_desagregado;
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
            $ac = Input::get('ac');
            $ae = Input::get('ae');

            $tab_ac_es_partida_desagregado = $this->tab_ac_es_partida_desagregado
            ->select(
                'id',
                'td_tab_ac',
                'id_tab_ac_ae_predefinida',
                'co_partida',
                'id_tab_ejercicio_fiscal',
                'de_denominacion',
                'nu_aplicacion',
                'mo_partida',
                'in_activo'
            )
            ->where('td_tab_ac', '=', $ac)
            ->where('id_tab_ac_ae_predefinida', '=', $ae);

            if (Input::get("BuscarBy")=="true") {

                if($variable!="") {
                    $tab_ac_es_partida_desagregado->where('de_denominacion', 'ILIKE', "%$variable%");
                }

                $response['success']  = 'true';
                $response['total'] = $tab_ac_es_partida_desagregado->count();
                $tab_ac_es_partida_desagregado->skip($start)->take($limit);
                $response['data']  = $tab_ac_es_partida_desagregado->orderby('id', 'ASC')->get()->toArray();
            } else {
                $response['success']  = 'true';
                $response['total'] = $tab_ac_es_partida_desagregado->count();
                $tab_ac_es_partida_desagregado->skip($start)->take($limit);
                $response['data']  = $tab_ac_es_partida_desagregado->orderby('id', 'ASC')->get()->toArray();
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
    public function procesarDesagregado()
    {

        $ac = Input::get('ac');
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
                            'ac' => $ac,
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
                        $borrar_ac_ae_partida = tab_ac_es_partida_desagregado::where('td_tab_ac', '=', $ac)
                        ->where('id_tab_ac_ae_predefinida', '=', $ae)
                        ->delete();

                        try {
                            $i=0;
                            $acumulado=0;

                            $consulta_ac_ae = tab_ac_ae::select('monto')
                            ->where('id_accion_centralizada', '=', $ac)
                            ->where('id_accion', '=', $ae)
                            ->first();

                            foreach ($insert as $key => $valor) {
                                $i++;
                                $validarDetalle = Validator::make($valor, tab_ac_es_partida_desagregado::$validarDesagregado);
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

                                    $partida = new tab_ac_es_partida_desagregado();
                                    $partida->td_tab_ac = $valor['ac'];
                                    $partida->id_tab_ac_ae_predefinida = $valor['ae'];
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
