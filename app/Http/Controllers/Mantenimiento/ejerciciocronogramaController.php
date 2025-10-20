<?php

namespace matriz\Http\Controllers\Mantenimiento;

//*******agregar esta linea******//
use matriz\Models\Mantenimiento\tab_apertura_ef;
use View;
use Validator;
use Input;
use Response;
use DB;
//*******************************//
use Illuminate\Http\Request;

use matriz\Http\Requests;
use matriz\Http\Controllers\Controller;

class ejerciciocronogramaController extends Controller
{
    protected $tab_apertura_ef;

    public function __construct(tab_apertura_ef $tab_apertura_ef)
    {
        $this->middleware('auth');
        $this->tab_apertura_ef = $tab_apertura_ef;
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

            $tab_apertura_ef = $this->tab_apertura_ef
            ->select(
                'id',
                'de_apertura',
                DB::raw("to_char(fe_desde, 'dd/mm/YYYY') as fe_desde"),
                DB::raw("to_char(fe_hasta, 'dd/mm/YYYY') as fe_hasta")
            )
            ->where('in_activo', '=', true)
            ->where('id_tab_ejercicio_fiscal', '=', Input::get('ejercicio'));

            if (Input::get("BuscarBy")=="true") {

                if($variable!="") {
                    $tab_apertura_ef->where('de_apertura', 'ILIKE', "%$variable%");
                }

                $response['success']  = 'true';
                $response['total'] = $tab_apertura_ef->count();
                $tab_apertura_ef->skip($start)->take($limit);
                $response['data']  = $tab_apertura_ef->orderby('id', 'ASC')->get()->toArray();
            } else {
                $response['success']  = 'true';
                $response['total'] = $tab_apertura_ef->count();
                $tab_apertura_ef->skip($start)->take($limit);
                $response['data']  = $tab_apertura_ef->orderby('id', 'ASC')->get()->toArray();
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

        $ejercicio = json_encode(array("id_tab_ejercicio_fiscal" => Input::get('ejercicio')));

        $fechaI = '01-01-'.((empty(Input::get('ejercicio')) ? $ejercicio->id_tab_ejercicio_fiscal : Input::get('ejercicio'))-1);
        $fechaF = '31-12-'.((empty(Input::get('ejercicio')) ? $ejercicio->id_tab_ejercicio_fiscal : Input::get('ejercicio'))-1);

        $data = json_encode(array(
          "id_tab_ejercicio_fiscal" => Input::get('ejercicio'),
          "fe_ini" => $fechaI,
          "fe_fin" => $fechaF
        ));

        return View::make('mantenimiento.ejercicio.cronograma.editar')->with('data', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function editar($id)
    {
        $data = tab_apertura_ef::select(
            'id',
            'id_tab_ejercicio_fiscal',
            'fe_desde',
            'fe_hasta',
            'de_apertura',
            'in_activo'
        )
        ->where('id', '=', $id)
        ->first();

        $fechaI = '01-01-'.($data->id_tab_ejercicio_fiscal-1);
        $fechaF = '31-12-'.($data->id_tab_ejercicio_fiscal-1);

        $limite = array('fe_ini' => $fechaI, 'fe_fin' => $fechaF );

//        $data = json_encode(array_merge($data->toArray(), $limite));

        return View::make('mantenimiento.ejercicio.cronograma.editar')->with('data', $data);
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
                $validator= Validator::make(Input::all(), tab_apertura_ef::$validarEditar);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }
                $tabla = tab_apertura_ef::find($id);
                $tabla->fe_desde = Input::get("fecha_apertura");
                $tabla->fe_hasta = Input::get("fecha_cierre");
                $tabla->de_apertura = Input::get("descripcion");
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
                $validator = Validator::make(Input::all(), tab_apertura_ef::$validarCrear);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }
                $tabla = new tab_apertura_ef();
                $tabla->id_tab_ejercicio_fiscal = Input::get("periodo");
                $tabla->fe_desde = Input::get("fecha_apertura");
                $tabla->fe_hasta = Input::get("fecha_cierre");
                $tabla->de_apertura = Input::get("descripcion");
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
            $tabla = tab_apertura_ef::find(Input::get("periodo"));
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

}
