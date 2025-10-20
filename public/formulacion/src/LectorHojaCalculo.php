<?php namespace Reingsys;

class LectorHojaCalculo {
	static $extensiones = array(
		'application/vnd.ms-excel' => 'Excel5',
		'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'Excel2007',
		'application/vnd.oasis.opendocument.spreadsheet' => 'OOCalc'
	);

	private $archivo;
	private $tipo;

	public function __construct( $archivo, $mime = null ) {
		$tipo = null;
		if ( array_key_exists( $mime, self::$extensiones) ) {
			$tipo = self::$extensiones[$mime];
		} else {
			$tipo = \PHPExcel_IOFactory::identify( $archivo );
		}
		$this->lector = \PHPExcel_IOFactory::createReader( $tipo );

		$this->archivo = $archivo;
	}
	public function leer( $inicio, $fin, array $cols = array(1) ) {
		$filtro = new FiltroLectura( intval( $inicio ), intval( $fin ), $cols );
		$this->lector->setReadFilter( $filtro );
		$this->lector->setReadDataOnly( true );
		$doc = $this->lector->load( $this->archivo );
		$datos = $doc->getActiveSheet()->toArray( null, true, true, true );
		$fin = count( $datos );

		$mapa = array_map ( function( $c ) {
			return array( $c, \PHPExcel_Cell::stringFromColumnIndex( $c ) );
		}, $cols );

		$salida = array();
		for ( $i = $inicio; $i < $fin; $i++ ) {
			$row = $datos[$i];
			$entrada = array();
			foreach( $mapa as $m ) {
				list($n, $l) = $m;
				$entrada[$n] = $row[$l];
			}
			$salida[] = $entrada;
		}
		return $salida;
	}
}

