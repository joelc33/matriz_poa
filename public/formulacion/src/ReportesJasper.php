<?php namespace Reingsys;
require_once JAVA_BRIDGE . '/java/Java.inc';

class ReportesJasper {
	private $_conn;
	private $_reportes;
	private $_salida;

	private static function cadenaConexion( $gestor, $host, $port, $db ) {
		return "jdbc:{$gestor}://{$host}:{$port}/{$db}";
	}

	function __construct( $reportes, $salida ) {
		try {
			$cadena = self::cadenaConexion(
				JDBC_TYPE, SERVIDOR, PUERTO, BASEDEDATOS );
			$this->_conn = new JdbcConnection(
				DRIVER, $cadena, USUARIO, CLAVE );
			$this->_reportes = $reportes;
			$this->_salida = $salida;
		} catch ( \JavaException $ex ) {
			$trace = new \Java( 'java.io.ByteArrayOutputStream' );
			$ex->printStackTrace(
				new \Java( 'java.io.PrintStream', $trace )
			);
			throw new \Exception( $trace->toString() );
		}
	}
	/**
	 * @desc genera el reporte señalado
	 * @param reporte nombre del reporte
	 * @param salida nombre del archivo de salida
	 * @param params mapa de parámetros del reporte
	 * @return string ruta al archivo pdf generado
	 * @throws Exception
	 */
	function generar ( $reporte, $pdf = null, array $params = array() ) {
		if( empty( $pdf ) ) {
			$pdf = $reporte;
		}
		$params = array_merge( array(
			'REPORT_DIR' => $this->_reportes,
			'SUBREPORT_DIR' => $this->_reportes
		), $params );

		try {
			$connection = $this->_conn->getConnection();
			$parameters = new \Java( 'java.util.HashMap' );

			foreach ( $params as $k => $v ) {
				$parameters->put( $k, $v );
			}

			$ruta = "{$this->_salida}/{$pdf}.pdf";

			$jrm =  \java( 'net.sf.jasperreports.engine.JasperRunManager' );
			$jrm->runReportToPdfFile(
				"{$this->_reportes}/{$reporte}.jasper",
				$ruta, $parameters, $connection
			);
			return $ruta;
		} catch ( \JavaException $ex ) {
			$trace = new \Java( 'java.io.ByteArrayOutputStream' );
			$ex->printStackTrace( new \Java( 'java.io.PrintStream', $trace ) );
			throw new \Exception( $trace->toString() );
		}
	}

	function generarXLS ( $reporte, $archivo = null, array $params = array() ) {
		if ( empty( $archivo ) ) {
			$archivo = $reporte;
		}
		$params = array_merge( array(
			'REPORT_DIR' => $this->_reportes,
			'SUBREPORT_DIR' => $this->_reportes
		), $params );

		try {
			$connection = $this->_conn->getConnection();
			$parameters = new \Java( 'java.util.HashMap' );

			foreach ( $params as $k => $v ) {
				$parameters->put( $k, $v );
			}

			$ruta = "{$this->_salida}/{$archivo}.xls";

			$jfm =  \java( 'net.sf.jasperreports.engine.JasperFillManager' );
			$jp = $jfm->fillReport(
				new \Java( 'java.lang.String', ("{$this->_reportes}/{$reporte}.jasper")),
				$parameters, $connection
			);
			$jxe = new \Java( 'net.sf.jasperreports.engine.export.JRXlsExporter' );
			$jep = java( 'net.sf.jasperreports.engine.JRExporterParameter' );
			$jxe->setParameter( $jep->JASPER_PRINT, $jp );
			$jxe->setParameter( $jep->OUTPUT_FILE_NAME, $ruta );
			$jxe->exportReport();
			return $ruta;
		} catch ( \JavaException $ex ) {
			$trace = new \Java( 'java.io.ByteArrayOutputStream' );
			$ex->printStackTrace( new \Java( 'java.io.PrintStream', $trace ) );
			throw new \Exception( $trace->toString() );
		}
	}
}

