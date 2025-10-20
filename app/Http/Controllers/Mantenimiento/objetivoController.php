<?php

namespace matriz\Http\Controllers\Mantenimiento;

//*******agregar esta linea******//
use matriz\Models\Mantenimiento\tab_planes;
use View;
use Validator;
use Input;
use Response;
use DB;
//*******************************//
use Illuminate\Http\Request;

use matriz\Http\Requests;
use matriz\Http\Controllers\Controller;

class objetivoController extends Controller
{
    protected $tab_planes;

    public function __construct(tab_planes $tab_planes)
    {
        $this->middleware('auth');
        $this->tab_planes = $tab_planes;
    }

    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function lista()
    {
        return View::make('mantenimiento.objetivo.lista');
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
            $limit  = Input::get('limit', 20);
            $variable = Input::get('variable');

            $tab_planes = $this->tab_planes
            ->select(
                'id',
                'co_objetivo_historico',
                'co_objetivo_nacional',
                'co_objetivo_estrategico',
                'co_objetivo_general',
                'nu_nivel',
                'tx_codigo',
                'nu_codigo',
                'tx_descripcion',
                'in_activo',
                'id_tab_ejercicio_fiscal'
            );

            if (Input::get("BuscarBy")=="true") {

                if($variable!="") {
                    $tab_planes->where('tx_descripcion', 'ILIKE', "%$variable%");
                }

                $response['success']  = 'true';
                $response['total'] = $tab_planes->count();
                $tab_planes->skip($start)->take($limit);
                $response['data']  = $tab_planes->orderby('id', 'ASC')->get()->toArray();
            } else {
                $response['success']  = 'true';
                $response['total'] = $tab_planes->count();
                $tab_planes->skip($start)->take($limit);
                $response['data']  = $tab_planes->orderby('id', 'ASC')->get()->toArray();
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
    public function nuevo()
    {
        $data = json_encode(array("id" => ""));
        return View::make('mantenimiento.objetivo.editar')->with('data', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function editar($id)
    {
        $data = tab_planes::select(
            'id',
            'co_objetivo_historico',
            'co_objetivo_nacional',
            'co_objetivo_estrategico',
            'co_objetivo_general',
            'nu_nivel',
            'tx_codigo',
            'nu_codigo',
            'tx_descripcion',
            'in_activo',
            'id_tab_ejercicio_fiscal'
        )
        ->where('id', '=', $id)
        ->first();
        return View::make('mantenimiento.objetivo.editar')->with('data', $data);
    }

        /**
         * Update the specified resource in storage.
         *
         * @param  int  $id
         * @return Response
         */
        public function guardar($id = null)
        {
            DB::beginTransaction();
            if($id!=''||$id!=null) {

                try {
                    $validator= Validator::make(Input::all(), tab_planes::$validarEditar);
                    if ($validator->fails()) {
                        return Response::json(array(
                          'success' => false,
                          'msg' => $validator->getMessageBag()->toArray()
                        ));
                    }

                    $ejercicio = Input::get('id_tab_ejercicio_fiscal');
                    // Can't save a null value
                    if (!$ejercicio) {
                        $ejercicio = [];
                    }

                    $ejercicio = json_encode($ejercicio);
                    $ejercicio = "'".preg_replace("#^\[(.*)\]$#", '{\1}', $ejercicio)."'";
                    $ejercicio = DB::raw($ejercicio);

                    $tabla = tab_planes::find($id);
                    $tabla->co_objetivo_historico = Input::get("objetivo_historico");
                    $tabla->co_objetivo_nacional = Input::get("objetivo_nacional");
                    $tabla->co_objetivo_estrategico = Input::get("objetivo_estrategico");
                    $tabla->co_objetivo_general = Input::get("objetivo_general");
                    $tabla->nu_nivel = Input::get("nivel");
                    $tabla->tx_codigo = Input::get("codigo");
                    $tabla->nu_codigo = Input::get("objetivo_historico").'.'.Input::get("objetivo_nacional").'.'.Input::get("objetivo_estrategico").'.'.Input::get("objetivo_general");
                    $tabla->tx_descripcion = Input::get("descripcion");
                    $tabla->id_tab_ejercicio_fiscal = $ejercicio;
                    $tabla->save();

                    DB::commit();
                    return Response::json(array(
                      'success' => true,
                      'msg' => 'Registro Editado con Exito!'
                    ));

                } catch (\Illuminate\Database\QueryException $e) {
                    DB::rollback();
                    return Response::json(array(
                      'success' => false,
                      'msg' => array('ERROR ('.$e->getCode().'):'=> $e->getMessage())
                    ));
                }

            } else {

                try {
                    $validator = Validator::make(Input::all(), tab_planes::$validarCrear);
                    if ($validator->fails()) {
                        return Response::json(array(
                          'success' => false,
                          'msg' => $validator->getMessageBag()->toArray()
                        ));
                    }

                    $ejercicio = Input::get('id_tab_ejercicio_fiscal');
                    // Can't save a null value
                    if (!$ejercicio) {
                        $ejercicio = [];
                    }

                    $ejercicio = json_encode($ejercicio);
                    $ejercicio = "'".preg_replace("#^\[(.*)\]$#", '{\1}', $ejercicio)."'";
                    $ejercicio = DB::raw($ejercicio);

                    $tabla = new tab_planes();
                    $tabla->co_objetivo_historico = Input::get("objetivo_historico");
                    $tabla->co_objetivo_nacional = Input::get("objetivo_nacional");
                    $tabla->co_objetivo_estrategico = Input::get("objetivo_estrategico");
                    $tabla->co_objetivo_general = Input::get("objetivo_general");
                    $tabla->nu_nivel = Input::get("nivel");
                    $tabla->tx_codigo = Input::get("codigo");
                    $tabla->nu_codigo = Input::get("objetivo_historico").'.'.Input::get("objetivo_nacional").'.'.Input::get("objetivo_estrategico").'.'.Input::get("objetivo_general");
                    $tabla->tx_descripcion = Input::get("codigo").'. - '.Input::get("descripcion");
                    $tabla->id_tab_ejercicio_fiscal = $ejercicio;
                    $tabla->in_activo = 'TRUE';
                    $tabla->save();

                    DB::commit();
                    return Response::json(array(
                      'success' => true,
                      'msg' => 'Registro Guardado con Exito!'
                    ));

                } catch (\Illuminate\Database\QueryException $e) {
                    DB::rollback();
                    return Response::json(array(
                      'success' => false,
                      'msg' => array('ERROR ('.$e->getCode().'):'=> $e->getMessage())
                    ));
                }
            }
        }

        /**
         * Show the form for creating a new resource.
         *
         * @return Response
         */
        public function eliminar()
        {
            DB::beginTransaction();
            try {
                $tabla = tab_planes::find(Input::get("id"));
                $tabla->in_activo = 'FALSE';
                $tabla->save();
                DB::commit();

                $response['success']  = 'true';
                $response['msg']  = 'Registro Deshabilitado con Exito!';
                return Response::json($response, 200);

            } catch (\Illuminate\Database\QueryException $e) {
                DB::rollback();

                $response['success']  = 'false';
                $response['msg']  = array('ERROR ('.$e->getCode().'):'=> $e->getMessage());
                return Response::json($response, 200);
            }
        }

        /**
         * Show the form for creating a new resource.
         *
         * @return Response
         */
        public function habilitar()
        {
            DB::beginTransaction();
            try {
                $tabla = tab_planes::find(Input::get("id"));
                $tabla->in_activo = 'TRUE';
                $tabla->save();
                DB::commit();

                $response['success']  = 'true';
                $response['msg']  = 'Registro Habilitado con Exito!';
                return Response::json($response, 200);

            } catch (\Illuminate\Database\QueryException $e) {
                DB::rollback();

                $response['success']  = 'false';
                $response['msg']  = array('ERROR ('.$e->getCode().'):'=> $e->getMessage());
                return Response::json($response, 200);
            }
        }
}
