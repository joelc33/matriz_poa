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

    function getRegistro($id_ejecutor='')
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
        //  pie($this, 'h', 2);
        //$this->Cell(0, 10, 'Pagina '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
    }
    public function setHeader()
    {
        //  encabezado($this, 'h', 1);
    }
    public function cuerpo()
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
     where t54.id_tab_ejercicio_fiscal = ".$_SESSION['ejercicio_fiscal']." and t02.co_partida like '".$campo2['co_partida']."%'
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
     where t54.id_tab_ejercicio_fiscal = ".$_SESSION['ejercicio_fiscal']." and t02.co_partida like '".$campo3['co_partida']."%'
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
$pdf->Output('POA_PG_' . $_SESSION['ejercicio_fiscal'] . '_' . date("H:i:s") . '.pdf', 'D');
