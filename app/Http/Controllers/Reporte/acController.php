<?php

namespace matriz\Http\Controllers\Reporte;

//*******agregar esta linea******//
use matriz\Models\Ac\tab_ac_ae;
use matriz\Models\Ac\tab_meta_financiera;
use matriz\Models\Ac\tab_ac_responsable;
use View;
use Validator;
use Input;
use Response;
use DB;
use Session;
use TCPDF;
use PHPExcel_IOFactory;
use PHPExcel;
use PHPExcel_Writer_Excel2007;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Border;
use PHPExcel_Style_Fill;
use PHPExcel_Cell_DataType;
//*******************************//
use Illuminate\Http\Request;

use matriz\Http\Requests;
use matriz\Http\Controllers\Controller;

//*******clase extendida TCPDF******//
class ReportePDF extends TCPDF
{
    public function encabezado($pdf)
    {
        $pdf->Image(public_path().'/images/zulia_escudo_negro.png', 15, 3, 20, 16, 'PNG', '', '', true, 150, '', false, false, 0, false, false, false);
        $pdf->setXY(35, 7);
        $pdf->SetFont('', 'B', 11);
        $pdf->MultiCell(190, 5, 'GOBERNACIÓN DEL ESTADO ZULIA', 0, 'L', 0, 0, '', '', true);
        $pdf->setXY(35, 14);
        $pdf->MultiCell(190, 5, 'PLAN OPERATIVO ANUAL '.Session::get('ejercicio'), 0, 'L', 0, 0, '', '', true);
        $pdf->setY(23);
        return $pdf;
    }

    public function pie($pdf)
    {
        $pdf->setXY(10, -10);
        $pdf->SetFont('', '', 8);
        $pdf->ln(0);
        $pdf->writeHTMLCell(200, 0, '', '', 'AC'.'-'.$pdf->getAliasNumPage().'/'.$pdf->getAliasNbPages(), 0, 0, 0, true, 'R', true);
        $pdf->ln(0);
        $pdf->writeHTMLCell(190, 0, '', '', 'Palacio de los Cóndores, Plaza Bolívar, Maracaibo, Estado Zulia, Venezuela', 0, 0, 0, true, 'C', true);
        return $pdf;
    }

    public function Footer()
    {
        self::pie($this);
    }

    public function Header()
    {
        self::encabezado($this);
    }
}

class acController extends Controller
{
    protected $tab_meta_financiera;

    public function __construct(tab_meta_financiera $tab_meta_financiera)
    {
        $this->middleware('auth');
        $this->tab_meta_financiera = $tab_meta_financiera;
    }
    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function lista()
    {
        $data = json_encode(array("id_ejecutor" => Session::get('ejecutor')));
        return View::make('reporte.poa.ac')->with('data', $data);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function resumen()
    {

        /***distribucion***/
        $htmlReporte = '
    <!-- Tabla 1 -->
    <table border="0.1" style="width:100%" style="font-size:9px" cellpadding="3">
    <thead>
    <tr align="left" bgcolor="#E6E6E6">
    <th colspan="5" style="width: 100%;"><b>RESUMEN DE ACCIONES CENTRALIZADAS POA: '.Session::get('ejercicio').' </b></th>
    </tr>
    <tr style="font-size:8px">
    <th align="center" bgcolor="#E6E6E6" style="width: 40%;"><b>ACCIONES CENTRALIZADAS</b></th>
    <th align="center" bgcolor="#E6E6E6" style="width: 40%;"><b>ACCIONES ESPECIFICAS</b></th>
    <th align="center" bgcolor="#E6E6E6" style="width: 20%;"><b>MONTO</b></th>
    </tr>
    </thead>
    ';

        $htmlReporte.='
    <tbody>
    ';

        //Query
        $consulta1 = tab_ac_ae::join('public.t46_acciones_centralizadas as t01', 't01.id', '=', 'public.t47_ac_accion_especifica.id_accion_centralizada')
        ->join('mantenimiento.tab_ac_ae_predefinida as t02', 't02.id', '=', 'public.t47_ac_accion_especifica.id_accion')
        ->join('mantenimiento.tab_ac_predefinida as t03', 't03.id', '=', 't01.id_accion')
        ->join('mantenimiento.tab_ejecutores as t04', 't04.id_ejecutor', '=', 't01.id_ejecutor')
        ->select(
            DB::raw("'AC' || t01.id_ejecutor || t01.id_ejercicio || lpad(t01.id_accion::text, 5, '0') as id_ac"),
            DB::raw('t02.nu_numero as nu_ae'),
            't03.de_nombre as de_ac',
            't02.de_nombre as de_ac_ae',
            'public.t47_ac_accion_especifica.monto as mo_ae'
        )
        ->where('t01.id_ejercicio', '=', Session::get('ejercicio'))
        ->orderBy('t01.id', 'public.t47_ac_accion_especifica.id_accion', 'ASC')
        ->get();

        $i = 0;
        $acumulado = 0;

        foreach ($consulta1 as $key => $value) {
            // Set cell An to the "name" column from the database (assuming you have a column called name)
            $i++;
            $acumulado = $acumulado+$value->mo_ae;
            $htmlReporte.='
      <tr style="font-size:8px" nobr="true">
        <td style="width: 40%;" align="justify">'.$value->id_ac.'-'.$value->de_ac.'</td>
        <td style="width: 40%;" align="justify">'.$value->nu_ae.'-'.$value->de_ac_ae.'</td>
        <td style="width: 20%;">'.number_format($value->mo_ae, 2, ',', '.').'</td>
      </tr>';

        }

        $htmlReporte.='
    <tr style="font-size:8px" nobr="true">
      <td style="width: 80%;" align="right"><b>TOTAL</b></td>
      <td style="width: 20%;">'.number_format($acumulado, 2, ',', '.').'</td>
    </tr>';

        $htmlReporte.='
    </tbody>
    </table>';

        $pdf = new ReportePDF("P", PDF_UNIT, 'Letter', true, 'UTF-8', false);
        $pdf->SetCreator('Yoser Perez');
        $pdf->SetAuthor('POA, SPE');
        $pdf->SetTitle('Reporte');
        $pdf->SetSubject('Reporte');
        $pdf->SetKeywords('Planilla, PDF, SPE');
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetTopMargin(20);
        $pdf->SetPrintHeader(true);
        $pdf->SetPrintFooter(true);
        // set auto page breaks
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->AddPage();
        //Cuerpo de la planilla
        $pdf->writeHTML($htmlReporte, true, false, false, false, '');
        $pdf->lastPage();
        $pdf->output('AC_RESUMEN_'.date("H:i:s").'.pdf', 'D');

    }

    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function ubica()
    {
        $data = json_encode(array("id_ejecutor" => Session::get('ejecutor')));
        return View::make('reporte.poa.ubicaac')->with('data', $data);
    }

    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function ubicacion()
    {

        try {

            //Query
            $tab_meta_financiera = $this->tab_meta_financiera
            ->join('t69_metas_ac as t69', 't69.co_metas', '=', 't70_metas_ac_detalle.co_metas')
            ->join('t47_ac_accion_especifica as t47', function ($j) {
                $j->on('t47.id_accion_centralizada', '=', 't69.id_accion_centralizada')
                  ->on('t47.id_accion', '=', 't69.co_ac_acc_espec');
            })
            ->join('t46_acciones_centralizadas as t46', 't46.id', '=', 't47.id_accion_centralizada')
            ->join('mantenimiento.tab_ejecutores as t24', 't24.id_ejecutor', '=', 't47.id_ejecutor')
            ->join('mantenimiento.tab_municipio_detalle as t13', 't13.id', '=', 't70_metas_ac_detalle.co_municipio')
            ->join('mantenimiento.tab_fuente_financiamiento as t06', 't06.id', '=', 't70_metas_ac_detalle.co_fuente')
            ->join('mantenimiento.tab_ac_ae_predefinida as t02', 't47.id_accion', '=', 't02.id')
            ->join('mantenimiento.tab_ac_predefinida as t03', 't46.id_accion', '=', 't03.id')
            ->select(
                't03.de_nombre as de_proyecto',
                'nu_numero',
                't02.de_nombre as de_ae',
                DB::raw("'AC' || t46.id_ejecutor || id_ejercicio || lpad(t46.id_accion::text, 5, '0') as id_proyecto"),
                DB::raw(" t24.id_ejecutor||' - '|| tx_ejecutor as ejecutor"),
                DB::raw("t69.codigo ||' - '|| t69.nb_meta as de_actividad"),
                'de_municipio',
                'mo_presupuesto',
                'de_fuente_financiamiento'
            )
            ->where('t70_metas_ac_detalle.co_municipio', '=', Input::get('id_tab_municipio'))
            ->where('t47.id_ejecutor', '=', Input::get('ejecutor'))
            ->where('t46.id_ejercicio', '=', Session::get('ejercicio'))
            ->where('t46.edo_reg', '=', true)
            ->where('t47.edo_reg', '=', true)
            ->where('t69.edo_reg', '=', true)
            ->where('t70_metas_ac_detalle.edo_reg', '=', true)
            ->orderBy(DB::raw('t46.id_accion', 't47.id_accion', 't69.codigo'), 'ASC')
            ->get();

            /*if (!empty(Input::get('fuente_financiamiento'))) {
              $consulta->where('t70_metas_ac_detalle.co_fuente', '=', Input::get('fuente_financiamiento'));
            }

            if (!empty(Input::get('ejecutor'))) {
              $consulta->where('t47.id_ejecutor', '=', Input::get('ejecutor'));
            }*/

            /*->when(Input::get('fuente_financiamiento'), function ($query) {
              return $query->where('t68_metas_detalle.co_fuente', '=', Input::get('fuente_financiamiento'));
            })
            ->when(Input::get('ejecutor'), function ($query) {
              return $query->where('t24.id_ejecutor', '=', Input::get('ejecutor'));
            })*/

            /*$tab_meta_financiera->orderBy(DB::raw('t46.id_accion', 't47.id_accion', 't69.codigo'), 'ASC')
            ->get();*/

            /***distribucion fisica***/
            $htmlReporte = '
    <!-- Tabla 1 -->
    <table border="0.1" style="width:100%" style="font-size:9px" cellpadding="3">
    <thead>
    <tr align="center" bgcolor="#BDBDBD">
    <th colspan="6" style="width: 100%;"><b>DISTRIBUCIÓN DE ACCION CENTRALIZADA POR MUNICIPIO - AÑO '.Session::get('ejercicio').'</b></th>
    </tr>
    <tr style="font-size:6px">
    <th align="center" bgcolor="#BDBDBD" style="width: 10%;">COD. AC</th>
    <th align="center" bgcolor="#BDBDBD" style="width: 15%;">DESCRIPCION AC</th>
    <th align="center" bgcolor="#BDBDBD" style="width: 15%;">ACCION ESPECIFICA</th>
    <th align="center" bgcolor="#BDBDBD" style="width: 15%;">ENTE EJECUTOR RESPONSABLE</th>
    <th align="center" bgcolor="#BDBDBD" style="width: 15%;">ACTIVIDAD</th>
    <th align="center" bgcolor="#BDBDBD" style="width: 10%;">MUNICIPIO</th>
    <th align="center" bgcolor="#BDBDBD" style="width: 10%;">MONTO</th>
    <th align="center" bgcolor="#BDBDBD" style="width: 10%;">FUENTE FINANCIAMIENTO</th>
    </tr>
    </thead>
    ';

            $htmlReporte.='
    <tbody>
    ';

            foreach ($tab_meta_financiera as $key => $value) {
                // Set cell An to the "name" column from the database (assuming you have a column called name)

                $htmlReporte.='
    			<tr style="font-size:7px" nobr="true">
    				<td style="width: 10%;">'.$value->id_proyecto.'</td>
    				<td style="width: 15%;" align="justify">'.$value->de_proyecto.'</td>
    				<td style="width: 15%;" align="justify">'.$value->nu_numero.' - '.$value->de_ae.'</td>
    				<td style="width: 15%;" align="justify">'.$value->ejecutor.'</td>
    				<td style="width: 15%;" align="justify">'.$value->de_actividad.'</td>
    				<td style="width: 10%;" align="center">'.$value->de_municipio.'</td>
    				<td style="width: 10%;">'.number_format($value->mo_presupuesto, 2, ',', '.').'</td>
    				<td style="width: 10%;" align="center">'.$value->de_fuente_financiamiento.'</td>
    			</tr>';

            }

            $htmlReporte.='
    </tbody>
    </table>';

            $pdf = new ReportePDF("L", PDF_UNIT, 'Letter', true, 'UTF-8', false);
            $pdf->SetCreator('Yoser Perez');
            $pdf->SetAuthor('POA, SPE');
            $pdf->SetTitle('ACCION CENTRALIZADA - UBICACIÓN GEOGRAFICA');
            $pdf->SetSubject('Reporte');
            $pdf->SetKeywords('Planilla, PDF, SPE');
            $pdf->SetMargins(10, 10, 10);
            $pdf->SetTopMargin(20);
            $pdf->SetPrintHeader(true);
            $pdf->SetPrintFooter(true);
            // set auto page breaks
            $pdf->SetAutoPageBreak(true, 15);
            $pdf->AddPage();
            //Cuerpo de la planilla
            $pdf->writeHTML($htmlReporte, true, false, false, false, '');
            $pdf->lastPage();
            $pdf->output('UBICACION_AC_'.Session::get('ejercicio').'_'.date("H:i:s").'.pdf', 'D');

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
    * Display a listing of the resource.
    *
    * @return Response
    */
public function ubicacionTodo()
{

    try {

        //Query
        $tab_meta_financiera = $this->tab_meta_financiera
        ->join('t69_metas_ac as t69', 't69.co_metas', '=', 't70_metas_ac_detalle.co_metas')
        ->join('t47_ac_accion_especifica as t47', function ($j) {
            $j->on('t47.id_accion_centralizada', '=', 't69.id_accion_centralizada')
              ->on('t47.id_accion', '=', 't69.co_ac_acc_espec');
        })
        ->join('t46_acciones_centralizadas as t46', 't46.id', '=', 't47.id_accion_centralizada')
        ->join('mantenimiento.tab_ejecutores as t24', 't24.id_ejecutor', '=', 't47.id_ejecutor')
        ->join('mantenimiento.tab_municipio_detalle as t13', 't13.id', '=', 't70_metas_ac_detalle.co_municipio')
        ->join('mantenimiento.tab_fuente_financiamiento as t06', 't06.id', '=', 't70_metas_ac_detalle.co_fuente')
        ->join('mantenimiento.tab_ac_ae_predefinida as t02', 't47.id_accion', '=', 't02.id')
        ->join('mantenimiento.tab_ac_predefinida as t03', 't46.id_accion', '=', 't03.id')
        ->select(
            't03.de_nombre as de_proyecto',
            'nu_numero',
            't02.de_nombre as de_ae',
            DB::raw("'AC' || t46.id_ejecutor || id_ejercicio || lpad(t46.id_accion::text, 5, '0') as id_proyecto"),
            DB::raw(" t24.id_ejecutor||' - '|| tx_ejecutor as ejecutor"),
            DB::raw("t69.codigo ||' - '|| t69.nb_meta as de_actividad"),
            'de_municipio',
            'mo_presupuesto',
            'de_fuente_financiamiento'
        )
        ->where('t46.id_ejercicio', '=', Session::get('ejercicio'))
        ->where('t46.edo_reg', '=', true)
        ->where('t47.edo_reg', '=', true)
        ->where('t69.edo_reg', '=', true)
        ->where('t70_metas_ac_detalle.edo_reg', '=', true)
        ->orderBy(DB::raw('t46.id_accion', 't47.id_accion', 't69.codigo'), 'ASC')
        ->get();

        /***distribucion fisica***/
        $htmlReporte = '
  <!-- Tabla 1 -->
  <table border="0.1" style="width:100%" style="font-size:9px" cellpadding="3">
  <thead>
  <tr align="center" bgcolor="#BDBDBD">
  <th colspan="6" style="width: 100%;"><b>DISTRIBUCIÓN DE ACCION CENTRALIZADA POR MUNICIPIO - AÑO '.Session::get('ejercicio').'</b></th>
  </tr>
  <tr style="font-size:6px">
  <th align="center" bgcolor="#BDBDBD" style="width: 10%;">COD. AC</th>
  <th align="center" bgcolor="#BDBDBD" style="width: 15%;">DESCRIPCION AC</th>
  <th align="center" bgcolor="#BDBDBD" style="width: 15%;">ACCION ESPECIFICA</th>
  <th align="center" bgcolor="#BDBDBD" style="width: 15%;">ENTE EJECUTOR RESPONSABLE</th>
  <th align="center" bgcolor="#BDBDBD" style="width: 15%;">ACTIVIDAD</th>
  <th align="center" bgcolor="#BDBDBD" style="width: 10%;">MUNICIPIO</th>
  <th align="center" bgcolor="#BDBDBD" style="width: 10%;">MONTO</th>
  <th align="center" bgcolor="#BDBDBD" style="width: 10%;">FUENTE FINANCIAMIENTO</th>
  </tr>
  </thead>
  ';

        $htmlReporte.='
  <tbody>
  ';

        foreach ($tab_meta_financiera as $key => $value) {
            // Set cell An to the "name" column from the database (assuming you have a column called name)

            $htmlReporte.='
        <tr style="font-size:7px" nobr="true">
          <td style="width: 10%;">'.$value->id_proyecto.'</td>
          <td style="width: 15%;" align="justify">'.$value->de_proyecto.'</td>
          <td style="width: 15%;" align="justify">'.$value->nu_numero.' - '.$value->de_ae.'</td>
          <td style="width: 15%;" align="justify">'.$value->ejecutor.'</td>
          <td style="width: 15%;" align="justify">'.$value->de_actividad.'</td>
          <td style="width: 10%;" align="center">'.$value->de_municipio.'</td>
          <td style="width: 10%;">'.number_format($value->mo_presupuesto, 2, ',', '.').'</td>
          <td style="width: 10%;" align="center">'.$value->de_fuente_financiamiento.'</td>
        </tr>';

        }

        $htmlReporte.='
  </tbody>
  </table>';

        $pdf = new ReportePDF("L", PDF_UNIT, 'Letter', true, 'UTF-8', false);
        $pdf->SetCreator('Yoser Perez');
        $pdf->SetAuthor('POA, SPE');
        $pdf->SetTitle('ACCION CENTRALIZADA - UBICACIÓN GEOGRAFICA');
        $pdf->SetSubject('Reporte');
        $pdf->SetKeywords('Planilla, PDF, SPE');
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetTopMargin(20);
        $pdf->SetPrintHeader(true);
        $pdf->SetPrintFooter(true);
        // set auto page breaks
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->AddPage();
        //Cuerpo de la planilla
        $pdf->writeHTML($htmlReporte, true, false, false, false, '');
        $pdf->lastPage();
        $pdf->output('UBICACION_AC_'.Session::get('ejercicio').'_'.date("H:i:s").'.pdf', 'D');

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
  * Display a listing of the resource.
  *
  * @return Response
  */
    public function poaTodo()
    {

        DB::beginTransaction();

        try {

            $responsable = tab_ac_responsable::join('public.t46_acciones_centralizadas as t01', 'public.t48_ac_responsables.id_accion_centralizada', '=', 't01.id')
            ->join('mantenimiento.tab_ac_predefinida as t02', 't01.id_accion', '=', 't02.id')
            ->join('mantenimiento.tab_ejecutores as t03', 't01.id_ejecutor', '=', 't03.id_ejecutor')
            ->select(
                'id_accion_centralizada',
                'realizador_nombres',
                'realizador_cedula',
                'realizador_cargo',
                'realizador_correo',
                'realizador_telefono',
                'realizador_unidad',
                'registrador_nombres',
                'registrador_cedula',
                'registrador_cargo',
                'registrador_correo',
                'registrador_telefono',
                'registrador_unidad',
                'autorizador_nombres',
                'autorizador_cedula',
                'autorizador_cargo',
                'autorizador_correo',
                'autorizador_telefono',
                'autorizador_unidad',
                'de_nombre',
                't01.id_ejecutor',
                'tx_ejecutor',
                DB::raw("'AC' || t01.id_ejecutor || id_ejercicio || lpad(id_accion::text, 5, '0') as codigo")
            )
            ->where('id_ejercicio', '=', Session::get('ejercicio'))
            ->orderBy('t01.id_ejecutor', 'ASC')
            ->get();

            // Instantiate a new PHPExcel object
            $objPHPExcel = new PHPExcel();
            // Set properties
            $objPHPExcel->getProperties()->setCreator("Yoser Perez");
            $objPHPExcel->getProperties()->setLastModifiedBy("SPE");
            $objPHPExcel->getProperties()->setTitle("Listado de Responsables");
            $objPHPExcel->getProperties()->setSubject("Reporte");
            $objPHPExcel->getProperties()->setDescription("Reporte para documento de Office 2007 XLSX.");
            // Set the active Excel worksheet to sheet 0
            $objPHPExcel->setActiveSheetIndex(0);
            // Rename sheet
            //$objPHPExcel->getActiveSheet()->getColumnDimension("A")->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(30);
            //$objPHPExcel->getActiveSheet()->getColumnDimension("B")->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension("C")->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension("D")->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension("E")->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension("F")->setAutoSize(true);
            //$objPHPExcel->getActiveSheet()->getColumnDimension("G")->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension("H")->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->setTitle('AC_'.Session::get('ejercicio').'_RESPONSABLES');
            $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->applyFromArray(
                array(
                        'font'    => array(
                            'bold'      => true
                        ),
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
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
            ->setCellValue('A1', 'EJECUTOR')
            ->setCellValue('B1', 'ACCION CENTRALIZADA')
            ->setCellValue('C1', 'CEDULA')
            ->setCellValue('D1', 'NOMBRE')
            ->setCellValue('E1', 'CARGO')
            ->setCellValue('F1', 'UNIDAD')
            ->setCellValue('G1', 'CORREO')
            ->setCellValue('H1', 'TELEFONO');

            // Make bold cells
            $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setBold(true);

            foreach ($responsable as $key => $value) {
                $final = $rowCount+2;
                $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowCount.':A'.$final);
                $objPHPExcel->getActiveSheet()->mergeCells('B'.$rowCount.':B'.$final);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount.':A'.$final)->getAlignment()->setWrapText(true);
                $objPHPExcel->getActiveSheet()->getStyle('B'.$rowCount.':B'.$final)->getAlignment()->setWrapText(true);
                // Set thin black border outline around column
                $styleThinBlackBorderOutline = array(
                    'borders' => array(
                        'outline' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                            'color' => array('argb' => 'FF000000'),
                        ),
                    ),
                );
                $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount.':H'.$final)->applyFromArray($styleThinBlackBorderOutline);
                // Set cell An to the "name" column from the database (assuming you have a column called name)
                //    where n is the Excel row number (ie cell A1 in the first row)
                $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $value->id_ejecutor.' - '.$value->tx_ejecutor);
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('B'.$rowCount, $value->codigo.' - '.$value->de_nombre, PHPExcel_Cell_DataType::TYPE_STRING);
                /**Datos del Titular**/
                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $value->autorizador_cedula, PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $value->autorizador_nombres, PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $value->autorizador_cargo, PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $value->autorizador_unidad, PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $value->autorizador_correo, PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, $value->autorizador_telefono);
                $rowCount=$rowCount+1;
                /**Datos del Planificador**/
                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $value->realizador_cedula, PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $value->realizador_nombres, PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $value->realizador_cargo, PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $value->realizador_unidad, PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $value->realizador_correo, PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, $value->realizador_telefono);
                $rowCount=$rowCount+1;
                /**Datos del Administrador**/
                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $value->registrador_cedula, PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $value->registrador_nombres, PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $value->registrador_cargo, PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $value->registrador_unidad, PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $value->registrador_correo, PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, $value->registrador_telefono);
                // Increment the Excel row counter
                $rowCount++;
            }

            // Instantiate a Writer to create an OfficeOpenXML Excel .xlsx file
            $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
            // We'll be outputting an excel file
            header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            // It will be called file.xls
            header('Content-Disposition: attachment; filename="POA_AC_TODO_'.Session::get('ejercicio').'_'.date("H:i:s").'.xlsx"');
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
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function exportacion_icp_ac()
    {

        DB::beginTransaction();

        try {

            $consulta = DB::select(
                'select t25.id as ejercicio,
        t24.id_ejecutor as ejecutor,
        ma.se as sector,
        ma.pro as proyecto,
        ma.sub as subproyecto,
        --ma.act as actividad,
        t53.numero as actividad,
        t54.co_partida as partida,
        tx_nombre as de_partida,
        t54.monto,
        t52.nombre as ac_nombre,
        t24.tx_ejecutor as ac_ej_nombre,
        t53.nombre as ae_nombre,
        ma.ej_ae as ae_ej_id,
        t24a.tx_ejecutor as ae_ej_nombre,
        t009.de_inicial,
        sp_ac_ae_fondo( t47.id_accion_centralizada, t47.id_accion) as fondo,
        t24a.id_tab_ambito_ejecutor as ambito,
        t02.de_estatus,
        tae24a.de_ambito_ejecutor
      from mantenimiento.tab_ejercicio_fiscal as t25
        join mapa_acs as ma on ma.ef = t25.id
        join t46_acciones_centralizadas as t46
          on ma.ac = t46.id_accion
          and ma.ej_ac = t46.id_ejecutor
          and ma.se = (
            select co_sector 
            from mantenimiento.tab_sectores
            where id = t46.id_subsector
              and in_activo is true
          )
        join t47_ac_accion_especifica as t47
          on t46.id = t47.id_accion_centralizada
          and ma.ej_ae = t47.id_ejecutor
          and t47.id_accion = (
            select id
            from t53_ac_ae_predefinidas
            where padre = ma.ac
              and numero = ma.ae
          )
        join t54_ac_ae_partidas as t54 on t46.id = t54.id_accion_centralizada and t47.id_accion = t54.id_accion
        join mantenimiento.tab_ejecutores as t24 on t24.id_ejecutor = t46.id_ejecutor
        join mantenimiento.tab_ejecutores as t24a on t24a.id_ejecutor = t47.id_ejecutor
        join t52_ac_predefinidas as t52 on t52.id = ma.ac
        join t53_ac_ae_predefinidas as t53 on t53.id = t47.id_accion and t53.padre = t52.id
        join mantenimiento.tab_partidas as tabp on tabp.co_partida = t54.co_partida and tabp.id_tab_ejercicio_fiscal = t25.id
        join mantenimiento.tab_tipo_ejecutor as t009 on t24a.id_tab_tipo_ejecutor = t009.id
        left join mantenimiento.tab_ambito_ejecutor as tae24a on t24a.id_tab_ambito_ejecutor = tae24a.id
        join mantenimiento.tab_estatus as t02 on t02.id = t46.id_estatus
      where 	ma.edo_reg
        and t46.edo_reg
        and t47.edo_reg
        and t24.in_activo
        and t25.id::text = :anio
        and t54.id_tab_ejercicio_fiscal::text = :anio
      order by 1, 2, 3, 4, 6;',
                array('anio' => Session::get('ejercicio'))
            );

            // Instantiate a new PHPExcel object
            $objPHPExcel = new PHPExcel();
            // Set properties
            $objPHPExcel->getProperties()->setCreator("Yoser Perez");
            $objPHPExcel->getProperties()->setLastModifiedBy("SPE");
            $objPHPExcel->getProperties()->setTitle("Listado de Responsables");
            $objPHPExcel->getProperties()->setSubject("Reporte");
            $objPHPExcel->getProperties()->setDescription("Reporte para documento de Office 2007 XLSX.");
            // Set the active Excel worksheet to sheet 0
            $objPHPExcel->setActiveSheetIndex(0);
            // Rename sheet
            //$objPHPExcel->getActiveSheet()->getColumnDimension("A")->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(10);
            //$objPHPExcel->getActiveSheet()->getColumnDimension("B")->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(10);
            //$objPHPExcel->getActiveSheet()->getColumnDimension("G")->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension("H")->setWidth(40);
            $objPHPExcel->getActiveSheet()->getColumnDimension("Q")->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension("R")->setWidth(20);
            $objPHPExcel->getActiveSheet()->setTitle('ac_'.Session::get('ejercicio').'_exportacion_icp_ac');
            $objPHPExcel->getActiveSheet()->getStyle('A1:R1')->applyFromArray(
                array(
                        'font'    => array(
                            'bold'      => true
                        ),
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
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
            $objPHPExcel->getActiveSheet()->getStyle('Q1')->applyFromArray(
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

            $objPHPExcel->getActiveSheet()->getStyle('Q1')->applyFromArray(
                array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                        )
                    )
            );

            $objPHPExcel->getActiveSheet()->getStyle('Q1')->applyFromArray(
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
            ->setCellValue('A1', 'Ejercicio')
            ->setCellValue('B1', 'Ejecutor')
            ->setCellValue('C1', 'Sector')
            ->setCellValue('D1', 'Proyecto')
            ->setCellValue('E1', 'Subproyecto')
            ->setCellValue('F1', 'Actividad')
            ->setCellValue('G1', 'Partida')
            ->setCellValue('H1', 'Nombre Partida')
      ->setCellValue('I1', 'Monto Registrado')
      ->setCellValue('J1', 'Nombre de la AC')
      ->setCellValue('K1', 'Nombre de la AE')
      ->setCellValue('L1', 'Nombre del Ejecutor de la AC')
      ->setCellValue('M1', 'Ejecutor de la AE')
      ->setCellValue('N1', 'Nombre del Ejecutor de la AE')
      ->setCellValue('O1', 'Tipo Ejecutor')
      ->setCellValue('P1', 'Fondo')
      ->setCellValue('Q1', 'Ambito')
        ->setCellValue('R1', 'Estatus');

            foreach ($consulta as $key => $value) {
                // Set thin black border outline around column
                $styleThinBlackBorderOutline = array(
                  'borders' => array(
                    'outline' => array(
                      'style' => PHPExcel_Style_Border::BORDER_THIN,
                      'color' => array('argb' => 'FF000000'),
                    ),
                  ),
                );
                $objPHPExcel->getActiveSheet()->getStyle('A1:R1')->applyFromArray($styleThinBlackBorderOutline);
                // Set cell An to the "name" column from the database (assuming you have a column called name)
                //    where n is the Excel row number (ie cell A1 in the first row)

                $ambito = $value->ambito.' - '.trim($value->de_ambito_ejecutor);

                $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $value->ejercicio, PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $value->ejecutor, PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $value->sector, PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $value->proyecto, PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $value->subproyecto, PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, (string)str_pad($value->actividad, 2, "0", STR_PAD_LEFT), PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $value->partida, PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, $value->de_partida, PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, $value->monto, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, $value->ac_nombre, PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('K'.$rowCount, $value->ae_nombre, PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('L'.$rowCount, $value->ac_ej_nombre, PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('M'.$rowCount, $value->ae_ej_id, PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('N'.$rowCount, $value->ae_ej_nombre, PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('O'.$rowCount, $value->de_inicial, PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('P'.$rowCount, $value->fondo, PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('Q'.$rowCount, $ambito, PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('R'.$rowCount, $value->de_estatus, PHPExcel_Cell_DataType::TYPE_STRING);

                // Increment the Excel row counter
                $rowCount++;
            }

            $objPHPExcel->getActiveSheet()->getStyle('B1:B'.$rowCount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $objPHPExcel->getActiveSheet()->getStyle('C1:C'.$rowCount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $objPHPExcel->getActiveSheet()->getStyle('M1:M'.$rowCount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $objPHPExcel->getActiveSheet()->getStyle('Q1:Q'.$rowCount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $objPHPExcel->getActiveSheet()->getStyle('R1:R'.$rowCount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            // Make bold cells
            $objPHPExcel->getActiveSheet()->getStyle('A1:R1')->getFont()->setBold(true);

            // Instantiate a Writer to create an OfficeOpenXML Excel .xlsx file
            $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
            // We'll be outputting an excel file
            header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            // It will be called file.xls
            header('Content-Disposition: attachment; filename="exportacion_icp_ac_'.Session::get('ejercicio').'_'.date("H:i:s").'.xlsx"');
            $objWriter->save('php://output');

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
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function exportacion_icp_ac_desagregado()
    {

        DB::beginTransaction();

        try {

            $consulta = DB::select(
                "
      SELECT 
      t01.id_tab_ejercicio_fiscal as ejercicio,
      t01.co_partida as partida,
      t01.de_denominacion as de_partida,
      t01.nu_aplicacion as aplicacion,
      t01.mo_partida as monto,
      t46.id_ejecutor as ejecutor,
      tss.co_sector as sector,
      tap.nu_original as proyecto,
      '00' as subproyecto,
      taap.nu_numero as actividad,
      tap.de_nombre as ac_nombre,
      taap.de_nombre as ae_nombre,
      t24.tx_ejecutor as ac_ej_nombre,
      t47.id_ejecutor as ae_ej_id,
      t24a.tx_ejecutor as ae_ej_nombre,
      t009.de_inicial,
      sp_ac_ae_fondo( t47.id_accion_centralizada, t47.id_accion) as fondo,
      t24a.id_tab_ambito_ejecutor as ambito,
      tae24a.de_ambito_ejecutor,
      tpzulia.tx_descripcion as area_estrategica
      FROM tab_ac_es_partida_desagregado AS t01
      INNER JOIN t46_acciones_centralizadas AS t46 ON t46.id = t01.td_tab_ac 
      INNER JOIN mantenimiento.tab_sectores AS tss ON tss.id = t46.id_subsector 
      INNER JOIN mantenimiento.tab_ac_predefinida AS tap ON tap.id = t46.id_accion 
      INNER JOIN tab_ac_ae_predefinida as taap ON taap.id = t01.id_tab_ac_ae_predefinida  
      INNER JOIN mantenimiento.tab_ejecutores as t24 ON t24.id_ejecutor = t46.id_ejecutor
      INNER JOIN t47_ac_accion_especifica as t47 ON t46.id = t47.id_accion_centralizada AND t47.id_accion = t01.id_tab_ac_ae_predefinida
      INNER JOIN mantenimiento.tab_ejecutores as t24a on t24a.id_ejecutor = t47.id_ejecutor
      INNER JOIN mantenimiento.tab_tipo_ejecutor as t009 on t24a.id_tab_tipo_ejecutor = t009.id
      LEFT JOIN mantenimiento.tab_ambito_ejecutor as tae24a on t24a.id_tab_ambito_ejecutor = tae24a.id
      LEFT JOIN t49_ac_planes as t49 on t46.id = t49.id_accion_centralizada
      LEFT JOIN mantenimiento.tab_planes_zulia as tpzulia on tpzulia.nu_nivel = 0 AND tpzulia.co_area_estrategica = t49.co_area_estrategica
      WHERE 
      t01.id_tab_ejercicio_fiscal::text = :anio
      ORDER BY 1, 6, 7, 8 ASC;",
                array('anio' => Session::get('ejercicio'))
            );

            // Instantiate a new PHPExcel object
            $objPHPExcel = new PHPExcel();
            // Set properties
            $objPHPExcel->getProperties()->setCreator("Yoser Perez");
            $objPHPExcel->getProperties()->setLastModifiedBy("SPE");
            $objPHPExcel->getProperties()->setTitle("Listado de Responsables");
            $objPHPExcel->getProperties()->setSubject("Reporte");
            $objPHPExcel->getProperties()->setDescription("Reporte para documento de Office 2007 XLSX.");
            // Set the active Excel worksheet to sheet 0
            $objPHPExcel->setActiveSheetIndex(0);
            // Rename sheet
            //$objPHPExcel->getActiveSheet()->getColumnDimension("A")->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(10);
            //$objPHPExcel->getActiveSheet()->getColumnDimension("B")->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(10);
            //$objPHPExcel->getActiveSheet()->getColumnDimension("G")->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension("H")->setWidth(40);
            $objPHPExcel->getActiveSheet()->getColumnDimension("Q")->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension("R")->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension("S")->setWidth(20);
            $objPHPExcel->getActiveSheet()->setTitle('ac_ae_'.Session::get('ejercicio').'_desagregado');
            $objPHPExcel->getActiveSheet()->getStyle('A1:S1')->applyFromArray(
                array(
                        'font'    => array(
                            'bold'      => true
                        ),
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
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
            $objPHPExcel->getActiveSheet()->getStyle('S1')->applyFromArray(
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

            $objPHPExcel->getActiveSheet()->getStyle('S1')->applyFromArray(
                array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                        )
                    )
            );

            $objPHPExcel->getActiveSheet()->getStyle('S1')->applyFromArray(
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
            ->setCellValue('A1', 'Ejercicio')
            ->setCellValue('B1', 'Ejecutor')
            ->setCellValue('C1', 'Sector')
            ->setCellValue('D1', 'Proyecto')
            ->setCellValue('E1', 'Subproyecto')
            ->setCellValue('F1', 'Actividad')
            ->setCellValue('G1', 'Partida')
            ->setCellValue('H1', 'Nombre Partida')
      ->setCellValue('I1', 'Monto Registrado')
      ->setCellValue('J1', 'Nombre de la AC')
      ->setCellValue('K1', 'Nombre de la AE')
      ->setCellValue('L1', 'Nombre del Ejecutor de la AC')
      ->setCellValue('M1', 'Ejecutor de la AE')
      ->setCellValue('N1', 'Nombre del Ejecutor de la AE')
      ->setCellValue('O1', 'Tipo Ejecutor')
      ->setCellValue('P1', 'Fondo')
      ->setCellValue('Q1', 'Ambito')
      ->setCellValue('R1', 'Aplicacion')
      ->setCellValue('S1', 'Area Estrategica');

            foreach ($consulta as $key => $value) {
                // Set thin black border outline around column
                $styleThinBlackBorderOutline = array(
                  'borders' => array(
                    'outline' => array(
                      'style' => PHPExcel_Style_Border::BORDER_THIN,
                      'color' => array('argb' => 'FF000000'),
                    ),
                  ),
                );
                $objPHPExcel->getActiveSheet()->getStyle('A1:S1')->applyFromArray($styleThinBlackBorderOutline);
                // Set cell An to the "name" column from the database (assuming you have a column called name)
                //    where n is the Excel row number (ie cell A1 in the first row)

                $ambito = $value->ambito.' - '.trim($value->de_ambito_ejecutor);

                $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $value->ejercicio, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $value->ejecutor, PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $value->sector, PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $value->proyecto, PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $value->subproyecto, PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, (string)str_pad($value->actividad, 2, "0", STR_PAD_LEFT), PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $value->partida, PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, $value->de_partida, PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, $value->monto, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, $value->ac_nombre, PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('K'.$rowCount, $value->ae_nombre, PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('L'.$rowCount, $value->ac_ej_nombre, PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('M'.$rowCount, $value->ae_ej_id, PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('N'.$rowCount, $value->ae_ej_nombre, PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('O'.$rowCount, $value->de_inicial, PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('P'.$rowCount, $value->fondo, PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('Q'.$rowCount, $ambito, PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('R'.$rowCount, $value->aplicacion, PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('S'.$rowCount, $value->area_estrategica, PHPExcel_Cell_DataType::TYPE_STRING);

                // Increment the Excel row counter
                $rowCount++;
            }

            $objPHPExcel->getActiveSheet()->getStyle('B1:B'.$rowCount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $objPHPExcel->getActiveSheet()->getStyle('C1:C'.$rowCount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $objPHPExcel->getActiveSheet()->getStyle('M1:M'.$rowCount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $objPHPExcel->getActiveSheet()->getStyle('R1:R'.$rowCount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            // Make bold cells
            $objPHPExcel->getActiveSheet()->getStyle('A1:S1')->getFont()->setBold(true);

            // Instantiate a Writer to create an OfficeOpenXML Excel .xlsx file
            $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
            // We'll be outputting an excel file
            header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            // It will be called file.xls
            header('Content-Disposition: attachment; filename="exportacion_icp_ac_desagregado_'.Session::get('ejercicio').'_'.date("H:i:s").'.xlsx"');
            $objWriter->save('php://output');

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

}
