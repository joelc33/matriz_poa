<?php
session_start(); 
if($_SESSION['estatus']!='OK'){
	header('Location: ../../');
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

	function getRegistro($id_ejecutor, $id_proy_ae){

		$condicionPR='';
		$condicionAC='';
		if($id_ejecutor!= '')
		{
			$condicionPR.= " t26.id_ejecutor = '".$id_ejecutor."' AND ";
			$condicionAC.= " t47.id_ejecutor = '".$id_ejecutor."' AND ";	    
		}

		if($id_proy_ae!= '')
		{
			$condicionPR.= " t26.id_proyecto = '".$id_proy_ae."' AND ";
			$condicionAC.= " ('AC' || t24.id_ejecutor || t46.id_ejercicio || lpad(t47.id_accion::text, 5, '0')) = '".$id_proy_ae."' AND ";	    
		}

		$comunes = new ConexionComun();

		$sql = "SELECT t26.id_proyecto as id_proy_ac, nombre, t24.tx_ejecutor, fecha_inicio, fecha_fin, monto, monto_cargado(t26.id_proyecto) as mo_registrado, '1' as co_tipo, 
                    t26.id_ejecutor, t18a.tx_codigo as tx_sector, t26.id_ejercicio::integer as nu_anio, t45.tx_descripcion as tx_area_estrategica, t20.tx_descripcion as tx_objetivo_historico, 
                    t20a.tx_descripcion as tx_objetivo_nacional, t20b.tx_descripcion as tx_objetivo_estrategico, t20c.tx_descripcion as tx_objetivo_general, t39.tx_codigo as tx_codigo_ae, 
                    t39.descripcion as tx_nombre_ae, t39.co_proyecto_acc_espec as co_ae, 0 as id_accion_centralizada, t39.total as subtotal_actividades, 
                    t26.monto as mo_proyecto_ac, tx_objetivo_institucional,t45a.tx_descripcion as tx_ambito_estado, t45b.tx_descripcion as tx_macroproblema,t32.co_nodo as tx_nodos, t24a.id_ejecutor as id_ejecutor_ae, tx_categoria_proyecto(t26.id_proyecto,t39.tx_codigo,t26.id_ejercicio)
		FROM t26_proyectos as t26
		inner join t24_ejecutores as t24 on t26.id_ejecutor=t24.id_ejecutor 
		inner join t18_sectores as t18a on t26.clase_sector=t18a.co_sector and t18a.nu_nivel = 1
		inner join t32_proyecto_vinculos as t32 on t26.id_proyecto=t32.id_proyecto
		inner join t45_planes_zulia as t45 on t32.co_area_estrategica=t45.co_area_estrategica and t45.nu_nivel = 0
		inner join t45_planes_zulia as t45a on t32.co_area_estrategica=t45a.co_area_estrategica and t32.co_ambito_estado=t45a.co_ambito_zulia and t45a.nu_nivel = 1
		inner join t45_planes_zulia as t45b on t32.co_ambito_estado=t45b.co_ambito_zulia and t32.co_macroproblema=t45b.co_macroproblema and t45b.nu_nivel = 3
		inner join t20_planes as t20 on t32.id_obj_historico=t20.co_objetivo_historico and t20.nu_nivel = 1
		inner join t20_planes as t20a on t32.id_obj_nacional=t20a.co_objetivo_nacional and t32.id_obj_historico=t20a.co_objetivo_historico and t20a.nu_nivel = 2
		inner join t20_planes as t20b on t32.id_ob_estrategico=t20b.co_objetivo_estrategico and t32.id_obj_historico=t20b.co_objetivo_historico and t32.id_obj_nacional=t20b.co_objetivo_nacional and t20b.nu_nivel = 3
		inner join t20_planes as t20c on t32.id_obj_general=t20c.co_objetivo_general and t32.id_ob_estrategico=t20c.co_objetivo_estrategico and t32.id_obj_historico=t20c.co_objetivo_historico and t32.id_obj_nacional=t20c.co_objetivo_nacional and t20c.nu_nivel = 4 and t20c.edo_reg is true
		inner join t39_proyecto_acc_espec as t39 on t26.id_proyecto=t39.id_proyecto
		inner join t24_ejecutores as t24a on t39.co_ejecutores=t24a.co_ejecutores
		inner join vista_cn_actividad_proy as v1 on v1.co_proyecto_acc_espec=t39.co_proyecto_acc_espec
		where t26.edo_reg is true AND ".$condicionPR." t39.edo_reg is true
		union all
	select 'AC' || t24.id_ejecutor || id_ejercicio || lpad(t46.id_accion::text, 5, '0') as codigo, t52.nombre, tx_ejecutor_poa as tx_ejecutor, t46.fecha_inicio, t46.fecha_fin, t46.monto,
	coalesce(t46.monto_calc, 0) as monto_calc, '2' as co_tipo, t46.id_ejecutor, t18b.tx_codigo as tx_sector, t46.id_ejercicio::integer as nu_anio, t45.tx_descripcion as tx_area_estrategica, 
        t20.tx_descripcion as tx_objetivo_historico, t20a.tx_descripcion as tx_objetivo_nacional, t20b.tx_descripcion as tx_objetivo_estrategico, t20c.tx_descripcion as tx_objetivo_general, 
        t53.numero::text as tx_codigo_ae, t53.nombre as tx_nombre_ae, t47.id_accion as co_ae, t46.id as id_accion_centralizada, t46.monto as subtotal_actividades, t46.monto as mo_proyecto_ac, 
        objetivo_institucional as tx_objetivo_institucional, t45a.tx_descripcion as tx_ambito_estado, t45b.tx_descripcion as tx_macroproblema,t49.co_nodos as tx_nodos, t47.id_ejecutor as id_ejecutor_ae, 
        tx_categoria_ac (t47.id_accion_centralizada::integer, t53.numero, t46.id_ejercicio::integer) as tx_categoria
		from t46_acciones_centralizadas as t46
		join t52_ac_predefinidas as t52 on t52.id = t46.id_accion
		join t24_ejecutores as t24 on t24.id_ejecutor = t46.id_ejecutor
		inner join t18_sectores as t18a on t46.id_subsector=t18a.co_sectores
		inner join t18_sectores as t18b on t18a.co_sector = t18b.co_sector and t18b.nu_nivel = 1
		inner join t49_ac_planes as t49 on t46.id=t49.id_accion_centralizada
		inner join t45_planes_zulia as t45 on t49.co_area_estrategica=t45.co_area_estrategica and t45.nu_nivel = 0
		inner join t45_planes_zulia as t45a on t49.co_area_estrategica=t45a.co_area_estrategica and t49.co_ambito_estado=t45a.co_ambito_zulia and t45a.nu_nivel = 1
		inner join t45_planes_zulia as t45b on t49.co_ambito_estado=t45b.co_ambito_zulia and t49.co_macroproblema=t45b.co_macroproblema and t45b.nu_nivel = 3
		inner join t20_planes as t20 on t49.co_objetivo_historico=t20.co_objetivo_historico and t20.nu_nivel = 1
		inner join t20_planes as t20a on t49.co_objetivo_nacional=t20a.co_objetivo_nacional and t49.co_objetivo_historico=t20a.co_objetivo_historico and t20a.nu_nivel = 2
		inner join t20_planes as t20b on t49.co_objetivo_estrategico=t20b.co_objetivo_estrategico and t49.co_objetivo_historico=t20b.co_objetivo_historico and t49.co_objetivo_nacional=t20b.co_objetivo_nacional and t20b.nu_nivel = 3
		inner join t20_planes as t20c on t49.co_objetivo_general=t20c.co_objetivo_general and t49.co_objetivo_estrategico=t20c.co_objetivo_estrategico and t49.co_objetivo_historico=t20c.co_objetivo_historico and t49.co_objetivo_nacional=t20c.co_objetivo_nacional and t20c.nu_nivel = 4 and t20c.edo_reg is true
		inner join t47_ac_accion_especifica as t47 on t46.id = t47.id_accion_centralizada
		inner join t53_ac_ae_predefinidas as t53 on t53.id = t47.id_accion
		inner join vista_cn_actividad_ac as v1 on v1.id_accion_centralizada=t47.id_accion_centralizada and v1.co_ac_acc_espec=t47.id_accion
	where t46.edo_reg is true and ".$condicionAC." t47.edo_reg is true order by 9, 8, 1, 17 ASC";

		$this->datos = $comunes->ObtenerFilasBySqlSelect($sql);
		$this->cantidadTotal = $comunes->getFilas($sql);
	}

	public function setFooter()	
	{
		/*$this->getRegistro('PR130120150002','');
		foreach($this->datos as $key => $campo){
			$tipo = $campo["co_tipo"];
		}
		pie($this,$tipo);*/
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

		if($_GET['id_proy_ae']!= '')
		{
			$id_proy_ae = decode($_GET['id_proy_ae']);	    
		}

	$this->getRegistro($id_ejecutor, $id_proy_ae);
       	$comunes = new ConexionComun();
	$this->SetFont('dejavusans','',11);
	$this->Ln(-18);
	$contador=0;
	foreach($this->datos as $key => $campo){
		if($campo["co_tipo"]==1){
			$datosEnunciado='PROYECTO';
			$datosEnunciadoSUBTOTAL='PROYECTO';
			$fieldDatos='Datos del Proyecto';
			$co_proy_ac='Codigo de Proyecto';
			$sqlActividad = "SELECT co_metas, t67.codigo, nb_meta, tx_prog_anual, fecha_inicio, fecha_fin, nb_responsable, tx_unidades_medida FROM t67_metas as t67
			inner join t21_unidades_medida as t21 on t67.co_unidades_medida=t21.co_unidades_medida
			WHERE co_proyecto_acc_espec='".$campo['co_ae']."' and t67.edo_reg is true order by codigo ASC";

			$sqlDetalleMonto= "SELECT SUM(mo_presupuesto) as subtotal_ac FROM t68_metas_detalle as t68
			inner join t67_metas as t67 on t68.co_metas=t67.co_metas
			WHERE co_proyecto_acc_espec='".$campo['co_ae']."' AND t68.edo_reg is true";   
                        
                        $sqlAlcance= "SELECT (benef_femeninos+benef_masculinos) as nu_beneficiarios, (emp_dir_feme+emp_dir_mascu+emp_new_feme+emp_new_mascu+emp_sos_feme+emp_sos_mascu) as nu_empleos FROM t38_proyecto_alcance 
			WHERE id_proyecto='".$campo['id_proy_ac']."' AND edo_reg is true";
                        
		}elseif($campo["co_tipo"]==2){
			$datosEnunciado='ACCION C.';
			$datosEnunciadoSUBTOTAL='ACCION CENTRALIZADA';
			$fieldDatos='Datos de la Accion Centralizada';
			$co_proy_ac='Codigo de la Accion Centralizada';
			$sqlActividad = "SELECT co_metas, t69.codigo, nb_meta, tx_prog_anual, fecha_inicio, fecha_fin, nb_responsable, tx_unidades_medida FROM t69_metas_ac as t69
			inner join t21_unidades_medida as t21 on t69.co_unidades_medida=t21.co_unidades_medida
			WHERE id_accion_centralizada='".$campo['id_accion_centralizada']."' and co_ac_acc_espec='".$campo['co_ae']."' and t69.edo_reg is true order by codigo ASC";

			$sqlDetalleMonto= "SELECT SUM(mo_presupuesto) as subtotal_ac FROM t70_metas_ac_detalle as t70
			inner join t69_metas_ac as t69 on t70.co_metas=t69.co_metas
			WHERE  id_accion_centralizada='".$campo['id_accion_centralizada']."' and co_ac_acc_espec='".$campo['co_ae']."' AND t70.edo_reg is true"; 
                        
                        $sqlAlcance= "SELECT '' as nu_beneficiarios, '' as nu_empleos FROM t47_ac_accion_especifica 
			WHERE id_accion_centralizada='".$campo['id_accion_centralizada']."' and id_accion='".$campo['co_ae']."' AND edo_reg is true";
		}

$html1 = '
<table border="0.1" style="width:100%" style="font-size:10px" cellpadding="3">
<tbody>
<tr align="center" bgcolor="#BDBDBD">
<td colspan="3"><b>PLAN OPERATIVO INSTITUCIONAL - PRESUPUESTO EJERCICIO FISCAL '.$campo['nu_anio'].'</b></td>
</tr>
<tr style="font-size:9px">
<td style="width: 50%;">'.$campo['id_ejecutor'].' - '.$campo['tx_ejecutor'].'</td>
<td style="width: 15%;">SECTOR: '.$campo['tx_sector'].'</td>
<td style="width: 35%;">AREA ESTRATEGICA: '.$campo['tx_area_estrategica'].'</td>
</tr>
<tr style="font-size:9px">
<td colspan="3">'.$datosEnunciado.': '.$campo['id_proy_ac'].' - '.$campo['nombre'].'</td>
</tr>
<tr style="font-size:9px">
<td rowspan="2" style="width: 30%;">OBJETIVO HISTORICO: '.$campo['tx_objetivo_historico'].'</td>
<td colspan="2" style="width: 70%;">OBJETIVO(s) NACIONAL(ES): '.$campo['tx_objetivo_nacional'].'</td>
</tr>
<tr style="font-size:9px">
<td colspan="2" style="width: 70%;">OBJETIVO(S) ESTRATEGICO(S): '.$campo['tx_objetivo_estrategico'].'</td>
</tr>
<tr style="font-size:9px">
<td colspan="3">OBJETIVO GENERAL: '.$campo['tx_objetivo_general'].'</td>
</tr>
<tr style="font-size:9px">
<td rowspan="2">AMBITO: '.$campo['tx_ambito_estado'].'</td>
<td colspan="2">PDEZ/NOMBRE DEL PROBLEMA: '.$campo['tx_macroproblema'].'</td>
</tr>
<tr style="font-size:9px">
<td colspan="2">PDEZ/NUDO CRITICO: '.$campo['tx_nodos'].'</td>
</tr>
<tr style="font-size:9px">
<td colspan="3">OBJETIVO INSTITUCIONAL POA: '.$campo['tx_objetivo_institucional'].'</td>
</tr>
<tr style="font-size:9px">
<td style="width: 80%;">ACCION E.: '.$campo['tx_codigo_ae'].' - '.$campo['tx_nombre_ae'].'</td>
<td style="width: 20%;">COD. EJECUTOR: '.$campo['id_ejecutor_ae'].' </td>
</tr>
</tbody>
</table>
';					    
		$this->writeHTML($html1, true, false, false, false, '');	
		$this->Ln(-3);
$html2=''; 
$html2.= '
<!-- Tabla 2 -->
<table border="0.1" style="width:100%" style="font-size:9px" cellpadding="3">
<tbody>
<tr align="center" bgcolor="#BDBDBD">
<td colspan="5" style="width: 41%;"><b>METAS FISICAS</b></td>
<td colspan="6" style="width: 59%;"><b>METAS FINANCIERAS</b></td>
</tr>
<tr style="font-size:6px">
<td align="center" bgcolor="#BDBDBD" style="width: 10%;">Actividad</td>
<td align="center" bgcolor="#BDBDBD" style="width: 7%;">U. Med</td>
<td align="center" bgcolor="#BDBDBD" style="width: 8%;">Programado</td>
<td align="center" bgcolor="#BDBDBD" style="width: 8%;">Inicio</td>
<td align="center" bgcolor="#BDBDBD" style="width: 8%;">Termino</td>
<td align="center" bgcolor="#BDBDBD" style="width: 9%;">REPONSABLE</td>
<td align="center" bgcolor="#BDBDBD" style="width: 10%;">Munic / Parroq</td>
<td align="center" bgcolor="#BDBDBD" style="width: 15%;">PRESUPUESTO</td>
<td align="center" bgcolor="#BDBDBD" style="width: 8%;">CATEGORIA</td>
<td align="center" bgcolor="#BDBDBD" style="width: 7%;">PARTIDA</td>
<td align="center" bgcolor="#BDBDBD" style="width: 10%;">FUENTE FIN.</td>
</tr>
</tbody>
</table>
';
 
$html23 ='
<!-- Tabla Dinamica -->
<table border="0.1" style="width:100%" style="font-size:9px" cellpadding="3">
<tbody>';
$this->datos_actividad = $comunes->ObtenerFilasBySqlSelect($sqlActividad);
foreach($this->datos_actividad as $key => $campo2){
	if($campo["co_tipo"]==1){
		$sqlDetalle= "SELECT mo_presupuesto, co_partida, tx_municipio, tx_parroquia, tx_fuente FROM t68_metas_detalle as t68
		left join t64_municipio_detalle as t64 on t68.co_municipio=t64.co_municipio
		left join t65_parroquia_detalle as t65 on t68.co_parroquia=t65.co_parroquia
		inner join t66_fuente_financiamiento as t66 on t68.co_fuente=t66.co_fuente
		WHERE co_metas='".$campo2['co_metas']."' AND t68.edo_reg is true order by tx_municipio, tx_fuente ASC";
	}elseif($campo["co_tipo"]==2){
		$sqlDetalle = "SELECT mo_presupuesto, co_partida, tx_municipio, tx_parroquia, tx_fuente FROM t70_metas_ac_detalle as t70
		left join t64_municipio_detalle as t64 on t70.co_municipio=t64.co_municipio
		left join t65_parroquia_detalle as t65 on t70.co_parroquia=t65.co_parroquia
		inner join t66_fuente_financiamiento as t66 on t70.co_fuente=t66.co_fuente
		WHERE co_metas='".$campo2['co_metas']."' AND t70.edo_reg is true order by tx_municipio, tx_fuente ASC";
	}
	$cantidadDetalle = $comunes->getFilas($sqlDetalle);

	if($cantidadDetalle>1){
		$html23.='
		<tr style="font-size:6px">
		<td style="width: 10%;" colspan="1" rowspan="'.$cantidadDetalle.'">'.$campo2['codigo'].' - '.$campo2['nb_meta'].'</td>
		<td style="width: 7%;" colspan="1" rowspan="'.$cantidadDetalle.'">'.$campo2['tx_unidades_medida'].'</td>
		<td style="width: 8%;" colspan="1" rowspan="'.$cantidadDetalle.'">'.$campo2['tx_prog_anual'].'</td>
		<td style="width: 8%;" colspan="1" rowspan="'.$cantidadDetalle.'">'.trim(date_format(date_create($campo2["fecha_inicio"]),'d/m/Y')).'</td>
		<td style="width: 8%;" colspan="1" rowspan="'.$cantidadDetalle.'">'.trim(date_format(date_create($campo2["fecha_fin"]),'d/m/Y')).'</td>
		<td style="width: 9%;" colspan="1" rowspan="'.$cantidadDetalle.'">'.$campo2['nb_responsable'].'</td>';
		        $this->datos_detalle = $comunes->ObtenerFilasBySqlSelect($sqlDetalle);
			$contar=0;
			foreach($this->datos_detalle as $key => $campo3){
			$contar=$contar+1;
			$html23.='
				<td style="width: 10%;" colspan="1" rowspan="1">'.$campo3['tx_municipio'].' / '.$campo3['tx_parroquia'].'</td>
				<td style="width: 15%;" colspan="1" rowspan="1">'.$this->formatoDinero($campo3['mo_presupuesto']).'</td>
				<td style="width: 8%;" colspan="1" rowspan="1">'.$campo['tx_categoria_proyecto'].'</td>
				<td style="width: 7%;" colspan="1" rowspan="1">'.$campo3['co_partida'].'</td>
				<td style="width: 10%;" colspan="1" rowspan="1">'.$campo3['tx_fuente'].'</td>
				';

				if($cantidadDetalle>$contar){
					$html23.='</tr>
					<tr style="font-size:6px">';
				}else{
					$html23.='';
				}
			}
		$html23.='</tr>';
	}elseif($cantidadDetalle==1){
	$this->datos_detalle = $comunes->ObtenerFilasBySqlSelect($sqlDetalle);
	foreach($this->datos_detalle as $key => $campo3){
		$html23.='
		<tr style="font-size:6px">
		<td style="width: 10%;">'.$campo2['codigo'].' - '.$campo2['nb_meta'].'</td>
		<td style="width: 7%;">'.$campo2['tx_unidades_medida'].'</td>
		<td style="width: 8%;">'.$campo2['tx_prog_anual'].'</td>
		<td style="width: 8%;" >'.trim(date_format(date_create($campo2["fecha_inicio"]),'d/m/Y')).'</td>
		<td style="width: 8%;" >'.trim(date_format(date_create($campo2["fecha_fin"]),'d/m/Y')).'</td>
		<td style="width: 9%;" >'.$campo2['nb_responsable'].'</td>
		<td style="width: 10%;" >'.$campo3['tx_municipio'].' / '.$campo3['tx_parroquia'].'</td>
		<td style="width: 15%;" >'.$this->formatoDinero($campo3['mo_presupuesto']).'</td>
		<td style="width: 8%;" >'.$campo['tx_categoria_proyecto'].'</td>
		<td style="width: 7%;" >'.$campo3['co_partida'].'</td>
		<td style="width: 10%;" >'.$campo3['tx_fuente'].'</td>
		</tr>';
	}
	}
}
$html23.='
</tbody>
</table>';
/*echo $html23;
exit();*/
		$this->writeHTML($html2, true, false, false, false, '');
		$this->Ln(-7);

		$this->writeHTML($html23, true, false, false, false, '');
		$this->Ln(-3);
$this->actividad_monto = $comunes->ObtenerFilasBySqlSelect($sqlDetalleMonto);
$html3 = '
<!-- Tabla 3 -->
<table border="0.1" style="width:100%" style="font-size:7px" cellpadding="3">
<tbody>
<tr>
<td colspan="6" align="right"><b>SUBTOTAL ACTIVIDADES</b></td>
<td colspan="5" align="left"><b>'.$this->formatoDinero($this->actividad_monto[0]['subtotal_ac']).'</b></td>
</tr>
</tbody>
</table>
';
		$this->writeHTML($html3, true, false, false, false, '');
		$this->Ln(-3);	
                
$this->monto_alcance = $comunes->ObtenerFilasBySqlSelect($sqlAlcance);
$html4 = '
<!-- Tabla 4 -->
<table border="0.1" style="width:100%" style="font-size:7px" cellpadding="3">
<tbody>
<tr>
<td rowspan="2" colspan="6" align="right"><b>SUBTOTAL '.$datosEnunciadoSUBTOTAL.'</b></td>
<td rowspan="2" colspan="2" align="left"><b>'.$this->formatoDinero($campo["subtotal_actividades"]).'</b></td>
<td colspan="3" align="left" style="font-size:6px">POBLACION A BENEFICIAR: '.$this->monto_alcance[0]['nu_beneficiarios'].'</td>
</tr>
<tr>
<td colspan="3" align="left" style="font-size:6px">EMPLEOS PREVISTOS: '.$this->monto_alcance[0]['nu_empleos'].'</td>
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
<tr>
<td colspan="6" align="right"><b>TOTAL EJECUTOR</b></td>
<td colspan="5" align="left"><b>'.$this->formatoDinero($campo["mo_proyecto_ac"]).'</b></td>
</tr>
</tbody>
</table>
';
		$this->writeHTML($html5, true, false, false, false, '');
		$this->Ln(5);	
$html6 = '
<!-- Tabla 6 -->
<table border="0.1" style="width:100%" style="font-size:7px" cellpadding="3">
<tbody>
<tr>
<td colspan="11" align="left"><b>RESULTADOS ESPERADOS:</b></td>
</tr>
</tbody>
</table>
';
		$this->writeHTML($html6, true, false, false, false, '');
		$contador=$contador+1;
		if($this->cantidadTotal>$contador){
			$this->AddPage();
			$this->Ln(-18);
		}
		}
        }
}

//Crear new PDF documento
$pdf = new MYPDF("L", PDF_UNIT, 'Letter', true, 'UTF-8', false);
$pdf->SetCreator('Yoser Perez');
$pdf->SetAuthor('Secretaria de Planificacion y Estadistica');
$pdf->SetTitle('ACCIONES ESPECIFICAS');
$pdf->SetSubject('MI DOCUMENTO');
$pdf->SetKeywords('Planilla, PDF, Registro');
$pdf->SetMargins(10,40,10);
$pdf->SetTopMargin(40);
$pdf->setPrintHeader(false);
$pdf->setFooterMargin(15);
$pdf->setFooterFont(Array('dejavusans', 'I', 8));
$pdf->AddPage();
$pdf->cuerpo();
$pdf->Output('POA_2015.pdf', 'I');
