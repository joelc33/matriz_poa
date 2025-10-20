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

	function getRegistro($id_ejecutor, $id_proy_ae){

		$condicionPR.= " t26.id_ejecutor = '".$id_ejecutor."' AND ";
		$condicionPRAE.= " t24a.id_ejecutor = '".$id_ejecutor."' AND ";	    

		$comunes = new ConexionComun();

		$sql = "SELECT co_proyectos, t26.id_ejecutor, tx_ejecutor, id_ejercicio, id_proyecto as id_proy_ac, nombre as de_nombre,
(SELECT sum(nu_monto) FROM t42_proyecto_acc_espec_partida as t42a
  inner join t39_proyecto_acc_espec as t39a on t42a.co_proyecto_acc_espec = t39a.co_proyecto_acc_espec and sp_verificar_hijo_ae(t39a.co_proyecto_acc_espec) is false
  inner join t26_proyectos as t26a on t39a.id_proyecto = t26a.id_proyecto
  where id_tab_ejercicio_fiscal = t26.id_ejercicio::int and t26a.id_ejecutor = t26.id_ejecutor and t42a.edo_reg is true) as total_ejecutor, monto_cargado(id_proyecto) as mo_registrado
FROM t26_proyectos as t26
inner join mantenimiento.tab_ejecutores as t02 on t26.id_ejecutor = t02.id_ejecutor
where ".$condicionPR." t26.id_ejercicio = '".$_SESSION['ejercicio_fiscal']."' and edo_reg is true

UNION

SELECT co_proyectos, t26.id_ejecutor, t24a.tx_ejecutor, id_ejercicio, t26.id_proyecto as id_proy_ac, nombre as de_nombre,
(
SELECT sum(mo_proy_ae_meta(co_proyecto_acc_espec))
	FROM t39_proyecto_acc_espec as t39s
	inner join mantenimiento.tab_unidad_medida as t21s on t39s.co_unidades_medida=t21s.id
	inner join mantenimiento.tab_ejecutores as t24s on t39s.co_ejecutores=t24s.id
	inner join t26_proyectos as t26s on t39s.id_proyecto=t26s.id_proyecto
	inner join mantenimiento.tab_ejecutores as t24bs on t26s.id_ejecutor=t24bs.id_ejecutor
WHERE t24s.id_ejecutor=t24a.id_ejecutor AND t26s.id_ejercicio = t26.id_ejercicio AND t39s.edo_reg is true AND t26s.edo_reg is true


) as total_ejecutor, (
SELECT sum(mo_proy_ae_meta(co_proyecto_acc_espec))
	FROM t39_proyecto_acc_espec as t39s
	inner join mantenimiento.tab_unidad_medida as t21s on t39s.co_unidades_medida=t21s.id
	inner join mantenimiento.tab_ejecutores as t24s on t39s.co_ejecutores=t24s.id
	inner join t26_proyectos as t26s on t39s.id_proyecto=t26s.id_proyecto
	inner join mantenimiento.tab_ejecutores as t24bs on t26s.id_ejecutor=t24bs.id_ejecutor
WHERE t24s.id_ejecutor=t24a.id_ejecutor AND t26s.id_ejercicio = t26.id_ejercicio AND t39s.edo_reg is true AND t26s.edo_reg is true
 AND t26s.id_proyecto=t26.id_proyecto
) as mo_registrado
FROM t26_proyectos as t26
inner join mantenimiento.tab_ejecutores as t24 on t26.id_ejecutor = t24.id_ejecutor
inner join t39_proyecto_acc_espec as t39 on t26.id_proyecto=t39.id_proyecto
inner join mantenimiento.tab_ejecutores as t24a on t39.co_ejecutores=t24a.id
where ".$condicionPRAE." t26.id_ejercicio = '".$_SESSION['ejercicio_fiscal']."' and t26.edo_reg is true order by 1 ASC

;";

		$this->datos = $comunes->ObtenerFilasBySqlSelect($sql);
		$this->cantidadTotal = $comunes->getFilas($sql);
	}

	public function Footer()	
	{
		pie($this,'v',2);
	}
	public function setHeader()	
	{
		encabezado($this,'v',1);
	}
        public function cuerpo()
        {
		
	$id_ejecutor = decode($_GET['id_ejecutor']);

	$this->getRegistro($id_ejecutor, $id_proy_ae);
       	$comunes = new ConexionComun();

	$contador=0;
	$portada=0;
	$acumulador_ac_p=0;
	foreach($this->datos as $key => $campo){
		$datosEnunciado='PROYECTO';
		$datosEnunciadoSUBTOTAL='PROYECTO';

		$sqlAE = "SELECT co_proyecto_acc_espec, tx_codigo, id_proyecto, descripcion
		FROM t39_proyecto_acc_espec as t39
		where id_proyecto = '".$campo['id_proy_ac']."' and edo_reg is true 
		and co_proyecto_acc_espec in (
			SELECT co_proyecto_acc_espec
			FROM t26_proyectos as t26
			inner join mantenimiento.tab_ejecutores as t24 on t26.id_ejecutor = t24.id_ejecutor
			inner join t39_proyecto_acc_espec as t39s on t26.id_proyecto=t39s.id_proyecto
			inner join mantenimiento.tab_ejecutores as t24a on t39s.co_ejecutores=t24a.id
			where t24a.id_ejecutor = '".$id_ejecutor."' AND t26.id_ejercicio = '".$_SESSION['ejercicio_fiscal']."' and t26.edo_reg is true order by 1 ASC
		)
		order by tx_codigo ASC;";
               	
		$this->datos_ae = $comunes->ObtenerFilasBySqlSelect($sqlAE);

		$this->SetFont('','',11);

/******POA*********/
$html1 = '
<table border="0.1" style="width:100%" style="font-size:10px" cellpadding="3">
<tbody>
<tr align="center" bgcolor="#BDBDBD">
<td colspan="3"><b>DISTRIBUCION DE PARTIDAS - PRESUPUESTO AÑO '.$campo['id_ejercicio'].'</b></td>
</tr>
<tr style="font-size:9px">
<td style="width: 22%;" bgcolor="#BDBDBD"><b>EJECUTOR</b></td>
<td style="width: 78%;">'.$campo['id_ejecutor'].' - '.$campo['tx_ejecutor'].'</td>
</tr>
<tr style="font-size:9px">
<td style="width: 22%;" bgcolor="#BDBDBD"><b>'.$datosEnunciado.'</b></td>
<td style="width: 78%;">'.$campo['id_proy_ac'].' - '.$campo['de_nombre'].'</td>
</tr>
</tbody>
</table>
';	

		$this->writeHTML($html1, true, false, false, false, '');

foreach($this->datos_ae as $key => $campo2){
	
		$this->Ln(-3);
$html23=''; 
$html23.= '
<!-- Tabla 2 -->
<table border="0.1" style="width:100%" style="font-size:9px" cellpadding="3">
<thead>
<tr style="font-size:9px">
<td style="width: 22%;" bgcolor="#BDBDBD"><b>ACCION ESPECIFICA</b></td>
<td style="width: 78%;">'.$campo2['tx_codigo'].' - '.$campo2['descripcion'].'</td>
</tr>
<tr style="font-size:8px">
<th align="center" bgcolor="#BDBDBD" style="width: 10%;"><b>PARTIDA</b></th>
<th align="center" bgcolor="#BDBDBD" style="width: 70%;"><b>DENOMINACIÓN</b></th>
<th align="center" bgcolor="#BDBDBD" style="width: 20%;"><b>MONTO</b></th>
</tr>
</thead>
';

		$sqlAEP = "SELECT co_proyecto_acc_espec, co_partida, tx_denominacion, nu_monto, id_tab_ejercicio_fiscal
			FROM t42_proyecto_acc_espec_partida
			where co_proyecto_acc_espec = ".$campo2['co_proyecto_acc_espec']." and edo_reg is true order by co_partida ASC;";
               	
		$this->datos_ae_partida = $comunes->ObtenerFilasBySqlSelect($sqlAEP);

$html23.='
<tbody>';
$acumulador_partida=0;
foreach($this->datos_ae_partida as $key => $campo3){
		$html23.='
		<tr style="font-size:8px" nobr="true">
		<td style="width: 10%;" >'.$campo3['co_partida'].'</td>
		<td style="width: 70%;" >'.$campo3['tx_denominacion'].'</td>
		<td style="width: 20%;" >'.$this->formatoDinero($campo3['nu_monto']).'</td>
		</tr>';
		$acumulador_partida = $acumulador_partida + $campo3['nu_monto'];
}

		$acumulador_ac_p = $acumulador_ac_p + $acumulador_partida;

$html23.='
<tr style="font-size:9px" nobr="true">
<td colspan="2" style="text-align: rigth;"><b>Total A.E.</b></td>
<td>'.$this->formatoDinero($acumulador_partida).'</td>
</tr>
<tr style="font-size:9px" nobr="true">
<td colspan="2" style="text-align: rigth;"><b>SubTotal Proyecto</b></td>
<td>'.$this->formatoDinero($campo['mo_registrado']).'</td>
</tr>
<tr style="font-size:9px" nobr="true">
<td colspan="2" style="text-align: rigth;"><b>Total Ejecutor</b></td>
<td>'.$this->formatoDinero($campo['total_ejecutor']).'</td>
</tr>
</tbody>
</table>';

		$this->writeHTML($html23, true, false, false, false, '');

}

		$contador=$contador+1;
		$portada=$portada+1;
		if($this->cantidadTotal>$contador){
			$this->AddPage();
		}
		}
        }
}

//Crear new PDF documento
$pdf = new MYPDF("P", PDF_UNIT, 'Letter', true, 'UTF-8', false);
$pdf->SetCreator('Yoser Perez');
$pdf->SetAuthor('Secretaria de Planificacion y Estadistica');
$pdf->SetTitle('PROYECTOS - RESUMEN DE PARTIDAS');
$pdf->SetSubject('MI DOCUMENTO');
$pdf->SetKeywords('Planilla, PDF, Registro');
$pdf->SetMargins(15,20,10);
$pdf->SetTopMargin(23);
$pdf->setPrintHeader(false);
$pdf->SetPrintFooter(true);
$pdf->AddPage();
$pdf->cuerpo();
$pdf->Output('PARTIDAS_PR_'.$_SESSION['ejercicio_fiscal'].'_'.date("H:i:s").'.pdf', 'D');
