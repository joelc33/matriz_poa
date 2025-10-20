<?php

namespace matriz\Http\Controllers\Reporte;

//*******agregar esta linea******//
use matriz\Models\AcSegto\tab_meta_financiera;
use matriz\Models\AcSegto\tab_forma_001;
use matriz\Models\AcSegto\tab_ac;
use matriz\Models\AcSegto\tab_meta_fisica;
use matriz\Models\Mantenimiento\tab_lapso;
use matriz\Models\AcSegto\tab_forma_005;
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

        return $pdf;
    }

    public function pie($pdf)
    {
        
        $pdf->setXY(10, -10);
        $pdf->SetFont('', '', 9);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->writeHTMLCell(265, 0, '', '', 'Palacio de los Cóndores, Plaza Bolívar, Maracaibo, Estado Zulia, Venezuela', 0, 0, 0, true, 'C', true);
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

class acseguimientoController extends Controller
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
      public function reporte($id)
      {
          
        $lapso = tab_lapso::where('id', '=', $id)
        ->first();
        $data = json_encode(array("id_ejecutor" => Session::get('ejecutor')));
        return View::make('reporte.seguimiento.ac')->with('data', $data)->with('lapso', $lapso);          
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

	public function encabezado2($pdf){
            $pdf->setY(10);
            $pdf->MultiCell(277, 5, 'SISTEMA DE SEGUIMIENTO, EVALUACIÓN Y CONTROL DEL PLAN OPERATIVO ESTADAL', 0, 'C', 0, 0, '', '', true);
            $pdf->Ln(5);        
            $pdf->MultiCell(277, 5, 'FORMULARIO Nº 2', 0, 'C', 0, 0, '', '', true);
            $pdf->Ln(5);
            $pdf->MultiCell(277, 5, 'METAS FÍSICAS', 0, 'C', 0, 0, '', '', true);
            $pdf->Ln(10);
            
            return $pdf;
	}        

	public function encabezado3($pdf){
            $pdf->setY(10);
            $pdf->MultiCell(277, 5, 'SISTEMA DE SEGUIMIENTO, EVALUACIÓN Y CONTROL DEL PLAN OPERATIVO ESTADAL', 0, 'C', 0, 0, '', '', true);
            $pdf->Ln(5);        
            $pdf->MultiCell(277, 5, 'FORMULARIO Nº 3', 0, 'C', 0, 0, '', '', true);
            $pdf->Ln(5);
            $pdf->MultiCell(277, 5, 'METAS FINANCIERAS', 0, 'C', 0, 0, '', '', true);
            $pdf->Ln(10);
            
            return $pdf;
	}    
        
	public function encabezado4($pdf){
            $pdf->setY(10);
            $pdf->MultiCell(277, 5, 'SISTEMA DE SEGUIMIENTO, EVALUACIÓN Y CONTROL DEL PLAN OPERATIVO ESTADAL', 0, 'C', 0, 0, '', '', true);
            $pdf->Ln(5);        
            $pdf->MultiCell(277, 5, 'FORMULARIO Nº 4', 0, 'C', 0, 0, '', '', true);
            $pdf->Ln(5);
            $pdf->MultiCell(277, 5, 'DESVIO DE LA GESTIÓN', 0, 'C', 0, 0, '', '', true);
            $pdf->Ln(10);
            
            return $pdf;
	}  
        
	public function encabezado5($pdf){
            $pdf->setY(10);
            $pdf->MultiCell(277, 5, 'SISTEMA DE SEGUIMIENTO, EVALUACIÓN Y CONTROL DEL PLAN OPERATIVO ESTADAL', 0, 'C', 0, 0, '', '', true);
            $pdf->Ln(5);        
            $pdf->MultiCell(277, 5, 'FORMULARIO N° 5', 0, 'C', 0, 0, '', '', true);
            $pdf->Ln(5);
            $pdf->MultiCell(277, 5, 'INDICADORES DE GESTIÓN', 0, 'C', 0, 0, '', '', true);
            $pdf->Ln(10);
            
            return $pdf;
	}  
        
	public function encabezado6($pdf){
            $pdf->setY(10);
            $pdf->MultiCell(277, 5, 'SISTEMA DE SEGUIMIENTO, EVALUACIÓN Y CONTROL DEL PLAN OPERATIVO ESTADAL', 0, 'C', 0, 0, '', '', true);
            $pdf->Ln(5);        
            $pdf->MultiCell(277, 5, 'REPORTE DE LA EJECUCIÓN DEL PRESUPUESTO DE LOS EGRESOS POR SECTORES Y PARTIDAS', 0, 'C', 0, 0, '', '', true);
            $pdf->Ln(5);
            $pdf->MultiCell(277, 5, 'EJECUCIÓN PRESUPUESTARIA', 0, 'C', 0, 0, '', '', true);
            $pdf->Ln(10);
            
            return $pdf;
	}        
        
      /**
       * Display a listing of the resource.
       *
       * @return \Illuminate\Http\Response
       */
      public function fichaConsolidado($id_tab_lapso,$id_ejecutor=null)
      {
          
        $data_lapso = tab_lapso::select(
            'id_tab_tipo_periodo'
        )
        ->where('id', '=', $id_tab_lapso)
        ->first();          

      
                if($data_lapso->id_tab_tipo_periodo==19){
                    
                    $periodo = '1T/'.Session::get("ejercicio");    
                }
                
                if($data_lapso->id_tab_tipo_periodo==20){
                    
                    $periodo = '2T/'.Session::get("ejercicio");    
                }

                if($data_lapso->id_tab_tipo_periodo==21){
                    
                    $periodo = '3T/'.Session::get("ejercicio");    
                }

                if($data_lapso->id_tab_tipo_periodo==22){
                    
                    $periodo = '4T/'.Session::get("ejercicio");    
                }        
                
                Session::put('periodo',$periodo);          
          
            if($id_ejecutor!=null){

            $data = tab_ac::join('mantenimiento.tab_ejecutores as t04', 't04.id_ejecutor', '=', 'ac_seguimiento.tab_ac.id_ejecutor')
            ->join('mantenimiento.tab_lapso as t02', 'ac_seguimiento.tab_ac.id_tab_lapso', '=', 't02.id')
            ->leftjoin('ac_seguimiento.tab_ac_vinculo as t49', 't49.id_tab_ac', '=', 'ac_seguimiento.tab_ac.id')
            ->leftjoin('t45_planes_zulia as t45', function ($join) {
            $join->on('t49.co_area_estrategica', '=', 't45.co_area_estrategica')
            ->on('t45.nu_nivel', '=', DB::raw('0'));
            })
            ->select(
            'ac_seguimiento.tab_ac.id_ejecutor',
            'tx_ejecutor_ac',
            't45.tx_descripcion as tx_area_estrategica',        
            'ac_seguimiento.tab_ac.inst_mision',
            'ac_seguimiento.tab_ac.inst_vision',
            'ac_seguimiento.tab_ac.inst_objetivos',
            'id_tab_tipo_periodo'
            )
            ->where('ac_seguimiento.tab_ac.id_ejecutor', '=', $id_ejecutor)
            ->where('ac_seguimiento.tab_ac.id_tab_lapso', '=', $id_tab_lapso)
            ->where('ac_seguimiento.tab_ac.in_activo', '=', true)
            ->groupBy('tx_ejecutor_ac')
            ->groupBy('tab_ac.id_ejecutor')
            ->groupBy('tx_area_estrategica')
            ->groupBy('tab_ac.inst_mision')
            ->groupBy('tab_ac.inst_vision')
            ->groupBy('tab_ac.inst_objetivos')
            ->groupBy('id_tab_tipo_periodo')                    
            ->orderby('ac_seguimiento.tab_ac.id_ejecutor', 'ASC')->get();
            }else{
                
            $data = tab_ac::join('mantenimiento.tab_ejecutores as t04', 't04.id_ejecutor', '=', 'ac_seguimiento.tab_ac.id_ejecutor')
            ->join('mantenimiento.tab_lapso as t02', 'ac_seguimiento.tab_ac.id_tab_lapso', '=', 't02.id')   
            ->leftjoin('ac_seguimiento.tab_ac_vinculo as t49', 't49.id_tab_ac', '=', 'ac_seguimiento.tab_ac.id')
            ->leftjoin('t45_planes_zulia as t45', function ($join) {
            $join->on('t49.co_area_estrategica', '=', 't45.co_area_estrategica')
            ->on('t45.nu_nivel', '=', DB::raw('0'));
            })
            ->select(
            'ac_seguimiento.tab_ac.id_ejecutor',
            'tx_ejecutor_ac',
            't45.tx_descripcion as tx_area_estrategica',        
            'ac_seguimiento.tab_ac.inst_mision',
            'ac_seguimiento.tab_ac.inst_vision',
            'ac_seguimiento.tab_ac.inst_objetivos',
            'id_tab_tipo_periodo'
            )
            ->where('ac_seguimiento.tab_ac.id_tab_lapso', '=', $id_tab_lapso)
            ->where('ac_seguimiento.tab_ac.in_activo', '=', true)
            ->where('ac_seguimiento.tab_ac.in_abierta', '=', false)
            ->groupBy('tx_ejecutor_ac')
            ->groupBy('tab_ac.id_ejecutor')
            ->groupBy('tx_area_estrategica')
            ->groupBy('tab_ac.inst_mision')
            ->groupBy('tab_ac.inst_vision')
            ->groupBy('tab_ac.inst_objetivos')
            ->groupBy('id_tab_tipo_periodo')
            ->orderby('ac_seguimiento.tab_ac.id_ejecutor', 'ASC')->get();    
            }
                       

          $pdf = new PDFseguimientoAC("L", PDF_UNIT, 'Letter', true, 'UTF-8', false);
          $pdf->SetCreator('Sistema POA, Yoser Perez');
          $pdf->SetAuthor('Yoser Perez');
          $pdf->SetTitle('Seguimiento AC');
          $pdf->SetSubject('Seguimiento AC');
          $pdf->SetKeywords('Seguimiento AC, PDF, Zulia, SPE, '.Session::get("ejercicio").'');
          $pdf->SetMargins(10,10,10);
          $pdf->SetTopMargin(35);
          $pdf->SetPrintHeader(true);
          $pdf->SetPrintFooter(true);
          // set auto page breaks
          $pdf->SetAutoPageBreak(true, 10);
          $pdf->SetFont('','',11);
 
          $pdf->Ln(-3);


            foreach($data as $item) {
                
                
                if($item->id_tab_tipo_periodo==19){
                    
                    $mes = 'Marzo';
                }
                
                if($item->id_tab_tipo_periodo==20){
                    
                    $mes = 'Junio';
                }

                if($item->id_tab_tipo_periodo==21){
                    
                    $mes = 'Septiembre';
                }

                if($item->id_tab_tipo_periodo==22){
                    
                    $mes = 'Diciembre';
                }                
            
                $pdf->AddPage();
                $pdf->SetFont('','B',12);
            $pdf->setY(10);
            $pdf->MultiCell(277, 5, 'REPÚBLICA BOLIVARIANA DE VENEZUELA', 0, 'C', 0, 0, '', '', true);
            $pdf->Ln(10);        
            $pdf->MultiCell(277, 5, 'GOBERNACIÓN DEL ESTADO ZULIA', 0, 'C', 0, 0, '', '', true);
            $pdf->Ln(5);                
            $pdf->SetY(75);
            $pdf->SetFont('','B',18);
            $pdf->SetTextColor(0,0,0);
            $pdf->Write(0, 'SISTEMA DE SEGUIMIENTO, EVALUACIÓN Y CONTROL DEL PLAN OPERATIVO ESTADAL', '', 0, 'C', true, 0, false, false, 0);
            $pdf->Ln(5);
            $pdf->Write(0, 'AÑO '.Session::get("ejercicio"), '', 0, 'C', true, 0, false, false, 0);
            $pdf->Ln(10);
            $pdf->Write(0, $item->tx_ejecutor_ac, '', 0, 'C', true, 0, false, false, 0);
            $pdf->SetY(190);
            $pdf->SetFont('','',11);            
            $pdf->Write(0, 'Maracaibo, '.$mes.' de '.Session::get("ejercicio"), '', 0, 'C', true, 0, false, false, 0);
            $pdf->AddPage();
            
            $pdf->setY(10);
            $pdf->MultiCell(277, 5, 'SISTEMA DE SEGUIMIENTO, EVALUACIÓN Y CONTROL DEL PLAN OPERATIVO ESTADAL', 0, 'C', 0, 0, '', '', true);
            $pdf->Ln(5);
            $pdf->MultiCell(277, 5, 'FORMULARIO Nº 1', 0, 'C', 0, 0, '', '', true);
            $pdf->Ln(5);
            $pdf->MultiCell(277, 5, 'MARCO NORMATIVO INSTITUCIONAL', 0, 'C', 0, 0, '', '', true);
            $pdf->Ln(10);            
            
            $htmlObjetivo = '
    <table border="0.1" style="width:100%;text-align: center;" cellpadding="3">
            <tr align="left">
                    <td colspan="2"><b>1.2. UNIDAD EJECUTORA RESPONSABLE: </b>'.$item->tx_ejecutor_ac.'</td>
            </tr>
            <tr align="left">
                    <td colspan="2"><b>2.5.1. AREA ESTRATEGICA: </b>'.$item->tx_area_estrategica.'</td>
            </tr>
            <tr>
                    <td><b>MISIÓN</b></td>
                    <td><b>VISIÓN</b></td>
            </tr>
            <tr>
                    <td height="100" align="justify">'.$item->inst_mision.'</td>
                    <td height="100" align="justify">'.$item->inst_vision.'</td>
            </tr>
    <thead>
            <tr>
                    <td colspan="2"><b>OBJETIVOS INSTITUCIONALES</b></td>
            </tr>
    </thead>
    <tbody>
            <tr nobr="true">
                    <td colspan="2" height="100" align="justify">'.str_replace(array("\r\n","\r","\n","\\r","\\n","\\r\\n"),"<br/>",$item->inst_objetivos).'</td>
            </tr>
    </tbody>
    </table>';            
           $pdf->writeHTML(Helper::htmlComprimir($htmlObjetivo), true, false, false, false, ''); 
            
            $data2 = tab_ac::join('mantenimiento.tab_ejecutores as t04', 't04.id_ejecutor', '=', 'ac_seguimiento.tab_ac.id_ejecutor')
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
            'ac_seguimiento.tab_ac.id_tab_ejercicio_fiscal',
            'ac_seguimiento.tab_ac.de_observacion_002',
            'ac_seguimiento.tab_ac.de_observacion_003',
            'ac_seguimiento.tab_ac.id',
            'ac_seguimiento.tab_ac.de_sector'                    
        )
        ->where('ac_seguimiento.tab_ac.id_ejecutor', '=', $item->id_ejecutor)
        ->where('ac_seguimiento.tab_ac.in_activo', '=', true)
        ->where('ac_seguimiento.tab_ac.id_tab_lapso', '=', $id_tab_lapso)
        ->orderby('ac_seguimiento.tab_ac.id_ejecutor', 'ASC')->orderby('ac_seguimiento.tab_ac.id_tab_ac_predefinida', 'ASC')->orderby('t21.id_tab_ac_ae_predefinida', 'ASC')
        ->get(); 
            
          foreach($data2 as $data) {
              
           $pdf->AddPage();
           
           $this->encabezado2($pdf);
           
           
            $actividad = tab_meta_fisica::select('codigo','nb_meta','tx_prog_anual','fecha_inicio','fecha_fin',
            'tab_meta_fisica.nb_responsable','de_unidad_medida as tx_unidades_medida',DB::raw('coalesce(tab_meta_fisica.nu_meta_modificada,0) as nu_meta_modificada'),
            DB::raw('coalesce(tab_meta_fisica.nu_meta_modificada_periodo,0) as nu_meta_modificada_periodo'),'de_municipio','de_parroquia','tab_meta_fisica.resultado','tab_meta_fisica.observacion',
            DB::raw('coalesce(tab_meta_fisica.nu_meta_actualizada,0) as nu_meta_actualizada'),DB::raw('coalesce(tab_meta_fisica.nu_obtenido,0) as nu_obtenido'))
            ->join('mantenimiento.tab_unidad_medida as t21', 'tab_meta_fisica.id_tab_unidad_medida', '=', 't21.id')
            ->leftjoin('ac_seguimiento.tab_forma_002 as t002', 'tab_meta_fisica.id', '=', 't002.id_tab_meta_fisica')
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

          $pdf->SetFont('','',11);
          $pdf->writeHTML(Helper::htmlComprimir($html1), true, false, false, false, '');
          $pdf->Ln(-3);
          $pdf->writeHTML($html23, true, false, false, false, '');
          
          }
          
          foreach($data2 as $data) {
          
                $mo_presupuesto_anual_accion = 0;
                $mo_modificado_anual_accion = 0;
                $mo_modificado_anual_accion_acu = 0;
                $mo_actualizado_anual_accion = 0;
                $mo_comprometido_accion = 0;
                $mo_causado_accion = 0;
                $mo_pagado_accion = 0; 
                
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
            
            $actividad_ejecutor = tab_meta_fisica::select('codigo','nb_meta',DB::raw('coalesce(mo_presupuesto,0) as mo_presupuesto'),DB::raw('coalesce(mo_modificado_anual,0) as mo_modificado_anual'),DB::raw('coalesce(mo_modificado,0) as mo_modificado'),DB::raw('coalesce(mo_actualizado_anual,0) as mo_actualizado_anual'),
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
            

                
                $mo_presupuesto_anual_ejecutor = 0;
                $mo_modificado_anual_ejecutor = 0;
                $mo_modificado_anual_ejecutor_acu = 0;
                $mo_actualizado_anual_ejecutor = 0;
                $mo_comprometido_ejecutor = 0;
                $mo_causado_ejecutor = 0;
                $mo_pagado_ejecutor = 0;                
                
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
          $this->encabezado3($pdf);

          $pdf->SetFont('','',11);
//          $pdf->writeHTML($htmlObjetivo, true, false, false, false, '');
          $pdf->writeHTML(Helper::htmlComprimir($html1), true, false, false, false, '');
          $pdf->Ln(-3);
          $pdf->writeHTML($html23, true, false, false, false, '');       
          }
          
          foreach($data2 as $data) {
          
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
             ->where(function ($query) {
             $query->orWhere('tab_meta_fisica.nu_meta_modificada', '!=', 0)
             ->orWhere('mo_modificado_anual', '!=', 0);
             })
            ->where('id_tab_ac_ae', '=', $data->id_tab_ac_ae)
            ->orderBy('codigo', 'ASC')
            ->get();
             
if($actividad->count()>0){             
          
$html1 = '';
            
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
          $this->encabezado4($pdf);
          $pdf->SetFont('','',11);
//          $pdf->writeHTML($htmlObjetivo, true, false, false, false, '');
          $pdf->writeHTML(Helper::htmlComprimir($html1), true, false, false, false, '');
          $pdf->Ln(-3);
          $pdf->writeHTML($html23, true, false, false, false, '');          
}  

          }
          
          $id_ac = '';
          
          foreach($data2 as $data) {
              
            if($id_ac!=$data->id){
                
            $actividad = tab_forma_005::join('ac_seguimiento.tab_ac as t01', 'ac_seguimiento.tab_forma_005.id_tab_ac', '=', 't01.id')
            ->join('mantenimiento.tab_ejecutores as t02', 't01.id_tab_ejecutores', '=', 't02.id')
            ->join('mantenimiento.tab_lapso as t03', 't01.id_tab_lapso', '=', 't03.id')
            ->join('mantenimiento.tab_estatus as t04', 't04.id', '=', 'ac_seguimiento.tab_forma_005.id_tab_estatus')
            ->select(
                'ac_seguimiento.tab_forma_005.id',
                'ac_seguimiento.tab_forma_005.pp_anual',
                'ac_seguimiento.tab_forma_005.tp_indicador',
                'ac_seguimiento.tab_forma_005.nb_indicador_gestion',
                'ac_seguimiento.tab_forma_005.de_indicador_descripcion',
                'ac_seguimiento.tab_forma_005.de_valor_objetivo',
                'ac_seguimiento.tab_forma_005.de_valor_obtenido',
                'ac_seguimiento.tab_forma_005.nu_cumplimiento',
                'ac_seguimiento.tab_forma_005.de_formula',
                'de_estatus',
                'ac_seguimiento.tab_forma_005.id_tab_estatus',
                'ac_seguimiento.tab_forma_005.in_005'
            )                  
            ->where('id_tab_ac', '=', $data->id)
            ->orderby('ac_seguimiento.tab_forma_005.id', 'ASC')
            ->get();

         if($actividad->count()>0){ 
      foreach($actividad as $item) {
          
$pdf->AddPage();
$this->encabezado5($pdf);
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
<td colspan="3" style="width: 100%;" align="justify"><b>PRODUCTO PROGRAMADO ANUAL DEL OBJETIVO INSTITUCIONAL:</b> '.$data->tx_pr_objetivo.'</td>
</tr>
<tr style="font-size:9px">
<td colspan="3" style="width: 100%;" align="justify"><b>INDICADORES DE GESTIÓN (EFICIENCIA, EFICACIA, EFECTIVIDAD):</b> '.$item->tp_indicador.'</td>
</tr>
<tr style="font-size:9px">
<td style="width: 70%;  height: 30px;" align="justify" rowspan="2"><b>NOMBRE DEL INDICADOR:</b> '.$item->nb_indicador_gestion.'</td>
<td style="width: 10%;" align="center"><b>VALOR OBJETIVO:</b></td>
<td style="width: 10%;" align="center"><b>VALOR OBTENIDO:</b></td>
<td style="width: 10%;" align="center"><b>CUMPLIMIENTO:</b></td>
</tr>
<tr style="font-size:9px">
<td style="width: 10%;" align="center">'.$item->de_valor_objetivo.' </td>
<td style="width: 10%;" align="center">'.$item->de_valor_obtenido.' </td>
<td style="width: 10%;" align="center">'.$item->nu_cumplimiento.' % </td>
</tr>
<tr style="font-size:9px height: 100px;">
<td colspan="4" style="height: 30px;" align="justify"><b>DESCRIPCIÓN DEL INDICADOR:</b> '.$item->de_indicador_descripcion.'</td>
</tr>
<tr style="font-size:9px  height: 100px;">
<td colspan="4" style="height: 30px;" align="justify"><b>FORMULA:</b> '.$item->de_formula.'</td>
</tr>
</tbody>
</table>
';
        $pdf->writeHTML(Helper::htmlComprimir($html1), true, false, false, false, '');  
      }        
         }
         
            }

        $id_ac = $data->id;

      }
      
      
              $data3 =  tab_meta_financiera::select(
                'tx_nombre',
                DB::raw('sum(coalesce(mo_presupuesto,0)) as mo_presupuesto'),
                DB::raw('sum(coalesce(mo_modificado_anual,0)) as mo_modificado_anual'),
                DB::raw('sum(coalesce(mo_modificado,0)) as mo_modificado'),
                DB::raw('sum(coalesce(mo_presupuesto,0)) + sum(coalesce(mo_modificado_anual,0)) + sum(coalesce(mo_modificado,0)) as mo_actualizado_anual'),
                DB::raw('sum(coalesce(mo_comprometido,0)) as mo_comprometido'),
                DB::raw('sum(coalesce(mo_causado,0)) as mo_causado'),
                DB::raw('sum(coalesce(mo_pagado,0)) as mo_pagado'),                   
                'ac_seguimiento.tab_meta_financiera.co_partida',
                't03.id_ejecutor',
                'tx_ejecutor_ac',
                't18b.tx_codigo as tx_sector',
                'de_fuente_financiamiento',
                'dia_mes_fin',
                't03.id_tab_ejercicio_fiscal'
            )
            ->join('ac_seguimiento.tab_meta_fisica as t01', 'ac_seguimiento.tab_meta_financiera.id_tab_meta_fisica', '=', 't01.id')
            ->join('ac_seguimiento.tab_ac_ae as t02', 't01.id_tab_ac_ae', '=', 't02.id')
            ->join('ac_seguimiento.tab_ac as t03', 't02.id_tab_ac', '=', 't03.id')
            ->join('mantenimiento.tab_ejecutores as t05', 't05.id_ejecutor', '=', 't03.id_ejecutor')
            ->join('mantenimiento.tab_fuente_financiamiento as t06', 'ac_seguimiento.tab_meta_financiera.id_tab_fuente_financiamiento', '=', 't06.id')  
            ->join('mantenimiento.tab_lapso as t07', 't03.id_tab_lapso', '=', 't07.id')
            ->join('mantenimiento.tab_tipo_periodo as t08', 't07.id_tab_tipo_periodo', '=', 't08.id')        
            ->join('mantenimiento.tab_partidas as t04', function ($j) {
                $j->on('t04.co_partida', '=', 'ac_seguimiento.tab_meta_financiera.co_partida')
                  ->on('t04.id_tab_ejercicio_fiscal', '=', 't03.id_tab_ejercicio_fiscal');
            })
            ->join('mantenimiento.tab_sectores as t18a', 't03.id_tab_sectores', '=', 't18a.id')
            ->join('mantenimiento.tab_sectores as t18b', function ($join) {
            $join->on('t18a.co_sector', '=', 't18b.co_sector')
            ->on('t18b.nu_nivel', '=', DB::raw('1'));
            })             
            ->where('t03.id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))
            ->where('t03.in_activo', '=', true) 
            ->where('t03.id_ejecutor', '=', $id_ejecutor)
            ->where('t03.id_tab_lapso', '=', $id_tab_lapso)
            ->groupBy('ac_seguimiento.tab_meta_financiera.co_partida')
            ->groupBy('t03.id_ejecutor')
            ->groupBy('tx_ejecutor_ac')
            ->groupBy('tx_sector')
            ->groupBy('tx_nombre')
            ->groupBy('dia_mes_fin')
            ->groupBy('de_fuente_financiamiento')
            ->groupBy('t03.id_tab_ejercicio_fiscal')
            ->orderby('tx_sector', 'ASC')->orderby('ac_seguimiento.tab_meta_financiera.co_partida', 'ASC')->get();    
            
            
            $data_responsables = tab_ac::join('ac_seguimiento.tab_ac_responsable as t01', 't01.id_tab_ac', '=', 'ac_seguimiento.tab_ac.id')                    
            ->select(
            'realizador_nombres',
            'registrador_nombres',
            'autorizador_nombres',
            'realizador_cedula',
            'registrador_cedula',
            'autorizador_cedula'
        )
        ->where('ac_seguimiento.tab_ac.id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))
        ->where('ac_seguimiento.tab_ac.id_tab_lapso', '=', $id_tab_lapso)
        ->where('ac_seguimiento.tab_ac.id_ejecutor', '=', $id_ejecutor)
        ->first();    
      
      
                $mo_presupuesto = 0;
                $mo_modificado_anual = 0;
                $mo_modificado_anual_acu = 0;
                $mo_actualizado_anual = 0;
                $mo_comprometido = 0;
                $mo_causado = 0;
                $mo_pagado = 0;           
                $id_partida = 0;
                $tx_sector = '';
                $pdf->AddPage();

foreach($data3 as $item3) {
    
         
                $de_lapso = $item3->dia_mes_fin;
                $id_tab_ejercicio_fiscal = $item3->id_tab_ejercicio_fiscal;
         
         if($tx_sector!=$item3->tx_sector){
             
          $id_partida = 0;
          
                if($tx_sector!=''){
  
		$html23.='
		<tr style="font-size:7px" >
                <td  style="width: 100%;" ><b>SECTOR:</b> '.$item3->tx_sector.'</td>';
                $html23.='</tr>';                    
               
   
      	if($pdf->getY()>140){
            $pdf->addPage();
            $pdf->setY(35);
         }
   
   $html1 = '
<table border="0.1" style="font-size:9px" cellpadding="3" >
<tbody>
<tr style="font-size:8px">
<td style="width: 35%;" align="center"><b>PLANIFICACIÓN, PRESUPUESTO Y CONTROL DE GESTIÓN</b></td>
<td style="width: 35%;" align="center"><b>ADMINISTRACIÓN Y FINANZAS</b></td>
<td style="width: 30%;" align="center"><b>TITULAR</b></td>
</tr>
<tr style="font-size:8px">
<td style="width: 35%;" align="center"><b>FIRMA Y SELLO</b></td>
<td style="width: 35%;" align="center"><b>FIRMA Y SELLO</b></td>
<td style="width: 30%;" align="center"><b>FIRMA Y SELLO</b></td>
</tr>
<tr style="font-size:8px">
<td style="width: 35%; height: 40px;" align="center"><b></b></td>
<td style="width: 35%; height: 40px;" align="center"><b></b></td>
<td style="width: 30%; height: 40px;" align="center"><b></b></td>
</tr>
<tr style="font-size:8px">
<td style="width: 35%;" align="center"><b>NOMBRE Y APELLIDO</b></td>
<td style="width: 35%;" align="center"><b>NOMBRE Y APELLIDO</b></td>
<td style="width: 30%;" align="center"><b>NOMBRE Y APELLIDO</b></td>
</tr>
<tr style="font-size:8px">
<td style="width: 35%; height: 40px;" align="center"><b></b></td>
<td style="width: 35%; height: 40px;" align="center"><b></b></td>
<td style="width: 30%; height: 40px;" align="center"><b></b></td>
</tr>
<tr style="font-size:8px">
<td style="width: 35%;" align="center"><b>C.I. </b></td>
<td style="width: 35%;" align="center"><b>C.I. </b></td>
<td style="width: 30%;" align="center"><b>C.I. </b></td>
</tr>
</tbody>
</table>
';

}else{         
         


         if($item3->id_ejecutor){
          $tx_sector = $item3->tx_sector;   
          $ejecutor = $item3->id_ejecutor.' - '.$item3->tx_ejecutor_ac;
         }else{
          $tx_sector = $item3->tx_sector;
          $ejecutor = 'EJECUTOR: TODOS';
         }    
$this->encabezado6($pdf);
$html23='';
$html23.= '
<table border="0.1" style="width:100%" style="font-size:9px" cellpadding="3">
<thead>
<tr style="font-size:9px">
<td style="width: 100%;"><b>'.$ejecutor.'</b> </td>
</tr>
<tr style="font-size:7px">
<td style="width: 20%;" align="center" colspan="2"><b>PARTIDA PRESUPUESTARIA</b></td>
<td rowspan="2" style="width: 10%;" align="center"><b>PRESUPUESTO INICIAL</b></td>
<td rowspan="2" style="width: 10%;" align="center"><b>PRESUPUESTO MODIFICADO T. ANT.</b></td>
<td rowspan="2" style="width: 10%;" align="center"><b>PRESUPUESTO MODIFICADO</b></td>
<td rowspan="2" style="width: 10%;" align="center"><b>PRESUPUESTO ACTUALIZADO (TOTAL)</b></td>
<td rowspan="2" style="width: 10%;" align="center"><b>COMPROMETIDO</b></td>
<td rowspan="2" style="width: 10%;" align="center"><b>CAUSADO</b></td>
<td rowspan="2" style="width: 10%;" align="center"><b>PAGADO</b></td>
<td rowspan="2" style="width: 10%;" align="center"><b>FUENTE DE FINANCIAMIENTO</b></td>
</tr>
<tr style="font-size:7px">
<td style="width: 5%;" align="center"><b>CÓDIGO</b></td>
<td style="width: 15%;" align="center"><b>DENOMINACIÓN</b></td>
</tr>
</thead>
';  
$html23.='
<tbody>';  

		$html23.='
		<tr style="font-size:7px" >
                <td  style="width: 100%;" ><b>SECTOR:</b> '.$item3->tx_sector.'</td>';
                $html23.='</tr>';   

}

            if($id_ejecutor!=null){
             $tab_meta_financiera = tab_meta_financiera::select(                  
                'ac_seguimiento.tab_meta_financiera.co_partida'
            )
            ->join('ac_seguimiento.tab_meta_fisica as t01', 'ac_seguimiento.tab_meta_financiera.id_tab_meta_fisica', '=', 't01.id')
            ->join('ac_seguimiento.tab_ac_ae as t02', 't01.id_tab_ac_ae', '=', 't02.id')
            ->join('ac_seguimiento.tab_ac as t03', 't02.id_tab_ac', '=', 't03.id')
            ->join('mantenimiento.tab_fuente_financiamiento as t06', 'ac_seguimiento.tab_meta_financiera.id_tab_fuente_financiamiento', '=', 't06.id')
            ->join('mantenimiento.tab_lapso as t07', 't03.id_tab_lapso', '=', 't07.id')
            ->join('mantenimiento.tab_partidas as t04', function ($j) {
                $j->on('t04.co_partida', '=', 'ac_seguimiento.tab_meta_financiera.co_partida')
                  ->on('t04.id_tab_ejercicio_fiscal', '=', 't03.id_tab_ejercicio_fiscal');
            })
            ->join('mantenimiento.tab_sectores as t18a', 't03.id_tab_sectores', '=', 't18a.id')
            ->join('mantenimiento.tab_sectores as t18b', function ($join) {
            $join->on('t18a.co_sector', '=', 't18b.co_sector')
            ->on('t18b.nu_nivel', '=', DB::raw('1'));
            })      
            ->where('t03.id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))
            ->where('t03.in_activo', '=', true)
            ->where('t03.id_ejecutor', '=', $id_ejecutor)
            ->where('t03.id_tab_lapso', '=', $id_tab_lapso)
            ->where('t18b.tx_codigo', '=', $item3->tx_sector)
            ->where('ac_seguimiento.tab_meta_financiera.co_partida', '=', $item3->co_partida)        
            ->groupBy('ac_seguimiento.tab_meta_financiera.co_partida')
            ->groupBy('de_fuente_financiamiento')
            ->get(); 
            
            }else{
             
             $tab_meta_financiera = tab_meta_financiera::select(                  
                'ac_seguimiento.tab_meta_financiera.co_partida'
            )
            ->join('ac_seguimiento.tab_meta_fisica as t01', 'ac_seguimiento.tab_meta_financiera.id_tab_meta_fisica', '=', 't01.id')
            ->join('ac_seguimiento.tab_ac_ae as t02', 't01.id_tab_ac_ae', '=', 't02.id')
            ->join('ac_seguimiento.tab_ac as t03', 't02.id_tab_ac', '=', 't03.id')
            ->join('mantenimiento.tab_fuente_financiamiento as t06', 'ac_seguimiento.tab_meta_financiera.id_tab_fuente_financiamiento', '=', 't06.id')
            ->join('mantenimiento.tab_lapso as t07', 't03.id_tab_lapso', '=', 't07.id')
            ->join('mantenimiento.tab_partidas as t04', function ($j) {
                $j->on('t04.co_partida', '=', 'ac_seguimiento.tab_meta_financiera.co_partida')
                  ->on('t04.id_tab_ejercicio_fiscal', '=', 't03.id_tab_ejercicio_fiscal');
            })
            ->join('mantenimiento.tab_sectores as t18a', 't03.id_tab_sectores', '=', 't18a.id')
            ->join('mantenimiento.tab_sectores as t18b', function ($join) {
            $join->on('t18a.co_sector', '=', 't18b.co_sector')
            ->on('t18b.nu_nivel', '=', DB::raw('1'));
            })      
            ->where('t03.id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))
            ->where('t03.in_activo', '=', true)
            ->where('t03.id_tab_lapso', '=', $id_tab_lapso)
            ->where('t18b.tx_codigo', '=', $item3->tx_sector)
            ->where('ac_seguimiento.tab_meta_financiera.co_partida', '=', $item3->co_partida)        
            ->groupBy('ac_seguimiento.tab_meta_financiera.co_partida')
            ->groupBy('de_fuente_financiamiento')
            ->get();                
                
            }
             if($tab_meta_financiera->count()>1){
                $i =  $tab_meta_financiera->count();
             }else{
             $i = 1;    
             }

//             var_dump($i);
//             exit();
             
             if($id_partida==$item3->co_partida){

		$html23.='
		<tr style="font-size:6px" >
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_presupuesto).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_modificado).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_modificado_anual).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_actualizado_anual).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_comprometido).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_causado).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_pagado).'</td>                                 
                <td style="width: 10%;" align="center">'.$item3->de_fuente_financiamiento.'</td>';
                $html23.='</tr>';
                
             }else{
                 
		$html23.='
		<tr style="font-size:6px" >
		<td style="width: 5%;" align="center" rowspan="'.$i.'">'.$item3->co_partida.'</td>
                <td style="width: 15%;" rowspan="'.$i.'">'.$item3->tx_nombre.'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_presupuesto).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_modificado).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_modificado_anual).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_actualizado_anual).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_comprometido).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_causado).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_pagado).'</td>                                 
                <td style="width: 10%;" align="center">'.$item3->de_fuente_financiamiento.'</td>';
                $html23.='</tr>';
                 
             }

             
             
                $mo_presupuesto = $mo_presupuesto + $item3->mo_presupuesto;
                $mo_modificado_anual = $mo_modificado_anual + $item3->mo_modificado_anual;
                $mo_modificado_anual_acu = $mo_modificado_anual_acu + $item3->mo_modificado;
                $mo_actualizado_anual = $mo_actualizado_anual + $item3->mo_actualizado_anual;
                $mo_comprometido = $mo_comprometido + $item3->mo_comprometido;
                $mo_causado = $mo_causado + $item3->mo_causado;
                $mo_pagado = $mo_pagado + $item3->mo_pagado;
                $de_lapso = $item3->dia_mes_fin;
                $id_tab_ejercicio_fiscal = $item3->id_tab_ejercicio_fiscal;

            $tx_sector = $item3->tx_sector;


         }else{
             
            if($id_ejecutor!=null){
             $tab_meta_financiera = tab_meta_financiera::select(                  
                'ac_seguimiento.tab_meta_financiera.co_partida'
            )
            ->join('ac_seguimiento.tab_meta_fisica as t01', 'ac_seguimiento.tab_meta_financiera.id_tab_meta_fisica', '=', 't01.id')
            ->join('ac_seguimiento.tab_ac_ae as t02', 't01.id_tab_ac_ae', '=', 't02.id')
            ->join('ac_seguimiento.tab_ac as t03', 't02.id_tab_ac', '=', 't03.id')
            ->join('mantenimiento.tab_fuente_financiamiento as t06', 'ac_seguimiento.tab_meta_financiera.id_tab_fuente_financiamiento', '=', 't06.id')
            ->join('mantenimiento.tab_lapso as t07', 't03.id_tab_lapso', '=', 't07.id')
            ->join('mantenimiento.tab_partidas as t04', function ($j) {
                $j->on('t04.co_partida', '=', 'ac_seguimiento.tab_meta_financiera.co_partida')
                  ->on('t04.id_tab_ejercicio_fiscal', '=', 't03.id_tab_ejercicio_fiscal');
            })
            ->join('mantenimiento.tab_sectores as t18a', 't03.id_tab_sectores', '=', 't18a.id')
            ->join('mantenimiento.tab_sectores as t18b', function ($join) {
            $join->on('t18a.co_sector', '=', 't18b.co_sector')
            ->on('t18b.nu_nivel', '=', DB::raw('1'));
            })      
            ->where('t03.id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))
            ->where('t03.in_activo', '=', true)
            ->where('t03.id_ejecutor', '=', $id_ejecutor)
            ->where('t03.id_tab_lapso', '=', $id_tab_lapso)
            ->where('t18b.tx_codigo', '=', $item3->tx_sector)
            ->where('ac_seguimiento.tab_meta_financiera.co_partida', '=', $item3->co_partida)        
            ->groupBy('ac_seguimiento.tab_meta_financiera.co_partida')
            ->groupBy('de_fuente_financiamiento')
            ->get(); 
            
            }else{
             
             $tab_meta_financiera = tab_meta_financiera::select(                  
                'ac_seguimiento.tab_meta_financiera.co_partida'
            )
            ->join('ac_seguimiento.tab_meta_fisica as t01', 'ac_seguimiento.tab_meta_financiera.id_tab_meta_fisica', '=', 't01.id')
            ->join('ac_seguimiento.tab_ac_ae as t02', 't01.id_tab_ac_ae', '=', 't02.id')
            ->join('ac_seguimiento.tab_ac as t03', 't02.id_tab_ac', '=', 't03.id')
            ->join('mantenimiento.tab_fuente_financiamiento as t06', 'ac_seguimiento.tab_meta_financiera.id_tab_fuente_financiamiento', '=', 't06.id')
            ->join('mantenimiento.tab_lapso as t07', 't03.id_tab_lapso', '=', 't07.id')
            ->join('mantenimiento.tab_partidas as t04', function ($j) {
                $j->on('t04.co_partida', '=', 'ac_seguimiento.tab_meta_financiera.co_partida')
                  ->on('t04.id_tab_ejercicio_fiscal', '=', 't03.id_tab_ejercicio_fiscal');
            })
            ->join('mantenimiento.tab_sectores as t18a', 't03.id_tab_sectores', '=', 't18a.id')
            ->join('mantenimiento.tab_sectores as t18b', function ($join) {
            $join->on('t18a.co_sector', '=', 't18b.co_sector')
            ->on('t18b.nu_nivel', '=', DB::raw('1'));
            })      
            ->where('t03.id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))
            ->where('t03.in_activo', '=', true)
            ->where('t03.id_tab_lapso', '=', $id_tab_lapso)
            ->where('t18b.tx_codigo', '=', $item3->tx_sector)
            ->where('ac_seguimiento.tab_meta_financiera.co_partida', '=', $item3->co_partida)        
            ->groupBy('ac_seguimiento.tab_meta_financiera.co_partida')
            ->groupBy('de_fuente_financiamiento')
            ->get();                
                
            }              
             if($tab_meta_financiera->count()>1){
                $i =  $tab_meta_financiera->count();
             }else{
             $i = 1;    
             }

//             var_dump($i);
//             exit();
             
             if($id_partida==$item3->co_partida){

		$html23.='
		<tr style="font-size:6px" >
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_presupuesto).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_modificado).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_modificado_anual).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_actualizado_anual).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_comprometido).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_causado).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_pagado).'</td>                                 
                <td style="width: 10%;" align="center">'.$item3->de_fuente_financiamiento.'</td>';
                $html23.='</tr>';
                
             }else{
                 
		$html23.='
		<tr style="font-size:6px" >
		<td style="width: 5%;" align="center" rowspan="'.$i.'">'.$item3->co_partida.'</td>
                <td style="width: 15%;" rowspan="'.$i.'">'.$item3->tx_nombre.'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_presupuesto).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_modificado).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_modificado_anual).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_actualizado_anual).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_comprometido).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_causado).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_pagado).'</td>                                 
                <td style="width: 10%;" align="center">'.$item3->de_fuente_financiamiento.'</td>';
                $html23.='</tr>';
                 
             }

                $mo_presupuesto = $mo_presupuesto + $item3->mo_presupuesto;
                $mo_modificado_anual = $mo_modificado_anual + $item3->mo_modificado_anual;
                $mo_modificado_anual_acu = $mo_modificado_anual_acu + $item3->mo_modificado;
                $mo_actualizado_anual = $mo_actualizado_anual + $item3->mo_actualizado_anual;
                $mo_comprometido = $mo_comprometido + $item3->mo_comprometido;
                $mo_causado = $mo_causado + $item3->mo_causado;
                $mo_pagado = $mo_pagado + $item3->mo_pagado;
                $de_lapso = $item3->dia_mes_fin;
                $id_tab_ejercicio_fiscal = $item3->id_tab_ejercicio_fiscal;            
         }

        $id_partida =$item3->co_partida;
         
     }
                  


		$html23.='
		<tr style="font-size:6px" >
                <td style="width: 20%;" colspan="2" align="right"> TOTAL DESDE '.$de_lapso.' '.$id_tab_ejercicio_fiscal.' </td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($mo_presupuesto).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($mo_modificado_anual_acu).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($mo_modificado_anual).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($mo_actualizado_anual).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($mo_comprometido).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($mo_causado).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($mo_pagado).'</td>
                <td style="width: 10%;" align="right"></td>';
                $html23.='</tr>';

   $html23.='
</tbody>
</table>';  
   
             $pdf->writeHTML(Helper::htmlComprimir($html23), true, false, false, false, '');
   
      	if($pdf->getY()>140){
            $pdf->addPage();
            $pdf->setY(35);
         }
   
         
         if($data_responsables==''){
   $html1 = '
<table border="0.1" style="font-size:9px" cellpadding="3" >
<tbody>
<tr style="font-size:8px">
<td style="width: 35%;" align="center"><b>PLANIFICACIÓN, PRESUPUESTO Y CONTROL DE GESTIÓN</b></td>
<td style="width: 35%;" align="center"><b>ADMINISTRACIÓN Y FINANZAS</b></td>
<td style="width: 30%;" align="center"><b>TITULAR</b></td>
</tr>
<tr style="font-size:8px">
<td style="width: 35%;" align="center"><b>FIRMA Y SELLO</b></td>
<td style="width: 35%;" align="center"><b>FIRMA Y SELLO</b></td>
<td style="width: 30%;" align="center"><b>FIRMA Y SELLO</b></td>
</tr>
<tr style="font-size:8px">
<td style="width: 35%; height: 40px;" align="center"><b></b></td>
<td style="width: 35%; height: 40px;" align="center"><b></b></td>
<td style="width: 30%; height: 40px;" align="center"><b></b></td>
</tr>
<tr style="font-size:8px">
<td style="width: 35%;" align="center"><b>NOMBRE Y APELLIDO</b></td>
<td style="width: 35%;" align="center"><b>NOMBRE Y APELLIDO</b></td>
<td style="width: 30%;" align="center"><b>NOMBRE Y APELLIDO</b></td>
</tr>
<tr style="font-size:8px">
<td style="width: 35%; height: 40px;" align="center"><b></b></td>
<td style="width: 35%; height: 40px;" align="center"><b></b></td>
<td style="width: 30%; height: 40px;" align="center"><b></b></td>
</tr>
<tr style="font-size:8px">
<td style="width: 35%;" align="center"><b>C.I. </b></td>
<td style="width: 35%;" align="center"><b>C.I. </b></td>
<td style="width: 30%;" align="center"><b>C.I. </b></td>
</tr>
</tbody>
</table>
';
         }else{
             
   $html1 = '
<table border="0.1" style="font-size:9px" cellpadding="3" >
<tbody>
<tr style="font-size:8px">
<td style="width: 35%;" align="center"><b>PLANIFICACIÓN, PRESUPUESTO Y CONTROL DE GESTIÓN</b></td>
<td style="width: 35%;" align="center"><b>ADMINISTRACIÓN Y FINANZAS</b></td>
<td style="width: 30%;" align="center"><b>TITULAR</b></td>
</tr>
<tr style="font-size:8px">
<td style="width: 35%;" align="center"><b>FIRMA Y SELLO</b></td>
<td style="width: 35%;" align="center"><b>FIRMA Y SELLO</b></td>
<td style="width: 30%;" align="center"><b>FIRMA Y SELLO</b></td>
</tr>
<tr style="font-size:8px">
<td style="width: 35%; height: 40px;" align="center"><b></b></td>
<td style="width: 35%; height: 40px;" align="center"><b></b></td>
<td style="width: 30%; height: 40px;" align="center"><b></b></td>
</tr>
<tr style="font-size:8px">
<td style="width: 35%;" align="center"><b>NOMBRE Y APELLIDO</b></td>
<td style="width: 35%;" align="center"><b>NOMBRE Y APELLIDO</b></td>
<td style="width: 30%;" align="center"><b>NOMBRE Y APELLIDO</b></td>
</tr>
<tr style="font-size:8px">
<td style="width: 35%; height: 20px;" align="center">'.$data_responsables->realizador_nombres.'<b></b></td>
<td style="width: 35%; height: 20px;" align="center">'.$data_responsables->registrador_nombres.'<b></b></td>
<td style="width: 30%; height: 20px;" align="center">'.$data_responsables->autorizador_nombres.'<b></b></td>
</tr>
<tr style="font-size:8px">
<td style="width: 35%;" align="center"><b>C.I. '.$data_responsables->realizador_cedula.'</b></td>
<td style="width: 35%;" align="center"><b>C.I. '.$data_responsables->registrador_cedula.'</b></td>
<td style="width: 30%;" align="center"><b>C.I. '.$data_responsables->autorizador_cedula.'</b></td>
</tr>
</tbody>
</table>
';             
             
         }
   

          $pdf->SetX(140);
          $pdf->writeHTML(Helper::htmlComprimir($html1), true, false, false, false, '');      
      
      
           
            }   

          $pdf->SetX(140);
          
          $pdf->output('SEGUIMIENTO_AC_'.Session::get("ejercicio").'_'.date("H:i:s").'.pdf', 'D');
      }
      
      public function fichaConsolidadoAcumulada($id_tab_lapso,$id_ejecutor=null)
      {
          
        $data_lapso = tab_lapso::select(
            'id_tab_tipo_periodo'
        )
        ->where('id', '=', $id_tab_lapso)
        ->first();          

      
                if($data_lapso->id_tab_tipo_periodo==19){
                    
                    $periodo = '1TA/'.Session::get("ejercicio");
                    $dia_mes_fin = '31-03-';
                }
                
                if($data_lapso->id_tab_tipo_periodo==20){
                    
                    $periodo = '2TA/'.Session::get("ejercicio"); 
                    $dia_mes_fin = '30-06-';
                }

                if($data_lapso->id_tab_tipo_periodo==21){
                    
                    $periodo = '3TA/'.Session::get("ejercicio");
                    $dia_mes_fin = '30-09-';
                }

                if($data_lapso->id_tab_tipo_periodo==22){
                    
                    $periodo = '4TA/'.Session::get("ejercicio");
                    $dia_mes_fin = '31-12-';
                }        
                
                Session::put('periodo',$periodo);          
          
            if($id_ejecutor!=null){

            $data = tab_ac::join('mantenimiento.tab_ejecutores as t04', 't04.id_ejecutor', '=', 'ac_seguimiento.tab_ac.id_ejecutor')
            ->join('mantenimiento.tab_lapso as t02', 'ac_seguimiento.tab_ac.id_tab_lapso', '=', 't02.id')
            ->leftjoin('ac_seguimiento.tab_ac_vinculo as t49', 't49.id_tab_ac', '=', 'ac_seguimiento.tab_ac.id')
            ->leftjoin('t45_planes_zulia as t45', function ($join) {
            $join->on('t49.co_area_estrategica', '=', 't45.co_area_estrategica')
            ->on('t45.nu_nivel', '=', DB::raw('0'));
            })
            ->select(
            'ac_seguimiento.tab_ac.id_ejecutor',
            'tx_ejecutor_ac',
            't45.tx_descripcion as tx_area_estrategica',        
            'ac_seguimiento.tab_ac.inst_mision',
            'ac_seguimiento.tab_ac.inst_vision',
            'ac_seguimiento.tab_ac.inst_objetivos',
            'id_tab_tipo_periodo'
            )
            ->where('ac_seguimiento.tab_ac.id_ejecutor', '=', $id_ejecutor)
            ->where('ac_seguimiento.tab_ac.id_tab_lapso', '=', $id_tab_lapso)
            ->where('ac_seguimiento.tab_ac.in_activo', '=', true)
            ->groupBy('tx_ejecutor_ac')
            ->groupBy('tab_ac.id_ejecutor')
            ->groupBy('tx_area_estrategica')
            ->groupBy('tab_ac.inst_mision')
            ->groupBy('tab_ac.inst_vision')
            ->groupBy('tab_ac.inst_objetivos')
            ->groupBy('id_tab_tipo_periodo')                    
            ->orderby('ac_seguimiento.tab_ac.id_ejecutor', 'ASC')->get();
            }else{
                
            $data = tab_ac::join('mantenimiento.tab_ejecutores as t04', 't04.id_ejecutor', '=', 'ac_seguimiento.tab_ac.id_ejecutor')
            ->join('mantenimiento.tab_lapso as t02', 'ac_seguimiento.tab_ac.id_tab_lapso', '=', 't02.id')   
            ->leftjoin('ac_seguimiento.tab_ac_vinculo as t49', 't49.id_tab_ac', '=', 'ac_seguimiento.tab_ac.id')
            ->leftjoin('t45_planes_zulia as t45', function ($join) {
            $join->on('t49.co_area_estrategica', '=', 't45.co_area_estrategica')
            ->on('t45.nu_nivel', '=', DB::raw('0'));
            })
            ->select(
            'ac_seguimiento.tab_ac.id_ejecutor',
            'tx_ejecutor_ac',
            't45.tx_descripcion as tx_area_estrategica',        
            'ac_seguimiento.tab_ac.inst_mision',
            'ac_seguimiento.tab_ac.inst_vision',
            'ac_seguimiento.tab_ac.inst_objetivos',
            'id_tab_tipo_periodo'
            )
            ->where('ac_seguimiento.tab_ac.id_tab_lapso', '=', $id_tab_lapso)
            ->where('ac_seguimiento.tab_ac.in_activo', '=', true)
            ->where('ac_seguimiento.tab_ac.in_abierta', '=', false)
            ->groupBy('tx_ejecutor_ac')
            ->groupBy('tab_ac.id_ejecutor')
            ->groupBy('tx_area_estrategica')
            ->groupBy('tab_ac.inst_mision')
            ->groupBy('tab_ac.inst_vision')
            ->groupBy('tab_ac.inst_objetivos')
            ->groupBy('id_tab_tipo_periodo')
            ->orderby('ac_seguimiento.tab_ac.id_ejecutor', 'ASC')->get();    
            }
                       

          $pdf = new PDFseguimientoAC("L", PDF_UNIT, 'Letter', true, 'UTF-8', false);
          $pdf->SetCreator('Sistema POA, Yoser Perez');
          $pdf->SetAuthor('Yoser Perez');
          $pdf->SetTitle('Seguimiento AC');
          $pdf->SetSubject('Seguimiento AC');
          $pdf->SetKeywords('Seguimiento AC, PDF, Zulia, SPE, '.Session::get("ejercicio").'');
          $pdf->SetMargins(10,10,10);
          $pdf->SetTopMargin(35);
          $pdf->SetPrintHeader(true);
          $pdf->SetPrintFooter(true);
          // set auto page breaks
          $pdf->SetAutoPageBreak(true, 10);
          $pdf->SetFont('','',11);
 
          $pdf->Ln(-3);


            foreach($data as $item) {
                
                
                if($item->id_tab_tipo_periodo==19){
                    
                    $mes = 'Marzo';
                }
                
                if($item->id_tab_tipo_periodo==20){
                    
                    $mes = 'Junio';
                }

                if($item->id_tab_tipo_periodo==21){
                    
                    $mes = 'Septiembre';
                }

                if($item->id_tab_tipo_periodo==22){
                    
                    $mes = 'Diciembre';
                }                
            
                $pdf->AddPage();
                $pdf->SetFont('','B',12);
            $pdf->setY(10);
            $pdf->MultiCell(277, 5, 'REPÚBLICA BOLIVARIANA DE VENEZUELA', 0, 'C', 0, 0, '', '', true);
            $pdf->Ln(10);        
            $pdf->MultiCell(277, 5, 'GOBERNACIÓN DEL ESTADO ZULIA', 0, 'C', 0, 0, '', '', true);
            $pdf->Ln(5);                
            $pdf->SetY(75);
            $pdf->SetFont('','B',18);
            $pdf->SetTextColor(0,0,0);
            $pdf->Write(0, 'SISTEMA DE SEGUIMIENTO, EVALUACIÓN Y CONTROL DEL PLAN OPERATIVO ESTADAL', '', 0, 'C', true, 0, false, false, 0);
            $pdf->Ln(5);
            $pdf->Write(0, 'AÑO '.Session::get("ejercicio"), '', 0, 'C', true, 0, false, false, 0);
            $pdf->Ln(10);
            $pdf->Write(0, $item->tx_ejecutor_ac, '', 0, 'C', true, 0, false, false, 0);
            $pdf->SetY(190);
            $pdf->SetFont('','',11);            
            $pdf->Write(0, 'Maracaibo, '.$mes.' de '.Session::get("ejercicio"), '', 0, 'C', true, 0, false, false, 0);
            $pdf->AddPage();
            
            $pdf->setY(10);
            $pdf->MultiCell(277, 5, 'SISTEMA DE SEGUIMIENTO, EVALUACIÓN Y CONTROL DEL PLAN OPERATIVO ESTADAL', 0, 'C', 0, 0, '', '', true);
            $pdf->Ln(5);
            $pdf->MultiCell(277, 5, 'FORMULARIO Nº 1', 0, 'C', 0, 0, '', '', true);
            $pdf->Ln(5);
            $pdf->MultiCell(277, 5, 'MARCO NORMATIVO INSTITUCIONAL', 0, 'C', 0, 0, '', '', true);
            $pdf->Ln(10);            
            
            $htmlObjetivo = '
    <table border="0.1" style="width:100%;text-align: center;" cellpadding="3">
            <tr align="left">
                    <td colspan="2"><b>1.2. UNIDAD EJECUTORA RESPONSABLE: </b>'.$item->tx_ejecutor_ac.'</td>
            </tr>
            <tr align="left">
                    <td colspan="2"><b>2.5.1. AREA ESTRATEGICA: </b>'.$item->tx_area_estrategica.'</td>
            </tr>
            <tr>
                    <td><b>MISIÓN</b></td>
                    <td><b>VISIÓN</b></td>
            </tr>
            <tr>
                    <td height="100" align="justify">'.$item->inst_mision.'</td>
                    <td height="100" align="justify">'.$item->inst_vision.'</td>
            </tr>
    <thead>
            <tr>
                    <td colspan="2"><b>OBJETIVOS INSTITUCIONALES</b></td>
            </tr>
    </thead>
    <tbody>
            <tr nobr="true">
                    <td colspan="2" height="100" align="justify">'.str_replace(array("\r\n","\r","\n","\\r","\\n","\\r\\n"),"<br/>",$item->inst_objetivos).'</td>
            </tr>
    </tbody>
    </table>';            
           $pdf->writeHTML(Helper::htmlComprimir($htmlObjetivo), true, false, false, false, ''); 
            
            $data2 = tab_ac::join('mantenimiento.tab_ejecutores as t04', 't04.id_ejecutor', '=', 'ac_seguimiento.tab_ac.id_ejecutor')
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
            'id_tab_tipo_periodo',
            'ac_seguimiento.tab_ac.id_tab_ejercicio_fiscal',
            'ac_seguimiento.tab_ac.de_observacion_002',
            'ac_seguimiento.tab_ac.de_observacion_003',
            'ac_seguimiento.tab_ac.id',
            'ac_seguimiento.tab_ac.de_sector',
            't21.id_tab_ac_ae_predefinida'                    
        )
        ->where('ac_seguimiento.tab_ac.id_ejecutor', '=', $item->id_ejecutor)
        ->where('ac_seguimiento.tab_ac.in_activo', '=', true)
        ->where('ac_seguimiento.tab_ac.id_tab_lapso', '=', $id_tab_lapso)
        ->orderby('ac_seguimiento.tab_ac.id_ejecutor', 'ASC')->orderby('ac_seguimiento.tab_ac.id_tab_ac_predefinida', 'ASC')->orderby('t21.id_tab_ac_ae_predefinida', 'ASC')
        ->get(); 
            
          foreach($data2 as $data) {
              
           $pdf->AddPage();
           
           $this->encabezado2($pdf);
           
           
            $actividad = tab_meta_fisica::select('codigo','nb_meta','tx_prog_anual','fecha_inicio','fecha_fin',
            'tab_meta_fisica.nb_responsable','de_unidad_medida as tx_unidades_medida',DB::raw('coalesce(tab_meta_fisica.nu_meta_modificada,0) as nu_meta_modificada'),'de_municipio','de_parroquia','tab_meta_fisica.resultado','tab_meta_fisica.observacion',
            DB::raw('coalesce(tab_meta_fisica.nu_meta_actualizada,0) as nu_meta_actualizada'),DB::raw('coalesce(tab_meta_fisica.nu_obtenido,0) as nu_obtenido'))
            ->join('mantenimiento.tab_unidad_medida as t21', 'tab_meta_fisica.id_tab_unidad_medida', '=', 't21.id')
            ->leftjoin('ac_seguimiento.tab_forma_002 as t002', 'tab_meta_fisica.id', '=', 't002.id_tab_meta_fisica')
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
 
                $data20 = tab_ac::select(
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

              $obtenido = ($data20->nu_obtenido/($item->tx_prog_anual + $data20->nu_meta_modificada))*100;   
            }          
          
$contar=$contar+1;
		$html23.='
		<tr style="font-size:6px" nobr="true">
		<td style="width: 18%;"  nobr="true">'.$item->codigo.' - '.$item->nb_meta.'</td>
		<td style="width: 7%;"  align="center">'.$item->tx_unidades_medida.'</td>
		<td style="width: 8%;"  align="center">'.$item->tx_prog_anual.'</td>
                <td style="width: 7%;" align="center">'.$data20->nu_meta_modificada.'</td>
                <td style="width: 8%;" align="center">'.($item->tx_prog_anual + $data20->nu_meta_modificada).'</td>                    
		<td style="width: 8%;"  align="center">'.trim(date_format(date_create($item->fecha_inicio),'d/m/Y')).'</td>
		<td style="width: 8%;" align="center">'.trim(date_format(date_create($item->fecha_fin),'d/m/Y')).'</td>
                <td style="width: 8%;" align="center">'.$this->formatoDinero($data20->nu_obtenido).'</td>
                <td style="width: 9%;" align="center">'.$this->formatoPorcentaje($obtenido).'</td>
                <td style="width: 10%;"  align="center">'.$item->de_municipio.' / '.$item->de_parroquia.'</td>
		<td style="width: 9%;" align="center">'.$item->nb_responsable.'</td>';
                $html23.='</tr>';

                
          
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

          $pdf->SetFont('','',11);
          $pdf->writeHTML(Helper::htmlComprimir($html1), true, false, false, false, '');
          $pdf->Ln(-3);
          $pdf->writeHTML($html23, true, false, false, false, '');
          
          }
          
          foreach($data2 as $data) {
          
                $mo_presupuesto_anual_accion = 0;
                $mo_modificado_anual_accion = 0;
                $mo_actualizado_anual_accion = 0;
                $mo_comprometido_accion = 0;
                $mo_causado_accion = 0;
                $mo_pagado_accion = 0; 
                
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
            

                
                $mo_presupuesto_anual_ejecutor = 0;
                $mo_modificado_anual_ejecutor = 0;
                $mo_actualizado_anual_ejecutor = 0;
                $mo_comprometido_ejecutor = 0;
                $mo_causado_ejecutor = 0;
                $mo_pagado_ejecutor = 0;                
                
      foreach($actividad_accion as $item1) {
          
                $data3 = tab_ac::select(
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
                $mo_comprometido_accion = $data3->mo_comprometido;
                $mo_causado_accion = $data3->mo_causado;
                $mo_pagado_accion = $data3->mo_pagado;

      } 
      
      foreach($actividad_ejecutor as $item2) {
          
                $data3 = tab_ac::select(
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
                $mo_comprometido_ejecutor = $data3->mo_comprometido;
                $mo_causado_ejecutor = $data3->mo_causado;
                $mo_pagado_ejecutor = $data3->mo_pagado;

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
          
                $data4 = tab_ac::select(
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
                ->where('t03.co_partida', '=', $item->co_partida)
                ->where('t01.id_tab_ac_ae_predefinida', '=', $data->id_tab_ac_ae_predefinida)
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
                <td style="width: 9%;" align="center">'.$this->formatoDinero($data4->mo_comprometido).'</td>                    
		<td style="width: 9%;"  align="center">'.$this->formatoDinero($data4->mo_causado).'</td>
		<td style="width: 9%;" align="center">'.$this->formatoDinero($data4->mo_pagado).'</td>
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
                <td style="width: 9%;" align="center">'.$this->formatoDinero($data4->mo_comprometido).'</td>                    
		<td style="width: 9%;"  align="center">'.$this->formatoDinero($data4->mo_causado).'</td>
		<td style="width: 9%;" align="center">'.$this->formatoDinero($data4->mo_pagado).'</td>
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
                $mo_comprometido = $mo_comprometido + $data4->mo_comprometido;
                $mo_causado = $mo_causado + $data4->mo_causado;
                $mo_pagado = $mo_pagado + $data4->mo_pagado;
                

          
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
          $this->encabezado3($pdf);

          $pdf->SetFont('','',11);
//          $pdf->writeHTML($htmlObjetivo, true, false, false, false, '');
          $pdf->writeHTML(Helper::htmlComprimir($html1), true, false, false, false, '');
          $pdf->Ln(-3);
          $pdf->writeHTML($html23, true, false, false, false, '');       
          }
          
          foreach($data2 as $data) {
          
             $tab_lapso = tab_lapso::where('id_tab_tipo_periodo', '<=', $data->id_tab_tipo_periodo)
            ->where('id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))
            ->get();  
             
              $j =  $tab_lapso->count();

            $actividad = tab_meta_fisica::select('codigo','nb_meta',
            'co_partida',DB::raw('sum(distinct tx_prog_anual::numeric) as tx_prog_anual'),
                    DB::raw("string_agg(distinct de_desvio, ',') as de_desvio"),
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
$k = 1;
$id = 0;
$de_desvio = '';
$desvio = '';

foreach($actividad as $item) { 
    
    
                    $data10 = tab_ac::select(
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
                    
            $data11 = tab_meta_fisica::select(
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
            
            $data12 = tab_meta_fisica::select('tab_meta_fisica.id','codigo','nb_meta','de_desvio'
            )
             ->join('ac_seguimiento.tab_ac_ae as t03', 'tab_meta_fisica.id_tab_ac_ae', '=', 't03.id')
             ->join('mantenimiento.tab_ac_ae_predefinida as t04', 't03.id_tab_ac_ae_predefinida', '=', 't04.id')
             ->join('ac_seguimiento.tab_ac as t05', 't03.id_tab_ac', '=', 't05.id')
             ->join('mantenimiento.tab_ac_predefinida as t06', 't05.id_tab_ac_predefinida', '=', 't06.id')
             ->join('mantenimiento.tab_sectores as t07', 't05.id_tab_sectores', '=', 't07.id') 
             ->join('mantenimiento.tab_lapso as t02', 't05.id_tab_lapso', '=', 't02.id')
            ->where('t05.nu_codigo', '=', $data->id_proy_ac)
            ->where('t05.in_activo', '=', true)
            ->where('tab_meta_fisica.codigo', '=', $item->codigo)
            ->where('t03.id_tab_ac_ae_predefinida', '=', $data->id_tab_ac_ae_predefinida)
            ->where('id_tab_tipo_periodo', '<=', $data->id_tab_tipo_periodo)
             ->groupBy('codigo')
             ->groupBy('nb_meta')
            ->groupBy('de_desvio')
            ->groupBy('tab_meta_fisica.id')            
            ->orderBy('tab_meta_fisica.id', 'ASC')
            ->get();            
    
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
		<td style="width: 10%;"  align="center">'.$this->formatoDinero($data11->mo_presupuesto).'</td>
		<td style="width: 10%;" align="center">'.$this->formatoDinero($data11->mo_modificado_anual).'</td>
                <td style="width: 10%;" align="center">'.$this->formatoDinero($data11->mo_actualizado_anual).'</td>';
                $html23.='</tr>';

                
                }else{
                    
                 if($de_desvio==''){
                     
                 }else{
                                         
                     
		$html23.='
		<tr style="font-size:6px" nobr="true">
		<td style="width: 100%;"  nobr="true" rowspan="1">CAUSAS DEL DESVIO: '.$desvio.'</td>';
                $html23.='</tr>';   
                $desvio = '';
                $k = 1;
                 }
                    
		$html23.='
		<tr style="font-size:6px" nobr="true">
                <td style="width: 30%;"  nobr="true" rowspan="'.$i.'">'.$item->codigo.' - '.$item->nb_meta.'</td>
		<td style="width: 10%;"  align="center" rowspan="'.$i.'">'.$this->formatoDinero($item->tx_prog_anual).'</td>
		<td style="width: 10%;"  align="center" rowspan="'.$i.'">'.$this->formatoDinero($data10->nu_meta_modificada).'</td>
                <td style="width: 10%;" align="center" rowspan="'.$i.'">'.$this->formatoDinero($item->tx_prog_anual + $data10->nu_meta_modificada).'</td>
                <td style="width: 10%;" align="center">'.$item->co_partida.'</td>                    
		<td style="width: 10%;"  align="center">'.$this->formatoDinero($data11->mo_presupuesto).'</td>
		<td style="width: 10%;" align="center">'.$this->formatoDinero($data11->mo_modificado_anual).'</td>
                <td style="width: 10%;" align="center">'.$this->formatoDinero($data11->mo_actualizado_anual).'</td>';
                $html23.='</tr>';   
                
                foreach($data12 as $item12) {
                if($item12->de_desvio!=''){    
                $desvio = $desvio.' '.$k.'. TRIMESTRE: '.$item12->de_desvio;
                
                }
                $k++;
                }                 
                    
                }
                    

                $id =$item->codigo;
                $de_desvio=$item->de_desvio;
                
          
      }
      
        $html23.='
        <tr style="font-size:6px" nobr="true">
        <td style="width: 100%;"  nobr="true" rowspan="1">CAUSAS DEL DESVIO: '.$desvio.'</td>';
        $html23.='</tr>';        

$html23.='
</tbody>
</table>';


          $pdf->AddPage();
          $this->encabezado4($pdf);
          $pdf->SetFont('','',11);
//          $pdf->writeHTML($htmlObjetivo, true, false, false, false, '');
          $pdf->writeHTML(Helper::htmlComprimir($html1), true, false, false, false, '');
          $pdf->Ln(-3);
          $pdf->writeHTML($html23, true, false, false, false, '');          
}  

          }
          
          $id_ac = '';
          
          foreach($data2 as $data) {
              
            if($id_ac!=$data->id){
                
            $actividad = tab_forma_005::join('ac_seguimiento.tab_ac as t01', 'ac_seguimiento.tab_forma_005.id_tab_ac', '=', 't01.id')
            ->join('mantenimiento.tab_ejecutores as t02', 't01.id_tab_ejecutores', '=', 't02.id')
            ->join('mantenimiento.tab_lapso as t03', 't01.id_tab_lapso', '=', 't03.id')
            ->join('mantenimiento.tab_estatus as t04', 't04.id', '=', 'ac_seguimiento.tab_forma_005.id_tab_estatus')
            ->select(
                'ac_seguimiento.tab_forma_005.id',
                'ac_seguimiento.tab_forma_005.pp_anual',
                'ac_seguimiento.tab_forma_005.tp_indicador',
                'ac_seguimiento.tab_forma_005.nb_indicador_gestion',
                'ac_seguimiento.tab_forma_005.de_indicador_descripcion',
                'ac_seguimiento.tab_forma_005.de_valor_objetivo',
                'ac_seguimiento.tab_forma_005.de_valor_obtenido',
                'ac_seguimiento.tab_forma_005.de_valor_objetivo_acu',
                'ac_seguimiento.tab_forma_005.de_valor_obtenido_acu',                    
                'ac_seguimiento.tab_forma_005.nu_cumplimiento',
                'ac_seguimiento.tab_forma_005.de_formula',
                'de_estatus',
                'ac_seguimiento.tab_forma_005.id_tab_estatus',
                'ac_seguimiento.tab_forma_005.in_005'
            )                  
            ->where('id_tab_ac', '=', $data->id)
            ->orderby('ac_seguimiento.tab_forma_005.id', 'ASC')
            ->get();

         if($actividad->count()>0){ 
      foreach($actividad as $item) {
          
          if($item->de_valor_objetivo_acu==null || $item->de_valor_objetivo_acu==0){
          $nu_cumplimiento = 0;    
          }else{
          $nu_cumplimiento = round(($item->de_valor_obtenido_acu/$item->de_valor_objetivo_acu)*100,2);
          }
          
$pdf->AddPage();
$this->encabezado5($pdf);
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
<td colspan="3" style="width: 100%;" align="justify"><b>PRODUCTO PROGRAMADO ANUAL DEL OBJETIVO INSTITUCIONAL:</b> '.$data->tx_pr_objetivo.'</td>
</tr>
<tr style="font-size:9px">
<td colspan="3" style="width: 100%;" align="justify"><b>INDICADORES DE GESTIÓN (EFICIENCIA, EFICACIA, EFECTIVIDAD):</b> '.$item->tp_indicador.'</td>
</tr>
<tr style="font-size:9px">
<td style="width: 70%;  height: 30px;" align="justify" rowspan="2"><b>NOMBRE DEL INDICADOR:</b> '.$item->nb_indicador_gestion.'</td>
<td style="width: 10%;" align="center"><b>VALOR OBJETIVO:</b></td>
<td style="width: 10%;" align="center"><b>VALOR OBTENIDO:</b></td>
<td style="width: 10%;" align="center"><b>CUMPLIMIENTO:</b></td>
</tr>
<tr style="font-size:9px">
<td style="width: 10%;" align="center">'.$item->de_valor_objetivo_acu.' </td>
<td style="width: 10%;" align="center">'.$item->de_valor_obtenido_acu.' </td>
<td style="width: 10%;" align="center">'.$nu_cumplimiento.' % </td>
</tr>
<tr style="font-size:9px height: 100px;">
<td colspan="4" style="height: 30px;" align="justify"><b>DESCRIPCIÓN DEL INDICADOR:</b> '.$item->de_indicador_descripcion.'</td>
</tr>
<tr style="font-size:9px  height: 100px;">
<td colspan="4" style="height: 30px;" align="justify"><b>FORMULA:</b> '.$item->de_formula.'</td>
</tr>
</tbody>
</table>
';
        $pdf->writeHTML(Helper::htmlComprimir($html1), true, false, false, false, '');  
      }        
         }
         
            }

        $id_ac = $data->id;

      }
      
              $data_ejecutor = tab_ac::select(
            'tx_ejecutor_ac'
        )
        ->where('id_ejecutor', '=', $id_ejecutor)
        ->where('ac_seguimiento.tab_ac.id_tab_ejercicio_fiscal', '=', Session::get("ejercicio"))
        ->where('id_tab_lapso', '=', $id_tab_lapso)
        ->first(); 
      
            $tab_lapso = tab_lapso::where('id', '<=', $id_tab_lapso)
            ->where('id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))
            ->get();  
             
              $i =  $tab_lapso->count();
      
      
              $data3 =  tab_meta_financiera::select(
                'tx_nombre',
                DB::raw('sum(coalesce(mo_presupuesto,0))/'.$i.' as mo_presupuesto'),
                DB::raw('sum(coalesce(mo_modificado_anual,0)) as mo_modificado_anual'),
                DB::raw('sum(coalesce(mo_presupuesto,0))/'.$i.' + sum(coalesce(mo_modificado_anual,0)) as mo_actualizado_anual'),
                DB::raw('sum(coalesce(mo_comprometido,0)) as mo_comprometido'),
                DB::raw('sum(coalesce(mo_causado,0)) as mo_causado'),
                DB::raw('sum(coalesce(mo_pagado,0)) as mo_pagado'),                   
                'ac_seguimiento.tab_meta_financiera.co_partida',
                't03.id_ejecutor',
                't18b.tx_codigo as tx_sector',
                'de_fuente_financiamiento',
                't03.id_tab_ejercicio_fiscal'
            )
            ->join('ac_seguimiento.tab_meta_fisica as t01', 'ac_seguimiento.tab_meta_financiera.id_tab_meta_fisica', '=', 't01.id')
            ->join('ac_seguimiento.tab_ac_ae as t02', 't01.id_tab_ac_ae', '=', 't02.id')
            ->join('ac_seguimiento.tab_ac as t03', 't02.id_tab_ac', '=', 't03.id')
            ->join('mantenimiento.tab_ejecutores as t05', 't05.id_ejecutor', '=', 't03.id_ejecutor')
            ->join('mantenimiento.tab_fuente_financiamiento as t06', 'ac_seguimiento.tab_meta_financiera.id_tab_fuente_financiamiento', '=', 't06.id')  
            ->join('mantenimiento.tab_lapso as t07', 't03.id_tab_lapso', '=', 't07.id')
            ->join('mantenimiento.tab_tipo_periodo as t08', 't07.id_tab_tipo_periodo', '=', 't08.id')        
            ->join('mantenimiento.tab_partidas as t04', function ($j) {
                $j->on('t04.co_partida', '=', 'ac_seguimiento.tab_meta_financiera.co_partida')
                  ->on('t04.id_tab_ejercicio_fiscal', '=', 't03.id_tab_ejercicio_fiscal');
            })
            ->join('mantenimiento.tab_sectores as t18a', 't03.id_tab_sectores', '=', 't18a.id')
            ->join('mantenimiento.tab_sectores as t18b', function ($join) {
            $join->on('t18a.co_sector', '=', 't18b.co_sector')
            ->on('t18b.nu_nivel', '=', DB::raw('1'));
            })             
            ->where('t03.id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))
            ->where('t03.in_activo', '=', true) 
            ->where('t03.id_ejecutor', '=', $id_ejecutor)
            ->where('t03.id_tab_lapso', '<=', $id_tab_lapso)
            ->groupBy('ac_seguimiento.tab_meta_financiera.co_partida')
            ->groupBy('t03.id_ejecutor')
            ->groupBy('tx_sector')
            ->groupBy('tx_nombre')
            ->groupBy('de_fuente_financiamiento')
            ->groupBy('t03.id_tab_ejercicio_fiscal')
            ->orderby('tx_sector', 'ASC')->orderby('ac_seguimiento.tab_meta_financiera.co_partida', 'ASC')->get();    
            
            
            $data_responsables = tab_ac::join('ac_seguimiento.tab_ac_responsable as t01', 't01.id_tab_ac', '=', 'ac_seguimiento.tab_ac.id')                    
            ->select(
            'realizador_nombres',
            'registrador_nombres',
            'autorizador_nombres',
            'realizador_cedula',
            'registrador_cedula',
            'autorizador_cedula'
        )
        ->where('ac_seguimiento.tab_ac.id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))
        ->where('ac_seguimiento.tab_ac.id_tab_lapso', '=', $id_tab_lapso)
        ->where('ac_seguimiento.tab_ac.id_ejecutor', '=', $id_ejecutor)
        ->first();    
      
      
                 $mo_presupuesto = 0;
                $mo_modificado_anual = 0;
                $mo_actualizado_anual = 0;
                $mo_comprometido = 0;
                $mo_causado = 0;
                $mo_pagado = 0;          
                $id_partida = 0;
                $tx_sector = '';
                $pdf->AddPage();

foreach($data3 as $item3) {
    
         
                $de_lapso = $dia_mes_fin;
                $id_tab_ejercicio_fiscal = $item3->id_tab_ejercicio_fiscal;
         
         if($tx_sector!=$item3->tx_sector){
             
          $id_partida = 0;
          
                if($tx_sector!=''){
    
//		$html23.='
//		<tr style="font-size:7px" >
//                <td style="width: 20%;" colspan="2" align="right"> TOTAL EJECUTADO AL '.$de_lapso.' '.$id_tab_ejercicio_fiscal.' </td>
//                <td style="width: 10%;" align="right">'.$this->formatoDinero($mo_presupuesto).'</td>
//                <td style="width: 10%;" align="right">'.$this->formatoDinero($mo_modificado_anual).'</td>
//                <td style="width: 10%;" align="right">'.$this->formatoDinero($mo_actualizado_anual).'</td>
//                <td style="width: 10%;" align="right">'.$this->formatoDinero($mo_comprometido).'</td>
//                <td style="width: 10%;" align="right">'.$this->formatoDinero($mo_causado).'</td>
//                <td style="width: 10%;" align="right">'.$this->formatoDinero($mo_pagado).'</td>';
//                $html23.='</tr>';
                
//                $mo_presupuesto = 0;
//                $mo_modificado_anual = 0;
//                $mo_actualizado_anual = 0;
//                $mo_comprometido = 0;
//                $mo_causado = 0;
//                $mo_pagado = 0;  

//   $html23.='
//</tbody>
//</table>';  
  
		$html23.='
		<tr style="font-size:7px" >
                <td  style="width: 100%;" ><b>SECTOR:</b> '.$item3->tx_sector.'</td>';
                $html23.='</tr>';                    
               
//             $pdf->writeHTML(Helper::htmlComprimir($html23), true, false, false, false, '');
//   var_dump($pdf->getY());
//   exit();
   
      	if($pdf->getY()>140){
            $pdf->addPage();
            $pdf->setY(35);
         }
   
   $html1 = '
<table border="0.1" style="font-size:9px" cellpadding="3" >
<tbody>
<tr style="font-size:8px">
<td style="width: 35%;" align="center"><b>PLANIFICACIÓN, PRESUPUESTO Y CONTROL DE GESTIÓN</b></td>
<td style="width: 35%;" align="center"><b>ADMINISTRACIÓN Y FINANZAS</b></td>
<td style="width: 30%;" align="center"><b>TITULAR</b></td>
</tr>
<tr style="font-size:8px">
<td style="width: 35%;" align="center"><b>FIRMA Y SELLO</b></td>
<td style="width: 35%;" align="center"><b>FIRMA Y SELLO</b></td>
<td style="width: 30%;" align="center"><b>FIRMA Y SELLO</b></td>
</tr>
<tr style="font-size:8px">
<td style="width: 35%; height: 40px;" align="center"><b></b></td>
<td style="width: 35%; height: 40px;" align="center"><b></b></td>
<td style="width: 30%; height: 40px;" align="center"><b></b></td>
</tr>
<tr style="font-size:8px">
<td style="width: 35%;" align="center"><b>NOMBRE Y APELLIDO</b></td>
<td style="width: 35%;" align="center"><b>NOMBRE Y APELLIDO</b></td>
<td style="width: 30%;" align="center"><b>NOMBRE Y APELLIDO</b></td>
</tr>
<tr style="font-size:8px">
<td style="width: 35%; height: 40px;" align="center"><b></b></td>
<td style="width: 35%; height: 40px;" align="center"><b></b></td>
<td style="width: 30%; height: 40px;" align="center"><b></b></td>
</tr>
<tr style="font-size:8px">
<td style="width: 35%;" align="center"><b>C.I. </b></td>
<td style="width: 35%;" align="center"><b>C.I. </b></td>
<td style="width: 30%;" align="center"><b>C.I. </b></td>
</tr>
</tbody>
</table>
';
   

//          $pdf->SetX(140);
//          $pdf->writeHTML(Helper::htmlComprimir($html1), true, false, false, false, '');   
    
}else{         
         
             
//$pdf->AddPage();

         if($item3->id_ejecutor){
          $tx_sector = $item3->tx_sector;   
          $ejecutor = $item3->id_ejecutor.' - '.$data_ejecutor->tx_ejecutor_ac;
         }else{
          $tx_sector = $item3->tx_sector;
          $ejecutor = 'EJECUTOR: TODOS';
         }    
$this->encabezado6($pdf);
$html23='';
$html23.= '
<table border="0.1" style="width:100%" style="font-size:9px" cellpadding="3">
<thead>
<tr style="font-size:9px">
<td style="width: 100%;"><b>'.$ejecutor.'</b> </td>
</tr>
<tr style="font-size:7px">
<td style="width: 20%;" align="center" colspan="2"><b>PARTIDA PRESUPUESTARIA</b></td>
<td rowspan="2" style="width: 10%;" align="center"><b>PRESUPUESTO INICIAL</b></td>
<td rowspan="2" style="width: 10%;" align="center"><b>PRESUPUESTO MODIFICADO</b></td>
<td rowspan="2" style="width: 10%;" align="center"><b>PRESUPUESTO ACTUALIZADO (TOTAL)</b></td>
<td rowspan="2" style="width: 10%;" align="center"><b>COMPROMETIDO</b></td>
<td rowspan="2" style="width: 10%;" align="center"><b>CAUSADO</b></td>
<td rowspan="2" style="width: 10%;" align="center"><b>PAGADO</b></td>
<td rowspan="2" style="width: 20%;" align="center"><b>FUENTE DE FINANCIAMIENTO</b></td>
</tr>
<tr style="font-size:7px">
<td style="width: 5%;" align="center"><b>CÓDIGO</b></td>
<td style="width: 15%;" align="center"><b>DENOMINACIÓN</b></td>
</tr>
</thead>
';  
$html23.='
<tbody>';  

		$html23.='
		<tr style="font-size:7px" >
                <td  style="width: 100%;" ><b>SECTOR:</b> '.$item3->tx_sector.'</td>';
                $html23.='</tr>';   

}

            if($id_ejecutor!=null){
             $tab_meta_financiera = tab_meta_financiera::select(                  
                'ac_seguimiento.tab_meta_financiera.co_partida'
            )
            ->join('ac_seguimiento.tab_meta_fisica as t01', 'ac_seguimiento.tab_meta_financiera.id_tab_meta_fisica', '=', 't01.id')
            ->join('ac_seguimiento.tab_ac_ae as t02', 't01.id_tab_ac_ae', '=', 't02.id')
            ->join('ac_seguimiento.tab_ac as t03', 't02.id_tab_ac', '=', 't03.id')
            ->join('mantenimiento.tab_fuente_financiamiento as t06', 'ac_seguimiento.tab_meta_financiera.id_tab_fuente_financiamiento', '=', 't06.id')
            ->join('mantenimiento.tab_lapso as t07', 't03.id_tab_lapso', '=', 't07.id')
            ->join('mantenimiento.tab_partidas as t04', function ($j) {
                $j->on('t04.co_partida', '=', 'ac_seguimiento.tab_meta_financiera.co_partida')
                  ->on('t04.id_tab_ejercicio_fiscal', '=', 't03.id_tab_ejercicio_fiscal');
            })
            ->join('mantenimiento.tab_sectores as t18a', 't03.id_tab_sectores', '=', 't18a.id')
            ->join('mantenimiento.tab_sectores as t18b', function ($join) {
            $join->on('t18a.co_sector', '=', 't18b.co_sector')
            ->on('t18b.nu_nivel', '=', DB::raw('1'));
            })      
            ->where('t03.id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))
            ->where('t03.in_activo', '=', true)
            ->where('t03.id_ejecutor', '=', $id_ejecutor)
            ->where('t03.id_tab_lapso', '=', $id_tab_lapso)
            ->where('t18b.tx_codigo', '=', $item3->tx_sector)
            ->where('ac_seguimiento.tab_meta_financiera.co_partida', '=', $item3->co_partida)        
            ->groupBy('ac_seguimiento.tab_meta_financiera.co_partida')
            ->groupBy('de_fuente_financiamiento')
            ->get(); 
            
            }else{
             
             $tab_meta_financiera = tab_meta_financiera::select(                  
                'ac_seguimiento.tab_meta_financiera.co_partida'
            )
            ->join('ac_seguimiento.tab_meta_fisica as t01', 'ac_seguimiento.tab_meta_financiera.id_tab_meta_fisica', '=', 't01.id')
            ->join('ac_seguimiento.tab_ac_ae as t02', 't01.id_tab_ac_ae', '=', 't02.id')
            ->join('ac_seguimiento.tab_ac as t03', 't02.id_tab_ac', '=', 't03.id')
            ->join('mantenimiento.tab_fuente_financiamiento as t06', 'ac_seguimiento.tab_meta_financiera.id_tab_fuente_financiamiento', '=', 't06.id')
            ->join('mantenimiento.tab_lapso as t07', 't03.id_tab_lapso', '=', 't07.id')
            ->join('mantenimiento.tab_partidas as t04', function ($j) {
                $j->on('t04.co_partida', '=', 'ac_seguimiento.tab_meta_financiera.co_partida')
                  ->on('t04.id_tab_ejercicio_fiscal', '=', 't03.id_tab_ejercicio_fiscal');
            })
            ->join('mantenimiento.tab_sectores as t18a', 't03.id_tab_sectores', '=', 't18a.id')
            ->join('mantenimiento.tab_sectores as t18b', function ($join) {
            $join->on('t18a.co_sector', '=', 't18b.co_sector')
            ->on('t18b.nu_nivel', '=', DB::raw('1'));
            })      
            ->where('t03.id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))
            ->where('t03.in_activo', '=', true)
            ->where('t03.id_tab_lapso', '=', $id_tab_lapso)
            ->where('t18b.tx_codigo', '=', $item3->tx_sector)
            ->where('ac_seguimiento.tab_meta_financiera.co_partida', '=', $item3->co_partida)        
            ->groupBy('ac_seguimiento.tab_meta_financiera.co_partida')
            ->groupBy('de_fuente_financiamiento')
            ->get();                
                
            }
             if($tab_meta_financiera->count()>1){
                $i =  $tab_meta_financiera->count();
             }else{
             $i = 1;    
             }

//             var_dump($i);
//             exit();
             
             if($id_partida==$item3->co_partida){

		$html23.='
		<tr style="font-size:7px" >
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_presupuesto).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_modificado_anual).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_actualizado_anual).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_comprometido).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_causado).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_pagado).'</td>                                 
                <td style="width: 20%;" align="center">'.$item3->de_fuente_financiamiento.'</td>';
                $html23.='</tr>';
                
             }else{
                 
		$html23.='
		<tr style="font-size:7px" >
		<td style="width: 5%;" align="center" rowspan="'.$i.'">'.$item3->co_partida.'</td>
                <td style="width: 15%;" rowspan="'.$i.'">'.$item3->tx_nombre.'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_presupuesto).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_modificado_anual).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_actualizado_anual).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_comprometido).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_causado).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_pagado).'</td>                                 
                <td style="width: 20%;" align="center">'.$item3->de_fuente_financiamiento.'</td>';
                $html23.='</tr>';
                 
             }

             
             
                $mo_presupuesto = $mo_presupuesto + $item3->mo_presupuesto;
                $mo_modificado_anual = $mo_modificado_anual + $item3->mo_modificado_anual;
                $mo_actualizado_anual = $mo_actualizado_anual + $item3->mo_actualizado_anual;
                $mo_comprometido = $mo_comprometido + $item3->mo_comprometido;
                $mo_causado = $mo_causado + $item3->mo_causado;
                $mo_pagado = $mo_pagado + $item3->mo_pagado;
                $de_lapso = $dia_mes_fin;
                $id_tab_ejercicio_fiscal = $item3->id_tab_ejercicio_fiscal;

            $tx_sector = $item3->tx_sector;


         }else{
             
            if($id_ejecutor!=null){
             $tab_meta_financiera = tab_meta_financiera::select(                  
                'ac_seguimiento.tab_meta_financiera.co_partida'
            )
            ->join('ac_seguimiento.tab_meta_fisica as t01', 'ac_seguimiento.tab_meta_financiera.id_tab_meta_fisica', '=', 't01.id')
            ->join('ac_seguimiento.tab_ac_ae as t02', 't01.id_tab_ac_ae', '=', 't02.id')
            ->join('ac_seguimiento.tab_ac as t03', 't02.id_tab_ac', '=', 't03.id')
            ->join('mantenimiento.tab_fuente_financiamiento as t06', 'ac_seguimiento.tab_meta_financiera.id_tab_fuente_financiamiento', '=', 't06.id')
            ->join('mantenimiento.tab_lapso as t07', 't03.id_tab_lapso', '=', 't07.id')
            ->join('mantenimiento.tab_partidas as t04', function ($j) {
                $j->on('t04.co_partida', '=', 'ac_seguimiento.tab_meta_financiera.co_partida')
                  ->on('t04.id_tab_ejercicio_fiscal', '=', 't03.id_tab_ejercicio_fiscal');
            })
            ->join('mantenimiento.tab_sectores as t18a', 't03.id_tab_sectores', '=', 't18a.id')
            ->join('mantenimiento.tab_sectores as t18b', function ($join) {
            $join->on('t18a.co_sector', '=', 't18b.co_sector')
            ->on('t18b.nu_nivel', '=', DB::raw('1'));
            })      
            ->where('t03.id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))
            ->where('t03.in_activo', '=', true)
            ->where('t03.id_ejecutor', '=', $id_ejecutor)
            ->where('t03.id_tab_lapso', '=', $id_tab_lapso)
            ->where('t18b.tx_codigo', '=', $item3->tx_sector)
            ->where('ac_seguimiento.tab_meta_financiera.co_partida', '=', $item3->co_partida)        
            ->groupBy('ac_seguimiento.tab_meta_financiera.co_partida')
            ->groupBy('de_fuente_financiamiento')
            ->get(); 
            
            }else{
             
             $tab_meta_financiera = tab_meta_financiera::select(                  
                'ac_seguimiento.tab_meta_financiera.co_partida'
            )
            ->join('ac_seguimiento.tab_meta_fisica as t01', 'ac_seguimiento.tab_meta_financiera.id_tab_meta_fisica', '=', 't01.id')
            ->join('ac_seguimiento.tab_ac_ae as t02', 't01.id_tab_ac_ae', '=', 't02.id')
            ->join('ac_seguimiento.tab_ac as t03', 't02.id_tab_ac', '=', 't03.id')
            ->join('mantenimiento.tab_fuente_financiamiento as t06', 'ac_seguimiento.tab_meta_financiera.id_tab_fuente_financiamiento', '=', 't06.id')
            ->join('mantenimiento.tab_lapso as t07', 't03.id_tab_lapso', '=', 't07.id')
            ->join('mantenimiento.tab_partidas as t04', function ($j) {
                $j->on('t04.co_partida', '=', 'ac_seguimiento.tab_meta_financiera.co_partida')
                  ->on('t04.id_tab_ejercicio_fiscal', '=', 't03.id_tab_ejercicio_fiscal');
            })
            ->join('mantenimiento.tab_sectores as t18a', 't03.id_tab_sectores', '=', 't18a.id')
            ->join('mantenimiento.tab_sectores as t18b', function ($join) {
            $join->on('t18a.co_sector', '=', 't18b.co_sector')
            ->on('t18b.nu_nivel', '=', DB::raw('1'));
            })      
            ->where('t03.id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))
            ->where('t03.in_activo', '=', true)
            ->where('t03.id_tab_lapso', '=', $id_tab_lapso)
            ->where('t18b.tx_codigo', '=', $item3->tx_sector)
            ->where('ac_seguimiento.tab_meta_financiera.co_partida', '=', $item3->co_partida)        
            ->groupBy('ac_seguimiento.tab_meta_financiera.co_partida')
            ->groupBy('de_fuente_financiamiento')
            ->get();                
                
            }              
             if($tab_meta_financiera->count()>1){
                $i =  $tab_meta_financiera->count();
             }else{
             $i = 1;    
             }

//             var_dump($i);
//             exit();
             
             if($id_partida==$item3->co_partida){

		$html23.='
		<tr style="font-size:7px" >
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_presupuesto).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_modificado_anual).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_actualizado_anual).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_comprometido).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_causado).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_pagado).'</td>                                 
                <td style="width: 20%;" align="center">'.$item3->de_fuente_financiamiento.'</td>';
                $html23.='</tr>';
                
             }else{
                 
		$html23.='
		<tr style="font-size:7px" >
		<td style="width: 5%;" align="center" rowspan="'.$i.'">'.$item3->co_partida.'</td>
                <td style="width: 15%;" rowspan="'.$i.'">'.$item3->tx_nombre.'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_presupuesto).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_modificado_anual).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_actualizado_anual).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_comprometido).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_causado).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_pagado).'</td>                                 
                <td style="width: 20%;" align="center">'.$item3->de_fuente_financiamiento.'</td>';
                $html23.='</tr>';
                 
             }

                $mo_presupuesto = $mo_presupuesto + $item3->mo_presupuesto;
                $mo_modificado_anual = $mo_modificado_anual + $item3->mo_modificado_anual;
                $mo_actualizado_anual = $mo_actualizado_anual + $item3->mo_actualizado_anual;
                $mo_comprometido = $mo_comprometido + $item3->mo_comprometido;
                $mo_causado = $mo_causado + $item3->mo_causado;
                $mo_pagado = $mo_pagado + $item3->mo_pagado;
                $de_lapso = $dia_mes_fin;
                $id_tab_ejercicio_fiscal = $item3->id_tab_ejercicio_fiscal;            
         }

        $id_partida =$item3->co_partida;
         
     }
                  


		$html23.='
		<tr style="font-size:7px" >
                <td style="width: 20%;" colspan="2" align="right"> TOTAL EJECUTADO AL '.$de_lapso.' '.$id_tab_ejercicio_fiscal.' </td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($mo_presupuesto).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($mo_modificado_anual).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($mo_actualizado_anual).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($mo_comprometido).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($mo_causado).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($mo_pagado).'</td>
                <td style="width: 20%;" align="right"></td>';
                $html23.='</tr>';

   $html23.='
</tbody>
</table>';  
   
             $pdf->writeHTML(Helper::htmlComprimir($html23), true, false, false, false, '');
   
      	if($pdf->getY()>140){
            $pdf->addPage();
            $pdf->setY(35);
         }
   
         
         if($data_responsables==''){
   $html1 = '
<table border="0.1" style="font-size:9px" cellpadding="3" >
<tbody>
<tr style="font-size:8px">
<td style="width: 35%;" align="center"><b>PLANIFICACIÓN, PRESUPUESTO Y CONTROL DE GESTIÓN</b></td>
<td style="width: 35%;" align="center"><b>ADMINISTRACIÓN Y FINANZAS</b></td>
<td style="width: 30%;" align="center"><b>TITULAR</b></td>
</tr>
<tr style="font-size:8px">
<td style="width: 35%;" align="center"><b>FIRMA Y SELLO</b></td>
<td style="width: 35%;" align="center"><b>FIRMA Y SELLO</b></td>
<td style="width: 30%;" align="center"><b>FIRMA Y SELLO</b></td>
</tr>
<tr style="font-size:8px">
<td style="width: 35%; height: 40px;" align="center"><b></b></td>
<td style="width: 35%; height: 40px;" align="center"><b></b></td>
<td style="width: 30%; height: 40px;" align="center"><b></b></td>
</tr>
<tr style="font-size:8px">
<td style="width: 35%;" align="center"><b>NOMBRE Y APELLIDO</b></td>
<td style="width: 35%;" align="center"><b>NOMBRE Y APELLIDO</b></td>
<td style="width: 30%;" align="center"><b>NOMBRE Y APELLIDO</b></td>
</tr>
<tr style="font-size:8px">
<td style="width: 35%; height: 40px;" align="center"><b></b></td>
<td style="width: 35%; height: 40px;" align="center"><b></b></td>
<td style="width: 30%; height: 40px;" align="center"><b></b></td>
</tr>
<tr style="font-size:8px">
<td style="width: 35%;" align="center"><b>C.I. </b></td>
<td style="width: 35%;" align="center"><b>C.I. </b></td>
<td style="width: 30%;" align="center"><b>C.I. </b></td>
</tr>
</tbody>
</table>
';
         }else{
             
   $html1 = '
<table border="0.1" style="font-size:9px" cellpadding="3" >
<tbody>
<tr style="font-size:8px">
<td style="width: 35%;" align="center"><b>PLANIFICACIÓN, PRESUPUESTO Y CONTROL DE GESTIÓN</b></td>
<td style="width: 35%;" align="center"><b>ADMINISTRACIÓN Y FINANZAS</b></td>
<td style="width: 30%;" align="center"><b>TITULAR</b></td>
</tr>
<tr style="font-size:8px">
<td style="width: 35%;" align="center"><b>FIRMA Y SELLO</b></td>
<td style="width: 35%;" align="center"><b>FIRMA Y SELLO</b></td>
<td style="width: 30%;" align="center"><b>FIRMA Y SELLO</b></td>
</tr>
<tr style="font-size:8px">
<td style="width: 35%; height: 40px;" align="center"><b></b></td>
<td style="width: 35%; height: 40px;" align="center"><b></b></td>
<td style="width: 30%; height: 40px;" align="center"><b></b></td>
</tr>
<tr style="font-size:8px">
<td style="width: 35%;" align="center"><b>NOMBRE Y APELLIDO</b></td>
<td style="width: 35%;" align="center"><b>NOMBRE Y APELLIDO</b></td>
<td style="width: 30%;" align="center"><b>NOMBRE Y APELLIDO</b></td>
</tr>
<tr style="font-size:8px">
<td style="width: 35%; height: 20px;" align="center">'.$data_responsables->realizador_nombres.'<b></b></td>
<td style="width: 35%; height: 20px;" align="center">'.$data_responsables->registrador_nombres.'<b></b></td>
<td style="width: 30%; height: 20px;" align="center">'.$data_responsables->autorizador_nombres.'<b></b></td>
</tr>
<tr style="font-size:8px">
<td style="width: 35%;" align="center"><b>C.I. '.$data_responsables->realizador_cedula.'</b></td>
<td style="width: 35%;" align="center"><b>C.I. '.$data_responsables->registrador_cedula.'</b></td>
<td style="width: 30%;" align="center"><b>C.I. '.$data_responsables->autorizador_cedula.'</b></td>
</tr>
</tbody>
</table>
';             
             
         }
   

          $pdf->SetX(140);
          $pdf->writeHTML(Helper::htmlComprimir($html1), true, false, false, false, '');      
      
      
           
            }   

          $pdf->SetX(140);
          
          $pdf->output('SEGUIMIENTO_AC_'.Session::get("ejercicio").'_'.date("H:i:s").'.pdf', 'D');
      }      
      
      public function fichaConsolidadoPrimero($id_tab_lapso,$id_tipo_ejecutor=null)
      {
          ini_set('max_execution_time', 3600); 
        $data_lapso = tab_lapso::select(
            'id_tab_tipo_periodo'
        )
        ->where('id', '=', $id_tab_lapso)
        ->first();          

      
                if($data_lapso->id_tab_tipo_periodo==19){
                    
                    $periodo = '1TA/'.Session::get("ejercicio");
                    $dia_mes_fin = '31-03-';
                }
                
                if($data_lapso->id_tab_tipo_periodo==20){
                    
                    $periodo = '2TA/'.Session::get("ejercicio"); 
                    $dia_mes_fin = '30-06-';
                }

                if($data_lapso->id_tab_tipo_periodo==21){
                    
                    $periodo = '3TA/'.Session::get("ejercicio");
                    $dia_mes_fin = '30-09-';
                }

                if($data_lapso->id_tab_tipo_periodo==22){
                    
                    $periodo = '4TA/'.Session::get("ejercicio");
                    $dia_mes_fin = '31-12-';
                }        

                Session::put('periodo',$periodo); 

                
            $data = tab_ac::join('mantenimiento.tab_ejecutores as t04', 't04.id_ejecutor', '=', 'ac_seguimiento.tab_ac.id_ejecutor')
            ->leftjoin('ac_seguimiento.tab_ac_vinculo as t49', 't49.id_tab_ac', '=', 'ac_seguimiento.tab_ac.id')
            ->leftjoin('t45_planes_zulia as t45', function ($join) {
            $join->on('t49.co_area_estrategica', '=', 't45.co_area_estrategica')
            ->on('t45.nu_nivel', '=', DB::raw('0'));
            })
            ->leftjoin('t45_planes_zulia as t45a', function ($join) {
            $join->on('t49.co_area_estrategica', '=', 't45a.co_area_estrategica')
            ->on('t49.co_ambito_estado', '=', 't45a.co_ambito_zulia')        
            ->on('t45a.nu_nivel', '=', DB::raw('1'));
            })            
            ->select(
            'ac_seguimiento.tab_ac.id_ejecutor'
            )
//            ->where('ac_seguimiento.tab_ac.id_ejecutor', '=', '0002')
            ->where('ac_seguimiento.tab_ac.in_activo', '=', true)
            ->where('ac_seguimiento.tab_ac.in_abierta', '=', false)
            ->where('t04.id_tab_tipo_ejecutor', '=', $id_tipo_ejecutor)
            ->where('ac_seguimiento.tab_ac.id_tab_ejercicio_fiscal', '=', Session::get("ejercicio"))
            ->groupBy('tab_ac.id_ejecutor')
            ->groupBy('t45.co_area_estrategica')
            ->groupBy('t45a.nu_orden')
            ->orderby('t45.co_area_estrategica', 'ASC')->orderby('t45a.nu_orden', 'ASC')->orderby('ac_seguimiento.tab_ac.id_ejecutor', 'ASC')->get();   
            
//var_dump($data);
//exit();
      
 
        
          $pdf = new PDFseguimientoAC("L", PDF_UNIT, 'Letter', true, 'UTF-8', false);
          $pdf->SetCreator('Sistema POA, Yoser Perez');
          $pdf->SetAuthor('Yoser Perez');
          $pdf->SetTitle('Seguimiento AC');
          $pdf->SetSubject('Seguimiento AC');
          $pdf->SetKeywords('Seguimiento AC, PDF, Zulia, SPE, '.Session::get("ejercicio").'');
          $pdf->SetMargins(10,10,10);
          $pdf->SetTopMargin(35);
          $pdf->SetPrintHeader(true);
          $pdf->SetPrintFooter(true);
          // set auto page breaks
          $pdf->SetAutoPageBreak(true, 10);
          $pdf->SetFont('','',11);
 
          $pdf->Ln(-3);


            foreach($data as $itemData) {
                
                $data_ejecutor = tab_ac::select(
                'tx_ejecutor_ac',
                't45.tx_descripcion as tx_area_estrategica',        
                'ac_seguimiento.tab_ac.inst_mision',
                'ac_seguimiento.tab_ac.inst_vision',
                'ac_seguimiento.tab_ac.inst_objetivos',
                'id_tab_tipo_periodo'
                )
                ->join('mantenimiento.tab_lapso as t02', 'ac_seguimiento.tab_ac.id_tab_lapso', '=', 't02.id')   
                ->leftjoin('ac_seguimiento.tab_ac_vinculo as t49', 't49.id_tab_ac', '=', 'ac_seguimiento.tab_ac.id')
                ->leftjoin('t45_planes_zulia as t45', function ($join) {
                $join->on('t49.co_area_estrategica', '=', 't45.co_area_estrategica')
                ->on('t45.nu_nivel', '=', DB::raw('0'));
                })                        
                ->where('id_ejecutor', '=', $itemData->id_ejecutor)
                ->where('ac_seguimiento.tab_ac.id_tab_ejercicio_fiscal', '=', Session::get("ejercicio"))
                ->where('id_tab_lapso', '=', $id_tab_lapso)
                ->first();                
                
                
                if($data_ejecutor){
                
                if($data_ejecutor->id_tab_tipo_periodo==19){
                    
                    $mes = 'Marzo';
                }
                
                if($data_ejecutor->id_tab_tipo_periodo==20){
                    
                    $mes = 'Junio';
                }

                if($data_ejecutor->id_tab_tipo_periodo==21){
                    
                    $mes = 'Septiembre';
                }

                if($data_ejecutor->id_tab_tipo_periodo==22){
                    
                    $mes = 'Diciembre';
                }                
            
                $pdf->AddPage();
                $pdf->SetFont('','B',12);
            $pdf->setY(10);
            $pdf->MultiCell(277, 5, 'REPÚBLICA BOLIVARIANA DE VENEZUELA', 0, 'C', 0, 0, '', '', true);
            $pdf->Ln(10);        
            $pdf->MultiCell(277, 5, 'GOBERNACIÓN DEL ESTADO ZULIA', 0, 'C', 0, 0, '', '', true);
            $pdf->Ln(5);                
            $pdf->SetY(75);
            $pdf->SetFont('','B',18);
            $pdf->SetTextColor(0,0,0);
            $pdf->Write(0, 'SISTEMA DE SEGUIMIENTO, EVALUACIÓN Y CONTROL DEL PLAN OPERATIVO ESTADAL', '', 0, 'C', true, 0, false, false, 0);
            $pdf->Ln(5);
            $pdf->Write(0, 'AÑO '.Session::get("ejercicio"), '', 0, 'C', true, 0, false, false, 0);
            $pdf->Ln(10);
            $pdf->Write(0, $data_ejecutor->tx_ejecutor_ac, '', 0, 'C', true, 0, false, false, 0);
            $pdf->SetY(190);
            $pdf->SetFont('','',11);            
            $pdf->Write(0, 'Maracaibo, '.$mes.' de '.Session::get("ejercicio"), '', 0, 'C', true, 0, false, false, 0);
            $pdf->AddPage();
            
            $pdf->setY(10);
            $pdf->MultiCell(277, 5, 'SISTEMA DE SEGUIMIENTO, EVALUACIÓN Y CONTROL DEL PLAN OPERATIVO ESTADAL', 0, 'C', 0, 0, '', '', true);
            $pdf->Ln(5);
            $pdf->MultiCell(277, 5, 'FORMULARIO Nº 1', 0, 'C', 0, 0, '', '', true);
            $pdf->Ln(5);
            $pdf->MultiCell(277, 5, 'MARCO NORMATIVO INSTITUCIONAL', 0, 'C', 0, 0, '', '', true);
            $pdf->Ln(10);            
            
            $htmlObjetivo = '
    <table border="0.1" style="width:100%;text-align: center;" cellpadding="3">
            <tr align="left">
                    <td colspan="2"><b>1.2. UNIDAD EJECUTORA RESPONSABLE: </b>'.$data_ejecutor->tx_ejecutor_ac.'</td>
            </tr>
            <tr align="left">
                    <td colspan="2"><b>2.5.1. AREA ESTRATEGICA: </b>'.$data_ejecutor->tx_area_estrategica.'</td>
            </tr>
            <tr>
                    <td><b>MISIÓN</b></td>
                    <td><b>VISIÓN</b></td>
            </tr>
            <tr>
                    <td height="100" align="justify">'.$data_ejecutor->inst_mision.'</td>
                    <td height="100" align="justify">'.$data_ejecutor->inst_vision.'</td>
            </tr>
    <thead>
            <tr>
                    <td colspan="2"><b>OBJETIVOS INSTITUCIONALES</b></td>
            </tr>
    </thead>
    <tbody>
            <tr nobr="true">
                    <td colspan="2" height="100" align="justify">'.str_replace(array("\r\n","\r","\n","\\r","\\n","\\r\\n"),"<br/>",$data_ejecutor->inst_objetivos).'</td>
            </tr>
    </tbody>
    </table>';            
           $pdf->writeHTML(Helper::htmlComprimir($htmlObjetivo), true, false, false, false, '');

           
           
            $data2 = tab_ac::join('mantenimiento.tab_ejecutores as t04', 't04.id_ejecutor', '=', 'ac_seguimiento.tab_ac.id_ejecutor')
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
            'id_tab_tipo_periodo',
            'ac_seguimiento.tab_ac.id_tab_ejercicio_fiscal',
            'ac_seguimiento.tab_ac.de_observacion_002',
            'ac_seguimiento.tab_ac.de_observacion_003',
            'ac_seguimiento.tab_ac.id',
            'ac_seguimiento.tab_ac.de_sector',
            't21.id_tab_ac_ae_predefinida'                    
        )
        ->where('ac_seguimiento.tab_ac.id_ejecutor', '=', $itemData->id_ejecutor)
        ->where('ac_seguimiento.tab_ac.id_tab_lapso', '=', $id_tab_lapso)
        ->where('ac_seguimiento.tab_ac.in_activo', '=', true)
        ->orderby('ac_seguimiento.tab_ac.id_ejecutor', 'ASC')->orderby('ac_seguimiento.tab_ac.id_tab_ac_predefinida', 'ASC')->orderby('t21.id_tab_ac_ae_predefinida', 'ASC')
        ->get(); 
            
          foreach($data2 as $data) {
              
           $pdf->AddPage();
           
           $this->encabezado2($pdf);
           
           
            $actividad = tab_meta_fisica::select('codigo','nb_meta','tx_prog_anual','fecha_inicio','fecha_fin',
            'tab_meta_fisica.nb_responsable','de_unidad_medida as tx_unidades_medida',DB::raw('coalesce(tab_meta_fisica.nu_meta_modificada,0) as nu_meta_modificada'),'de_municipio','de_parroquia','tab_meta_fisica.resultado','tab_meta_fisica.observacion',
            DB::raw('coalesce(tab_meta_fisica.nu_meta_actualizada,0) as nu_meta_actualizada'),DB::raw('coalesce(tab_meta_fisica.nu_obtenido,0) as nu_obtenido'))
            ->join('mantenimiento.tab_unidad_medida as t21', 'tab_meta_fisica.id_tab_unidad_medida', '=', 't21.id')
            ->leftjoin('ac_seguimiento.tab_forma_002 as t002', 'tab_meta_fisica.id', '=', 't002.id_tab_meta_fisica')
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
 
                $data20 = tab_ac::select(
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

              $obtenido = ($data20->nu_obtenido/($item->tx_prog_anual + $data20->nu_meta_modificada))*100;   
            }          
          
$contar=$contar+1;
		$html23.='
		<tr style="font-size:6px" nobr="true">
		<td style="width: 18%;"  nobr="true">'.$item->codigo.' - '.$item->nb_meta.'</td>
		<td style="width: 7%;"  align="center">'.$item->tx_unidades_medida.'</td>
		<td style="width: 8%;"  align="center">'.$item->tx_prog_anual.'</td>
                <td style="width: 7%;" align="center">'.$data20->nu_meta_modificada.'</td>
                <td style="width: 8%;" align="center">'.($item->tx_prog_anual + $data20->nu_meta_modificada).'</td>                    
		<td style="width: 8%;"  align="center">'.trim(date_format(date_create($item->fecha_inicio),'d/m/Y')).'</td>
		<td style="width: 8%;" align="center">'.trim(date_format(date_create($item->fecha_fin),'d/m/Y')).'</td>
                <td style="width: 8%;" align="center">'.$this->formatoDinero($data20->nu_obtenido).'</td>
                <td style="width: 9%;" align="center">'.$this->formatoPorcentaje($obtenido).'</td>
                <td style="width: 10%;"  align="center">'.$item->de_municipio.' / '.$item->de_parroquia.'</td>
		<td style="width: 9%;" align="center">'.$item->nb_responsable.'</td>';
                $html23.='</tr>';

                
          
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

          $pdf->SetFont('','',11);
          $pdf->writeHTML(Helper::htmlComprimir($html1), true, false, false, false, '');
          $pdf->Ln(-3);
          $pdf->writeHTML($html23, true, false, false, false, '');
          
          }

          foreach($data2 as $data) {
          
                $mo_presupuesto_anual_accion = 0;
                $mo_modificado_anual_accion = 0;
                $mo_actualizado_anual_accion = 0;
                $mo_comprometido_accion = 0;
                $mo_causado_accion = 0;
                $mo_pagado_accion = 0; 
                
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
            

                
                $mo_presupuesto_anual_ejecutor = 0;
                $mo_modificado_anual_ejecutor = 0;
                $mo_actualizado_anual_ejecutor = 0;
                $mo_comprometido_ejecutor = 0;
                $mo_causado_ejecutor = 0;
                $mo_pagado_ejecutor = 0;                
                
      foreach($actividad_accion as $item1) {
          
                $data3 = tab_ac::select(
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
                $mo_comprometido_accion = $data3->mo_comprometido;
                $mo_causado_accion = $data3->mo_causado;
                $mo_pagado_accion = $data3->mo_pagado;

      } 
      
      foreach($actividad_ejecutor as $item2) {
          
                $data3 = tab_ac::select(
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
                $mo_comprometido_ejecutor = $data3->mo_comprometido;
                $mo_causado_ejecutor = $data3->mo_causado;
                $mo_pagado_ejecutor = $data3->mo_pagado;

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
          
                $data4 = tab_ac::select(
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
                ->where('t03.co_partida', '=', $item->co_partida)
                ->where('t01.id_tab_ac_ae_predefinida', '=', $data->id_tab_ac_ae_predefinida)
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
                <td style="width: 9%;" align="center">'.$this->formatoDinero($data4->mo_comprometido).'</td>                    
		<td style="width: 9%;"  align="center">'.$this->formatoDinero($data4->mo_causado).'</td>
		<td style="width: 9%;" align="center">'.$this->formatoDinero($data4->mo_pagado).'</td>
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
                <td style="width: 9%;" align="center">'.$this->formatoDinero($data4->mo_comprometido).'</td>                    
		<td style="width: 9%;"  align="center">'.$this->formatoDinero($data4->mo_causado).'</td>
		<td style="width: 9%;" align="center">'.$this->formatoDinero($data4->mo_pagado).'</td>
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
                $mo_comprometido = $mo_comprometido + $data4->mo_comprometido;
                $mo_causado = $mo_causado + $data4->mo_causado;
                $mo_pagado = $mo_pagado + $data4->mo_pagado;
                

          
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
          $this->encabezado3($pdf);

          $pdf->SetFont('','',11);
//          $pdf->writeHTML($htmlObjetivo, true, false, false, false, '');
          $pdf->writeHTML(Helper::htmlComprimir($html1), true, false, false, false, '');
          $pdf->Ln(-3);
          $pdf->writeHTML($html23, true, false, false, false, '');       
          }



            $tab_lapso = tab_lapso::where('id', '<=', $id_tab_lapso)
            ->where('id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))
            ->get();  
             
              $i =  $tab_lapso->count();
      
      
              $data3 =  tab_meta_financiera::select(
                'tx_nombre',
                DB::raw('sum(coalesce(mo_presupuesto,0))/'.$i.' as mo_presupuesto'),
                DB::raw('sum(coalesce(mo_modificado_anual,0)) as mo_modificado_anual'),
                DB::raw('sum(coalesce(mo_presupuesto,0))/'.$i.' + sum(coalesce(mo_modificado_anual,0)) as mo_actualizado_anual'),
                DB::raw('sum(coalesce(mo_comprometido,0)) as mo_comprometido'),
                DB::raw('sum(coalesce(mo_causado,0)) as mo_causado'),
                DB::raw('sum(coalesce(mo_pagado,0)) as mo_pagado'),                   
                'ac_seguimiento.tab_meta_financiera.co_partida',
                't03.id_ejecutor',
                't18b.tx_codigo as tx_sector',
                'de_fuente_financiamiento',
                't03.id_tab_ejercicio_fiscal'
            )
            ->join('ac_seguimiento.tab_meta_fisica as t01', 'ac_seguimiento.tab_meta_financiera.id_tab_meta_fisica', '=', 't01.id')
            ->join('ac_seguimiento.tab_ac_ae as t02', 't01.id_tab_ac_ae', '=', 't02.id')
            ->join('ac_seguimiento.tab_ac as t03', 't02.id_tab_ac', '=', 't03.id')
            ->join('mantenimiento.tab_ejecutores as t05', 't05.id_ejecutor', '=', 't03.id_ejecutor')
            ->join('mantenimiento.tab_fuente_financiamiento as t06', 'ac_seguimiento.tab_meta_financiera.id_tab_fuente_financiamiento', '=', 't06.id')  
            ->join('mantenimiento.tab_lapso as t07', 't03.id_tab_lapso', '=', 't07.id')
            ->join('mantenimiento.tab_tipo_periodo as t08', 't07.id_tab_tipo_periodo', '=', 't08.id')        
            ->join('mantenimiento.tab_partidas as t04', function ($j) {
                $j->on('t04.co_partida', '=', 'ac_seguimiento.tab_meta_financiera.co_partida')
                  ->on('t04.id_tab_ejercicio_fiscal', '=', 't03.id_tab_ejercicio_fiscal');
            })
            ->join('mantenimiento.tab_sectores as t18a', 't03.id_tab_sectores', '=', 't18a.id')
            ->join('mantenimiento.tab_sectores as t18b', function ($join) {
            $join->on('t18a.co_sector', '=', 't18b.co_sector')
            ->on('t18b.nu_nivel', '=', DB::raw('1'));
            })             
            ->where('t03.id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))
            ->where('t03.in_activo', '=', true) 
            ->where('t03.id_ejecutor', '=', $itemData->id_ejecutor)
            ->where('t03.id_tab_lapso', '<=', $id_tab_lapso)
            ->groupBy('ac_seguimiento.tab_meta_financiera.co_partida')
            ->groupBy('t03.id_ejecutor')
            ->groupBy('tx_sector')
            ->groupBy('tx_nombre')
            ->groupBy('de_fuente_financiamiento')
            ->groupBy('t03.id_tab_ejercicio_fiscal')
            ->orderby('tx_sector', 'ASC')->orderby('ac_seguimiento.tab_meta_financiera.co_partida', 'ASC')->get();    
            
            
            $data_responsables = tab_ac::join('ac_seguimiento.tab_ac_responsable as t01', 't01.id_tab_ac', '=', 'ac_seguimiento.tab_ac.id')                    
            ->select(
            'realizador_nombres',
            'registrador_nombres',
            'autorizador_nombres',
            'realizador_cedula',
            'registrador_cedula',
            'autorizador_cedula'
        )
        ->where('ac_seguimiento.tab_ac.id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))
        ->where('ac_seguimiento.tab_ac.id_tab_lapso', '=', $id_tab_lapso)
        ->where('ac_seguimiento.tab_ac.id_ejecutor', '=', $itemData->id_ejecutor)
        ->first();    
      
      
                 $mo_presupuesto = 0;
                $mo_modificado_anual = 0;
                $mo_actualizado_anual = 0;
                $mo_comprometido = 0;
                $mo_causado = 0;
                $mo_pagado = 0;          
                $id_partida = 0;
                $tx_sector = '';
                $pdf->AddPage();

foreach($data3 as $item3) {
    
         
                $de_lapso = $dia_mes_fin;
                $id_tab_ejercicio_fiscal = $item3->id_tab_ejercicio_fiscal;
         
         if($tx_sector!=$item3->tx_sector){
             
          $id_partida = 0;
          
                if($tx_sector!=''){
    
//		$html23.='
//		<tr style="font-size:7px" >
//                <td style="width: 20%;" colspan="2" align="right"> TOTAL EJECUTADO AL '.$de_lapso.' '.$id_tab_ejercicio_fiscal.' </td>
//                <td style="width: 10%;" align="right">'.$this->formatoDinero($mo_presupuesto).'</td>
//                <td style="width: 10%;" align="right">'.$this->formatoDinero($mo_modificado_anual).'</td>
//                <td style="width: 10%;" align="right">'.$this->formatoDinero($mo_actualizado_anual).'</td>
//                <td style="width: 10%;" align="right">'.$this->formatoDinero($mo_comprometido).'</td>
//                <td style="width: 10%;" align="right">'.$this->formatoDinero($mo_causado).'</td>
//                <td style="width: 10%;" align="right">'.$this->formatoDinero($mo_pagado).'</td>';
//                $html23.='</tr>';
                
//                $mo_presupuesto = 0;
//                $mo_modificado_anual = 0;
//                $mo_actualizado_anual = 0;
//                $mo_comprometido = 0;
//                $mo_causado = 0;
//                $mo_pagado = 0;  

//   $html23.='
//</tbody>
//</table>';  
  
		$html23.='
		<tr style="font-size:7px" >
                <td  style="width: 100%;" ><b>SECTOR:</b> '.$item3->tx_sector.'</td>';
                $html23.='</tr>';                    
               
//             $pdf->writeHTML(Helper::htmlComprimir($html23), true, false, false, false, '');
//   var_dump($pdf->getY());
//   exit();
   
      	if($pdf->getY()>140){
            $pdf->addPage();
            $pdf->setY(35);
         }
   
   $html1 = '
<table border="0.1" style="font-size:9px" cellpadding="3" >
<tbody>
<tr style="font-size:8px">
<td style="width: 35%;" align="center"><b>PLANIFICACIÓN, PRESUPUESTO Y CONTROL DE GESTIÓN</b></td>
<td style="width: 35%;" align="center"><b>ADMINISTRACIÓN Y FINANZAS</b></td>
<td style="width: 30%;" align="center"><b>TITULAR</b></td>
</tr>
<tr style="font-size:8px">
<td style="width: 35%;" align="center"><b>FIRMA Y SELLO</b></td>
<td style="width: 35%;" align="center"><b>FIRMA Y SELLO</b></td>
<td style="width: 30%;" align="center"><b>FIRMA Y SELLO</b></td>
</tr>
<tr style="font-size:8px">
<td style="width: 35%; height: 40px;" align="center"><b></b></td>
<td style="width: 35%; height: 40px;" align="center"><b></b></td>
<td style="width: 30%; height: 40px;" align="center"><b></b></td>
</tr>
<tr style="font-size:8px">
<td style="width: 35%;" align="center"><b>NOMBRE Y APELLIDO</b></td>
<td style="width: 35%;" align="center"><b>NOMBRE Y APELLIDO</b></td>
<td style="width: 30%;" align="center"><b>NOMBRE Y APELLIDO</b></td>
</tr>
<tr style="font-size:8px">
<td style="width: 35%; height: 40px;" align="center"><b></b></td>
<td style="width: 35%; height: 40px;" align="center"><b></b></td>
<td style="width: 30%; height: 40px;" align="center"><b></b></td>
</tr>
<tr style="font-size:8px">
<td style="width: 35%;" align="center"><b>C.I. </b></td>
<td style="width: 35%;" align="center"><b>C.I. </b></td>
<td style="width: 30%;" align="center"><b>C.I. </b></td>
</tr>
</tbody>
</table>
';
   

//          $pdf->SetX(140);
//          $pdf->writeHTML(Helper::htmlComprimir($html1), true, false, false, false, '');   
    
}else{         
         
             
//$pdf->AddPage();

         if($item3->id_ejecutor){
          $tx_sector = $item3->tx_sector;   
          $ejecutor = $item3->id_ejecutor.' - '.$data_ejecutor->tx_ejecutor_ac;
         }else{
          $tx_sector = $item3->tx_sector;
          $ejecutor = 'EJECUTOR: TODOS';
         }    
$this->encabezado6($pdf);
$html23='';
$html23.= '
<table border="0.1" style="width:100%" style="font-size:9px" cellpadding="3">
<thead>
<tr style="font-size:9px">
<td style="width: 100%;"><b>'.$ejecutor.'</b> </td>
</tr>
<tr style="font-size:7px">
<td style="width: 20%;" align="center" colspan="2"><b>PARTIDA PRESUPUESTARIA</b></td>
<td rowspan="2" style="width: 10%;" align="center"><b>PRESUPUESTO INICIAL</b></td>
<td rowspan="2" style="width: 10%;" align="center"><b>PRESUPUESTO MODIFICADO</b></td>
<td rowspan="2" style="width: 10%;" align="center"><b>PRESUPUESTO ACTUALIZADO (TOTAL)</b></td>
<td rowspan="2" style="width: 10%;" align="center"><b>COMPROMETIDO</b></td>
<td rowspan="2" style="width: 10%;" align="center"><b>CAUSADO</b></td>
<td rowspan="2" style="width: 10%;" align="center"><b>PAGADO</b></td>
<td rowspan="2" style="width: 20%;" align="center"><b>FUENTE DE FINANCIAMIENTO</b></td>
</tr>
<tr style="font-size:7px">
<td style="width: 5%;" align="center"><b>CÓDIGO</b></td>
<td style="width: 15%;" align="center"><b>DENOMINACIÓN</b></td>
</tr>
</thead>
';  
$html23.='
<tbody>';  

		$html23.='
		<tr style="font-size:7px" >
                <td  style="width: 100%;" ><b>SECTOR:</b> '.$item3->tx_sector.'</td>';
                $html23.='</tr>';   

}

            if($itemData->id_ejecutor!=null){
             $tab_meta_financiera = tab_meta_financiera::select(                  
                'ac_seguimiento.tab_meta_financiera.co_partida'
            )
            ->join('ac_seguimiento.tab_meta_fisica as t01', 'ac_seguimiento.tab_meta_financiera.id_tab_meta_fisica', '=', 't01.id')
            ->join('ac_seguimiento.tab_ac_ae as t02', 't01.id_tab_ac_ae', '=', 't02.id')
            ->join('ac_seguimiento.tab_ac as t03', 't02.id_tab_ac', '=', 't03.id')
            ->join('mantenimiento.tab_fuente_financiamiento as t06', 'ac_seguimiento.tab_meta_financiera.id_tab_fuente_financiamiento', '=', 't06.id')
            ->join('mantenimiento.tab_lapso as t07', 't03.id_tab_lapso', '=', 't07.id')
            ->join('mantenimiento.tab_partidas as t04', function ($j) {
                $j->on('t04.co_partida', '=', 'ac_seguimiento.tab_meta_financiera.co_partida')
                  ->on('t04.id_tab_ejercicio_fiscal', '=', 't03.id_tab_ejercicio_fiscal');
            })
            ->join('mantenimiento.tab_sectores as t18a', 't03.id_tab_sectores', '=', 't18a.id')
            ->join('mantenimiento.tab_sectores as t18b', function ($join) {
            $join->on('t18a.co_sector', '=', 't18b.co_sector')
            ->on('t18b.nu_nivel', '=', DB::raw('1'));
            })      
            ->where('t03.id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))
            ->where('t03.in_activo', '=', true)
            ->where('t03.id_ejecutor', '=', $itemData->id_ejecutor)
            ->where('t03.id_tab_lapso', '=', $id_tab_lapso)
            ->where('t18b.tx_codigo', '=', $item3->tx_sector)
            ->where('ac_seguimiento.tab_meta_financiera.co_partida', '=', $item3->co_partida)        
            ->groupBy('ac_seguimiento.tab_meta_financiera.co_partida')
            ->groupBy('de_fuente_financiamiento')
            ->get(); 
            
            }else{
             
             $tab_meta_financiera = tab_meta_financiera::select(                  
                'ac_seguimiento.tab_meta_financiera.co_partida'
            )
            ->join('ac_seguimiento.tab_meta_fisica as t01', 'ac_seguimiento.tab_meta_financiera.id_tab_meta_fisica', '=', 't01.id')
            ->join('ac_seguimiento.tab_ac_ae as t02', 't01.id_tab_ac_ae', '=', 't02.id')
            ->join('ac_seguimiento.tab_ac as t03', 't02.id_tab_ac', '=', 't03.id')
            ->join('mantenimiento.tab_fuente_financiamiento as t06', 'ac_seguimiento.tab_meta_financiera.id_tab_fuente_financiamiento', '=', 't06.id')
            ->join('mantenimiento.tab_lapso as t07', 't03.id_tab_lapso', '=', 't07.id')
            ->join('mantenimiento.tab_partidas as t04', function ($j) {
                $j->on('t04.co_partida', '=', 'ac_seguimiento.tab_meta_financiera.co_partida')
                  ->on('t04.id_tab_ejercicio_fiscal', '=', 't03.id_tab_ejercicio_fiscal');
            })
            ->join('mantenimiento.tab_sectores as t18a', 't03.id_tab_sectores', '=', 't18a.id')
            ->join('mantenimiento.tab_sectores as t18b', function ($join) {
            $join->on('t18a.co_sector', '=', 't18b.co_sector')
            ->on('t18b.nu_nivel', '=', DB::raw('1'));
            })      
            ->where('t03.id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))
            ->where('t03.in_activo', '=', true)
            ->where('t03.id_tab_lapso', '=', $id_tab_lapso)
            ->where('t18b.tx_codigo', '=', $item3->tx_sector)
            ->where('ac_seguimiento.tab_meta_financiera.co_partida', '=', $item3->co_partida)        
            ->groupBy('ac_seguimiento.tab_meta_financiera.co_partida')
            ->groupBy('de_fuente_financiamiento')
            ->get();                
                
            }
             if($tab_meta_financiera->count()>1){
                $i =  $tab_meta_financiera->count();
             }else{
             $i = 1;    
             }

//             var_dump($i);
//             exit();
             
             if($id_partida==$item3->co_partida){

		$html23.='
		<tr style="font-size:7px" >
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_presupuesto).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_modificado_anual).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_actualizado_anual).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_comprometido).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_causado).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_pagado).'</td>                                 
                <td style="width: 20%;" align="center">'.$item3->de_fuente_financiamiento.'</td>';
                $html23.='</tr>';
                
             }else{
                 
		$html23.='
		<tr style="font-size:7px" >
		<td style="width: 5%;" align="center" rowspan="'.$i.'">'.$item3->co_partida.'</td>
                <td style="width: 15%;" rowspan="'.$i.'">'.$item3->tx_nombre.'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_presupuesto).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_modificado_anual).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_actualizado_anual).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_comprometido).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_causado).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_pagado).'</td>                                 
                <td style="width: 20%;" align="center">'.$item3->de_fuente_financiamiento.'</td>';
                $html23.='</tr>';
                 
             }

             
             
                $mo_presupuesto = $mo_presupuesto + $item3->mo_presupuesto;
                $mo_modificado_anual = $mo_modificado_anual + $item3->mo_modificado_anual;
                $mo_actualizado_anual = $mo_actualizado_anual + $item3->mo_actualizado_anual;
                $mo_comprometido = $mo_comprometido + $item3->mo_comprometido;
                $mo_causado = $mo_causado + $item3->mo_causado;
                $mo_pagado = $mo_pagado + $item3->mo_pagado;
                $de_lapso = $dia_mes_fin;
                $id_tab_ejercicio_fiscal = $item3->id_tab_ejercicio_fiscal;

            $tx_sector = $item3->tx_sector;


         }else{
             
            if($itemData->id_ejecutor!=null){
             $tab_meta_financiera = tab_meta_financiera::select(                  
                'ac_seguimiento.tab_meta_financiera.co_partida'
            )
            ->join('ac_seguimiento.tab_meta_fisica as t01', 'ac_seguimiento.tab_meta_financiera.id_tab_meta_fisica', '=', 't01.id')
            ->join('ac_seguimiento.tab_ac_ae as t02', 't01.id_tab_ac_ae', '=', 't02.id')
            ->join('ac_seguimiento.tab_ac as t03', 't02.id_tab_ac', '=', 't03.id')
            ->join('mantenimiento.tab_fuente_financiamiento as t06', 'ac_seguimiento.tab_meta_financiera.id_tab_fuente_financiamiento', '=', 't06.id')
            ->join('mantenimiento.tab_lapso as t07', 't03.id_tab_lapso', '=', 't07.id')
            ->join('mantenimiento.tab_partidas as t04', function ($j) {
                $j->on('t04.co_partida', '=', 'ac_seguimiento.tab_meta_financiera.co_partida')
                  ->on('t04.id_tab_ejercicio_fiscal', '=', 't03.id_tab_ejercicio_fiscal');
            })
            ->join('mantenimiento.tab_sectores as t18a', 't03.id_tab_sectores', '=', 't18a.id')
            ->join('mantenimiento.tab_sectores as t18b', function ($join) {
            $join->on('t18a.co_sector', '=', 't18b.co_sector')
            ->on('t18b.nu_nivel', '=', DB::raw('1'));
            })      
            ->where('t03.id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))
            ->where('t03.in_activo', '=', true)
            ->where('t03.id_ejecutor', '=', $itemData->id_ejecutor)
            ->where('t03.id_tab_lapso', '=', $id_tab_lapso)
            ->where('t18b.tx_codigo', '=', $item3->tx_sector)
            ->where('ac_seguimiento.tab_meta_financiera.co_partida', '=', $item3->co_partida)        
            ->groupBy('ac_seguimiento.tab_meta_financiera.co_partida')
            ->groupBy('de_fuente_financiamiento')
            ->get(); 
            
            }else{
             
             $tab_meta_financiera = tab_meta_financiera::select(                  
                'ac_seguimiento.tab_meta_financiera.co_partida'
            )
            ->join('ac_seguimiento.tab_meta_fisica as t01', 'ac_seguimiento.tab_meta_financiera.id_tab_meta_fisica', '=', 't01.id')
            ->join('ac_seguimiento.tab_ac_ae as t02', 't01.id_tab_ac_ae', '=', 't02.id')
            ->join('ac_seguimiento.tab_ac as t03', 't02.id_tab_ac', '=', 't03.id')
            ->join('mantenimiento.tab_fuente_financiamiento as t06', 'ac_seguimiento.tab_meta_financiera.id_tab_fuente_financiamiento', '=', 't06.id')
            ->join('mantenimiento.tab_lapso as t07', 't03.id_tab_lapso', '=', 't07.id')
            ->join('mantenimiento.tab_partidas as t04', function ($j) {
                $j->on('t04.co_partida', '=', 'ac_seguimiento.tab_meta_financiera.co_partida')
                  ->on('t04.id_tab_ejercicio_fiscal', '=', 't03.id_tab_ejercicio_fiscal');
            })
            ->join('mantenimiento.tab_sectores as t18a', 't03.id_tab_sectores', '=', 't18a.id')
            ->join('mantenimiento.tab_sectores as t18b', function ($join) {
            $join->on('t18a.co_sector', '=', 't18b.co_sector')
            ->on('t18b.nu_nivel', '=', DB::raw('1'));
            })      
            ->where('t03.id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))
            ->where('t03.in_activo', '=', true)
            ->where('t03.id_tab_lapso', '=', $id_tab_lapso)
            ->where('t18b.tx_codigo', '=', $item3->tx_sector)
            ->where('ac_seguimiento.tab_meta_financiera.co_partida', '=', $item3->co_partida)        
            ->groupBy('ac_seguimiento.tab_meta_financiera.co_partida')
            ->groupBy('de_fuente_financiamiento')
            ->get();                
                
            }              
             if($tab_meta_financiera->count()>1){
                $i =  $tab_meta_financiera->count();
             }else{
             $i = 1;    
             }

//             var_dump($i);
//             exit();
             
             if($id_partida==$item3->co_partida){

		$html23.='
		<tr style="font-size:7px" >
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_presupuesto).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_modificado_anual).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_actualizado_anual).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_comprometido).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_causado).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_pagado).'</td>                                 
                <td style="width: 20%;" align="center">'.$item3->de_fuente_financiamiento.'</td>';
                $html23.='</tr>';
                
             }else{
                 
		$html23.='
		<tr style="font-size:7px" >
		<td style="width: 5%;" align="center" rowspan="'.$i.'">'.$item3->co_partida.'</td>
                <td style="width: 15%;" rowspan="'.$i.'">'.$item3->tx_nombre.'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_presupuesto).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_modificado_anual).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_actualizado_anual).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_comprometido).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_causado).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($item3->mo_pagado).'</td>                                 
                <td style="width: 20%;" align="center">'.$item3->de_fuente_financiamiento.'</td>';
                $html23.='</tr>';
                 
             }

                $mo_presupuesto = $mo_presupuesto + $item3->mo_presupuesto;
                $mo_modificado_anual = $mo_modificado_anual + $item3->mo_modificado_anual;
                $mo_actualizado_anual = $mo_actualizado_anual + $item3->mo_actualizado_anual;
                $mo_comprometido = $mo_comprometido + $item3->mo_comprometido;
                $mo_causado = $mo_causado + $item3->mo_causado;
                $mo_pagado = $mo_pagado + $item3->mo_pagado;
                $de_lapso = $dia_mes_fin;
                $id_tab_ejercicio_fiscal = $item3->id_tab_ejercicio_fiscal;            
         }

        $id_partida =$item3->co_partida;
         
     }
                  


		$html23.='
		<tr style="font-size:7px" >
                <td style="width: 20%;" colspan="2" align="right"> TOTAL EJECUTADO AL '.$de_lapso.' '.$id_tab_ejercicio_fiscal.' </td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($mo_presupuesto).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($mo_modificado_anual).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($mo_actualizado_anual).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($mo_comprometido).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($mo_causado).'</td>
                <td style="width: 10%;" align="right">'.$this->formatoDinero($mo_pagado).'</td>
                <td style="width: 20%;" align="right"></td>';
                $html23.='</tr>';

   $html23.='
</tbody>
</table>';  
   
             $pdf->writeHTML(Helper::htmlComprimir($html23), true, false, false, false, '');
   
      	if($pdf->getY()>140){
            $pdf->addPage();
            $pdf->setY(35);
         }
   
         
         if($data_responsables==''){
   $html1 = '
<table border="0.1" style="font-size:9px" cellpadding="3" >
<tbody>
<tr style="font-size:8px">
<td style="width: 35%;" align="center"><b>PLANIFICACIÓN, PRESUPUESTO Y CONTROL DE GESTIÓN</b></td>
<td style="width: 35%;" align="center"><b>ADMINISTRACIÓN Y FINANZAS</b></td>
<td style="width: 30%;" align="center"><b>TITULAR</b></td>
</tr>
<tr style="font-size:8px">
<td style="width: 35%;" align="center"><b>FIRMA Y SELLO</b></td>
<td style="width: 35%;" align="center"><b>FIRMA Y SELLO</b></td>
<td style="width: 30%;" align="center"><b>FIRMA Y SELLO</b></td>
</tr>
<tr style="font-size:8px">
<td style="width: 35%; height: 40px;" align="center"><b></b></td>
<td style="width: 35%; height: 40px;" align="center"><b></b></td>
<td style="width: 30%; height: 40px;" align="center"><b></b></td>
</tr>
<tr style="font-size:8px">
<td style="width: 35%;" align="center"><b>NOMBRE Y APELLIDO</b></td>
<td style="width: 35%;" align="center"><b>NOMBRE Y APELLIDO</b></td>
<td style="width: 30%;" align="center"><b>NOMBRE Y APELLIDO</b></td>
</tr>
<tr style="font-size:8px">
<td style="width: 35%; height: 40px;" align="center"><b></b></td>
<td style="width: 35%; height: 40px;" align="center"><b></b></td>
<td style="width: 30%; height: 40px;" align="center"><b></b></td>
</tr>
<tr style="font-size:8px">
<td style="width: 35%;" align="center"><b>C.I. </b></td>
<td style="width: 35%;" align="center"><b>C.I. </b></td>
<td style="width: 30%;" align="center"><b>C.I. </b></td>
</tr>
</tbody>
</table>
';
         }else{
             
   $html1 = '
<table border="0.1" style="font-size:9px" cellpadding="3" >
<tbody>
<tr style="font-size:8px">
<td style="width: 35%;" align="center"><b>PLANIFICACIÓN, PRESUPUESTO Y CONTROL DE GESTIÓN</b></td>
<td style="width: 35%;" align="center"><b>ADMINISTRACIÓN Y FINANZAS</b></td>
<td style="width: 30%;" align="center"><b>TITULAR</b></td>
</tr>
<tr style="font-size:8px">
<td style="width: 35%;" align="center"><b>FIRMA Y SELLO</b></td>
<td style="width: 35%;" align="center"><b>FIRMA Y SELLO</b></td>
<td style="width: 30%;" align="center"><b>FIRMA Y SELLO</b></td>
</tr>
<tr style="font-size:8px">
<td style="width: 35%; height: 40px;" align="center"><b></b></td>
<td style="width: 35%; height: 40px;" align="center"><b></b></td>
<td style="width: 30%; height: 40px;" align="center"><b></b></td>
</tr>
<tr style="font-size:8px">
<td style="width: 35%;" align="center"><b>NOMBRE Y APELLIDO</b></td>
<td style="width: 35%;" align="center"><b>NOMBRE Y APELLIDO</b></td>
<td style="width: 30%;" align="center"><b>NOMBRE Y APELLIDO</b></td>
</tr>
<tr style="font-size:8px">
<td style="width: 35%; height: 20px;" align="center">'.$data_responsables->realizador_nombres.'<b></b></td>
<td style="width: 35%; height: 20px;" align="center">'.$data_responsables->registrador_nombres.'<b></b></td>
<td style="width: 30%; height: 20px;" align="center">'.$data_responsables->autorizador_nombres.'<b></b></td>
</tr>
<tr style="font-size:8px">
<td style="width: 35%;" align="center"><b>C.I. '.$data_responsables->realizador_cedula.'</b></td>
<td style="width: 35%;" align="center"><b>C.I. '.$data_responsables->registrador_cedula.'</b></td>
<td style="width: 30%;" align="center"><b>C.I. '.$data_responsables->autorizador_cedula.'</b></td>
</tr>
</tbody>
</table>
';             
             
         }
   

          $pdf->SetX(140);
          $pdf->writeHTML(Helper::htmlComprimir($html1), true, false, false, false, '');          
                       
            }
            
      }
          $pdf->output('SEGUIMIENTO_AC_'.Session::get("ejercicio").'_'.date("H:i:s").'.pdf', 'D'); 

      }
      
      public function fichaConsolidadoSegundo($id_tab_lapso,$id_tipo_ejecutor=null)
      {
          ini_set('max_execution_time', 3600); 
        $data_lapso = tab_lapso::select(
            'id_tab_tipo_periodo'
        )
        ->where('id', '=', $id_tab_lapso)
        ->first();          

      
                if($data_lapso->id_tab_tipo_periodo==19){
                    
                    $periodo = '1TA/'.Session::get("ejercicio");
                    $dia_mes_fin = '31-03-';
                }
                
                if($data_lapso->id_tab_tipo_periodo==20){
                    
                    $periodo = '2TA/'.Session::get("ejercicio"); 
                    $dia_mes_fin = '30-06-';
                }

                if($data_lapso->id_tab_tipo_periodo==21){
                    
                    $periodo = '3TA/'.Session::get("ejercicio");
                    $dia_mes_fin = '30-09-';
                }

                if($data_lapso->id_tab_tipo_periodo==22){
                    
                    $periodo = '4TA/'.Session::get("ejercicio");
                    $dia_mes_fin = '31-12-';
                }        

                Session::put('periodo',$periodo); 

                
            $data = tab_ac::join('mantenimiento.tab_ejecutores as t04', 't04.id_ejecutor', '=', 'ac_seguimiento.tab_ac.id_ejecutor')
            ->leftjoin('ac_seguimiento.tab_ac_vinculo as t49', 't49.id_tab_ac', '=', 'ac_seguimiento.tab_ac.id')
            ->leftjoin('t45_planes_zulia as t45', function ($join) {
            $join->on('t49.co_area_estrategica', '=', 't45.co_area_estrategica')
            ->on('t45.nu_nivel', '=', DB::raw('0'));
            })
            ->leftjoin('t45_planes_zulia as t45a', function ($join) {
            $join->on('t49.co_area_estrategica', '=', 't45a.co_area_estrategica')
            ->on('t49.co_ambito_estado', '=', 't45a.co_ambito_zulia')        
            ->on('t45a.nu_nivel', '=', DB::raw('1'));
            })            
            ->select(
            'ac_seguimiento.tab_ac.id_ejecutor'
            )
//            ->where('ac_seguimiento.tab_ac.id_ejecutor', '=', '0002')
            ->where('ac_seguimiento.tab_ac.in_activo', '=', true)
            ->where('ac_seguimiento.tab_ac.in_abierta', '=', false)
            ->where('t04.id_tab_tipo_ejecutor', '=', $id_tipo_ejecutor)
            ->where('ac_seguimiento.tab_ac.id_tab_ejercicio_fiscal', '=', Session::get("ejercicio"))
            ->groupBy('tab_ac.id_ejecutor')
            ->groupBy('t45.co_area_estrategica')
            ->groupBy('t45a.nu_orden')
            ->orderby('t45.co_area_estrategica', 'ASC')->orderby('t45a.nu_orden', 'ASC')->orderby('ac_seguimiento.tab_ac.id_ejecutor', 'ASC')->get();   
            
//var_dump($data);
//exit();
      
 
        
          $pdf = new PDFseguimientoAC("L", PDF_UNIT, 'Letter', true, 'UTF-8', false);
          $pdf->SetCreator('Sistema POA, Yoser Perez');
          $pdf->SetAuthor('Yoser Perez');
          $pdf->SetTitle('Seguimiento AC');
          $pdf->SetSubject('Seguimiento AC');
          $pdf->SetKeywords('Seguimiento AC, PDF, Zulia, SPE, '.Session::get("ejercicio").'');
          $pdf->SetMargins(10,10,10);
          $pdf->SetTopMargin(35);
          $pdf->SetPrintHeader(true);
          $pdf->SetPrintFooter(true);
          // set auto page breaks
          $pdf->SetAutoPageBreak(true, 10);
          $pdf->SetFont('','',11);
 
          $pdf->Ln(-3);


            foreach($data as $itemData) {
                
                $data_ejecutor = tab_ac::select(
                'tx_ejecutor_ac',
                't45.tx_descripcion as tx_area_estrategica',        
                'ac_seguimiento.tab_ac.inst_mision',
                'ac_seguimiento.tab_ac.inst_vision',
                'ac_seguimiento.tab_ac.inst_objetivos',
                'id_tab_tipo_periodo'
                )
                ->join('mantenimiento.tab_lapso as t02', 'ac_seguimiento.tab_ac.id_tab_lapso', '=', 't02.id')   
                ->leftjoin('ac_seguimiento.tab_ac_vinculo as t49', 't49.id_tab_ac', '=', 'ac_seguimiento.tab_ac.id')
                ->leftjoin('t45_planes_zulia as t45', function ($join) {
                $join->on('t49.co_area_estrategica', '=', 't45.co_area_estrategica')
                ->on('t45.nu_nivel', '=', DB::raw('0'));
                })                        
                ->where('id_ejecutor', '=', $itemData->id_ejecutor)
                ->where('ac_seguimiento.tab_ac.id_tab_ejercicio_fiscal', '=', Session::get("ejercicio"))
                ->where('id_tab_lapso', '=', $id_tab_lapso)
                ->first();                
                
              if($data_ejecutor){
                
                
                if($data_ejecutor->id_tab_tipo_periodo==19){
                    
                    $mes = 'Marzo';
                }
                
                if($data_ejecutor->id_tab_tipo_periodo==20){
                    
                    $mes = 'Junio';
                }

                if($data_ejecutor->id_tab_tipo_periodo==21){
                    
                    $mes = 'Septiembre';
                }

                if($data_ejecutor->id_tab_tipo_periodo==22){
                    
                    $mes = 'Diciembre';
                }                
            
                $pdf->AddPage();
                $pdf->SetFont('','B',12);
            $pdf->setY(10);
            $pdf->MultiCell(277, 5, 'REPÚBLICA BOLIVARIANA DE VENEZUELA', 0, 'C', 0, 0, '', '', true);
            $pdf->Ln(10);        
            $pdf->MultiCell(277, 5, 'GOBERNACIÓN DEL ESTADO ZULIA', 0, 'C', 0, 0, '', '', true);
            $pdf->Ln(5);                
            $pdf->SetY(75);
            $pdf->SetFont('','B',18);
            $pdf->SetTextColor(0,0,0);
            $pdf->Write(0, 'SISTEMA DE SEGUIMIENTO, EVALUACIÓN Y CONTROL DEL PLAN OPERATIVO ESTADAL', '', 0, 'C', true, 0, false, false, 0);
            $pdf->Ln(5);
            $pdf->Write(0, 'AÑO '.Session::get("ejercicio"), '', 0, 'C', true, 0, false, false, 0);
            $pdf->Ln(10);
            $pdf->Write(0, $data_ejecutor->tx_ejecutor_ac, '', 0, 'C', true, 0, false, false, 0);
            $pdf->SetY(190);
            $pdf->SetFont('','',11);            
            $pdf->Write(0, 'Maracaibo, '.$mes.' de '.Session::get("ejercicio"), '', 0, 'C', true, 0, false, false, 0);

            $data2 = tab_ac::join('mantenimiento.tab_ejecutores as t04', 't04.id_ejecutor', '=', 'ac_seguimiento.tab_ac.id_ejecutor')
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
            'id_tab_tipo_periodo',
            'ac_seguimiento.tab_ac.id_tab_ejercicio_fiscal',
            'ac_seguimiento.tab_ac.de_observacion_002',
            'ac_seguimiento.tab_ac.de_observacion_003',
            'ac_seguimiento.tab_ac.id',
            'ac_seguimiento.tab_ac.de_sector',
            't21.id_tab_ac_ae_predefinida'                    
        )
        ->where('ac_seguimiento.tab_ac.id_ejecutor', '=', $itemData->id_ejecutor)
        ->where('ac_seguimiento.tab_ac.id_tab_lapso', '=', $id_tab_lapso)
        ->where('ac_seguimiento.tab_ac.in_activo', '=', true)
        ->orderby('ac_seguimiento.tab_ac.id_ejecutor', 'ASC')->orderby('ac_seguimiento.tab_ac.id_tab_ac_predefinida', 'ASC')->orderby('t21.id_tab_ac_ae_predefinida', 'ASC')
        ->get(); 
            

          foreach($data2 as $data) {
          
             $tab_lapso = tab_lapso::where('id_tab_tipo_periodo', '<=', $data->id_tab_tipo_periodo)
             ->where('id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))
            ->get();  
             
              $j =  $tab_lapso->count();

            $actividad = tab_meta_fisica::select('codigo','nb_meta',
            'co_partida',DB::raw('sum(distinct tx_prog_anual::numeric) as tx_prog_anual'),
                    DB::raw("string_agg(distinct de_desvio, ',') as de_desvio"),
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
$k = 1;
$id = 0;
$de_desvio = '';
$desvio = '';

foreach($actividad as $item) { 
    
    
                    $data10 = tab_ac::select(
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
                    
            $data11 = tab_meta_fisica::select(
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
            
            $data12 = tab_meta_fisica::select('tab_meta_fisica.id','codigo','nb_meta','de_desvio'
            )
             ->join('ac_seguimiento.tab_ac_ae as t03', 'tab_meta_fisica.id_tab_ac_ae', '=', 't03.id')
             ->join('mantenimiento.tab_ac_ae_predefinida as t04', 't03.id_tab_ac_ae_predefinida', '=', 't04.id')
             ->join('ac_seguimiento.tab_ac as t05', 't03.id_tab_ac', '=', 't05.id')
             ->join('mantenimiento.tab_ac_predefinida as t06', 't05.id_tab_ac_predefinida', '=', 't06.id')
             ->join('mantenimiento.tab_sectores as t07', 't05.id_tab_sectores', '=', 't07.id') 
             ->join('mantenimiento.tab_lapso as t02', 't05.id_tab_lapso', '=', 't02.id')
            ->where('t05.nu_codigo', '=', $data->id_proy_ac)
            ->where('t05.in_activo', '=', true)
            ->where('tab_meta_fisica.codigo', '=', $item->codigo)
            ->where('t03.id_tab_ac_ae_predefinida', '=', $data->id_tab_ac_ae_predefinida)
            ->where('id_tab_tipo_periodo', '<=', $data->id_tab_tipo_periodo)
             ->groupBy('codigo')
             ->groupBy('nb_meta')
            ->groupBy('de_desvio')
            ->groupBy('tab_meta_fisica.id')            
            ->orderBy('tab_meta_fisica.id', 'ASC')
            ->get();            
    
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
		<td style="width: 10%;"  align="center">'.$this->formatoDinero($data11->mo_presupuesto).'</td>
		<td style="width: 10%;" align="center">'.$this->formatoDinero($data11->mo_modificado_anual).'</td>
                <td style="width: 10%;" align="center">'.$this->formatoDinero($data11->mo_actualizado_anual).'</td>';
                $html23.='</tr>';

                
                }else{
                    
                 if($de_desvio==''){
                     
                 }else{
                                         
                     
		$html23.='
		<tr style="font-size:6px" nobr="true">
		<td style="width: 100%;"  nobr="true" rowspan="1">CAUSAS DEL DESVIO: '.$desvio.'</td>';
                $html23.='</tr>';   
                $desvio = '';
                $k = 1;
                 }
                    
		$html23.='
		<tr style="font-size:6px" nobr="true">
                <td style="width: 30%;"  nobr="true" rowspan="'.$i.'">'.$item->codigo.' - '.$item->nb_meta.'</td>
		<td style="width: 10%;"  align="center" rowspan="'.$i.'">'.$this->formatoDinero($item->tx_prog_anual).'</td>
		<td style="width: 10%;"  align="center" rowspan="'.$i.'">'.$this->formatoDinero($data10->nu_meta_modificada).'</td>
                <td style="width: 10%;" align="center" rowspan="'.$i.'">'.$this->formatoDinero($item->tx_prog_anual + $data10->nu_meta_modificada).'</td>
                <td style="width: 10%;" align="center">'.$item->co_partida.'</td>                    
		<td style="width: 10%;"  align="center">'.$this->formatoDinero($data11->mo_presupuesto).'</td>
		<td style="width: 10%;" align="center">'.$this->formatoDinero($data11->mo_modificado_anual).'</td>
                <td style="width: 10%;" align="center">'.$this->formatoDinero($data11->mo_actualizado_anual).'</td>';
                $html23.='</tr>';   
                
                foreach($data12 as $item12) {
                if($item12->de_desvio!=''){    
                $desvio = $desvio.' '.$k.'. TRIMESTRE: '.$item12->de_desvio;
                
                }
                $k++;
                }                  
                    
                }
                    

                $id =$item->codigo;
                $de_desvio=$item->de_desvio;
                
          
      }
      
        $html23.='
        <tr style="font-size:6px" nobr="true">
        <td style="width: 100%;"  nobr="true" rowspan="1">CAUSAS DEL DESVIO: '.$desvio.'</td>';
        $html23.='</tr>';        

$html23.='
</tbody>
</table>';


          $pdf->AddPage();
          $this->encabezado4($pdf);
          $pdf->SetFont('','',11);
//          $pdf->writeHTML($htmlObjetivo, true, false, false, false, '');
          $pdf->writeHTML(Helper::htmlComprimir($html1), true, false, false, false, '');
          $pdf->Ln(-3);
          $pdf->writeHTML($html23, true, false, false, false, '');          
}  

          }
          
          $id_ac = '';
          
          foreach($data2 as $data) {
              
            if($id_ac!=$data->id){
                
            $actividad = tab_forma_005::join('ac_seguimiento.tab_ac as t01', 'ac_seguimiento.tab_forma_005.id_tab_ac', '=', 't01.id')
            ->join('mantenimiento.tab_ejecutores as t02', 't01.id_tab_ejecutores', '=', 't02.id')
            ->join('mantenimiento.tab_lapso as t03', 't01.id_tab_lapso', '=', 't03.id')
            ->join('mantenimiento.tab_estatus as t04', 't04.id', '=', 'ac_seguimiento.tab_forma_005.id_tab_estatus')
            ->select(
                'ac_seguimiento.tab_forma_005.id',
                'ac_seguimiento.tab_forma_005.pp_anual',
                'ac_seguimiento.tab_forma_005.tp_indicador',
                'ac_seguimiento.tab_forma_005.nb_indicador_gestion',
                'ac_seguimiento.tab_forma_005.de_indicador_descripcion',
                'ac_seguimiento.tab_forma_005.de_valor_objetivo',
                'ac_seguimiento.tab_forma_005.de_valor_obtenido',
                'ac_seguimiento.tab_forma_005.de_valor_objetivo_acu',
                'ac_seguimiento.tab_forma_005.de_valor_obtenido_acu',                    
                'ac_seguimiento.tab_forma_005.nu_cumplimiento',
                'ac_seguimiento.tab_forma_005.de_formula',
                'de_estatus',
                'ac_seguimiento.tab_forma_005.id_tab_estatus',
                'ac_seguimiento.tab_forma_005.in_005'
            )                  
            ->where('id_tab_ac', '=', $data->id)
            ->orderby('ac_seguimiento.tab_forma_005.id', 'ASC')
            ->get();

         if($actividad->count()>0){ 
      foreach($actividad as $item) {
          
          if($item->de_valor_objetivo_acu==null || $item->de_valor_objetivo_acu==0){
          $nu_cumplimiento = 0;    
          }else{
          $nu_cumplimiento = round(($item->de_valor_obtenido_acu/$item->de_valor_objetivo_acu)*100,2);
          }
          
$pdf->AddPage();
$this->encabezado5($pdf);
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
<td colspan="3" style="width: 100%;" align="justify"><b>PRODUCTO PROGRAMADO ANUAL DEL OBJETIVO INSTITUCIONAL:</b> '.$data->tx_pr_objetivo.'</td>
</tr>
<tr style="font-size:9px">
<td colspan="3" style="width: 100%;" align="justify"><b>INDICADORES DE GESTIÓN (EFICIENCIA, EFICACIA, EFECTIVIDAD):</b> '.$item->tp_indicador.'</td>
</tr>
<tr style="font-size:9px">
<td style="width: 70%;  height: 30px;" align="justify" rowspan="2"><b>NOMBRE DEL INDICADOR:</b> '.$item->nb_indicador_gestion.'</td>
<td style="width: 10%;" align="center"><b>VALOR OBJETIVO:</b></td>
<td style="width: 10%;" align="center"><b>VALOR OBTENIDO:</b></td>
<td style="width: 10%;" align="center"><b>CUMPLIMIENTO:</b></td>
</tr>
<tr style="font-size:9px">
<td style="width: 10%;" align="center">'.$item->de_valor_objetivo_acu.' </td>
<td style="width: 10%;" align="center">'.$item->de_valor_obtenido_acu.' </td>
<td style="width: 10%;" align="center">'.$nu_cumplimiento.' % </td>
</tr>
<tr style="font-size:9px height: 100px;">
<td colspan="4" style="height: 30px;" align="justify"><b>DESCRIPCIÓN DEL INDICADOR:</b> '.$item->de_indicador_descripcion.'</td>
</tr>
<tr style="font-size:9px  height: 100px;">
<td colspan="4" style="height: 30px;" align="justify"><b>FORMULA:</b> '.$item->de_formula.'</td>
</tr>
</tbody>
</table>
';
        $pdf->writeHTML(Helper::htmlComprimir($html1), true, false, false, false, '');  
      }        
         }
         
            }

        $id_ac = $data->id;

      }            
                       
            }
            
            }
          $pdf->output('SEGUIMIENTO_AC_'.Session::get("ejercicio").'_'.date("H:i:s").'.pdf', 'D'); 

      }      

      public function fichaConsolidadoCuarto($id_tab_lapso,$id_tipo_ejecutor=null)
      {
          ini_set('max_execution_time', 3600); 
        $data_lapso = tab_lapso::select(
            'id_tab_tipo_periodo'
        )
        ->where('id', '=', $id_tab_lapso)
        ->first();          

      
                if($data_lapso->id_tab_tipo_periodo==19){
                    
                    $periodo = '1TA/'.Session::get("ejercicio");
                    $dia_mes_fin = '31-03-';
                }
                
                if($data_lapso->id_tab_tipo_periodo==20){
                    
                    $periodo = '2TA/'.Session::get("ejercicio"); 
                    $dia_mes_fin = '30-06-';
                }

                if($data_lapso->id_tab_tipo_periodo==21){
                    
                    $periodo = '3TA/'.Session::get("ejercicio");
                    $dia_mes_fin = '30-09-';
                }

                if($data_lapso->id_tab_tipo_periodo==22){
                    
                    $periodo = '4TA/'.Session::get("ejercicio");
                    $dia_mes_fin = '31-12-';
                }        

                Session::put('periodo',$periodo); 

                
            $data = tab_ac::join('mantenimiento.tab_ejecutores as t04', 't04.id_ejecutor', '=', 'ac_seguimiento.tab_ac.id_ejecutor')
            ->leftjoin('ac_seguimiento.tab_ac_vinculo as t49', 't49.id_tab_ac', '=', 'ac_seguimiento.tab_ac.id')
            ->leftjoin('t45_planes_zulia as t45', function ($join) {
            $join->on('t49.co_area_estrategica', '=', 't45.co_area_estrategica')
            ->on('t45.nu_nivel', '=', DB::raw('0'));
            })
            ->leftjoin('t45_planes_zulia as t45a', function ($join) {
            $join->on('t49.co_area_estrategica', '=', 't45a.co_area_estrategica')
            ->on('t49.co_ambito_estado', '=', 't45a.co_ambito_zulia')        
            ->on('t45a.nu_nivel', '=', DB::raw('1'));
            })            
            ->select(
            'ac_seguimiento.tab_ac.id_ejecutor'
            )
//            ->where('ac_seguimiento.tab_ac.id_ejecutor', '=', '0002')
            ->where('ac_seguimiento.tab_ac.in_activo', '=', true)
            ->where('ac_seguimiento.tab_ac.in_abierta', '=', false)
            ->where('t04.id_tab_tipo_ejecutor', '=', $id_tipo_ejecutor)
            ->where('ac_seguimiento.tab_ac.id_tab_ejercicio_fiscal', '=', Session::get("ejercicio"))
            ->groupBy('tab_ac.id_ejecutor')
            ->groupBy('t45.co_area_estrategica')
            ->groupBy('t45a.nu_orden')
            ->orderby('t45.co_area_estrategica', 'ASC')->orderby('t45a.nu_orden', 'ASC')->orderby('ac_seguimiento.tab_ac.id_ejecutor', 'ASC')->get();   
            
//var_dump($data);
//exit();
      
 
        
          $pdf = new PDFseguimientoAC("L", PDF_UNIT, 'Letter', true, 'UTF-8', false);
          $pdf->SetCreator('Sistema POA, Yoser Perez');
          $pdf->SetAuthor('Yoser Perez');
          $pdf->SetTitle('Seguimiento AC');
          $pdf->SetSubject('Seguimiento AC');
          $pdf->SetKeywords('Seguimiento AC, PDF, Zulia, SPE, '.Session::get("ejercicio").'');
          $pdf->SetMargins(10,10,10);
          $pdf->SetTopMargin(35);
          $pdf->SetPrintHeader(true);
          $pdf->SetPrintFooter(true);
          // set auto page breaks
          $pdf->SetAutoPageBreak(true, 10);
          $pdf->SetFont('','',11);
 
          $pdf->Ln(-3);


            foreach($data as $itemData) {
                
                $data_ejecutor = tab_ac::select(
                'tx_ejecutor_ac',
                't45.tx_descripcion as tx_area_estrategica',        
                'ac_seguimiento.tab_ac.inst_mision',
                'ac_seguimiento.tab_ac.inst_vision',
                'ac_seguimiento.tab_ac.inst_objetivos',
                'id_tab_tipo_periodo'
                )
                ->join('mantenimiento.tab_lapso as t02', 'ac_seguimiento.tab_ac.id_tab_lapso', '=', 't02.id')   
                ->leftjoin('ac_seguimiento.tab_ac_vinculo as t49', 't49.id_tab_ac', '=', 'ac_seguimiento.tab_ac.id')
                ->leftjoin('t45_planes_zulia as t45', function ($join) {
                $join->on('t49.co_area_estrategica', '=', 't45.co_area_estrategica')
                ->on('t45.nu_nivel', '=', DB::raw('0'));
                })                        
                ->where('id_ejecutor', '=', $itemData->id_ejecutor)
                ->where('ac_seguimiento.tab_ac.id_tab_ejercicio_fiscal', '=', Session::get("ejercicio"))
                ->where('id_tab_lapso', '=', $id_tab_lapso)
                ->first(); 
                
                if($data_ejecutor){
                
                
                if($data_ejecutor->id_tab_tipo_periodo==19){
                    
                    $mes = 'Marzo';
                }
                
                if($data_ejecutor->id_tab_tipo_periodo==20){
                    
                    $mes = 'Junio';
                }

                if($data_ejecutor->id_tab_tipo_periodo==21){
                    
                    $mes = 'Septiembre';
                }

                if($data_ejecutor->id_tab_tipo_periodo==22){
                    
                    $mes = 'Diciembre';
                }                
            
                $pdf->AddPage();
                $pdf->SetFont('','B',12);
            $pdf->setY(10);
            $pdf->MultiCell(277, 5, 'REPÚBLICA BOLIVARIANA DE VENEZUELA', 0, 'C', 0, 0, '', '', true);
            $pdf->Ln(10);        
            $pdf->MultiCell(277, 5, 'GOBERNACIÓN DEL ESTADO ZULIA', 0, 'C', 0, 0, '', '', true);
            $pdf->Ln(5);                
            $pdf->SetY(75);
            $pdf->SetFont('','B',18);
            $pdf->SetTextColor(0,0,0);
            $pdf->Write(0, 'SISTEMA DE SEGUIMIENTO, EVALUACIÓN Y CONTROL DEL PLAN OPERATIVO ESTADAL', '', 0, 'C', true, 0, false, false, 0);
            $pdf->Ln(5);
            $pdf->Write(0, 'AÑO '.Session::get("ejercicio"), '', 0, 'C', true, 0, false, false, 0);
            $pdf->Ln(10);
            $pdf->Write(0, $data_ejecutor->tx_ejecutor_ac, '', 0, 'C', true, 0, false, false, 0);
            $pdf->SetY(190);
            $pdf->SetFont('','',11);            
            $pdf->Write(0, 'Maracaibo, '.$mes.' de '.Session::get("ejercicio"), '', 0, 'C', true, 0, false, false, 0);

            $data2 = tab_ac::join('mantenimiento.tab_ejecutores as t04', 't04.id_ejecutor', '=', 'ac_seguimiento.tab_ac.id_ejecutor')
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
            'id_tab_tipo_periodo',
            'ac_seguimiento.tab_ac.id_tab_ejercicio_fiscal',
            'ac_seguimiento.tab_ac.de_observacion_002',
            'ac_seguimiento.tab_ac.de_observacion_003',
            'ac_seguimiento.tab_ac.id',
            'ac_seguimiento.tab_ac.de_sector',
            't21.id_tab_ac_ae_predefinida'                    
        )
        ->where('ac_seguimiento.tab_ac.id_ejecutor', '=', $itemData->id_ejecutor)
        ->where('ac_seguimiento.tab_ac.id_tab_lapso', '=', $id_tab_lapso)
        ->where('ac_seguimiento.tab_ac.in_activo', '=', true)
        ->orderby('ac_seguimiento.tab_ac.id_ejecutor', 'ASC')->orderby('ac_seguimiento.tab_ac.id_tab_ac_predefinida', 'ASC')->orderby('t21.id_tab_ac_ae_predefinida', 'ASC')
        ->get(); 
            

          foreach($data2 as $data) {
          
             $tab_lapso = tab_lapso::where('id_tab_tipo_periodo', '<=', $data->id_tab_tipo_periodo)
             ->where('id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))
            ->get();  
             
              $j =  $tab_lapso->count();

            $actividad = tab_meta_fisica::select('codigo','nb_meta',
            'co_partida',DB::raw('sum(distinct tx_prog_anual::numeric) as tx_prog_anual'),
                    DB::raw("string_agg(distinct de_desvio, ',') as de_desvio"),
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
$k = 1;
$id = 0;
$de_desvio = '';
$desvio = '';

foreach($actividad as $item) { 
    
    
                    $data10 = tab_ac::select(
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
                    
            $data11 = tab_meta_fisica::select(
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
            
            $data12 = tab_meta_fisica::select('tab_meta_fisica.id','codigo','nb_meta','de_desvio'
            )
             ->join('ac_seguimiento.tab_ac_ae as t03', 'tab_meta_fisica.id_tab_ac_ae', '=', 't03.id')
             ->join('mantenimiento.tab_ac_ae_predefinida as t04', 't03.id_tab_ac_ae_predefinida', '=', 't04.id')
             ->join('ac_seguimiento.tab_ac as t05', 't03.id_tab_ac', '=', 't05.id')
             ->join('mantenimiento.tab_ac_predefinida as t06', 't05.id_tab_ac_predefinida', '=', 't06.id')
             ->join('mantenimiento.tab_sectores as t07', 't05.id_tab_sectores', '=', 't07.id') 
             ->join('mantenimiento.tab_lapso as t02', 't05.id_tab_lapso', '=', 't02.id')
            ->where('t05.nu_codigo', '=', $data->id_proy_ac)
            ->where('t05.in_activo', '=', true)
            ->where('tab_meta_fisica.codigo', '=', $item->codigo)
            ->where('t03.id_tab_ac_ae_predefinida', '=', $data->id_tab_ac_ae_predefinida)
            ->where('id_tab_tipo_periodo', '<=', $data->id_tab_tipo_periodo)
             ->groupBy('codigo')
             ->groupBy('nb_meta')
            ->groupBy('de_desvio')
            ->groupBy('tab_meta_fisica.id')            
            ->orderBy('tab_meta_fisica.id', 'ASC')
            ->get();            
    
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
		<td style="width: 10%;"  align="center">'.$this->formatoDinero($data11->mo_presupuesto).'</td>
		<td style="width: 10%;" align="center">'.$this->formatoDinero($data11->mo_modificado_anual).'</td>
                <td style="width: 10%;" align="center">'.$this->formatoDinero($data11->mo_actualizado_anual).'</td>';
                $html23.='</tr>';

                
                }else{
                    
                 if($de_desvio==''){
                     
                 }else{
                                         
                     
		$html23.='
		<tr style="font-size:6px" nobr="true">
		<td style="width: 100%;"  nobr="true" rowspan="1">CAUSAS DEL DESVIO: '.$desvio.'</td>';
                $html23.='</tr>';   
                $desvio = '';
                $k = 1;
                 }
                    
		$html23.='
		<tr style="font-size:6px" nobr="true">
                <td style="width: 30%;"  nobr="true" rowspan="'.$i.'">'.$item->codigo.' - '.$item->nb_meta.'</td>
		<td style="width: 10%;"  align="center" rowspan="'.$i.'">'.$this->formatoDinero($item->tx_prog_anual).'</td>
		<td style="width: 10%;"  align="center" rowspan="'.$i.'">'.$this->formatoDinero($data10->nu_meta_modificada).'</td>
                <td style="width: 10%;" align="center" rowspan="'.$i.'">'.$this->formatoDinero($item->tx_prog_anual + $data10->nu_meta_modificada).'</td>
                <td style="width: 10%;" align="center">'.$item->co_partida.'</td>                    
		<td style="width: 10%;"  align="center">'.$this->formatoDinero($data11->mo_presupuesto).'</td>
		<td style="width: 10%;" align="center">'.$this->formatoDinero($data11->mo_modificado_anual).'</td>
                <td style="width: 10%;" align="center">'.$this->formatoDinero($data11->mo_actualizado_anual).'</td>';
                $html23.='</tr>';   
                
                foreach($data12 as $item12) {
                if($item12->de_desvio!=''){    
                $desvio = $desvio.' '.$k.'. TRIMESTRE: '.$item12->de_desvio;
                
                }
                $k++;
                }                  
                    
                }
                    

                $id =$item->codigo;
                $de_desvio=$item->de_desvio;
                
          
      }
      
        $html23.='
        <tr style="font-size:6px" nobr="true">
        <td style="width: 100%;"  nobr="true" rowspan="1">CAUSAS DEL DESVIO: '.$desvio.'</td>';
        $html23.='</tr>';        

$html23.='
</tbody>
</table>';


          $pdf->AddPage();
          $this->encabezado4($pdf);
          $pdf->SetFont('','',11);
//          $pdf->writeHTML($htmlObjetivo, true, false, false, false, '');
          $pdf->writeHTML(Helper::htmlComprimir($html1), true, false, false, false, '');
          $pdf->Ln(-3);
          $pdf->writeHTML($html23, true, false, false, false, '');          
}  

          }
     
            }
            
            }
          $pdf->output('SEGUIMIENTO_AC_'.Session::get("ejercicio").'_'.date("H:i:s").'.pdf', 'D'); 

      }

      public function fichaConsolidadoQuinto($id_tab_lapso,$id_tipo_ejecutor=null)
      {
          ini_set('max_execution_time', 3600); 
        $data_lapso = tab_lapso::select(
            'id_tab_tipo_periodo'
        )
        ->where('id', '=', $id_tab_lapso)
        ->first();          

      
                if($data_lapso->id_tab_tipo_periodo==19){
                    
                    $periodo = '1TA/'.Session::get("ejercicio");
                    $dia_mes_fin = '31-03-';
                }
                
                if($data_lapso->id_tab_tipo_periodo==20){
                    
                    $periodo = '2TA/'.Session::get("ejercicio"); 
                    $dia_mes_fin = '30-06-';
                }

                if($data_lapso->id_tab_tipo_periodo==21){
                    
                    $periodo = '3TA/'.Session::get("ejercicio");
                    $dia_mes_fin = '30-09-';
                }

                if($data_lapso->id_tab_tipo_periodo==22){
                    
                    $periodo = '4TA/'.Session::get("ejercicio");
                    $dia_mes_fin = '31-12-';
                }        

                Session::put('periodo',$periodo); 

                
            $data = tab_ac::join('mantenimiento.tab_ejecutores as t04', 't04.id_ejecutor', '=', 'ac_seguimiento.tab_ac.id_ejecutor')
            ->leftjoin('ac_seguimiento.tab_ac_vinculo as t49', 't49.id_tab_ac', '=', 'ac_seguimiento.tab_ac.id')
            ->leftjoin('t45_planes_zulia as t45', function ($join) {
            $join->on('t49.co_area_estrategica', '=', 't45.co_area_estrategica')
            ->on('t45.nu_nivel', '=', DB::raw('0'));
            })
            ->leftjoin('t45_planes_zulia as t45a', function ($join) {
            $join->on('t49.co_area_estrategica', '=', 't45a.co_area_estrategica')
            ->on('t49.co_ambito_estado', '=', 't45a.co_ambito_zulia')        
            ->on('t45a.nu_nivel', '=', DB::raw('1'));
            })            
            ->select(
            'ac_seguimiento.tab_ac.id_ejecutor'
            )
//            ->where('ac_seguimiento.tab_ac.id_ejecutor', '=', '0002')
            ->where('ac_seguimiento.tab_ac.in_activo', '=', true)
            ->where('ac_seguimiento.tab_ac.in_abierta', '=', false)
            ->where('t04.id_tab_tipo_ejecutor', '=', $id_tipo_ejecutor)
            ->where('ac_seguimiento.tab_ac.id_tab_ejercicio_fiscal', '=', Session::get("ejercicio"))
            ->groupBy('tab_ac.id_ejecutor')
            ->groupBy('t45.co_area_estrategica')
            ->groupBy('t45a.nu_orden')
            ->orderby('t45.co_area_estrategica', 'ASC')->orderby('t45a.nu_orden', 'ASC')->orderby('ac_seguimiento.tab_ac.id_ejecutor', 'ASC')->get();   
            
//var_dump($data);
//exit();
      
 
        
          $pdf = new PDFseguimientoAC("L", PDF_UNIT, 'Letter', true, 'UTF-8', false);
          $pdf->SetCreator('Sistema POA, Yoser Perez');
          $pdf->SetAuthor('Yoser Perez');
          $pdf->SetTitle('Seguimiento AC');
          $pdf->SetSubject('Seguimiento AC');
          $pdf->SetKeywords('Seguimiento AC, PDF, Zulia, SPE, '.Session::get("ejercicio").'');
          $pdf->SetMargins(10,10,10);
          $pdf->SetTopMargin(35);
          $pdf->SetPrintHeader(true);
          $pdf->SetPrintFooter(true);
          // set auto page breaks
          $pdf->SetAutoPageBreak(true, 10);
          $pdf->SetFont('','',11);
 
          $pdf->Ln(-3);


            foreach($data as $itemData) {
                
                $data_ejecutor = tab_ac::select(
                'tx_ejecutor_ac',
                't45.tx_descripcion as tx_area_estrategica',        
                'ac_seguimiento.tab_ac.inst_mision',
                'ac_seguimiento.tab_ac.inst_vision',
                'ac_seguimiento.tab_ac.inst_objetivos',
                'id_tab_tipo_periodo'
                )
                ->join('mantenimiento.tab_lapso as t02', 'ac_seguimiento.tab_ac.id_tab_lapso', '=', 't02.id')   
                ->leftjoin('ac_seguimiento.tab_ac_vinculo as t49', 't49.id_tab_ac', '=', 'ac_seguimiento.tab_ac.id')
                ->leftjoin('t45_planes_zulia as t45', function ($join) {
                $join->on('t49.co_area_estrategica', '=', 't45.co_area_estrategica')
                ->on('t45.nu_nivel', '=', DB::raw('0'));
                })                        
                ->where('id_ejecutor', '=', $itemData->id_ejecutor)
                ->where('ac_seguimiento.tab_ac.id_tab_ejercicio_fiscal', '=', Session::get("ejercicio"))
                ->where('id_tab_lapso', '=', $id_tab_lapso)
                ->first();                
                
                if($data_ejecutor){
                
                if($data_ejecutor->id_tab_tipo_periodo==19){
                    
                    $mes = 'Marzo';
                }
                
                if($data_ejecutor->id_tab_tipo_periodo==20){
                    
                    $mes = 'Junio';
                }

                if($data_ejecutor->id_tab_tipo_periodo==21){
                    
                    $mes = 'Septiembre';
                }

                if($data_ejecutor->id_tab_tipo_periodo==22){
                    
                    $mes = 'Diciembre';
                }                
            
                $pdf->AddPage();
                $pdf->SetFont('','B',12);
            $pdf->setY(10);
            $pdf->MultiCell(277, 5, 'REPÚBLICA BOLIVARIANA DE VENEZUELA', 0, 'C', 0, 0, '', '', true);
            $pdf->Ln(10);        
            $pdf->MultiCell(277, 5, 'GOBERNACIÓN DEL ESTADO ZULIA', 0, 'C', 0, 0, '', '', true);
            $pdf->Ln(5);                
            $pdf->SetY(75);
            $pdf->SetFont('','B',18);
            $pdf->SetTextColor(0,0,0);
            $pdf->Write(0, 'SISTEMA DE SEGUIMIENTO, EVALUACIÓN Y CONTROL DEL PLAN OPERATIVO ESTADAL', '', 0, 'C', true, 0, false, false, 0);
            $pdf->Ln(5);
            $pdf->Write(0, 'AÑO '.Session::get("ejercicio"), '', 0, 'C', true, 0, false, false, 0);
            $pdf->Ln(10);
            $pdf->Write(0, $data_ejecutor->tx_ejecutor_ac, '', 0, 'C', true, 0, false, false, 0);
            $pdf->SetY(190);
            $pdf->SetFont('','',11);            
            $pdf->Write(0, 'Maracaibo, '.$mes.' de '.Session::get("ejercicio"), '', 0, 'C', true, 0, false, false, 0);

            $data2 = tab_ac::join('mantenimiento.tab_ejecutores as t04', 't04.id_ejecutor', '=', 'ac_seguimiento.tab_ac.id_ejecutor')
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
            'id_tab_tipo_periodo',
            'ac_seguimiento.tab_ac.id_tab_ejercicio_fiscal',
            'ac_seguimiento.tab_ac.de_observacion_002',
            'ac_seguimiento.tab_ac.de_observacion_003',
            'ac_seguimiento.tab_ac.id',
            'ac_seguimiento.tab_ac.de_sector',
            't21.id_tab_ac_ae_predefinida'                    
        )
        ->where('ac_seguimiento.tab_ac.id_ejecutor', '=', $itemData->id_ejecutor)
        ->where('ac_seguimiento.tab_ac.id_tab_lapso', '=', $id_tab_lapso)
        ->where('ac_seguimiento.tab_ac.in_activo', '=', true)
        ->orderby('ac_seguimiento.tab_ac.id_ejecutor', 'ASC')->orderby('ac_seguimiento.tab_ac.id_tab_ac_predefinida', 'ASC')->orderby('t21.id_tab_ac_ae_predefinida', 'ASC')
        ->get(); 
          
          $id_ac = '';
          
          foreach($data2 as $data) {
              
            if($id_ac!=$data->id){
                
            $actividad = tab_forma_005::join('ac_seguimiento.tab_ac as t01', 'ac_seguimiento.tab_forma_005.id_tab_ac', '=', 't01.id')
            ->join('mantenimiento.tab_ejecutores as t02', 't01.id_tab_ejecutores', '=', 't02.id')
            ->join('mantenimiento.tab_lapso as t03', 't01.id_tab_lapso', '=', 't03.id')
            ->join('mantenimiento.tab_estatus as t04', 't04.id', '=', 'ac_seguimiento.tab_forma_005.id_tab_estatus')
            ->select(
                'ac_seguimiento.tab_forma_005.id',
                'ac_seguimiento.tab_forma_005.pp_anual',
                'ac_seguimiento.tab_forma_005.tp_indicador',
                'ac_seguimiento.tab_forma_005.nb_indicador_gestion',
                'ac_seguimiento.tab_forma_005.de_indicador_descripcion',
                'ac_seguimiento.tab_forma_005.de_valor_objetivo',
                'ac_seguimiento.tab_forma_005.de_valor_obtenido',
                'ac_seguimiento.tab_forma_005.de_valor_objetivo_acu',
                'ac_seguimiento.tab_forma_005.de_valor_obtenido_acu',                    
                'ac_seguimiento.tab_forma_005.nu_cumplimiento',
                'ac_seguimiento.tab_forma_005.de_formula',
                'de_estatus',
                'ac_seguimiento.tab_forma_005.id_tab_estatus',
                'ac_seguimiento.tab_forma_005.in_005'
            )                  
            ->where('id_tab_ac', '=', $data->id)
            ->orderby('ac_seguimiento.tab_forma_005.id', 'ASC')
            ->get();

         if($actividad->count()>0){ 
      foreach($actividad as $item) {
          
          if($item->de_valor_objetivo_acu==null || $item->de_valor_objetivo_acu==0){
          $nu_cumplimiento = 0;    
          }else{
          $nu_cumplimiento = round(($item->de_valor_obtenido_acu/$item->de_valor_objetivo_acu)*100,2);
          }
          
$pdf->AddPage();
$this->encabezado5($pdf);
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
<td colspan="3" style="width: 100%;" align="justify"><b>PRODUCTO PROGRAMADO ANUAL DEL OBJETIVO INSTITUCIONAL:</b> '.$data->tx_pr_objetivo.'</td>
</tr>
<tr style="font-size:9px">
<td colspan="3" style="width: 100%;" align="justify"><b>INDICADORES DE GESTIÓN (EFICIENCIA, EFICACIA, EFECTIVIDAD):</b> '.$item->tp_indicador.'</td>
</tr>
<tr style="font-size:9px">
<td style="width: 70%;  height: 30px;" align="justify" rowspan="2"><b>NOMBRE DEL INDICADOR:</b> '.$item->nb_indicador_gestion.'</td>
<td style="width: 10%;" align="center"><b>VALOR OBJETIVO:</b></td>
<td style="width: 10%;" align="center"><b>VALOR OBTENIDO:</b></td>
<td style="width: 10%;" align="center"><b>CUMPLIMIENTO:</b></td>
</tr>
<tr style="font-size:9px">
<td style="width: 10%;" align="center">'.$item->de_valor_objetivo_acu.' </td>
<td style="width: 10%;" align="center">'.$item->de_valor_obtenido_acu.' </td>
<td style="width: 10%;" align="center">'.$nu_cumplimiento.' % </td>
</tr>
<tr style="font-size:9px height: 100px;">
<td colspan="4" style="height: 30px;" align="justify"><b>DESCRIPCIÓN DEL INDICADOR:</b> '.$item->de_indicador_descripcion.'</td>
</tr>
<tr style="font-size:9px  height: 100px;">
<td colspan="4" style="height: 30px;" align="justify"><b>FORMULA:</b> '.$item->de_formula.'</td>
</tr>
</tbody>
</table>
';
        $pdf->writeHTML(Helper::htmlComprimir($html1), true, false, false, false, '');  
      }        
         }
         
            }

        $id_ac = $data->id;

      }            
                       
            }
            
            }
          $pdf->output('SEGUIMIENTO_AC_'.Session::get("ejercicio").'_'.date("H:i:s").'.pdf', 'D'); 

      }

    public function exportar($id_tab_lapso)
    {

        DB::beginTransaction();

        try {

            //Query
             $tab_lapso = tab_lapso::where('id', '<=', $id_tab_lapso)
             ->where('id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))
            ->get();

        $lapso_desc = tab_lapso::where('id', '=', $id_tab_lapso)
        ->first();             
           
             
              $i =  $tab_lapso->count();
              
//              var_dump($i);
//              exit();

            $tab_meta_financiera = tab_meta_financiera::select(
                'tx_nombre',
                't03.tx_ejecutor_ac',
                't03.id_ejecutor',
                't06.de_tipo_ejecutor',
                't05.id_tab_tipo_ejecutor',
                't03.id_tab_ejercicio_fiscal',
                't45.tx_descripcion as tx_area_estrategica',
                't45a.tx_descripcion as tx_ambito_estado',
                DB::raw('sum(coalesce(mo_presupuesto,0))/'.$i.' as mo_presupuesto'),
                DB::raw('sum(coalesce(mo_modificado_anual,0)) as mo_modificado_anual'),
                DB::raw('sum(coalesce(mo_presupuesto,0))/'.$i.' + sum(coalesce(mo_modificado_anual,0)) as mo_actualizado_anual'),
                DB::raw('sum(coalesce(mo_comprometido,0)) as mo_comprometido'),
                DB::raw('sum(coalesce(mo_causado,0)) as mo_causado'),
                DB::raw('sum(coalesce(mo_pagado,0)) as mo_pagado'),
                DB::raw('sum(coalesce(mo_presupuesto,0))/'.$i.' + sum(coalesce(mo_modificado_anual,0)) -  sum(coalesce(mo_pagado,0)) as mo_financiera'),
                DB::raw('sum(coalesce(mo_presupuesto,0))/'.$i.' + sum(coalesce(mo_modificado_anual,0))-  sum(coalesce(mo_comprometido,0)) as mo_presupuestaria'),                    
                'ac_seguimiento.tab_meta_financiera.co_partida'
            )
            ->join('ac_seguimiento.tab_meta_fisica as t01', 'ac_seguimiento.tab_meta_financiera.id_tab_meta_fisica', '=', 't01.id')
            ->join('ac_seguimiento.tab_ac_ae as t02', 't01.id_tab_ac_ae', '=', 't02.id')
            ->join('ac_seguimiento.tab_ac as t03', 't02.id_tab_ac', '=', 't03.id')
            ->join('mantenimiento.tab_ejecutores as t05', 't03.id_ejecutor', '=', 't05.id_ejecutor')
            ->join('mantenimiento.tab_tipo_ejecutor as t06', 't05.id_tab_tipo_ejecutor', '=', 't06.id')
            ->join('mantenimiento.tab_partidas as t04', function ($j) {
                $j->on('t04.co_partida', '=', 'ac_seguimiento.tab_meta_financiera.co_partida')
                  ->on('t04.id_tab_ejercicio_fiscal', '=', 't03.id_tab_ejercicio_fiscal');
            })
            ->leftjoin('ac_seguimiento.tab_ac_vinculo as t49', 't49.id_tab_ac', '=', 't03.id')
            ->leftjoin('t45_planes_zulia as t45', function ($join) {
            $join->on('t49.co_area_estrategica', '=', 't45.co_area_estrategica')
            ->on('t45.nu_nivel', '=', DB::raw('0'));
            })
            ->leftjoin('t45_planes_zulia as t45a', function ($join) {
            $join->on('t49.co_area_estrategica', '=', 't45a.co_area_estrategica')
            ->on('t49.co_ambito_estado', '=', 't45a.co_ambito_zulia')        
            ->on('t45a.nu_nivel', '=', DB::raw('1'));
            })            
            ->where('t03.id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))
            ->where('t03.id_tab_lapso', '<=', $id_tab_lapso)
            ->where('t03.in_activo', '=', true)
            ->groupBy('t03.id_tab_ejercicio_fiscal')
            ->groupBy('t03.id_ejecutor')
            ->groupBy('t03.tx_ejecutor_ac')
            ->groupBy('ac_seguimiento.tab_meta_financiera.co_partida')
            ->groupBy('tx_nombre')
            ->groupBy('tx_area_estrategica')
            ->groupBy('tx_ambito_estado')
            ->groupBy('t05.id_tab_tipo_ejecutor')
            ->groupBy('t06.de_tipo_ejecutor')        
            ->orderBy('ac_seguimiento.tab_meta_financiera.co_partida', 'ASC')
            ->get();

            $acumulado = 0;

            // Instantiate a new PHPExcel object
            $objPHPExcel = new PHPExcel();
            // Set properties
            $objPHPExcel->getProperties()->setCreator("Yoser Perez");
            $objPHPExcel->getProperties()->setLastModifiedBy("SPE");
            $objPHPExcel->getProperties()->setTitle("Listado POA Proyectos");
            $objPHPExcel->getProperties()->setSubject("Reporte");
            $objPHPExcel->getProperties()->setDescription("Reporte para documento de Office 2007 XLSX.");
            // Set the active Excel worksheet to sheet 0
            $objPHPExcel->setActiveSheetIndex(0);
            // Rename sheet
            //$objPHPExcel->getActiveSheet()->getColumnDimension("A")->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(15);
            //$objPHPExcel->getActiveSheet()->getColumnDimension("B")->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(55);
            $objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(15);
            //$objPHPExcel->getActiveSheet()->getColumnDimension("G")->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension("H")->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension("I")->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension("J")->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension("K")->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension("L")->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension("M")->setWidth(20);
            $objPHPExcel->getActiveSheet()->setTitle('REPORTE_CONSOLIDADO');
            $objPHPExcel->getActiveSheet()->getStyle('A1:M1')->applyFromArray(
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

            $objPHPExcel->getActiveSheet()->getStyle('L1')->applyFromArray(
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
            ->setCellValue('B1', 'Periodo')
            ->setCellValue('C1', 'Unidad Ejecutora')
            ->setCellValue('D1', 'Area Estrategica')
            ->setCellValue('E1', 'Ambito')
            ->setCellValue('F1', 'Partida')
            ->setCellValue('G1', 'Presupuesto Inicial')
            ->setCellValue('H1', 'Presupuesto Modificado')
            ->setCellValue('I1', 'Presupuesto Aprobado')
            ->setCellValue('J1', 'Comprometido')
            ->setCellValue('K1', 'Causado')
            ->setCellValue('L1', 'Pagado')
            ->setCellValue('M1', 'Tipo');

            // Make bold cells
            $objPHPExcel->getActiveSheet()->getStyle('A1:M1')->getFont()->setBold(true);


            foreach ($tab_meta_financiera as $key => $value) {
                // Set cell An to the "name" column from the database (assuming you have a column called name)
                $acumulado = $acumulado+$value->mo_ingreso;

                // Set thin black border outline around column
                $styleThinBlackBorderOutline = array(
                  'borders' => array(
                    'outline' => array(
                      'style' => PHPExcel_Style_Border::BORDER_THIN,
                      'color' => array('argb' => 'FF000000'),
                    ),
                  ),
                );
                $objPHPExcel->getActiveSheet()->getStyle('A1:L1')->applyFromArray($styleThinBlackBorderOutline);

                $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $value->id_tab_ejercicio_fiscal);
                $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $lapso_desc->de_lapso);
                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $value->id_ejecutor.'-'.$value->tx_ejecutor_ac);
                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $value->tx_area_estrategica);
                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $value->tx_ambito_estado);
                $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $value->co_partida);
                $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $value->mo_presupuesto);
                $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, $value->mo_modificado_anual);
                $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, $value->mo_actualizado_anual);
                $objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, $value->mo_comprometido);
                $objPHPExcel->getActiveSheet()->SetCellValue('K'.$rowCount, $value->mo_causado);
                $objPHPExcel->getActiveSheet()->SetCellValue('L'.$rowCount, $value->mo_pagado);
                $objPHPExcel->getActiveSheet()->SetCellValue('M'.$rowCount, $value->de_tipo_ejecutor);

                $rowCount++;

            }


            // Instantiate a Writer to create an OfficeOpenXML Excel .xlsx file
            $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
            // We'll be outputting an excel file
            header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            // It will be called file.xls
            header('Content-Disposition: attachment; filename="reporte_consolidado_'.date("H:i:s").'.xlsx"');
            $objWriter->save('php://output');

            DB::commit();

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();
            return Response::json(array(
              'success' => false,
              'msg' => array('ERROR ('.$e->getCode().'):'=> $e->getMessage())
            ));
        }

    }
    
    public function exportarA($id_tab_lapso)
    {

        DB::beginTransaction();

        try {

            //Query
             $tab_lapso = tab_lapso::where('id', '<=', $id_tab_lapso)
             ->where('id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))
            ->get();

        $lapso_desc = tab_lapso::where('id', '=', $id_tab_lapso)
        ->first();             
           
             
              $i =  $tab_lapso->count();
              
//              var_dump($i);
//              exit();

            $tab_meta_financiera = tab_meta_financiera::select(
                'tx_nombre',
                't03.tx_ejecutor_ac',
                't03.id_ejecutor',
                't06.de_tipo_ejecutor',
                't05.id_tab_tipo_ejecutor',
                't03.id_tab_ejercicio_fiscal',
                't01.nb_meta',
                't03.nu_codigo',    
                DB::raw('sum(coalesce(mo_presupuesto,0))/'.$i.' as mo_presupuesto'),
                DB::raw('sum(coalesce(mo_modificado_anual,0)) as mo_modificado_anual'),
                DB::raw('sum(coalesce(mo_presupuesto,0))/'.$i.' + sum(coalesce(mo_modificado_anual,0)) as mo_actualizado_anual'),
                DB::raw('sum(coalesce(mo_comprometido,0)) as mo_comprometido'),
                DB::raw('sum(coalesce(mo_causado,0)) as mo_causado'),
                DB::raw('sum(coalesce(mo_pagado,0)) as mo_pagado'),
                DB::raw('sum(coalesce(mo_presupuesto,0))/'.$i.' + sum(coalesce(mo_modificado_anual,0)) -  sum(coalesce(mo_pagado,0)) as mo_financiera'),
                DB::raw('sum(coalesce(mo_presupuesto,0))/'.$i.' + sum(coalesce(mo_modificado_anual,0))-  sum(coalesce(mo_comprometido,0)) as mo_presupuestaria'),                    
                'ac_seguimiento.tab_meta_financiera.co_partida'
            )
            ->join('ac_seguimiento.tab_meta_fisica as t01', 'ac_seguimiento.tab_meta_financiera.id_tab_meta_fisica', '=', 't01.id')
            ->join('ac_seguimiento.tab_ac_ae as t02', 't01.id_tab_ac_ae', '=', 't02.id')
            ->join('ac_seguimiento.tab_ac as t03', 't02.id_tab_ac', '=', 't03.id')
            ->join('mantenimiento.tab_ejecutores as t05', 't03.id_ejecutor', '=', 't05.id_ejecutor')
            ->join('mantenimiento.tab_tipo_ejecutor as t06', 't05.id_tab_tipo_ejecutor', '=', 't06.id')
            ->join('mantenimiento.tab_partidas as t04', function ($j) {
                $j->on('t04.co_partida', '=', 'ac_seguimiento.tab_meta_financiera.co_partida')
                  ->on('t04.id_tab_ejercicio_fiscal', '=', 't03.id_tab_ejercicio_fiscal');
            })
            ->leftjoin('ac_seguimiento.tab_ac_vinculo as t49', 't49.id_tab_ac', '=', 't03.id')
            ->leftjoin('t45_planes_zulia as t45', function ($join) {
            $join->on('t49.co_area_estrategica', '=', 't45.co_area_estrategica')
            ->on('t45.nu_nivel', '=', DB::raw('0'));
            })
            ->leftjoin('t45_planes_zulia as t45a', function ($join) {
            $join->on('t49.co_area_estrategica', '=', 't45a.co_area_estrategica')
            ->on('t49.co_ambito_estado', '=', 't45a.co_ambito_zulia')        
            ->on('t45a.nu_nivel', '=', DB::raw('1'));
            })            
            ->where('t03.id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))
            ->where('t03.id_tab_lapso', '<=', $id_tab_lapso)
            ->where('t03.in_activo', '=', true)
            ->groupBy('t03.id_tab_ejercicio_fiscal')
            ->groupBy('t03.id_ejecutor')
            ->groupBy('t03.tx_ejecutor_ac')
            ->groupBy('ac_seguimiento.tab_meta_financiera.co_partida')
            ->groupBy('tx_nombre')
            ->groupBy('t05.id_tab_tipo_ejecutor')
            ->groupBy('t06.de_tipo_ejecutor')
            ->groupBy('t01.nb_meta')
            ->groupBy('t03.nu_codigo')
            ->orderBy('ac_seguimiento.tab_meta_financiera.co_partida', 'ASC')
            ->get();

            $acumulado = 0;

            // Instantiate a new PHPExcel object
            $objPHPExcel = new PHPExcel();
            // Set properties
            $objPHPExcel->getProperties()->setCreator("Yoser Perez");
            $objPHPExcel->getProperties()->setLastModifiedBy("SPE");
            $objPHPExcel->getProperties()->setTitle("Listado POA Proyectos");
            $objPHPExcel->getProperties()->setSubject("Reporte");
            $objPHPExcel->getProperties()->setDescription("Reporte para documento de Office 2007 XLSX.");
            // Set the active Excel worksheet to sheet 0
            $objPHPExcel->setActiveSheetIndex(0);
            // Rename sheet
            //$objPHPExcel->getActiveSheet()->getColumnDimension("A")->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(15);
            //$objPHPExcel->getActiveSheet()->getColumnDimension("B")->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(55);
            $objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(15);
            //$objPHPExcel->getActiveSheet()->getColumnDimension("G")->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension("H")->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension("I")->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension("J")->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension("K")->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension("L")->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension("M")->setWidth(20);
            $objPHPExcel->getActiveSheet()->setTitle('REPORTE_CONSOLIDADO_ACTIVIDAD');
            $objPHPExcel->getActiveSheet()->getStyle('A1:M1')->applyFromArray(
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

            $objPHPExcel->getActiveSheet()->getStyle('L1')->applyFromArray(
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
            ->setCellValue('B1', 'Periodo')
            ->setCellValue('C1', 'Unidad Ejecutora')
            ->setCellValue('D1', 'Accion Centralizada')        
            ->setCellValue('E1', 'Actividad')
            ->setCellValue('F1', 'Partida')
            ->setCellValue('G1', 'Presupuesto Inicial')
            ->setCellValue('H1', 'Presupuesto Modificado')
            ->setCellValue('I1', 'Presupuesto Aprobado')
            ->setCellValue('J1', 'Comprometido')
            ->setCellValue('K1', 'Causado')
            ->setCellValue('L1', 'Pagado')
            ->setCellValue('M1', 'Tipo');

            // Make bold cells
            $objPHPExcel->getActiveSheet()->getStyle('A1:M1')->getFont()->setBold(true);


            foreach ($tab_meta_financiera as $key => $value) {
                // Set cell An to the "name" column from the database (assuming you have a column called name)
                $acumulado = $acumulado+$value->mo_ingreso;

                // Set thin black border outline around column
                $styleThinBlackBorderOutline = array(
                  'borders' => array(
                    'outline' => array(
                      'style' => PHPExcel_Style_Border::BORDER_THIN,
                      'color' => array('argb' => 'FF000000'),
                    ),
                  ),
                );
                $objPHPExcel->getActiveSheet()->getStyle('A1:L1')->applyFromArray($styleThinBlackBorderOutline);

                $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $value->id_tab_ejercicio_fiscal);
                $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $lapso_desc->de_lapso);
                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $value->id_ejecutor.'-'.$value->tx_ejecutor_ac);
                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $value->nu_codigo);
                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $value->nb_meta);
                $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $value->co_partida);
                $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $value->mo_presupuesto);
                $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, $value->mo_modificado_anual);
                $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, $value->mo_actualizado_anual);
                $objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, $value->mo_comprometido);
                $objPHPExcel->getActiveSheet()->SetCellValue('K'.$rowCount, $value->mo_causado);
                $objPHPExcel->getActiveSheet()->SetCellValue('L'.$rowCount, $value->mo_pagado);
                $objPHPExcel->getActiveSheet()->SetCellValue('M'.$rowCount, $value->de_tipo_ejecutor);

                $rowCount++;

            }


            // Instantiate a Writer to create an OfficeOpenXML Excel .xlsx file
            $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
            // We'll be outputting an excel file
            header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            // It will be called file.xls
            header('Content-Disposition: attachment; filename="reporte_consolidado_a_'.date("H:i:s").'.xlsx"');
            $objWriter->save('php://output');

            DB::commit();

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();
            return Response::json(array(
              'success' => false,
              'msg' => array('ERROR ('.$e->getCode().'):'=> $e->getMessage())
            ));
        }

    }    
      
}
