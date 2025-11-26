<?php
session_start();
if( $_SESSION['estatus'] !== 'OK' ) {
    http_response_code(403);
	die();
}
include("../../configuracion/ConexionComun.php");
define('FPDF_FONTPATH','font/');
require_once('../../plugins/tcpdf/examples/lang/spa.php');
require_once('../../plugins/tcpdf/tcpdf.php');

$original_mem = ini_get('memory_limit');
ini_set('memory_limit','1024M');
ini_set('max_execution_time', 600);

class MYPDF extends TCPDF {
	public $conexion;
//=========================================== Datos del Reporte ====================================================/

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
	    return "Bs. ".$numero;
	}

	function getRegistro($id_ejecutor){


		$condicionAC='';
		if($id_ejecutor!= '')
		{

			$condicionAC.= " t46.id_ejecutor = '".$id_ejecutor."' AND ";
		}

		$comunes = new ConexionComun();

		$sql = "select t3.tx_codigo as nu_sector,t3.tx_descripcion as tx_sector,t46.id as id_accion_centralizada,
                t47.id_accion,t46.id_ejercicio::integer as nu_anio,t1.de_nombre as de_programa,
                t2.tx_ejecutor,t4.nu_numero,t4.de_nombre as de_actividad,
                t2.id_ejecutor,t3.tx_codigo||'.'||t1.nu_original||'.00.'||t4.nu_numero as co_presupuesto,t1.nu_original,t3.id as id_sector
		from t46_acciones_centralizadas as t46
                left join t47_ac_accion_especifica as t47 on t46.id = t47.id_accion_centralizada
		join mantenimiento.tab_ac_predefinida as t1 on t1.id = t46.id_accion
		join mantenimiento.tab_ejecutores as t2 on t2.id_ejecutor = t46.id_ejecutor
		inner join mantenimiento.tab_sectores as t3 on t46.id_subsector=t3.id
                join mantenimiento.tab_ac_ae_predefinida as t4 on t4.id = t47.id_accion
	where t46.edo_reg is true and ".$condicionAC." t46.id_ejercicio = ".$_SESSION['ejercicio_fiscal']." group by 1,2,3,4,5,6,7,8,t4.de_nombre,10,t1.nu_original,t3.id  order by 1 asc, t46.id_ejecutor asc, 4 asc";

/*echo $sql;
exit();*/
		$this->datos = $comunes->ObtenerFilasBySqlSelect($sql);
		$this->cantidadTotal = $comunes->getFilas($sql);
	}

	public function Footer()
	{
		/*$this->getRegistro('PR130120150002','');
		foreach($this->datos as $key => $campo){
			$tipo = $campo["co_tipo"];
		}*/
		pie($this,'h',2);
		//$this->Cell(0, 10, 'Pagina '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
	}
	public function setHeader()
	{
		encabezado($this,'h',1);
	}
        public function cuerpo()
        {

		if($_GET['id_ejecutor']!= '')
		{
			$id_ejecutor = decode($_GET['id_ejecutor']);
		}


	$this->getRegistro($id_ejecutor);
       	$comunes = new ConexionComun();

        $portada=0;
        $nu_sector = '';
                
        $cant_sector = 0;
        
	foreach($this->datos as $key => $campo){
            
            if($nu_sector<>$campo['nu_sector']){
                
                
                 if($nu_sector==''){



                }else{

                    $this->AddPage();
                    
		if($id_ejecutor!= '')
		{

			$condicionSector.= " t46.id_ejecutor = '".$id_ejecutor."' AND ";
		}                    
                    
		$sql_sector = "select distinct t46.id as id_accion_centralizada,
                t1.de_nombre as de_programa,t46.id_ejecutor,
                t2.tx_ejecutor,t3.tx_codigo,tx_ejecutor_poa,t46.monto,t3.tx_descripcion
		from t46_acciones_centralizadas as t46
                left join t47_ac_accion_especifica as t47 on t46.id = t47.id_accion_centralizada
		join mantenimiento.tab_ac_predefinida as t1 on t1.id = t46.id_accion
		join mantenimiento.tab_ejecutores as t2 on t2.id_ejecutor = t46.id_ejecutor
		inner join mantenimiento.tab_sectores as t3 on t46.id_subsector=t3.id
                join mantenimiento.tab_ac_ae_predefinida as t4 on t4.id = t47.id_accion
                where t46.edo_reg is true and ".$condicionSector." t3.tx_codigo = '".$nu_sector."' and t46.id_ejercicio = ".$_SESSION['ejercicio_fiscal']." order by t46.id_ejecutor asc";                    
/*echo $sql_sector;
exit(); */                
                $this->datos_sector = $comunes->ObtenerFilasBySqlSelect($sql_sector);
                
	$htmlSector = '
<table border="0.1" style="width:100%;text-align: center;" cellpadding="3">
	<tr align="center" bgcolor="#BDBDBD">
		<td colspan="2"><b>VINCULACIÓN PLAN-PRESUPUESTO</b></td>
	</tr>  
<thead>
<tr style="font-size:9px">
<th colspan="11" align="center" bgcolor="#BDBDBD" style="width: 40%;"><b>PROGRAMAS / ACTIVIDADES</b></th>
<th colspan="11" align="center" bgcolor="#BDBDBD" style="width: 40%;"><b>UNIDAD EJECUTORA</b></th>
<th colspan="11" align="right" bgcolor="#BDBDBD" style="width: 20%;"><b>ASIGNACIÓN PRESUPUESTARIA</b></th>
</tr>
</thead>'; 
        
$htmlSector.='
<tbody>';        

$total_sector=0;

foreach($this->datos_sector as $key => $campo4){
    
    		$htmlSector.='
		<tr style="font-size:8px">
                <td style="width: 40%;" align="left" ><b>'.$campo4['de_programa'].'</b></td>
                <td style="width: 40%;" align="center" ><b>'.$campo4['id_ejecutor'].'-'.$campo4['tx_ejecutor_poa'].'</b></td>
                <td style="width: 20%;" align="right" ><b>'.number_format($campo4['monto'], 2, ',','.').'</b></td>
                </tr>';
                $tx_descripcion = $campo4['tx_descripcion'];
                $total_sector = $total_sector + $campo4['monto'];
                
                
		$sql_partidas = "select SUBSTRING(t54.co_partida, 1,3) as co_partida,t54.id_tab_ejercicio_fiscal,tx_nombre,sum(monto) as monto
		from t54_ac_ae_partidas t54
                inner join mantenimiento.tab_partidas as t02 on SUBSTRING(t54.co_partida, 1,3)=t02.co_partida and t54.id_tab_ejercicio_fiscal = t02.id_tab_ejercicio_fiscal
                where edo_reg is true and id_accion_centralizada = ".$campo4['id_accion_centralizada']." and t54.id_tab_ejercicio_fiscal = ".$_SESSION['ejercicio_fiscal']." group by 1,2,3 order by 1 asc";                    
/*echo $sql_partidas;
exit(); */                
                $this->datos_partidas = $comunes->ObtenerFilasBySqlSelect($sql_partidas);

                foreach($this->datos_partidas as $key => $campo5){
                    
    		$htmlSector.='
		<tr style="font-size:8px">
                <td style="width: 80%;" align="left" > - '.$campo5['tx_nombre'].'</td>
                <td style="width: 20%;" align="right" >'.number_format($campo5['monto'], 2, ',','.').'</td>
                </tr>';                    
                    

                }                
                
    
}

    		$htmlSector.='
		<tr style="font-size:9px">
                <td style="width: 80%;" bgcolor="#BDBDBD" align="left" ><b>TOTAL, SECTOR '.$nu_sector.': '.$tx_descripcion.'</b></td>
                <td style="width: 20%;" bgcolor="#BDBDBD" align="right" ><b>'.number_format($total_sector, 2, ',','.').'</b></td>
                </tr>';

$htmlSector.='
</tbody>
</table>';
        
		$this->SetFont('','',11);
		$this->writeHTML($htmlSector, true, false, false, false, ''); 
                
                

                }
                
               
                
                $this->AddPage();
                
		$bMargin = $this->getBreakMargin();
		$auto_page_break = $this->AutoPageBreak;
		$this->SetAutoPageBreak(false, 0);
		$this->SetAutoPageBreak($auto_page_break, $bMargin);
		$this->setPageMark();                 
/******Portada*********/
		$this->SetY(75);
		$this->SetFont('','B',20);
		$this->SetTextColor(0,0,0);
		$this->Write(0, 'PLAN OPERATIVO ANUAL', '', 0, 'C', true, 0, false, false, 0);
		$this->Write(0, 'AÑO '.$campo['nu_anio'], '', 0, 'C', true, 0, false, false, 0);
		$this->SetFont('','B',20);
		$this->SetTextColor(0,0,0);
                $this->Ln(10);
//                $this->Image('../../images/escudo_vertical.png', 0, 10, 0, 0, '', '', '', false, 300, 'C', false, false, 0);
//                $this->Image('../../images/escudo_vertical.png', 60, 60, 80, 80, 'PNG', '', '', true, 150, '', false, false, 0, false, false, false);
		$this->Write(0, 'SECTOR '.$campo['nu_sector'], '', 0, 'C', true, 0, false, false, 0);
                $this->SetFont('','BU',20);
		$this->Write(0, $campo['tx_sector'], '', 0, 'C', true, 0, false, false, 0);
                $anio = $campo['nu_anio'] -1;
		$this->SetY(190);
		$this->SetFont('','',11);
		$this->Write(0, 'Maracaibo, '.'Diciembre'.' de '.$anio, '', 0, 'C', true, 0, false, false, 0);
		
                $nu_sector = $campo['nu_sector'];
                
                $cant_sector++;
                

            }
            $this->AddPage();
         

                
		$sqlAc = "SELECT inst_objetivos, inst_mision, inst_vision FROM t46_acciones_centralizadas
		WHERE id= ".$campo['id_accion_centralizada'];     
                $this->datos_ac = $comunes->ObtenerFilasBySqlSelect($sqlAc);
            
/******Objetivos*********/

	$htmlObjetivo = '
<table border="0.1" style="width:100%;text-align: center;" cellpadding="3">
	<tr align="center" bgcolor="#BDBDBD">
		<td colspan="2"><b>PLAN OPERATIVO ANUAL '.$campo['nu_anio'].'</b></td>
	</tr>
	<tr align="left">
		<td colspan="2"><b>SECTOR: </b>'.$campo['nu_sector'].'</td>
	</tr>
	<tr align="left">
		<td colspan="2"><b>UNIDAD EJECUTORA RESPONSABLE: </b>'.$campo['tx_ejecutor'].'</td>
	</tr>   
<thead>
	<tr>
		<td colspan="2"><b>OBJETIVO GENERAL</b></td>
	</tr>
</thead>
<tbody>
	<tr nobr="true">
		<td colspan="2" height="100" align="justify">'.str_replace(array("\r\n","\r","\n","\\r","\\n","\\r\\n"),"<br/>",$this->datos_ac[0]['inst_objetivos']).'</td>
	</tr>        
	<tr>
		<td><b>MISIÓN</b></td>
		<td><b>VISIÓN</b></td>
	</tr>
	<tr>
		<td height="100" align="justify">'.$this->datos_ac[0]['inst_mision'].'</td>
		<td height="100" align="justify">'.$this->datos_ac[0]['inst_vision'].'</td>
	</tr>

</tbody>
</table>';
		$this->SetFont('','',11);
		//$this->Ln(-20);
		$this->writeHTML($htmlObjetivo, true, false, false, false, '');
                $portada=$portada+1;
		$this->AddPage();            

            
/******POA*********/
$html1 = '
<table border="0.1" style="width:100%" style="font-size:10px" cellpadding="3">
<tbody>
<tr align="center" bgcolor="#BDBDBD">
<td colspan="3"><b>PLAN OPERATIVO ANUAL '.$campo['nu_anio'].'</b></td>
</tr>
<tr style="font-size:9px">
<td style="width: 85%;"><b>UNIDAD EJECUTORA RESPONSABLE: </b>'.$campo['id_ejecutor'].' - '.$campo['tx_ejecutor'].'</td>
<td style="width: 15%;"><b>SECTOR:</b> '.$campo['nu_sector'].'</td>
</tr>
<tr style="font-size:9px">
<td colspan="2"><b>PROGRAMA: </b> '.$campo['nu_original'].' - '.$campo['de_programa'].'</td>
</tr>
<tr style="font-size:9px">
<td style="width: 70%;"><b>ACTIVIDAD PROGRAMATICA.:</b> '.$campo['nu_numero'].' - '.$campo['de_actividad'].'</td>
<td style="width: 30%;"><b>PROGRAMATICA: </b> '.$campo['co_presupuesto'].' </td>
</tr>
</tbody>
</table>
';

		$this->writeHTML($html1, true, false, false, false, '');
                $this->Ln(-3); 
                
$html23='';
$html23.= '
<!-- Tabla 2 -->
<table border="0.1" style="width:100%" style="font-size:9px" cellpadding="3">
<thead>
<tr align="center" bgcolor="#BDBDBD">
<th colspan="5" style="width: 100%;"><b>METAS FISICAS</b></th>
</tr>
<tr style="font-size:6px">
<th colspan="11" align="center" bgcolor="#BDBDBD" style="width: 10%;" rowspan="2">DEPARTAMENTO/  GERENCIA/UNIDAD ADMINISTRATIVA</th>
<th colspan="11" align="center" bgcolor="#BDBDBD" style="width: 20%;" rowspan="2">OBJETIVO ESPECIFICO</th>
<th colspan="11" align="center" bgcolor="#BDBDBD" style="width: 20%;" rowspan="2">ACTIVIDAD</th>
<th colspan="11" align="center" bgcolor="#BDBDBD" style="width: 18%;" rowspan="2">INDICADOR</th>
<th colspan="11" align="center" bgcolor="#BDBDBD" style="width: 5%;"  rowspan="2">META ANUAL</th>
<th colspan="11" align="center" bgcolor="#BDBDBD" style="width: 7%;" rowspan="2">UNIDAD DE MEDIDA</th>
<th colspan="11" align="center" bgcolor="#BDBDBD" style="width: 5%;">I TRIM</th>
<th colspan="11" align="center" bgcolor="#BDBDBD" style="width: 5%;">2 TRIM</th>
<th colspan="11" align="center" bgcolor="#BDBDBD" style="width: 5%;">3 TRIM</th>
<th colspan="11" align="center" bgcolor="#BDBDBD" style="width: 5%;">4 TRIM</th>
</tr>
<tr style="font-size:6px">
<th align="center" bgcolor="#BDBDBD" style="width: 5%;">PROG</th>
<th align="center" bgcolor="#BDBDBD" style="width: 5%;">PROG</th>
<th align="center" bgcolor="#BDBDBD" style="width: 5%;">PROG</th>
<th align="center" bgcolor="#BDBDBD" style="width: 5%;">PROG</th>
</tr>

</thead>
';

$html23.='
<tbody>';

		$sqlOficinas = "SELECT id_tab_ac_ae_oficina,t1.de_nombre as de_oficina,t47.objetivo_institucional,t69.id_tab_t47_ac_accion_especifica
        FROM t69_metas_ac as t69
		inner join mantenimiento.tab_unidad_medida as t21 on t69.co_unidades_medida=t21.id
        inner join mantenimiento.tab_ac_ae_oficina as t1 on t1.id=t69.id_tab_ac_ae_oficina
        inner join public.t47_ac_accion_especifica as t47 on t47.id_tab_t47_ac_accion_especifica=t69.id_tab_t47_ac_accion_especifica
		WHERE t69.id_accion_centralizada='".$campo['id_accion_centralizada']."' and co_ac_acc_espec='".$campo['id_accion']."' and t69.edo_reg is true 
        group by id_tab_ac_ae_oficina,t1.de_nombre,t47.objetivo_institucional,t69.id_tab_t47_ac_accion_especifica 
        order by id_tab_ac_ae_oficina ASC,t69.id_tab_t47_ac_accion_especifica asc";
                
 /*echo $sqlOficinas;
exit();*/
                
    $id_oficina = 0; 
    $id_nueva_oficina = 0;
    $contador = 0;
    $id_tab_t47_ac_accion_especifica = 0;
                
$this->datos_oficinas = $comunes->ObtenerFilasBySqlSelect($sqlOficinas);
foreach($this->datos_oficinas as $key => $campo2){
    
		$sqlCantOficinas = "SELECT id_tab_ac_ae_oficina,t1.de_nombre as de_oficina,t47.objetivo_institucional
        FROM t69_metas_ac as t69
		inner join mantenimiento.tab_unidad_medida as t21 on t69.co_unidades_medida=t21.id
        inner join mantenimiento.tab_ac_ae_oficina as t1 on t1.id=t69.id_tab_ac_ae_oficina
        inner join public.t47_ac_accion_especifica as t47 on t47.id_tab_t47_ac_accion_especifica=t69.id_tab_t47_ac_accion_especifica
		WHERE t69.id_accion_centralizada='".$campo['id_accion_centralizada']."' and co_ac_acc_espec='".$campo['id_accion']."' and id_tab_ac_ae_oficina=".$campo2['id_tab_ac_ae_oficina']." and t69.edo_reg is true 
        order by de_nombre ASC";    


    $cantidadOficinas = $comunes->getFilas($sqlCantOficinas);
    
/*     echo $sqlCantOficinas;
exit();*/
    
    
		$sqlActividades = "SELECT co_metas,id_tab_t47_ac_accion_especifica,nb_meta,nb_responsable,tx_prog_anual,
    (select sum(monto)::integer from t71_metas_distribucion_fisica where co_metas = t69.co_metas and mes in (1,2,3)) as primer_trimestre,
    (select sum(monto)::integer from t71_metas_distribucion_fisica where co_metas = t69.co_metas and mes in (4,5,6)) as segundo_trimestre,
    (select sum(monto)::integer from t71_metas_distribucion_fisica where co_metas = t69.co_metas and mes in (7,8,9)) as tercer_trimestre,
    (select sum(monto)::integer from t71_metas_distribucion_fisica where co_metas = t69.co_metas and mes in (10,11,12)) as cuarto_trimestre,
    de_unidad_medida
        FROM t69_metas_ac as t69
		inner join mantenimiento.tab_unidad_medida as t21 on t69.co_unidades_medida=t21.id
		WHERE t69.id_tab_t47_ac_accion_especifica=".$campo2['id_tab_t47_ac_accion_especifica']." and t69.id_tab_ac_ae_oficina=".$campo2['id_tab_ac_ae_oficina']." and t69.edo_reg is true 
        order by co_metas ASC"; 
                
$cantidadActividades = $comunes->getFilas($sqlActividades);                
                
/*      echo $sqlActividades;
exit();*/               
$this->datos_actividades = $comunes->ObtenerFilasBySqlSelect($sqlActividades);

foreach($this->datos_actividades as $key => $campo3){


    if($id_oficina==$campo2['id_tab_ac_ae_oficina']){
        
        if($id_tab_t47_ac_accion_especifica==$campo3['id_tab_t47_ac_accion_especifica']){

		$html23.='
		<tr style="font-size:6px">
                <td style="width: 20%;" align="left" >'.$campo3['nb_meta'].'</td>
		<td style="width: 18%;" align="left">'.$campo3['nb_responsable'].'</td>
                <td style="width: 5%;" align="center" >'.$campo3['tx_prog_anual'].'</td>
                <td style="width: 7%;" align="center" >'.$campo3['de_unidad_medida'].'</td>                    
                <td style="width: 5%;" align="center" >'.$campo3['primer_trimestre'].'</td>
                <td style="width: 5%;" align="center" >'.$campo3['segundo_trimestre'].'</td>
                <td style="width: 5%;" align="center" >'.$campo3['tercer_trimestre'].'</td>
                <td style="width: 5%;" align="center" >'.$campo3['cuarto_trimestre'].'</td>
                </tr>';   
            
        }else{
        
		$html23.='
		<tr style="font-size:6px">
		<td style="width: 20%;" align="left" rowspan="'.$cantidadActividades.'">'.$campo2['objetivo_institucional'].'</td>
                <td style="width: 20%;" align="left" >'.$campo3['nb_meta'].'</td>
		<td style="width: 18%;" align="left">'.$campo3['nb_responsable'].'</td>
                <td style="width: 5%;" align="center" >'.$campo3['tx_prog_anual'].'</td>
                <td style="width: 7%;" align="center" >'.$campo3['de_unidad_medida'].'</td>                    
                <td style="width: 5%;" align="center" >'.$campo3['primer_trimestre'].'</td>
                <td style="width: 5%;" align="center" >'.$campo3['segundo_trimestre'].'</td>
                <td style="width: 5%;" align="center" >'.$campo3['tercer_trimestre'].'</td>
                <td style="width: 5%;" align="center" >'.$campo3['cuarto_trimestre'].'</td>
                </tr>';   
        }
        
    }else{
        

        
        if($id_tab_t47_ac_accion_especifica==$campo3['id_tab_t47_ac_accion_especifica']){
                      
            if($id_nueva_oficina==1){
                
		$html23.='
		<tr style="font-size:6px">
		<td style="width: 10%;" align="center" rowspan="'.$cantidadOficinas.'">'.$campo2['de_oficina'].'</td>
		<td style="width: 20%;" align="left" rowspan="'.$cantidadActividades.'">'.$campo2['objetivo_institucional'].'</td>
                <td style="width: 20%;" align="left" >'.$campo3['nb_meta'].'</td>
		<td style="width: 18%;" align="left">'.$campo3['nb_responsable'].'</td>
                <td style="width: 5%;" align="center" >'.$campo3['tx_prog_anual'].'</td>
                <td style="width: 7%;" align="center" >'.$campo3['de_unidad_medida'].'</td>                    
                <td style="width: 5%;" align="center" >'.$campo3['primer_trimestre'].'</td>
                <td style="width: 5%;" align="center" >'.$campo3['segundo_trimestre'].'</td>
                <td style="width: 5%;" align="center" >'.$campo3['tercer_trimestre'].'</td>
                <td style="width: 5%;" align="center" >'.$campo3['cuarto_trimestre'].'</td>
                </tr>';   
                
                $id_nueva_oficina = 0;
                
            }else{
            
		$html23.='
		<tr style="font-size:6px">
                <td style="width: 20%;" align="left" >'.$campo3['nb_meta'].'</td>
		<td style="width: 18%;" align="left">'.$campo3['nb_responsable'].'</td>
                <td style="width: 5%;" align="center" >'.$campo3['tx_prog_anual'].'</td>
                <td style="width: 7%;" align="center" >'.$campo3['de_unidad_medida'].'</td>                    
                <td style="width: 5%;" align="center" >'.$campo3['primer_trimestre'].'</td>
                <td style="width: 5%;" align="center" >'.$campo3['segundo_trimestre'].'</td>
                <td style="width: 5%;" align="center" >'.$campo3['tercer_trimestre'].'</td>
                <td style="width: 5%;" align="center" >'.$campo3['cuarto_trimestre'].'</td>
                </tr>'; 
                
                
            }
                
        }else{
        
		$html23.='
		<tr style="font-size:6px">
		<td style="width: 10%;" align="center" rowspan="'.$cantidadOficinas.'">'.$campo2['de_oficina'].'</td>
		<td style="width: 20%;" align="left" rowspan="'.$cantidadActividades.'">'.$campo2['objetivo_institucional'].'</td>
                <td style="width: 20%;" align="left" >'.$campo3['nb_meta'].'</td>
		<td style="width: 18%;" align="left">'.$campo3['nb_responsable'].'</td>
                <td style="width: 5%;" align="center" >'.$campo3['tx_prog_anual'].'</td>
                <td style="width: 7%;" align="center" >'.$campo3['de_unidad_medida'].'</td>                    
                <td style="width: 5%;" align="center" >'.$campo3['primer_trimestre'].'</td>
                <td style="width: 5%;" align="center" >'.$campo3['segundo_trimestre'].'</td>
                <td style="width: 5%;" align="center" >'.$campo3['tercer_trimestre'].'</td>
                <td style="width: 5%;" align="center" >'.$campo3['cuarto_trimestre'].'</td>
                </tr>';   
                
                $id_nueva_oficina = 0;
        }        
             

               
    }
    
    $id_tab_t47_ac_accion_especifica=$campo3['id_tab_t47_ac_accion_especifica'];
    
         }


      
//echo $html23;
//exit();

         $id_oficina=$campo2['id_tab_ac_ae_oficina'];
         $id_nueva_oficina=1;
         $contador++;

}



$html23.='
</tbody>
</table>';

$this->writeHTML($html23, true, false, false, false, ''); 

		$this->Ln(-3);
                
    $sqlDetalleMonto= "SELECT SUM(monto) as subtotal_ac FROM
    t69_metas_ac as t69
    WHERE  id_accion_centralizada='".$campo['id_accion_centralizada']."' and co_ac_acc_espec='".$campo['id_accion']."' AND t69.edo_reg is true";                
// echo $sqlDetalleMonto;
//exit();               
$this->actividad_monto = $comunes->ObtenerFilasBySqlSelect($sqlDetalleMonto);
$html3 = '
<!-- Tabla 3 -->
<table border="0.1" style="width:100%" style="font-size:7px" cellpadding="3">
<tbody>
<tr nobr="true">
<td colspan="8" align="right"><b>SUBTOTAL ACTIVIDADES</b></td>
<td colspan="3" align="left"><b>'.number_format($this->actividad_monto[0]['subtotal_ac'], 2, ',','.').'</b></td>
</tr>
</tbody>
</table>
';
		$this->writeHTML($html3, true, false, false, false, '');
		$this->Ln(-3);
                
    $sqlMontoPro= "SELECT *,mo_total_ejecutor(id_ejecutor,id_ejercicio::int) as mo_proyecto_ac FROM
    t46_acciones_centralizadas
    WHERE  id=".$campo['id_accion_centralizada'];      
    
$this->programa_monto = $comunes->ObtenerFilasBySqlSelect($sqlMontoPro);    
                
$html4 = '
<!-- Tabla 4 -->
<table border="0.1" style="width:100%" style="font-size:7px" cellpadding="3">
<tbody>
<tr nobr="true">
<td colspan="8" align="right"><b>SUBTOTAL PROGRAMA</b></td>
<td colspan="3" align="left"><b>'.number_format($this->programa_monto[0]['monto'], 2, ',','.').'</b></td>
</tr>
</tbody>
</table>
';
		$this->writeHTML($html4, true, false, false, false, '');
		$this->Ln(-3);

$html5 = '
<!-- Tabla 5 -->
<table border="0.1" style="width:100%" style="font-size:7px" cellpadding="3">
<tbody>
<tr nobr="true">
<td colspan="8" align="right"><b>TOTAL EJECUTOR</b></td>
<td colspan="3" align="left"><b>'.number_format($this->programa_monto[0]['mo_proyecto_ac'], 2, ',','.').'</b></td>
</tr>
</tbody>
</table>
';
		$this->writeHTML($html5, true, false, false, false, '');
		$this->Ln(-3); 
                
        
                $id_ejecutor_poa = $campo['id_ejecutor'];

		}
                
                    $this->AddPage();
                    
                    
                    
		if($id_ejecutor!= '')
		{

			$condicionSector.= " t46.id_ejecutor = '".$id_ejecutor."' AND ";
		}                    
                    
		$sql_sector = "select distinct t46.id as id_accion_centralizada,
                t1.de_nombre as de_programa,t46.id_ejecutor,
                t2.tx_ejecutor,t3.tx_codigo,tx_ejecutor_poa,t46.monto,t3.tx_descripcion
		from t46_acciones_centralizadas as t46
                left join t47_ac_accion_especifica as t47 on t46.id = t47.id_accion_centralizada
		join mantenimiento.tab_ac_predefinida as t1 on t1.id = t46.id_accion
		join mantenimiento.tab_ejecutores as t2 on t2.id_ejecutor = t46.id_ejecutor
		inner join mantenimiento.tab_sectores as t3 on t46.id_subsector=t3.id
                join mantenimiento.tab_ac_ae_predefinida as t4 on t4.id = t47.id_accion
                where t46.edo_reg is true and ".$condicionSector." t3.tx_codigo = '".$nu_sector."' and t46.id_ejercicio = ".$_SESSION['ejercicio_fiscal']." order by t46.id_ejecutor asc";                    
                 
                $this->datos_sector = $comunes->ObtenerFilasBySqlSelect($sql_sector);
$htmlSector='';                
	$htmlSector = '
<table border="0.1" style="width:100%;text-align: center;" cellpadding="3">
	<tr align="center" bgcolor="#BDBDBD">
		<td colspan="2"><b>VINCULACIÓN PLAN-PRESUPUESTO</b></td>
	</tr>  
<thead>
<tr style="font-size:9px">
<th colspan="11" align="center" bgcolor="#BDBDBD" style="width: 40%;"><b>PROGRAMAS / ACTIVIDADES</b></th>
<th colspan="11" align="center" bgcolor="#BDBDBD" style="width: 40%;"><b>UNIDAD EJECUTORA</b></th>
<th colspan="11" align="right" bgcolor="#BDBDBD" style="width: 20%;"><b>ASIGNACIÓN PRESUPUESTARIA</b></th>
</tr>
</thead>'; 
        
$htmlSector.='
<tbody>';        

$total_sector=0;

foreach($this->datos_sector as $key => $campo4){
    
    		$htmlSector.='
		<tr style="font-size:8px">
                <td style="width: 40%;" align="left" ><b>'.$campo4['de_programa'].'</b></td>
                <td style="width: 40%;" align="center" ><b>'.$campo4['id_ejecutor'].'-'.$campo4['tx_ejecutor_poa'].'</b></td>
                <td style="width: 20%;" align="right" ><b>'.number_format($campo4['monto'], 2, ',','.').'</b></td>
                </tr>';
                $tx_descripcion = $campo4['tx_descripcion'];
                $total_sector = $total_sector + $campo4['monto'];
                
		$sql_partidas = "select SUBSTRING(t54.co_partida, 1,3) as co_partida,t54.id_tab_ejercicio_fiscal,tx_nombre,sum(monto) as monto
		from t54_ac_ae_partidas t54
                inner join mantenimiento.tab_partidas as t02 on SUBSTRING(t54.co_partida, 1,3)=t02.co_partida and t54.id_tab_ejercicio_fiscal = t02.id_tab_ejercicio_fiscal
                where edo_reg is true and id_accion_centralizada = ".$campo4['id_accion_centralizada']." and t54.id_tab_ejercicio_fiscal = ".$_SESSION['ejercicio_fiscal']." group by 1,2,3 order by 1 asc";                    
/*echo $sql_partidas;
exit(); */                
                $this->datos_partidas = $comunes->ObtenerFilasBySqlSelect($sql_partidas);

                foreach($this->datos_partidas as $key => $campo5){
                    
    		$htmlSector.='
		<tr style="font-size:8px">
                <td style="width: 80%;" align="left" > - '.$campo5['tx_nombre'].'</td>
                <td style="width: 20%;" align="right" >'.number_format($campo5['monto'], 2, ',','.').'</td>
                </tr>';                    
                    

                }                  
                
    
}

    		$htmlSector.='
		<tr style="font-size:9px">
                <td style="width: 80%;" bgcolor="#BDBDBD" align="left" ><b>TOTAL, SECTOR '.$nu_sector.': '.$tx_descripcion.'</b></td>
                <td style="width: 20%;" bgcolor="#BDBDBD" align="right" ><b>'.number_format($total_sector, 2, ',','.').'</b></td>
                </tr>';

$htmlSector.='
</tbody>
</table>';
        
		$this->SetFont('','',11);
		$this->writeHTML($htmlSector, true, false, false, false, ''); 


                
/*vinculacion metas*/
        $nu_sector = '';        
        $co_portada_vinculacion = 0;        
	foreach($this->datos as $key => $campo){
        
            if($co_portada_vinculacion==0){
                $this->AddPage();
		$this->SetY(75);
		$this->SetFont('','B',20);
		$this->SetTextColor(0,0,0);
                $this->Ln(10);
		$this->Write(0, 'VINCULACIÓN', '', 0, 'C', true, 0, false, false, 0);
		$this->Write(0, 'METAS '.$campo['nu_anio'], '', 0, 'C', true, 0, false, false, 0);
                $anio = $campo['nu_anio'] -1;
		$this->SetY(190);
		$this->SetFont('','',11);
		$this->Write(0, 'Maracaibo, '.'Diciembre'.' de '.$anio, '', 0, 'C', true, 0, false, false, 0);   
                $co_portada_vinculacion++;
            }
            
            if($nu_sector<>$campo['nu_sector']){

                
                $this->AddPage();
/******Portada*********/
            $this->SetFont('','B',12);
            $this->SetTextColor(0,0,0);    
            $this->Write(0, 'ÁREA INSTITUCIONAL ', '', 0, 'L', true, 0, false, false, 0);
            $this->Write(0, 'SECTOR '.$campo['nu_sector'].': '.$campo['tx_sector'], '', 0, 'L', true, 0, false, false, 0);
            
$html23='';
$html23.= '
<!-- Tabla 2 -->
<table border="0.1" style="width:100%" style="font-size:9px" cellpadding="3">
<thead>
<tr align="center" bgcolor="#BDBDBD">
<th style="width: 35%;"><b>DENOMINACIÓN</b></th>
<th style="width: 30%;"><b>UNIDAD DE MEDIDA</b></th>
<th style="width: 15%;"><b>CANTIDADES PROGRAMADAS</b></th>
<th style="width: 20%;"><b>COSTO FINANCIERO</b></th>
</tr>
</thead>
';

$html23.='
<tbody>'; 

		$sql_unidad_ejecuotra = "select distinct t46.id as id_accion_centralizada,
                t1.de_nombre as de_programa,t46.id_ejecutor,
                t2.tx_ejecutor,t3.tx_codigo,tx_ejecutor_poa,t46.monto,t3.tx_descripcion
		from t46_acciones_centralizadas as t46
                left join t47_ac_accion_especifica as t47 on t46.id = t47.id_accion_centralizada
		join mantenimiento.tab_ac_predefinida as t1 on t1.id = t46.id_accion
		join mantenimiento.tab_ejecutores as t2 on t2.id_ejecutor = t46.id_ejecutor
		inner join mantenimiento.tab_sectores as t3 on t46.id_subsector=t3.id
                join mantenimiento.tab_ac_ae_predefinida as t4 on t4.id = t47.id_accion
                where t46.edo_reg is true and  t3.id = '".$campo['id_sector']."' and t46.id_ejercicio = ".$_SESSION['ejercicio_fiscal']." order by t46.id_ejecutor asc";                    
/*echo $sql_sector;
exit(); */                
                $this->datos_unidad_ejecutora = $comunes->ObtenerFilasBySqlSelect($sql_unidad_ejecuotra);
                
foreach($this->datos_unidad_ejecutora as $key => $campo6){
    
    		$html23.='
		<tr style="font-size:8px">
                <td style="width: 80%;" bgcolor="#BDBDBD" align="left" ><b>'.$campo6['tx_ejecutor'].'</b></td>
                <td style="width: 20%;" bgcolor="#BDBDBD" align="right" ><b>'.number_format($campo6['monto'], 2, ',','.').'</b></td>
                </tr>';  
                
		$sqlActividades_vinculacion = "SELECT co_metas,id_tab_t47_ac_accion_especifica,nb_meta,nb_responsable,tx_prog_anual,
                de_unidad_medida,monto
                FROM t69_metas_ac as t69
		inner join mantenimiento.tab_unidad_medida as t21 on t69.co_unidades_medida=t21.id
		WHERE t69.id_accion_centralizada=".$campo6['id_accion_centralizada']." and t69.edo_reg is true 
        order by id_tab_t47_ac_accion_especifica asc, co_metas ASC"; 
                
         
$this->datos_actividades_vinculacion = $comunes->ObtenerFilasBySqlSelect($sqlActividades_vinculacion);  

foreach($this->datos_actividades_vinculacion as $key => $campo7){

    		$html23.='
		<tr style="font-size:8px">
                <td style="width: 35%;"  align="left" ><b>'.$campo7['nb_meta'].'</b></td>
                <td style="width: 30%;"  align="left" ><b>'.$campo7['nb_responsable'].'</b></td>
                <td style="width: 15%;"  align="center" ><b>'.$campo7['tx_prog_anual'].'</b></td>
                <td style="width: 20%;"  align="right" ><b>'.number_format($campo7['monto'], 2, ',','.').'</b></td>
                </tr>';
    
}
    
} 

$html23.='
</tbody>
</table>';
        
		$this->SetFont('','',11);
		$this->writeHTML($html23, true, false, false, false, ''); 
                
            $nu_sector = $campo['nu_sector'];
            }            
            
            
            
        }
        
       
                
        }
}

//Crear new PDF documento
$pdf = new MYPDF("L", PDF_UNIT, 'Letter', true, 'UTF-8', false);
$pdf->SetCreator('Yoser Perez');
$pdf->SetAuthor('Secretaria de Planificacion y Estadistica');
$pdf->SetTitle('PROGRAMAS - ACTIVIDADES');
$pdf->SetSubject('MI DOCUMENTO');
$pdf->SetKeywords('Planilla, PDF, Registro');
$pdf->SetMargins(15,20,15);
$pdf->SetTopMargin(30);
$pdf->setPrintHeader(false);
$pdf->SetPrintFooter(true);
$pdf->cuerpo();
$pdf->Output('POA_PG_'.$_SESSION['ejercicio_fiscal'].'_'.date("H:i:s").'.pdf', 'D');
