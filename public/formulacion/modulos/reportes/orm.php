<?php
session_start();
if($_SESSION['estatus']!='OK'){
	http_response_code(403);
	die();
}

require_once (__DIR__.'/../../plugins/eloquent/app.config.php');
require_once (__DIR__.'/../../model/tab_proyecto_aepartida.php');
require_once (__DIR__.'/../../model/tab_ac_ae_partida.php');
require_once (__DIR__.'/../../model/mantenimiento/tab_municipio_detalle.php');
require_once (__DIR__.'/../../model/mantenimiento/tab_parroquia.php');
require_once (__DIR__.'/../../model/proyecto/tab_meta_financiera.php');

$router->get('/exportar/partida/pr', function(){
	partida_proyecto_ae();
});
$router->get('/exportar/partida/pr/todo', function(){
	partida_proyecto_ae_todo();
});
$router->get('/exportar/partida/ac', function(){
	partida_ac_ae();
});
$router->get('/exportar/partida/ac/todo', function(){
	partida_ac_ae_todo();
});
$router->get('/municipio', function(){
	municipio();
});
$router->post('/parroquia', function(){
	parroquia();
});
$router->get('/exportar/ubicacion', function(){
	exportar_proyecto_ubicacion();
});
$router->get('/exportar/ubicacion/todo', function(){
	exportar_proyecto_ubicacion_todo();
});

require_once (__DIR__.'/../../model/route.php');

function partida_proyecto_ae() {

	DB::beginTransaction();

	/** Incluir la clase PHPExcel_IOFactory agregada en el directorio /lib/vendor/PHPExcel */
	include("../../plugins/reader/Classes/PHPExcel/IOFactory.php");

	try {

		// Instantiate a new PHPExcel object
		$objPHPExcel = new PHPExcel();
		// Set properties
		$objPHPExcel->getProperties()->setCreator("Yoser Perez");
		$objPHPExcel->getProperties()->setLastModifiedBy("SPE");
		$objPHPExcel->getProperties()->setTitle("Listado de Partidas");
		$objPHPExcel->getProperties()->setSubject("Reporte");
		$objPHPExcel->getProperties()->setDescription("Reporte para documento de Office 2007 XLSX.");
		// Set the active Excel worksheet to sheet 0
		$objPHPExcel->setActiveSheetIndex(0);
		// Rename sheet
		$objPHPExcel->getActiveSheet()->getColumnDimension("D")->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension("H")->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension("J")->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setTitle('PROYECTO_PARTIDAS');
		// Initialise the Excel row number
		$rowCount = 2;
		// Iterate through each result from the SQL query in turn
		// We fetch each database result row into $row in turn

		$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue('A1', 'EJERCICIO FISCAL')
		->setCellValue('B1', 'CODIGO EJECUTOR')
		->setCellValue('C1', 'DESCRIPCION EJECUTOR')
		->setCellValue('D1', 'CODIGO PROYECTO')
		->setCellValue('E1', 'DESCRIPCION PROYECTO')
		->setCellValue('F1', 'CODIGO AE')
		->setCellValue('G1', 'DESCRIPCION AE')
		->setCellValue('H1', 'CODIGO PARTIDA')
		->setCellValue('I1', 'DESCRIPCION PARTIDA')
		->setCellValue('J1', 'MONTO PARTIDA');

		// Make bold cells
		$objPHPExcel->getActiveSheet()->getStyle('A1:J1')->getFont()->setBold(true);

		//Query
		$partida = tab_proyecto_aepartida::join('t39_proyecto_acc_espec as t39','t39.co_proyecto_acc_espec','=','t42_proyecto_acc_espec_partida.co_proyecto_acc_espec')
		->join('t26_proyectos as t26','t26.id_proyecto','=','t39.id_proyecto')
		->join('mantenimiento.tab_ejecutores as t01','t01.id','=','t39.co_ejecutores')
		->select( 't26.id_ejercicio', 't01.id_ejecutor', 'tx_ejecutor', 't26.id_proyecto', DB::raw('t26.nombre as nb_proyecto'), 'tx_codigo', DB::raw('t39.descripcion as nb_ae'), 'co_partida', 'tx_denominacion', 'nu_monto')
		->where('t42_proyecto_acc_espec_partida.edo_reg', '=', true)
		->where('t39.edo_reg', '=', true)
		->where('t26.edo_reg', '=', true)
		->where('t26.id_ejecutor', '=', $_GET['id_ejecutor'])
		->where('t26.id_ejercicio', '=', $_SESSION['ejercicio_fiscal'])
		->orderBy(DB::raw('id_ejecutor, id_proyecto, tx_codigo, co_partida'), 'ASC')
		->get();

		foreach ($partida as $key => $value) {
		    // Set cell An to the "name" column from the database (assuming you have a column called name)
		    //    where n is the Excel row number (ie cell A1 in the first row)
		    $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $value->id_ejercicio);
		    $objPHPExcel->getActiveSheet()->setCellValueExplicit('B'.$rowCount, $value->id_ejecutor, PHPExcel_Cell_DataType::TYPE_STRING);
		    $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $value->tx_ejecutor);
		    $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $value->id_proyecto);
		    $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $value->nb_proyecto);
		    $objPHPExcel->getActiveSheet()->setCellValueExplicit('F'.$rowCount, $value->tx_codigo, PHPExcel_Cell_DataType::TYPE_STRING);
		    $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $value->nb_ae);
		    $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, $value->co_partida);
		    $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, $value->tx_denominacion);
		    $objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, $value->nu_monto);
		    // Increment the Excel row counter
		    $rowCount++;
		}

		// Instantiate a Writer to create an OfficeOpenXML Excel .xlsx file
		$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
		// We'll be outputting an excel file
		header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		// It will be called file.xls
		header('Content-Disposition: attachment; filename="proyectos_partidas_'.$_SESSION['ejercicio_fiscal'].'_'.date("H:i:s").'.xlsx"');
		$objWriter->save('php://output');

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

function partida_proyecto_ae_todo() {

	DB::beginTransaction();

	/** Incluir la clase PHPExcel_IOFactory agregada en el directorio /lib/vendor/PHPExcel */
	include("../../plugins/reader/Classes/PHPExcel/IOFactory.php");

	try {

		// Instantiate a new PHPExcel object
		$objPHPExcel = new PHPExcel();
		// Set properties
		$objPHPExcel->getProperties()->setCreator("Yoser Perez");
		$objPHPExcel->getProperties()->setLastModifiedBy("SPE");
		$objPHPExcel->getProperties()->setTitle("Listado de Partidas");
		$objPHPExcel->getProperties()->setSubject("Reporte Partidas Proyecto");
		$objPHPExcel->getProperties()->setDescription("Reporte para documento de Office 2007 XLSX.");
		// Set the active Excel worksheet to sheet 0
		$objPHPExcel->setActiveSheetIndex(0);
		// Rename sheet
		$objPHPExcel->getActiveSheet()->getColumnDimension("D")->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension("H")->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension("J")->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setTitle('PROYECTO_PARTIDAS');
		// Initialise the Excel row number
		$rowCount = 2;
		// Iterate through each result from the SQL query in turn
		// We fetch each database result row into $row in turn

		$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue('A1', 'EJERCICIO FISCAL')
		->setCellValue('B1', 'CODIGO EJECUTOR')
		->setCellValue('C1', 'DESCRIPCION EJECUTOR')
		->setCellValue('D1', 'CODIGO PROYECTO')
		->setCellValue('E1', 'DESCRIPCION PROYECTO')
		->setCellValue('F1', 'CODIGO AE')
		->setCellValue('G1', 'DESCRIPCION AE')
		->setCellValue('H1', 'CODIGO PARTIDA')
		->setCellValue('I1', 'DESCRIPCION PARTIDA')
		->setCellValue('J1', 'MONTO PARTIDA');

		// Make bold cells
		$objPHPExcel->getActiveSheet()->getStyle('A1:J1')->getFont()->setBold(true);

		//Query
		$partida = tab_proyecto_aepartida::join('t39_proyecto_acc_espec as t39','t39.co_proyecto_acc_espec','=','t42_proyecto_acc_espec_partida.co_proyecto_acc_espec')
		->join('t26_proyectos as t26','t26.id_proyecto','=','t39.id_proyecto')
		->join('mantenimiento.tab_ejecutores as t01','t01.id','=','t39.co_ejecutores')
		->select( 't26.id_ejercicio', 't01.id_ejecutor', 'tx_ejecutor', 't26.id_proyecto', DB::raw('t26.nombre as nb_proyecto'), 'tx_codigo', DB::raw('t39.descripcion as nb_ae'), 'co_partida', 'tx_denominacion', 'nu_monto')
		->where('t42_proyecto_acc_espec_partida.edo_reg', '=', true)
		->where('t39.edo_reg', '=', true)
		->where('t26.edo_reg', '=', true)
		->where('t26.id_ejercicio', '=', $_SESSION['ejercicio_fiscal'])
		->orderBy(DB::raw('id_ejecutor, id_proyecto, tx_codigo, co_partida'), 'ASC')
		->get();

		foreach ($partida as $key => $value) {
		    // Set cell An to the "name" column from the database (assuming you have a column called name)
		    //    where n is the Excel row number (ie cell A1 in the first row)
		    $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $value->id_ejercicio);
		    $objPHPExcel->getActiveSheet()->setCellValueExplicit('B'.$rowCount, $value->id_ejecutor, PHPExcel_Cell_DataType::TYPE_STRING);
		    $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $value->tx_ejecutor);
		    $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $value->id_proyecto);
		    $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $value->nb_proyecto);
		    $objPHPExcel->getActiveSheet()->setCellValueExplicit('F'.$rowCount, $value->tx_codigo, PHPExcel_Cell_DataType::TYPE_STRING);
		    $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $value->nb_ae);
		    $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, $value->co_partida);
		    $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, $value->tx_denominacion);
		    $objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, $value->nu_monto);
		    // Increment the Excel row counter
		    $rowCount++;
		}

		// Instantiate a Writer to create an OfficeOpenXML Excel .xlsx file
		$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
		// We'll be outputting an excel file
		header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		// It will be called file.xls
		header('Content-Disposition: attachment; filename="proyectos_partidas_'.$_SESSION['ejercicio_fiscal'].'_'.date("H:i:s").'.xlsx"');
		$objWriter->save('php://output');

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

function partida_ac_ae() {

	DB::beginTransaction();

	/** Incluir la clase PHPExcel_IOFactory agregada en el directorio /lib/vendor/PHPExcel */
	include("../../plugins/reader/Classes/PHPExcel/IOFactory.php");

	try {

		// Instantiate a new PHPExcel object
		$objPHPExcel = new PHPExcel();
		// Set properties
		$objPHPExcel->getProperties()->setCreator("Yoser Perez");
		$objPHPExcel->getProperties()->setLastModifiedBy("SPE");
		$objPHPExcel->getProperties()->setTitle("Listado de Partidas");
		$objPHPExcel->getProperties()->setSubject("Reporte Partidas AC");
		$objPHPExcel->getProperties()->setDescription("Reporte para documento de Office 2007 XLSX.");
		// Set the active Excel worksheet to sheet 0
		$objPHPExcel->setActiveSheetIndex(0);
		// Rename sheet
		$objPHPExcel->getActiveSheet()->getColumnDimension("D")->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension("H")->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension("J")->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setTitle('AC_PARTIDAS');
		// Initialise the Excel row number
		$rowCount = 2;
		// Iterate through each result from the SQL query in turn
		// We fetch each database result row into $row in turn

		$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue('A1', 'EJERCICIO FISCAL')
		->setCellValue('B1', 'CODIGO EJECUTOR')
		->setCellValue('C1', 'DESCRIPCION EJECUTOR')
		->setCellValue('D1', 'CODIGO AC')
		->setCellValue('E1', 'DESCRIPCION AC')
		->setCellValue('F1', 'CODIGO AE')
		->setCellValue('G1', 'DESCRIPCION AE')
		->setCellValue('H1', 'CODIGO PARTIDA')
		->setCellValue('I1', 'DESCRIPCION PARTIDA')
		->setCellValue('J1', 'MONTO PARTIDA');

		// Make bold cells
		$objPHPExcel->getActiveSheet()->getStyle('A1:J1')->getFont()->setBold(true);

		//Query
		$partida = tab_ac_ae_partida::join('mantenimiento.tab_partidas as t01', function ($j) {
			$j->on('t01.co_partida','=','t54_ac_ae_partidas.co_partida')
				->on('t01.id_tab_ejercicio_fiscal','=','t54_ac_ae_partidas.id_tab_ejercicio_fiscal');
		})
		->join('t47_ac_accion_especifica as t47', function ($j) {
			$j->on('t47.id_accion_centralizada','=','t54_ac_ae_partidas.id_accion_centralizada')
				->on('t47.id_accion','=','t54_ac_ae_partidas.id_accion');
		})
		->join('t46_acciones_centralizadas as t46','t46.id','=','t47.id_accion_centralizada')
		->join('mantenimiento.tab_ac_ae_predefinida as t02','t02.id','=','t47.id_accion')
		->join('mantenimiento.tab_ac_predefinida as t03','t03.id','=','t46.id_accion')
		->join('mantenimiento.tab_ejecutores as t04','t04.id_ejecutor','=','t46.id_ejecutor')
		->select( 't46.id_ejercicio', 't46.id_ejecutor', 'tx_ejecutor', DB::raw("'AC' || t46.id_ejecutor || t46.id_ejercicio || lpad(t46.id_accion::text, 5, '0') as id_ac"), DB::raw('t03.de_nombre as nb_ac'), DB::raw('t02.nu_numero as nu_ae'), DB::raw('t02.de_nombre as nb_ae'), 't54_ac_ae_partidas.co_partida', DB::raw('t01.tx_nombre as tx_denominacion'), DB::raw('t54_ac_ae_partidas.monto as nu_monto'))
		->where('t54_ac_ae_partidas.edo_reg', '=', true)
		->where('t47.edo_reg', '=', true)
		->where('t46.edo_reg', '=', true)
		->where('t46.id_ejecutor', '=', $_GET['id_ejecutor'])
		->where('t46.id_ejercicio', '=', $_SESSION['ejercicio_fiscal'])
		//->where('t54_ac_ae_partidas.id_tab_ejercicio_fiscal', '=', $_SESSION['ejercicio_fiscal'])
		->orderBy(DB::raw(' t46.id_ejecutor, t46.id_accion, t02.nu_numero, co_partida'), 'ASC')
		->get();

		foreach ($partida as $key => $value) {
		    // Set cell An to the "name" column from the database (assuming you have a column called name)
		    //    where n is the Excel row number (ie cell A1 in the first row)
		    $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $value->id_ejercicio);
		    $objPHPExcel->getActiveSheet()->setCellValueExplicit('B'.$rowCount, $value->id_ejecutor, PHPExcel_Cell_DataType::TYPE_STRING);
		    $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $value->tx_ejecutor);
		    $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $value->id_ac);
		    $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $value->nb_ac);
		    $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $value->nu_ae);
		    $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $value->nb_ae);
		    $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, $value->co_partida);
		    $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, $value->tx_denominacion);
		    $objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, $value->nu_monto);
		    // Increment the Excel row counter
		    $rowCount++;
		}

		// Instantiate a Writer to create an OfficeOpenXML Excel .xlsx file
		$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
		// We'll be outputting an excel file
		header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		// It will be called file.xls
		header('Content-Disposition: attachment; filename="ac_partidas_'.$_SESSION['ejercicio_fiscal'].'_'.date("H:i:s").'.xlsx"');
		$objWriter->save('php://output');

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

function partida_ac_ae_todo() {

	DB::beginTransaction();

	/** Incluir la clase PHPExcel_IOFactory agregada en el directorio /lib/vendor/PHPExcel */
	include("../../plugins/reader/Classes/PHPExcel/IOFactory.php");

	try {

		// Instantiate a new PHPExcel object
		$objPHPExcel = new PHPExcel();
		// Set properties
		$objPHPExcel->getProperties()->setCreator("Yoser Perez");
		$objPHPExcel->getProperties()->setLastModifiedBy("SPE");
		$objPHPExcel->getProperties()->setTitle("Listado de Partidas");
		$objPHPExcel->getProperties()->setSubject("Reporte Partidas AC");
		$objPHPExcel->getProperties()->setDescription("Reporte para documento de Office 2007 XLSX.");
		// Set the active Excel worksheet to sheet 0
		$objPHPExcel->setActiveSheetIndex(0);
		// Rename sheet
		$objPHPExcel->getActiveSheet()->getColumnDimension("D")->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension("H")->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension("J")->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setTitle('AC_PARTIDAS');
		// Initialise the Excel row number
		$rowCount = 2;
		// Iterate through each result from the SQL query in turn
		// We fetch each database result row into $row in turn

		$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue('A1', 'EJERCICIO FISCAL')
		->setCellValue('B1', 'CODIGO EJECUTOR')
		->setCellValue('C1', 'DESCRIPCION EJECUTOR')
		->setCellValue('D1', 'CODIGO AC')
		->setCellValue('E1', 'DESCRIPCION AC')
		->setCellValue('F1', 'CODIGO AE')
		->setCellValue('G1', 'DESCRIPCION AE')
		->setCellValue('H1', 'CODIGO PARTIDA')
		->setCellValue('I1', 'DESCRIPCION PARTIDA')
		->setCellValue('J1', 'MONTO PARTIDA');

		// Make bold cells
		$objPHPExcel->getActiveSheet()->getStyle('A1:J1')->getFont()->setBold(true);

		//Query
		$partida = tab_ac_ae_partida::join('mantenimiento.tab_partidas as t01', function ($j) {
			$j->on('t01.co_partida','=','t54_ac_ae_partidas.co_partida')
				->on('t01.id_tab_ejercicio_fiscal','=','t54_ac_ae_partidas.id_tab_ejercicio_fiscal');
		})
		->join('t47_ac_accion_especifica as t47', function ($j) {
			$j->on('t47.id_accion_centralizada','=','t54_ac_ae_partidas.id_accion_centralizada')
				->on('t47.id_accion','=','t54_ac_ae_partidas.id_accion');
		})
		->join('t46_acciones_centralizadas as t46','t46.id','=','t47.id_accion_centralizada')
		->join('mantenimiento.tab_ac_ae_predefinida as t02','t02.id','=','t47.id_accion')
		->join('mantenimiento.tab_ac_predefinida as t03','t03.id','=','t46.id_accion')
		->join('mantenimiento.tab_ejecutores as t04','t04.id_ejecutor','=','t46.id_ejecutor')
		->select( 't46.id_ejercicio', 't46.id_ejecutor', 'tx_ejecutor', DB::raw("'AC' || t46.id_ejecutor || t46.id_ejercicio || lpad(t46.id_accion::text, 5, '0') as id_ac"), DB::raw('t03.de_nombre as nb_ac'), DB::raw('t02.nu_numero as nu_ae'), DB::raw('t02.de_nombre as nb_ae'), 't54_ac_ae_partidas.co_partida', DB::raw('t01.tx_nombre as tx_denominacion'), DB::raw('t54_ac_ae_partidas.monto as nu_monto'))
		->where('t54_ac_ae_partidas.edo_reg', '=', true)
		->where('t47.edo_reg', '=', true)
		->where('t46.edo_reg', '=', true)
		->where('t46.id_ejercicio', '=', $_SESSION['ejercicio_fiscal'])
		//->where('t54_ac_ae_partidas.id_tab_ejercicio_fiscal', '=', $_SESSION['ejercicio_fiscal'])
		->orderBy(DB::raw(' t46.id_ejecutor, t46.id_accion, t02.nu_numero, co_partida'), 'ASC')
		->get();

		foreach ($partida as $key => $value) {
		    // Set cell An to the "name" column from the database (assuming you have a column called name)
		    //    where n is the Excel row number (ie cell A1 in the first row)
		    $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $value->id_ejercicio);
		    $objPHPExcel->getActiveSheet()->setCellValueExplicit('B'.$rowCount, $value->id_ejecutor, PHPExcel_Cell_DataType::TYPE_STRING);
		    $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $value->tx_ejecutor);
		    $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $value->id_ac);
		    $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $value->nb_ac);
		    $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $value->nu_ae);
		    $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $value->nb_ae);
		    $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, $value->co_partida);
		    $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, $value->tx_denominacion);
		    $objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, $value->nu_monto);
		    // Increment the Excel row counter
		    $rowCount++;
		}

		// Instantiate a Writer to create an OfficeOpenXML Excel .xlsx file
		$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
		// We'll be outputting an excel file
		header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		// It will be called file.xls
		header('Content-Disposition: attachment; filename="ac_partidas_'.$_SESSION['ejercicio_fiscal'].'_'.date("H:i:s").'.xlsx"');
		$objWriter->save('php://output');

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

function municipio() {

	$response['success']  = 'true';
	$response['data']  = tab_municipio_detalle::select( 'id', 'de_municipio')
	//->where('in_activo', '=', true)
	->where('id_tab_estado', '=', 23)
	->orderby('id','ASC')->get()->toArray();

	header('Content-Type: application/json');
	echo json_encode($response);
	exit();
}

function parroquia() {

	$response['success']  = 'true';
	$response['data']  = tab_parroquia::select( 'id', 'de_parroquia')
	//->where('in_activo', '=', true)
	->where('id_tab_municipio', '=', $_POST['id_tab_municipio'])
	->orderby('de_parroquia','ASC')->get()->toArray();

	header('Content-Type: application/json');
	echo json_encode($response);
	exit();
}

function exportar_proyecto_ubicacion() {

	DB::beginTransaction();

	/** Incluir la clase PHPExcel_IOFactory agregada en el directorio /lib/vendor/PHPExcel */
	include("../../plugins/reader/Classes/PHPExcel/IOFactory.php");

	try {

		// Instantiate a new PHPExcel object
		$objPHPExcel = new PHPExcel();
		// Set properties
		$objPHPExcel->getProperties()->setCreator("Yoser Perez");
		$objPHPExcel->getProperties()->setLastModifiedBy("SPE");
		$objPHPExcel->getProperties()->setTitle("Listado de Proyectos");
		$objPHPExcel->getProperties()->setSubject("Reporte Proyectos Ubicacion");
		$objPHPExcel->getProperties()->setDescription("Reporte para documento de Office 2007 XLSX.");
		// Set the active Excel worksheet to sheet 0
		$objPHPExcel->setActiveSheetIndex(0);
		// Rename sheet
		$objPHPExcel->getActiveSheet()->getColumnDimension("A")->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension("F")->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension("G")->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension("H")->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setTitle('PROYECTOS');
		// Initialise the Excel row number
		$rowCount = 2;
		// Iterate through each result from the SQL query in turn
		// We fetch each database result row into $row in turn

		$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue('A1', 'PROYECTO')
		->setCellValue('B1', 'DESCRIPCION PROYECTO')
		->setCellValue('C1', 'ACCION ESPECIFICA')
		->setCellValue('D1', 'ENTE EJECUTOR RESPONSABLE')
		->setCellValue('E1', 'ACTIVIDAD')
		->setCellValue('F1', 'MUNICIPIO')
		->setCellValue('G1', 'MONTO')
		->setCellValue('H1', 'FUENTE FINANCIAMIENTO');

		// Make bold cells
		$objPHPExcel->getActiveSheet()->getStyle('A1:J1')->getFont()->setBold(true);

		//Query
		$partida = tab_meta_financiera::join('t67_metas as t67','t67.co_metas','=','t68_metas_detalle.co_metas')
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
		->where('t68_metas_detalle.co_municipio', '=', $_GET['id_tab_municipio'])
		->where('t26.id_ejercicio', '=', $_SESSION['ejercicio_fiscal'])
		->where('t26.edo_reg', '=', true)
		->where('t39.edo_reg', '=', true)
		->where('t67.edo_reg', '=', true)
		->where('t68_metas_detalle.edo_reg', '=', true)
		->orderBy(DB::raw('id_proyecto', 'tx_codigo', 't67.codigo'), 'ASC')
		->get();

		foreach ($partida as $key => $value) {
		    // Set cell An to the "name" column from the database (assuming you have a column called name)
		    //    where n is the Excel row number (ie cell A1 in the first row)
		    $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $value->id_proyecto);
		    $objPHPExcel->getActiveSheet()->setCellValueExplicit('B'.$rowCount, $value->de_proyecto, PHPExcel_Cell_DataType::TYPE_STRING);
		    $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $value->de_ae);
		    $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, trim($value->ejecutor));
		    $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $value->de_actividad);
		    $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $value->de_municipio);
		    $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $value->mo_presupuesto);
				$objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, $value->de_fuente_financiamiento);
		    // Increment the Excel row counter
		    $rowCount++;
		}

		// Instantiate a Writer to create an OfficeOpenXML Excel .xlsx file
		$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
		// We'll be outputting an excel file
		header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		// It will be called file.xls
		header('Content-Disposition: attachment; filename="ac_partidas_'.$_SESSION['ejercicio_fiscal'].'_'.date("H:i:s").'.xlsx"');
		$objWriter->save('php://output');

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

function exportar_proyecto_ubicacion_todo() {

	DB::beginTransaction();

	/** Incluir la clase PHPExcel_IOFactory agregada en el directorio /lib/vendor/PHPExcel */
	include("../../plugins/reader/Classes/PHPExcel/IOFactory.php");

	try {

		// Instantiate a new PHPExcel object
		$objPHPExcel = new PHPExcel();
		// Set properties
		$objPHPExcel->getProperties()->setCreator("Yoser Perez");
		$objPHPExcel->getProperties()->setLastModifiedBy("SPE");
		$objPHPExcel->getProperties()->setTitle("Listado de Proyectos");
		$objPHPExcel->getProperties()->setSubject("Reporte Proyectos Ubicacion");
		$objPHPExcel->getProperties()->setDescription("Reporte para documento de Office 2007 XLSX.");
		// Set the active Excel worksheet to sheet 0
		$objPHPExcel->setActiveSheetIndex(0);
		// Rename sheet
		$objPHPExcel->getActiveSheet()->getColumnDimension("A")->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension("F")->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension("G")->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension("H")->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setTitle('PROYECTOS');
		// Initialise the Excel row number
		$rowCount = 2;
		// Iterate through each result from the SQL query in turn
		// We fetch each database result row into $row in turn

		$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue('A1', 'PROYECTO')
		->setCellValue('B1', 'DESCRIPCION PROYECTO')
		->setCellValue('C1', 'ACCION ESPECIFICA')
		->setCellValue('D1', 'ENTE EJECUTOR RESPONSABLE')
		->setCellValue('E1', 'ACTIVIDAD')
		->setCellValue('F1', 'MUNICIPIO')
		->setCellValue('G1', 'MONTO')
		->setCellValue('H1', 'FUENTE FINANCIAMIENTO');

		// Make bold cells
		$objPHPExcel->getActiveSheet()->getStyle('A1:J1')->getFont()->setBold(true);

		//Query
		$partida = tab_meta_financiera::join('t67_metas as t67','t67.co_metas','=','t68_metas_detalle.co_metas')
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
		->orderBy(DB::raw('id_proyecto', 't39.tx_codigo', 't67.codigo'), 'ASC')
		->get();

		foreach ($partida as $key => $value) {
		    // Set cell An to the "name" column from the database (assuming you have a column called name)
		    //    where n is the Excel row number (ie cell A1 in the first row)
		    $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $value->id_proyecto);
		    $objPHPExcel->getActiveSheet()->setCellValueExplicit('B'.$rowCount, $value->de_proyecto, PHPExcel_Cell_DataType::TYPE_STRING);
		    $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $value->de_ae);
		    $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, trim($value->ejecutor));
		    $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $value->de_actividad);
		    $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $value->de_municipio);
		    $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $value->mo_presupuesto);
				$objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, $value->de_fuente_financiamiento);
		    // Increment the Excel row counter
		    $rowCount++;
		}

		// Instantiate a Writer to create an OfficeOpenXML Excel .xlsx file
		$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
		// We'll be outputting an excel file
		header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		// It will be called file.xls
		header('Content-Disposition: attachment; filename="ac_partidas_'.$_SESSION['ejercicio_fiscal'].'_'.date("H:i:s").'.xlsx"');
		$objWriter->save('php://output');

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
