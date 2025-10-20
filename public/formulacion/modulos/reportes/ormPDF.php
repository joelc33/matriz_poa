<?php
session_start();
if($_SESSION['estatus']!='OK'){
	http_response_code(403);
	die();
}

require_once (__DIR__.'/../../plugins/eloquent/app.config.php');
require_once (__DIR__.'/../../model/proyecto/tab_meta_financiera.php');

$router->get('/reporte/ubicacion', function(){
	reporte_proyecto_ubicacion();
});
$router->get('/reporte/ubicacion/todo', function(){
	reporte_proyecto_ubicacion_todo();
});

require_once (__DIR__.'/../../model/route.php');



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

function reporte_proyecto_ubicacion() {

	DB::beginTransaction();

	/** Incluir la clase TCPDF*/
	include("../../configuracion/ConexionComun.php");
	define('FPDF_FONTPATH','font/');
	require_once('../../plugins/tcpdf/examples/lang/spa.php');
	require_once('../../plugins/tcpdf/tcpdf.php');
	$original_mem = ini_get('memory_limit');
	ini_set('memory_limit','1024M');
	ini_set('max_execution_time', 600);

	class MYPDF extends TCPDF {
	//=========================================== Datos del Reporte ====================================================/
		public function Footer()
		{
			pie($this,'h',1);
		}
		public function setHeader()
		{
			encabezado($this,'h',1);
		}
	}

	try {

		//Query
		$consulta = tab_meta_financiera::join('t67_metas as t67','t67.co_metas','=','t68_metas_detalle.co_metas')
		->join('t39_proyecto_acc_espec as t39','t39.co_proyecto_acc_espec','=','t67.co_proyecto_acc_espec')
		->join('t26_proyectos as t26','t26.id_proyecto','=','t39.id_proyecto')
		->join('mantenimiento.tab_ejecutores as t24','t24.id','=','t39.co_ejecutores')
		->join('mantenimiento.tab_ejecutores as t24a','t24a.id_ejecutor','=','t26.id_ejecutor')
		->join('mantenimiento.tab_municipio_detalle as t13','t13.id','=','t68_metas_detalle.co_municipio')
		->join('mantenimiento.tab_fuente_financiamiento as t06','t06.id','=','t68_metas_detalle.co_fuente')
		->select( 't26.id_proyecto', DB::raw('t26.nombre as de_proyecto'),
		DB::raw("t39.tx_codigo ||' - '|| t39.descripcion as de_ae"),
		DB::raw("t24.id_ejecutor||' - '|| t24.tx_ejecutor as ejecutor"),
		DB::raw("t24a.tx_ejecutor as ejecutor_a"),
		DB::raw("t67.codigo ||' - '|| t67.nb_meta as de_actividad"),
		'de_municipio', 'mo_presupuesto', 'de_fuente_financiamiento')
		//->where('t68_metas_detalle.co_municipio', '=', $_GET['id_tab_municipio'])
		->where('t26.id_ejercicio', '=', $_SESSION['ejercicio_fiscal'])
		->where('t26.edo_reg', '=', true)
		->where('t39.edo_reg', '=', true)
		->where('t67.edo_reg', '=', true)
		->where('t68_metas_detalle.edo_reg', '=', true)
		->when($_GET['id_tab_municipio'], function ($query) {
			return $query->where('t68_metas_detalle.co_municipio', '=', $_GET['id_tab_municipio']);
		})
		->when($_GET['fuente_financiamiento'], function ($query) {
		 	return $query->where('t68_metas_detalle.co_fuente', '=', $_GET['fuente_financiamiento']);
 		})
		->when($_GET['ejecutor'], function ($query) {
			return $query->where('t24.id_ejecutor', '=', $_GET['ejecutor']);
		})
		/*->when($_GET['ejecutor'], function ($query) {
			return $query->where('t26.id_ejecutor', '=', $_GET['ejecutor']);
		})*/
		->orderBy(DB::raw('id_proyecto', 'tx_codigo', 't67.codigo'), 'ASC')
		->get();

/***distribucion fisica***/
$htmlUbicacion = '
<!-- Tabla 1 -->
<table border="0.1" style="width:100%" style="font-size:9px" cellpadding="3">
<thead>
<tr align="center" bgcolor="#BDBDBD">
<th colspan="6" style="width: 100%;"><b>DISTRIBUCIÓN DE PROYECTOS POR MUNICIPIO - AÑO '.$_SESSION['ejercicio_fiscal'].'</b></th>
</tr>';
$htmlUbicacion.='
<tr style="font-size:6px">
<th colspan="6" style="width: 100%;">FILTROS: ';
	if (!empty($_GET['ejecutor'])) {
		$htmlUbicacion.='EJECUTOR: '.$_GET['ejecutor'];
	}
$htmlUbicacion.='</th>
</tr>';
$htmlUbicacion.='
<tr style="font-size:6px">
<th align="center" bgcolor="#BDBDBD" style="width: 10%;">COD. PROYECTO EJEC.</th>
<th align="center" bgcolor="#BDBDBD" style="width: 15%;">DESCRIPCION PROYECTO</th>
<th align="center" bgcolor="#BDBDBD" style="width: 15%;">ACCION ESPECIFICA</th>
<th align="center" bgcolor="#BDBDBD" style="width: 15%;">ENTE EJECUTOR RESPONSABLE</th>
<th align="center" bgcolor="#BDBDBD" style="width: 15%;">ACTIVIDAD</th>
<th align="center" bgcolor="#BDBDBD" style="width: 10%;">MUNICIPIO</th>
<th align="center" bgcolor="#BDBDBD" style="width: 10%;">MONTO</th>
<th align="center" bgcolor="#BDBDBD" style="width: 10%;">FUENTE FINANCIAMIENTO</th>
</tr>
</thead>
';

$htmlUbicacion.='
<tbody>
';

		foreach ($consulta as $key => $value) {
		// Set cell An to the "name" column from the database (assuming you have a column called name)

			$htmlUbicacion.='
			<tr style="font-size:7px" nobr="true">
				<td style="width: 10%;">'.$value->id_proyecto.' - '.$value->ejecutor_a.'</td>
				<td style="width: 15%;" align="justify">'.$value->de_proyecto.'</td>
				<td style="width: 15%;" align="justify">'.$value->de_ae.'</td>
				<td style="width: 15%;" align="justify">'.$value->ejecutor.'</td>
				<td style="width: 15%;" align="justify">'.$value->de_actividad.'</td>
				<td style="width: 10%;" align="center">'.$value->de_municipio.'</td>
				<td style="width: 10%;">'.formatoDinero($value->mo_presupuesto).'</td>
				<td style="width: 10%;" align="center">'.$value->de_fuente_financiamiento.'</td>
			</tr>';

		}

$htmlUbicacion.='
</tbody>
</table>';

		//Crear new PDF documento
		$pdf = new MYPDF("L", PDF_UNIT, 'Letter', true, 'UTF-8', false);
		$pdf->SetCreator('Yoser Perez');
		$pdf->SetAuthor('Secretaria de Planificacion y Estadistica');
		$pdf->SetTitle('PROYECTOS - UBICACIÓN GEOGRAFICA');
		$pdf->SetSubject('MI DOCUMENTO');
		$pdf->SetKeywords('Planilla, PDF, Registro');
		$pdf->SetMargins(15,20,10);
		$pdf->SetTopMargin(23);
		$pdf->setPrintHeader(false);
		$pdf->SetPrintFooter(true);
		$pdf->AddPage();
		$pdf->writeHTML($htmlUbicacion, true, false, false, false, '');
		$pdf->Output('UBICACION_PR_'.$_SESSION['ejercicio_fiscal'].'_'.date("H:i:s").'.pdf', 'D');

		DB::commit();

	}catch (\Illuminate\Database\QueryException $e)
	{
		DB::rollback();
		header('Content-Type: text/html');
		echo json_encode(array(
			'success' => false,
			'msg' => array('ERROR ('.$e->getCode().'):'=> $e->getMessage())
			//'msg' => array('ERROR ('.$e->getCode().'):'=> 'CODIGO['.$e->getCode().']: Error en Transaccion, verfique e intente de nuevo.')
		));
	}

}

function reporte_proyecto_ubicacion_todo() {

	DB::beginTransaction();

	/** Incluir la clase TCPDF*/
	include("../../configuracion/ConexionComun.php");
	define('FPDF_FONTPATH','font/');
	require_once('../../plugins/tcpdf/examples/lang/spa.php');
	require_once('../../plugins/tcpdf/tcpdf.php');
	$original_mem = ini_get('memory_limit');
	ini_set('memory_limit','1024M');
	ini_set('max_execution_time', 600);

	class MYPDF extends TCPDF {
	//=========================================== Datos del Reporte ====================================================/
		public function Footer()
		{
			pie($this,'h',1);
		}
		public function setHeader()
		{
			encabezado($this,'h',1);
		}
	}

	try {

		//Query
		$consulta = tab_meta_financiera::join('t67_metas as t67','t67.co_metas','=','t68_metas_detalle.co_metas')
		->join('t39_proyecto_acc_espec as t39','t39.co_proyecto_acc_espec','=','t67.co_proyecto_acc_espec')
		->join('t26_proyectos as t26','t26.id_proyecto','=','t39.id_proyecto')
		->join('mantenimiento.tab_ejecutores as t24','t24.id','=','t39.co_ejecutores')
		->join('mantenimiento.tab_municipio_detalle as t13','t13.id','=','t68_metas_detalle.co_municipio')
		->join('mantenimiento.tab_fuente_financiamiento as t06','t06.id','=','t68_metas_detalle.co_fuente')
		->select( 't26.id_proyecto', DB::raw('t26.nombre as de_proyecto'),
		DB::raw("t39.tx_codigo ||' - '|| t39.descripcion as de_ae"),
		DB::raw(" t24.id_ejecutor||' - '|| tx_ejecutor as ejecutor"),
		DB::raw("t67.codigo ||' - '|| t67.nb_meta as de_actividad"),
		'de_municipio', 'mo_presupuesto', 'de_fuente_financiamiento')
		->where('t26.id_ejercicio', '=', $_SESSION['ejercicio_fiscal'])
		->where('t26.edo_reg', '=', true)
		->where('t39.edo_reg', '=', true)
		->where('t67.edo_reg', '=', true)
		->where('t68_metas_detalle.edo_reg', '=', true)
		->orderBy(DB::raw('id_proyecto', 'tx_codigo', 't67.codigo'), 'ASC')
		->get();

/***distribucion fisica***/
$htmlUbicacion = '
<!-- Tabla 1 -->
<table border="0.1" style="width:100%" style="font-size:9px" cellpadding="3">
<thead>
<tr align="center" bgcolor="#BDBDBD">
<th colspan="6" style="width: 100%;"><b>DISTRIBUCIÓN DE PROYECTOS POR MUNICIPIO - AÑO '.$_SESSION['ejercicio_fiscal'].'</b></th>
</tr>
<tr style="font-size:6px">
<th align="center" bgcolor="#BDBDBD" style="width: 10%;">COD. PROYECTO</th>
<th align="center" bgcolor="#BDBDBD" style="width: 15%;">DESCRIPCION PROYECTO</th>
<th align="center" bgcolor="#BDBDBD" style="width: 15%;">ACCION ESPECIFICA</th>
<th align="center" bgcolor="#BDBDBD" style="width: 15%;">ENTE EJECUTOR RESPONSABLE</th>
<th align="center" bgcolor="#BDBDBD" style="width: 15%;">ACTIVIDAD</th>
<th align="center" bgcolor="#BDBDBD" style="width: 10%;">MUNICIPIO</th>
<th align="center" bgcolor="#BDBDBD" style="width: 10%;">MONTO</th>
<th align="center" bgcolor="#BDBDBD" style="width: 10%;">FUENTE FINANCIAMIENTO</th>
</tr>
</thead>
';

$htmlUbicacion.='
<tbody>
';

		foreach ($consulta as $key => $value) {
		// Set cell An to the "name" column from the database (assuming you have a column called name)

			$htmlUbicacion.='
			<tr style="font-size:7px" nobr="true">
			<td style="width: 10%;">'.$value->id_proyecto.'</td>
			<td style="width: 15%;" align="justify">'.$value->de_proyecto.'</td>
			<td style="width: 15%;" align="justify">'.$value->de_ae.'</td>
			<td style="width: 15%;" align="justify">'.$value->ejecutor.'</td>
			<td style="width: 15%;" align="justify">'.$value->de_actividad.'</td>
			<td style="width: 10%;" align="center">'.$value->de_municipio.'</td>
			<td style="width: 10%;">'.formatoDinero($value->mo_presupuesto).'</td>
			<td style="width: 10%;" align="center">'.$value->de_fuente_financiamiento.'</td>
			</tr>';

		}

$htmlUbicacion.='
</tbody>
</table>';

		//Crear new PDF documento
		$pdf = new MYPDF("L", PDF_UNIT, 'Letter', true, 'UTF-8', false);
		$pdf->SetCreator('Yoser Perez');
		$pdf->SetAuthor('Secretaria de Planificacion y Estadistica');
		$pdf->SetTitle('PROYECTOS - UBICACIÓN GEOGRAFICA');
		$pdf->SetSubject('MI DOCUMENTO');
		$pdf->SetKeywords('Planilla, PDF, Registro');
		$pdf->SetMargins(15,20,10);
		$pdf->SetTopMargin(23);
		$pdf->setPrintHeader(false);
		$pdf->SetPrintFooter(true);
		$pdf->AddPage();
		$pdf->writeHTML($htmlUbicacion, true, false, false, false, '');
		$pdf->Output('UBICACION_PR_'.$_SESSION['ejercicio_fiscal'].'_'.date("H:i:s").'.pdf', 'D');

		DB::commit();

	}catch (\Illuminate\Database\QueryException $e)
	{
		DB::rollback();
		header('Content-Type: text/html');
		echo json_encode(array(
			'success' => false,
			'msg' => array('ERROR ('.$e->getCode().'):'=> $e->getMessage())
			//'msg' => array('ERROR ('.$e->getCode().'):'=> 'CODIGO['.$e->getCode().']: Error en Transaccion, verfique e intente de nuevo.')
		));
	}

}
