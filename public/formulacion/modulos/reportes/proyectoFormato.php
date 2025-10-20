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

		$condicionPR.= " t26.id_ejecutor = '".$id_ejecutor."' AND ";
		$condicionPRAE.= " t24a.id_ejecutor = '".$id_ejecutor."' AND ";

		$comunes = new ConexionComun();

		/*$sql = "SELECT t26.id_proyecto as id_proy_ac, nombre, t24.tx_ejecutor, t24.tx_ejecutor AS tx_eje_titulo, t24a.tx_ejecutor AS tx_eje_det, fecha_inicio, fecha_fin, monto, monto_cargado(t26.id_proyecto) as mo_registrado, '1' as co_tipo,
                    t26.id_ejecutor, t18a.tx_codigo as tx_sector, t26.id_ejercicio::integer as nu_anio, t45.tx_descripcion as tx_area_estrategica, t20.tx_descripcion as tx_objetivo_historico,
                    t20a.tx_descripcion as tx_objetivo_nacional, t20b.tx_descripcion as tx_objetivo_estrategico, t20c.tx_descripcion as tx_objetivo_general, t39.tx_codigo as tx_codigo_ae,
                    t39.descripcion as tx_nombre_ae, t39.co_proyecto_acc_espec as co_ae, 0 as id_accion_centralizada, t39.total as subtotal_actividades,
                    mo_total_ejecutor_pr(t26.id_ejecutor, t26.id_ejercicio) as mo_proyecto_ac, tx_objetivo_institucional,t45a.tx_descripcion as tx_ambito_estado, t45b.tx_descripcion as tx_macroproblema,t32.co_nodo as tx_nodos, t24a.id_ejecutor as id_ejecutor_ae, tx_categoria_proyecto(t26.id_proyecto,t39.tx_codigo,t26.id_ejercicio)
		FROM t26_proyectos as t26
		inner join mantenimiento.tab_ejecutores as t24 on t26.id_ejecutor=t24.id_ejecutor
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
		inner join mantenimiento.tab_ejecutores as t24a on t39.co_ejecutores=t24a.id
		inner join vista_cn_actividad_proy as v1 on v1.co_proyecto_acc_espec=t39.co_proyecto_acc_espec
		where t26.edo_reg is true AND ".$condicionPR." t39.edo_reg is true AND t26.id_ejercicio = '".$_SESSION['ejercicio_fiscal']."'

UNION

SELECT t26.id_proyecto as id_proy_ac, nombre, t24.tx_ejecutor, t24a.tx_ejecutor AS tx_eje_titulo, t24a.tx_ejecutor AS tx_eje_det, fecha_inicio, fecha_fin, monto, (
SELECT sum(mo_proy_ae_meta(co_proyecto_acc_espec))
	FROM t39_proyecto_acc_espec as t39s
	inner join mantenimiento.tab_unidad_medida as t21s on t39s.co_unidades_medida=t21s.id
	inner join mantenimiento.tab_ejecutores as t24s on t39s.co_ejecutores=t24s.id
	inner join t26_proyectos as t26s on t39s.id_proyecto=t26s.id_proyecto
	inner join mantenimiento.tab_ejecutores as t24bs on t26s.id_ejecutor=t24bs.id_ejecutor
WHERE t24s.id_ejecutor=t24a.id_ejecutor AND t26s.id_ejercicio = t26.id_ejercicio AND t39s.edo_reg is true AND t26s.edo_reg is true
 AND t26s.id_proyecto=t26.id_proyecto
) as mo_registrado, '1' as co_tipo,
                    t26.id_ejecutor, t18a.tx_codigo as tx_sector, t26.id_ejercicio::integer as nu_anio, t45.tx_descripcion as tx_area_estrategica, t20.tx_descripcion as tx_objetivo_historico,
                    t20a.tx_descripcion as tx_objetivo_nacional, t20b.tx_descripcion as tx_objetivo_estrategico, t20c.tx_descripcion as tx_objetivo_general, t39.tx_codigo as tx_codigo_ae,
                    t39.descripcion as tx_nombre_ae, t39.co_proyecto_acc_espec as co_ae, 0 as id_accion_centralizada, t39.total as subtotal_actividades,
                    (
SELECT sum(mo_proy_ae_meta(co_proyecto_acc_espec))
	FROM t39_proyecto_acc_espec as t39s
	inner join mantenimiento.tab_unidad_medida as t21s on t39s.co_unidades_medida=t21s.id
	inner join mantenimiento.tab_ejecutores as t24s on t39s.co_ejecutores=t24s.id
	inner join t26_proyectos as t26s on t39s.id_proyecto=t26s.id_proyecto
	inner join mantenimiento.tab_ejecutores as t24bs on t26s.id_ejecutor=t24bs.id_ejecutor
WHERE t24s.id_ejecutor=t24a.id_ejecutor AND t26s.id_ejercicio = t26.id_ejercicio AND t39s.edo_reg is true AND t26s.edo_reg is true


) as mo_proyecto_ac, tx_objetivo_institucional,t45a.tx_descripcion as tx_ambito_estado, t45b.tx_descripcion as tx_macroproblema,t32.co_nodo as tx_nodos, t24a.id_ejecutor as id_ejecutor_ae, tx_categoria_proyecto(t26.id_proyecto,t39.tx_codigo,t26.id_ejercicio)
		FROM t26_proyectos as t26
		inner join mantenimiento.tab_ejecutores as t24 on t26.id_ejecutor=t24.id_ejecutor
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
		inner join mantenimiento.tab_ejecutores as t24a on t39.co_ejecutores=t24a.id
		inner join vista_cn_actividad_proy as v1 on v1.co_proyecto_acc_espec=t39.co_proyecto_acc_espec
		where t26.edo_reg is true AND ".$condicionPRAE." t39.edo_reg is true AND t26.id_ejercicio = '".$_SESSION['ejercicio_fiscal']."'

order by 1, 19 ASC";*/

		$sql = "SELECT t26.id_proyecto as id_proy_ac, nombre, t24.tx_ejecutor, t24a.tx_ejecutor AS tx_eje_titulo, t24a.tx_ejecutor AS tx_eje_det, fecha_inicio, fecha_fin, EXTRACT(month FROM t26.fecha_actualizacion::DATE) as nu_mes_poa, EXTRACT(year FROM t26.fecha_actualizacion::DATE) as nu_anio_poa, monto, (
SELECT sum(mo_proy_ae_meta(co_proyecto_acc_espec))
	FROM t39_proyecto_acc_espec as t39s
	inner join mantenimiento.tab_unidad_medida as t21s on t39s.co_unidades_medida=t21s.id
	inner join mantenimiento.tab_ejecutores as t24s on t39s.co_ejecutores=t24s.id
	inner join t26_proyectos as t26s on t39s.id_proyecto=t26s.id_proyecto
	inner join mantenimiento.tab_ejecutores as t24bs on t26s.id_ejecutor=t24bs.id_ejecutor
WHERE t24s.id_ejecutor=t24a.id_ejecutor AND t26s.id_ejercicio = t26.id_ejercicio AND t39s.edo_reg is true AND t26s.edo_reg is true
 AND t26s.id_proyecto=t26.id_proyecto
) as mo_registrado, '1' as co_tipo,
                    t26.id_ejecutor, t18a.tx_codigo as tx_sector, t26.id_ejercicio::integer as nu_anio, t45.tx_descripcion as tx_area_estrategica, t20.tx_descripcion as tx_objetivo_historico,
                    t20a.tx_descripcion as tx_objetivo_nacional, t20b.tx_descripcion as tx_objetivo_estrategico, t20c.tx_descripcion as tx_objetivo_general, t39.tx_codigo as tx_codigo_ae,
                    t39.descripcion as tx_nombre_ae, t39.co_proyecto_acc_espec as co_ae, 0 as id_accion_centralizada, t39.total as subtotal_actividades,
                    (
SELECT sum(mo_proy_ae_meta(co_proyecto_acc_espec))
	FROM t39_proyecto_acc_espec as t39s
	inner join mantenimiento.tab_unidad_medida as t21s on t39s.co_unidades_medida=t21s.id
	inner join mantenimiento.tab_ejecutores as t24s on t39s.co_ejecutores=t24s.id
	inner join t26_proyectos as t26s on t39s.id_proyecto=t26s.id_proyecto
	inner join mantenimiento.tab_ejecutores as t24bs on t26s.id_ejecutor=t24bs.id_ejecutor
WHERE t24s.id_ejecutor=t24a.id_ejecutor AND t26s.id_ejercicio = t26.id_ejercicio AND t39s.edo_reg is true AND t26s.edo_reg is true


) as mo_proyecto_ac, tx_objetivo_institucional,t45a.tx_descripcion as tx_ambito_estado, t45b.tx_descripcion as tx_macroproblema,t32.co_nodo as tx_nodos, t24a.id_ejecutor as id_ejecutor_ae, tx_categoria_proyecto(t26.id_proyecto,t39.tx_codigo,t26.id_ejercicio)
		FROM t26_proyectos as t26
		inner join mantenimiento.tab_ejecutores as t24 on t26.id_ejecutor=t24.id_ejecutor
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
		inner join mantenimiento.tab_ejecutores as t24a on t39.co_ejecutores=t24a.id
		inner join vista_cn_actividad_proy as v1 on v1.co_proyecto_acc_espec=t39.co_proyecto_acc_espec
		where t26.edo_reg is true AND ".$condicionPRAE." t39.edo_reg is true AND t26.id_ejercicio = '".$_SESSION['ejercicio_fiscal']."'

order by 1, 19 ASC";

		$this->datos = $comunes->ObtenerFilasBySqlSelect($sql);
		$this->cantidadTotal = $comunes->getFilas($sql);
	}

	public function Footer()
	{
		pie($this,'h',1);
	}
	public function setHeader()
	{
		encabezado($this,'h',1);
	}
        public function cuerpo()
        {

	$id_ejecutor = decode($_GET['id_ejecutor']);

	$this->getRegistro($id_ejecutor, $id_proy_ae);
       	$comunes = new ConexionComun();
	$this->SetFont('','',11);

	$contador=0;
	$portada=0;
	$acumulador_pr_a=0;
	foreach($this->datos as $key => $campo){
		$datosEnunciado='PROYECTO';
		$datosEnunciadoSUBTOTAL='PROYECTO';
		$fieldDatos='Datos del Proyecto';
		$co_proy_ac='Codigo de Proyecto';
		$sqlActividad = "SELECT co_metas, t67.codigo, nb_meta, tx_prog_anual, fecha_inicio, fecha_fin, nb_responsable, de_unidad_medida as tx_unidades_medida FROM t67_metas as t67
		inner join mantenimiento.tab_unidad_medida as t21 on t67.co_unidades_medida=t21.id
		WHERE co_proyecto_acc_espec='".$campo['co_ae']."' and t67.edo_reg is true order by codigo ASC";

		$sqlDetalleMonto= "SELECT SUM(mo_presupuesto) as subtotal_ac FROM t68_metas_detalle as t68
		inner join t67_metas as t67 on t68.co_metas=t67.co_metas
		WHERE co_proyecto_acc_espec='".$campo['co_ae']."' AND t68.edo_reg is true";

                $sqlAlcance= "SELECT (benef_femeninos+benef_masculinos) as nu_beneficiarios, (emp_dir_feme+emp_dir_mascu+emp_new_feme+emp_new_mascu+emp_sos_feme+emp_sos_mascu) as nu_empleos FROM t38_proyecto_alcance
		WHERE id_proyecto='".$campo['id_proy_ac']."' AND edo_reg is true";

/******Portada*********/

		if($portada==0){

		//$this->SetXY(30,50);
		$this->SetY(75);
		$this->SetFont('','B',20);
		$this->SetTextColor(0,0,0);
		$this->Write(0, 'PLAN OPERATIVO INSTITUCIONAL PRESUPUESTO', '', 0, 'C', true, 0, false, false, 0);
		$this->Ln(5);
		$this->Write(0, 'AÑO '.$campo['nu_anio'], '', 0, 'C', true, 0, false, false, 0);
		//$this->Ln(26);
		$this->Ln(10);
		$this->Write(0, $campo['tx_eje_titulo'], '', 0, 'C', true, 0, false, false, 0);
		$this->SetY(190);
		$this->SetFont('','',11);
		$this->Write(0, 'Maracaibo, '.'Diciembre'/*mes($campo['nu_mes_poa'])*/.' del '.$campo['nu_anio_poa'], '', 0, 'C', true, 0, false, false, 0);
		$this->AddPage();

		}

		$this->SetFont('','',11);
/******POA*********/
$html1 = '
<table border="0.1" style="width:100%" style="font-size:10px" cellpadding="3">
<tbody>
<tr align="center" bgcolor="#BDBDBD">
<td colspan="3"><b>PLAN OPERATIVO INSTITUCIONAL - PRESUPUESTO AÑO '.$campo['nu_anio'].'</b></td>
</tr>
<tr style="font-size:9px">
<td style="width: 50%;"><b>'.$campo['id_ejecutor'].'</b> - '.$campo['tx_ejecutor'].'</td>
<td style="width: 15%;"><b>SECTOR:</b> '.$campo['tx_sector'].'</td>
<td style="width: 35%;"><b>AREA ESTRATEGICA:</b> '.$campo['tx_area_estrategica'].'</td>
</tr>
<tr style="font-size:9px">
<td rowspan="2" style="width: 30%;" align="justify"><b>OBJETIVO HISTORICO:</b> '.$campo['tx_objetivo_historico'].'</td>
<td colspan="2" style="width: 70%;" align="justify"><b>OBJETIVO(s) NACIONAL(ES):</b> '.$campo['tx_objetivo_nacional'].'</td>
</tr>
<tr style="font-size:9px">
<td colspan="2" style="width: 70%;" align="justify"><b>OBJETIVO(S) ESTRATEGICO(S):</b> '.$campo['tx_objetivo_estrategico'].'</td>
</tr>
<tr style="font-size:9px">
<td colspan="3" align="justify"><b>OBJETIVO GENERAL:</b> '.$campo['tx_objetivo_general'].'</td>
</tr>
<tr style="font-size:9px">
<td rowspan="2"><b>AMBITO:</b> '.$campo['tx_ambito_estado'].'</td>
<td colspan="2"><b>PDEZ/NOMBRE DEL PROBLEMA:</b> '.$campo['tx_macroproblema'].'</td>
</tr>
<tr style="font-size:9px">
<td colspan="2"><b>PDEZ/NUDO CRITICO:</b> '.$campo['tx_nodos'].'</td>
</tr>
<tr style="font-size:9px">
<td colspan="3"><b>OBJETIVO INSTITUCIONAL POA:</b> '.$campo['tx_objetivo_institucional'].'</td>
</tr>
<tr style="font-size:9px">
<td colspan="3"><b>'.$datosEnunciado.':</b> '.$campo['id_proy_ac'].' - '.$campo['nombre'].'</td>
</tr>
<tr style="font-size:9px">
<td style="width: 60%;"><b>ACCION E.:</b> '.$campo['tx_codigo_ae'].' - '.$campo['tx_nombre_ae'].'</td>
<td style="width: 40%;"><b>EJECUTOR:</b> '.$campo['id_ejecutor_ae'].' - '.$campo['tx_eje_det'].' </td>
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
<th colspan="5" style="width: 48%;"><b>METAS FISICAS</b></th>
<th colspan="6" style="width: 52%;"><b>METAS FINANCIERAS</b></th>
</tr>
<tr style="font-size:6px">
<th align="center" bgcolor="#BDBDBD" style="width: 17%;">Actividad</th>
<th align="center" bgcolor="#BDBDBD" style="width: 7%;">U. Med</th>
<th align="center" bgcolor="#BDBDBD" style="width: 8%;">Programado</th>
<th align="center" bgcolor="#BDBDBD" style="width: 8%;">Inicio</th>
<th align="center" bgcolor="#BDBDBD" style="width: 8%;">Termino</th>
<th align="center" bgcolor="#BDBDBD" style="width: 9%;">REPONSABLE</th>
<th align="center" bgcolor="#BDBDBD" style="width: 10%;">Munic / Parroq</th>
<th align="center" bgcolor="#BDBDBD" style="width: 8%;">PRESUPUESTO</th>
<th align="center" bgcolor="#BDBDBD" style="width: 8%;">CATEGORIA</th>
<th align="center" bgcolor="#BDBDBD" style="width: 7%;">PARTIDA</th>
<th align="center" bgcolor="#BDBDBD" style="width: 10%;">FUENTE FIN.</th>
</tr>
</thead>
';

$html23.='
<tbody>';
$this->datos_actividad = $comunes->ObtenerFilasBySqlSelect($sqlActividad);
foreach($this->datos_actividad as $key => $campo2){
	if($campo["co_tipo"]==1){
		$sqlDetalle= "SELECT mo_presupuesto, co_partida, de_municipio as tx_municipio, de_parroquia as tx_parroquia, de_fuente_financiamiento as tx_fuente FROM t68_metas_detalle as t68
		left join mantenimiento.tab_municipio_detalle as t64 on t68.co_municipio=t64.id
		left join mantenimiento.tab_parroquia_detalle as t65 on t68.co_parroquia=t65.id
		inner join mantenimiento.tab_fuente_financiamiento as t66 on t68.co_fuente=t66.id
		WHERE co_metas='".$campo2['co_metas']."' AND t68.edo_reg is true order by tx_municipio, tx_fuente ASC";
	}elseif($campo["co_tipo"]==2){
		$sqlDetalle = "SELECT mo_presupuesto, co_partida, de_municipio as tx_municipio, de_parroquia as tx_parroquia, de_fuente_financiamiento as tx_fuente FROM t70_metas_ac_detalle as t70
		left join mantenimiento.tab_municipio_detalle as t64 on t70.co_municipio=t64.id
		left join mantenimiento.tab_parroquia_detalle as t65 on t70.co_parroquia=t65.id
		inner join mantenimiento.tab_fuente_financiamiento as t66 on t70.co_fuente=t66.id
		WHERE co_metas='".$campo2['co_metas']."' AND t70.edo_reg is true order by tx_municipio, tx_fuente ASC";
	}
	$cantidadDetalle = $comunes->getFilas($sqlDetalle);

	if($cantidadDetalle>1){
		$html23.='
		<tr style="font-size:6px" nobr="true">
		<td style="width: 17%;" colspan="1" rowspan="'.$cantidadDetalle.'" nobr="true">'.$campo2['codigo'].' - '.$campo2['nb_meta'].'</td>
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
				<td style="width: 8%;" colspan="1" rowspan="1">'.$this->formatoDinero($campo3['mo_presupuesto']).'</td>
				<td style="width: 8%;" colspan="1" rowspan="1" align="center">'.$campo['tx_categoria_proyecto'].'</td>
				<td style="width: 7%;" colspan="1" rowspan="1" align="center">'.$campo3['co_partida'].'</td>
				<td style="width: 10%;" colspan="1" rowspan="1" align="center">'.$campo3['tx_fuente'].'</td>
				';

				if($cantidadDetalle>$contar){
					$html23.='</tr>
					<tr style="font-size:6px" nobr="true">';
				}else{
					$html23.='';
				}
			}
		$html23.='</tr>';
	}elseif($cantidadDetalle==1){
	$this->datos_detalle = $comunes->ObtenerFilasBySqlSelect($sqlDetalle);
	foreach($this->datos_detalle as $key => $campo3){
		$html23.='
		<tr style="font-size:6px" nobr="true">
		<td style="width: 17%;">'.$campo2['codigo'].' - '.$campo2['nb_meta'].'</td>
		<td style="width: 7%;">'.$campo2['tx_unidades_medida'].'</td>
		<td style="width: 8%;">'.$campo2['tx_prog_anual'].'</td>
		<td style="width: 8%;" >'.trim(date_format(date_create($campo2["fecha_inicio"]),'d/m/Y')).'</td>
		<td style="width: 8%;" >'.trim(date_format(date_create($campo2["fecha_fin"]),'d/m/Y')).'</td>
		<td style="width: 9%;" >'.$campo2['nb_responsable'].'</td>
		<td style="width: 10%;" >'.$campo3['tx_municipio'].' / '.$campo3['tx_parroquia'].'</td>
		<td style="width: 8%;" >'.$this->formatoDinero($campo3['mo_presupuesto']).'</td>
		<td style="width: 8%;" align="center">'.$campo['tx_categoria_proyecto'].'</td>
		<td style="width: 7%;" align="center">'.$campo3['co_partida'].'</td>
		<td style="width: 10%;" align="center">'.$campo3['tx_fuente'].'</td>
		</tr>';
	}
	}elseif($cantidadDetalle==0){
		$html23.='
		<tr style="font-size:6px" nobr="true">
		<td style="width: 17%;">'.$campo2['codigo'].' - '.$campo2['nb_meta'].'</td>
		<td style="width: 7%;">'.$campo2['tx_unidades_medida'].'</td>
		<td style="width: 8%;">'.$campo2['tx_prog_anual'].'</td>
		<td style="width: 8%;" >'.trim(date_format(date_create($campo2["fecha_inicio"]),'d/m/Y')).'</td>
		<td style="width: 8%;" >'.trim(date_format(date_create($campo2["fecha_fin"]),'d/m/Y')).'</td>
		<td style="width: 9%;" >'.$campo2['nb_responsable'].'</td>
		<td style="width: 10%;" align="center" cellpadding="6">N/A</td>
		<td style="width: 8%;" align="center" cellpadding="6">N/A</td>
		<td style="width: 8%;" align="center" cellpadding="6">N/A</td>
		<td style="width: 7%;" align="center" cellpadding="6">N/A</td>
		<td style="width: 10%;" align="center" cellpadding="6">N/A</td>
		</tr>';
	}
}
$html23.='
</tbody>
</table>';

		$this->writeHTML($html23, true, false, false, false, '');
		$this->Ln(-3);
$this->actividad_monto = $comunes->ObtenerFilasBySqlSelect($sqlDetalleMonto);
$html3 = '
<!-- Tabla 3 -->
<table border="0.1" style="width:100%" style="font-size:7px" cellpadding="3">
<tbody>
<tr nobr="true">
<td colspan="6" align="right"><b>SUBTOTAL ACTIVIDADES</b></td>
<td colspan="5" align="left"><b>'.$this->formatoDinero($this->actividad_monto[0]['subtotal_ac']).'</b></td>
</tr>
</tbody>
</table>
';
		$this->writeHTML($html3, true, false, false, false, '');
		$this->Ln(-3);

		$acumulador_pr_a = $acumulador_pr_a+$this->actividad_monto[0]['subtotal_ac'];

$html4 = '
<!-- Tabla 4 -->
<table border="0.1" style="width:100%" style="font-size:7px" cellpadding="3">
<tbody>
<tr nobr="true">
<td rowspan="2" colspan="6" align="right"><b>SUBTOTAL '.$datosEnunciadoSUBTOTAL.'</b></td>
<td rowspan="2" colspan="2" align="left"><b>'.$this->formatoDinero($campo["mo_registrado"]).'</b></td>
<td colspan="3" align="left" style="font-size:6px">POBLACION A BENEFICIAR: '.$campo['nu_po_beneficiar'].'</td>
</tr>
<tr nobr="true">
<td colspan="3" align="left" style="font-size:6px">EMPLEOS PREVISTOS: '.$campo['nu_em_previsto'].'</td>
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
<td colspan="6" align="right"><b>TOTAL EJECUTOR</b></td>
<td colspan="5" align="left"><b>'.$this->formatoDinero($campo["mo_proyecto_ac"]).'</b></td>
</tr>
</tbody>
</table>
';
		$this->writeHTML($html5, true, false, false, false, '');
		$this->Ln(-3);
$html6 = '
<!-- Tabla 6 -->
<table border="0.1" style="width:100%" style="font-size:7px" cellpadding="3">
<tbody>
<tr nobr="true">
<td colspan="11" align="left"><b>RESULTADOS ESPERADOS:</b></td>
</tr>
</tbody>
</table>
';
		$this->writeHTML($html6, true, false, false, false, '');






		$contador=$contador+1;
		$portada=$portada+1;
		if($this->cantidadTotal>$contador){
			$this->AddPage();
		}
		}

$sqlDistribucion = "SELECT id_proyecto, id_ejercicio::integer as nu_anio FROM t26_proyectos where edo_reg is true AND id_ejecutor = '".$id_ejecutor."' AND id_ejercicio = '".$_SESSION['ejercicio_fiscal']."'";

$this->datos_distribucion = $comunes->ObtenerFilasBySqlSelect($sqlDistribucion);
foreach($this->datos_distribucion as $key => $campo_distribucion){

/***distribucion fisica***/
$htmlFisicoDetalle = '
<table border="0.1" style="width:100%" style="font-size:10px" cellpadding="3">
<thead>
<tr align="center" bgcolor="#BDBDBD">
<th colspan="17"><b>PLAN OPERATIVO INSTITUCIONAL - PRESUPUESTO AÑO '.$campo_distribucion['nu_anio'].'</b></th>
</tr>
<tr align="center" bgcolor="#BDBDBD" style="font-size:7px" >
<th rowspan="2" style="width: 4%;"><b>CÓD.</b></th>
<th rowspan="2" style="width: 15%;"><b>Nombre de la Acción </b></th>
<th rowspan="2" style="width: 8%;"><b>DISTRIBUCIÓN</b></th>
<th colspan="17" style="width: 73%;"><b>DISTRIBUCIÓN MENSUAL Y TRIMESTRAL '.$campo_distribucion['nu_anio'].'</b></th>
</tr>
<tr style="font-size:6px">
<th align="center" bgcolor="#BDBDBD" style="width: 4%;">Enero</th>
<th align="center" bgcolor="#BDBDBD" style="width: 4%;">Febrero</th>
<th align="center" bgcolor="#BDBDBD" style="width: 4%;">Marzo</th>
<th align="center" bgcolor="#BDBDBD" style="width: 5%;"><b>Trimestre I</b></th>
<th align="center" bgcolor="#BDBDBD" style="width: 4%;">Abril</th>
<th align="center" bgcolor="#BDBDBD" style="width: 4%;">Mayo</th>
<th align="center" bgcolor="#BDBDBD" style="width: 4%;">Junio</th>
<th align="center" bgcolor="#BDBDBD" style="width: 5%;"><b>Trimestre II</b></th>
<th align="center" bgcolor="#BDBDBD" style="width: 4%;">Julio</th>
<th align="center" bgcolor="#BDBDBD" style="width: 4%;">Agosto</th>
<th align="center" bgcolor="#BDBDBD" style="width: 4%;">Septiembre</th>
<th align="center" bgcolor="#BDBDBD" style="width: 5%;"><b>Trimestre III</b></th>
<th align="center" bgcolor="#BDBDBD" style="width: 4%;">Obtubre</th>
<th align="center" bgcolor="#BDBDBD" style="width: 4%;">Noviembre</th>
<th align="center" bgcolor="#BDBDBD" style="width: 4%;">Diciembre</th>
<th align="center" bgcolor="#BDBDBD" style="width: 5%;"><b>Trimestre IV</b></th>
<th align="center" bgcolor="#BDBDBD" style="width: 5%;"><b>Total '.$campo_distribucion['nu_anio'].'</b></th>
</tr>
</thead>
';
		$this->AddPage();

		$variableFisico = "'Fisica'";
		$variablePresupuestaria = "'Financiera (Bs.)'";

		$sqlFisicoFinanciero= "SELECT co_proyecto_acc_espec_rec, t40.id_proyecto, t40.co_proyecto_acc_espec, 'Financiera (Bs)' AS tx_distribucion,
	presup_01, presup_02, presup_03, presup_04, presup_05, presup_06,
	presup_07, presup_08, presup_09, presup_10, presup_11, presup_12,
	t40.fecha_creacion, t40.edo_reg, tx_codigo,
	CASE WHEN (id_padre IS NOT NULL) THEN
	tx_padre(id_padre)
	ELSE
	descripcion
	END AS descripcion, id_padre
	FROM t40_proyecto_acc_espec_rec as t40
	inner join t39_proyecto_acc_espec as t39 on t40.co_proyecto_acc_espec=t39.co_proyecto_acc_espec
	WHERE t40.id_proyecto='".$campo_distribucion['id_proyecto']."' AND t40.edo_reg is true and t39.edo_reg is true
	UNION
	SELECT co_proyecto_acc_espec_rec, t40.id_proyecto, t40.co_proyecto_acc_espec,'Fisica' AS tx_distribucion,
	fisico_01, fisico_02, fisico_03, fisico_04, fisico_05, fisico_06,
	fisico_07, fisico_08, fisico_09, fisico_10, fisico_11, fisico_12,
	t40.fecha_actualizacion as fecha_creacion, t40.edo_reg, tx_codigo, descripcion, id_padre
	FROM t40_proyecto_acc_espec_rec as t40
	inner join t39_proyecto_acc_espec as t39 on t40.co_proyecto_acc_espec=t39.co_proyecto_acc_espec
	WHERE t40.id_proyecto='".$campo_distribucion['id_proyecto']."' AND t40.edo_reg is true and t39.edo_reg is true and id_padre is null
	ORDER BY tx_codigo, co_proyecto_acc_espec_rec, tx_distribucion, fecha_creacion ASC
;";

		$this->datos_fisico = $comunes->ObtenerFilasBySqlSelect($sqlFisicoFinanciero);

$htmlFisicoDetalle.='
<tbody>
';

		foreach($this->datos_fisico as $key => $campoFisico){
			$htmlFisicoDetalle.= '
			<tr style="font-size:7px" nobr="true">
			<td style="width: 4%;">'.$campoFisico['tx_codigo'].'</td>
			<td style="width: 15%;">'.$campoFisico['descripcion'].'</td>
			<td style="width: 8%;">'.$campoFisico['tx_distribucion'].'</td>
			<td style="width: 4%;">'.$campoFisico['presup_01'].'</td>
			<td style="width: 4%;">'.$campoFisico['presup_02'].'</td>
			<td style="width: 4%;">'.$campoFisico['presup_03'].'</td>
			<td bgcolor="#dedede" style="width: 5%;"><b>'.($campoFisico['presup_01']+$campoFisico['presup_02']+$campoFisico['presup_03']).'</b></td>
			<td style="width: 4%;">'.$campoFisico['presup_04'].'</td>
			<td style="width: 4%;">'.$campoFisico['presup_05'].'</td>
			<td style="width: 4%;">'.$campoFisico['presup_06'].'</td>
			<td style="width: 5%;"><b>'.($campoFisico['presup_04']+$campoFisico['presup_05']+$campoFisico['presup_06']).'</b></td>
			<td style="width: 4%;">'.$campoFisico['presup_07'].'</td>
			<td style="width: 4%;">'.$campoFisico['presup_08'].'</td>
			<td style="width: 4%;">'.$campoFisico['presup_09'].'</td>
			<td bgcolor="#dedede" style="width: 5%;"><b>'.($campoFisico['presup_07']+$campoFisico['presup_08']+$campoFisico['presup_09']).'</b></td>
			<td style="width: 4%;">'.$campoFisico['presup_10'].'</td>
			<td style="width: 4%;">'.$campoFisico['presup_11'].'</td>
			<td style="width: 4%;">'.$campoFisico['presup_12'].'</td>
			<td bgcolor="#dedede" style="width: 5%;"><b>'.($campoFisico['presup_10']+$campoFisico['presup_11']+$campoFisico['presup_12']).'</b></td>
			<td style="width: 5%;"><b>'.(($campoFisico['presup_01']+$campoFisico['presup_02']+$campoFisico['presup_03'])+($campoFisico['presup_04']+$campoFisico['presup_05']+$campoFisico['presup_06'])+($campoFisico['presup_07']+$campoFisico['presup_08']+$campoFisico['presup_09'])+($campoFisico['presup_10']+$campoFisico['presup_11']+$campoFisico['presup_12'])).'</b></td>
			</tr>
			';
		}
$htmlFisicoDetalle.='
</tbody>
</table>';
		$this->writeHTML($htmlFisicoDetalle, true, false, false, false, '');

/***Partidas***/
$htmlPartidaDetalle = '
<table border="0.1" style="width:100%" style="font-size:10px" cellpadding="3">
<thead>
<tr align="center" bgcolor="#BDBDBD">
<th colspan="17"><b>PLAN OPERATIVO INSTITUCIONAL - PRESUPUESTO AÑO '.$campo_distribucion['nu_anio'].'</b></th>
</tr>
<tr align="center" bgcolor="#BDBDBD" style="font-size:7px" >
<th rowspan="2" style="width: 4%;"><b>CÓD.</b></th>
<th rowspan="2" style="width: 23%;"><b>Nombre de la Acción </b></th>
<th colspan="17" style="width: 73%;"><b>DISTRIBUCIÓN PRESUPUESTARÍA POR ACCIONES ESPECÍFICAS DEL AÑO A FORMULAR (Bs.)</b></th>
</tr>
<tr style="font-size:6px">
<th align="center" bgcolor="#BDBDBD" style="width: 6%;">401</th>
<th align="center" bgcolor="#BDBDBD" style="width: 6%;">402</th>
<th align="center" bgcolor="#BDBDBD" style="width: 6%;">403</th>
<th align="center" bgcolor="#BDBDBD" style="width: 6%;">404</th>
<th align="center" bgcolor="#BDBDBD" style="width: 5%;">405</th>
<th align="center" bgcolor="#BDBDBD" style="width: 5%;">406</th>
<th align="center" bgcolor="#BDBDBD" style="width: 5%;">407</th>
<th align="center" bgcolor="#BDBDBD" style="width: 6%;">408</th>
<th align="center" bgcolor="#BDBDBD" style="width: 5%;">409</th>
<th align="center" bgcolor="#BDBDBD" style="width: 5%;">410</th>
<th align="center" bgcolor="#BDBDBD" style="width: 5%;">411</th>
<th align="center" bgcolor="#BDBDBD" style="width: 6%;">498</th>
<th align="center" bgcolor="#BDBDBD" style="width: 7%;"><b>Total '.$campo_distribucion['nu_anio'].'</b></th>
</tr>
</thead>
';
		$this->AddPage();
		//$this->Ln(-20);
		/*$this->writeHTML($htmlPartidas, true, false, false, false, '');
		$this->Ln(-7);*/

		$sqlPartidasDetalle= "SELECT t41.*, tx_codigo,
	CASE WHEN (id_padre IS NOT NULL) THEN
	tx_padre(id_padre)
	ELSE
	descripcion
	END AS descripcion, id_padre
	FROM t41_proyecto_acc_espec_dist as t41
	inner join t39_proyecto_acc_espec as t39 on t41.co_proyecto_acc_espec=t39.co_proyecto_acc_espec
	WHERE t41.id_proyecto='".$campo_distribucion['id_proyecto']."' AND t39.edo_reg is true
	ORDER BY tx_codigo, co_proyecto_acc_espec_dist ASC
;";

		$this->datos_partidas = $comunes->ObtenerFilasBySqlSelect($sqlPartidasDetalle);

$htmlPartidaDetalle.='
<tbody>
';

		foreach($this->datos_partidas as $key => $campoPartida){
			$htmlPartidaDetalle.= '
			<tr style="font-size:7px" nobr="true">
			<td style="width: 4%;">'.$campoPartida['tx_codigo'].'</td>
			<td style="width: 23%;">'.$campoPartida['descripcion'].'</td>
			<td style="width: 6%;">'.$campoPartida['monto_401'].'</td>
			<td style="width: 6%;">'.$campoPartida['monto_402'].'</td>
			<td style="width: 6%;">'.$campoPartida['monto_403'].'</td>
			<td style="width: 6%;">'.$campoPartida['monto_404'].'</td>
			<td style="width: 5%;">'.$campoPartida['monto_405'].'</td>
			<td style="width: 5%;">'.$campoPartida['monto_406'].'</td>
			<td style="width: 5%;">'.$campoPartida['monto_407'].'</td>
			<td style="width: 6%;">'.$campoPartida['monto_408'].'</td>
			<td style="width: 5%;">'.$campoPartida['monto_409'].'</td>
			<td style="width: 5%;">'.$campoPartida['monto_410'].'</td>
			<td style="width: 5%;">'.$campoPartida['monto_411'].'</td>
			<td style="width: 6%;">'.$campoPartida['monto_498'].'</td>
			<td style="width: 7%;">'.$campoPartida['total'].'</td>
			</tr>
			';
		}
$htmlPartidaDetalle.='
</tbody>
</table>';
		$this->writeHTML($htmlPartidaDetalle, true, false, false, false, '');

		}
        }
}

//Crear new PDF documento
$pdf = new MYPDF("L", PDF_UNIT, 'Letter', true, 'UTF-8', false);
$pdf->SetCreator('Yoser Perez');
$pdf->SetAuthor('Secretaria de Planificacion y Estadistica');
$pdf->SetTitle('PROYECTOS - ACCIONES ESPECIFICAS');
$pdf->SetSubject('MI DOCUMENTO');
$pdf->SetKeywords('Planilla, PDF, Registro');
$pdf->SetMargins(15,20,10);
$pdf->SetTopMargin(23);
$pdf->setPrintHeader(false);
$pdf->SetPrintFooter(true);
$pdf->AddPage();
$pdf->cuerpo();
$pdf->Output('POA_PR_'.$_SESSION['ejercicio_fiscal'].'_'.date("H:i:s").'.pdf', 'D');
