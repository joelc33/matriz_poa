<?php

namespace matriz\Http\Controllers\Reporte;

//*******agregar esta linea******//
use matriz\Models\AcSegto\tab_meta_fisica;
use matriz\Models\AcSegto\tab_meta_financiera;
use matriz\Models\AcSegto\tab_forma_005;
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
        $pdf->MultiCell(277, 5, 'FORMULARIO N° 5', 0, 'C', 0, 0, '', '', true);
        $pdf->Ln(5);
        $pdf->MultiCell(277, 5, 'INDICADORES DE GESTIÓN', 0, 'C', 0, 0, '', '', true);
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

class acseguimiento005Controller extends Controller
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
      /**
       * Display a listing of the resource.
       *
       * @return \Illuminate\Http\Response
       */      
      
      public function ficha005($id)
      {

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
            'id_tab_tipo_periodo',
            'ac_seguimiento.tab_ac.de_sector'
        )
        ->where('ac_seguimiento.tab_ac.id', '=', $id)
        ->first();          
                    
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
            ->where('id_tab_ac', '=', $id)
            ->orderby('ac_seguimiento.tab_forma_005.id', 'ASC')
            ->get();
          


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
//          $pdf->AddPage();

          $pdf->SetFont('','',11);

          
          $pdf->Ln(-3);


         if($actividad->count()>0){ 
      foreach($actividad as $item) {
          
$pdf->AddPage();
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
         }else{
             $pdf->AddPage();
         }

          $pdf->lastPage();
          $pdf->output('SEGUIMIENTO_AC_'.Session::get("ejercicio").'_'.date("H:i:s").'.pdf', 'D');
      }

      public function ficha005Acumulada($id)
      {

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
            'id_tab_tipo_periodo',
            'ac_seguimiento.tab_ac.de_sector'
        )
        ->where('ac_seguimiento.tab_ac.id', '=', $id)
        ->first();          
                    
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
            ->where('id_tab_ac', '=', $id)
            ->orderby('ac_seguimiento.tab_forma_005.id', 'ASC')
            ->get();
          


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
//          $pdf->AddPage();

          $pdf->SetFont('','',11);

          
          $pdf->Ln(-3);


         if($actividad->count()>0){ 
      foreach($actividad as $item) {
          
          if($item->de_valor_objetivo_acu==null || $item->de_valor_objetivo_acu==0){
          $nu_cumplimiento = 0;    
          }else{
          $nu_cumplimiento = round(($item->de_valor_obtenido_acu/$item->de_valor_objetivo_acu)*100,2);
          }
          
          
$pdf->AddPage();
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
         }else{
             $pdf->AddPage();
         }

          $pdf->lastPage();
          $pdf->output('SEGUIMIENTO_AC_'.Session::get("ejercicio").'_'.date("H:i:s").'.pdf', 'D');
      }       

}
