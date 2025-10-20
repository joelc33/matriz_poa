<?php

namespace matriz\Http\Controllers\Ac;

//*******agregar esta linea******//
use matriz\Models\Ac\tab_ac_ae_partida;
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

class acaepartidaController extends Controller
{
    protected $tab_ac_ae_partida;

    public function __construct(tab_ac_ae_partida $tab_ac_ae_partida)
    {
        $this->middleware('auth');
        $this->tab_ac_ae_partida = $tab_ac_ae_partida;
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
            $ac = Input::get('id_accion_centralizada');
            $ae = Input::get('id_accion_especifica');

            $tab_ac_ae_partida = $this->tab_ac_ae_partida
            //->join('mantenimiento.tab_partidas as t01','t01.co_partida','=','public.t54_ac_ae_partidas.co_partida')
            ->join('mantenimiento.tab_partidas as t01', function ($j) {
                $j->on('t01.co_partida', '=', 'public.t54_ac_ae_partidas.co_partida')
                  ->on('t01.id_tab_ejercicio_fiscal', '=', 'public.t54_ac_ae_partidas.id_tab_ejercicio_fiscal');
            })
            ->select('public.t54_ac_ae_partidas.co_partida', 'tx_nombre', 'monto')
            ->where('id_accion_centralizada', '=', $ac)
            ->where('id_accion', '=', $ae);

            if (Input::get("BuscarBy")=="true") {

                if($variable!="") {
                    $tab_ac_ae_partida->where('public.t54_ac_ae_partidas.co_partida', 'ILIKE', "%$variable%");
                }

                $response['success']  = 'true';
                $response['total'] = $tab_ac_ae_partida->count();
                $tab_ac_ae_partida->skip($start)->take($limit);
                $response['data']  = $tab_ac_ae_partida->orderby('public.t54_ac_ae_partidas.co_partida', 'ASC')->get()->toArray();
            } else {
                $response['success']  = 'true';
                $response['total'] = $tab_ac_ae_partida->count();
                $tab_ac_ae_partida->skip($start)->take($limit);
                $response['data']  = $tab_ac_ae_partida->orderby('public.t54_ac_ae_partidas.co_partida', 'ASC')->get()->toArray();
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
    public function procesarMasivo()
    {

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

                //Funciones extras
                function get_cell($cell, $objPHPExcel)
                {
                    //seleccionar una celda
                    $objCell = ($objPHPExcel->getActiveSheet()->getCell($cell));
                    //tomar valor de la celda
                    return $objCell->getvalue();
                }

                function pp(&$var)
                {
                    $var = chr(ord($var)+1);
                    return true;
                }

                if(strtolower($file->getClientOriginalExtension()) == 'xls') {
                    // Extension excel 97
                    $ext = 'Excel5';
                } elseif(strtolower($file->getClientOriginalExtension()) == 'xlsx') {
                    // Extension excel 2007 y 2010
                    $ext = 'Excel2007';
                }

                //creando el lector
                $objReader = PHPExcel_IOFactory::createReader($ext);

                //cargamos el archivo
                $objPHPExcel = $objReader->load($path);

                $dim = $objPHPExcel->getActiveSheet()->calculateWorksheetDimension();

                // list coloca en array $start y $end
                list($start, $end) = explode(':', $dim);

                if(!preg_match('#([A-Z]+)([0-9]+)#', $start, $rslt)) {
                    return false;
                }
                list($start, $start_h, $start_v) = $rslt;
                if(!preg_match('#([A-Z]+)([0-9]+)#', $end, $rslt)) {
                    return false;
                }
                list($end, $end_h, $end_v) = $rslt;

                $contador = 0;
                $abecedario = range('F', 'Z');

                DB::beginTransaction();
                try {

                    $borrar_ac_ae_partida = tab_ac_ae_partida::where('id_accion_centralizada', '=', Input::get('accion_centralizada'))->delete();

                    foreach($abecedario as $abc) {
                        $contenido = get_cell($abc.'9', $objPHPExcel);
                        if($contenido!=''||$contenido!=null) {
                            $contador = $contador+1;

                            $consulta_ae = tab_ac_ae::select('id_accion')
                            ->join('mantenimiento.tab_ac_ae_predefinida as t01', 't01.id', '=', 'public.t47_ac_accion_especifica.id_accion')
                            ->where('id_accion_centralizada', '=', Input::get('accion_centralizada'))
                            ->where('nu_numero', '=', $contador)
                            ->first();

                            //empieza  lectura vertical
                            $start_v=10;
                            $end_v=2006;

                            for($v=$start_v; $v<=$end_v; $v++) {
                                //empieza lectura horizontal
                                for($h=$start_h; ord($h)<=ord($end_h); pp($h)) {
                                    $cellValue1 = get_cell("A".$v, $objPHPExcel);
                                    $cellValue2 = get_cell("B".$v, $objPHPExcel);
                                    $cellValue3 = get_cell("C".$v, $objPHPExcel);
                                    $cellValue4 = get_cell("D".$v, $objPHPExcel);
                                    $cellValue5 = get_cell("E".$v, $objPHPExcel);
                                    //$cellValue6 = get_cell("F".$v, $objPHPExcel);
                                    //$cellValue7 = get_cell("G".$v, $objPHPExcel);
                                    $cellValue8 = get_cell($abc.$v, $objPHPExcel);
                                }

                                if ($cellValue8>0) {

                                    $mensajes = array(
                                      'monto.regex'=>'En la celda: '.$abc.$v.' el monto no debe poseer decimales.',
                                      //'aplicacion.required'=>'Para la celda: '.$abc.$v.' el campo Aplicacion es requerido.',
                                      //'aplicacion.exists'=>'Para la celda: '.$abc.$v.' el codigo de aplicacion no existe por favor verificar.',
                                      'partida.exists'=>'Para la celda: '.$abc.$v.' el codigo de partida no existe por favor verificar.'
                                    );

                                    //$partidaCrear = $cellValue1.$cellValue2.$cellValue3.$cellValue4.$cellValue5;
                                    $partidaCrear = $cellValue1.$cellValue2.$cellValue3.$cellValue4;

                                    $datos = array(
                                      'accion_centralizada' => Input::get('accion_centralizada'),
                                      'accion_especifica' => $consulta_ae->id_accion,
                                      'partida' => $partidaCrear,
                                      //'aplicacion' => $cellValue6,
                                      'denominacion' => $cellValue5,
                                      'monto' => floatval($cellValue8)
                                    );

                                    $validador = Validator::make($datos, tab_ac_ae_partida::$validar_campo, $mensajes);

                                    if ($validador->fails()) {
                                        $data = json_encode(array('success' => false, 'msg' => $validador->getMessageBag()->toArray()));
                                        $response = Response::make($data);
                                        $response->header('Content-Type', 'text/html');
                                        return $response;
                                    }

                                    if (mmt_ac_ae_partida::where('id_tab_ac_ae_predefinida', '=', $consulta_ae->id_accion)
                                    ->where('nu_partida', '=', $partidaCrear)
                                    ->where('in_activo', '=', true)
                                    ->exists()) {

                                    } else {

                                        $validar_ae = tab_ac_ae_predefinida::select('id', 'nu_numero', 'de_nombre')
                                        ->where('id', '=', $consulta_ae->id_accion)
                                        ->first();

                                        $data = json_encode(array('success' => false, 'msg' => array('ERROR:'=> 'Para la celda: '.$abc.$v.' la Partida: '.$partidaCrear.', Monto: '.$cellValue8.', No se encuentra dentro de las partidas admitidas para: <br>'.$validar_ae->nu_numero.' - '.$validar_ae->de_nombre)));
                                        $response = Response::make($data);
                                        $response->header('Content-Type', 'text/html');
                                        return $response;

                                    }

                                    $partida = new tab_ac_ae_partida();
                                    $partida->id_accion_centralizada = Input::get('accion_centralizada');
                                    $partida->id_accion = $consulta_ae->id_accion;
                                    $partida->id_tab_ejercicio_fiscal = Session::get('ejercicio');
                                    //$partida->nu_aplicacion = $cellValue6;
                                    $partida->de_denominacion = $cellValue5;
                                    $partida->co_partida = $partidaCrear;
                                    $partida->monto = floatval($cellValue8);
                                    $partida->edo_reg = true;
                                    $partida->save();

                                    $calculo_ac_ae = tab_ac_ae::select(DB::raw("calcular_monto(id_accion_centralizada, id_accion) as nu_monto"))
                                    ->where('id_accion_centralizada', '=', Input::get('accion_centralizada'))
                                    ->where('id_accion', '=', $consulta_ae->id_accion)
                                    ->first();

                                    /*$ac_ae = tab_ac_ae::updateOrCreate(array('id_accion_centralizada' => Input::get('accion_centralizada'), 'id_accion' => $consulta_ae->id_accion));
                                    $ac_ae->monto_calc = $calculo_ac_ae->nu_monto;
                                    $ac_ae->save();*/

                                    $ac_ae = tab_ac_ae::where('id_accion_centralizada', '=', Input::get('accion_centralizada'))
                                    ->where('id_accion', '=', $consulta_ae->id_accion)
                                            ->update(array('monto_calc' => $calculo_ac_ae->nu_monto));

                                    $calculo_ac = tab_ac::select(DB::raw("calcular_monto(id) as nu_monto"))
                                    ->where('id', '=', Input::get('accion_centralizada'))
                                    ->first();

                                    /*$ac = tab_ac::where('id', '=', Input::get('accion_centralizada'))
            				->update(array('monto_calc' => $calculo_ac->nu_monto));*/

                                    $ac = tab_ac::find(Input::get('accion_centralizada'));
                                    $ac->monto_calc = $calculo_ac->nu_monto;
                                    $ac->save();

                                }
                            }
                        }
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

            } catch (\Exception $e) {
                $data = json_encode(array('success' => false, 'msg' => array('ERROR ('.$e->getCode().'):'=> $e->getMessage())));
                $response = Response::make($data);
                $response->header('Content-Type', 'text/html');
                return $response;
            }
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function bajar($ac, $ae)
    {

        DB::beginTransaction();

        try {

            $descripcion = tab_ac_ae::join('public.t46_acciones_centralizadas as t01', 't01.id', '=', 'public.t47_ac_accion_especifica.id_accion_centralizada')
            ->join('mantenimiento.tab_ac_ae_predefinida as t02', 't02.id', '=', 'public.t47_ac_accion_especifica.id_accion')
            ->join('mantenimiento.tab_ac_predefinida as t03', 't03.id', '=', 't01.id_accion')
            ->join('mantenimiento.tab_ejecutores as t04', 't04.id_ejecutor', '=', 't01.id_ejecutor')
            ->select(
                DB::raw("'AC' || t01.id_ejecutor || t01.id_ejercicio || lpad(t01.id_accion::text, 5, '0') as id_ac"),
                DB::raw('t02.nu_numero as nu_ae')
            )
            ->where('id_accion_centralizada', '=', $ac)
            ->where('public.t47_ac_accion_especifica.id_accion', '=', $ae)
            ->first();

            // Instantiate a new PHPExcel object
            $objPHPExcel = new PHPExcel();
            // Set properties
            $objPHPExcel->getProperties()->setCreator("Yoser Perez");
            $objPHPExcel->getProperties()->setLastModifiedBy("SPE");
            $objPHPExcel->getProperties()->setTitle("Listado de Partidas");
            $objPHPExcel->getProperties()->setSubject("Reporte");
            $objPHPExcel->getProperties()->setDescription("Reporte para documento de Office 2007 XLSX.");
            // Set the active Excel worksheet to sheet 0
            $objPHPExcel->setActiveSheetIndex(0);
            // Rename sheet
            $objPHPExcel->getActiveSheet()->getColumnDimension("A")->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension("B")->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension("C")->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension("D")->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension("E")->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension("F")->setAutoSize(true);
            //$objPHPExcel->getActiveSheet()->getColumnDimension("G")->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension("H")->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->setTitle($descripcion->id_ac.'_'.$descripcion->nu_ae.'_PARTIDAS');
            $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->applyFromArray(
                array(
                        'font'    => array(
                            'bold'      => true
                        ),
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                        ),
                        'borders' => array(
                            'top'     => array(
                                 'style' => PHPExcel_Style_Border::BORDER_THIN
                             )
                        ),
                        'fill' => array(
                            'type'       => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR,
                            'rotation'   => 90,
                            'startcolor' => array(
                                'argb' => 'FFA0A0A0'
                            ),
                            'endcolor'   => array(
                                'argb' => 'FFFFFFFF'
                            )
                        )
                    )
            );
            $objPHPExcel->getActiveSheet()->getStyle('F1')->applyFromArray(
                array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                        ),
                        'borders' => array(
                            'left'     => array(
                                 'style' => PHPExcel_Style_Border::BORDER_THIN
                             )
                        )
                    )
            );

            $objPHPExcel->getActiveSheet()->getStyle('G1')->applyFromArray(
                array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                        )
                    )
            );

            $objPHPExcel->getActiveSheet()->getStyle('H1')->applyFromArray(
                array(
                        'borders' => array(
                            'right'     => array(
                                 'style' => PHPExcel_Style_Border::BORDER_THIN
                             )
                        )
                    )
            );
            // Initialise the Excel row number
            $rowCount = 2;
            // Iterate through each result from the SQL query in turn
            // We fetch each database result row into $row in turn

            $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', 'PA')
            ->setCellValue('B1', 'GE')
            ->setCellValue('C1', 'ES')
            ->setCellValue('D1', 'SE')
            ->setCellValue('E1', 'SSE')
            ->setCellValue('F1', 'APLICACION')
            ->setCellValue('G1', 'DENOMINACIÓN')
            ->setCellValue('H1', 'MONTO');

            // Make bold cells
            $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setBold(true);

            $tab_ac_ae_partida = $this->tab_ac_ae_partida
            //->join('mantenimiento.tab_partidas as t01','t01.co_partida','=','public.t54_ac_ae_partidas.co_partida')
            ->join('mantenimiento.tab_partidas as t01', function ($j) {
                $j->on('t01.co_partida', '=', 'public.t54_ac_ae_partidas.co_partida')
                  ->on('t01.id_tab_ejercicio_fiscal', '=', 'public.t54_ac_ae_partidas.id_tab_ejercicio_fiscal');
            })
            ->select('public.t54_ac_ae_partidas.co_partida', 'tx_nombre', 'monto', 'nu_aplicacion')
            ->where('id_accion_centralizada', '=', $ac)
            ->where('id_accion', '=', $ae)
            ->orderBy('co_partida', 'ASC')
              ->get();

            foreach ($tab_ac_ae_partida as $key => $value) {
                // Set cell An to the "name" column from the database (assuming you have a column called name)
                //    where n is the Excel row number (ie cell A1 in the first row)
                $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, substr($value->co_partida, 0, 3));
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('B'.$rowCount, substr(substr($value->co_partida, 0, 5), 3), PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, substr(substr($value->co_partida, 0, 7), 5), PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, substr(substr($value->co_partida, 0, 9), 7), PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, substr(substr($value->co_partida, 0, 12), 9), PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $value->nu_aplicacion, PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $value->tx_nombre, PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, $value->monto);
                // Increment the Excel row counter
                $rowCount++;
            }

            // Instantiate a Writer to create an OfficeOpenXML Excel .xlsx file
            $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
            // We'll be outputting an excel file
            header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            // It will be called file.xls
            header('Content-Disposition: attachment; filename="'.$descripcion->id_ac.'_'.$descripcion->nu_ae.'_partidas_'.date("H:i:s").'.xlsx"');
            $objWriter->save('php://output');

            DB::commit();

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();
            header('Content-Type: text/html');
            echo json_encode(array(
              'success' => false,
              'msg' => array('ERROR ('.$e->getCode().'):'=> $e->getMessage())
              //'msg' => array('ERROR ('.$e->getCode().'):'=> 'CODIGO['.$e->getCode().']: Error en Transaccion, verfique e intente de nuevo.')
            ));
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
                        $borrar_ac_ae_partida = tab_ac_ae_partida::where('id_accion_centralizada', '=', $ac)
                        ->where('id_accion', '=', $ae)
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
                                $validarDetalle = Validator::make($valor, tab_ac_ae_partida::$validarDesagregado);
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

                                    if (tab_partidas::where('id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))
                                    ->where('co_partida', '=', $partidaCrear)
                                    ->exists()) {

                                    } else {

                                        $tabla = new tab_partidas();
                                        $tabla->id_tab_ejercicio_fiscal = Session::get('ejercicio');
                                        $tabla->co_partida = $partidaCrear;
                                        $tabla->tx_nombre = $valor['denominacion'];
                                        $tabla->ace_mov = true;
                                        $tabla->in_activo = true;
                                        $tabla->save();

                                    }

                                    $partida = new tab_ac_ae_partida();
                                    $partida->id_accion_centralizada = $valor['ac'];
                                    $partida->id_accion = $valor['ae'];
                                    $partida->id_tab_ejercicio_fiscal = Session::get('ejercicio');
                                    $partida->nu_aplicacion = $valor['aplicacion'];
                                    $partida->de_denominacion = $valor['denominacion'];
                                    $partida->co_partida = $partidaCrear;
                                    $partida->monto = floatval($valor['monto']);
                                    $partida->edo_reg = true;
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
