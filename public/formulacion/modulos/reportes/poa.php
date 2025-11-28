<?php

session_start();

if ($_SESSION['estatus'] !== 'OK') {
    http_response_code(403);
    die();
}

include("../../configuracion/ConexionComun.php");

define('FPDF_FONTPATH', 'font/');

require_once('../../plugins/tcpdf/examples/lang/spa.php');

require_once('../../plugins/tcpdf/tcpdf.php');

$original_mem = ini_get('memory_limit');
ini_set('memory_limit', '1024M');
ini_set('max_execution_time', 600);

class MYPDF extends TCPDF
{
    public $conexion;
    //=========================================== Datos del Reporte ====================================================/

    function formatoDinero($numero, $fractional = true)
    {
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
        return "Bs. " . $numero;
    }

    function getRegistro($id_ejecutor)
    {


        $condicionAC = '';
        if ($id_ejecutor != '') {

            $condicionAC .= " t46.id_ejecutor = '" . $id_ejecutor . "' AND ";
        }

        $comunes = new ConexionComun();

        $sql = "select t3.tx_codigo as nu_sector,t3.tx_descripcion as tx_sector,t46.id as id_accion_centralizada,
                        t47.id_accion,t46.id_ejercicio::integer as nu_anio,t1.de_nombre as de_programa,
                        t2.tx_ejecutor,t4.nu_numero,t4.de_nombre as de_actividad,
                        t2.id_ejecutor,t3.tx_codigo||'.'||t1.nu_original||'.00.'||t4.nu_numero as co_presupuesto,t1.nu_original,t3.id as id_sector
                from    t46_acciones_centralizadas as t46
                        left join t47_ac_accion_especifica as t47 on t46.id = t47.id_accion_centralizada
                        join mantenimiento.tab_ac_predefinida as t1 on t1.id = t46.id_accion
                        join mantenimiento.tab_ejecutores as t2 on t2.id_ejecutor = t46.id_ejecutor
                        inner join mantenimiento.tab_sectores as t3 on t46.id_subsector=t3.id
                        join mantenimiento.tab_ac_ae_predefinida as t4 on t4.id = t47.id_accion
                where t46.edo_reg is true and " . $condicionAC . " t46.id_ejercicio = " . $_SESSION['ejercicio_fiscal'] . " group by 1,2,3,4,5,6,7,8,t4.de_nombre,10,t1.nu_original,t3.id  order by 1 asc, t46.id_ejecutor asc, 4 asc";

        /*echo $sql;
exit();*/
        $this->datos = $comunes->ObtenerFilasBySqlSelect($sql);
        $this->cantidadTotal = $comunes->getFilas($sql);
    }

    public function Footer()
    {
        /*$this->getRegistro('PR130120150002','');
		foreach($this->datos as $key => $campo){
			$tipo = $campo["co_tipo"];
		}*/
        pie($this, 'h', 2);
        $this->Cell(0, 10, 'Pagina '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
    }
    public function setHeader()
    {
           /* $this->Image('../../images/escudo.jpg', 200, 3, 30, 25, 'JPG', '', '', true, 150, '', false, false, 0, false, false, false);
            $this->setXY(55,7);
            $this->SetFont('','B',11);
            $this->setY(30);*/
    }
    public function cuerpo()
    {
        
        $this->getPortada();
        $this->getPartida();
        $this->getSector();
        
    }

    function getPortada(){

                $this->AddPage();
                $this->Image('../../images/escudo.jpg', 110, 15, 60, 60, 'JPG', '', '', true, 150, '', false, false, 0, false, false, false);
         
                $bMargin = $this->getBreakMargin();
                $auto_page_break = $this->AutoPageBreak;
                $this->SetAutoPageBreak(false, 0);
                $this->SetAutoPageBreak($auto_page_break, $bMargin);
                $this->setPageMark();
                /******Portada*********/
                $this->SetY(75);
                $this->SetFont('', 'B', 20);
                $this->SetTextColor(0, 0, 0);
                $this->SetFont('', 'B', 20);
                $this->SetTextColor(0, 0, 0);
                $this->Ln(10);
                $this->Write(0, "PROYECTO DE ORDENANZA DE \n PRESUPUESTO DE INGRESOS Y  \n GASTOS AÑO ".$_SESSION['ejercicio_fiscal'], '', 0, 'C', true, 0, false, false, 0);
                $this->SetFont('', 'BU', 20);


                $this->SetFont('', 'B', 12);
                $this->Ln(50);
                $this->Write(0, "DICIEMBRE ".($_SESSION['ejercicio_fiscal']-1), '', 0, 'R', false, 0, false, false, 0);
                

                $this->SetY(190);
                $this->SetFont('', '', 11); 

    }



    function getSector(){

        if ($_GET['id_ejecutor'] != '') {
            $id_ejecutor = decode($_GET['id_ejecutor']);
        }

        $this->getRegistro($id_ejecutor);
        $comunes = new ConexionComun();

        $portada = 0;
        $nu_sector = '';

        $cant_sector = 0;

        foreach ($this->datos as $key => $campo) {

            if ($nu_sector <> $campo['nu_sector']) {


                if ($nu_sector == '') {
                    $nu_sector = $campo['nu_sector'];
                }

                $this->AddPage();
                $this->Image('../../images/escudo.jpg', 230, 10, 30, 25, 'JPG', '', '', true, 150, '', false, false, 0, false, false, false);
         
                $bMargin = $this->getBreakMargin();
                $auto_page_break = $this->AutoPageBreak;
                $this->SetAutoPageBreak(false, 0);
                $this->SetAutoPageBreak($auto_page_break, $bMargin);
                $this->setPageMark();
                /******Portada*********/
                $this->SetY(75);
                $this->SetFont('', 'B', 20);
                $this->SetTextColor(0, 0, 0);
                $this->SetFont('', 'B', 20);
                $this->SetTextColor(0, 0, 0);
                $this->Ln(10);
                $this->Write(0, 'SECTOR ' . $campo['nu_sector'] . ' "' . $campo['tx_sector'] . '"', '', 0, 'C', true, 0, false, false, 0);
                $this->SetFont('', 'BU', 20);
                $anio = $campo['nu_anio'] - 1;
                $this->SetY(190);
                $this->SetFont('', '', 11);

                $nu_sector = $campo['nu_sector'];


                $this->getPrograma($comunes,  $nu_sector);
            }
        }

    }

    function getPartida()
    {

             
        /******Objetivos*********/
        $this->AddPage();
        $this->Image('../../images/escudo.jpg', 240, 31,20, 20, 'JPG', '', '', true, 150, '', false, false, 0, false, false, false);
          
        $htmlObjetivo = '
                <table border="0.1" style="width:100%;text-align: center;" cellpadding="3">
                     <tr>
                        <td>
                            <table>
                               <tr align="left" style="border: 0px">
                                    <td colspan="3"><b>ENTIDAD FEDERAL : ZULIA</b></td>
                                </tr>
                                <tr align="left" border: 0px>                        
                                    <td colspan="3"><b>CODIGO PRESUPUESTARIO Y NOMBRE DEL MUNICIPIO: E7210 MARACAIBO</b></td>
                                </tr>
                                <tr align="left" >
                                    <td colspan="3"><b>PRESUPUESTO: '.$_SESSION['ejercicio_fiscal'].'</b></td>
                                </tr>
                                <tr align="center" >
                                    <td colspan="3"><b>TITULO II</b></td>
                                </tr>
                                <tr align="center" >
                                    <td colspan="3"><b>PRESUPUESTO DE INGRESOS</b></td>
                                </tr>
                                <tr align="center" >
                                    <td colspan="3">(EN BOLIVARES)</td>
                                </tr>
                            </table>
                        </td>
                    </tr>                                         
                </table>';
        $this->SetFont('', '', 8);
        //$this->Ln(-20);
        $this->writeHTML($htmlObjetivo, true, false, false, false, '');
        $this->Ln(-6);

        $deno = '<table border="0.1" style="width:100%;text-align: center;" cellpadding="3">
                    <thead>
                        <tr>
                            <td style="width:200px;text-align: center;" colspan="4"><b>CODIGO</b></td>
                            <td style="width:350px;text-align: center;" rowspan="2"><b>DENOMINACIÓN</b></td>
                            <td style="width:157px;text-align: center;" rowspan="2"><b>MONTO</b></td>
                        </tr>
                        <tr>
                            <td style="text-align: center;"><b>RAMO</b></td>
                            <td style="text-align: center;"><b>SUB-RAMO</b></td>
                            <td style="text-align: center;"><b>ESP.</b></td>
                            <td style="text-align: center;"><b>SUB-ESP</b></td>
                        </tr>
                    </thead>';
      

        $deno .= '</table>';
        $this->SetFont('', '', 8);
        //$this->Ln(-20);
        $this->writeHTML($deno, true, false, false, false, '');

        // $portada = $portada + 1;
        //$obj->AddPage();
    }



    function getPrograma($comunes,  $nu_sector)
    {
        $sql_sector = "select distinct t46.id as id_accion_centralizada,
                                        t1.nu_original as nu_programa,
                                        t1.de_nombre as de_programa,t46.id_ejecutor,
                                        t2.tx_ejecutor,t3.tx_codigo,tx_ejecutor_poa,t46.monto,t3.tx_descripcion
                                from t46_acciones_centralizadas as t46
                                        left join t47_ac_accion_especifica as t47 on t46.id = t47.id_accion_centralizada
                                join mantenimiento.tab_ac_predefinida as t1 on t1.id = t46.id_accion
                                join mantenimiento.tab_ejecutores as t2 on t2.id_ejecutor = t46.id_ejecutor
                                inner join mantenimiento.tab_sectores as t3 on t46.id_subsector=t3.id
                                        join mantenimiento.tab_ac_ae_predefinida as t4 on t4.id = t47.id_accion
                                        where t46.edo_reg is true and  t3.tx_codigo = '" . $nu_sector . "' and t46.id_ejercicio = " . $_SESSION['ejercicio_fiscal'] . " order by t46.id_ejecutor asc";


        $datos_programa = $comunes->ObtenerFilasBySqlSelect($sql_sector);

        foreach ($datos_programa as $key => $campo) {

            $this->AddPage();
            $this->Image('../../images/escudo.jpg', 230, 10, 30, 25, 'JPG', '', '', true, 150, '', false, false, 0, false, false, false);
         
            $bMargin = $this->getBreakMargin();
            $auto_page_break = $this->AutoPageBreak;
            $this->SetAutoPageBreak(false, 0);
            $this->SetAutoPageBreak($auto_page_break, $bMargin);
            $this->setPageMark();
            /******Portada*********/
            $this->SetY(75);
            $this->SetFont('', 'B', 20);
            $this->SetTextColor(0, 0, 0);
            $this->SetFont('', 'B', 20);
            $this->SetTextColor(0, 0, 0);
            $this->Ln(10);
            $this->Write(0, 'PROGRAMA ' . $campo['nu_programa'] . ' "' . $campo['de_programa'] . '"', '', 0, 'C', true, 0, false, false, 0);
            $this->SetFont('', 'BU', 20);
            $anio = $campo['nu_anio'] - 1;
            $this->SetY(190);
            $this->SetFont('', '', 11);

            $this->getDescripcionPrograma($comunes, $campo['id_accion_centralizada']);

            $nu_sector = $campo['nu_sector'];
        }
    }

    function getDescripcionPrograma($comunes, $id_accion_centralizada)
    {

        $sqlAc = "select t3.tx_codigo as nu_sector,t3.tx_descripcion as tx_sector,t46.id as id_accion_centralizada,t46.inst_objetivos,
                        t47.id_accion,t46.id_ejercicio::integer as nu_anio,t1.de_nombre as de_programa, 
                        t2.tx_ejecutor,t4.nu_numero,t4.de_nombre as de_actividad,
                        t2.id_ejecutor,t3.tx_codigo||'.'||t1.nu_original||'.00.'||t4.nu_numero as co_presupuesto,t1.nu_original,t3.id as id_sector,t2.id_ejecutor
                from    t46_acciones_centralizadas as t46
                        left join t47_ac_accion_especifica as t47 on t46.id = t47.id_accion_centralizada
                        join mantenimiento.tab_ac_predefinida as t1 on t1.id = t46.id_accion
                        join mantenimiento.tab_ejecutores as t2 on t2.id_ejecutor = t46.id_ejecutor
                        inner join mantenimiento.tab_sectores as t3 on t46.id_subsector=t3.id
                        join mantenimiento.tab_ac_ae_predefinida as t4 on t4.id = t47.id_accion
                where t46.id= $id_accion_centralizada group by 1,2,3,4,5,6,7,8,t4.de_nombre,10,t4.nu_numero,t1.nu_original,t3.id,t2.id_ejecutor,t46.inst_objetivos  order by 1 asc, t46.id_ejecutor asc, 4 asc";
        
        $datos = $comunes->ObtenerFilasBySqlSelect($sqlAc);

        $datos_ac = $datos[0];

      
        /******Objetivos*********/
        $this->AddPage();
        $this->Image('../../images/escudo.jpg', 240, 31, 18, 18, 'JPG', '', '', true, 150, '', false, false, 0, false, false, false);
         
        $htmlObjetivo = '
                <table border="0.1" style="width:100%;text-align: center;" cellpadding="3">
                    <tr>
                        <td>
                            <table>
                               <tr align="left" style="border: 0px">
                                    <td colspan="3"><b>ENTIDAD FEDERAL : ZULIA</b></td>
                                </tr>
                                <tr align="left" border: 0px>                        
                                    <td colspan="3"><b>CODIGO PRESUPUESTARIO Y NOMBRE DEL MUNICIPIO: E7210 MARACAIBO</b></td>
                                </tr>
                                <tr align="left" >
                                    <td colspan="3"><b>PRESUPUESTO: '.$datos_ac["nu_anio"].'</b></td>
                                </tr>
                                <tr align="center" >
                                    <td colspan="3"><b>DESCRIPCION DEL PROGRAMA Y SUB - PROGRAMA</b></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr align="left">
                        <td style="width:160px;text-align: center;"></td>
                        <td style="width:60px;text-align: center;"><b>CODIGO</b></td>
                        <td style="width:487px;text-align: center;"><b>DENOMINACIÓN</b></td>
                    </tr>
                    <tr align="left">
                        <td><b>SECTOR: </b></td>
                        <td align="center">'.$datos_ac["nu_sector"].'</td>
                        <td>'.$datos_ac["tx_sector"].'</td>
                    </tr>
                    <tr align="left">
                        <td><b>PROGRAMA: </b></td>
                        <td align="center">'.$datos_ac["nu_original"].'</td>
                        <td>'.$datos_ac["de_programa"].'</td>
                    </tr>
                    <tr align="left">
                        <td><b>SUB-PROGRAMA: </b></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr align="left">
                        <td><b>UNIDAD (ES) EJECUTORA (S): </b></td>
                        <td align="center">'.$datos_ac["id_ejecutor"].'</td>
                        <td>'.$datos_ac["tx_ejecutor"].'</td>
                    </tr>   
                <thead>
                    <tr>
                        <td colspan="3"><b>DESCRIPCIÓN</b></td>
                    </tr>
                </thead>
                <tbody>
                    <tr nobr="true">
                        <td colspan="3" height="100" align="justify">' . str_replace(array("\r\n", "\r", "\n", "\\r", "\\n", "\\r\\n"), "<br/>", $datos_ac['inst_objetivos']) . '</td>
                    </tr>        
                   
                </tbody>
                </table>';
        $this->SetFont('', '', 10);
        //$this->Ln(-20);
        $this->writeHTML($htmlObjetivo, true, false, false, false, '');

        $this->getMetasPrograma($comunes, $datos_ac["id_accion_centralizada"],$datos_ac['id_accion'],$datos_ac);

        // $portada = $portada + 1;
        //$obj->AddPage();
    }

    
    function getMetasPrograma($comunes, $id_accion_centralizada,$co_ac_acc_espec, $datos_ac)
    {

        $sqlAc = "SELECT co_metas,id_tab_t47_ac_accion_especifica,capitalize_sentence(lower(nb_meta)) as nb_meta,capitalize_sentence(lower(nb_responsable)) as nb_responsable,tx_prog_anual,
                        monto,
                        de_unidad_medida
                    FROM t69_metas_ac as t69
                    inner join mantenimiento.tab_unidad_medida as t21 on t69.co_unidades_medida=t21.id
                    WHERE t69.id_accion_centralizada = $id_accion_centralizada and t69.co_ac_acc_espec = $co_ac_acc_espec and t69.edo_reg is true 
                    order by co_metas ASC ";

             
        $datos = $comunes->ObtenerFilasBySqlSelect($sqlAc);
      
        /******Objetivos*********/
        $this->AddPage();
        $this->Image('../../images/escudo.jpg', 240, 31, 15, 15, 'JPG', '', '', true, 150, '', false, false, 0, false, false, false);
          
        $htmlObjetivo = '
                <table border="0.1" style="width:100%;text-align: center;" cellpadding="3">
                     <tr>
                        <td>
                            <table>
                               <tr align="left" style="border: 0px">
                                    <td colspan="3"><b>ENTIDAD FEDERAL : ZULIA</b></td>
                                </tr>
                                <tr align="left" border: 0px>                        
                                    <td colspan="3"><b>CODIGO PRESUPUESTARIO Y NOMBRE DEL MUNICIPIO: E7210 MARACAIBO</b></td>
                                </tr>
                                <tr align="left" >
                                    <td colspan="3"><b>PRESUPUESTO: '.$datos_ac["nu_anio"].'</b></td>
                                </tr>
                                <tr align="center" >
                                    <td colspan="3"><b>METAS DEL PROGRAMA, SUB - PROGRAMA Y/O PROYECTO</b></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr align="left">
                        <td style="width:160px;text-align: center;"></td>
                        <td style="width:60px;text-align: center;"><b>CODIGO</b></td>
                        <td style="width:487px;text-align: center;"><b>DENOMINACIÓN</b></td>
                    </tr>
                    <tr align="left">
                        <td><b>SECTOR: </b></td>
                        <td align="center">'.$datos_ac["nu_sector"].'</td>
                        <td>'.$datos_ac["tx_sector"].'</td>
                    </tr>
                    <tr align="left">
                        <td><b>PROGRAMA: </b></td>
                        <td align="center">'.$datos_ac["nu_original"].'</td>
                        <td>'.$datos_ac["de_programa"].'</td>
                    </tr>
                    <tr align="left">
                        <td><b>SUB-PROGRAMA: </b></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr align="left">
                        <td><b>UNIDAD (ES) EJECUTORA (S): </b></td>
                        <td align="center">'.$datos_ac["id_ejecutor"].'</td>
                        <td>'.$datos_ac["tx_ejecutor"].'</td>
                    </tr>                              
                </table>';
        $this->SetFont('', '', 8);
        //$this->Ln(-20);
        $this->writeHTML($htmlObjetivo, true, false, false, false, '');
        $this->Ln(-6);

        $deno = '<table border="0.1" style="width:100%;text-align: center;" cellpadding="3">
                    <thead>
                        <tr>
                            <td style="width:300px;text-align: center;"><b>DENOMINACIÓN</b></td>
                            <td style="width:148px;text-align: center;"><b>UNIDAD DE MEDIDA</b></td>
                            <td style="width:130px;text-align: center;"><b>CANTIDADES PROGRAMADAS</b></td>
                            <td style="width:130px;text-align: center;"><b>COSTO FINANCIERO</b></td>
                       </tr>
                    </thead>';
      //  var_dump($datos); exit();
        foreach ($datos as $key => $campo) {

            $deno .= '<tr>
                         <td style="width:300px;text-align: justify;">'. $campo["nb_meta"].'</td>
                         <td style="width:148px;text-align: justify;">'. $campo["nb_responsable"].'</td>
                         <td style="width:130px;text-align: center;">'. $campo["tx_prog_anual"].'</td>
                         <td style="width:130px;text-align: rigth;">'. number_format($campo["monto"]).'</td>
                      </tr>';       
        }



        $deno .= '</table>';
        $this->SetFont('', '', 8);
        //$this->Ln(-20);
        $this->writeHTML($deno, true, false, false, false, '');

        // $portada = $portada + 1;
        //$obj->AddPage();
    }
}

//Crear new PDF documento
$pdf = new MYPDF("L", PDF_UNIT, 'Letter', true, 'UTF-8', false);
$pdf->SetCreator('Yoser Perez');
$pdf->SetAuthor('Secretaria de Planificacion y Estadistica');
$pdf->SetTitle('PROGRAMAS - ACTIVIDADES');
$pdf->SetSubject('MI DOCUMENTO');
$pdf->SetKeywords('Planilla, PDF, Registro');
$pdf->SetMargins(15, 20, 15);
$pdf->SetTopMargin(30);
$pdf->setPrintHeader(false);
$pdf->SetPrintFooter(true);
$pdf->cuerpo();
$pdf->Output('Ordenanza_' . $_SESSION['ejercicio_fiscal'] . '_' . date("H:i:s") . '.pdf', 'D');