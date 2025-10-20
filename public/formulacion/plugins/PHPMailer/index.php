<?php
require 'PHPMailerAutoload.php';

$mail = new PHPMailer;

//$mail->SMTPDebug = 3;                               // Enable verbose debug output

$mail->isSMTP();                                      // Set mailer to use SMTP
$mail->Host = 'mail.yoser.dafesoluciones.net;mail.yoser.dafesoluciones.net';  // Specify main and backup SMTP servers
$mail->SMTPAuth = true;                               // Enable SMTP authentication
$mail->Username = 'sedatez@yoser.dafesoluciones.net';                 // SMTP username
$mail->Password = 'sedatez';                           // SMTP password
$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
$mail->Port = 25;                                    // TCP port to connect to

$mail->From = 'sedatez@yoser.dafesoluciones.net';
$mail->FromName = utf8_encode('Sistema Automatizado - SEDATEZ');
$mail->addAddress('yoserp1@gmail.com', 'Yoser Perez');     // Add a recipient
//$mail->addAddress('ellen@example.com');               // Name is optional
$mail->addReplyTo('sedatez@yoser.dafesoluciones.net', 'Informacion');
//$mail->addCC('cc@example.com');
//$mail->addBCC('bcc@example.com');

$mail->WordWrap = 50;                                 // Set word wrap to 50 characters
//$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
$mail->isHTML(true);                                  // Set email format to HTML

$mail->Subject = 'REGISTRO DE EXPENDEDORES DE ESPECIES FISCALES DEL ESTADO ZULIA';
$mail->Body    = 'Estimado(a) ciudadano(a), usted acaba de registrarse en el Sistema del Servicio Desconcentrado De Administración Tributaria Del Estado Zulia. Sus datos de registro son:
<ul><li><strong>Usuario:</strong> V-18821386</li>
<li><strong>Clave:</strong> 223366</li></ul>
A partir de este momento, con la utilización de su usuario y contraseña usted tiene la posibilidad de acceder a nuestros Servicios en Línea.<br><br>Usted se registró en el sitio con los siguientes datos personales. Compruebe que estos sean correctos:
<ul><li>R.I.F: V-18821386</li>
<li>Nombre / Razon Social: Yoser Gregori Perez Gascon</li>
<li>Dirección Fiscal: LOMITAS DEL ZULIA, CALLE 95, CASA 63A-105 SECTOR CUATRICENTENARIO, MARACAIBO-ZULIA</li>
<li>Teléfono: <a href="tel:0424-6292831" value="+584246292831" target="_blank">0424-6292831</a></li></ul>
<br><br>Para activar su cuenta por favor haga clic en el siguiente enlace: <a href="http://activar.sedatez.zulia.gob.ve/" target="_blank">http://activar.sedatez.zulia.gob.ve/<wbr>123</a>

<br><br>La ruta de acceso al sistema es <a href="http://etrib.sedatez.zulia.gob.ve" target="_blank">http://etrib.sedatez.zulia.gob.ve</a>

<br><br>Si sus datos no son correctos, puede actualizarlos una vez que ingrese con su usuario y clave en la opción "Perfil" del menu<br><br>Gracias por utilizar nuestros servicios.

<br><br><i><font color="#9C9C9C">Esta es una cuenta de correo no monitoreada. Por favor, no responda ni reenvíe mensajes a esta cuenta.</font></i>';
$mail->AltBody = '';

if(!$mail->send()) {
    echo 'El correo electronico no pudo ser enviado.';
    echo 'Error: ' . $mail->ErrorInfo;
} else {
    echo 'El mensaje ha sido Enviado...';
}
?>
