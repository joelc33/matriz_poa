<?php
require_once("adodb5/adodb.inc.php");
require_once("adodb5/adodb-exceptions.inc.php");
require_once("config.php");

$paraTransaccion = NewADOConnection(GESTOR_DATABASE);
/*$paraTransaccion ->Connect(SERVIDOR,USUARIO,CLAVE,BASEDEDATOS);*/
$paraTransaccion ->PConnect('host='.SERVIDOR.' port='.PUERTO.' user='.USUARIO.' password='.CLAVE.' dbname='.BASEDEDATOS.'');

class ConexionComun{
    protected $rCampos = "";
    protected $db;
    protected $rs;
    protected $instruccion;
    public    $errorTransaccion =1;

    function __construct(){
        $this->db = NewADOConnection(GESTOR_DATABASE);
        /*$this->db->Connect(SERVIDOR,USUARIO,CLAVE,BASEDEDATOS);*/
        $this->db->PConnect('host='.SERVIDOR.' port='.PUERTO.' user='.USUARIO.' password='.CLAVE.' dbname='.BASEDEDATOS.'');
        $this->db->SetFetchMode(ADODB_FETCH_ASSOC);
        $this->db->debug = false;
    }

     /**
     * Retorna la cantidad de filas que genero la consulta sql.
     * @return int
     */
    function getFilas($sql){
        $this->instruccion = $sql;
        $this->rs = $this->db->Execute($this->instruccion);
    	if(!$this->rs){
            return 0;
        }else{
            return $this->rs->RecordCount();
        }
    }

    /**
     * Función para realizar consultas sql a la base de datos.
     * @param string $sql Consulta SQL que se desea veriticar.
     * @return string
     */
    function ObtenerFilasBySqlSelect($sql, $params = null){
        $this->instruccion = $sql;
		if (empty($params)) {
			$this->rs = $this->db->Execute($this->instruccion);
		} else {
			$this->rs = $this->db->Execute($this->instruccion, $params);
		}
        if(!$this->rs){
            //return echo "Error: ".$this->db->ErrorMsg();
            //$this->rCampos = -1;
            return "";
        }else{
            $this->rCampos = $this->rs->GetRows();
        }
        return $this->rCampos;
    }

	function EjecutarQuery($sql, $params = null) {
		if ( $params ) {
			return $this->db->Execute( $sql, $params );
		} else {
			$this->instruccion = $sql;
			$this->rs = $this->db->Execute($this->instruccion);
			return "1";
		}
	}

    function InsertUpdate($tabla,$variable,$tquery,$id=null){
	if($tquery=="INSERT"){
		$this->rs = $this->db->AutoExecute($tabla, $variable, "INSERT");
	}elseif($tquery=="UPDATE"){
		$this->rs = $this->db->AutoExecute($tabla, $variable, "UPDATE", $id);
	}
        if(!$this->rs){
            //return echo "Error: ".$this->db->ErrorMsg();
            return "";
        }else{
            return "Ok";
        }
    }

    function InsertConID($tabla,$variable,$primaryKey){
	$insertSQL = $this->db->GetInsertSQL($tabla, $variable);
	$this->rs = $this->db->Execute($insertSQL);
        if(!$this->rs){
            return "";
        }else{
            $this->rCampos = $this->db->Insert_ID($tabla,$primaryKey);
        }
	return $this->rCampos;
    }

    }

function tx_codigo_padre($cadena) //dividir cadena por .
{
	$array_palabras = explode(".", $cadena);
	$palabras_cadena = count($array_palabras);
	$cadenaf="";
	foreach($array_palabras as $key => $campo){
		if($key == $palabras_cadena-1){
		}else{
		$cadenaf=$cadenaf.$campo.".";
		}
	}
	return $cadenaf;
}

function tx_codigo_hijo($cadena) //dividir cadena por .
{
	$array_palabras = explode(".", $cadena);
	$palabras_cadena = count($array_palabras);
	$cadenaf="";
	foreach($array_palabras as $key => $campo){
		if($key == $palabras_cadena-1){
		$cadenaf=$cadenaf.$campo;
		}else{
		}
	}
	return $cadenaf;
}

function decode($string) //quitar caracteres especiales para evitar sqlinjection
{
	$nopermitidos = array("'",'\\','<','>',"\"","-","%");
	$string = str_replace($nopermitidos, "", $string);
	return $string;
}

function sinnull($string) //quitar caracteres especiales para evitar sqlinjection
{
	$nopermitidos = array("null");
	$string = str_replace($nopermitidos, "", $string);
	return $string;
}

function mes($nu_mes){

	$mes['01']='Enero';
	$mes['02']='Febrero';
	$mes['03']='Marzo';
	$mes['04']='Abril';
	$mes['05']='Mayo';
	$mes['06']='Junio';
	$mes['07']='Julio';
	$mes['08']='Agosto';
	$mes['09']='Septiembre';
	$mes['10']='Octubre';
	$mes['11']='Noviembre';
	$mes['12']='Diciembre';

	return $mes[$nu_mes];
}

function genera_clave(){
	$cadena = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";
	$longitudCadena=strlen($cadena);
	$pass = "";
	$longitudPass=8;
	for($i=1 ; $i<=$longitudPass ; $i++){
		$pos=rand(0,$longitudCadena-1);
		$pass .= substr($cadena,$pos,1);
	}
	return $pass;
}

function enviar_correo($correo,$nombre,$motivo,$cuerpo){
	require '../../plugins/PHPMailer/PHPMailerAutoload.php';
	$mail = new PHPMailer;
	$mail->isSMTP();
	$mail->Host = 'mail.yoser.dafesoluciones.net;mail.yoser.dafesoluciones.net';
	$mail->SMTPAuth = true;
	$mail->Username = 'spe@yoser.dafesoluciones.net';
	$mail->Password = 'spe';
	$mail->SMTPSecure = 'tls';
	$mail->Port = 25;
//*********************************CONTENIDO************************************************//
	$mail->From = 'sedatez@yoser.dafesoluciones.net';
	$mail->FromName = utf8_encode('Sistema Automatizado - SPE');
	$mail->addAddress($correo, $nombre);
	$mail->addReplyTo('sedatez@yoser.dafesoluciones.net', 'Informacion');

	$mail->WordWrap = 50;
	//$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
	//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
	$mail->isHTML(true);

	$mail->Subject = $motivo;
	$mail->Body    = $cuerpo;
	$mail->AltBody = '';

	if(!$mail->send()) {
	    $respuesta = 'Error: ' . $mail->ErrorInfo;
	    //$respuesta = '';
	} else {
	    $respuesta = 'Ok';
	}
	return $respuesta;
}

function encabezado($pdf,$h,$tipo){
	if($h=='v'){
    if($_SESSION['ejercicio_fiscal'] == 2015){
      //$pdf->Image('../../images/cintillo_2015.png', 15, 3, 195, 20, 'PNG', '', '', true, 150, '', false, false, 0, false, false, false);
	  $pdf->Image('../../images/zulia_escudo_negro.png', 15, 3, 20, 16, 'PNG', '', '', true, 150, '', false, false, 0, false, false, false);
    }elseif($_SESSION['ejercicio_fiscal'] == 2016){
      //$pdf->Image('../../images/cintillo_2016.png', 15, 3, 195, 20, 'PNG', '', '', true, 150, '', false, false, 0, false, false, false);
	  $pdf->Image('../../images/zulia_escudo_negro.png', 15, 3, 20, 16, 'PNG', '', '', true, 150, '', false, false, 0, false, false, false);
    }elseif($_SESSION['ejercicio_fiscal'] == 2017){
      //$pdf->Image('../../images/cintillo_2017.jpg', 15, 3, 195, 16, 'JPG', '', '', true, 150, '', false, false, 0, false, false, false);
	  $pdf->Image('../../images/zulia_escudo_negro.png', 15, 3, 20, 16, 'PNG', '', '', true, 150, '', false, false, 0, false, false, false);
    }elseif($_SESSION['ejercicio_fiscal'] > 2017){
      $pdf->Image('../../images/zulia_escudo_negro.png', 15, 3, 20, 16, 'PNG', '', '', true, 150, '', false, false, 0, false, false, false);
      $pdf->setXY(35,7);
      $pdf->SetFont('','B',11);
      $pdf->MultiCell(190, 5, 'GOBERNACIÓN DEL ESTADO ZULIA', 0, 'L', 0, 0, '', '', true);
      $pdf->setXY(35,14);
      $pdf->MultiCell(190, 5, 'PLAN OPERATIVO ANUAL '.$_SESSION['ejercicio_fiscal'], 0, 'L', 0, 0, '', '', true);
      $pdf->setY(23);
    }
		//$pdf->Image('../../images/cintillo_2017.jpg', 10, 3, 195, 16, 'JPG', '', '', true, 150, '', false, false, 0, false, false, false);
		//$pdf->Image('../../images/izquierda.png', 10, 3, 120, 16, 'PNG', '', '', true, 150, '', false, false, 0, false, false, false);
		//$pdf->Image('../../images/derecha.png', 170, 3, 35, 16, 'PNG', '', '', true, 150, '', false, false, 0, false, false, false);
	}
	if($h=='h'){
    if($_SESSION['ejercicio_fiscal'] == 2015){
      $pdf->Image('../../images/cintillo_2015.png', 15, 3, 259, 20, 'PNG', '', '', true, 150, '', false, false, 0, false, false, false);
    }elseif($_SESSION['ejercicio_fiscal'] == 2016){
      $pdf->Image('../../images/cintillo_2016.png', 15, 3, 259, 20, 'PNG', '', '', true, 150, '', false, false, 0, false, false, false);
    }elseif($_SESSION['ejercicio_fiscal'] == 2017){
      $pdf->Image('../../images/cintillo_2017.jpg', 15, 3, 259, 16, 'JPG', '', '', true, 150, '', false, false, 0, false, false, false);
    }elseif($_SESSION['ejercicio_fiscal'] > 2017){
      $pdf->Image('../../images/zulia_escudo_negro.png', 15, 3, 20, 16, 'PNG', '', '', true, 150, '', false, false, 0, false, false, false);
      $pdf->setXY(35,7);
      $pdf->SetFont('','B',11);
      $pdf->MultiCell(190, 5, 'GOBERNACIÓN DEL ESTADO ZULIA', 0, 'L', 0, 0, '', '', true);
      $pdf->setXY(35,14);
      $pdf->MultiCell(190, 5, 'PLAN OPERATIVO ANUAL '.$_SESSION['ejercicio_fiscal'], 0, 'L', 0, 0, '', '', true);
      $pdf->setY(23);
    }
		//$pdf->Image('../../images/cintillo_2018.jpg', 10, 3, 259, 16, 'JPG', '', '', true, 150, '', false, false, 0, false, false, false);
    //$pdf->Image('../../images/cintillo_2017.jpg', 10, 3, 259, 16, 'JPG', '', '', true, 150, '', false, false, 0, false, false, false);
    //$pdf->Image('../../images/cintillo_2016.png', 10, 3, 259, 20, 'PNG', '', '', true, 150, '', false, false, 0, false, false, false);
    //$pdf->Image('../../images/cintillo_2015.png', 10, 3, 259, 20, 'PNG', '', '', true, 150, '', false, false, 0, false, false, false);
		//$pdf->Image('../../images/izquierda.png', 10, 3, 120, 16, 'PNG', '', '', true, 150, '', false, false, 0, false, false, false);
		//$pdf->Image('../../images/derecha.png', 250, 3, 20, 16, 'PNG', '', '', true, 150, '', false, false, 0, false, false, false);
		//$pdf->Image('../../images/derecha.png', 235, 3, 35, 16, 'PNG', '', '', true, 150, '', false, false, 0, false, false, false);
	}
		//$pdf->Ln(20);
	return $pdf;
}

function pie( $pdf, $h, $tipo){
	$pdf->setXY(10,-15);
	$pdf->SetFont('','i',8);
	$pdf->SetTextColor(0,0,0);
		if($tipo==1){
			$termino='PR';
		}else{
			$termino='AC';
		}
	if($h=='v'){
		//$pdf->Image('../../images/logo.png', 10, 260, 25, 12, 'PNG', '', '', true, 150, '', false, false, 0, false, false, false);
		$pdf->ln(0);
		$pdf->writeHTMLCell(205,0, '', '', $termino.'-'.$pdf->getAliasNumPage().'/'.$pdf->getAliasNbPages(), 0, 0, 0, true, 'R', true);
		$pdf->ln(0);
		$pdf->writeHTMLCell(205,0, '', '', 'Palacio de los Cóndores, Plaza Bolívar, Maracaibo, Estado Zulia, Venezuela', 0, 0, 0, true, 'C', true);
	}
	if($h=='h'){
		//$pdf->Image('../../images/logo.png', 10, 198, 25, 12, 'PNG', '', '', true, 150, '', false, false, 0, false, false, false);
		$pdf->ln(0);
		$pdf->writeHTMLCell(260,0, '', '', $termino/*.'-'.$pdf->getAliasNumPage().'/'.$pdf->getAliasNbPages()*/, 0, 0, 0, true, 'R', true);
		$pdf->ln(0);
		$pdf->writeHTMLCell(260,0, '', '', 'Palacio de los Cóndores, Plaza Bolívar, Maracaibo, Estado Zulia, Venezuela', 0, 0, 0, true, 'C', true);
	}
	$pdf->ln(6);
	return $pdf;
}
?>
