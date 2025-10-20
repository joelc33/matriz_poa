<?php

namespace matriz\Http\Controllers\Reporte;

//*******agregar esta linea******//
use matriz\Models\AcSegto\tab_meta_financiera;
use matriz\Models\AcSegto\tab_forma_001;
use matriz\Models\AcSegto\tab_ac;
use matriz\Models\Ac\tab_meta_fisica;
use View;
use Input;
use Response;
use DB;
use Auth;
use TCPDF;
use Crypt;
use File;
use Blade;
use Session;
use Helper;
//*******************************//
use Illuminate\Http\Request;

use matriz\Http\Requests;
use matriz\Http\Controllers\Controller;

//*******clase extendida TCPDF******//
class PDFseguimientoAC extends TCPDF
{
    public function encabezado($pdf)
    {

        $pdf->Image(public_path().'/images/zulia_escudo.png', 10, 10, 20, 18, 'PNG', '', '', true, 150, '', false, false, 0, false, false, false);
        $pdf->setXY(30, 15);
//        $pdf->SetFont('', 'B', 11);
//        $pdf->MultiCell(190, 5, 'GOBERNACIÓN DEL ESTADO ZULIA', 0, 'L', 0, 0, '', '', true);
//        $pdf->setXY(30, 20);
//        $pdf->MultiCell(190, 5, 'PLAN OPERATIVO ANUAL '.Session::get("ejercicio"), 0, 'L', 0, 0, '', '', true);
        $pdf->setY(10);
        $pdf->MultiCell(277, 5, 'SISTEMA DE SEGUIMIENTO, EVALUACIÓN Y CONTROL DEL PLAN OPERATIVO ESTADAL', 0, 'C', 0, 0, '', '', true);
        $pdf->Ln(5);
        $pdf->MultiCell(277, 5, 'FORMULARIO Nº 1', 0, 'C', 0, 0, '', '', true);
        $pdf->Ln(5);
        $pdf->MultiCell(277, 5, 'MARCO NORMATIVO INSTITUCIONAL', 0, 'C', 0, 0, '', '', true);
        $pdf->Ln(5);
//        $pdf->MultiCell(275, 5, Session::get("periodo"), 0, 'R', 0, 0, '', '', true);        

        return $pdf;
    }

    public function pie($pdf)
    {
        $pdf->setXY(10, -10);
        $pdf->SetFont('', '', 9);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->writeHTMLCell(250, 0, '', '', 'Palacio de los Cóndores, Plaza Bolívar, Maracaibo, Estado Zulia, Venezuela', 0, 0, 0, true, 'C', true);
        $pdf->SetFont('', '', 9);
        $pdf->writeHTMLCell(15, 0, '', '', Session::get("periodo"), 0, 0, 0, true, 'C', true);

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

class acseguimiento001Controller extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

      /**
       * Display a listing of the resource.
       *
       * @return \Illuminate\Http\Response
       */
      public function reporte()
      {
          return View::make('reporte.seguimiento.ac');
      }

      /**
       * Display a listing of the resource.
       *
       * @return \Illuminate\Http\Response
       */
      public function ficha001($id)
      {

          

            $data = tab_ac::join('mantenimiento.tab_ejecutores as t04', 't04.id_ejecutor', '=', 'ac_seguimiento.tab_ac.id_ejecutor')              
            ->leftjoin('ac_seguimiento.tab_ac_vinculo as t49', 't49.id_tab_ac', '=', 'ac_seguimiento.tab_ac.id')
            ->leftjoin('t45_planes_zulia as t45', 't45.co_area_estrategica', '=', 't49.co_area_estrategica')
            ->join('mantenimiento.tab_lapso as t02', 'ac_seguimiento.tab_ac.id_tab_lapso', '=', 't02.id')        
            ->select(
            'nu_codigo',
            'id_tab_ejecutores',
            'ac_seguimiento.tab_ac.id_tab_ejercicio_fiscal',
            'tx_ejecutor_ac',
            't45.tx_descripcion as tx_area_estrategica',        
            'id_tab_ac_predefinida',
            'id_tab_sectores',
            'id_tab_estatus',
            'id_tab_situacion_presupuestaria',
            'id_tab_tipo_registro',
            'co_new_etapa',
            'de_ac',
            'mo_ac',
            'mo_calculado',
            'ac_seguimiento.tab_ac.inst_mision',
            'ac_seguimiento.tab_ac.inst_vision',
            'ac_seguimiento.tab_ac.inst_objetivos',
            'ac_seguimiento.tab_ac.nu_po_beneficiar',
            'ac_seguimiento.tab_ac.nu_em_previsto',
            'ac_seguimiento.tab_ac.tx_re_esperado',
            't02.id_tab_tipo_periodo',
            'id_tab_lapso',
            'in_bloquear_001',
            DB::raw("to_char(t02.fe_inicio, 'dd/mm/YYYY') as fe_inicio"),
            DB::raw("to_char(t02.fe_fin, 'dd/mm/YYYY') as fe_fin"),        
            'de_observacion_001'
        )
        ->where('ac_seguimiento.tab_ac.id', '=', $id)
        ->first();
            
                if($data->id_tab_tipo_periodo==19){
                    
                    $periodo = '1T/'.$data->id_tab_ejercicio_fiscal;    
                }
                
                if($data->id_tab_tipo_periodo==20){
                    
                    $periodo = '2T/'.$data->id_tab_ejercicio_fiscal;    
                }

                if($data->id_tab_tipo_periodo==21){
                    
                    $periodo = '3T/'.$data->id_tab_ejercicio_fiscal;    
                }

                if($data->id_tab_tipo_periodo==22){
                    
                    $periodo = '4T/'.$data->id_tab_ejercicio_fiscal;    
                }            
            
        
            
          Session::put('periodo',$periodo);
//          var_dump($data->nu_codigo);
//          exit();
          
          /******Objetivos*********/

	$htmlObjetivo = '
<table border="0.1" style="width:100%;text-align: center;" cellpadding="3">
	<tr align="left">
		<td colspan="2"><b>1.2. UNIDAD EJECUTORA RESPONSABLE: </b>'.$data->tx_ejecutor_ac.'</td>
	</tr>
	<tr align="left">
		<td colspan="2"><b>2.5.1. AREA ESTRATEGICA: </b>'.$data->tx_area_estrategica.'</td>
	</tr>
	<tr>
		<td><b>MISIÓN</b></td>
		<td><b>VISIÓN</b></td>
	</tr>
	<tr>
		<td height="100" align="justify">'.$data->inst_mision.'</td>
		<td height="100" align="justify">'.$data->inst_vision.'</td>
	</tr>
<thead>
	<tr>
		<td colspan="2"><b>OBJETIVOS INSTITUCIONALES</b></td>
	</tr>
</thead>
<tbody>
	<tr nobr="true">
		<td colspan="2" height="100" align="justify">'.str_replace(array("\r\n","\r","\n","\\r","\\n","\\r\\n"),"<br/>",$data->inst_objetivos).'</td>
	</tr>
</tbody>
</table>';

        

        

          $pdf = new PDFseguimientoAC("L", PDF_UNIT, 'Letter', true, 'UTF-8', false);
          $pdf->SetCreator('Sistema POA, Yoser Perez');
          $pdf->SetAuthor('Yoser Perez');
          $pdf->SetTitle('Seguimiento AC');
          $pdf->SetTitle('Seguimiento AC');
          $pdf->SetSubject('Seguimiento AC');
          $pdf->SetKeywords('Seguimiento AC, PDF, Zulia, SPE, '.Session::get("ejercicio").'');
          $pdf->SetMargins(10,10,10);
          $pdf->SetTopMargin(30);
          $pdf->SetPrintHeader(true);
          $pdf->SetPrintFooter(true);
          // set auto page breaks
          $pdf->SetAutoPageBreak(true, 10);
          $pdf->AddPage();
//          $pdf->encabezado($pdf,$data);
          $pdf->SetFont('','',11);
          $pdf->writeHTML(Helper::htmlComprimir($htmlObjetivo), true, false, false, false, '');
          $pdf->lastPage();
          $pdf->output('SEGUIMIENTO_AC_'.Session::get("ejercicio").'_'.date("H:i:s").'.pdf', 'D');
      }
      
}
