<?php namespace Reingsys;

abstract class Helpers {

	/*
	 * Evalua la respuesta de una verificación / procedimiento de BD
	 * @return array(boolean, string)
	 */
	static function evaluar( $res ) {
		if ( $res ) {
			return array( $res[0]['resultado']  === 't', $res[0]['mensaje'] );
		} else {
			return array( false, null );
		}
	}

	/*
	 * Respuestas json siguiendo el formato por defecto de formularios en ExtJS
	 * @return string
	 */
	static function responder( $resultado = true, $mensaje = null, array $extra = array()) {
		$resultado = !!$resultado;
		if ( empty( $mensaje ) ) {
			$mensaje = $resultado ?
				'Operación exitosa' : 'Error ejecutando la operación';
		}
		return json_encode(
			array_merge( array(
				'success' => $resultado,
				'msg' => $mensaje
			), $extra )
		);
	}

	static function obtener( array $entrada, $llave, $default = null ) {
		if( isset( $entrada[$llave] ) ) {
			return $entrada[$llave];
		}
		return $default;
	}

	/**
	 * Devuelve un arreglo con los valores de las (opcionalmente traducidas)
	 * entradas del segundo arreglo, y los valores del primero. En caso de no
	 * existir se introduce como nula.
	 *
	 * @return array
	 */
	static function obtener_pertinentes( array $entrada, array $lista, $estricto = false ) {
		$salida = array();
		foreach ( $lista as $k => $v ) {
			if ( is_int( $k ) ) {
				$k = $v;
			}
			if ( array_key_exists ( $k, $entrada ) !== FALSE
				and $entrada[$k] !== ''
			) {
				$salida[$v] = $entrada[$k];
			} else {
				if( !$estricto ) {
					$salida[$v] = null;
				}
			}
		}
		return $salida;
	}

	/*
	* Envía el archivo (como adjunto) mediante http
	* @param filename nombre (completo) del archivo
	*/
	static function send_file_http( $filename, $name=null, $mime=null ) {
		if ( !is_file( $filename ) ) {
			return false;
		}
		$filepath = str_replace( '\\', '/', realpath( $filename ) );
		$filesize = filesize($filepath);
		$filename = substr(strrchr('/'.$filepath, '/'), 1);
		$extension = strtolower(substr(strrchr($filepath, '.'), 1));

		if (empty($mime)) {
			$mime = 'application/x-download';
		}

		if (empty($name)) {
			$name = $filename;
		}

		header('Content-Type: '.$mime);
		// header('Content-Disposition: inline; filename="'.$name.'"');
		header('Content-Disposition: attachment; filename="'.$name.'"');

		header('Cache-Control: private, max-age=0, must-revalidate');
		// header('Content-Transfer-Encoding: binary');
		header('Content-Length: '.sprintf('%d', $filesize));
		header('Pragma: public');

		// check for IE only headers
		if (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)) {
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
		} else {
			header('Pragma: no-cache');
		}

		$handle = fopen($filepath, 'rb');

		if (fpassthru($handle) === FALSE) {
			return false;
		}

		fclose($handle);

		return true;
	}
	/**
	 * jTraceEx() - provide a Java style exception trace
	 * @param $exception
	 * @param $seen      - array passed to recursive calls to accumulate trace lines already seen
	 *                     leave as NULL when calling this function
	 * @return array of strings, one entry per trace line
	 */
	static function jTraceEx( $e, $seen=null ) {
		$starter = $seen ? 'Caused by: ' : '';
		$result = array();
		if (!$seen) $seen = array();
		$trace  = $e->getTrace();
		$prev   = $e->getPrevious();
		$result[] = sprintf('%s%s: %s', $starter, get_class($e), $e->getMessage());
		$file = $e->getFile();
		$line = $e->getLine();
		while (true) {
			$current = "$file:$line";
			if (is_array($seen) && in_array($current, $seen)) {
				$result[] = sprintf(' ... %d more', count($trace)+1);
				break;
			}
			$result[] = sprintf(' at %s%s%s(%s%s%s)',
				count($trace) && array_key_exists('class', $trace[0]) ? str_replace('\\', '.', $trace[0]['class']) : '',
				count($trace) && array_key_exists('class', $trace[0]) && array_key_exists('function', $trace[0]) ? '.' : '',
				count($trace) && array_key_exists('function', $trace[0]) ? str_replace('\\', '.', $trace[0]['function']) : '(main)',
				$line === null ? $file : basename($file),
				$line === null ? '' : ':',
				$line === null ? '' : $line);
			if (is_array($seen))
				$seen[] = "$file:$line";
			if (!count($trace))
				break;
			$file = array_key_exists('file', $trace[0]) ? $trace[0]['file'] : 'Unknown Source';
			$line = array_key_exists('file', $trace[0]) && array_key_exists('line', $trace[0]) && $trace[0]['line'] ? $trace[0]['line'] : null;
			array_shift($trace);
		}
		$result = join("\n", $result);
		if ($prev)
			$result  .= "\n" . jTraceEx($prev, $seen);

		return $result;
	}
}

