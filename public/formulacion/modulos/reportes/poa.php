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

    //$_SESSION['ejercicio_fiscal'] 

    public function Footer()
    {
        /*$this->getRegistro('PR130120150002','');
		foreach($this->datos as $key => $campo){
			$tipo = $campo["co_tipo"];
		}*/
        pie($this, 'h', 2);
        $this->Cell(0, 10, 'Pagina ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
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
        $this->getTitulo2();        
        $this->getTitulo3();
        $this->getSector();

    }

    function getPortada()
    {

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
        $this->Ln();
        $this->SetFont('', 'B', 14);
        $this->Write(0, "Alcaldia Bolivariana \n de Maracaibo ", '', 0, 'C', true, 0, false, false, 0);
      
        $this->Ln(10);
        $this->SetFont('', 'B', 20);
        $this->Write(0, "PROYECTO DE ORDENANZA DE \n PRESUPUESTO DE INGRESOS Y  \n GASTOS AÑO " . $_SESSION['ejercicio_fiscal'], '', 0, 'C', true, 0, false, false, 0);
        $this->SetFont('', 'BU', 20);


        $this->SetFont('', 'B', 12);
        $this->Ln(50);
        $this->Write(0, "DICIEMBRE " . ($_SESSION['ejercicio_fiscal'] - 1), '', 0, 'R', false, 0, false, false, 0);$this->SetFont('', 'B', 14);
        $this->Ln(10);
        $this->SetFont('', 'BI', 12);
        $this->Write(0, "ALCALDIA BOLIVARIANA DE MARACAIBO", '', 0, 'R', true, 0, false, false, 0);



        $this->SetY(190);
        $this->SetFont('', '', 11);
    }
    
    
    function getTitulo2()
    {


        $comunes = new ConexionComun();
        /******Objetivos*********/
        $this->AddPage();
        $this->Image('../../images/escudo.jpg', 240, 31, 20, 20, 'JPG', '', '', true, 150, '', false, false, 0, false, false, false);

        $total = 0;
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
                                    <td colspan="3"><b>PRESUPUESTO: ' . $_SESSION['ejercicio_fiscal'] . '</b></td>
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

        $sql_nivel1 = "SELECT co_partida,tx_nombre,
                                            sum((select sum(mo_partida) from mantenimiento.tab_presupuesto_ingreso where id_tab_ejercicio_fiscal = tp.id_tab_ejercicio_fiscal and cast(nu_partida as varchar) like cast(tp.co_partida as varchar)||'%')) as monto
                                        FROM mantenimiento.tab_partidas as tp
                                        where co_partida like '3%' and 
                                            id_tab_ejercicio_fiscal = " . $_SESSION['ejercicio_fiscal'] . " and
                                            length(co_partida) = 3
                                        group by tp.id
                                        ORDER BY length(co_partida),co_partida ASC";

        $datos_nivel1 = $comunes->ObtenerFilasBySqlSelect($sql_nivel1);

        foreach ($datos_nivel1 as $key => $campo) {

            $deno .= '<tr>
                                    <td style="width:50px;text-align: justify;"><b>' . $campo["co_partida"] . '</b></td>
                                    <td style="width:50px;text-align: justify;"><b>00</b></td>
                                    <td style="width:50px;text-align: justify;"><b>00</b></td>
                                    <td style="width:50px;text-align: justify;"><b>00</b></td>
                                    <td style="width:350px;text-align: justify;"><b><u>' . $campo["tx_nombre"] . '</u></b></td>
                                    <td style="width:157px;text-align: rigth;"><b>' . number_format($campo["monto"]) . '</b></td>
                                </tr>';

            $total += $campo["monto"];                    
            $sql_nivel2 = "SELECT SUBSTRING(co_partida,0,4) as ramo,SUBSTRING(co_partida,4,3) as subramo,tx_nombre,length(co_partida),co_partida,
                                            sum((select coalesce(sum(mo_partida),0) from mantenimiento.tab_presupuesto_ingreso where id_tab_ejercicio_fiscal = tp.id_tab_ejercicio_fiscal and cast(nu_partida as varchar) like cast(tp.co_partida as varchar)||'%')) as monto
                                        FROM mantenimiento.tab_partidas as tp
                                        where co_partida like '" . trim($campo["co_partida"]) . "%' and 
                                            id_tab_ejercicio_fiscal = " . $_SESSION['ejercicio_fiscal'] . " and
                                            length(co_partida) = 5
                                        group by tp.id
                                        having sum((select coalesce(sum(mo_partida),0) from mantenimiento.tab_presupuesto_ingreso where id_tab_ejercicio_fiscal = tp.id_tab_ejercicio_fiscal and cast(nu_partida as varchar) like cast(tp.co_partida as varchar)||'%'))  > 0
                                        ORDER BY length(co_partida),co_partida ASC";
           


            $datos_nivel2 = $comunes->ObtenerFilasBySqlSelect($sql_nivel2);

            foreach ($datos_nivel2 as $key2 => $campo2) {

                                $deno .= '<tr>
                                    <td style="width:50px;text-align: justify;"><b>' . $campo2["ramo"] . '</b></td>
                                    <td style="width:50px;text-align: justify;"><b>' . $campo2["subramo"] . '</b></td>
                                    <td style="width:50px;text-align: justify;"><b>00</b></td>
                                    <td style="width:50px;text-align: justify;"><b>00</b></td>
                                    <td style="width:350px;text-align: justify;">&nbsp;&nbsp;&nbsp;&nbsp;<b>' . $campo2["tx_nombre"] . '</b></td>
                                    <td style="width:157px;text-align: rigth;">' . number_format($campo2["monto"]) . '</td>
                                </tr>';

                    $sql_nivel4 = "SELECT   distinct SUBSTRING(co_partida,0,4) as ramo,
                                            SUBSTRING(co_partida,4,2) as subramo,
                                            SUBSTRING(co_partida,6,2) as esp,
                                            co_partida,tx_nombre,length(co_partida),
                                            sum((select coalesce(sum(mo_partida),0) from mantenimiento.tab_presupuesto_ingreso where id_tab_ejercicio_fiscal = tp.id_tab_ejercicio_fiscal and cast(nu_partida as varchar) like cast(tp.co_partida as varchar)||'%')) as monto
                                        FROM mantenimiento.tab_partidas as tp
                                        where co_partida like '".trim($campo2["co_partida"])."%' and 
                                            id_tab_ejercicio_fiscal = ".$_SESSION['ejercicio_fiscal']." and
                                            length(co_partida) = 9
                                        group by tp.id
                                        having sum((select coalesce(sum(mo_partida),0) from mantenimiento.tab_presupuesto_ingreso where id_tab_ejercicio_fiscal = tp.id_tab_ejercicio_fiscal and cast(nu_partida as varchar) like cast(tp.co_partida as varchar)||'%'))  > 0
                                        ORDER BY length(co_partida),co_partida ASC";

                    $datos_nivel4 = $comunes->ObtenerFilasBySqlSelect($sql_nivel4);

                     foreach ($datos_nivel4 as $key2 => $campo4) {

                                $deno .= '<tr>
                                    <td style="width:50px;text-align: justify;">' . $campo4["ramo"] . '</td>
                                    <td style="width:50px;text-align: justify;">' . $campo4["subramo"] . '</td>
                                    <td style="width:50px;text-align: justify;">' . $campo4["esp"] . '</td>
                                    <td style="width:50px;text-align: justify;">00</td>
                                    <td style="width:350px;text-align: justify;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<u>' . $campo4["tx_nombre"] . '</u></td>
                                    <td style="width:157px;text-align: rigth;">' . number_format($campo4["monto"]) . '</td>
                                </tr>';


                                 $sql_partida = "select SUBSTRING(cast(nu_partida as varchar),0,4) as ramo,
                                                SUBSTRING(cast(nu_partida as varchar),4,2) as subramo,
                                                SUBSTRING(cast(nu_partida as varchar),6,2) as esp,
                                                SUBSTRING(cast(nu_partida as varchar),8,2) as subesp,
                                                de_partida,
                                                mo_partida
                                            from mantenimiento.tab_presupuesto_ingreso 
                                            where id_tab_ejercicio_fiscal = ".$_SESSION['ejercicio_fiscal']." and cast(nu_partida as varchar) like '".trim($campo4["co_partida"])."'||'%'";

                               

                                $datos_partida  = $comunes->ObtenerFilasBySqlSelect($sql_partida);

                                if(count($datos_partida) > 1){

                                    foreach ($datos_partida as $keyp => $campop) {

                                        $deno .= '<tr>
                                            <td style="width:50px;text-align: justify;">' . $campop["ramo"] . '</td>
                                            <td style="width:50px;text-align: justify;">' . $campop["subramo"] . '</td>
                                            <td style="width:50px;text-align: justify;">' . $campop["esp"] . '</td>
                                            <td style="width:50px;text-align: justify;">' . $campop["subesp"] . '</td>
                                            <td style="width:350px;text-align: justify;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $campop["de_partida"] . '</td>
                                            <td style="width:157px;text-align: rigth;">' . number_format($campop["mo_partida"]) . '</td>
                                        </tr>';                                   
                                    }
                            }

                     }
            
            }
        }

        
         $deno .= '<tr>                                    
                                    <td style="width:550px;text-align: rigth;" colspan="5"><b>Monto Total</b></td>
                                    <td style="width:157px;text-align: rigth;"><b>' . number_format($total) . '</b></td>
                                </tr>';

            





        $deno .= '</table>';
        $this->SetFont('', '', 8);
        //$this->Ln(-20);
        $this->writeHTML($deno, true, false, false, false, '');

        // $portada = $portada + 1;
        //$obj->AddPage();
    }    
    
        public function getTitulo3()
    {
        

        $comunes = new ConexionComun();
       $this->AddPage(); 
       $this->Image('../../images/escudo.jpg', 240, 31, 20, 20, 'JPG', '', '', true, 150, '', false, false, 0, false, false, false);
        $html3 = '
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
                                    <td colspan="3"><b>TITULO III</b></td>
                                </tr>
                                <tr align="center" >
                                    <td colspan="3"><b>PRESUPUESTO DE GASTOS DEL MUNICIPIO POR SECTORES A NIVEL DE PARTIDAS Y SUB-PARTIDAS</b></td>
                                </tr> 
                                    <tr align="center" >
                                    <td colspan="3">(EN BOLIVARES)</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                <tbody> 
                   
                </tbody>
                </table>';
        
        $html31 = '<style type="text/css">
.tg  {border-collapse:collapse;border-spacing:0;}
.tg td{border-color:black;border-style:solid;border-width:1px;font-family:Arial, sans-serif;font-size:14px;
  overflow:hidden;padding:10px 5px;word-break:normal;}
.tg th{border-color:black;border-style:solid;border-width:1px;font-family:Arial, sans-serif;font-size:14px;
  font-weight:normal;overflow:hidden;padding:10px 5px;word-break:normal;}
.tg .tg-cly1{text-align:left;vertical-align:middle}
.tg .tg-baqh{text-align:center;vertical-align:top}
.tg .tg-nrix{text-align:center;vertical-align:middle}
.tg .tg-0lax{text-align:left;vertical-align:top}
</style>
                <table border="0.1" style="width:100%;text-align: center;" cellpadding="3">
<thead>
  <tr style="font-size:6px">
    <td style="width: 15%" colspan="4">CODIGOS</td>
    <td style="width: 15%;vertical-align:middle;" rowspan="3">DENOMINACION </td>
    <td style="width: 70%" colspan="9">SECTORES </td>
  </tr>
  <tr style="font-size:6px">
    <td rowspan="2">PART.</td>
    <td colspan="3">SUB-PARTIDAS</td>
    <td rowspan="2">01. "DIRECCION SUPERIOR DE LA MUNICIPALIDAD"</td>
    <td rowspan="2">02. "SEGURIDAD Y DEFENSA"</td>
    <td rowspan="2">06. "TURISMO Y RECREACION"</td>
    <td rowspan="2">09. "CULTURA Y COMUNICACION SOCIAL"</td>
    <td rowspan="2">11. "VIVIENDA, DESARROLLO URBANO Y SERVICIOS CONEXOS"</td>
    <td rowspan="2">13. "DESARROLLO SOCIAL Y PARTICIPACION"</td>
    <td rowspan="2">14. "SEGURIDAD SOCIAL"</td>
    <td rowspan="2">15. "GASTOS NO CLASIFICADOS SECTORIALMENTE"</td>
    <td rowspan="2">TOTAL</td>
  </tr>
  <tr style="font-size:6px">
    <td class="tg-cly1">GEN.</td>
    <td class="tg-cly1">ESP.</td>
    <td class="tg-nrix">SUB-ESP.</td>
  </tr>
</thead>';  
        
$html31.='
</table>';

$html32.='
<table border="0.1">
<tr>
<td>
<table>';
        
                
        $sql_nivel1 = " SELECT REPLACE(t02.co_partida, ' ', '') as co_partida,tx_nombre
        FROM t54_ac_ae_partidas t54
     inner join mantenimiento.tab_partidas as t02 on SUBSTRING(t54.co_partida, 1,3)=t02.co_partida and t54.id_tab_ejercicio_fiscal = t02.id_tab_ejercicio_fiscal
     JOIN t46_acciones_centralizadas t46 ON t46.id = t54.id_accion_centralizada
     where t54.id_tab_ejercicio_fiscal = ".$_SESSION['ejercicio_fiscal']."
     group by t02.co_partida,tx_nombre";

        $datos_nivel1 = $comunes->ObtenerFilasBySqlSelect($sql_nivel1);

        foreach ($datos_nivel1 as $key => $campo1) {
            
        $total_sector = 0;
            
        $monto_sector_1 =  $this->getMontoPartidaSector($comunes, $campo1['co_partida'], 1);
        $monto_sector_2 =  $this->getMontoPartidaSector($comunes, $campo1['co_partida'], 3);
        $monto_sector_6 =  $this->getMontoPartidaSector($comunes, $campo1['co_partida'], 11);
        $monto_sector_9 =  $this->getMontoPartidaSector($comunes, $campo1['co_partida'], 17);
        $monto_sector_11 =  $this->getMontoPartidaSector($comunes, $campo1['co_partida'], 19);
        $monto_sector_13 =  $this->getMontoPartidaSector($comunes, $campo1['co_partida'], 23);
        $monto_sector_14 =  $this->getMontoPartidaSector($comunes, $campo1['co_partida'], 25);
        $monto_sector_15 =  $this->getMontoPartidaSector($comunes, $campo1['co_partida'], 27);
        
        $total_sector = $monto_sector_1 + $monto_sector_2 + $monto_sector_6 + $monto_sector_9 + $monto_sector_11 + $monto_sector_13 + $monto_sector_14 + $monto_sector_15;
            

    		$html32.='
		<tr style="font-size:6px">
                <td style="width: 3.75%;" align="center" ><b>'.$campo1['co_partida'].'</b></td>
                <td style="width: 3.75%;" align="left" ></td>
                <td style="width: 3.75%;" align="left" ></td>
                <td style="width: 3.75%;" align="left" ></td>
                <td style="width: 15%;" align="left" ><b>'.$campo1['tx_nombre'].'</b></td>
                <td style="width: 7.78%;" align="right" ><b>'.number_format($monto_sector_1, 2, ',','.').'</b></td>
                <td style="width: 7.78%;" align="right" ><b>'.number_format($monto_sector_2, 2, ',','.').'</b></td>
                <td style="width: 7.78%;" align="right" ><b>'.number_format($monto_sector_6, 2, ',','.').'</b></td>
                <td style="width: 7.78%;" align="right" ><b>'.number_format($monto_sector_9, 2, ',','.').'</b></td>
                <td style="width: 7.78%;" align="right" ><b>'.number_format($monto_sector_11, 2, ',','.').'</b></td>
                <td style="width: 7.78%;" align="right" ><b>'.number_format($monto_sector_13, 2, ',','.').'</b></td>
                <td style="width: 7.78%;" align="right" ><b>'.number_format($monto_sector_14, 2, ',','.').'</b></td>
                <td style="width: 7.78%;" align="right" ><b>'.number_format($monto_sector_15, 2, ',','.').'</b></td>
                <td style="width: 7.78%;" align="right" ><b>'.number_format($total_sector, 2, ',','.').'</b></td>    
                </tr>';  
                

        $sql_nivel2 = " SELECT REPLACE(t02.co_partida, ' ', '') as co_partida,tx_nombre,SUBSTRING(t02.co_partida, 4,2) as co_partida_gen
        FROM t54_ac_ae_partidas t54
     inner join mantenimiento.tab_partidas as t02 on SUBSTRING(t54.co_partida, 1,5)=t02.co_partida and t54.id_tab_ejercicio_fiscal = t02.id_tab_ejercicio_fiscal
     JOIN t46_acciones_centralizadas t46 ON t46.id = t54.id_accion_centralizada
     where t54.id_tab_ejercicio_fiscal = ".$_SESSION['ejercicio_fiscal']." and t02.co_partida like '".$campo1['co_partida']."%'
     group by t02.co_partida,tx_nombre";

        $datos_nivel2 = $comunes->ObtenerFilasBySqlSelect($sql_nivel2);

        foreach ($datos_nivel2 as $key => $campo2) {
         
        $total_sector = 0;    
            
        $monto_sector_1 =  $this->getMontoPartidaSector($comunes, $campo2['co_partida'], 1);
        $monto_sector_2 =  $this->getMontoPartidaSector($comunes, $campo2['co_partida'], 3);
        $monto_sector_6 =  $this->getMontoPartidaSector($comunes, $campo2['co_partida'], 11);
        $monto_sector_9 =  $this->getMontoPartidaSector($comunes, $campo2['co_partida'], 17);
        $monto_sector_11 =  $this->getMontoPartidaSector($comunes, $campo2['co_partida'], 19);
        $monto_sector_13 =  $this->getMontoPartidaSector($comunes, $campo2['co_partida'], 23);
        $monto_sector_14 =  $this->getMontoPartidaSector($comunes, $campo2['co_partida'], 25);
        $monto_sector_15 =  $this->getMontoPartidaSector($comunes, $campo2['co_partida'], 27);
        
        $total_sector = $monto_sector_1 + $monto_sector_2 + $monto_sector_6 + $monto_sector_9 + $monto_sector_11 + $monto_sector_13 + $monto_sector_14 + $monto_sector_15;
            

    		$html32.='
		<tr style="font-size:6px">
                <td style="width: 3.75%;" align="center" ></td>
                <td style="width: 3.75%;" align="center" >'.$campo2['co_partida_gen'].'</td>
                <td style="width: 3.75%;" align="left" ></td>
                <td style="width: 3.75%;" align="left" ></td>
                <td style="width: 15%;" align="left" >'.$campo2['tx_nombre'].'</td>
                <td style="width: 7.78%;" align="right" >'.number_format($monto_sector_1, 2, ',','.').'</td>
                <td style="width: 7.78%;" align="right" >'.number_format($monto_sector_2, 2, ',','.').'</td>
                <td style="width: 7.78%;" align="right" >'.number_format($monto_sector_6, 2, ',','.').'</td>
                <td style="width: 7.78%;" align="right" >'.number_format($monto_sector_9, 2, ',','.').'</td>
                <td style="width: 7.78%;" align="right" >'.number_format($monto_sector_11, 2, ',','.').'</td>
                <td style="width: 7.78%;" align="right" >'.number_format($monto_sector_13, 2, ',','.').'</td>
                <td style="width: 7.78%;" align="right" >'.number_format($monto_sector_14, 2, ',','.').'</td>
                <td style="width: 7.78%;" align="right" >'.number_format($monto_sector_15, 2, ',','.').'</td>
                <td style="width: 7.78%;" align="right" >'.number_format($total_sector, 2, ',','.').'</td>    
                </tr>';  
                
        $sql_nivel3 = " SELECT REPLACE(t02.co_partida, ' ', '') as co_partida,tx_nombre,SUBSTRING(t02.co_partida, 6,2) as co_partida_es
        FROM t54_ac_ae_partidas t54
     inner join mantenimiento.tab_partidas as t02 on SUBSTRING(t54.co_partida, 1,7)=t02.co_partida and t54.id_tab_ejercicio_fiscal = t02.id_tab_ejercicio_fiscal
     JOIN t46_acciones_centralizadas t46 ON t46.id = t54.id_accion_centralizada
     where t54.id_tab_ejercicio_fiscal = ".$_SESSION['ejercicio_fiscal']." and t02.co_partida like '".$campo1['co_partida']."%'
     group by t02.co_partida,tx_nombre";

        $datos_nivel3 = $comunes->ObtenerFilasBySqlSelect($sql_nivel3);

        foreach ($datos_nivel3 as $key => $campo3) {
            
        $total_sector = 0;    
            
        $monto_sector_1 =  $this->getMontoPartidaSector($comunes, $campo3['co_partida'], 1);
        $monto_sector_2 =  $this->getMontoPartidaSector($comunes, $campo3['co_partida'], 3);
        $monto_sector_6 =  $this->getMontoPartidaSector($comunes, $campo3['co_partida'], 11);
        $monto_sector_9 =  $this->getMontoPartidaSector($comunes, $campo3['co_partida'], 17);
        $monto_sector_11 =  $this->getMontoPartidaSector($comunes, $campo3['co_partida'], 19);
        $monto_sector_13 =  $this->getMontoPartidaSector($comunes, $campo3['co_partida'], 23);
        $monto_sector_14 =  $this->getMontoPartidaSector($comunes, $campo3['co_partida'], 25);
        $monto_sector_15 =  $this->getMontoPartidaSector($comunes, $campo3['co_partida'], 27);
        
        $total_sector = $monto_sector_1 + $monto_sector_2 + $monto_sector_6 + $monto_sector_9 + $monto_sector_11 + $monto_sector_13 + $monto_sector_14 + $monto_sector_15;
            

    		$html32.='
		<tr style="font-size:6px">
                <td style="width: 3.75%;" align="center" ></td>
                <td style="width: 3.75%;" align="center" ></td>
                <td style="width: 3.75%;" align="center" >'.$campo3['co_partida_es'].'</td>
                <td style="width: 3.75%;" align="left" ></td>
                <td style="width: 15%;" align="left" >'.$campo3['tx_nombre'].'</td>
                <td style="width: 7.78%;" align="right" >'.number_format($monto_sector_1, 2, ',','.').'</td>
                <td style="width: 7.78%;" align="right" >'.number_format($monto_sector_2, 2, ',','.').'</td>
                <td style="width: 7.78%;" align="right" >'.number_format($monto_sector_6, 2, ',','.').'</td>
                <td style="width: 7.78%;" align="right" >'.number_format($monto_sector_9, 2, ',','.').'</td>
                <td style="width: 7.78%;" align="right" >'.number_format($monto_sector_11, 2, ',','.').'</td>
                <td style="width: 7.78%;" align="right" >'.number_format($monto_sector_13, 2, ',','.').'</td>
                <td style="width: 7.78%;" align="right" >'.number_format($monto_sector_14, 2, ',','.').'</td>
                <td style="width: 7.78%;" align="right" >'.number_format($monto_sector_15, 2, ',','.').'</td>
                <td style="width: 7.78%;" align="right" >'.number_format($total_sector, 2, ',','.').'</td>    
                </tr>';            
            
        $sql_nivel4 = " SELECT REPLACE(t02.co_partida, ' ', '') as co_partida,tx_nombre,SUBSTRING(t02.co_partida, 8,2) as co_partida_ses
        FROM t54_ac_ae_partidas t54
     inner join mantenimiento.tab_partidas as t02 on SUBSTRING(t54.co_partida, 1,9)=t02.co_partida and t54.id_tab_ejercicio_fiscal = t02.id_tab_ejercicio_fiscal
     JOIN t46_acciones_centralizadas t46 ON t46.id = t54.id_accion_centralizada
     where t54.id_tab_ejercicio_fiscal = ".$_SESSION['ejercicio_fiscal']." and t02.co_partida like '".$campo1['co_partida']."%'
     group by t02.co_partida,tx_nombre";

        $datos_nivel4 = $comunes->ObtenerFilasBySqlSelect($sql_nivel4);

        foreach ($datos_nivel4 as $key => $campo4) { 
            
        $total_sector = 0;    
            
        $monto_sector_1 =  $this->getMontoPartidaSector($comunes, $campo4['co_partida'], 1);
        $monto_sector_2 =  $this->getMontoPartidaSector($comunes, $campo4['co_partida'], 3);
        $monto_sector_6 =  $this->getMontoPartidaSector($comunes, $campo4['co_partida'], 11);
        $monto_sector_9 =  $this->getMontoPartidaSector($comunes, $campo4['co_partida'], 17);
        $monto_sector_11 =  $this->getMontoPartidaSector($comunes, $campo4['co_partida'], 19);
        $monto_sector_13 =  $this->getMontoPartidaSector($comunes, $campo4['co_partida'], 23);
        $monto_sector_14 =  $this->getMontoPartidaSector($comunes, $campo4['co_partida'], 25);
        $monto_sector_15 =  $this->getMontoPartidaSector($comunes, $campo4['co_partida'], 27);
        
        $total_sector = $monto_sector_1 + $monto_sector_2 + $monto_sector_6 + $monto_sector_9 + $monto_sector_11 + $monto_sector_13 + $monto_sector_14 + $monto_sector_15;
            

    		$html32.='
		<tr style="font-size:6px">
                <td style="width: 3.75%;" align="center" ></td>
                <td style="width: 3.75%;" align="center" ></td>
                <td style="width: 3.75%;" align="center" ></td>
                <td style="width: 3.75%;" align="center" >'.$campo4['co_partida_ses'].'</td>
                <td style="width: 15%;" align="left" >'.$campo4['tx_nombre'].'</td>
                <td style="width: 7.78%;" align="right" >'.number_format($monto_sector_1, 2, ',','.').'</td>
                <td style="width: 7.78%;" align="right" >'.number_format($monto_sector_2, 2, ',','.').'</td>
                <td style="width: 7.78%;" align="right" >'.number_format($monto_sector_6, 2, ',','.').'</td>
                <td style="width: 7.78%;" align="right" >'.number_format($monto_sector_9, 2, ',','.').'</td>
                <td style="width: 7.78%;" align="right" >'.number_format($monto_sector_11, 2, ',','.').'</td>
                <td style="width: 7.78%;" align="right" >'.number_format($monto_sector_13, 2, ',','.').'</td>
                <td style="width: 7.78%;" align="right" >'.number_format($monto_sector_14, 2, ',','.').'</td>
                <td style="width: 7.78%;" align="right" >'.number_format($monto_sector_15, 2, ',','.').'</td>
                <td style="width: 7.78%;" align="right" >'.number_format($total_sector, 2, ',','.').'</td>    
                </tr>';             
            
            
            
        }               
                
            
            
        }                
                
            
        }
                
            
        } 

$html32.='
</table></td>
</tr>
</table>';

$htmlT.='
<table border="0.1" style="width:100%;text-align: center;" cellpadding="3">
<thead>';

        $monto_sector_1 =  $this->getMontoSector($comunes, 1);
        $monto_sector_2 =  $this->getMontoSector($comunes, 3);
        $monto_sector_6 =  $this->getMontoSector($comunes, 11);
        $monto_sector_9 =  $this->getMontoSector($comunes, 17);
        $monto_sector_11 =  $this->getMontoSector($comunes, 19);
        $monto_sector_13 =  $this->getMontoSector($comunes, 23);
        $monto_sector_14 =  $this->getMontoSector($comunes, 25);
        $monto_sector_15 =  $this->getMontoSector($comunes, 27);
        
        $total_sector = $monto_sector_1 + $monto_sector_2 + $monto_sector_6 + $monto_sector_9 + $monto_sector_11 + $monto_sector_13 + $monto_sector_14 + $monto_sector_15;


    		$htmlT.='
		<tr style="font-size:6px">
                <td style="width: 30%;" align="center" > TOTAL</td>
                <td style="width: 7.78%;" align="right" >'.number_format($monto_sector_1, 2, ',','.').'</td>
                <td style="width: 7.78%;" align="right" >'.number_format($monto_sector_2, 2, ',','.').'</td>
                <td style="width: 7.78%;" align="right" >'.number_format($monto_sector_6, 2, ',','.').'</td>
                <td style="width: 7.78%;" align="right" >'.number_format($monto_sector_9, 2, ',','.').'</td>
                <td style="width: 7.78%;" align="right" >'.number_format($monto_sector_11, 2, ',','.').'</td>
                <td style="width: 7.78%;" align="right" >'.number_format($monto_sector_13, 2, ',','.').'</td>
                <td style="width: 7.78%;" align="right" >'.number_format($monto_sector_14, 2, ',','.').'</td>
                <td style="width: 7.78%;" align="right" >'.number_format($monto_sector_15, 2, ',','.').'</td>
                <td style="width: 7.78%;" align="right" >'.number_format($total_sector, 2, ',','.').'</td>    
                </tr>';

$htmlT.='
</thead>
</table>';
                
        
        $this->SetFont('', '', 10);
        $this->writeHTML($html3, true, false, false, false, '');    
        $this->Ln(-3);
        $this->writeHTML($html31, true, false, false, false, '');
        $this->Ln(-6);
        $this->writeHTML($html32, true, false, false, false, '');
        $this->Ln(-4);
        $this->writeHTML($htmlT, true, false, false, false, '');        
               



    }

     function getMontoPartidaSector($comunes,$partida,$id_sector )
    {
         
         
         $cant = strlen($partida);
         
               $sql_partida_sector = " SELECT sum(t54.monto) as monto
   FROM t54_ac_ae_partidas t54
     inner join mantenimiento.tab_partidas as t02 on SUBSTRING(t54.co_partida, 1,$cant)=t02.co_partida and t54.id_tab_ejercicio_fiscal = t02.id_tab_ejercicio_fiscal
     JOIN t46_acciones_centralizadas t46 ON t46.id = t54.id_accion_centralizada
     where t54.id_tab_ejercicio_fiscal = ".$_SESSION['ejercicio_fiscal']." and SUBSTRING(t54.co_partida, 1,$cant) = '".$partida."' and  id_subsector = $id_sector
     group by t02.co_partida,tx_nombre"; 
               
        $datos_partida_sector = $comunes->ObtenerFilasBySqlSelect($sql_partida_sector);
        
        return $datos_partida_sector[0]["monto"];

    }  
    
     function getMontoSector($comunes,$id_sector )
    {
         
         
               $sql_partida_sector = " SELECT sum(t54.monto) as monto
   FROM t54_ac_ae_partidas t54
     JOIN t46_acciones_centralizadas t46 ON t46.id = t54.id_accion_centralizada
     where t54.id_tab_ejercicio_fiscal = ".$_SESSION['ejercicio_fiscal']."  and  id_subsector = ".$id_sector; 
               
        $datos_partida_sector = $comunes->ObtenerFilasBySqlSelect($sql_partida_sector);
        
        return $datos_partida_sector[0]["monto"];

    }

    function getSector()
    {

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
                                    <td colspan="3"><b>PRESUPUESTO: ' . $datos_ac["nu_anio"] . '</b></td>
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
                        <td align="center">' . $datos_ac["nu_sector"] . '</td>
                        <td>' . $datos_ac["tx_sector"] . '</td>
                    </tr>
                    <tr align="left">
                        <td><b>PROGRAMA: </b></td>
                        <td align="center">' . $datos_ac["nu_original"] . '</td>
                        <td>' . $datos_ac["de_programa"] . '</td>
                    </tr>
                    <tr align="left">
                        <td><b>SUB-PROGRAMA: </b></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr align="left">
                        <td><b>UNIDAD (ES) EJECUTORA (S): </b></td>
                        <td align="center">' . $datos_ac["id_ejecutor"] . '</td>
                        <td>' . $datos_ac["tx_ejecutor"] . '</td>
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

        $this->getMetasPrograma($comunes, $datos_ac["id_accion_centralizada"], $datos_ac['id_accion'], $datos_ac);

        // $portada = $portada + 1;
        //$obj->AddPage();
    }


    function getMetasPrograma($comunes, $id_accion_centralizada, $co_ac_acc_espec, $datos_ac)
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
                                    <td colspan="3"><b>PRESUPUESTO: ' . $datos_ac["nu_anio"] . '</b></td>
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
                        <td align="center">' . $datos_ac["nu_sector"] . '</td>
                        <td>' . $datos_ac["tx_sector"] . '</td>
                    </tr>
                    <tr align="left">
                        <td><b>PROGRAMA: </b></td>
                        <td align="center">' . $datos_ac["nu_original"] . '</td>
                        <td>' . $datos_ac["de_programa"] . '</td>
                    </tr>
                    <tr align="left">
                        <td><b>SUB-PROGRAMA: </b></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr align="left">
                        <td><b>UNIDAD (ES) EJECUTORA (S): </b></td>
                        <td align="center">' . $datos_ac["id_ejecutor"] . '</td>
                        <td>' . $datos_ac["tx_ejecutor"] . '</td>
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
                         <td style="width:300px;text-align: justify;">' . $campo["nb_meta"] . '</td>
                         <td style="width:148px;text-align: justify;">' . $campo["nb_responsable"] . '</td>
                         <td style="width:130px;text-align: center;">' . $campo["tx_prog_anual"] . '</td>
                         <td style="width:130px;text-align: rigth;">' . number_format($campo["monto"]) . '</td>
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
