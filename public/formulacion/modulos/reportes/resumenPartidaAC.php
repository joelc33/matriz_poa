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

		$condicionPR='';
		$condicionAC='';
		if($id_ejecutor!= '')
		{
			$condicionPR.= " t26.id_ejecutor = '".$id_ejecutor."' AND ";
			$condicionAC.= " t46.id_ejecutor = '".$id_ejecutor."' AND ";	    
		}

		if($id_proy_ae!= '')
		{
			$condicionPR.= " t26.id_proyecto = '".$id_proy_ae."' AND ";
			$condicionAC.= " ('AC' || t24.id_ejecutor || t46.id_ejercicio || lpad(t47.id_accion::text, 5, '0')) = '".$id_proy_ae."' AND ";	    
		}

		$comunes = new ConexionComun();

		$sql = "SELECT t46.id, t46.id_ejecutor, tx_ejecutor, id_ejercicio, id_accion, de_nombre, 'AC' || t46.id_ejecutor || id_ejercicio || lpad(t46.id_accion::text, 5, '0') as id_proy_ac, 
(select sum(t54a.monto) FROM t54_ac_ae_partidas as t54a 
inner join t46_acciones_centralizadas as t46a on t54a.id_accion_centralizada=t46a.id 
where t46a.id_ejecutor = t46.id_ejecutor and t46a.id_ejercicio = t46.id_ejercicio) as total_ejecutor, coalesce(t46.monto_calc, 0) as monto_calc
		FROM t46_acciones_centralizadas as t46
		inner join mantenimiento.tab_ejecutores as t02 on t46.id_ejecutor = t02.id_ejecutor
		inner join mantenimiento.tab_ac_predefinida as t52 on t52.id = t46.id_accion
		where ".$condicionAC." id_ejercicio = ".$_SESSION['ejercicio_fiscal']." order by t46.id_accion ASC;";

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

	$contador=0;
	$portada=0;
	$acumulador_ac_p=0;
	$ac_ant = '';
	foreach($this->datos as $key => $campo){
		$datosEnunciado='ACCION CENTRALIZADA';
		$datosEnunciadoSUBTOTAL='ACCION CENTRALIZADA';
		$fieldDatos='Datos de la Accion Centralizada';
		$co_proy_ac='Codigo de la Accion Centralizada';

		$sqlAE = "SELECT id_accion_centralizada, id_accion, nu_numero, de_nombre
		FROM t47_ac_accion_especifica as t47
		inner join mantenimiento.tab_ac_ae_predefinida as t53 on t53.id = t47.id_accion
		where id_accion_centralizada= ".$campo['id']." order by id_accion ASC;";
               	
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
<td style="width: 78%;">'.$campo2['nu_numero'].' - '.$campo2['de_nombre'].'</td>
</tr>
<tr style="font-size:8px">
<th align="center" bgcolor="#BDBDBD" style="width: 10%;"><b>PARTIDA</b></th>
<th align="center" bgcolor="#BDBDBD" style="width: 70%;"><b>DENOMINACIÓN</b></th>
<th align="center" bgcolor="#BDBDBD" style="width: 20%;"><b>MONTO</b></th>
</tr>
</thead>
';

		$sqlAEP = "SELECT id_accion_centralizada, id_accion, t54.co_partida, tx_nombre, monto, t54.id_tab_ejercicio_fiscal
FROM t54_ac_ae_partidas as t54
inner join mantenimiento.tab_partidas as t02 on t54.co_partida=t02.co_partida and t54.id_tab_ejercicio_fiscal = t02.id_tab_ejercicio_fiscal
		where id_accion_centralizada = ".$campo2['id_accion_centralizada']." and id_accion = ".$campo2['id_accion']." order by co_partida ASC;";
               	
		$this->datos_ae_partida = $comunes->ObtenerFilasBySqlSelect($sqlAEP);

$html23.='
<tbody>';
$acumulador_partida=0;
foreach($this->datos_ae_partida as $key => $campo3){
		$html23.='
		<tr style="font-size:8px" nobr="true">
		<td style="width: 10%;" >'.$campo3['co_partida'].'</td>
		<td style="width: 70%;" >'.$campo3['tx_nombre'].'</td>
		<td style="width: 20%;" >'.number_format($campo3['monto'], 2, ',','.').'</td>
		</tr>';
		$acumulador_partida = $acumulador_partida + $campo3['monto'];
}

		if($campo2['id_accion_centralizada']!=$ac_ant){ $acumulador_ac_p = 0; }

		$acumulador_ac_p = $acumulador_ac_p + $acumulador_partida;

		$ac_ant = $campo2['id_accion_centralizada'];

$html23.='
<tr style="font-size:9px" nobr="true">
<td colspan="2" style="text-align: rigth;"><b>Total A.E.</b></td>
<td>'.number_format($acumulador_partida, 2, ',','.').'</td>
</tr>
<tr style="font-size:9px" nobr="true">
<td colspan="2" style="text-align: rigth;"><b>SubTotal A.C.</b></td>
<td>'.number_format($campo['monto_calc'], 2, ',','.').'</td>
</tr>
<tr style="font-size:9px" nobr="true">
<td colspan="2" style="text-align: rigth;"><b>Total Ejecutor</b></td>
<td>'.number_format($campo['total_ejecutor'], 2, ',','.').'</td>
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
$pdf->SetTitle('ACCIONES CENTRALIZADAS - RESUMEN DE PARTIDAS');
$pdf->SetSubject('MI DOCUMENTO');
$pdf->SetKeywords('Planilla, PDF, Registro');
$pdf->SetMargins(15,20,10);
$pdf->SetTopMargin(23);
$pdf->setPrintHeader(false);
$pdf->SetPrintFooter(true);
$pdf->AddPage();
$pdf->cuerpo();
$pdf->Output('PARTIDAS_AC_'.$_SESSION['ejercicio_fiscal'].'_'.date("H:i:s").'.pdf', 'D');
