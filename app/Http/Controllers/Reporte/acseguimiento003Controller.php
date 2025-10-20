<?php

namespace matriz\Http\Controllers\Reporte;

//*******agregar esta linea******//
use matriz\Models\AcSegto\tab_meta_fisica;
use matriz\Models\AcSegto\tab_meta_financiera;
use matriz\Models\AcSegto\tab_forma_001;
use matriz\Models\AcSegto\tab_ac;
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
        $pdf->MultiCell(277, 5, 'FORMULARIO Nº 3', 0, 'C', 0, 0, '', '', true);
        $pdf->Ln(5);
        $pdf->MultiCell(277, 5, 'METAS FINANCIERAS', 0, 'C', 0, 0, '', '', true);
        $pdf->Ln(5);
//        $pdf->MultiCell(275, 5, Session::get("periodo"), 0, 'R', 0, 0, '', '', true);
        $pdf->Ln(5);        

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

class acseguimiento003Controller extends Controller
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
	function formatoDinero($numero, $fractional=true){
	    if ($fractional) {
		$numero = sprintf('%.2f', $numero);
	    }
	    while (true) {
		$replaced = preg_replace('/(-?\d+)(\d\d\d)/', '$1,$2', $numero);
		if ($replaced != $numero) {
		    $numero = $replaced;
		} else {
		    break;
		}
	    }
	    return $numero;
	}
      /**
       * Display a listing of the resource.
       *
       * @return \Illuminate\Http\Response
       */      
      
      public function ficha003($id)
      {
          
          $pdf = new PDFseguimientoAC("L", PDF_UNIT, 'Letter', true, 'UTF-8', false);
          $pdf->SetCreator('Sistema POA, Yoser Perez');
          $pdf->SetAuthor('Yoser Perez');
          $pdf->SetTitle('Seguimiento AC');
          $pdf->SetSubject('Seguimiento AC');
          $pdf->SetKeywords('Seguimiento AC, PDF, Zulia, SPE, '.Session::get("ejercicio").'');
          $pdf->SetMargins(10,10,10);
          $pdf->SetTopMargin(30);
          $pdf->SetPrintHeader(true);
          $pdf->SetPrintFooter(true);
          // set auto page breaks
          $pdf->SetAutoPageBreak(true, 10);          

            $data = tab_ac::join('mantenimiento.tab_ejecutores as t04', 't04.id_ejecutor', '=', 'ac_seguimiento.tab_ac.id_ejecutor')
            ->join('mantenimiento.tab_lapso as t02', 'ac_seguimiento.tab_ac.id_tab_lapso', '=', 't02.id')
            ->leftjoin('ac_seguimiento.tab_ac_ae as t21', 't21.id_tab_ac', '=', 'ac_seguimiento.tab_ac.id')
            ->leftjoin('t52_ac_predefinidas as t52', 't52.id', '=', 'ac_seguimiento.tab_ac.id_tab_ac_predefinida')        
            ->leftjoin('ac_seguimiento.tab_ac_vinculo as t49', 't49.id_tab_ac', '=', 'ac_seguimiento.tab_ac.id')
            ->leftjoin('t53_ac_ae_predefinidas as t53', 't53.id', '=', 't21.id_tab_ac_ae_predefinida')
            ->leftjoin('t45_planes_zulia as t45', function ($join) {
            $join->on('t49.co_area_estrategica', '=', 't45.co_area_estrategica')
            ->on('t45.nu_nivel', '=', DB::raw('0'));
            })
            ->leftjoin('t45_planes_zulia as t45a', function ($join) {
            $join->on('t49.co_area_estrategica', '=', 't45a.co_area_estrategica')
            ->on('t49.co_ambito_estado', '=', 't45a.co_ambito_zulia')        
            ->on('t45a.nu_nivel', '=', DB::raw('1'));
            })
            ->leftjoin('t45_planes_zulia as t45b', function ($join) {
            $join->on('t49.co_ambito_estado', '=', 't45b.co_ambito_zulia')
            ->on('t49.co_macroproblema', '=', 't45b.co_macroproblema')
            ->on('t45b.edo_reg', '=', DB::raw('true'))        
            ->on('t45b.nu_nivel', '=', DB::raw('3'));
            })
            ->leftjoin('t45_planes_zulia as t45c', function ($join) {
            $join->on('t49.co_ambito_estado', '=', 't45c.co_ambito_zulia')
            ->on(DB::raw('t49.co_nodos::integer'), '=', 't45c.co_nodo')       
            ->on('t45c.edo_reg', '=', DB::raw('true'))        
            ->on('t45c.nu_nivel', '=', DB::raw('4'));
            })            
            ->join('mantenimiento.tab_sectores as t18a', 'ac_seguimiento.tab_ac.id_tab_sectores', '=', 't18a.id')
            ->join('mantenimiento.tab_sectores as t18b', function ($join) {
            $join->on('t18a.co_sector', '=', 't18b.co_sector')
            ->on('t18b.nu_nivel', '=', DB::raw('1'));
            })
            ->leftjoin('t20_planes as t20', function ($join) {
            $join->on('t49.co_objetivo_historico', '=', 't20.co_objetivo_historico')
            ->on('t20.nu_nivel', '=', DB::raw('1'));
            }) 
            ->leftjoin('t20_planes as t20a', function ($join) {
            $join->on('t49.co_objetivo_nacional', '=', 't20a.co_objetivo_nacional')
            ->on('t49.co_objetivo_historico', '=', 't20a.co_objetivo_historico')        
            ->on('t20a.nu_nivel', '=', DB::raw('2'));
            })
            ->leftjoin('t20_planes as t20b', function ($join) {
            $join->on('t49.co_objetivo_estrategico', '=', 't20b.co_objetivo_estrategico')
            ->on('t49.co_objetivo_historico', '=', 't20b.co_objetivo_historico')        
            ->on('t49.co_objetivo_nacional', '=', 't20b.co_objetivo_nacional')        
            ->on('t20b.nu_nivel', '=', DB::raw('3'));
            })
            ->leftjoin('t20_planes as t20c', function ($join) {
            $join->on('t49.co_objetivo_general', '=', 't20c.co_objetivo_general')
            ->on('t49.co_objetivo_estrategico', '=', 't20c.co_objetivo_estrategico')
            ->on('t49.co_objetivo_historico', '=', 't20c.co_objetivo_historico')        
            ->on('t49.co_objetivo_nacional', '=', 't20c.co_objetivo_nacional')
            ->on('t20c.edo_reg', '=', DB::raw('true'))        
            ->on('t20c.nu_nivel', '=', DB::raw('4'));
            })            
            ->select(
            'ac_seguimiento.tab_ac.id_ejecutor',
            'tx_ejecutor_ac',
            't18b.tx_codigo as tx_sector',
            't45.tx_descripcion as tx_area_estrategica',
            't20.tx_descripcion as tx_objetivo_historico',
            't20a.tx_descripcion as tx_objetivo_nacional',
            't20b.tx_descripcion as tx_objetivo_estrategico',
            't20c.tx_descripcion as tx_objetivo_general',
            't45a.tx_descripcion as tx_ambito_estado', 
            't45b.tx_descripcion as tx_macroproblema',
            't45c.tx_descripcion as tx_nodos',
            't21.objetivo_institucional as tx_objetivo_institucional',
            DB::raw("'AC' || t04.id_ejecutor || ac_seguimiento.tab_ac.id_tab_ejercicio_fiscal || lpad(ac_seguimiento.tab_ac.id_tab_ac_predefinida::text, 5, '0') as id_proy_ac"),
            't52.nombre',
            DB::raw('t53.numero::text as tx_codigo_ae'),
            't53.nombre as tx_nombre_ae',
            't21.id_ejecutor as id_ejecutor_ae',
            'ac_seguimiento.tab_ac.pp_anual as tx_pr_objetivo',
            'ac_seguimiento.tab_ac.tx_pr_obtenido',
            'ac_seguimiento.tab_ac.de_observacion_003',
            DB::raw("to_char(t02.fe_inicio, 'dd/mm/YYYY') as fe_inicio"),
            DB::raw("to_char(t02.fe_fin, 'dd/mm/YYYY') as fe_fin"),
            't21.id as id_tab_ac_ae',
            'ac_seguimiento.tab_ac.id_tab_ejercicio_fiscal',
            'id_tab_tipo_periodo',
            'ac_seguimiento.tab_ac.de_observacion_003',
            'ac_seguimiento.tab_ac.de_sector'
        )
        ->where('t21.id_tab_ac', '=', $id)
        ->get(); 

           
            
            foreach($data as $data) {
            
                if($data->id_tab_tipo_periodo==19){
                    
                    $periodo = '1T/'.Session::get("ejercicio");    
                }
                
                if($data->id_tab_tipo_periodo==20){
                    
                    $periodo = '2T/'.Session::get("ejercicio");    
                }

                if($data->id_tab_tipo_periodo==21){
                    
                    $periodo = '3T/'.Session::get("ejercicio");    
                }

                if($data->id_tab_tipo_periodo==22){
                    
                    $periodo = '4T/'.Session::get("ejercicio");    
                }   
            
          Session::put('periodo',$periodo); 

            $actividad = tab_meta_fisica::select('tab_meta_fisica.id','codigo','nb_meta',DB::raw('coalesce(mo_presupuesto,0) as mo_presupuesto'),DB::raw('coalesce(mo_modificado_anual,0) as mo_modificado_anual'),DB::raw('coalesce(mo_modificado,0) as mo_modificado'),DB::raw('coalesce(mo_actualizado_anual,0) as mo_actualizado_anual'),
            DB::raw('coalesce(mo_comprometido,0) as mo_comprometido'),DB::raw('coalesce(mo_causado,0) as mo_causado'),DB::raw('coalesce(mo_pagado,0) as mo_pagado'),'de_fuente_financiamiento','co_partida',
            'nu_numero',
            'nu_original',
            'co_sector')
            ->join('ac_seguimiento.tab_meta_financiera as t22', 'tab_meta_fisica.id', '=', 't22.id_tab_meta_fisica')
            ->join('mantenimiento.tab_fuente_financiamiento as t66', 't22.id_tab_fuente_financiamiento', '=', 't66.id')
             ->join('ac_seguimiento.tab_ac_ae as t03', 'tab_meta_fisica.id_tab_ac_ae', '=', 't03.id')
             ->join('mantenimiento.tab_ac_ae_predefinida as t04', 't03.id_tab_ac_ae_predefinida', '=', 't04.id')
             ->join('ac_seguimiento.tab_ac as t05', 't03.id_tab_ac', '=', 't05.id')
             ->join('mantenimiento.tab_ac_predefinida as t06', 't05.id_tab_ac_predefinida', '=', 't06.id')
             ->join('mantenimiento.tab_sectores as t07', 't05.id_tab_sectores', '=', 't07.id')  
             ->join('mantenimiento.tab_lapso as t08', 't05.id_tab_lapso', '=', 't08.id')
            ->where('id_tab_ac_ae', '=', $data->id_tab_ac_ae)
            ->where('id_tab_tipo_periodo', '=', $data->id_tab_tipo_periodo)
            ->orderBy('codigo', 'ASC')
            ->orderBy('co_partida', 'ASC')
            ->get();
            
            $actividad_accion = tab_meta_fisica::select('codigo','nb_meta',DB::raw('coalesce(mo_presupuesto,0) as mo_presupuesto'),DB::raw('coalesce(mo_modificado_anual,0) as mo_modificado_anual'),DB::raw('coalesce(mo_modificado,0) as mo_modificado'),DB::raw('coalesce(mo_actualizado_anual,0) as mo_actualizado_anual'),
            DB::raw('coalesce(mo_comprometido,0) as mo_comprometido'),DB::raw('coalesce(mo_causado,0) as mo_causado'),DB::raw('coalesce(mo_pagado,0) as mo_pagado'),'de_fuente_financiamiento','co_partida',
            'nu_numero',
            'nu_original',
            'co_sector')
            ->join('ac_seguimiento.tab_meta_financiera as t22', 'tab_meta_fisica.id', '=', 't22.id_tab_meta_fisica')
            ->join('mantenimiento.tab_fuente_financiamiento as t66', 't22.id_tab_fuente_financiamiento', '=', 't66.id')
             ->join('ac_seguimiento.tab_ac_ae as t03', 'tab_meta_fisica.id_tab_ac_ae', '=', 't03.id')
             ->join('mantenimiento.tab_ac_ae_predefinida as t04', 't03.id_tab_ac_ae_predefinida', '=', 't04.id')
             ->join('ac_seguimiento.tab_ac as t05', 't03.id_tab_ac', '=', 't05.id')
             ->join('mantenimiento.tab_ac_predefinida as t06', 't05.id_tab_ac_predefinida', '=', 't06.id')
             ->join('mantenimiento.tab_sectores as t07', 't05.id_tab_sectores', '=', 't07.id')   
             ->join('mantenimiento.tab_lapso as t08', 't05.id_tab_lapso', '=', 't08.id')
             ->where('t05.nu_codigo', '=', $data->id_proy_ac)
            ->where('t05.in_activo', '=', true)
            ->where('id_tab_tipo_periodo', '=', $data->id_tab_tipo_periodo)
            ->where('t05.id_tab_ejercicio_fiscal', '=', $data->id_tab_ejercicio_fiscal)
            ->orderBy('codigo', 'ASC')
            ->get();    
            
            $actividad_ejecutor = tab_meta_fisica::select('codigo','nb_meta',DB::raw('coalesce(mo_presupuesto,0) as mo_presupuesto'),DB::raw('coalesce(mo_modificado_anual,0) as mo_modificado_anual')
            ,DB::raw('coalesce(mo_modificado,0) as mo_modificado'),DB::raw('coalesce(mo_actualizado_anual,0) as mo_actualizado_anual')
            ,DB::raw('coalesce(mo_comprometido,0) as mo_comprometido'),DB::raw('coalesce(mo_causado,0) as mo_causado'),DB::raw('coalesce(mo_pagado,0) as mo_pagado'))
            ->join('ac_seguimiento.tab_meta_financiera as t22', 'tab_meta_fisica.id', '=', 't22.id_tab_meta_fisica')
            ->join('mantenimiento.tab_fuente_financiamiento as t66', 't22.id_tab_fuente_financiamiento', '=', 't66.id')
             ->join('ac_seguimiento.tab_ac_ae as t03', 'tab_meta_fisica.id_tab_ac_ae', '=', 't03.id')
             ->join('mantenimiento.tab_ac_ae_predefinida as t04', 't03.id_tab_ac_ae_predefinida', '=', 't04.id')
             ->join('ac_seguimiento.tab_ac as t05', 't03.id_tab_ac', '=', 't05.id')
            ->join('mantenimiento.tab_lapso as t08', 't05.id_tab_lapso', '=', 't08.id')
            ->where('t05.id_ejecutor', '=', $data->id_ejecutor)
            ->where('t05.in_activo', '=', true)
            ->where('id_tab_tipo_periodo', '=', $data->id_tab_tipo_periodo)
            ->where('t05.id_tab_ejercicio_fiscal', '=', $data->id_tab_ejercicio_fiscal)
            ->orderBy('codigo', 'ASC')
            ->get();             

                
                $mo_presupuesto_anual_ejecutor = 0;
                $mo_modificado_anual_ejecutor = 0;
                $mo_modificado_anual_ejecutor_acu = 0;
                $mo_actualizado_anual_ejecutor = 0;
                $mo_comprometido_ejecutor = 0;
                $mo_causado_ejecutor = 0;
                $mo_pagado_ejecutor = 0;

                $mo_presupuesto_anual_accion = 0;
                $mo_modificado_anual_accion = 0;
                $mo_modificado_anual_accion_acu = 0;
                $mo_actualizado_anual_accion = 0;
                $mo_comprometido_accion = 0;
                $mo_causado_accion = 0;
                $mo_pagado_accion = 0;                 
                
      foreach($actividad_accion as $item1) {
          

                $mo_presupuesto_anual_accion = $mo_presupuesto_anual_accion + $item1->mo_presupuesto;
                $mo_modificado_anual_accion = $mo_modificado_anual_accion + $item1->mo_modificado_anual;
                $mo_modificado_anual_accion_acu = $mo_modificado_anual_accion_acu + $item1->mo_modificado;
                $mo_actualizado_anual_accion = $mo_actualizado_anual_accion + ($item1->mo_presupuesto + $item1->mo_modificado_anual + $item1->mo_modificado);
                $mo_comprometido_accion = $mo_comprometido_accion + $item1->mo_comprometido;
                $mo_causado_accion = $mo_causado_accion + $item1->mo_causado;
                $mo_pagado_accion = $mo_pagado_accion + $item1->mo_pagado;

      } 
      
      foreach($actividad_ejecutor as $item2) {
          

                $mo_presupuesto_anual_ejecutor = $mo_presupuesto_anual_ejecutor + $item2->mo_presupuesto;
                $mo_modificado_anual_ejecutor = $mo_modificado_anual_ejecutor + $item2->mo_modificado_anual;
                $mo_modificado_anual_ejecutor_acu = $mo_modificado_anual_ejecutor_acu + $item2->mo_modificado;
                $mo_actualizado_anual_ejecutor = $mo_actualizado_anual_ejecutor + ($item2->mo_presupuesto + $item2->mo_modificado_anual + $item2->mo_modificado);
                $mo_comprometido_ejecutor = $mo_comprometido_ejecutor + $item2->mo_comprometido;
                $mo_causado_ejecutor = $mo_causado_ejecutor + $item2->mo_causado;
                $mo_pagado_ejecutor = $mo_pagado_ejecutor + $item2->mo_pagado;

      }      
          
$html1 = '
<table border="0.1" style="width:100%" style="font-size:10px" cellpadding="3">
<tbody>
<tr style="font-size:9px">
<td style="width: 50%;"><b>'.$data->id_ejecutor.'</b> - '.$data->tx_ejecutor_ac.'</td>
<td style="width: 15%;"><b>SECTOR:</b> '.$data->de_sector.'</td>
<td style="width: 35%;"><b>AREA ESTRATEGICA:</b> '.$data->tx_area_estrategica.'</td>
</tr>
<tr style="font-size:9px">
<td rowspan="2" style="width: 30%;" align="justify"><b>OBJETIVO HISTORICO:</b> '.$data->tx_objetivo_historico.'</td>
<td colspan="2" style="width: 70%;" align="justify"><b>OBJETIVO(s) NACIONAL(ES):</b> '.$data->tx_objetivo_nacional.'</td>
</tr>
<tr style="font-size:9px">
<td colspan="2" style="width: 70%;" align="justify"><b>OBJETIVO(S) ESTRATEGICO(S):</b> '.$data->tx_objetivo_estrategico.'</td>
</tr>
<tr style="font-size:9px">
<td colspan="3" align="justify"><b>OBJETIVO GENERAL:</b> '.$data->tx_objetivo_general.'</td>
</tr>
<tr style="font-size:9px">
<td rowspan="2"><b>AMBITO:</b> '.$data->tx_ambito_estado.'</td>
<td colspan="2"><b>PDEZ/NOMBRE DEL PROBLEMA:</b> '.$data->tx_macroproblema.'</td>
</tr>
<tr style="font-size:9px">
<td colspan="2"><b>PDEZ/LÍNEA MATRIZ:</b> '.$data->tx_nodos.'</td>
</tr>
<tr style="font-size:9px">
<td colspan="3"><b>OBJETIVO INSTITUCIONAL POA:</b> '.$data->tx_objetivo_institucional.'</td>
</tr>
<tr style="font-size:9px">
<td colspan="3"><b>ACCION C.:</b> '.$data->id_proy_ac.' - '.$data->nombre.'</td>
</tr>
<tr style="font-size:9px">
<td style="width: 80%;"><b>ACCION E.:</b> '.$data->tx_codigo_ae.' - '.$data->tx_nombre_ae.'</td>
<td style="width: 20%;"><b>COD. EJECUTOR:</b> '.$data->id_ejecutor_ae.' </td>
</tr>
<tr style="font-size:9px">
<td colspan="3" style="width: 50%;" align="justify"><b>PRODUCTO PROGRAMADO ANUAL DEL OBJETIVO INSTITUCIONAL:</b> '.$data->tx_pr_objetivo.'</td>
<td colspan="3" style="width: 50%;" align="justify"><b>PRODUCTO OBTENIDO DEL OBJETIVO INSTITUCIONAL:</b> '.$data->tx_pr_obtenido.'</td>
</tr>
</tbody>
</table>
';

$html23='';
$html23.= '
<table border="0.1" style="width:100%" style="font-size:9px" cellpadding="3">
<thead>
<tr align="center" bgcolor="#BDBDBD">
<th colspan="11" style="width: 16%;" rowspan="2"><b>ACTIVIDADES</b></th>
<th colspan="11" style="width: 60%;"><b>METAS FINANCIERAS</b></th>
<th colspan="11" style="width: 24%;"><b>CATEGORÍA PRESUPUESTARIA</b></th>
</tr>
<tr style="font-size:6px">
<th align="center" bgcolor="#BDBDBD" style="width: 9%;" rowspan="2">PRESUPUESTO PROGRAM. ANUAL (Bs.)</th>
<th align="center" bgcolor="#BDBDBD" style="width: 9%;" rowspan="2">PRESUPUESTO MODIFICADO ANUAL T. ANT. (Bs.)</th>
<th align="center" bgcolor="#BDBDBD" style="width: 9%;" rowspan="2">PRESUPUESTO MODIFICADO ANUAL TRI (Bs.)</th>
<th align="center" bgcolor="#BDBDBD" style="width: 9%;" rowspan="2">PRESUPUESTO ACTUALIZADO ANUAL (Bs.)</th>
<th align="center" bgcolor="#BDBDBD" style="width: 8%;" rowspan="2">PRESUPUESTO COMPROM. AL CORTE (Bs.)</th>
<th align="center" bgcolor="#BDBDBD" style="width: 8%;" rowspan="2">PRESUPUESTO CAUSADO AL CORTE (Bs.)</th>
<th align="center" bgcolor="#BDBDBD" style="width: 8%;">PRESUPUESTO PAGADO AL CORTE (Bs.)</th>
<th align="center" bgcolor="#BDBDBD" style="width: 4%;">SECTOR</th>
<th align="center" bgcolor="#BDBDBD" style="width: 5%;">PROY. Y/O A. CENTRAL.</th>
<th align="center" bgcolor="#BDBDBD" style="width: 4%;">ACCIÓN ESP.</th>
<th align="center" bgcolor="#BDBDBD" style="width: 3%;">PART.</th>
<th align="center" bgcolor="#BDBDBD" style="width: 8%;">FUENTE FINANCIAMIENTO</th>
</tr>
</thead>
';        
       
$html23.='
<tbody>';

$mo_presupuesto = 0;
$mo_modificado_anual = 0;
$mo_modificado_anual_acu = 0;
$mo_actualizado_anual = 0;
$mo_comprometido = 0;
$mo_causado = 0;
$mo_pagado = 0;
$i = 1;
$id = 0;

      foreach($actividad as $item) {
          
          
             $tab_meta_financiera = tab_meta_financiera::where('id_tab_meta_fisica', '=', $item->id)
            ->get();         
             if($tab_meta_financiera->count()>1){
                $i =  $tab_meta_financiera->count();
             }else{
             $i = 1;    
             }
          
             if($id==$item->id){
             
		$html23.='
		<tr style="font-size:6px" nobr="true">
		<td style="width: 9%;"  align="center">'.$this->formatoDinero($item->mo_presupuesto).'</td>
                <td style="width: 9%;"  align="center">'.$this->formatoDinero($item->mo_modificado).'</td>
		<td style="width: 9%;"  align="center">'.$this->formatoDinero($item->mo_modificado_anual).'</td>
                <td style="width: 9%;" align="center">'.$this->formatoDinero($item->mo_presupuesto + $item->mo_modificado_anual + $item->mo_modificado).'</td>
                <td style="width: 8%;" align="center">'.$this->formatoDinero($item->mo_comprometido).'</td>                    
		<td style="width: 8%;"  align="center">'.$this->formatoDinero($item->mo_causado).'</td>
		<td style="width: 8%;" align="center">'.$this->formatoDinero($item->mo_pagado).'</td>
                <td style="width: 4%;" align="center">'.$item->co_sector.'</td>
                <td style="width: 5%;" align="center">'.$item->nu_original.'</td>
                <td style="width: 4%;" align="center">0'.$item->nu_numero.'</td>
                <td style="width: 3%;" align="center">'.$item->co_partida.'</td>
		<td style="width: 8%;" align="center">'.$item->de_fuente_financiamiento.'</td>';
                $html23.='</tr>'; 
             }else{
                 
		$html23.='
		<tr style="font-size:6px" nobr="true">
		<td style="width: 16%;"  nobr="true" rowspan="'.$i.'">'.$item->codigo.' - '.$item->nb_meta.'</td>
		<td style="width: 9%;"  align="center">'.$this->formatoDinero($item->mo_presupuesto).'</td>
                <td style="width: 9%;"  align="center">'.$this->formatoDinero($item->mo_modificado).'</td>
                <td style="width: 9%;"  align="center">'.$this->formatoDinero($item->mo_modificado_anual).'</td>
                <td style="width: 9%;" align="center">'.$this->formatoDinero($item->mo_presupuesto + $item->mo_modificado_anual + $item->mo_modificado).'</td>
                <td style="width: 8%;" align="center">'.$this->formatoDinero($item->mo_comprometido).'</td>                    
		<td style="width: 8%;"  align="center">'.$this->formatoDinero($item->mo_causado).'</td>
		<td style="width: 8%;" align="center">'.$this->formatoDinero($item->mo_pagado).'</td>
                <td style="width: 4%;" align="center">'.$item->co_sector.'</td>
                <td style="width: 5%;" align="center">'.$item->nu_original.'</td>
                <td style="width: 4%;" align="center">0'.$item->nu_numero.'</td>
                <td style="width: 3%;" align="center">'.$item->co_partida.'</td>
		<td style="width: 8%;" align="center">'.$item->de_fuente_financiamiento.'</td>';
                $html23.='</tr>';                 
                
             }
                $id =$item->id;
                $mo_presupuesto = $mo_presupuesto + $item->mo_presupuesto;
                $mo_modificado_anual = $mo_modificado_anual + $item->mo_modificado_anual;
                $mo_modificado_anual_acu = $mo_modificado_anual_acu + $item->mo_modificado;
                $mo_actualizado_anual = $mo_actualizado_anual + ($item->mo_presupuesto + $item->mo_modificado_anual + $item->mo_modificado);
                $mo_comprometido = $mo_comprometido + $item->mo_comprometido;
                $mo_causado = $mo_causado + $item->mo_causado;
                $mo_pagado = $mo_pagado + $item->mo_pagado;
                

          
      }        

$html23.='      
<tr style="font-size:6px" nobr="true">
		<td style="width: 16%;"  nobr="true"><b>TOTAL POR ACCION ESPECÍFICA</b></td>
		<td style="width: 9%;"  align="center"><b>'.$this->formatoDinero($mo_presupuesto).'</b></td>
                <td style="width: 9%;"  align="center"><b>'.$this->formatoDinero($mo_modificado_anual_acu).'</b></td>
		<td style="width: 9%;"  align="center"><b>'.$this->formatoDinero($mo_modificado_anual).'</b></td>
                <td style="width: 9%;" align="center"><b>'.$this->formatoDinero($mo_actualizado_anual).'</b></td>
                <td style="width: 8%;" align="center"><b>'.$this->formatoDinero($mo_comprometido).'</b></td>                    
		<td style="width: 8%;"  align="center"><b>'.$this->formatoDinero($mo_causado).'</b></td>
		<td style="width: 8%;" align="center"><b>'.$this->formatoDinero($mo_pagado).'</b></td>
</tr>
<tr style="font-size:6px" nobr="true">
		<td style="width: 16%;"  nobr="true"><b>TOTAL POR ACCION CENTRALIZADA</b></td>
		<td style="width: 9%;"  align="center"><b>'.$this->formatoDinero($mo_presupuesto_anual_accion).'</b></td>
                <td style="width: 9%;"  align="center"><b>'.$this->formatoDinero($mo_modificado_anual_accion_acu).'</b></td>
		<td style="width: 9%;"  align="center"><b>'.$this->formatoDinero($mo_modificado_anual_accion).'</b></td>
                <td style="width: 9%;" align="center"><b>'.$this->formatoDinero($mo_actualizado_anual_accion).'</b></td>
                <td style="width: 8%;" align="center"><b>'.$this->formatoDinero($mo_comprometido_accion).'</b></td>                    
		<td style="width: 8%;"  align="center"><b>'.$this->formatoDinero($mo_causado_accion).'</b></td>
		<td style="width: 8%;" align="center"><b>'.$this->formatoDinero($mo_pagado_accion).'</b></td>
</tr>
<tr style="font-size:6px" nobr="true">
		<td style="width: 16%;"  nobr="true"> <b>PRESUPUESTO TOTAL EJECUTOR:</b></td>
		<td style="width: 9%;"  align="center"><b>'.$this->formatoDinero($mo_presupuesto_anual_ejecutor).'</b></td>
                <td style="width: 9%;"  align="center"><b>'.$this->formatoDinero($mo_modificado_anual_ejecutor_acu).'</b></td>
		<td style="width: 9%;"  align="center"><b>'.$this->formatoDinero($mo_modificado_anual_ejecutor).'</b></td>
                <td style="width: 9%;" align="center"><b>'.$this->formatoDinero($mo_actualizado_anual_ejecutor).'</b></td>
                <td style="width: 8%;" align="center"><b>'.$this->formatoDinero($mo_comprometido_ejecutor).'</b></td>                    
		<td style="width: 8%;"  align="center"><b>'.$this->formatoDinero($mo_causado_ejecutor).'</b></td>
		<td style="width: 8%;" align="center"><b>'.$this->formatoDinero($mo_pagado_ejecutor).'</b></td>
</tr>
<tr style="font-size:9px">
<td colspan="9" style="width: 100%;height: 30px;" align="justify"><b>OBSERVACIONES:</b>  '.$data->de_observacion_003.'</td>
</tr>'        ;
      
$html23.='
</tbody>
</table>';


          $pdf->AddPage();

          $pdf->SetFont('','',11);
//          $pdf->writeHTML($htmlObjetivo, true, false, false, false, '');
          $pdf->writeHTML(Helper::htmlComprimir($html1), true, false, false, false, '');
          $pdf->Ln(-3);
          $pdf->writeHTML($html23, true, false, false, false, '');
          
            }
          $pdf->lastPage();
          $pdf->output('SEGUIMIENTO_AC_'.Session::get("ejercicio").'_'.date("H:i:s").'.pdf', 'D');
      }      

      
      public function ficha003Acumulada($id)
      {
          
          $pdf = new PDFseguimientoAC("L", PDF_UNIT, 'Letter', true, 'UTF-8', false);
          $pdf->SetCreator('Sistema POA, Yoser Perez');
          $pdf->SetAuthor('Yoser Perez');
          $pdf->SetTitle('Seguimiento AC');
          $pdf->SetSubject('Seguimiento AC');
          $pdf->SetKeywords('Seguimiento AC, PDF, Zulia, SPE, '.Session::get("ejercicio").'');
          $pdf->SetMargins(10,10,10);
          $pdf->SetTopMargin(30);
          $pdf->SetPrintHeader(true);
          $pdf->SetPrintFooter(true);
          // set auto page breaks
          $pdf->SetAutoPageBreak(true, 10);          

            $data = tab_ac::join('mantenimiento.tab_ejecutores as t04', 't04.id_ejecutor', '=', 'ac_seguimiento.tab_ac.id_ejecutor')
            ->join('mantenimiento.tab_lapso as t02', 'ac_seguimiento.tab_ac.id_tab_lapso', '=', 't02.id')
            ->leftjoin('ac_seguimiento.tab_ac_ae as t21', 't21.id_tab_ac', '=', 'ac_seguimiento.tab_ac.id')
            ->leftjoin('t52_ac_predefinidas as t52', 't52.id', '=', 'ac_seguimiento.tab_ac.id_tab_ac_predefinida')        
            ->leftjoin('ac_seguimiento.tab_ac_vinculo as t49', 't49.id_tab_ac', '=', 'ac_seguimiento.tab_ac.id')
            ->leftjoin('t53_ac_ae_predefinidas as t53', 't53.id', '=', 't21.id_tab_ac_ae_predefinida')
            ->leftjoin('t45_planes_zulia as t45', function ($join) {
            $join->on('t49.co_area_estrategica', '=', 't45.co_area_estrategica')
            ->on('t45.nu_nivel', '=', DB::raw('0'));
            })
            ->leftjoin('t45_planes_zulia as t45a', function ($join) {
            $join->on('t49.co_area_estrategica', '=', 't45a.co_area_estrategica')
            ->on('t49.co_ambito_estado', '=', 't45a.co_ambito_zulia')        
            ->on('t45a.nu_nivel', '=', DB::raw('1'));
            })
            ->leftjoin('t45_planes_zulia as t45b', function ($join) {
            $join->on('t49.co_ambito_estado', '=', 't45b.co_ambito_zulia')
            ->on('t49.co_macroproblema', '=', 't45b.co_macroproblema')
            ->on('t45b.edo_reg', '=', DB::raw('true'))        
            ->on('t45b.nu_nivel', '=', DB::raw('3'));
            })
            ->leftjoin('t45_planes_zulia as t45c', function ($join) {
            $join->on('t49.co_ambito_estado', '=', 't45c.co_ambito_zulia')
            ->on(DB::raw('t49.co_nodos::integer'), '=', 't45c.co_nodo')       
            ->on('t45c.edo_reg', '=', DB::raw('true'))        
            ->on('t45c.nu_nivel', '=', DB::raw('4'));
            })            
            ->join('mantenimiento.tab_sectores as t18a', 'ac_seguimiento.tab_ac.id_tab_sectores', '=', 't18a.id')
            ->join('mantenimiento.tab_sectores as t18b', function ($join) {
            $join->on('t18a.co_sector', '=', 't18b.co_sector')
            ->on('t18b.nu_nivel', '=', DB::raw('1'));
            })
            ->leftjoin('t20_planes as t20', function ($join) {
            $join->on('t49.co_objetivo_historico', '=', 't20.co_objetivo_historico')
            ->on('t20.nu_nivel', '=', DB::raw('1'));
            }) 
            ->leftjoin('t20_planes as t20a', function ($join) {
            $join->on('t49.co_objetivo_nacional', '=', 't20a.co_objetivo_nacional')
            ->on('t49.co_objetivo_historico', '=', 't20a.co_objetivo_historico')        
            ->on('t20a.nu_nivel', '=', DB::raw('2'));
            })
            ->leftjoin('t20_planes as t20b', function ($join) {
            $join->on('t49.co_objetivo_estrategico', '=', 't20b.co_objetivo_estrategico')
            ->on('t49.co_objetivo_historico', '=', 't20b.co_objetivo_historico')        
            ->on('t49.co_objetivo_nacional', '=', 't20b.co_objetivo_nacional')        
            ->on('t20b.nu_nivel', '=', DB::raw('3'));
            })
            ->leftjoin('t20_planes as t20c', function ($join) {
            $join->on('t49.co_objetivo_general', '=', 't20c.co_objetivo_general')
            ->on('t49.co_objetivo_estrategico', '=', 't20c.co_objetivo_estrategico')
            ->on('t49.co_objetivo_historico', '=', 't20c.co_objetivo_historico')        
            ->on('t49.co_objetivo_nacional', '=', 't20c.co_objetivo_nacional')
            ->on('t20c.edo_reg', '=', DB::raw('true'))        
            ->on('t20c.nu_nivel', '=', DB::raw('4'));
            })            
            ->select(
            'ac_seguimiento.tab_ac.id_ejecutor',
            'tx_ejecutor_ac',
            't18b.tx_codigo as tx_sector',
            't45.tx_descripcion as tx_area_estrategica',
            't20.tx_descripcion as tx_objetivo_historico',
            't20a.tx_descripcion as tx_objetivo_nacional',
            't20b.tx_descripcion as tx_objetivo_estrategico',
            't20c.tx_descripcion as tx_objetivo_general',
            't45a.tx_descripcion as tx_ambito_estado', 
            't45b.tx_descripcion as tx_macroproblema',
            't45c.tx_descripcion as tx_nodos',
            't21.objetivo_institucional as tx_objetivo_institucional',
            DB::raw("'AC' || t04.id_ejecutor || ac_seguimiento.tab_ac.id_tab_ejercicio_fiscal || lpad(ac_seguimiento.tab_ac.id_tab_ac_predefinida::text, 5, '0') as id_proy_ac"),
            't52.nombre',
            DB::raw('t53.numero::text as tx_codigo_ae'),
            't53.nombre as tx_nombre_ae',
            't21.id_ejecutor as id_ejecutor_ae',
            'ac_seguimiento.tab_ac.pp_anual as tx_pr_objetivo',
            'ac_seguimiento.tab_ac.tx_pr_obtenido',
            'ac_seguimiento.tab_ac.tx_pr_obtenido_a',
            'ac_seguimiento.tab_ac.de_observacion_003',
            DB::raw("to_char(t02.fe_inicio, 'dd/mm/YYYY') as fe_inicio"),
            DB::raw("to_char(t02.fe_fin, 'dd/mm/YYYY') as fe_fin"),
            't21.id as id_tab_ac_ae',
            'ac_seguimiento.tab_ac.id_tab_ejercicio_fiscal',
            'id_tab_tipo_periodo',
            'ac_seguimiento.tab_ac.de_observacion_003',
            'ac_seguimiento.tab_ac.de_sector',
            't21.id_tab_ac_ae_predefinida'
        )
        ->where('t21.id_tab_ac', '=', $id)
        ->get(); 

           
            
            foreach($data as $data) {
            
                if($data->id_tab_tipo_periodo==19){
                    
                    $periodo = '1TA/'.Session::get("ejercicio");    
                }
                
                if($data->id_tab_tipo_periodo==20){
                    
                    $periodo = '2TA/'.Session::get("ejercicio");    
                }

                if($data->id_tab_tipo_periodo==21){
                    
                    $periodo = '3TA/'.Session::get("ejercicio");    
                }

                if($data->id_tab_tipo_periodo==22){
                    
                    $periodo = '4TA/'.Session::get("ejercicio");    
                } 
            
          Session::put('periodo',$periodo); 

            $actividad = tab_meta_fisica::select('codigo','nb_meta',DB::raw('sum(coalesce(mo_presupuesto,0)) as mo_presupuesto'),
            DB::raw('sum(coalesce(mo_modificado_anual,0)) as mo_modificado_anual'),DB::raw('sum(coalesce(mo_actualizado_anual,0)) as mo_actualizado_anual'),DB::raw('sum(coalesce(mo_modificado,0)) as mo_modificado'),
            'de_fuente_financiamiento','co_partida','id_tab_fuente_financiamiento',
            'nu_numero',
            'nu_original',
            'co_sector')
            ->join('ac_seguimiento.tab_meta_financiera as t22', 'tab_meta_fisica.id', '=', 't22.id_tab_meta_fisica')
            ->join('mantenimiento.tab_fuente_financiamiento as t66', 't22.id_tab_fuente_financiamiento', '=', 't66.id')
             ->join('ac_seguimiento.tab_ac_ae as t03', 'tab_meta_fisica.id_tab_ac_ae', '=', 't03.id')
             ->join('mantenimiento.tab_ac_ae_predefinida as t04', 't03.id_tab_ac_ae_predefinida', '=', 't04.id')
             ->join('ac_seguimiento.tab_ac as t05', 't03.id_tab_ac', '=', 't05.id')
             ->join('mantenimiento.tab_ac_predefinida as t06', 't05.id_tab_ac_predefinida', '=', 't06.id')
             ->join('mantenimiento.tab_sectores as t07', 't05.id_tab_sectores', '=', 't07.id')                  
            ->where('id_tab_ac_ae', '=', $data->id_tab_ac_ae)
            ->orderBy('codigo', 'ASC')
            ->orderBy('co_partida', 'ASC')
             ->groupBy('codigo')
             ->groupBy('nb_meta')
             ->groupBy('co_partida')
             ->groupBy('id_tab_fuente_financiamiento')
             ->groupBy('de_fuente_financiamiento')
             ->groupBy('nu_numero') 
            ->groupBy('nu_original') 
            ->groupBy('co_sector') 
            ->get();
            
            $actividad_accion = tab_meta_fisica::select('codigo','nb_meta',DB::raw('coalesce(mo_presupuesto,0) as mo_presupuesto'),DB::raw('coalesce(mo_modificado_anual,0) as mo_modificado_anual'),DB::raw('coalesce(mo_actualizado_anual,0) as mo_actualizado_anual'),DB::raw('coalesce(mo_modificado,0) as mo_modificado'),
            DB::raw('coalesce(mo_comprometido,0) as mo_comprometido'),DB::raw('coalesce(mo_causado,0) as mo_causado'),DB::raw('coalesce(mo_pagado,0) as mo_pagado'),'de_fuente_financiamiento','co_partida',
            'nu_numero',
            'nu_original',
            'co_sector')
            ->join('ac_seguimiento.tab_meta_financiera as t22', 'tab_meta_fisica.id', '=', 't22.id_tab_meta_fisica')
            ->join('mantenimiento.tab_fuente_financiamiento as t66', 't22.id_tab_fuente_financiamiento', '=', 't66.id')
             ->join('ac_seguimiento.tab_ac_ae as t03', 'tab_meta_fisica.id_tab_ac_ae', '=', 't03.id')
             ->join('mantenimiento.tab_ac_ae_predefinida as t04', 't03.id_tab_ac_ae_predefinida', '=', 't04.id')
             ->join('ac_seguimiento.tab_ac as t05', 't03.id_tab_ac', '=', 't05.id')
             ->join('mantenimiento.tab_ac_predefinida as t06', 't05.id_tab_ac_predefinida', '=', 't06.id')
             ->join('mantenimiento.tab_sectores as t07', 't05.id_tab_sectores', '=', 't07.id')   
             ->join('mantenimiento.tab_lapso as t08', 't05.id_tab_lapso', '=', 't08.id')
             ->where('t05.nu_codigo', '=', $data->id_proy_ac)
             ->where('t05.in_activo', '=', true)
            ->where('id_tab_tipo_periodo', '=', $data->id_tab_tipo_periodo)
            ->orderBy('codigo', 'ASC')
            ->get();    
            
            $actividad_ejecutor = tab_meta_fisica::select('codigo','nb_meta',DB::raw('coalesce(mo_presupuesto,0) as mo_presupuesto'),DB::raw('coalesce(mo_modificado_anual,0) as mo_modificado_anual'),DB::raw('coalesce(mo_actualizado_anual,0) as mo_actualizado_anual'),DB::raw('coalesce(mo_modificado,0) as mo_modificado'),
            DB::raw('coalesce(mo_comprometido,0) as mo_comprometido'),DB::raw('coalesce(mo_causado,0) as mo_causado'),DB::raw('coalesce(mo_pagado,0) as mo_pagado'),'de_fuente_financiamiento','co_partida',
            'nu_numero',
            'nu_original',
            'co_sector')
            ->join('ac_seguimiento.tab_meta_financiera as t22', 'tab_meta_fisica.id', '=', 't22.id_tab_meta_fisica')
            ->join('mantenimiento.tab_fuente_financiamiento as t66', 't22.id_tab_fuente_financiamiento', '=', 't66.id')
             ->join('ac_seguimiento.tab_ac_ae as t03', 'tab_meta_fisica.id_tab_ac_ae', '=', 't03.id')
             ->join('mantenimiento.tab_ac_ae_predefinida as t04', 't03.id_tab_ac_ae_predefinida', '=', 't04.id')
             ->join('ac_seguimiento.tab_ac as t05', 't03.id_tab_ac', '=', 't05.id')
             ->join('mantenimiento.tab_ac_predefinida as t06', 't05.id_tab_ac_predefinida', '=', 't06.id')
             ->join('mantenimiento.tab_sectores as t07', 't05.id_tab_sectores', '=', 't07.id')
            ->join('mantenimiento.tab_lapso as t08', 't05.id_tab_lapso', '=', 't08.id')
            ->where('t05.id_ejecutor', '=', $data->id_ejecutor)
            ->where('t05.in_activo', '=', true)
            ->where('id_tab_tipo_periodo', '=', $data->id_tab_tipo_periodo)
            ->where('t05.id_tab_ejercicio_fiscal', '=', $data->id_tab_ejercicio_fiscal)
            ->orderBy('codigo', 'ASC')
            ->get();             
            
                $mo_presupuesto_anual_accion = 0;
                $mo_modificado_anual_accion = 0;
                $mo_actualizado_anual_accion = 0;
                $mo_comprometido_accion = 0;
                $mo_causado_accion = 0;
                $mo_pagado_accion = 0; 
                
                $mo_presupuesto_anual_ejecutor = 0;
                $mo_modificado_anual_ejecutor = 0;
                $mo_actualizado_anual_ejecutor = 0;
                $mo_comprometido_ejecutor = 0;
                $mo_causado_ejecutor = 0;
                $mo_pagado_ejecutor = 0;                
                
      foreach($actividad_accion as $item1) {
          
                $data2 = tab_ac::select(
                 DB::raw("coalesce(sum(mo_comprometido),0) as mo_comprometido"),
                DB::raw("coalesce(sum(mo_causado),0) as mo_causado"),
                DB::raw("coalesce(sum(mo_pagado),0) as mo_pagado")
                )
                ->join('ac_seguimiento.tab_ac_ae as t01', 'ac_seguimiento.tab_ac.id', '=', 't01.id_tab_ac')
                ->join('ac_seguimiento.tab_meta_fisica as t02', 't01.id', '=', 't02.id_tab_ac_ae')
                ->join('ac_seguimiento.tab_meta_financiera as t03', 't03.id_tab_meta_fisica', '=', 't02.id')
                ->join('mantenimiento.tab_lapso as t04', 'ac_seguimiento.tab_ac.id_tab_lapso', '=', 't04.id')
                ->where('ac_seguimiento.tab_ac.nu_codigo', '=', $data->id_proy_ac)
                ->where('ac_seguimiento.tab_ac.in_activo', '=', true)
                ->where('id_tab_tipo_periodo', '<=', $data->id_tab_tipo_periodo)
                ->where('ac_seguimiento.tab_ac.id_tab_ejercicio_fiscal', '=', $data->id_tab_ejercicio_fiscal)
                ->first();          
          

                $mo_presupuesto_anual_accion = $mo_presupuesto_anual_accion + $item1->mo_presupuesto;
                $mo_modificado_anual_accion = $mo_modificado_anual_accion + ($item1->mo_modificado_anual + $item1->mo_modificado);
                $mo_actualizado_anual_accion = $mo_actualizado_anual_accion + ($item1->mo_presupuesto + $item1->mo_modificado_anual + $item1->mo_modificado);
                $mo_comprometido_accion = $data2->mo_comprometido;
                $mo_causado_accion = $data2->mo_causado;
                $mo_pagado_accion = $data2->mo_pagado;

      } 
      
      foreach($actividad_ejecutor as $item2) {
          
                $data2 = tab_ac::select(
                 DB::raw("coalesce(sum(mo_comprometido),0) as mo_comprometido"),
                DB::raw("coalesce(sum(mo_causado),0) as mo_causado"),
                DB::raw("coalesce(sum(mo_pagado),0) as mo_pagado")
                )
                ->join('ac_seguimiento.tab_ac_ae as t01', 'ac_seguimiento.tab_ac.id', '=', 't01.id_tab_ac')
                ->join('ac_seguimiento.tab_meta_fisica as t02', 't01.id', '=', 't02.id_tab_ac_ae')
                ->join('ac_seguimiento.tab_meta_financiera as t03', 't03.id_tab_meta_fisica', '=', 't02.id')
                ->join('mantenimiento.tab_lapso as t04', 'ac_seguimiento.tab_ac.id_tab_lapso', '=', 't04.id')
                ->where('ac_seguimiento.tab_ac.id_ejecutor', '=', $data->id_ejecutor)
                ->where('ac_seguimiento.tab_ac.in_activo', '=', true)
                ->where('id_tab_tipo_periodo', '<=', $data->id_tab_tipo_periodo)
                ->where('ac_seguimiento.tab_ac.id_tab_ejercicio_fiscal', '=', $data->id_tab_ejercicio_fiscal)
                ->first();          

                $mo_presupuesto_anual_ejecutor = $mo_presupuesto_anual_ejecutor + $item2->mo_presupuesto;
                $mo_modificado_anual_ejecutor = $mo_modificado_anual_ejecutor + ($item2->mo_modificado_anual + $item2->mo_modificado);
                $mo_actualizado_anual_ejecutor = $mo_actualizado_anual_ejecutor + ($item2->mo_presupuesto + $item2->mo_modificado_anual + $item2->mo_modificado);
                $mo_comprometido_ejecutor = $data2->mo_comprometido;
                $mo_causado_ejecutor = $data2->mo_causado;
                $mo_pagado_ejecutor = $data2->mo_pagado;

      }      
          
$html1 = '
<table border="0.1" style="width:100%" style="font-size:10px" cellpadding="3">
<tbody>
<tr style="font-size:9px">
<td style="width: 50%;"><b>'.$data->id_ejecutor.'</b> - '.$data->tx_ejecutor_ac.'</td>
<td style="width: 15%;"><b>SECTOR:</b> '.$data->de_sector.'</td>
<td style="width: 35%;"><b>AREA ESTRATEGICA:</b> '.$data->tx_area_estrategica.'</td>
</tr>
<tr style="font-size:9px">
<td rowspan="2" style="width: 30%;" align="justify"><b>OBJETIVO HISTORICO:</b> '.$data->tx_objetivo_historico.'</td>
<td colspan="2" style="width: 70%;" align="justify"><b>OBJETIVO(s) NACIONAL(ES):</b> '.$data->tx_objetivo_nacional.'</td>
</tr>
<tr style="font-size:9px">
<td colspan="2" style="width: 70%;" align="justify"><b>OBJETIVO(S) ESTRATEGICO(S):</b> '.$data->tx_objetivo_estrategico.'</td>
</tr>
<tr style="font-size:9px">
<td colspan="3" align="justify"><b>OBJETIVO GENERAL:</b> '.$data->tx_objetivo_general.'</td>
</tr>
<tr style="font-size:9px">
<td rowspan="2"><b>AMBITO:</b> '.$data->tx_ambito_estado.'</td>
<td colspan="2"><b>PDEZ/NOMBRE DEL PROBLEMA:</b> '.$data->tx_macroproblema.'</td>
</tr>
<tr style="font-size:9px">
<td colspan="2"><b>PDEZ/LÍNEA MATRIZ:</b> '.$data->tx_nodos.'</td>
</tr>
<tr style="font-size:9px">
<td colspan="3"><b>OBJETIVO INSTITUCIONAL POA:</b> '.$data->tx_objetivo_institucional.'</td>
</tr>
<tr style="font-size:9px">
<td colspan="3"><b>ACCION C.:</b> '.$data->id_proy_ac.' - '.$data->nombre.'</td>
</tr>
<tr style="font-size:9px">
<td style="width: 80%;"><b>ACCION E.:</b> '.$data->tx_codigo_ae.' - '.$data->tx_nombre_ae.'</td>
<td style="width: 20%;"><b>COD. EJECUTOR:</b> '.$data->id_ejecutor_ae.' </td>
</tr>
<tr style="font-size:9px">
<td colspan="3" style="width: 50%;" align="justify"><b>PRODUCTO PROGRAMADO ANUAL DEL OBJETIVO INSTITUCIONAL:</b> '.$data->tx_pr_objetivo.'</td>
<td colspan="3" style="width: 50%;" align="justify"><b>PRODUCTO OBTENIDO DEL OBJETIVO INSTITUCIONAL:</b> '.$data->tx_pr_obtenido_a.'</td>
</tr>
</tbody>
</table>
';

$html23='';
$html23.= '
<table border="0.1" style="width:100%" style="font-size:9px" cellpadding="3">
<thead>
<tr align="center" bgcolor="#BDBDBD">
<th colspan="11" style="width: 16%;" rowspan="2"><b>ACTIVIDADES</b></th>
<th colspan="11" style="width: 54%;"><b>METAS FINANCIERAS</b></th>
<th colspan="11" style="width: 30%;"><b>CATEGORÍA PRESUPUESTARIA</b></th>
</tr>
<tr style="font-size:6px">
<th align="center" bgcolor="#BDBDBD" style="width: 9%;" rowspan="2">PRESUPUESTO PROGRAM. ANUAL (Bs.)</th>
<th align="center" bgcolor="#BDBDBD" style="width: 9%;" rowspan="2">PRESUPUESTO MODIFICADO ANUAL (Bs.)</th>
<th align="center" bgcolor="#BDBDBD" style="width: 9%;" rowspan="2">PRESUPUESTO ACTUALIZADO ANUAL (Bs.)</th>
<th align="center" bgcolor="#BDBDBD" style="width: 9%;" rowspan="2">PRESUPUESTO COMPROM. AL CORTE (Bs.)</th>
<th align="center" bgcolor="#BDBDBD" style="width: 9%;" rowspan="2">PRESUPUESTO CAUSADO AL CORTE (Bs.)</th>
<th align="center" bgcolor="#BDBDBD" style="width: 9%;">PRESUPUESTO PAGADO AL CORTE (Bs.)</th>
<th align="center" bgcolor="#BDBDBD" style="width: 5%;">SECTOR</th>
<th align="center" bgcolor="#BDBDBD" style="width: 7%;">PROY. Y/O A. CENTRAL.</th>
<th align="center" bgcolor="#BDBDBD" style="width: 5%;">ACCIÓN ESP.</th>
<th align="center" bgcolor="#BDBDBD" style="width: 5%;">PART.</th>
<th align="center" bgcolor="#BDBDBD" style="width: 8%;">FUENTE FINANCIAMIENTO</th>
</tr>
</thead>
';        
       
$html23.='
<tbody>';

$mo_presupuesto = 0;
$mo_modificado_anual = 0;
$mo_actualizado_anual = 0;
$mo_comprometido = 0;
$mo_causado = 0;
$mo_pagado = 0;
$i = 1;
$id = 0;

      foreach($actividad as $item) {
          
                $data2 = tab_ac::select(
                 DB::raw("coalesce(sum(mo_comprometido),0) as mo_comprometido"),
                DB::raw("coalesce(sum(mo_causado),0) as mo_causado"),
                DB::raw("coalesce(sum(mo_pagado),0) as mo_pagado")
                )
                ->join('ac_seguimiento.tab_ac_ae as t01', 'ac_seguimiento.tab_ac.id', '=', 't01.id_tab_ac')
                ->join('ac_seguimiento.tab_meta_fisica as t02', 't01.id', '=', 't02.id_tab_ac_ae')
                ->join('ac_seguimiento.tab_meta_financiera as t03', 't03.id_tab_meta_fisica', '=', 't02.id')
                ->join('mantenimiento.tab_lapso as t04', 'ac_seguimiento.tab_ac.id_tab_lapso', '=', 't04.id')
                ->where('ac_seguimiento.tab_ac.nu_codigo', '=', $data->id_proy_ac)
                ->where('ac_seguimiento.tab_ac.in_activo', '=', true)
                ->where('t02.codigo', '=', $item->codigo)
                ->where('t01.id_tab_ac_ae_predefinida', '=', $data->id_tab_ac_ae_predefinida)
                ->where('t03.co_partida', '=', $item->co_partida)
                ->where('t03.id_tab_fuente_financiamiento', '=', $item->id_tab_fuente_financiamiento)
                ->where('id_tab_tipo_periodo', '<=', $data->id_tab_tipo_periodo)
                ->where('ac_seguimiento.tab_ac.id_tab_ejercicio_fiscal', '=', $data->id_tab_ejercicio_fiscal)
                ->first();          
          
            $tab_meta_financiera = tab_meta_fisica::select('codigo','nb_meta',DB::raw('sum(coalesce(mo_presupuesto,0)) as mo_presupuesto'),
            DB::raw('sum(coalesce(mo_modificado_anual,0)) as mo_modificado_anual'),DB::raw('sum(coalesce(mo_actualizado_anual,0)) as mo_actualizado_anual'),DB::raw('sum(coalesce(mo_modificado,0)) as mo_modificado'),
            'de_fuente_financiamiento','co_partida','id_tab_fuente_financiamiento',
            'nu_numero',
            'nu_original',
            'co_sector')
            ->join('ac_seguimiento.tab_meta_financiera as t22', 'tab_meta_fisica.id', '=', 't22.id_tab_meta_fisica')
            ->join('mantenimiento.tab_fuente_financiamiento as t66', 't22.id_tab_fuente_financiamiento', '=', 't66.id')
             ->join('ac_seguimiento.tab_ac_ae as t03', 'tab_meta_fisica.id_tab_ac_ae', '=', 't03.id')
             ->join('mantenimiento.tab_ac_ae_predefinida as t04', 't03.id_tab_ac_ae_predefinida', '=', 't04.id')
             ->join('ac_seguimiento.tab_ac as t05', 't03.id_tab_ac', '=', 't05.id')
             ->join('mantenimiento.tab_ac_predefinida as t06', 't05.id_tab_ac_predefinida', '=', 't06.id')
             ->join('mantenimiento.tab_sectores as t07', 't05.id_tab_sectores', '=', 't07.id')                  
            ->where('id_tab_ac_ae', '=', $data->id_tab_ac_ae)
            ->where('codigo', '=', $item->codigo)
            ->orderBy('codigo', 'ASC')
            ->orderBy('co_partida', 'ASC')
             ->groupBy('codigo')
             ->groupBy('nb_meta')
             ->groupBy('co_partida')
             ->groupBy('id_tab_fuente_financiamiento')
             ->groupBy('de_fuente_financiamiento')
             ->groupBy('nu_numero') 
            ->groupBy('nu_original') 
            ->groupBy('co_sector') 
            ->get();          
//             $tab_meta_financiera = tab_meta_financiera::where('id_tab_meta_fisica', '=', $item->id)
//            ->get();         
             if($tab_meta_financiera->count()>1){
                $i =  $tab_meta_financiera->count();
             }else{
             $i = 1;    
             }
          
             if($id==$item->codigo){
             
		$html23.='
		<tr style="font-size:6px" nobr="true">
		<td style="width: 9%;"  align="center">'.$this->formatoDinero($item->mo_presupuesto).'</td>
		<td style="width: 9%;"  align="center">'.$this->formatoDinero($item->mo_modificado_anual + $item->mo_modificado).'</td>
                <td style="width: 9%;" align="center">'.$this->formatoDinero(($item->mo_presupuesto + $item->mo_modificado_anual + $item->mo_modificado)).'</td>
                <td style="width: 9%;" align="center">'.$this->formatoDinero($data2->mo_comprometido).'</td>                    
		<td style="width: 9%;"  align="center">'.$this->formatoDinero($data2->mo_causado).'</td>
		<td style="width: 9%;" align="center">'.$this->formatoDinero($data2->mo_pagado).'</td>
                <td style="width: 5%;" align="center">'.$item->co_sector.'</td>
                <td style="width: 7%;" align="center">'.$item->nu_original.'</td>
                <td style="width: 5%;" align="center">0'.$item->nu_numero.'</td>
                <td style="width: 5%;" align="center">'.$item->co_partida.'</td>
		<td style="width: 8%;" align="center">'.$item->de_fuente_financiamiento.'</td>';
                $html23.='</tr>'; 
             }else{
                 
		$html23.='
		<tr style="font-size:6px" nobr="true">
		<td style="width: 16%;"  nobr="true" rowspan="'.$i.'">'.$item->codigo.' - '.$item->nb_meta.'</td>
		<td style="width: 9%;"  align="center">'.$this->formatoDinero($item->mo_presupuesto).'</td>
		<td style="width: 9%;"  align="center">'.$this->formatoDinero($item->mo_modificado_anual + $item->mo_modificado).'</td>
                <td style="width: 9%;" align="center">'.$this->formatoDinero(($item->mo_presupuesto + $item->mo_modificado_anual + $item->mo_modificado)).'</td>
                <td style="width: 9%;" align="center">'.$this->formatoDinero($data2->mo_comprometido).'</td>                    
		<td style="width: 9%;"  align="center">'.$this->formatoDinero($data2->mo_causado).'</td>
		<td style="width: 9%;" align="center">'.$this->formatoDinero($data2->mo_pagado).'</td>
                <td style="width: 5%;" align="center">'.$item->co_sector.'</td>
                <td style="width: 7%;" align="center">'.$item->nu_original.'</td>
                <td style="width: 5%;" align="center">0'.$item->nu_numero.'</td>
                <td style="width: 5%;" align="center">'.$item->co_partida.'</td>
		<td style="width: 8%;" align="center">'.$item->de_fuente_financiamiento.'</td>';
                $html23.='</tr>';                 
                
             }
                $id =$item->codigo;
                $mo_presupuesto = $mo_presupuesto + $item->mo_presupuesto;
                $mo_modificado_anual = $mo_modificado_anual + ($item->mo_modificado_anual + $item->mo_modificado);
                $mo_actualizado_anual = $mo_actualizado_anual + ($item->mo_presupuesto + $item->mo_modificado_anual + $item->mo_modificado);
                $mo_comprometido = $mo_comprometido + $data2->mo_comprometido;
                $mo_causado = $mo_causado + $data2->mo_causado;
                $mo_pagado = $mo_pagado + $data2->mo_pagado;
                

          
      }        

$html23.='      
<tr style="font-size:6px" nobr="true">
		<td style="width: 16%;"  nobr="true"><b>TOTAL POR ACCION ESPECÍFICA</b></td>
		<td style="width: 9%;"  align="center"><b>'.$this->formatoDinero($mo_presupuesto).'</b></td>
		<td style="width: 9%;"  align="center"><b>'.$this->formatoDinero($mo_modificado_anual).'</b></td>
                <td style="width: 9%;" align="center"><b>'.$this->formatoDinero($mo_actualizado_anual).'</b></td>
                <td style="width: 9%;" align="center"><b>'.$this->formatoDinero($mo_comprometido).'</b></td>                    
		<td style="width: 9%;"  align="center"><b>'.$this->formatoDinero($mo_causado).'</b></td>
		<td style="width: 9%;" align="center"><b>'.$this->formatoDinero($mo_pagado).'</b></td>
</tr>
<tr style="font-size:6px" nobr="true">
		<td style="width: 16%;"  nobr="true"><b>TOTAL POR ACCION CENTRALIZADA</b></td>
		<td style="width: 9%;"  align="center"><b>'.$this->formatoDinero($mo_presupuesto_anual_accion).'</b></td>
		<td style="width: 9%;"  align="center"><b>'.$this->formatoDinero($mo_modificado_anual_accion).'</b></td>
                <td style="width: 9%;" align="center"><b>'.$this->formatoDinero($mo_actualizado_anual_accion).'</b></td>
                <td style="width: 9%;" align="center"><b>'.$this->formatoDinero($mo_comprometido_accion).'</b></td>                    
		<td style="width: 9%;"  align="center"><b>'.$this->formatoDinero($mo_causado_accion).'</b></td>
		<td style="width: 9%;" align="center"><b>'.$this->formatoDinero($mo_pagado_accion).'</b></td>
</tr>
<tr style="font-size:6px" nobr="true">
		<td style="width: 16%;"  nobr="true"> <b>PRESUPUESTO TOTAL EJECUTOR:</b></td>
		<td style="width: 9%;"  align="center"><b>'.$this->formatoDinero($mo_presupuesto_anual_ejecutor).'</b></td>
		<td style="width: 9%;"  align="center"><b>'.$this->formatoDinero($mo_modificado_anual_ejecutor).'</b></td>
                <td style="width: 9%;" align="center"><b>'.$this->formatoDinero($mo_actualizado_anual_ejecutor).'</b></td>
                <td style="width: 9%;" align="center"><b>'.$this->formatoDinero($mo_comprometido_ejecutor).'</b></td>                    
		<td style="width: 9%;"  align="center"><b>'.$this->formatoDinero($mo_causado_ejecutor).'</b></td>
		<td style="width: 9%;" align="center"><b>'.$this->formatoDinero($mo_pagado_ejecutor).'</b></td>
</tr>
<tr style="font-size:9px">
<td colspan="9" style="width: 100%;height: 30px;" align="justify"><b>OBSERVACIONES:</b>  '.$data->de_observacion_003.'</td>
</tr>'        ;
      
$html23.='
</tbody>
</table>';


          $pdf->AddPage();

          $pdf->SetFont('','',11);
//          $pdf->writeHTML($htmlObjetivo, true, false, false, false, '');
          $pdf->writeHTML(Helper::htmlComprimir($html1), true, false, false, false, '');
          $pdf->Ln(-3);
          $pdf->writeHTML($html23, true, false, false, false, '');
          
            }
          $pdf->lastPage();
          $pdf->output('SEGUIMIENTO_AC_'.Session::get("ejercicio").'_'.date("H:i:s").'.pdf', 'D');
      }
      
      public function exportar($id)
      {

          DB::beginTransaction();

          try {

            $data = tab_ac::join('mantenimiento.tab_ejecutores as t04', 't04.id_ejecutor', '=', 'ac_seguimiento.tab_ac.id_ejecutor')
            ->join('mantenimiento.tab_lapso as t02', 'ac_seguimiento.tab_ac.id_tab_lapso', '=', 't02.id')
            ->leftjoin('ac_seguimiento.tab_ac_ae as t21', 't21.id_tab_ac', '=', 'ac_seguimiento.tab_ac.id')
            ->leftjoin('t52_ac_predefinidas as t52', 't52.id', '=', 'ac_seguimiento.tab_ac.id_tab_ac_predefinida')        
            ->leftjoin('ac_seguimiento.tab_ac_vinculo as t49', 't49.id_tab_ac', '=', 'ac_seguimiento.tab_ac.id')
            ->leftjoin('t53_ac_ae_predefinidas as t53', 't53.id', '=', 't21.id_tab_ac_ae_predefinida')
            ->leftjoin('t45_planes_zulia as t45', function ($join) {
            $join->on('t49.co_area_estrategica', '=', 't45.co_area_estrategica')
            ->on('t45.nu_nivel', '=', DB::raw('0'));
            })
            ->leftjoin('t45_planes_zulia as t45a', function ($join) {
            $join->on('t49.co_area_estrategica', '=', 't45a.co_area_estrategica')
            ->on('t49.co_ambito_estado', '=', 't45a.co_ambito_zulia')        
            ->on('t45a.nu_nivel', '=', DB::raw('1'));
            })
            ->leftjoin('t45_planes_zulia as t45b', function ($join) {
            $join->on('t49.co_ambito_estado', '=', 't45b.co_ambito_zulia')
            ->on('t49.co_macroproblema', '=', 't45b.co_macroproblema')
            ->on('t45b.edo_reg', '=', DB::raw('true'))        
            ->on('t45b.nu_nivel', '=', DB::raw('3'));
            })
            ->leftjoin('t45_planes_zulia as t45c', function ($join) {
            $join->on('t49.co_ambito_estado', '=', 't45c.co_ambito_zulia')
            ->on(DB::raw('t49.co_nodos::integer'), '=', 't45c.co_nodo')       
            ->on('t45c.edo_reg', '=', DB::raw('true'))        
            ->on('t45c.nu_nivel', '=', DB::raw('4'));
            })            
            ->join('mantenimiento.tab_sectores as t18a', 'ac_seguimiento.tab_ac.id_tab_sectores', '=', 't18a.id')
            ->join('mantenimiento.tab_sectores as t18b', function ($join) {
            $join->on('t18a.co_sector', '=', 't18b.co_sector')
            ->on('t18b.nu_nivel', '=', DB::raw('1'));
            })
            ->leftjoin('t20_planes as t20', function ($join) {
            $join->on('t49.co_objetivo_historico', '=', 't20.co_objetivo_historico')
            ->on('t20.nu_nivel', '=', DB::raw('1'));
            }) 
            ->leftjoin('t20_planes as t20a', function ($join) {
            $join->on('t49.co_objetivo_nacional', '=', 't20a.co_objetivo_nacional')
            ->on('t49.co_objetivo_historico', '=', 't20a.co_objetivo_historico')        
            ->on('t20a.nu_nivel', '=', DB::raw('2'));
            })
            ->leftjoin('t20_planes as t20b', function ($join) {
            $join->on('t49.co_objetivo_estrategico', '=', 't20b.co_objetivo_estrategico')
            ->on('t49.co_objetivo_historico', '=', 't20b.co_objetivo_historico')        
            ->on('t49.co_objetivo_nacional', '=', 't20b.co_objetivo_nacional')        
            ->on('t20b.nu_nivel', '=', DB::raw('3'));
            })
            ->leftjoin('t20_planes as t20c', function ($join) {
            $join->on('t49.co_objetivo_general', '=', 't20c.co_objetivo_general')
            ->on('t49.co_objetivo_estrategico', '=', 't20c.co_objetivo_estrategico')
            ->on('t49.co_objetivo_historico', '=', 't20c.co_objetivo_historico')        
            ->on('t49.co_objetivo_nacional', '=', 't20c.co_objetivo_nacional')
            ->on('t20c.edo_reg', '=', DB::raw('true'))        
            ->on('t20c.nu_nivel', '=', DB::raw('4'));
            })            
            ->select(
            'ac_seguimiento.tab_ac.id_ejecutor',
            'tx_ejecutor_ac',
            't18b.tx_codigo as tx_sector',
            't45.tx_descripcion as tx_area_estrategica',
            't20.tx_descripcion as tx_objetivo_historico',
            't20a.tx_descripcion as tx_objetivo_nacional',
            't20b.tx_descripcion as tx_objetivo_estrategico',
            't20c.tx_descripcion as tx_objetivo_general',
            't45a.tx_descripcion as tx_ambito_estado', 
            't45b.tx_descripcion as tx_macroproblema',
            't45c.tx_descripcion as tx_nodos',
            't21.objetivo_institucional as tx_objetivo_institucional',
            DB::raw("'AC' || t04.id_ejecutor || ac_seguimiento.tab_ac.id_tab_ejercicio_fiscal || lpad(ac_seguimiento.tab_ac.id_tab_ac_predefinida::text, 5, '0') as id_proy_ac"),
            't52.nombre',
            DB::raw('t53.numero::text as tx_codigo_ae'),
            't53.nombre as tx_nombre_ae',
            't21.id_ejecutor as id_ejecutor_ae',
            'ac_seguimiento.tab_ac.pp_anual as tx_pr_objetivo',
            'ac_seguimiento.tab_ac.tx_pr_obtenido',
            'ac_seguimiento.tab_ac.tx_pr_obtenido_a',
            'ac_seguimiento.tab_ac.de_observacion_003',
            DB::raw("to_char(t02.fe_inicio, 'dd/mm/YYYY') as fe_inicio"),
            DB::raw("to_char(t02.fe_fin, 'dd/mm/YYYY') as fe_fin"),
            't21.id as id_tab_ac_ae',
            'ac_seguimiento.tab_ac.id_tab_ejercicio_fiscal',
            'id_tab_tipo_periodo',
            'ac_seguimiento.tab_ac.de_observacion_003',
            'ac_seguimiento.tab_ac.de_sector'
        )
        ->where('t21.id_tab_ac', '=', $id)
        ->get();

            foreach ($data as $item1) {
                if($item1->id_tab_tipo_periodo==19){
                    
                    $periodo = '1T '.Session::get("ejercicio");    
                }
                
                if($item1->id_tab_tipo_periodo==20){
                    
                    $periodo = '2T '.Session::get("ejercicio");    
                }

                if($item1->id_tab_tipo_periodo==21){
                    
                    $periodo = '3T '.Session::get("ejercicio");    
                }

                if($item1->id_tab_tipo_periodo==22){
                    
                    $periodo = '4T '.Session::get("ejercicio");    
                }
            }
              // Instantiate a new PHPExcel object
              $objPHPExcel = new PHPExcel();
              // Set properties
              $objPHPExcel->getProperties()->setCreator("Isilio Vilchez");
              $objPHPExcel->getProperties()->setLastModifiedBy("SEG");
              $objPHPExcel->getProperties()->setTitle("Listado");
              $objPHPExcel->getProperties()->setSubject("Reporte");
              $objPHPExcel->getProperties()->setDescription("Reporte para documento de Office 2007 XLSX.");
              // Set the active Excel worksheet to sheet 0
              $objPHPExcel->setActiveSheetIndex(0);
              // Rename sheet
//              $objPHPExcel->getActiveSheet()->getColumnDimension("A")->setAutoSize(true);
              $objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(30);
              $objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(20);
              $objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(20);
              $objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(20);
              $objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(20);
              $objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(20);
              $objPHPExcel->getActiveSheet()->getColumnDimension("G")->setWidth(20);
              $objPHPExcel->getActiveSheet()->getColumnDimension("H")->setWidth(10);
              $objPHPExcel->getActiveSheet()->getColumnDimension("I")->setWidth(10);
              $objPHPExcel->getActiveSheet()->getColumnDimension("J")->setWidth(10);
              $objPHPExcel->getActiveSheet()->getColumnDimension("K")->setWidth(10);
              $objPHPExcel->getActiveSheet()->getColumnDimension("L")->setWidth(20);
//              $objPHPExcel->getActiveSheet()->getColumnDimension("B")->setAutoSize(true);
////              $objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(30);
//              $objPHPExcel->getActiveSheet()->getColumnDimension("C")->setAutoSize(true);
//              $objPHPExcel->getActiveSheet()->getColumnDimension("D")->setAutoSize(true);
//              $objPHPExcel->getActiveSheet()->getColumnDimension("E")->setAutoSize(true);
//              $objPHPExcel->getActiveSheet()->getColumnDimension("F")->setAutoSize(true);
//              $objPHPExcel->getActiveSheet()->getColumnDimension("G")->setAutoSize(true);
////              $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(30);
//              $objPHPExcel->getActiveSheet()->getColumnDimension("H")->setAutoSize(true);
//              $objPHPExcel->getActiveSheet()->getColumnDimension("I")->setAutoSize(true);
//              $objPHPExcel->getActiveSheet()->getColumnDimension("J")->setAutoSize(true);
//              $objPHPExcel->getActiveSheet()->getColumnDimension("K")->setAutoSize(true);
//              $objPHPExcel->getActiveSheet()->getColumnDimension("L")->setAutoSize(true);
              $objPHPExcel->getActiveSheet()->setTitle('Mestas financieras '.$periodo);
              $objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray(
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
              $objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray(
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
              $objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray(
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
//              $objPHPExcel->getActiveSheet()->getStyle('F1')->applyFromArray(
//                  array(
//                          'alignment' => array(
//                              'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
//                          ),
//                          'borders' => array(
//                              'left'     => array(
//                                  'style' => PHPExcel_Style_Border::BORDER_THIN
//                              )
//                          )
//                      )
//              );
//
//              $objPHPExcel->getActiveSheet()->getStyle('G1')->applyFromArray(
//                  array(
//                          'alignment' => array(
//                              'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
//                          )
//                      )
//              );
//
//              $objPHPExcel->getActiveSheet()->getStyle('H1')->applyFromArray(
//                  array(
//                          'borders' => array(
//                              'right'     => array(
//                                  'style' => PHPExcel_Style_Border::BORDER_THIN
//                              )
//                          )
//                      )
//              );
              // Initialise the Excel row number
              $rowCount = 3;
              // Iterate through each result from the SQL query in turn
              // We fetch each database result row into $row in turn

//              $objPHPExcel->setActiveSheetIndex(0)
//              ->setCellValue('A1', 'PERIODO')
//              ->setCellValue('B1', 'EJECUTOR')
//              ->setCellValue('C1', 'CODIGO')
//              ->setCellValue('D1', 'DESCRIPCION');

              // Make bold cells
//              $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setBold(true);

              foreach ($data as $key => $value) {
                                  
                  
//                  $final = $rowCount+2;
//                  $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowCount.':A'.$final);
//                  $objPHPExcel->getActiveSheet()->mergeCells('B'.$rowCount.':B'.$final);
                  $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setWrapText(true);
                  $objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setWrapText(true);
                  $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount)->getAlignment()->setWrapText(true);
//                  $objPHPExcel->getActiveSheet()->getStyle('B'.$rowCount.':B'.$final)->getAlignment()->setWrapText(true);
                  // Set thin black border outline around column
                  $styleThinBlackBorderOutline = array(
                      'borders' => array(
                          'outline' => array(
                              'style' => PHPExcel_Style_Border::BORDER_THIN,
                              'color' => array('argb' => 'FF000000'),
                          ),
                      ),
                  );
                  
//                  $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->applyFromArray($styleThinBlackBorderOutline);
                  $objPHPExcel->getActiveSheet()->SetCellValue('A1', $value->id_ejecutor.' - '.$value->tx_ejecutor_ac, PHPExcel_Cell_DataType::TYPE_STRING);
                  $objPHPExcel->getActiveSheet()->setCellValueExplicit('A2', $value->id_proy_ac.' - '.$value->nombre, PHPExcel_Cell_DataType::TYPE_STRING);
                  $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $value->tx_codigo_ae.' - '.$value->tx_nombre_ae, PHPExcel_Cell_DataType::TYPE_STRING); 

              $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount.':L'.$rowCount)->applyFromArray(
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
              $rowCount++; 
              $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount.':L'.$rowCount)->applyFromArray(
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
                          )
                      )
              );              
              
              $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount.':L'.$rowCount)->getAlignment()->setWrapText(true);
              $objPHPExcel->setActiveSheetIndex(0)
              ->setCellValue('A'.$rowCount, 'ACTIVIDADES')
              ->setCellValue('B'.$rowCount, 'PRESUPUESTO PROGRAM. ANUAL (Bs.)')
              ->setCellValue('C'.$rowCount, 'PRESUPUESTO MODIFICADO ANUAL (Bs.)')
              ->setCellValue('D'.$rowCount, 'PRESUPUESTO ACTUALIZADO ANUAL (Bs.)')
              ->setCellValue('E'.$rowCount, 'PRESUPUESTO COMPROM. AL CORTE (Bs.)')
              ->setCellValue('F'.$rowCount, 'PRESUPUESTO CAUSADO AL CORTE (Bs.)')
              ->setCellValue('G'.$rowCount, 'PRESUPUESTO PAGADO AL CORTE (Bs.)')
              ->setCellValue('H'.$rowCount, 'SECTOR')
              ->setCellValue('I'.$rowCount, 'PROY. Y/O A. CENTRAL.')
              ->setCellValue('J'.$rowCount, 'ACCIÓN ESPECIFICA')
              ->setCellValue('K'.$rowCount, 'PARTIDA')
              ->setCellValue('L'.$rowCount, 'FUENTE FINANCIAMIENTO');  
              $rowCount++;
                  
             $actividad = tab_meta_fisica::select('tab_meta_fisica.id','codigo','nb_meta',DB::raw('coalesce(mo_presupuesto,0) as mo_presupuesto'),DB::raw('coalesce(mo_modificado_anual,0) as mo_modificado_anual'),DB::raw('coalesce(mo_modificado,0) as mo_modificado'),DB::raw('coalesce(mo_actualizado_anual,0) as mo_actualizado_anual'),
            DB::raw('coalesce(mo_comprometido,0) as mo_comprometido'),DB::raw('coalesce(mo_causado,0) as mo_causado'),DB::raw('coalesce(mo_pagado,0) as mo_pagado'),'de_fuente_financiamiento','co_partida',
            'nu_numero',
            'nu_original',
            'co_sector')
            ->join('ac_seguimiento.tab_meta_financiera as t22', 'tab_meta_fisica.id', '=', 't22.id_tab_meta_fisica')
            ->join('mantenimiento.tab_fuente_financiamiento as t66', 't22.id_tab_fuente_financiamiento', '=', 't66.id')
             ->join('ac_seguimiento.tab_ac_ae as t03', 'tab_meta_fisica.id_tab_ac_ae', '=', 't03.id')
             ->join('mantenimiento.tab_ac_ae_predefinida as t04', 't03.id_tab_ac_ae_predefinida', '=', 't04.id')
             ->join('ac_seguimiento.tab_ac as t05', 't03.id_tab_ac', '=', 't05.id')
             ->join('mantenimiento.tab_ac_predefinida as t06', 't05.id_tab_ac_predefinida', '=', 't06.id')
             ->join('mantenimiento.tab_sectores as t07', 't05.id_tab_sectores', '=', 't07.id')  
             ->join('mantenimiento.tab_lapso as t08', 't05.id_tab_lapso', '=', 't08.id')
            ->where('id_tab_ac_ae', '=', $value->id_tab_ac_ae)
            ->where('id_tab_tipo_periodo', '=', $value->id_tab_tipo_periodo)
            ->orderBy('codigo', 'ASC')
            ->orderBy('co_partida', 'ASC')
            ->get();  
             
            $mo_presupuesto = 0;
            $mo_modificado_anual = 0;
            $mo_actualizado_anual = 0;
            $mo_comprometido = 0;
            $mo_causado = 0;
            $mo_pagado = 0;             
                  
            foreach($actividad as $item) {  
            $tab_meta_financiera = tab_meta_financiera::where('id_tab_meta_fisica', '=', $item->id)
            ->get();         
             if($tab_meta_financiera->count()>1){
                $i =  $tab_meta_financiera->count();
                 $final = $rowCount + ($i-1);
                  $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowCount.':A'.$final);  
             }else{
             $i = 1; 
             $final = $rowCount;
             }
                 
                 
                  $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount.':L'.$rowCount)->applyFromArray($styleThinBlackBorderOutline);
                  $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount.':A'.$final)->applyFromArray(
                  array(
                          'alignment' => array(
                              'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                          )
                      )
              ); 
                  $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount.':L'.$rowCount)->getAlignment()->setWrapText(true);
                  $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $item->codigo.' - '.$item->nb_meta, PHPExcel_Cell_DataType::TYPE_STRING);
                  $objPHPExcel->getActiveSheet()->setCellValueExplicit('B'.$rowCount, $item->mo_presupuesto, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $item->mo_modificado_anual, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $objPHPExcel->getActiveSheet()->setCellValueExplicit('D'.$rowCount, $item->mo_presupuesto + $item->mo_modificado_anual, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $item->mo_comprometido, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $objPHPExcel->getActiveSheet()->setCellValueExplicit('F'.$rowCount, $item->mo_causado, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $item->mo_pagado, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $objPHPExcel->getActiveSheet()->setCellValueExplicit('H'.$rowCount, $item->co_sector, PHPExcel_Cell_DataType::TYPE_STRING);
                  $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, $item->nu_original, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $objPHPExcel->getActiveSheet()->setCellValueExplicit('J'.$rowCount, $item->nu_numero, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $objPHPExcel->getActiveSheet()->SetCellValue('K'.$rowCount, $item->co_partida, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $objPHPExcel->getActiveSheet()->SetCellValue('L'.$rowCount, $item->de_fuente_financiamiento, PHPExcel_Cell_DataType::TYPE_STRING);
                  $rowCount++;
                
                $mo_presupuesto = $mo_presupuesto + $item->mo_presupuesto;
                $mo_modificado_anual = $mo_modificado_anual + $item->mo_modificado_anual;
                $mo_actualizado_anual = $mo_actualizado_anual + ($item->mo_presupuesto + $item->mo_modificado_anual);
                $mo_comprometido = $mo_comprometido + $item->mo_comprometido;
                $mo_causado = $mo_causado + $item->mo_causado;
                $mo_pagado = $mo_pagado + $item->mo_pagado;
                  
            }
            
            
              $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount.':L'.$rowCount)->applyFromArray(
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
                          )
                      )
              );
                  $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, ' TOTAL ACCION ESPECIFICA ', PHPExcel_Cell_DataType::TYPE_STRING);
                  $objPHPExcel->getActiveSheet()->setCellValueExplicit('B'.$rowCount, $mo_presupuesto, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $mo_modificado_anual, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $objPHPExcel->getActiveSheet()->setCellValueExplicit('D'.$rowCount, $mo_actualizado_anual, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $mo_comprometido, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $objPHPExcel->getActiveSheet()->setCellValueExplicit('F'.$rowCount, $mo_causado, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $mo_pagado, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $rowCount++; 
                  $rowCount++;
            
              }

              // Instantiate a Writer to create an OfficeOpenXML Excel .xlsx file
              $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
              // We'll be outputting an excel file
              header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
              // It will be called file.xls
              header('Content-Disposition: attachment; filename="METAS_FINANCIERAS_'.Session::get('ejercicio').'_'.date("Y-m-d").'.xlsx"');
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
      
      public function exportarAcumulada($id)
      {

          DB::beginTransaction();

          try {

            $data = tab_ac::join('mantenimiento.tab_ejecutores as t04', 't04.id_ejecutor', '=', 'ac_seguimiento.tab_ac.id_ejecutor')
            ->join('mantenimiento.tab_lapso as t02', 'ac_seguimiento.tab_ac.id_tab_lapso', '=', 't02.id')
            ->leftjoin('ac_seguimiento.tab_ac_ae as t21', 't21.id_tab_ac', '=', 'ac_seguimiento.tab_ac.id')
            ->leftjoin('t52_ac_predefinidas as t52', 't52.id', '=', 'ac_seguimiento.tab_ac.id_tab_ac_predefinida')        
            ->leftjoin('ac_seguimiento.tab_ac_vinculo as t49', 't49.id_tab_ac', '=', 'ac_seguimiento.tab_ac.id')
            ->leftjoin('t53_ac_ae_predefinidas as t53', 't53.id', '=', 't21.id_tab_ac_ae_predefinida')
            ->leftjoin('t45_planes_zulia as t45', function ($join) {
            $join->on('t49.co_area_estrategica', '=', 't45.co_area_estrategica')
            ->on('t45.nu_nivel', '=', DB::raw('0'));
            })
            ->leftjoin('t45_planes_zulia as t45a', function ($join) {
            $join->on('t49.co_area_estrategica', '=', 't45a.co_area_estrategica')
            ->on('t49.co_ambito_estado', '=', 't45a.co_ambito_zulia')        
            ->on('t45a.nu_nivel', '=', DB::raw('1'));
            })
            ->leftjoin('t45_planes_zulia as t45b', function ($join) {
            $join->on('t49.co_ambito_estado', '=', 't45b.co_ambito_zulia')
            ->on('t49.co_macroproblema', '=', 't45b.co_macroproblema')
            ->on('t45b.edo_reg', '=', DB::raw('true'))        
            ->on('t45b.nu_nivel', '=', DB::raw('3'));
            })
            ->leftjoin('t45_planes_zulia as t45c', function ($join) {
            $join->on('t49.co_ambito_estado', '=', 't45c.co_ambito_zulia')
            ->on(DB::raw('t49.co_nodos::integer'), '=', 't45c.co_nodo')       
            ->on('t45c.edo_reg', '=', DB::raw('true'))        
            ->on('t45c.nu_nivel', '=', DB::raw('4'));
            })            
            ->join('mantenimiento.tab_sectores as t18a', 'ac_seguimiento.tab_ac.id_tab_sectores', '=', 't18a.id')
            ->join('mantenimiento.tab_sectores as t18b', function ($join) {
            $join->on('t18a.co_sector', '=', 't18b.co_sector')
            ->on('t18b.nu_nivel', '=', DB::raw('1'));
            })
            ->leftjoin('t20_planes as t20', function ($join) {
            $join->on('t49.co_objetivo_historico', '=', 't20.co_objetivo_historico')
            ->on('t20.nu_nivel', '=', DB::raw('1'));
            }) 
            ->leftjoin('t20_planes as t20a', function ($join) {
            $join->on('t49.co_objetivo_nacional', '=', 't20a.co_objetivo_nacional')
            ->on('t49.co_objetivo_historico', '=', 't20a.co_objetivo_historico')        
            ->on('t20a.nu_nivel', '=', DB::raw('2'));
            })
            ->leftjoin('t20_planes as t20b', function ($join) {
            $join->on('t49.co_objetivo_estrategico', '=', 't20b.co_objetivo_estrategico')
            ->on('t49.co_objetivo_historico', '=', 't20b.co_objetivo_historico')        
            ->on('t49.co_objetivo_nacional', '=', 't20b.co_objetivo_nacional')        
            ->on('t20b.nu_nivel', '=', DB::raw('3'));
            })
            ->leftjoin('t20_planes as t20c', function ($join) {
            $join->on('t49.co_objetivo_general', '=', 't20c.co_objetivo_general')
            ->on('t49.co_objetivo_estrategico', '=', 't20c.co_objetivo_estrategico')
            ->on('t49.co_objetivo_historico', '=', 't20c.co_objetivo_historico')        
            ->on('t49.co_objetivo_nacional', '=', 't20c.co_objetivo_nacional')
            ->on('t20c.edo_reg', '=', DB::raw('true'))        
            ->on('t20c.nu_nivel', '=', DB::raw('4'));
            })            
            ->select(
            'ac_seguimiento.tab_ac.id_ejecutor',
            'tx_ejecutor_ac',
            't18b.tx_codigo as tx_sector',
            't45.tx_descripcion as tx_area_estrategica',
            't20.tx_descripcion as tx_objetivo_historico',
            't20a.tx_descripcion as tx_objetivo_nacional',
            't20b.tx_descripcion as tx_objetivo_estrategico',
            't20c.tx_descripcion as tx_objetivo_general',
            't45a.tx_descripcion as tx_ambito_estado', 
            't45b.tx_descripcion as tx_macroproblema',
            't45c.tx_descripcion as tx_nodos',
            't21.objetivo_institucional as tx_objetivo_institucional',
            DB::raw("'AC' || t04.id_ejecutor || ac_seguimiento.tab_ac.id_tab_ejercicio_fiscal || lpad(ac_seguimiento.tab_ac.id_tab_ac_predefinida::text, 5, '0') as id_proy_ac"),
            't52.nombre',
            DB::raw('t53.numero::text as tx_codigo_ae'),
            't53.nombre as tx_nombre_ae',
            't21.id_ejecutor as id_ejecutor_ae',
            'ac_seguimiento.tab_ac.pp_anual as tx_pr_objetivo',
            'ac_seguimiento.tab_ac.tx_pr_obtenido',
            'ac_seguimiento.tab_ac.tx_pr_obtenido_a',
            'ac_seguimiento.tab_ac.de_observacion_003',
            DB::raw("to_char(t02.fe_inicio, 'dd/mm/YYYY') as fe_inicio"),
            DB::raw("to_char(t02.fe_fin, 'dd/mm/YYYY') as fe_fin"),
            't21.id as id_tab_ac_ae',
            'ac_seguimiento.tab_ac.id_tab_ejercicio_fiscal',
            'id_tab_tipo_periodo',
            'ac_seguimiento.tab_ac.de_observacion_003',
            'ac_seguimiento.tab_ac.de_sector',
            't21.id_tab_ac_ae_predefinida'
        )
        ->where('t21.id_tab_ac', '=', $id)
        ->get();

            foreach ($data as $item1) {
                if($item1->id_tab_tipo_periodo==19){
                    
                    $periodo = '1TA '.Session::get("ejercicio");    
                }
                
                if($item1->id_tab_tipo_periodo==20){
                    
                    $periodo = '2TA '.Session::get("ejercicio");    
                }

                if($item1->id_tab_tipo_periodo==21){
                    
                    $periodo = '3TA '.Session::get("ejercicio");    
                }

                if($item1->id_tab_tipo_periodo==22){
                    
                    $periodo = '4TA '.Session::get("ejercicio");    
                }
            }
              // Instantiate a new PHPExcel object
              $objPHPExcel = new PHPExcel();
              // Set properties
              $objPHPExcel->getProperties()->setCreator("Isilio Vilchez");
              $objPHPExcel->getProperties()->setLastModifiedBy("SEG");
              $objPHPExcel->getProperties()->setTitle("Listado");
              $objPHPExcel->getProperties()->setSubject("Reporte");
              $objPHPExcel->getProperties()->setDescription("Reporte para documento de Office 2007 XLSX.");
              // Set the active Excel worksheet to sheet 0
              $objPHPExcel->setActiveSheetIndex(0);
              // Rename sheet
//              $objPHPExcel->getActiveSheet()->getColumnDimension("A")->setAutoSize(true);
              $objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(30);
              $objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(20);
              $objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(20);
              $objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(20);
              $objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(20);
              $objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(20);
              $objPHPExcel->getActiveSheet()->getColumnDimension("G")->setWidth(20);
              $objPHPExcel->getActiveSheet()->getColumnDimension("H")->setWidth(10);
              $objPHPExcel->getActiveSheet()->getColumnDimension("I")->setWidth(10);
              $objPHPExcel->getActiveSheet()->getColumnDimension("J")->setWidth(10);
              $objPHPExcel->getActiveSheet()->getColumnDimension("K")->setWidth(10);
              $objPHPExcel->getActiveSheet()->getColumnDimension("L")->setWidth(20);
//              $objPHPExcel->getActiveSheet()->getColumnDimension("B")->setAutoSize(true);
////              $objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(30);
//              $objPHPExcel->getActiveSheet()->getColumnDimension("C")->setAutoSize(true);
//              $objPHPExcel->getActiveSheet()->getColumnDimension("D")->setAutoSize(true);
//              $objPHPExcel->getActiveSheet()->getColumnDimension("E")->setAutoSize(true);
//              $objPHPExcel->getActiveSheet()->getColumnDimension("F")->setAutoSize(true);
//              $objPHPExcel->getActiveSheet()->getColumnDimension("G")->setAutoSize(true);
////              $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(30);
//              $objPHPExcel->getActiveSheet()->getColumnDimension("H")->setAutoSize(true);
//              $objPHPExcel->getActiveSheet()->getColumnDimension("I")->setAutoSize(true);
//              $objPHPExcel->getActiveSheet()->getColumnDimension("J")->setAutoSize(true);
//              $objPHPExcel->getActiveSheet()->getColumnDimension("K")->setAutoSize(true);
//              $objPHPExcel->getActiveSheet()->getColumnDimension("L")->setAutoSize(true);
              $objPHPExcel->getActiveSheet()->setTitle('Mestas financieras '.$periodo);
              $objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray(
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
              $objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray(
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
              $objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray(
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
//              $objPHPExcel->getActiveSheet()->getStyle('F1')->applyFromArray(
//                  array(
//                          'alignment' => array(
//                              'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
//                          ),
//                          'borders' => array(
//                              'left'     => array(
//                                  'style' => PHPExcel_Style_Border::BORDER_THIN
//                              )
//                          )
//                      )
//              );
//
//              $objPHPExcel->getActiveSheet()->getStyle('G1')->applyFromArray(
//                  array(
//                          'alignment' => array(
//                              'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
//                          )
//                      )
//              );
//
//              $objPHPExcel->getActiveSheet()->getStyle('H1')->applyFromArray(
//                  array(
//                          'borders' => array(
//                              'right'     => array(
//                                  'style' => PHPExcel_Style_Border::BORDER_THIN
//                              )
//                          )
//                      )
//              );
              // Initialise the Excel row number
              $rowCount = 3;
              // Iterate through each result from the SQL query in turn
              // We fetch each database result row into $row in turn

//              $objPHPExcel->setActiveSheetIndex(0)
//              ->setCellValue('A1', 'PERIODO')
//              ->setCellValue('B1', 'EJECUTOR')
//              ->setCellValue('C1', 'CODIGO')
//              ->setCellValue('D1', 'DESCRIPCION');

              // Make bold cells
//              $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setBold(true);

              foreach ($data as $key => $value) {
                                  
                  
//                  $final = $rowCount+2;
//                  $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowCount.':A'.$final);
//                  $objPHPExcel->getActiveSheet()->mergeCells('B'.$rowCount.':B'.$final);
                  $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setWrapText(true);
                  $objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setWrapText(true);
                  $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount)->getAlignment()->setWrapText(true);
//                  $objPHPExcel->getActiveSheet()->getStyle('B'.$rowCount.':B'.$final)->getAlignment()->setWrapText(true);
                  // Set thin black border outline around column
                  $styleThinBlackBorderOutline = array(
                      'borders' => array(
                          'outline' => array(
                              'style' => PHPExcel_Style_Border::BORDER_THIN,
                              'color' => array('argb' => 'FF000000'),
                          ),
                      ),
                  );
                  
//                  $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->applyFromArray($styleThinBlackBorderOutline);
                  $objPHPExcel->getActiveSheet()->SetCellValue('A1', $value->id_ejecutor.' - '.$value->tx_ejecutor_ac, PHPExcel_Cell_DataType::TYPE_STRING);
                  $objPHPExcel->getActiveSheet()->setCellValueExplicit('A2', $value->id_proy_ac.' - '.$value->nombre, PHPExcel_Cell_DataType::TYPE_STRING);
                  $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $value->tx_codigo_ae.' - '.$value->tx_nombre_ae, PHPExcel_Cell_DataType::TYPE_STRING); 

              $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount.':L'.$rowCount)->applyFromArray(
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
              $rowCount++; 
              $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount.':L'.$rowCount)->applyFromArray(
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
                          )
                      )
              );              
              
              $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount.':L'.$rowCount)->getAlignment()->setWrapText(true);
              $objPHPExcel->setActiveSheetIndex(0)
              ->setCellValue('A'.$rowCount, 'ACTIVIDADES')
              ->setCellValue('B'.$rowCount, 'PRESUPUESTO PROGRAM. ANUAL (Bs.)')
              ->setCellValue('C'.$rowCount, 'PRESUPUESTO MODIFICADO ANUAL (Bs.)')
              ->setCellValue('D'.$rowCount, 'PRESUPUESTO ACTUALIZADO ANUAL (Bs.)')
              ->setCellValue('E'.$rowCount, 'PRESUPUESTO COMPROM. AL CORTE (Bs.)')
              ->setCellValue('F'.$rowCount, 'PRESUPUESTO CAUSADO AL CORTE (Bs.)')
              ->setCellValue('G'.$rowCount, 'PRESUPUESTO PAGADO AL CORTE (Bs.)')
              ->setCellValue('H'.$rowCount, 'SECTOR')
              ->setCellValue('I'.$rowCount, 'PROY. Y/O A. CENTRAL.')
              ->setCellValue('J'.$rowCount, 'ACCIÓN ESPECIFICA')
              ->setCellValue('K'.$rowCount, 'PARTIDA')
              ->setCellValue('L'.$rowCount, 'FUENTE FINANCIAMIENTO');  
              $rowCount++;
                  
            $actividad = tab_meta_fisica::select('codigo','nb_meta',DB::raw('sum(coalesce(mo_presupuesto,0)) as mo_presupuesto'),
            DB::raw('sum(coalesce(mo_modificado_anual,0)) as mo_modificado_anual'),DB::raw('sum(coalesce(mo_actualizado_anual,0)) as mo_actualizado_anual'),DB::raw('sum(coalesce(mo_modificado,0)) as mo_modificado'),
            'de_fuente_financiamiento','co_partida','id_tab_fuente_financiamiento',
            'nu_numero',
            'nu_original',
            'co_sector')
            ->join('ac_seguimiento.tab_meta_financiera as t22', 'tab_meta_fisica.id', '=', 't22.id_tab_meta_fisica')
            ->join('mantenimiento.tab_fuente_financiamiento as t66', 't22.id_tab_fuente_financiamiento', '=', 't66.id')
             ->join('ac_seguimiento.tab_ac_ae as t03', 'tab_meta_fisica.id_tab_ac_ae', '=', 't03.id')
             ->join('mantenimiento.tab_ac_ae_predefinida as t04', 't03.id_tab_ac_ae_predefinida', '=', 't04.id')
             ->join('ac_seguimiento.tab_ac as t05', 't03.id_tab_ac', '=', 't05.id')
             ->join('mantenimiento.tab_ac_predefinida as t06', 't05.id_tab_ac_predefinida', '=', 't06.id')
             ->join('mantenimiento.tab_sectores as t07', 't05.id_tab_sectores', '=', 't07.id')                  
            ->where('id_tab_ac_ae', '=', $value->id_tab_ac_ae)
            ->orderBy('codigo', 'ASC')
            ->orderBy('co_partida', 'ASC')
             ->groupBy('codigo')
             ->groupBy('nb_meta')
             ->groupBy('co_partida')
             ->groupBy('id_tab_fuente_financiamiento')
             ->groupBy('de_fuente_financiamiento')
             ->groupBy('nu_numero') 
            ->groupBy('nu_original') 
            ->groupBy('co_sector') 
            ->get();  
             
            $mo_presupuesto = 0;
            $mo_modificado_anual = 0;
            $mo_actualizado_anual = 0;
            $mo_comprometido = 0;
            $mo_causado = 0;
            $mo_pagado = 0;             
                  
            foreach($actividad as $item) { 
                
                
                $data2 = tab_ac::select(
                 DB::raw("coalesce(sum(mo_comprometido),0) as mo_comprometido"),
                DB::raw("coalesce(sum(mo_causado),0) as mo_causado"),
                DB::raw("coalesce(sum(mo_pagado),0) as mo_pagado")
                )
                ->join('ac_seguimiento.tab_ac_ae as t01', 'ac_seguimiento.tab_ac.id', '=', 't01.id_tab_ac')
                ->join('ac_seguimiento.tab_meta_fisica as t02', 't01.id', '=', 't02.id_tab_ac_ae')
                ->join('ac_seguimiento.tab_meta_financiera as t03', 't03.id_tab_meta_fisica', '=', 't02.id')
                ->join('mantenimiento.tab_lapso as t04', 'ac_seguimiento.tab_ac.id_tab_lapso', '=', 't04.id')
                ->where('ac_seguimiento.tab_ac.nu_codigo', '=', $value->id_proy_ac)
                ->where('t02.codigo', '=', $item->codigo)
                ->where('ac_seguimiento.tab_ac.in_activo', '=', true)        
                ->where('t03.co_partida', '=', $item->co_partida)
                ->where('t01.id_tab_ac_ae_predefinida', '=', $value->id_tab_ac_ae_predefinida)
                ->where('t03.id_tab_fuente_financiamiento', '=', $item->id_tab_fuente_financiamiento)
                ->where('id_tab_tipo_periodo', '<=', $value->id_tab_tipo_periodo)
                ->where('ac_seguimiento.tab_ac.id_tab_ejercicio_fiscal', '=', $value->id_tab_ejercicio_fiscal)
                ->first();                              
                
            $tab_meta_financiera = tab_meta_financiera::where('id_tab_meta_fisica', '=', $item->id)
            ->get();         
             if($tab_meta_financiera->count()>1){
                $i =  $tab_meta_financiera->count();
                 $final = $rowCount + ($i-1);
                  $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowCount.':A'.$final);  
             }else{
             $i = 1; 
             $final = $rowCount;
             }
                 
                 
                  $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount.':L'.$rowCount)->applyFromArray($styleThinBlackBorderOutline);
                  $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount.':A'.$final)->applyFromArray(
                  array(
                          'alignment' => array(
                              'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                          )
                      )
              ); 
                  $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount.':L'.$rowCount)->getAlignment()->setWrapText(true);
                  $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $item->codigo.' - '.$item->nb_meta, PHPExcel_Cell_DataType::TYPE_STRING);
                  $objPHPExcel->getActiveSheet()->setCellValueExplicit('B'.$rowCount, $item->mo_presupuesto, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, ($item->mo_modificado_anual + $item->mo_modificado), PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $objPHPExcel->getActiveSheet()->setCellValueExplicit('D'.$rowCount, ($item->mo_presupuesto + $item->mo_modificado_anual + $item->mo_modificado), PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $data2->mo_comprometido, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $objPHPExcel->getActiveSheet()->setCellValueExplicit('F'.$rowCount, $data2->mo_causado, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $data2->mo_pagado, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $objPHPExcel->getActiveSheet()->setCellValueExplicit('H'.$rowCount, $item->co_sector, PHPExcel_Cell_DataType::TYPE_STRING);
                  $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, $item->nu_original, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $objPHPExcel->getActiveSheet()->setCellValueExplicit('J'.$rowCount, $item->nu_numero, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $objPHPExcel->getActiveSheet()->SetCellValue('K'.$rowCount, $item->co_partida, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $objPHPExcel->getActiveSheet()->SetCellValue('L'.$rowCount, $item->de_fuente_financiamiento, PHPExcel_Cell_DataType::TYPE_STRING);
                  $rowCount++;
                
                $mo_presupuesto = $mo_presupuesto + $item->mo_presupuesto;
                $mo_modificado_anual = $mo_modificado_anual + ($item->mo_modificado_anual + $item->mo_modificado);
                $mo_actualizado_anual = $mo_actualizado_anual + ($item->mo_presupuesto + $item->mo_modificado_anual + $item->mo_modificado);
                $mo_comprometido = $mo_comprometido + $data2->mo_comprometido;
                $mo_causado = $mo_causado + $data2->mo_causado;
                $mo_pagado = $mo_pagado + $data2->mo_pagado;
                  
            }
            
            
              $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount.':L'.$rowCount)->applyFromArray(
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
                          )
                      )
              );
                  $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, ' TOTAL ACCION ESPECIFICA ', PHPExcel_Cell_DataType::TYPE_STRING);
                  $objPHPExcel->getActiveSheet()->setCellValueExplicit('B'.$rowCount, $mo_presupuesto, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $mo_modificado_anual, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $objPHPExcel->getActiveSheet()->setCellValueExplicit('D'.$rowCount, $mo_actualizado_anual, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $mo_comprometido, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $objPHPExcel->getActiveSheet()->setCellValueExplicit('F'.$rowCount, $mo_causado, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $mo_pagado, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $rowCount++; 
                  $rowCount++;
            
              }

              // Instantiate a Writer to create an OfficeOpenXML Excel .xlsx file
              $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
              // We'll be outputting an excel file
              header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
              // It will be called file.xls
              header('Content-Disposition: attachment; filename="METAS_FINANCIERAS_'.Session::get('ejercicio').'_'.date("Y-m-d").'.xlsx"');
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

      public function pendientes($id)
      {

          DB::beginTransaction();

          try {

            $tab_forma_003 = tab_meta_financiera::join('ac_seguimiento.tab_meta_fisica as t06', 'ac_seguimiento.tab_meta_financiera.id_tab_meta_fisica', '=', 't06.id')        
            ->join('ac_seguimiento.tab_ac_ae as t05', 't06.id_tab_ac_ae', '=', 't05.id')        
            ->join('ac_seguimiento.tab_ac as t01', 't05.id_tab_ac', '=', 't01.id')
            ->join('mantenimiento.tab_ejecutores as t02', 't01.id_tab_ejecutores', '=', 't02.id')
            ->join('mantenimiento.tab_lapso as t03', 't01.id_tab_lapso', '=', 't03.id')
            ->join('mantenimiento.tab_ac_ae_predefinida as t07', 't05.id_tab_ac_ae_predefinida', '=', 't07.id')
            ->select(
                'tx_ejecutor_ac',
                't01.id as id_ac',    
                't02.in_activo',
                'id_tab_ac_ae',
                'nu_codigo',
                'de_ac',
                'de_lapso',
                't01.id_ejecutor',
                'de_nombre',
                't05.in_003' 
            )
            ->where('t01.id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))
            ->where('t01.in_activo', '=', true)
            ->where('in_enviado', '=', false)
            ->where('t01.id_tab_lapso', '=', $id)
                    ->groupBy('t01.id')
                    ->groupBy('tx_ejecutor_ac')
                    ->groupBy('t02.in_activo')
                    ->groupBy('t05.in_003')
                    ->groupBy('id_tab_ac_ae')
                    ->groupBy('nu_codigo')
                    ->groupBy('de_ac')
                    ->groupBy('de_lapso')
                    ->groupBy('t01.id_ejecutor')
                    ->groupBy('de_nombre')         
                    ->orderby('t01.id_ejecutor', 'ASC')
                    ->orderby('nu_codigo', 'ASC')
                    ->get();

              // Instantiate a new PHPExcel object
              $objPHPExcel = new PHPExcel();
              // Set properties
              $objPHPExcel->getProperties()->setCreator("Isilio Vilchez");
              $objPHPExcel->getProperties()->setLastModifiedBy("SEG");
              $objPHPExcel->getProperties()->setTitle("Listado");
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
              $objPHPExcel->getActiveSheet()->setTitle('Mestas financieras x Validar');
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
              ->setCellValue('A1', 'PERIODO')
              ->setCellValue('B1', 'EJECUTOR')
              ->setCellValue('C1', 'CODIGO')
              ->setCellValue('D1', 'DESCRIPCION');

              // Make bold cells
              $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setBold(true);

              foreach ($tab_forma_003 as $key => $value) {
//                  $final = $rowCount+2;
//                  $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowCount.':A'.$final);
//                  $objPHPExcel->getActiveSheet()->mergeCells('B'.$rowCount.':B'.$final);
//                  $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount.':A'.$final)->getAlignment()->setWrapText(true);
//                  $objPHPExcel->getActiveSheet()->getStyle('B'.$rowCount.':B'.$final)->getAlignment()->setWrapText(true);
                  // Set thin black border outline around column
                  $styleThinBlackBorderOutline = array(
                      'borders' => array(
                          'outline' => array(
                              'style' => PHPExcel_Style_Border::BORDER_THIN,
                              'color' => array('argb' => 'FF000000'),
                          ),
                      ),
                  );
                  $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount.':D'.$rowCount)->applyFromArray($styleThinBlackBorderOutline);
                  $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $value->de_lapso);
                  $objPHPExcel->getActiveSheet()->setCellValueExplicit('B'.$rowCount, $value->id_ejecutor.' - '.$value->tx_ejecutor_ac, PHPExcel_Cell_DataType::TYPE_STRING);
                  $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $value->nu_codigo, PHPExcel_Cell_DataType::TYPE_STRING);
                  $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $value->de_nombre, PHPExcel_Cell_DataType::TYPE_STRING);
                  $rowCount++;
              }

              // Instantiate a Writer to create an OfficeOpenXML Excel .xlsx file
              $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
              // We'll be outputting an excel file
              header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
              // It will be called file.xls
              header('Content-Disposition: attachment; filename="METAS_FINANCIERAS_PENDIENTES_X_VALIDAR_'.Session::get('ejercicio').'_'.date("Y-m-d").'.xlsx"');
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
