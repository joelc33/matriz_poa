<?php

namespace matriz\Http\Controllers\Mantenimiento;

//*******agregar esta linea******//
use matriz\Models\Mantenimiento\tab_objetivo_sectorial;
use View;
use Validator;
use Input;
use Response;
use DB;
use Session;
//*******************************//
use Illuminate\Http\Request;

use matriz\Http\Requests;
use matriz\Http\Controllers\Controller;

class objetivosectorialController extends Controller
{
    protected $tab_objetivo_sectorial;

    public function __construct(tab_objetivo_sectorial $tab_objetivo_sectorial)
    {
        $this->middleware('auth');
        $this->tab_objetivo_sectorial = $tab_objetivo_sectorial;
    }

    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function lista()
    {
        return View::make('mantenimiento.objetivosectorial.lista');
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

            $tab_objetivo_sectorial = $this->tab_objetivo_sectorial
            ->join('mantenimiento.tab_sectores as t01', 't01.id', '=', 'mantenimiento.tab_objetivo_sectorial.id_tab_sectores')
            ->select(
                'mantenimiento.tab_objetivo_sectorial.id',
                'id_tab_ejercicio_fiscal',
                'id_tab_sectores',
                'de_objetivo_sectorial',
                'mantenimiento.tab_objetivo_sectorial.in_activo',
                'nu_descripcion'
            )
            ->where('id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'));

            if (Input::get("BuscarBy")=="true") {

                if($variable!="") {
                    $tab_objetivo_sectorial->where('nu_descripcion', 'ILIKE', "%$variable%");
                }

                $response['success']  = 'true';
                $response['total'] = $tab_objetivo_sectorial->count();
                $tab_objetivo_sectorial->skip($start)->take($limit);
                $response['data']  = $tab_objetivo_sectorial->orderby('mantenimiento.tab_objetivo_sectorial.id', 'ASC')->get()->toArray();
            } else {
                $response['success']  = 'true';
                $response['total'] = $tab_objetivo_sectorial->count();
                $tab_objetivo_sectorial->skip($start)->take($limit);
                $response['data']  = $tab_objetivo_sectorial->orderby('mantenimiento.tab_objetivo_sectorial.id', 'ASC')->get()->toArray();
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
        $data = json_encode(array("id" => "", "id_tab_ejercicio_fiscal" => Session::get('ejercicio'), "id_tab_sectores" => ""));
        return View::make('mantenimiento.objetivosectorial.editar')->with('data', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function editar($id)
    {
        $data = tab_objetivo_sectorial::select(
            'id',
            'id_tab_ejercicio_fiscal',
            'id_tab_sectores',
            'de_objetivo_sectorial',
            'in_activo'
        )
        ->where('id', '=', $id)
        ->first();
        return View::make('mantenimiento.objetivosectorial.editar')->with('data', $data);
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
                    $validator= Validator::make(Input::all(), tab_objetivo_sectorial::$validarEditar);
                    if ($validator->fails()) {
                        return Response::json(array(
                          'success' => false,
                          'msg' => $validator->getMessageBag()->toArray()
                        ));
                    }
                    $tabla = tab_objetivo_sectorial::find($id);
                    $tabla->id_tab_sectores = Input::get("id_tab_sectores");
                    $tabla->de_objetivo_sectorial = Input::get("objetivo");
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

                    // 5.1 or newer
                    Validator::extend('composite_unique', function ($attribute, $value, $parameters, $validator) {
                        // remove first parameter and assume it is the table name
                        $table = array_shift($parameters);
                        // start building the conditions
                        $fields = [ $attribute => $value ]; // current field, company_code in your case
                        // iterates over the other parameters and build the conditions for all the required fields
                        while ($field = array_shift($parameters)) {
                            $fields[ $field ] = \Request::get($field);
                        }
                        // query the table with all the conditions
                        $result = DB::table($table)->select(DB::raw(1))->where($fields)->first();

                        return empty($result); // edited here
                    }, 'El objetivo para este :attribute ya esta registrado favor verificar.');

                    $validator = Validator::make(Input::all(), tab_objetivo_sectorial::$validarCrear);
                    if ($validator->fails()) {
                        return Response::json(array(
                          'success' => false,
                          'msg' => $validator->getMessageBag()->toArray()
                        ));
                    }
                    $tabla = new tab_objetivo_sectorial();
                    $tabla->id_tab_ejercicio_fiscal = Session::get('ejercicio');
                    $tabla->id_tab_sectores = Input::get("id_tab_sectores");
                    $tabla->de_objetivo_sectorial = Input::get("objetivo");
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
                $tabla = tab_objetivo_sectorial::find(Input::get("id"));
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
                $tabla = tab_objetivo_sectorial::find(Input::get("id"));
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
