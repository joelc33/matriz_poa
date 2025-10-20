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

	function getRegistro($id_ejecutor){

		$condicion='';
		if($id_ejecutor!= '')
		{
			$condicion.= " t24.id_ejecutor = '".$id_ejecutor."' AND ";	    
		}

		$comunes = new ConexionComun();

		$sql = "SELECT t26.id_ejecutor, tx_ejecutor
		FROM t26_proyectos as t26 
		inner join mantenimiento.tab_ejecutores as t24 on t26.id_ejecutor=t24.id_ejecutor
		where ".$condicion." t26.edo_reg is true AND t26.id_ejercicio = '".$_SESSION['ejercicio_fiscal']."'
		group by 1,2 order by 1,2 asc";

		$this->datos = $comunes->ObtenerFilasBySqlSelect($sql);
		$this->cantidadTotal = $comunes->getFilas($sql);
	}

	public function Footer()	
	{
		/*$this->getRegistro('PR130120150002','');
		foreach($this->datos as $key => $campo){
			$tipo = $campo["co_tipo"];
		}*/
		pie($this,'h',1);
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
	$this->SetFont('dejavusans','',11);
	//$this->Ln(-20);
	$contador=0;
	foreach($this->datos as $key => $campo){              
		
	$html1 = '
	<table border="0.1" style="width:100%" style="font-size:9px" cellpadding="3">
	<tbody>
	<tr align="center" bgcolor="#BDBDBD">
	<td colspan="3"><b>RESUMEN DE PROYECTOS POR EJECUTOR - EJERCICIO FISCAL '.$_SESSION['ejercicio_fiscal'].'</b></td>
	</tr>
	<tr style="font-size:7px">
	<td colspan="3"><b>EJECUTOR:</b> '.$campo['id_ejecutor'].' - '.$campo['tx_ejecutor'].'</td>
	</tr>
	</tbody>
	</table>
	';					    
		$this->writeHTML($html1, true, false, false, false, '');	
		$this->Ln(-3);

	$sqlProyecto = "SELECT id_proyecto, nombre, monto_cargado(id_proyecto) as mo_registrado
	FROM t26_proyectos WHERE id_ejecutor='".$campo['id_ejecutor']."' and edo_reg is true AND id_ejercicio = '".$_SESSION['ejercicio_fiscal']."' order by 1 asc";  
	$this->datos_proyecto = $comunes->ObtenerFilasBySqlSelect($sqlProyecto);

$html3='';
	foreach($this->datos_proyecto as $key => $campo2){
		$html3.= '
		<!-- Tabla 3 -->
		<table border="0.1" style="width:100%" style="font-size:7px" cellpadding="3">
		<thead>
		<tr align="left" bgcolor="#BDBDBD">
		<th colspan="3" bgcolor="#BDBDBD"><b>PROYECTO</b></th>
		</tr>
		<tr style="font-size:7px">
		<th align="center" bgcolor="#BDBDBD" style="width: 15%;">CODIGO</th>
		<th align="center" bgcolor="#BDBDBD" style="width: 70%;">DESCRIPCION</th>
		<th align="center" bgcolor="#BDBDBD" style="width: 15%;">MONTO</th>
		</tr>
		<tr style="font-size:7px">
		<th style="width: 15%;">'.$campo2['id_proyecto'].'</th>
		<th style="width: 70%;">'.$campo2['nombre'].'</th>
		<th style="width: 15%;">'.$this->formatoDinero($campo2['mo_registrado']).'</th>
		</tr>
		<tr align="left" bgcolor="#BDBDBD">
		<th colspan="3" bgcolor="#BDBDBD"><b>ACCIONES ESPECIFICAS</b></th>
		</tr>
		<tr style="font-size:7px">
		<th align="center" bgcolor="#BDBDBD" style="width: 15%;">CODIGO</th>
		<th align="center" bgcolor="#BDBDBD" style="width: 70%;">DESCRIPCION</th>
		<th align="center" bgcolor="#BDBDBD" style="width: 15%;">MONTO</th>
		</tr>
		</thead>
		<tbody>
		';
	$sqlAe = "SELECT tx_codigo, t39.descripcion, monto_cargado_ae_proy(co_proyecto_acc_espec) as mo_cargado
		FROM t39_proyecto_acc_espec as t39
		inner join t26_proyectos as t26 on t26.id_proyecto=t39.id_proyecto
		where t39.id_proyecto='".$campo2['id_proyecto']."' AND t39.edo_reg is true and t26.edo_reg is true and id_padre is null order by 1 ASC;";  

	$sqlAeMonto = "SELECT SUM(monto_cargado_ae_proy(co_proyecto_acc_espec)) as mo_total_ae
	FROM t39_proyecto_acc_espec WHERE id_proyecto='".$campo2['id_proyecto']."' and id_padre is null"; 

		$this->ae_datos = $comunes->ObtenerFilasBySqlSelect($sqlAe);
		foreach($this->ae_datos as $key => $campo4){
			$html3.= '
			<tr style="font-size:7px" nobr="true">
			<td style="width: 15%;">'.$campo4['tx_codigo'].'</td>
			<td style="width: 70%;">'.$campo4['descripcion'].'</td>
			<td style="width: 15%;">'.$this->formatoDinero($campo4['mo_cargado']).'</td>
			</tr>
			';
		}
	$this->ae_monto = $comunes->ObtenerFilasBySqlSelect($sqlAeMonto);
	$html3.= '
	<tr style="font-size:7px" nobr="true">
	<td align="rigth" style="width: 85%;"><b>SUBTOTAL</b></td>
	<td style="width: 15%;">'.$this->formatoDinero($this->ae_monto[0]['mo_total_ae']).'</td>
	</tr>
	<tr align="left" bgcolor="#BDBDBD">
	<td colspan="2" bgcolor="#BDBDBD"><b>FUENTES DE FINANCIAMIENTO</b></td>
	</tr>
	<tr style="font-size:7px">
	<td align="center" bgcolor="#BDBDBD" style="width: 85%;">DESCRIPCION</td>
	<td align="center" bgcolor="#BDBDBD" style="width: 15%;">MONTO</td>
	</tr>
	';

	$sqlFuenteF = "SELECT de_tipo_fondo as tx_tipo_fondo, SUM(mo_fondo) as mo_fondo
			FROM t62_proyecto_distribucion as t62
			inner join mantenimiento.tab_tipo_fondo as t61 on t61.id=t62.co_tipo_fondo
			inner join t26_proyectos as t26 on t26.id_proyecto=t62.id_proyecto
			WHERE t26.edo_reg is true AND t62.id_proyecto='".$campo2['id_proyecto']."' AND mo_fondo> 0 group by 1 order by 1 ASC;";  

	$sqlFuenteFMonto = "SELECT SUM(mo_fondo) as mo_total_fondo
	FROM t62_proyecto_distribucion WHERE id_proyecto='".$campo2['id_proyecto']."'";

		$this->datos_FuenteF = $comunes->ObtenerFilasBySqlSelect($sqlFuenteF);
		foreach($this->datos_FuenteF as  $key => $campo5){
			$html3.= '
			<tr style="font-size:7px" nobr="true">
			<td style="width: 85%;">'.$campo5['tx_tipo_fondo'].'</td>
			<td style="width: 15%;">'.$this->formatoDinero($campo5['mo_fondo']).'</td>
			</tr>
			';
		}

		$this->fondo_monto = $comunes->ObtenerFilasBySqlSelect($sqlFuenteFMonto);
		$html3.= '
		<tr style="font-size:7px" nobr="true">
		<td align="rigth" style="width: 85%;"><b>SUBTOTAL</b></td>
		<td style="width: 15%;">'.$this->formatoDinero($this->fondo_monto[0]['mo_total_fondo']).'</td>
		</tr>
		</tbody>
		</table>
		<table border="0">
		<tbody>
		<tr>
		<td style="width: 100%;"></td>
		</tr>
		</tbody>
		</table>
		';
}
		$this->writeHTML($html3, true, false, false, false, '');
		$this->Ln(-3);

		$contador=$contador+1;
		if($this->cantidadTotal>$contador){
			$this->AddPage();
			//$this->Ln(-20);
		}
		}
        }
}

//Crear new PDF documento
$pdf = new MYPDF("L", PDF_UNIT, 'Letter', true, 'UTF-8', false);
$pdf->SetCreator('Yoser Perez');
$pdf->SetAuthor('Secretaria de Planificacion y Estadistica');
$pdf->SetTitle('RESUMEN EJECUTORES');
$pdf->SetSubject('MI DOCUMENTO');
$pdf->SetKeywords('Planilla, PDF, Registro');
$pdf->SetMargins(15,20,10);
$pdf->SetTopMargin(23);
$pdf->setPrintHeader(false);
$pdf->SetPrintFooter(true);
$pdf->AddPage();
$pdf->cuerpo();
$pdf->Output('POA_PROYECTOS_RESUMEN_'.$_SESSION['ejercicio_fiscal'].'_'.date("H:i:s").'.pdf', 'D');
