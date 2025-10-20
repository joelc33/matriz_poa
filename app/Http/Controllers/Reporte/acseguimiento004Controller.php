<?php

namespace matriz\Http\Controllers\Reporte;

//*******agregar esta linea******//
use matriz\Models\AcSegto\tab_meta_fisica;
use matriz\Models\AcSegto\tab_meta_financiera;
use matriz\Models\AcSegto\tab_forma_001;
use matriz\Models\AcSegto\tab_ac;
use matriz\Models\Mantenimiento\tab_lapso;
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
        $pdf->MultiCell(277, 5, 'FORMULARIO Nº 4', 0, 'C', 0, 0, '', '', true);
        $pdf->Ln(5);
        $pdf->MultiCell(277, 5, 'DESVIO DE LA GESTIÓN', 0, 'C', 0, 0, '', '', true);
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

class acseguimiento004Controller extends Controller
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
      
      public function ficha004($id)
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

            $actividad = tab_meta_fisica::select('tab_meta_fisica.id','codigo','nb_meta',DB::raw('coalesce(mo_presupuesto,0) as mo_presupuesto'),DB::raw('coalesce(mo_modificado_anual,0) as mo_modificado_anual'),DB::raw('coalesce(mo_presupuesto,0) + coalesce(mo_modificado_anual,0) + coalesce(mo_modificado,0) as mo_actualizado_anual'),DB::raw('coalesce(mo_modificado,0) as mo_modificado'),
            'mo_comprometido','mo_causado','mo_pagado','de_fuente_financiamiento','co_partida','de_desvio','tx_prog_anual',
            DB::raw('coalesce(tx_prog_anual::numeric) + coalesce(ac_seguimiento.tab_meta_fisica.nu_meta_modificada,0) + coalesce(ac_seguimiento.tab_meta_fisica.nu_meta_modificada_periodo,0) as nu_meta_actualizada'),DB::raw('coalesce(tab_meta_fisica.nu_meta_modificada_periodo,0) as nu_meta_modificada_periodo'),        
            'nu_numero',DB::raw('coalesce(ac_seguimiento.tab_meta_fisica.nu_meta_modificada,0) as nu_meta_modificada'),
            'nu_original',
            'co_sector')
            ->join('ac_seguimiento.tab_meta_financiera as t22', 'tab_meta_fisica.id', '=', 't22.id_tab_meta_fisica')
            ->join('mantenimiento.tab_fuente_financiamiento as t66', 't22.id_tab_fuente_financiamiento', '=', 't66.id')
             ->join('ac_seguimiento.tab_ac_ae as t03', 'tab_meta_fisica.id_tab_ac_ae', '=', 't03.id')
             ->join('mantenimiento.tab_ac_ae_predefinida as t04', 't03.id_tab_ac_ae_predefinida', '=', 't04.id')
             ->join('ac_seguimiento.tab_ac as t05', 't03.id_tab_ac', '=', 't05.id')
             ->join('mantenimiento.tab_ac_predefinida as t06', 't05.id_tab_ac_predefinida', '=', 't06.id')
             ->join('mantenimiento.tab_sectores as t07', 't05.id_tab_sectores', '=', 't07.id')
//            ->leftjoin('ac_seguimiento.tab_forma_002 as t002', function ($join) {
//            $join->on('tab_meta_fisica.id', '=', 't002.id_tab_meta_fisica')
//            ->on('t002.id_tab_estatus', '=', DB::raw('6'));
//            })                    
             ->where(function ($query) {
             $query->orWhere('tab_meta_fisica.nu_meta_modificada', '!=', 0)
             ->orWhere('mo_modificado_anual', '!=', 0);
             })
            ->where('id_tab_ac_ae', '=', $data->id_tab_ac_ae)
            ->orderBy('codigo', 'ASC')
            ->get();
             
if($actividad->count()>0){
$html1 = '';
foreach($actividad as $item) {
            
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
}
      
$html23='';
$html23.= '
<table border="0.1" style="width:100%" style="font-size:9px" cellpadding="3">
<thead>
<tr style="font-size:6px">
<th align="center" bgcolor="#BDBDBD" style="width: 28%;" rowspan="2">ACTIVIDAD</th>
<th align="center" bgcolor="#BDBDBD" style="width: 32%;" colspan="3">METAS FISICA</th>
<th align="center" bgcolor="#BDBDBD" style="width: 40%;" colspan="4">METAS FINANCIERAS</th>
</tr>
<tr style="font-size:6px">
<th align="center" bgcolor="#BDBDBD" style="width: 8%;">METAS PROGRAMADAS POA</th>
<th align="center" bgcolor="#BDBDBD" style="width: 8%;">METAS MODIFICADAS (T.ANT.)</th>
<th align="center" bgcolor="#BDBDBD" style="width: 8%;">METAS MODIFICADAS</th>
<th align="center" bgcolor="#BDBDBD" style="width: 8%;">METAS ACTUALIZADAS</th>
<th align="center" bgcolor="#BDBDBD" style="width: 8%;">PARTIDA PRESUPUESTARIA</th>
<th align="center" bgcolor="#BDBDBD" style="width: 8%;">PRESUPUESTO PROGRAMADO POA (Bs.)</th>
<th align="center" bgcolor="#BDBDBD" style="width: 8%;">PRESUPUESTO MODIFICADO (T.ANT.) (Bs.)</th>
<th align="center" bgcolor="#BDBDBD" style="width: 8%;">PRESUPUESTO MODIFICADO (Bs.)</th>
<th align="center" bgcolor="#BDBDBD" style="width: 8%;">PRESUPUESTO ACTUALIZADO (Bs.)</th>
</tr>
</thead>
';

$i = 1;
$id = 0;
$de_desvio = '';

foreach($actividad as $item) { 
    
            $tab_meta_financiera = tab_meta_fisica::join('ac_seguimiento.tab_meta_financiera as t01', 't01.id_tab_meta_fisica', '=', 'tab_meta_fisica.id')
             ->where('tab_meta_fisica.id', '=', $item->id)
             ->where(function ($query) {
             $query->orWhere('tab_meta_fisica.nu_meta_modificada', '!=', 0)
             ->orWhere('mo_modificado_anual', '!=', 0);
             })    
            ->get();       
             if($tab_meta_financiera->count()>1){
                $i =  $tab_meta_financiera->count();
             }else{
             $i = 1;    
             }
       
$html23.='
<tbody>';

                if($id==$item->id){

		$html23.='
		<tr style="font-size:6px" nobr="true">
                <td style="width: 8%;" align="center">'.$item->co_partida.'</td>                    
		<td style="width: 8%;"  align="center">'.$this->formatoDinero($item->mo_presupuesto).'</td>
		<td style="width: 8%;" align="center">'.$this->formatoDinero($item->mo_modificado).'</td>
                <td style="width: 8%;" align="center">'.$this->formatoDinero($item->mo_modificado_anual).'</td>
                <td style="width: 8%;" align="center">'.$this->formatoDinero($item->mo_actualizado_anual).'</td>';
                $html23.='</tr>';

                
                }else{
                    
                 if($de_desvio==''){
                     
                 }else{
		$html23.='
		<tr style="font-size:6px" nobr="true">
		<td style="width: 100%;"  nobr="true" rowspan="1">CAUSAS DEL DESVIO: '.$de_desvio.'</td>';
                $html23.='</tr>';                        
                 }
                    
 		$html23.='
		<tr style="font-size:6px" nobr="true">
		<td style="width: 28%;"  nobr="true" rowspan="'.$i.'">'.$item->codigo.' - '.$item->nb_meta.'</td>
		<td style="width: 8%;"  align="center" rowspan="'.$i.'">'.$this->formatoDinero($item->tx_prog_anual).'</td>
		<td style="width: 8%;"  align="center" rowspan="'.$i.'">'.$this->formatoDinero($item->nu_meta_modificada_periodo).'</td>
                <td style="width: 8%;"  align="center" rowspan="'.$i.'">'.$this->formatoDinero($item->nu_meta_modificada).'</td>
                <td style="width: 8%;" align="center" rowspan="'.$i.'">'.$this->formatoDinero($item->nu_meta_actualizada).'</td>
                <td style="width: 8%;" align="center">'.$item->co_partida.'</td>                    
		<td style="width: 8%;"  align="center">'.$this->formatoDinero($item->mo_presupuesto).'</td>
		<td style="width: 8%;" align="center">'.$this->formatoDinero($item->mo_modificado).'</td>
                <td style="width: 8%;" align="center">'.$this->formatoDinero($item->mo_modificado_anual).'</td>
                <td style="width: 8%;" align="center">'.$this->formatoDinero($item->mo_actualizado_anual).'</td>';
                $html23.='</tr>';                   
                    
                }
                $id =$item->id;
                $de_desvio=$item->de_desvio;
          
      }
      
        $html23.='
        <tr style="font-size:6px" nobr="true">
        <td style="width: 100%;"  nobr="true" rowspan="1">CAUSAS DEL DESVIO: '.$de_desvio.'</td>';
        $html23.='</tr>';  

$html23.='
</tbody>
</table>';


          $pdf->AddPage();

          $pdf->SetFont('','',11);
//          $pdf->writeHTML($htmlObjetivo, true, false, false, false, '');
          $pdf->writeHTML(Helper::htmlComprimir($html1), true, false, false, false, '');
          $pdf->Ln(-3);
          $pdf->writeHTML($html23, true, false, false, false, '');
          
            
            
}else{
$pdf->AddPage(); 
$pdf->SetFont('','B',11);
$pdf->MultiCell(277, 5, 'NO SE ENCONTRARÓN REGISTROS', 0, 'C', 0, 0, '', '', true);
}      
}
          $pdf->lastPage();
          $pdf->output('SEGUIMIENTO_AC_'.Session::get("ejercicio").'_'.date("H:i:s").'.pdf', 'D');
      }      

      public function ficha004Acumulada($id)
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
            'id_tab_tipo_periodo',
            'ac_seguimiento.tab_ac.de_observacion_003',
            'ac_seguimiento.tab_ac.de_sector',
            'ac_seguimiento.tab_ac.id_tab_ejercicio_fiscal',
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
          
          
          
             $tab_lapso = tab_lapso::where('id_tab_tipo_periodo', '<=', $data->id_tab_tipo_periodo)
            ->get();  
             
              $j =  $tab_lapso->count();

            $actividad = tab_meta_fisica::select('codigo','nb_meta',
            'co_partida',DB::raw('sum(distinct tx_prog_anual::numeric) as tx_prog_anual'),
                    DB::raw("string_agg(distinct de_desvio, ', ' ORDER BY de_desvio) as de_desvio"),
                DB::raw('sum(coalesce(mo_presupuesto,0))/'.$j.' as mo_presupuesto'),
                DB::raw('sum(coalesce(mo_modificado_anual,0)) as mo_modificado_anual'),
                DB::raw('sum(coalesce(mo_presupuesto,0))/'.$j.' + sum(coalesce(mo_modificado_anual,0)) as mo_actualizado_anual'))
            ->join('ac_seguimiento.tab_meta_financiera as t22', 'tab_meta_fisica.id', '=', 't22.id_tab_meta_fisica')
            ->join('mantenimiento.tab_fuente_financiamiento as t66', 't22.id_tab_fuente_financiamiento', '=', 't66.id')
             ->join('ac_seguimiento.tab_ac_ae as t03', 'tab_meta_fisica.id_tab_ac_ae', '=', 't03.id')
             ->join('mantenimiento.tab_ac_ae_predefinida as t04', 't03.id_tab_ac_ae_predefinida', '=', 't04.id')
             ->join('ac_seguimiento.tab_ac as t05', 't03.id_tab_ac', '=', 't05.id')
             ->join('mantenimiento.tab_ac_predefinida as t06', 't05.id_tab_ac_predefinida', '=', 't06.id')
             ->join('mantenimiento.tab_sectores as t07', 't05.id_tab_sectores', '=', 't07.id') 
             ->join('mantenimiento.tab_lapso as t02', 't05.id_tab_lapso', '=', 't02.id')
             ->where(function ($query) {
             $query->orWhere('tab_meta_fisica.nu_meta_modificada', '!=', 0)
             ->orWhere('mo_modificado_anual', '!=', 0);
             })
            ->where('t05.nu_codigo', '=', $data->id_proy_ac)
             ->where('t05.in_activo', '=', true)
            ->where('t03.id_tab_ac_ae_predefinida', '=', $data->id_tab_ac_ae_predefinida)
            ->where('id_tab_tipo_periodo', '<=', $data->id_tab_tipo_periodo)
             ->groupBy('codigo')
             ->groupBy('nb_meta')
             ->groupBy('co_partida')
            ->orderBy('codigo', 'ASC')
            ->get();
          
if($actividad->count()>0){             
             
$html1 = '';
foreach($actividad as $item) {
            
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
}
      
$html23='';
$html23.= '
<table border="0.1" style="width:100%" style="font-size:9px" cellpadding="3">
<thead>
<tr style="font-size:6px">
<th align="center" bgcolor="#BDBDBD" style="width: 30%;" rowspan="2">ACTIVIDAD</th>
<th align="center" bgcolor="#BDBDBD" style="width: 30%;" colspan="3">METAS FISICA</th>
<th align="center" bgcolor="#BDBDBD" style="width: 40%;" colspan="4">METAS FINANCIERAS</th>
</tr>
<tr style="font-size:6px">
<th align="center" bgcolor="#BDBDBD" style="width: 10%;">METAS PROGRAMADAS POA</th>
<th align="center" bgcolor="#BDBDBD" style="width: 10%;">METAS MODIFICADAS</th>
<th align="center" bgcolor="#BDBDBD" style="width: 10%;">METAS ACTUALIZADAS</th>
<th align="center" bgcolor="#BDBDBD" style="width: 10%;">PARTIDA PRESUPUESTARIA</th>
<th align="center" bgcolor="#BDBDBD" style="width: 10%;">PRESUPUESTO PROGRAMADO POA (Bs.)</th>
<th align="center" bgcolor="#BDBDBD" style="width: 10%;">PRESUPUESTO MODIFICADO (Bs.)</th>
<th align="center" bgcolor="#BDBDBD" style="width: 10%;">PRESUPUESTO ACTUALIZADO (Bs.)</th>
</tr>
</thead>
';

$i = 1;
$id = 0;
$de_desvio = '';

foreach($actividad as $item) {
    
    
                    $data2 = tab_ac::select(
                 DB::raw("coalesce(sum(nu_obtenido),0) as nu_obtenido"),DB::raw("coalesce(sum(nu_meta_modificada),0) as nu_meta_modificada")
                )
                ->join('ac_seguimiento.tab_ac_ae as t01', 'ac_seguimiento.tab_ac.id', '=', 't01.id_tab_ac')
                ->join('ac_seguimiento.tab_meta_fisica as t02', 't01.id', '=', 't02.id_tab_ac_ae')
                ->join('mantenimiento.tab_lapso as t03', 'ac_seguimiento.tab_ac.id_tab_lapso', '=', 't03.id')
                ->where('ac_seguimiento.tab_ac.nu_codigo', '=', $data->id_proy_ac)
                ->where('ac_seguimiento.tab_ac.in_activo', '=', true)
                ->where('t01.id_tab_ac_ae_predefinida', '=', $data->id_tab_ac_ae_predefinida)
                ->where('t02.codigo', '=', $item->codigo)
                ->where('id_tab_tipo_periodo', '<=', $data->id_tab_tipo_periodo)
                ->where('ac_seguimiento.tab_ac.id_tab_ejercicio_fiscal', '=', $data->id_tab_ejercicio_fiscal)
                ->first();
                    
            $data3 = tab_meta_fisica::select(
                DB::raw('sum(coalesce(mo_presupuesto,0))/'.$j.' as mo_presupuesto'),
                DB::raw('sum(coalesce(mo_modificado_anual,0)) as mo_modificado_anual'),
                DB::raw('sum(coalesce(mo_presupuesto,0))/'.$j.' + sum(coalesce(mo_modificado_anual,0)) as mo_actualizado_anual'))
            ->join('ac_seguimiento.tab_meta_financiera as t22', 'tab_meta_fisica.id', '=', 't22.id_tab_meta_fisica')
            ->join('mantenimiento.tab_fuente_financiamiento as t66', 't22.id_tab_fuente_financiamiento', '=', 't66.id')
             ->join('ac_seguimiento.tab_ac_ae as t03', 'tab_meta_fisica.id_tab_ac_ae', '=', 't03.id')
             ->join('mantenimiento.tab_ac_ae_predefinida as t04', 't03.id_tab_ac_ae_predefinida', '=', 't04.id')
             ->join('ac_seguimiento.tab_ac as t05', 't03.id_tab_ac', '=', 't05.id')
             ->join('mantenimiento.tab_ac_predefinida as t06', 't05.id_tab_ac_predefinida', '=', 't06.id')
             ->join('mantenimiento.tab_sectores as t07', 't05.id_tab_sectores', '=', 't07.id') 
             ->join('mantenimiento.tab_lapso as t02', 't05.id_tab_lapso', '=', 't02.id')
            ->where('t05.nu_codigo', '=', $data->id_proy_ac)
            ->where('t05.in_activo', '=', true)
            ->where('tab_meta_fisica.codigo', '=', $item->codigo)
            ->where('t22.co_partida', '=', $item->co_partida)
            ->where('t03.id_tab_ac_ae_predefinida', '=', $data->id_tab_ac_ae_predefinida)
            ->where('id_tab_tipo_periodo', '<=', $data->id_tab_tipo_periodo)
             ->groupBy('codigo')
             ->groupBy('nb_meta')
             ->groupBy('co_partida')
            ->orderBy('codigo', 'ASC')
            ->first();                    
                    
    
            $tab_meta_financiera = tab_meta_fisica::select('codigo','nb_meta',
            'co_partida',DB::raw('sum(distinct tx_prog_anual::numeric) as tx_prog_anual'),
                    DB::raw("string_agg(distinct de_desvio, ', ' ORDER BY de_desvio) as de_desvio"),
                DB::raw('sum(coalesce(mo_presupuesto,0))/'.$j.' as mo_presupuesto'),
                DB::raw('sum(coalesce(mo_modificado_anual,0)) as mo_modificado_anual'),
                DB::raw('sum(coalesce(mo_presupuesto,0))/'.$j.' + sum(coalesce(mo_modificado_anual,0)) as mo_actualizado_anual'))
            ->join('ac_seguimiento.tab_meta_financiera as t22', 'tab_meta_fisica.id', '=', 't22.id_tab_meta_fisica')
            ->join('mantenimiento.tab_fuente_financiamiento as t66', 't22.id_tab_fuente_financiamiento', '=', 't66.id')
             ->join('ac_seguimiento.tab_ac_ae as t03', 'tab_meta_fisica.id_tab_ac_ae', '=', 't03.id')
             ->join('mantenimiento.tab_ac_ae_predefinida as t04', 't03.id_tab_ac_ae_predefinida', '=', 't04.id')
             ->join('ac_seguimiento.tab_ac as t05', 't03.id_tab_ac', '=', 't05.id')
             ->join('mantenimiento.tab_ac_predefinida as t06', 't05.id_tab_ac_predefinida', '=', 't06.id')
             ->join('mantenimiento.tab_sectores as t07', 't05.id_tab_sectores', '=', 't07.id') 
             ->join('mantenimiento.tab_lapso as t02', 't05.id_tab_lapso', '=', 't02.id')
             ->where(function ($query) {
             $query->orWhere('tab_meta_fisica.nu_meta_modificada', '!=', 0)
             ->orWhere('mo_modificado_anual', '!=', 0);
             })
            ->where('t05.nu_codigo', '=', $data->id_proy_ac)
            ->where('t05.in_activo', '=', true)
            ->where('t03.id_tab_ac_ae_predefinida', '=', $data->id_tab_ac_ae_predefinida)
            ->where('id_tab_tipo_periodo', '<=', $data->id_tab_tipo_periodo)
            ->where('codigo', '=', $item->codigo)
             ->groupBy('codigo')
             ->groupBy('nb_meta')
             ->groupBy('co_partida')
            ->orderBy('codigo', 'ASC')
            ->get();
             if($tab_meta_financiera->count()>1){
                $i = $tab_meta_financiera->count();
             }else{
             $i = 1;    
             }
       
$html23.='
<tbody>';

                if($id==$item->codigo){

		$html23.='
		<tr style="font-size:6px" nobr="true">
                <td style="width: 10%;" align="center">'.$item->co_partida.'</td>                    
		<td style="width: 10%;"  align="center">'.$this->formatoDinero($data3->mo_presupuesto).'</td>
		<td style="width: 10%;" align="center">'.$this->formatoDinero($data3->mo_modificado_anual).'</td>
                <td style="width: 10%;" align="center">'.$this->formatoDinero($data3->mo_actualizado_anual).'</td>';
                $html23.='</tr>';

                
                }else{
                    
                 if($de_desvio==''){
                     
                 }else{
		$html23.='
		<tr style="font-size:6px" nobr="true">
		<td style="width: 100%;"  nobr="true" rowspan="1">CAUSAS DEL DESVIO: '.$de_desvio.'</td>';
                $html23.='</tr>';                        
                 }
                    
		$html23.='
		<tr style="font-size:6px" nobr="true">
                <td style="width: 30%;"  nobr="true" rowspan="'.$i.'">'.$item->codigo.' - '.$item->nb_meta.'</td>
		<td style="width: 10%;"  align="center" rowspan="'.$i.'">'.$this->formatoDinero($item->tx_prog_anual).'</td>
		<td style="width: 10%;"  align="center" rowspan="'.$i.'">'.$this->formatoDinero($data2->nu_meta_modificada).'</td>
                <td style="width: 10%;" align="center" rowspan="'.$i.'">'.$this->formatoDinero($item->tx_prog_anual + $data2->nu_meta_modificada).'</td>
                <td style="width: 10%;" align="center">'.$item->co_partida.'</td>                    
		<td style="width: 10%;"  align="center">'.$this->formatoDinero($data3->mo_presupuesto).'</td>
		<td style="width: 10%;" align="center">'.$this->formatoDinero($data3->mo_modificado_anual).'</td>
                <td style="width: 10%;" align="center">'.$this->formatoDinero($data3->mo_actualizado_anual).'</td>';
                $html23.='</tr>';                  
                    
                }
                    

                $id =$item->codigo;
                $de_desvio=$item->de_desvio;
          
      }
      
        $html23.='
        <tr style="font-size:6px" nobr="true">
        <td style="width: 100%;"  nobr="true" rowspan="1">CAUSAS DEL DESVIO: '.$de_desvio.'</td>';
        $html23.='</tr>';  

$html23.='
</tbody>
</table>';


          $pdf->AddPage();

          $pdf->SetFont('','',11);
//          $pdf->writeHTML($htmlObjetivo, true, false, false, false, '');
          $pdf->writeHTML(Helper::htmlComprimir($html1), true, false, false, false, '');
          $pdf->Ln(-3);
          $pdf->writeHTML($html23, true, false, false, false, '');
}else{
$pdf->AddPage(); 
$pdf->SetFont('','B',11);
$pdf->MultiCell(277, 5, 'NO SE ENCONTRARÓN REGISTROS', 0, 'C', 0, 0, '', '', true);
}     
            }
          $pdf->lastPage();
          $pdf->output('SEGUIMIENTO_AC_'.Session::get("ejercicio").'_'.date("H:i:s").'.pdf', 'D');
      }      
      
      
}
