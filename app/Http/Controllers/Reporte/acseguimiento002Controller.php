<?php

namespace matriz\Http\Controllers\Reporte;

//*******agregar esta linea******//
use matriz\Models\AcSegto\tab_meta_fisica;
use matriz\Models\AcSegto\tab_forma_001;
use matriz\Models\AcSegto\tab_forma_002;
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
        $pdf->MultiCell(277, 5, 'FORMULARIO Nº 2', 0, 'C', 0, 0, '', '', true);
        $pdf->Ln(5);
        $pdf->MultiCell(277, 5, 'METAS FÍSICAS', 0, 'C', 0, 0, '', '', true);
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

class acseguimiento002Controller extends Controller
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
	public function formatoPorcentaje($numero, $fractional=true){
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
	    return $numero."%";
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
      
      public function ficha002($id)
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
            DB::raw("to_char(t02.fe_inicio, 'dd/mm/YYYY') as fe_inicio"),
            DB::raw("to_char(t02.fe_fin, 'dd/mm/YYYY') as fe_fin"),
            't21.id as id_tab_ac_ae',
            'ac_seguimiento.tab_ac.tx_re_esperado',
            'ac_seguimiento.tab_ac.nu_po_beneficiar',
            'ac_seguimiento.tab_ac.nu_em_previsto',
            'ac_seguimiento.tab_ac.nu_po_beneficiada',
            'ac_seguimiento.tab_ac.nu_em_generado',
            'ac_seguimiento.tab_ac.tx_pr_programado',
            'ac_seguimiento.tab_ac.tx_pr_obtenido',
            'id_tab_tipo_periodo',
            'ac_seguimiento.tab_ac.de_observacion_002',
            'ac_seguimiento.tab_ac.de_sector'                    
        )
        ->where('t21.id_tab_ac', '=', $id)
        ->get();  
//var_dump($data);
//exit();
          
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

            $actividad = tab_meta_fisica::select('codigo','nb_meta',DB::raw('coalesce(tx_prog_anual::numeric,0) as tx_prog_anual'),'fecha_inicio','fecha_fin',
            'tab_meta_fisica.nb_responsable','de_unidad_medida as tx_unidades_medida',DB::raw('coalesce(tab_meta_fisica.nu_meta_modificada,0) as nu_meta_modificada'),
            DB::raw('coalesce(tab_meta_fisica.nu_meta_modificada_periodo,0) as nu_meta_modificada_periodo'),'de_municipio','de_parroquia','tab_meta_fisica.resultado','tab_meta_fisica.observacion',
            DB::raw('coalesce(tab_meta_fisica.nu_meta_actualizada,0) as nu_meta_actualizada'),DB::raw('coalesce(tab_meta_fisica.nu_obtenido,0) as nu_obtenido'))
            ->join('mantenimiento.tab_unidad_medida as t21', 'tab_meta_fisica.id_tab_unidad_medida', '=', 't21.id')
            ->leftjoin('ac_seguimiento.tab_forma_002 as t002', 'tab_meta_fisica.id', '=', 't002.id_tab_meta_fisica')
//            ->leftjoin('ac_seguimiento.tab_forma_002 as t002', function ($join) {
//            $join->on('tab_meta_fisica.id', '=', 't002.id_tab_meta_fisica')
//            ->on('t002.id_tab_estatus', '=', DB::raw('6'));
//            })
            ->leftjoin('mantenimiento.tab_municipio_detalle as t64', 'tab_meta_fisica.id_tab_municipio_detalle', '=', 't64.id')
            ->leftjoin('mantenimiento.tab_parroquia_detalle as t65', 'tab_meta_fisica.id_tab_parroquia_detalle', '=', 't65.id')            
            ->where('id_tab_ac_ae', '=', $data->id_tab_ac_ae)
            ->orderBy('codigo', 'ASC')
            ->get();
            
            $obtenido = '';
             $resultado = '';
              $observacion = '';
            
            foreach($actividad as $item) {
                
             $obtenido.=  $item->nu_obtenido.' '.$item->tx_unidades_medida.',';
             $resultado.=  $item->resultado.'-';
             $observacion.=  $item->observacion.'-';
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
<th colspan="11" style="width: 100%;"><b>METAS FISICAS</b></th>
</tr>
<tr style="font-size:6px">
<th align="center" bgcolor="#BDBDBD" style="width: 16%;" rowspan="2">ACTIVIDAD</th>
<th align="center" bgcolor="#BDBDBD" style="width: 7%;" rowspan="2">UNIDAD DE MEDIDA</th>
<th align="center" bgcolor="#BDBDBD" style="width: 8%;" rowspan="2">META PROGRAMADA POA</th>
<th align="center" bgcolor="#BDBDBD" style="width: 7%;" rowspan="2">META MODIFICADA (T.ANT.)</th>
<th align="center" bgcolor="#BDBDBD" style="width: 7%;" rowspan="2">META MODIFICADA (TRI)</th>
<th align="center" bgcolor="#BDBDBD" style="width: 8%;" rowspan="2">META ACTUALIZADA</th>
<th align="center" bgcolor="#BDBDBD" style="width: 12%;" colspan="2">FECHA PROGRAMADA</th>
<th align="center" bgcolor="#BDBDBD" style="width: 7%;" rowspan="2">OBTENIDO AL CORTE</th>
<th align="center" bgcolor="#BDBDBD" style="width: 9%;" rowspan="2">% EJEC. OBTENIDA AL CORTE Vs. EJEC. PROG. ANUAL</th>
<th align="center" bgcolor="#BDBDBD" style="width: 10%;" rowspan="2">LOCALIZACIÓN</th>
<th align="center" bgcolor="#BDBDBD" style="width: 9%;" rowspan="2">RESPONSABLE</th>
</tr>
<tr style="font-size:6px">
<th align="center" bgcolor="#BDBDBD" style="width: 6%;">INICIO</th>
<th align="center" bgcolor="#BDBDBD" style="width: 6%;">FINAL</th>
</tr>
</thead>
';        
       
$html23.='
<tbody>';
$cantidad = $actividad->count();

$contar=0;
      foreach($actividad as $item) {
 
          if($item->nu_meta_actualizada==0){
             $nu_meta_actualizada =  1;
             $obtenido = 0;
          }else{
            $nu_meta_actualizada =  $item->tx_prog_anual + $item->nu_meta_modificada + $item->nu_meta_modificada_periodo;
            if($nu_meta_actualizada==0){
                 $obtenido = 0;
            }else{
              $obtenido = ($item->nu_obtenido/$nu_meta_actualizada)*100;   
            }
          }
          
          
$contar=$contar+1;
		$html23.='
		<tr style="font-size:6px" nobr="true">
		<td style="width: 16%;"  nobr="true">'.$item->codigo.' - '.$item->nb_meta.'</td>
		<td style="width: 7%;"  align="center">'.$item->tx_unidades_medida.'</td>
		<td style="width: 8%;"  align="center">'.$item->tx_prog_anual.'</td>
                <td style="width: 7%;" align="center">'.$item->nu_meta_modificada_periodo.'</td>
                <td style="width: 7%;" align="center">'.$item->nu_meta_modificada.'</td>
                <td style="width: 8%;" align="center">'.($item->tx_prog_anual + $item->nu_meta_modificada + $item->nu_meta_modificada_periodo).'</td>                    
		<td style="width: 6%;"  align="center">'.trim(date_format(date_create($item->fecha_inicio),'d/m/Y')).'</td>
		<td style="width: 6%;" align="center">'.trim(date_format(date_create($item->fecha_fin),'d/m/Y')).'</td>
                <td style="width: 7%;" align="center">'.$this->formatoDinero($item->nu_obtenido).'</td>
                <td style="width: 9%;" align="center">'.$this->formatoPorcentaje($obtenido).'</td>
                <td style="width: 10%;"  align="center">'.$item->de_municipio.' / '.$item->de_parroquia.'</td>
		<td style="width: 9%;" align="center">'.$item->nb_responsable.'</td>';
                $html23.='</tr>';
//                				if($cantidad>$contar){
//					$html23.='</tr>
//					<tr style="font-size:6px" nobr="true">';
//				}else{
//					$html23.='';
//				}
                
          
      }
$html23.='      
<tr style="font-size:9px">
<td colspan="3" style="width: 60%;" align="justify" rowspan="2"><b>RESULTADOS ESPERADOS DEL OBJETIVO INSTITUCIONAL:</b>'.$data->tx_re_esperado.'</td>
<td colspan="3" style="width: 10%;" align="center"><b>POBLACIÓN A BENEFICIAR:</b></td>
<td colspan="3" style="width: 10%;" align="center"><b>POBLACIÓN BENEFICIADA:</b></td>
<td colspan="3" style="width: 10%;" align="center"><b>EMPLEOS A GENERAR:</b></td>
<td colspan="3" style="width: 10%;" align="center"><b>EMPLEOS GENERADOS:</b></td>
</tr>
<tr style="font-size:9px">
<td colspan="3" style="width: 10%;" align="center">'.$data->nu_po_beneficiar.'</td>
<td colspan="3" style="width: 10%;" align="center">'.$data->nu_po_beneficiada.'</td>
<td colspan="3" style="width: 10%;" align="center">'.$data->nu_em_previsto.'</td>
<td colspan="3" style="width: 10%;" align="center">'.$data->nu_em_generado.'</td>
</tr>
<tr style="font-size:9px">
<td colspan="16" style="height: 30px;" align="justify"><b>RESULTADOS OBTENIDOS:</b> '.$data->tx_pr_programado.'</td>
</tr>
<tr style="font-size:9px">
<td colspan="16" style="height: 30px;" align="justify"><b>OBSERVACIONES:</b>  '.$data->de_observacion_002.'</td>
</tr>';   

$html23.='
</tbody>
</table>';


          $pdf->AddPage();

          $pdf->SetFont('','',11);
          $pdf->writeHTML(Helper::htmlComprimir($html1), true, false, false, false, '');
          $pdf->Ln(-3);
          $pdf->writeHTML($html23, true, false, false, false, '');

      }      
          $pdf->lastPage();
          $pdf->output('SEGUIMIENTO_AC_'.Session::get("ejercicio").'_'.date("H:i:s").'.pdf', 'D');
      }
      
      
      public function ficha002Acumulada($id)
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
            'ac_seguimiento.tab_ac.id_tab_ac_predefinida',
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
            DB::raw("to_char(t02.fe_inicio, 'dd/mm/YYYY') as fe_inicio"),
            DB::raw("to_char(t02.fe_fin, 'dd/mm/YYYY') as fe_fin"),
            't21.id as id_tab_ac_ae',
            'ac_seguimiento.tab_ac.tx_re_esperado',
            'ac_seguimiento.tab_ac.nu_po_beneficiar',
            'ac_seguimiento.tab_ac.nu_em_previsto',
            'ac_seguimiento.tab_ac.nu_po_beneficiada',
            'ac_seguimiento.tab_ac.nu_em_generado',
            'ac_seguimiento.tab_ac.tx_pr_programado',
            'ac_seguimiento.tab_ac.tx_pr_obtenido',
            'ac_seguimiento.tab_ac.tx_pr_obtenido_a',
            'ac_seguimiento.tab_ac.id_tab_ejercicio_fiscal',
            'id_tab_tipo_periodo',
            'ac_seguimiento.tab_ac.de_observacion_002',
            'ac_seguimiento.tab_ac.de_sector',
            't21.id_tab_ac_ae_predefinida'                  
        )
        ->where('t21.id_tab_ac', '=', $id)
        ->get();  
//var_dump($data);
//exit();
          
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

            $actividad = tab_meta_fisica::select('codigo','nb_meta',DB::raw('coalesce(tx_prog_anual::numeric,0) as tx_prog_anual'),'fecha_inicio','fecha_fin',
            'tab_meta_fisica.nb_responsable','de_unidad_medida as tx_unidades_medida',DB::raw('coalesce(tab_meta_fisica.nu_meta_modificada,0) as nu_meta_modificada'),'de_municipio','de_parroquia','tab_meta_fisica.resultado','tab_meta_fisica.observacion',
            DB::raw('coalesce(tab_meta_fisica.nu_meta_actualizada,0) as nu_meta_actualizada'),DB::raw('coalesce(tab_meta_fisica.nu_obtenido,0) as nu_obtenido'))
            ->join('mantenimiento.tab_unidad_medida as t21', 'tab_meta_fisica.id_tab_unidad_medida', '=', 't21.id')
            ->leftjoin('ac_seguimiento.tab_forma_002 as t002', 'tab_meta_fisica.id', '=', 't002.id_tab_meta_fisica')
//            ->leftjoin('ac_seguimiento.tab_forma_002 as t002', function ($join) {
//            $join->on('tab_meta_fisica.id', '=', 't002.id_tab_meta_fisica')
//            ->on('t002.id_tab_estatus', '=', DB::raw('6'));
//            })
            ->leftjoin('mantenimiento.tab_municipio_detalle as t64', 'tab_meta_fisica.id_tab_municipio_detalle', '=', 't64.id')
            ->leftjoin('mantenimiento.tab_parroquia_detalle as t65', 'tab_meta_fisica.id_tab_parroquia_detalle', '=', 't65.id')            
            ->where('id_tab_ac_ae', '=', $data->id_tab_ac_ae)
            ->orderBy('codigo', 'ASC')
            ->get();
            
            $obtenido = '';
             $resultado = '';
              $observacion = '';
            
            foreach($actividad as $item) {
                
             $obtenido.=  $item->nu_obtenido.' '.$item->tx_unidades_medida.',';
             $resultado.=  $item->resultado.'-';
             $observacion.=  $item->observacion.'-';            
             
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
<th colspan="11" style="width: 100%;"><b>METAS FISICAS</b></th>
</tr>
<tr style="font-size:6px">
<th align="center" bgcolor="#BDBDBD" style="width: 18%;" rowspan="2">ACTIVIDAD</th>
<th align="center" bgcolor="#BDBDBD" style="width: 7%;" rowspan="2">UNIDAD DE MEDIDA</th>
<th align="center" bgcolor="#BDBDBD" style="width: 8%;" rowspan="2">META PROGRAMADA POA</th>
<th align="center" bgcolor="#BDBDBD" style="width: 7%;" rowspan="2">META MODIFICADA</th>
<th align="center" bgcolor="#BDBDBD" style="width: 8%;" rowspan="2">META ACTUALIZADA</th>
<th align="center" bgcolor="#BDBDBD" style="width: 16%;" colspan="2">FECHA PROGRAMADA</th>
<th align="center" bgcolor="#BDBDBD" style="width: 8%;" rowspan="2">OBTENIDO AL CORTE</th>
<th align="center" bgcolor="#BDBDBD" style="width: 9%;" rowspan="2">% EJEC. OBTENIDA AL CORTE Vs. EJEC. PROG. ANUAL</th>
<th align="center" bgcolor="#BDBDBD" style="width: 10%;" rowspan="2">LOCALIZACIÓN</th>
<th align="center" bgcolor="#BDBDBD" style="width: 9%;" rowspan="2">RESPONSABLE</th>
</tr>
<tr style="font-size:6px">
<th align="center" bgcolor="#BDBDBD" style="width: 8%;">INICIO</th>
<th align="center" bgcolor="#BDBDBD" style="width: 8%;">FINAL</th>
</tr>
</thead>
';        
       
$html23.='
<tbody>';
$cantidad = $actividad->count();

$contar=0;
      foreach($actividad as $item) {    
             
                $data2 = tab_ac::select(
                 DB::raw("coalesce(sum(nu_obtenido),0) as nu_obtenido"),
                        DB::raw("coalesce(sum(nu_meta_modificada),0) as nu_meta_modificada"),
                        DB::raw("coalesce(sum(nu_po_beneficiada),0) as nu_po_beneficiada")
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
                
                $data_poblacion = tab_ac::select(
                        DB::raw("coalesce(sum(nu_po_beneficiada),0) as nu_po_beneficiada")
                )
                ->join('mantenimiento.tab_lapso as t03', 'ac_seguimiento.tab_ac.id_tab_lapso', '=', 't03.id')
                ->where('ac_seguimiento.tab_ac.nu_codigo', '=', $data->id_proy_ac)
                ->where('ac_seguimiento.tab_ac.in_activo', '=', true)
                ->where('id_tab_tipo_periodo', '<=', $data->id_tab_tipo_periodo)
                ->where('ac_seguimiento.tab_ac.id_tab_ejercicio_fiscal', '=', $data->id_tab_ejercicio_fiscal)
                ->first();                 
                
                
                
             if($data->id_tab_ac_predefinida==1){
                 
             if($data_poblacion->nu_po_beneficiada>=$data->nu_po_beneficiar){
             $nu_po_beneficiada =   $data->nu_po_beneficiar;    
             }else{
             $nu_po_beneficiada =   $data_poblacion->nu_po_beneficiada;    
             }    
                 
             }else{
             if($data_poblacion->nu_po_beneficiada>=$data->nu_po_beneficiar){
             $nu_po_beneficiada =   $data->nu_po_beneficiar;    
             }else{
             $nu_po_beneficiada =   $data_poblacion->nu_po_beneficiada;    
             }   
             }   
                
            if($item->nu_meta_actualizada==0){
             $obtenido = 0;
            }else{

              $obtenido = ($data2->nu_obtenido/($item->tx_prog_anual + $data2->nu_meta_modificada))*100;   
            }                
          
$contar=$contar+1;
		$html23.='
		<tr style="font-size:6px" nobr="true">
		<td style="width: 18%;"  nobr="true">'.$item->codigo.' - '.$item->nb_meta.'</td>
		<td style="width: 7%;"  align="center">'.$item->tx_unidades_medida.'</td>
		<td style="width: 8%;"  align="center">'.$item->tx_prog_anual.'</td>
                <td style="width: 7%;" align="center">'.$data2->nu_meta_modificada.'</td>
                <td style="width: 8%;" align="center">'.($item->tx_prog_anual + $data2->nu_meta_modificada).'</td>                    
		<td style="width: 8%;"  align="center">'.trim(date_format(date_create($item->fecha_inicio),'d/m/Y')).'</td>
		<td style="width: 8%;" align="center">'.trim(date_format(date_create($item->fecha_fin),'d/m/Y')).'</td>
                <td style="width: 8%;" align="center">'.$this->formatoDinero($data2->nu_obtenido).'</td>
                <td style="width: 9%;" align="center">'.$this->formatoPorcentaje($obtenido).'</td>
                <td style="width: 10%;"  align="center">'.$item->de_municipio.' / '.$item->de_parroquia.'</td>
		<td style="width: 9%;" align="center">'.$item->nb_responsable.'</td>';
                $html23.='</tr>';
//                				if($cantidad>$contar){
//					$html23.='</tr>
//					<tr style="font-size:6px" nobr="true">';
//				}else{
//					$html23.='';
//				}
                
          
      }
$html23.='      
<tr style="font-size:9px">
<td colspan="3" style="width: 60%;" align="justify" rowspan="2"><b>RESULTADOS ESPERADOS DEL OBJETIVO INSTITUCIONAL:</b>'.$data->tx_re_esperado.'</td>
<td colspan="3" style="width: 10%;" align="center"><b>POBLACIÓN A BENEFICIAR:</b></td>
<td colspan="3" style="width: 10%;" align="center"><b>POBLACIÓN BENEFICIADA:</b></td>
<td colspan="3" style="width: 10%;" align="center"><b>EMPLEOS A GENERAR:</b></td>
<td colspan="3" style="width: 10%;" align="center"><b>EMPLEOS GENERADOS:</b></td>
</tr>
<tr style="font-size:9px">
<td colspan="3" style="width: 10%;" align="center">'.$data->nu_po_beneficiar.'</td>
<td colspan="3" style="width: 10%;" align="center">'.$nu_po_beneficiada.'</td>
<td colspan="3" style="width: 10%;" align="center">'.$data->nu_em_previsto.'</td>
<td colspan="3" style="width: 10%;" align="center">'.$data->nu_em_generado.'</td>
</tr>
<tr style="font-size:9px">
<td colspan="16" style="height: 30px;" align="justify"><b>RESULTADOS OBTENIDOS:</b> '.$data->tx_pr_programado.'</td>
</tr>
<tr style="font-size:9px">
<td colspan="16" style="height: 30px;" align="justify"><b>OBSERVACIONES:</b>  '.$data->de_observacion_002.'</td>
</tr>';   

$html23.='
</tbody>
</table>';


          $pdf->AddPage();

          $pdf->SetFont('','',11);
          $pdf->writeHTML(Helper::htmlComprimir($html1), true, false, false, false, '');
          $pdf->Ln(-3);
          $pdf->writeHTML($html23, true, false, false, false, '');

      }      
          $pdf->lastPage();
          $pdf->output('SEGUIMIENTO_AC_'.Session::get("ejercicio").'_'.date("H:i:s").'.pdf', 'D');
      }

      public function pendientes($id)
      {

          DB::beginTransaction();

          try {

            $tab_forma_002 = tab_meta_fisica::join('ac_seguimiento.tab_ac_ae as t05', 'ac_seguimiento.tab_meta_fisica.id_tab_ac_ae', '=', 't05.id')        
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
                't05.in_002' 
            )
            ->where('t01.id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))
            ->where('t01.in_activo', '=', true)
            ->where('in_cargado', '=', false)
            ->where('t01.id_tab_lapso', '=', $id)
                    ->groupBy('t01.id')
            ->groupBy('tx_ejecutor_ac')
                    ->groupBy('t02.in_activo')
                   ->groupBy('t05.in_002')
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
              $objPHPExcel->getActiveSheet()->setTitle('Mestas fisicas x Validar');
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

              foreach ($tab_forma_002 as $key => $value) {
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
              header('Content-Disposition: attachment; filename="METAS_FISICAS_PENDIENTES_X_VALIDAR_'.Session::get('ejercicio').'_'.date("Y-m-d").'.xlsx"');
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
