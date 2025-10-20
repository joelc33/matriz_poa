<?php
session_start();
if($_SESSION['estatus']!='OK'){
	http_response_code(403);
	die();
}

require_once (__DIR__.'/../../plugins/eloquent/app.config.php');
require_once (__DIR__.'/../../model/proyecto/tmp_proyecto_aepartida.php');

$router->post('/partida/proyecto/ae', function($id){
	$codigo = $_POST['id_proyecto'];
	$codigo_ae = $_POST['co_proyecto_acc_espec'];
	$tx_codigo_ae = $_POST['tx_codigo'];
	partida_proyecto_ae( $codigo, $codigo_ae, $tx_codigo_ae);
});

require_once (__DIR__.'/../../model/route.php');

function partida_proyecto_ae( $codigo, $codigo_ae, $tx_codigo_ae) {

	DB::beginTransaction();

	try {

		$array = explode('.', $_FILES['archivo']['name']);
		$extension = end($array);

		$datos = array(
			'proyecto' => $_POST['id_proyecto'],
			'archivo' => $_FILES['archivo'],
			'extension' => $extension
		);

		$validador = Validator::make($datos, tmp_proyecto_aepartida::$importar_archivo_foraneo);


		if ($validador->fails()) {
			header('Content-Type: text/html');
			echo json_encode(array(
				'success' => false,
				'msg' => $validador->getMessageBag()->toArray()
			)); 
			exit();
		}

		/** Incluir la clase PHPExcel_IOFactory agregada en el directorio /lib/vendor/PHPExcel */
		include("../../plugins/reader/Classes/PHPExcel/IOFactory.php");

		//Funciones extras
	
		function get_cell($cell, $objPHPExcel){
			//seleccionar una celda
			$objCell = ($objPHPExcel->getActiveSheet()->getCell($cell));
			//tomar valor de la celda
			return $objCell->getvalue();
		}
	
		function pp(&$var){
			$var = chr(ord($var)+1);
			return true;
		}

		$name	  = $_FILES['archivo']['name'];
		$tname 	  = $_FILES['archivo']['tmp_name'];
		$type 	  = $_FILES['archivo']['type'];
		
		if($type == 'application/vnd.ms-excel')
		{
			// Extension excel 97
			$ext = 'xls';
		}
		else if($type == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
		{
			// Extension excel 2007 y 2010
			$ext = 'xlsx';
		}else{
			// Extension no valida
			echo -1;
			exit();
		}

		$xlsx = 'Excel2007';
		$xls  = 'Excel5';

		//creando el lector
		$objReader = PHPExcel_IOFactory::createReader($$ext);
	
		//cargamos el archivo
		$objPHPExcel = $objReader->load($tname);

		$dim = $objPHPExcel->getActiveSheet()->calculateWorksheetDimension();

		// list coloca en array $start y $end
		list($start, $end) = explode(':', $dim);
		
		if(!preg_match('#([A-Z]+)([0-9]+)#', $start, $rslt)){
			return false;
		}
		list($start, $start_h, $start_v) = $rslt;
		if(!preg_match('#([A-Z]+)([0-9]+)#', $end, $rslt)){
			return false;
		}
		list($end, $end_h, $end_v) = $rslt;

		$delete_ae_partida_tmp = DB::select( DB::raw("DELETE FROM t43_acc_espec_partida_tmp WHERE id_proyecto = :proyecto;"), array( 'proyecto' => $codigo));

		$update_ae_partida = DB::select( DB::raw("UPDATE t42_proyecto_acc_espec_partida SET edo_reg=false 
				FROM t39_proyecto_acc_espec
				WHERE t42_proyecto_acc_espec_partida.co_proyecto_acc_espec = t39_proyecto_acc_espec.co_proyecto_acc_espec
				AND id_proyecto = :proyecto and t42_proyecto_acc_espec_partida.co_proyecto_acc_espec = :ae;"), array( 'proyecto' => $codigo, 'ae' => $codigo_ae));

		$contenido = get_cell('F9', $objPHPExcel);
			if($contenido!=''||$contenido!=null){
				$tx_codigo = $tx_codigo_ae;

			//empieza  lectura vertical
			$start_v=10;
			$end_v=1923;

			for($v=$start_v; $v<=$end_v; $v++){
				//empieza lectura horizontal
				for($h=$start_h; ord($h)<=ord($end_h); pp($h)){
					$cellValue1 = get_cell("A".$v, $objPHPExcel);
					$cellValue2 = get_cell("B".$v, $objPHPExcel);
					$cellValue3 = get_cell("C".$v, $objPHPExcel);
					$cellValue4 = get_cell("D".$v, $objPHPExcel);
					$cellValue5 = get_cell("E".$v, $objPHPExcel);
					$cellValue6 = get_cell("F".$v, $objPHPExcel);
				}

				$mensajes = array(
					'monto.regex'=>'En la celda: F'.$v.' el monto no debe poseer decimales.'
				);

				$datos = array(
					'monto' => floatval($cellValue6)
				);

				$validador = Validator::make($datos, tmp_proyecto_aepartida::$validar_campo, $mensajes);

				if ($validador->fails()) {
					header('Content-Type: text/html');
					echo json_encode(array(
						'success' => false,
						'msg' => $validador->getMessageBag()->toArray()
					)); 
					exit();
				}


				$partida = new tmp_proyecto_aepartida;
				$partida->id_proyecto = $codigo;
				$partida->id_tab_ejercicio_fiscal = $_SESSION['ejercicio_fiscal'];
				$partida->tx_codigo = $tx_codigo;
				$partida->tx_pa = $cellValue1;
				$partida->tx_pa = $cellValue1;
				$partida->tx_ge = $cellValue2;
				$partida->tx_es = $cellValue3;
				$partida->tx_se = $cellValue4;
				$partida->tx_denominacion = $cellValue5;
				$partida->nu_monto = floatval($cellValue6);
				$partida->edo_reg = TRUE;
				$partida->save();

			}
		}

		DB::commit();
		header('Content-Type: text/html');
		echo json_encode(array(
			'success' => true,
			'msg' => 'Archivo procesado Exitosamente<br>Se leyeron '.$end_v.' Filas.'
		)); 

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
