<?php

namespace matriz\Http\Controllers\Reporte;

//*******agregar esta linea******//
use matriz\Models\Ac\tab_ac_responsable;
use matriz\Models\Mantenimiento\tab_ejecutores;
use View;
use Validator;
use Input;
use Response;
use DB;
use Session;
use TCPDF;
use Helper;
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
class PDFresponsable extends TCPDF
{
    public function encabezado($pdf)
    {

        $pdf->Image(public_path().'/images/zulia_escudo.png', 10, 10, 20, 18, 'PNG', '', '', true, 150, '', false, false, 0, false, false, false);
        $pdf->setXY(30, 15);
        $pdf->SetFont('', 'B', 11);
        $pdf->MultiCell(190, 5, 'GOBERNACIÓN DEL ESTADO ZULIA', 0, 'L', 0, 0, '', '', true);
        $pdf->setXY(30, 20);
        $pdf->MultiCell(190, 5, 'PLAN OPERATIVO ANUAL '.Session::get("ejercicio"), 0, 'L', 0, 0, '', '', true);

        return $pdf;
    }

    public function pie($pdf)
    {
        $pdf->setXY(10, -10);
        $pdf->SetFont('', '', 7);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->writeHTMLCell(180, 0, '', '', 'Palacio de los Cóndores, Plaza Bolívar, Maracaibo, Estado Zulia, Venezuela', 0, 0, 0, true, 'C', true);
        $pdf->writeHTMLCell(15, 0, '', '', $pdf->getAliasNumPage().'/'.$pdf->getAliasNbPages(), 0, 0, 0, true, 'C', true);

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
//*******************************//

class acresponsableController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function responsable()
    {

        $ejecutor = tab_ejecutores::select('id', 'id_ejecutor', 'tx_ejecutor')
        ->where('id_ejecutor', '=', Input::get('id_ejecutor'))
        ->first();

        $htmlReporte = '
		<!-- Tabla 1 -->
		<table border="0.1" style="width:100%" style="font-size:9px" cellpadding="3">
		<thead>
		<tr align="left" bgcolor="#E6E6E6">
		<th colspan="5" style="width: 100%;"><b>LISTADO DE RESPONSABLES EJECUTOR: '.$ejecutor->id_ejecutor.' - '.$ejecutor->tx_ejecutor.' </b></th>
		</tr>
		<tr style="font-size:8px">
		<th align="center" bgcolor="#E6E6E6" style="width: 25%;"><b>ACCION CENTRALIZADA</b></th>
		<th align="center" bgcolor="#E6E6E6" style="width: 25%;"><b>TITULAR</b></th>
		<th align="center" bgcolor="#E6E6E6" style="width: 25%;"><b>PLANIFICADOR</b></th>
		<th align="center" bgcolor="#E6E6E6" style="width: 25%;"><b>ADMINISTRADOR</b></th>
		</tr>
		</thead>
		';

        $htmlReporte.='
		<tbody>';

        $responsable = tab_ac_responsable::join('public.t46_acciones_centralizadas as t01', 'public.t48_ac_responsables.id_accion_centralizada', '=', 't01.id')
        ->join('mantenimiento.tab_ac_predefinida as t02', 't01.id_accion', '=', 't02.id')
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
            DB::raw("'AC' || t01.id_ejecutor || id_ejercicio || lpad(id_accion::text, 5, '0') as codigo")
        )
        ->where('id_ejecutor', '=', Input::get('id_ejecutor'))
        ->where('id_ejercicio', '=', Session::get('ejercicio'))
        ->orderBy('id_accion', 'ASC')
        ->get();

        foreach ($responsable as $key => $value) {

            $htmlReporte.='
		<tr style="font-size:7px" nobr="true">
			<td rowspan="6" style="width: 25%;">'.$value->codigo.' - '.$value->de_nombre.'</td>
			<td style="width: 9%;"><b>Cédula</b></td>
			<td style="width: 16%;">'.$value->autorizador_cedula.'</td>
			<td style="width: 9%;"><b>Cédula</b></td>
			<td style="width: 16%;">'.$value->realizador_cedula.'</td>
			<td style="width: 9%;"><b>Cédula</b></td>
			<td style="width: 16%;">'.$value->registrador_cedula.'</td>
		</tr>
		<tr style="font-size:7px">
			<td><b>Nombre</b></td>
			<td>'.$value->autorizador_nombres.'</td>
			<td><b>Nombre</b></td>
			<td>'.$value->realizador_nombres.'</td>
			<td><b>Nombre</b></td>
			<td>'.$value->registrador_nombres.'</td>
		</tr>
		<tr style="font-size:7px">
			<td><b>Cargo</b></td>
			<td>'.$value->autorizador_cargo.'</td>
			<td><b>Cargo</b></td>
			<td>'.$value->realizador_cargo.'</td>
			<td><b>Cargo</b></td>
			<td>'.$value->registrador_cargo.'</td>
		</tr>
		<tr style="font-size:7px">
			<td><b>Unidad de Adscripción</b></td>
			<td>'.$value->autorizador_unidad.'</td>
			<td><b>Unidad de Adscripción</b></td>
			<td>'.$value->realizador_unidad.'</td>
			<td><b>Unidad de Adscripción</b></td>
			<td>'.$value->registrador_unidad.'</td>
		</tr>
		<tr style="font-size:7px">
			<td><b>Correo electrónico</b></td>
			<td>'.$value->autorizador_correo.'</td>
			<td><b>Correo electrónico</b></td>
			<td>'.$value->realizador_correo.'</td>
			<td><b>Correo electrónico</b></td>
			<td>'.$value->registrador_correo.'</td>
		</tr>
		<tr style="font-size:7px">
			<td><b>Teléfono</b></td>
			<td>'.$value->autorizador_telefono.'</td>
			<td><b>Teléfono</b></td>
			<td>'.$value->realizador_telefono.'</td>
			<td><b>Teléfono</b></td>
			<td>'.$value->registrador_telefono.'</td>
		</tr>
		';
        }

        $htmlReporte.='
		</tbody>
		</table>';

        $pdf = new PDFresponsable('P', PDF_UNIT, 'LETTER', true, 'UTF-8', false);
        $pdf->SetCreator('Sistema POA, Yoser Perez');
        $pdf->SetAuthor('Yoser Perez');
        $pdf->SetTitle('Ley de Presupuesto');
        $pdf->SetSubject('Ley de Presupuesto');
        $pdf->SetKeywords('Ley de Presupuesto, PDF, Zulia, SPE, '.Session::get("ejercicio").'');
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetTopMargin(30);
        $pdf->SetPrintHeader(true);
        $pdf->SetPrintFooter(true);
        // set auto page breaks
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->AddPage();
        //Cierre de Reporte
        $pdf->writeHTML(Helper::htmlComprimir($htmlReporte), true, false, false, false, '');
        $pdf->lastPage();
        $pdf->output('LISTADO_RESPONSABLES_AC_'.Input::get('id_ejecutor').'_'.Session::get("ejercicio").'_'.date("H:i:s").'.pdf', 'D');
    }

      /**
      * Display a listing of the resource.
      *
      * @return Response
      */
      public function responsableTodo()
      {

          $htmlReporte = '
		<!-- Tabla 1 -->
		<table border="0.1" style="width:100%" style="font-size:9px" cellpadding="3">
		<thead>
		<tr align="left" bgcolor="#E6E6E6">
		<th colspan="5" style="width: 100%;"><b>LISTADO DE RESPONSABLES POR ACCIONES CENTRALIZADAS</b></th>
		</tr>
		<tr style="font-size:8px">
		<th align="center" bgcolor="#E6E6E6" style="width: 10%;"><b>EJECUTOR</b></th>
		<th align="center" bgcolor="#E6E6E6" style="width: 15%;"><b>ACCION CENTRALIZADA</b></th>
		<th align="center" bgcolor="#E6E6E6" style="width: 25%;"><b>TITULAR</b></th>
		<th align="center" bgcolor="#E6E6E6" style="width: 25%;"><b>PLANIFICADOR</b></th>
		<th align="center" bgcolor="#E6E6E6" style="width: 25%;"><b>ADMINISTRADOR</b></th>
		</tr>
		</thead>
		';

          $htmlReporte.='
		<tbody>';

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

          foreach ($responsable as $key => $value) {

              $htmlReporte.='
		<tr style="font-size:7px" nobr="true">
			<td rowspan="6" style="width: 10%;">'.$value->id_ejecutor.' -  '.$value->tx_ejecutor.'</td>
			<td rowspan="6" style="width: 15%;">'.$value->codigo.' - '.$value->de_nombre.'</td>
			<td style="width: 9%;"><b>Cédula</b></td>
			<td style="width: 16%;">'.$value->autorizador_cedula.'</td>
			<td style="width: 9%;"><b>Cédula</b></td>
			<td style="width: 16%;">'.$value->realizador_cedula.'</td>
			<td style="width: 9%;"><b>Cédula</b></td>
			<td style="width: 16%;">'.$value->registrador_cedula.'</td>
		</tr>
		<tr style="font-size:7px">
			<td><b>Nombre</b></td>
			<td>'.$value->autorizador_nombres.'</td>
			<td><b>Nombre</b></td>
			<td>'.$value->realizador_nombres.'</td>
			<td><b>Nombre</b></td>
			<td>'.$value->registrador_nombres.'</td>
		</tr>
		<tr style="font-size:7px">
			<td><b>Cargo</b></td>
			<td>'.$value->autorizador_cargo.'</td>
			<td><b>Cargo</b></td>
			<td>'.$value->realizador_cargo.'</td>
			<td><b>Cargo</b></td>
			<td>'.$value->registrador_cargo.'</td>
		</tr>
		<tr style="font-size:7px">
			<td><b>Unidad de Adscripción</b></td>
			<td>'.$value->autorizador_unidad.'</td>
			<td><b>Unidad de Adscripción</b></td>
			<td>'.$value->realizador_unidad.'</td>
			<td><b>Unidad de Adscripción</b></td>
			<td>'.$value->registrador_unidad.'</td>
		</tr>
		<tr style="font-size:7px">
			<td><b>Correo electrónico</b></td>
			<td>'.$value->autorizador_correo.'</td>
			<td><b>Correo electrónico</b></td>
			<td>'.$value->realizador_correo.'</td>
			<td><b>Correo electrónico</b></td>
			<td>'.$value->registrador_correo.'</td>
		</tr>
		<tr style="font-size:7px">
			<td><b>Teléfono</b></td>
			<td>'.$value->autorizador_telefono.'</td>
			<td><b>Teléfono</b></td>
			<td>'.$value->realizador_telefono.'</td>
			<td><b>Teléfono</b></td>
			<td>'.$value->registrador_telefono.'</td>
		</tr>
		';
          }

          $htmlReporte.='
		</tbody>
		</table>';

          $pdf = new PDFresponsable('P', PDF_UNIT, 'LETTER', true, 'UTF-8', false);
          $pdf->SetCreator('Sistema POA, Yoser Perez');
          $pdf->SetAuthor('Yoser Perez');
          $pdf->SetTitle('Ley de Presupuesto');
          $pdf->SetSubject('Ley de Presupuesto');
          $pdf->SetKeywords('Ley de Presupuesto, PDF, Zulia, SPE, '.Session::get("ejercicio").'');
          $pdf->SetMargins(10, 10, 10);
          $pdf->SetTopMargin(30);
          $pdf->SetPrintHeader(true);
          $pdf->SetPrintFooter(true);
          // set auto page breaks
          $pdf->SetAutoPageBreak(true, 15);
          $pdf->AddPage();
          //Cierre de Reporte
          $pdf->writeHTML(Helper::htmlComprimir($htmlReporte), true, false, false, false, '');
          $pdf->lastPage();
          $pdf->output('LISTADO_RESPONSABLES_AC_'.Session::get("ejercicio").'_'.date("H:i:s").'.pdf', 'D');
      }

      /**
       * Display a listing of the resource.
       *
       * @return Response
       */
      public function responsableExportar()
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
              ->where('t01.id_ejecutor', '=', Input::get('id_ejecutor'))
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
              $objPHPExcel->getActiveSheet()->setTitle(Input::get('id_ejecutor').'_RESPONSABLES');
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
              header('Content-Disposition: attachment; filename="RESPONSABLES_'.Input::get('id_ejecutor').'_'.Session::get('ejercicio').'_'.date("H:i:s").'.xlsx"');
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
      public function responsableTodoExportar()
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
              header('Content-Disposition: attachment; filename="AC_RESPONSABLES_'.Session::get('ejercicio').'_'.date("H:i:s").'.xlsx"');
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
}
