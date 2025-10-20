<?php

namespace matriz\Http\Controllers\Reporte;

//*******agregar esta linea******//
use matriz\Models\Mantenimiento\tab_presupuesto_ingreso;
use matriz\Models\Mantenimiento\tab_partidas;
use matriz\Models\Mantenimiento\tab_objetivo_sectorial;
use matriz\Models\Ac\tab_ac;
use matriz\Models\Ac\tab_ac_ae_partida;
use matriz\Models\Proyecto\tab_proyecto;
use matriz\Models\Proyecto\tab_proyecto_ae_partida;
use matriz\Models\Proyecto\vista_distribucion_presupuesto;
use matriz\Models\Ac\tab_ac_es_partida_desagregado;
use View;
use Input;
use Response;
use DB;
use Auth;
use TCPDF;
use Crypt;
use File;
use Blade;
use Session;
use Helper;
//*******************************//
use Illuminate\Http\Request;

use matriz\Http\Requests;
use matriz\Http\Controllers\Controller;

class distribucionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function libro()
    {

        $ejercicio = Session::get("ejercicio");

        $pdf = new TCPDF('P', PDF_UNIT, 'LETTER', true, 'UTF-8', false);
        $pdf->SetCreator('Sistema POA, Yoser Perez');
        $pdf->SetAuthor('Yoser Perez');
        $pdf->SetTitle('Distribución de Presupuesto');
        $pdf->SetSubject('Distribución de Presupuesto');
        $pdf->SetKeywords('Distribución de Presupuesto, PDF, Zulia, SPE, '.$ejercicio.'');
        $pdf->SetMargins(15, 10, 10);
        $pdf->SetTopMargin(10);
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);
        // set auto page breaks
        $pdf->SetAutoPageBreak(true, 5);
        //$pdf->AddPage();

        $distribucion_uno = vista_distribucion_presupuesto::select('co_sector', 'tx_descripcion')
        ->where('ef_uno', '=', $ejercicio)
        ->where('id_tab_tipo_ejecutor', '=', 1)
        ->groupBy('co_sector')
        ->groupBy('tx_descripcion')
        ->orderBy('co_sector', 'ASC')
        ->get();

        foreach ($distribucion_uno as $key => $value_distribucion_uno) {

            $pdf->AddPage();

            // reset font stretching  reset font spacing
            $pdf->setFontStretching(100);
            $pdf->setFontSpacing(0);
            $pdf->SetLineWidth(0.150);
            $pdf->setCellHeightRatio(2);

            /******Portada Titulo I*********/
            $pdf->SetAlpha(0.3);
            $pdf->Image(public_path().'/images/escudo_zulia.png', 15, 40, 190, 190, 'PNG', '', '', false, 170, '', false, false, 0);
            $pdf->ln(30);
            $pdf->setAlpha(1);
            $pdf->SetFont('', '', 8);

            // reset font stretching  reset font spacing
            $pdf->setFontStretching(100);
            $pdf->setFontSpacing(1);
            //
            $pdf->SetY(15);
            $pdf->SetFont('', 'B', 14);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->MultiCell(190, 5, 'GOBERNACIÓN DEL ESTADO ZULIA', 0, 'C', 0, 0, '', '', true);
            $pdf->ln(230);
            $pdf->SetFont('', 'B', 12);
            //$pdf->MultiCell(190, 5, 'TITULO I', 0, 'R', 0, 0, '', '', true);
            $pdf->writeHTML('<b><u>SECTOR: '.$value_distribucion_uno->co_sector.'<u/></b>', true, false, true, false, 'R');
            $pdf->ln(1);
            $pdf->MultiCell(195, 5, mb_strtoupper($value_distribucion_uno->tx_descripcion, 'UTF-8'), 0, 'R', 0, 0, '', '', true);
            $pdf->ln(10);
            // set border width
            $pdf->SetLineWidth(0.508);
            $pdf->SetDrawColor(0, 0, 0);
            $pdf->SetFillColor(0, 0, 0);
            $pdf->setCellHeightRatio(0);
            $pdf->Cell(195, 0, '', 'B', 1, 'R', 1, '', 0, false, 'T', 'R');
            $pdf->ln(2);
            $pdf->Cell(195, 0, '', 'B', 1, 'R', 1, '', 0, false, 'T', 'R');
            // reset font stretching  reset font spacing
            $pdf->setFontStretching(100);
            $pdf->setFontSpacing(0);
            $pdf->SetLineWidth(0.150);
            $pdf->setCellHeightRatio(2);

            $distribucion_dos = vista_distribucion_presupuesto::select('nu_original', 'de_nombre')
            ->where('ef_uno', '=', $ejercicio)
            ->where('co_sector', '=', $value_distribucion_uno->co_sector)
            ->where('id_tab_tipo_ejecutor', '=', 1)
            ->groupBy('nu_original')
            ->groupBy('de_nombre')
            ->orderBy('nu_original', 'ASC')
            ->get();

            foreach ($distribucion_dos as $key => $value_distribucion_dos) {

                $pdf->AddPage();

                // reset font stretching  reset font spacing
                $pdf->setFontStretching(100);
                $pdf->setFontSpacing(0);
                $pdf->SetLineWidth(0.150);
                $pdf->setCellHeightRatio(2);

                /******Portada Titulo I*********/
                $pdf->SetAlpha(0.3);
                $pdf->Image(public_path().'/images/escudo_zulia.png', 15, 40, 190, 190, 'PNG', '', '', false, 170, '', false, false, 0);
                $pdf->ln(30);
                $pdf->setAlpha(1);
                $pdf->SetFont('', '', 8);

                // reset font stretching  reset font spacing
                $pdf->setFontStretching(100);
                $pdf->setFontSpacing(1);
                //
                $pdf->SetY(15);
                $pdf->SetFont('', 'B', 14);
                $pdf->SetTextColor(0, 0, 0);
                $pdf->MultiCell(190, 5, 'GOBERNACIÓN DEL ESTADO ZULIA', 0, 'C', 0, 0, '', '', true);
                $pdf->ln(210);
                $pdf->SetFont('', 'B', 12);
                //$pdf->MultiCell(190, 5, 'TITULO I', 0, 'R', 0, 0, '', '', true);
                $pdf->writeHTML('<b><u>PROYECTO Y/O ACCIÓN CENTRALIZADA: '.substr($value_distribucion_dos->nu_original, -2).'<u/></b>', true, false, true, false, 'R');
                $pdf->ln(1);
                $pdf->MultiCell(195, 5, mb_strtoupper($value_distribucion_dos->de_nombre, 'UTF-8'), 0, 'R', 0, 0, '', '', true);
                $pdf->ln(30);
                // set border width
                $pdf->SetLineWidth(0.508);
                $pdf->SetDrawColor(0, 0, 0);
                $pdf->SetFillColor(0, 0, 0);
                $pdf->setCellHeightRatio(0);
                $pdf->Cell(195, 0, '', 'B', 1, 'R', 1, '', 0, false, 'T', 'R');
                $pdf->ln(2);
                $pdf->Cell(195, 0, '', 'B', 1, 'R', 1, '', 0, false, 'T', 'R');
                // reset font stretching  reset font spacing
                $pdf->setFontStretching(100);
                $pdf->setFontSpacing(0);
                $pdf->SetLineWidth(0.150);
                $pdf->setCellHeightRatio(2);


                $distribucion_tres = vista_distribucion_presupuesto::select('co_sector', 'tx_descripcion', 'id_ejecutor', 'tx_ejecutor', 'nu_original', 'de_nombre')
                ->where('ef_uno', '=', $ejercicio)
                ->where('co_sector', '=', $value_distribucion_uno->co_sector)
                ->where('nu_original', '=', $value_distribucion_dos->nu_original)
                //->where('id_ejecutor', '=', '0001')
                ->where('id_tab_tipo_ejecutor', '=', 1)
                ->groupBy('co_sector')
                ->groupBy('tx_descripcion')
                ->groupBy('id_ejecutor')
                ->groupBy('tx_ejecutor')
                ->groupBy('nu_original')
                ->groupBy('de_nombre')
                ->orderBy('co_sector', 'ASC')
                ->orderBy('id_ejecutor', 'ASC')
                ->orderBy('nu_original', 'ASC')
                ->get();

                foreach ($distribucion_tres as $key => $value_distribucion_tres) {

                    $pdf->AddPage();

                    $pdf->SetFont('', 'B', 8);
                    $pdf->setCellHeightRatio(1.2);
                    $pdf->MultiCell(30, 5, 'GOBERNACIÓN '.chr(10).'DEL ESTADO ZULIA', 0, 'C', 0, 0, '', '', true);
                    $pdf->MultiCell(25, 5, '', 0, 'C', 0, 0, '', '', true);
                    $pdf->setCellHeightRatio(2);
                    $pdf->SetFont('', 'B', 8);
                    $pdf->setCellHeightRatio(1);
                    $pdf->MultiCell(90, 5, 'CRÉDITOS PRESUPUESTARIOS DEL PROYECTO Y/O ACCIÓN CENTRALIZADA A NIVEL DE PROYECTOS Y/O ACCIÓN CENTRALIZADA', 0, 'C', 0, 0, '', '', true);
                    $pdf->setCellHeightRatio(2);
                    $pdf->ln(8);
                    $pdf->SetFont('', 'B', 8);
                    $pdf->MultiCell(55, 5, 'PRESUPUESTO '.$ejercicio, 0, 'L', 0, 0, '', '', true);
                    $pdf->MultiCell(90, 5, '(EN BOLÍVARES)', 0, 'C', 0, 0, '', '', true);
                    $pdf->ln(-10);
                    $pdf->MultiCell(196, 18, '', 1, 'C', 0, 0, '', '', true);
                    $pdf->ln(19);
                    $pdf->SetFont('', 'B', 7);
                    $pdf->setCellHeightRatio(1.2);

                    $pdf->MultiCell(29, 7, chr(10).'SECTOR', 1, 'L', 0, 0, '', '', true);
                    $pdf->SetFont('', '', 6);
                    $pdf->MultiCell(71, 7, chr(10).$value_distribucion_tres->co_sector.' - '.mb_strtoupper($value_distribucion_tres->tx_descripcion, 'UTF-8'), 1, 'L', 0, 0, '', '', true);
                    $pdf->SetFont('', 'B', 7);
                    $pdf->MultiCell(20, 14, chr(10).'UNIDAD EJECUTORA', 1, 'C', 0, 0, '', '', true);
                    $pdf->SetFont('', '', 6.5);
                    $pdf->MultiCell(76, 14, chr(10).$value_distribucion_tres->id_ejecutor.' - '.mb_strtoupper($value_distribucion_tres->tx_ejecutor, 'UTF-8'), 1, 'L', 0, 0, '', '', true);
                    $pdf->ln(7);
                    $pdf->SetFont('', 'B', 6);
                    $pdf->MultiCell(29, 7, 'PROYECTO Y/O ACCIÓN CENTRALIZADA', 1, 'L', 0, 0, '', '', true);
                    $pdf->SetFont('', '', 6);
                    $pdf->MultiCell(71, 7, substr($value_distribucion_tres->nu_original, -2).' - '.mb_strtoupper($value_distribucion_tres->de_nombre, 'UTF-8'), 1, 'L', 0, 0, '', '', true);
                    $pdf->ln(7);
                    $pdf->MultiCell(196, 226, '', 1, 'C', 0, 0, '', '', true);
                    $pdf->ln(0);
                    $pdf->SetFont('', 'B', 6);
                    $pdf->MultiCell(6, 5, '', 0, 'L', 0, 0, '', '', true);
                    $pdf->MultiCell(23, 5, 'SUB - PARTIDA', 1, 'L', 0, 0, '', '', true);
                    $pdf->ln(20);
                    $pdf->SetFont('', 'B', 6);
                    $pdf->StartTransform();
                    $pdf->Rotate(90);
                    $pdf->MultiCell(20, 6, 'PARTIDA', 1, 'L', 0, 0, '', '', true);
                    $pdf->ln(6);
                    $pdf->MultiCell(15, 5, 'GENERICA', 1, 'L', 0, 0, '', '', true);
                    $pdf->MultiCell(5, 5, '', 0, 'L', 0, 0, '', '', true);
                    $pdf->ln(5);
                    $pdf->MultiCell(15, 5, 'ESPECIFICA', 1, 'L', 0, 0, '', '', true);
                    $pdf->MultiCell(5, 5, '', 0, 'L', 0, 0, '', '', true);
                    $pdf->ln(5);
                    $pdf->MultiCell(15, 6, 'SUB ESPECIFICA', 1, 'L', 0, 0, '', '', true);
                    $pdf->MultiCell(5, 5, '', 0, 'L', 0, 0, '', '', true);
                    $pdf->ln(6);
                    $pdf->MultiCell(15, 7, 'SUB SUB ESPECIFICA', 1, 'L', 0, 0, '', '', true);
                    $pdf->MultiCell(5, 5, '', 0, 'L', 0, 0, '', '', true);
                    $pdf->ln(40);
                    $pdf->StopTransform();
                    $pdf->ln(-82);
                    $pdf->SetFont('', 'B', 8);
                    $pdf->setCellHeightRatio(1.2);
                    $pdf->MultiCell(29, 20, '', 0, 'C', 0, 0, '', '', true);
                    $pdf->MultiCell(71, 20, chr(10).chr(10).'DENOMINACIÓN', 1, 'C', 0, 0, '', '', true);
                    $pdf->SetFont('', 'B', 6);
                    $pdf->setCellHeightRatio(1.2);
                    $pdf->MultiCell(20, 20, chr(10).chr(10).'TOTAL PROYECTO Y/O ACCIÓN CENTRALIZADA', 1, 'C', 0, 0, '', '', true);
                    $pdf->MultiCell(80, 5, 'ACCIÓNES ESPECIFICAS', 0, 'C', 0, 0, '', '', true);
                    $pdf->ln(0);
                    $pdf->MultiCell(100, 20, '', 0, 'C', 0, 0, '', '', true);
                    $pdf->ln(5);
                    $pdf->MultiCell(120, 20, '', 0, 'C', 0, 0, '', '', true);

                    $distribucion_ae = vista_distribucion_presupuesto::select('nu_ae', 'de_ae')
                    ->where('ef_uno', '=', $ejercicio)
                    ->where('co_sector', '=', $value_distribucion_uno->co_sector)
                    ->where('nu_original', '=', $value_distribucion_dos->nu_original)
                    ->where('id_ejecutor', '=', $value_distribucion_tres->id_ejecutor)
                    ->where('id_tab_tipo_ejecutor', '=', 1)
                    ->groupBy('nu_ae')
                    ->groupBy('de_ae')
                    ->orderBy('nu_ae', 'ASC')
                    ->get();

                    $a = 4;
                    $b = 0;
                    foreach($distribucion_ae as $key => $value_distribucion_ae) {
                        $b++;
                        $pdf->SetFont('', 'B', 4);
                        $pdf->MultiCell(19, 15, substr($value_distribucion_ae->nu_ae, -2).' - '.mb_strtoupper($value_distribucion_ae->de_ae, 'UTF-8'), 1, 'L', 0, 0, '', '', true);
                        $pdf->SetFont('', 'B', 6);
                        $c = $a - $b;
                    }
                    for ($n=0; $n<$c; $n++) {
                        $pdf->MultiCell(19, 15, '', 1, 'C', 0, 0, '', '', true);
                    }

                    $pdf->ln(15);
                    $pdf->setCellHeightRatio(1);
                    $pdf->MultiCell(6, 206, '', 1, 'C', 0, 0, '', '', true);
                    $pdf->MultiCell(5, 206, '', 1, 'C', 0, 0, '', '', true);
                    $pdf->MultiCell(5, 206, '', 1, 'C', 0, 0, '', '', true);
                    $pdf->MultiCell(6, 206, '', 1, 'C', 0, 0, '', '', true);
                    $pdf->MultiCell(7, 206, '', 1, 'C', 0, 0, '', '', true);
                    $pdf->MultiCell(71, 206, '', 1, 'C', 0, 0, '', '', true);
                    $pdf->MultiCell(20, 206, '', 1, 'C', 0, 0, '', '', true);
                    $pdf->MultiCell(19, 206, '', 1, 'C', 0, 0, '', '', true);
                    $pdf->MultiCell(19, 206, '', 1, 'C', 0, 0, '', '', true);
                    $pdf->MultiCell(19, 206, '', 1, 'C', 0, 0, '', '', true);
                    $pdf->MultiCell(19, 206, '', 1, 'C', 0, 0, '', '', true);
                    $pdf->ln(208);
                    $pdf->SetFont('', '', 7);
                    $pdf->MultiCell(29, 5, 'Pag. '.$pdf->getAliasNumPage().' de '.$pdf->getAliasNbPages(), 0, 'L', 0, 0, '', '', true);
                    $pdf->MultiCell(151, 5, '', 0, 'C', 0, 0, '', '', true);
                    $pdf->MultiCell(16, 5, 'GEZ: '.$ejercicio, 0, 'L', 0, 0, '', '', true);
                    $pdf->ln(-208);
                    $pdf->ln(2);
                    $pdf->SetFont('', '', 7);
                    $pdf->setCellHeightRatio(1);

                    $distribucion_cuatro = vista_distribucion_presupuesto::join('mantenimiento.tab_partidas as t01', 't01.co_partida', '=', DB::raw('left(public.vista_distribucion_presupuesto.co_partida, 3)'))
                    ->select('t01.co_partida', 'tx_nombre', DB::raw('sum(monto) as mo_partida'))
                    ->where('ef_uno', '=', $ejercicio)
                    ->where('t01.id_tab_ejercicio_fiscal', '=', $ejercicio)
                    ->where('co_sector', '=', $value_distribucion_uno->co_sector)
                    ->where('nu_original', '=', $value_distribucion_dos->nu_original)
                    //->where('id_ejecutor', '=', $value_distribucion_tres->id_ejecutor)
                    ->where('id_tab_tipo_ejecutor', '=', 1)
                    ->groupBy('t01.co_partida')
                    ->groupBy('tx_nombre')
                    ->orderBy('co_partida', 'ASC')
                    ->get();

                    $movimiento = 0;

                    foreach ($distribucion_cuatro as $key => $value_distribucion_cuatro) {


                        $total_distribucion_cuatro = vista_distribucion_presupuesto::join('mantenimiento.tab_partidas as t01', 't01.co_partida', '=', DB::raw('left(public.vista_distribucion_presupuesto.co_partida, 3)'))
                        ->select(DB::raw('sum(monto) as mo_partida'))
                        ->where('ef_uno', '=', $ejercicio)
                        ->where('t01.id_tab_ejercicio_fiscal', '=', $ejercicio)
                        ->where('co_sector', '=', $value_distribucion_uno->co_sector)
                        ->where('nu_original', '=', $value_distribucion_dos->nu_original)
                        ->where('id_tab_tipo_ejecutor', '=', 1)
                        ->where(DB::raw('left(public.vista_distribucion_presupuesto.co_partida::bigint::text::varchar, 3)'), '=', trim($value_distribucion_cuatro->co_partida))
                        ->first();


                        $pdf->SetFont('', '', 5);
                        //$pdf->MultiCell(7, 5, $value_distribucion_cuatro->co_partida, 0, 'L', 0, 0, '', '', true);
                        $pdf->writeHTMLCell(6, 9, '', '', '<u><b>'.$value_distribucion_cuatro->co_partida.'</b></u>', 0, 0, 0, true, 'L', true);
                        $pdf->MultiCell(5, 9, '', 0, 'C', 0, 0, '', '', true);
                        $pdf->MultiCell(5, 9, '', 0, 'C', 0, 0, '', '', true);
                        $pdf->MultiCell(6, 9, '', 0, 'C', 0, 0, '', '', true);
                        $pdf->MultiCell(7, 9, '', 0, 'C', 0, 0, '', '', true);
                        //$pdf->MultiCell(83, 5, $value_distribucion_cuatro->tx_nombre, 0, 'L', 0, 0, '', '', true);
                        $pdf->writeHTMLCell(71, 9, '', '', '<u><b>'.$value_distribucion_cuatro->tx_nombre.'</b></u>', 0, 0, 0, true, 'L', true);
                        $pdf->SetFont('', 'B', 5);
                        //$pdf->MultiCell(20, 5, number_format($value_distribucion_cuatro->mo_partida, 0, ',', '.'), 0, 'R', 0, 0, '', '', true);
                        $pdf->writeHTMLCell(21, 9, '', '', '<u><b>'.number_format($total_distribucion_cuatro->mo_partida, 0, ',', '.').'</b></u>', 0, 0, 0, true, 'R', true);
                        $pdf->SetFont('', '', 5);
                        //$pdf->writeHTMLCell(20,5, '', '', '<u><b>'.number_format($value_distribucion_cuatro->mo_partida, 2, ',', '.').'</b></u>', 0, 0, 0, true, 'R', true);

                        $distribucion_ae = vista_distribucion_presupuesto::select('nu_ae', 'de_ae')
                        ->where('ef_uno', '=', $ejercicio)
                        ->where('co_sector', '=', $value_distribucion_uno->co_sector)
                        ->where('nu_original', '=', $value_distribucion_dos->nu_original)
                        ->where('id_ejecutor', '=', $value_distribucion_tres->id_ejecutor)
                        ->where('id_tab_tipo_ejecutor', '=', 1)
                        ->groupBy('nu_ae')
                        ->groupBy('de_ae')
                        ->orderBy('nu_ae', 'ASC')
                        ->get();

                        foreach($distribucion_ae as $key => $value_distribucion_ae) {

                            $distribucion_ae_partida = vista_distribucion_presupuesto::join('mantenimiento.tab_partidas as t01', 't01.co_partida', '=', DB::raw('left(public.vista_distribucion_presupuesto.co_partida, 3)'))
                            ->select('nu_ae', DB::raw('sum(monto) as mo_partida'))
                            ->where('ef_uno', '=', $ejercicio)
                            ->where('t01.id_tab_ejercicio_fiscal', '=', $ejercicio)
                            ->where('co_sector', '=', $value_distribucion_uno->co_sector)
                            ->where('nu_original', '=', $value_distribucion_dos->nu_original)
                            ->where('id_ejecutor', '=', $value_distribucion_tres->id_ejecutor)
                            ->where('nu_ae', '=', $value_distribucion_ae->nu_ae)
                            ->where('t01.co_partida', '=', $value_distribucion_cuatro->co_partida)
                            ->where('id_tab_tipo_ejecutor', '=', 1)
                            ->groupBy('nu_ae')
                            ->orderBy('nu_ae', 'ASC')
                            ->get();

                            $i = 0;
                            foreach($distribucion_ae_partida as $key => $value_distribucion_ae_partida) {
                                $i++;
                                $pdf->SetFont('', 'B', 5);
                                //$pdf->MultiCell(19, 5, number_format($value_distribucion_ae_partida->mo_partida, 0, ',', '.'), 0, 'R', 0, 0, '', '', true);
                                $pdf->writeHTMLCell(19, 5, '', '', '<u><b>'.number_format($value_distribucion_ae_partida->mo_partida, 0, ',', '.').'</b></u>', 0, 0, 0, true, 'R', true);
                                $pdf->SetFont('', 'B', 5);
                            }
                            if ($i == 0) {
                                $pdf->MultiCell(19, 5, '', 0, 'C', 0, 0, '', '', true);
                            }
                        }

                        /*$pdf->MultiCell(16, 5, '', 0, 'C', 0, 0, '', '', true);
                        $pdf->MultiCell(16, 5, '', 0, 'C', 0, 0, '', '', true);
                        $pdf->MultiCell(16, 5, '', 0, 'C', 0, 0, '', '', true);
                        $pdf->MultiCell(16, 5, '', 0, 'C', 0, 0, '', '', true);*/
                        //$pdf->ln(4);

                        $movimiento = $movimiento + $total_distribucion_cuatro->mo_partida;

                        $condicionPartida = strlen($value_distribucion_cuatro->tx_nombre);
                        if ($condicionPartida >= 60) {
                            $pdf->ln(4);
                        } else {
                            $pdf->ln(2);
                        }
                        if ($condicionPartida >= 120) {
                            $pdf->ln(2);
                        }

                        $start_y = $pdf->GetY();

                        if ($start_y >= 260) {

                            // reset font stretching  reset font spacing
                            $pdf->setFontStretching(100);
                            $pdf->setFontSpacing(0);
                            $pdf->SetLineWidth(0.150);
                            $pdf->setCellHeightRatio(2);

                            $pdf->AddPage();

                            $pdf->SetFont('', 'B', 8);
                            $pdf->setCellHeightRatio(1.2);
                            $pdf->MultiCell(30, 5, 'GOBERNACIÓN '.chr(10).'DEL ESTADO ZULIA', 0, 'C', 0, 0, '', '', true);
                            $pdf->MultiCell(25, 5, '', 0, 'C', 0, 0, '', '', true);
                            $pdf->setCellHeightRatio(2);
                            $pdf->SetFont('', 'B', 8);
                            $pdf->setCellHeightRatio(1);
                            $pdf->MultiCell(90, 5, 'CRÉDITOS PRESUPUESTARIOS DEL PROYECTO Y/O ACCIÓN CENTRALIZADA A NIVEL DE PROYECTOS Y/O ACCIÓN CENTRALIZADA', 0, 'C', 0, 0, '', '', true);
                            $pdf->setCellHeightRatio(2);
                            $pdf->ln(8);
                            $pdf->SetFont('', 'B', 8);
                            $pdf->MultiCell(55, 5, 'PRESUPUESTO '.$ejercicio, 0, 'L', 0, 0, '', '', true);
                            $pdf->MultiCell(90, 5, '(EN BOLÍVARES)', 0, 'C', 0, 0, '', '', true);
                            $pdf->ln(-10);
                            $pdf->MultiCell(196, 18, '', 1, 'C', 0, 0, '', '', true);
                            $pdf->ln(19);
                            $pdf->SetFont('', 'B', 7);
                            $pdf->setCellHeightRatio(1.2);

                            $pdf->MultiCell(29, 7, chr(10).'SECTOR', 1, 'L', 0, 0, '', '', true);
                            $pdf->SetFont('', '', 6);
                            $pdf->MultiCell(71, 7, chr(10).$value_distribucion_tres->co_sector.' - '.mb_strtoupper($value_distribucion_tres->tx_descripcion, 'UTF-8'), 1, 'L', 0, 0, '', '', true);
                            $pdf->SetFont('', 'B', 7);
                            $pdf->MultiCell(20, 14, chr(10).'UNIDAD EJECUTORA', 1, 'C', 0, 0, '', '', true);
                            $pdf->SetFont('', '', 7);
                            $pdf->MultiCell(76, 14, chr(10).$value_distribucion_tres->id_ejecutor.' - '.mb_strtoupper($value_distribucion_tres->tx_ejecutor, 'UTF-8'), 1, 'L', 0, 0, '', '', true);
                            $pdf->ln(7);
                            $pdf->SetFont('', 'B', 6);
                            $pdf->MultiCell(29, 7, 'PROYECTO Y/O ACCIÓN CENTRALIZADA', 1, 'L', 0, 0, '', '', true);
                            $pdf->SetFont('', '', 6);
                            $pdf->MultiCell(71, 7, substr($value_distribucion_tres->nu_original, -2).' - '.mb_strtoupper($value_distribucion_tres->de_nombre, 'UTF-8'), 1, 'L', 0, 0, '', '', true);
                            $pdf->ln(7);
                            $pdf->MultiCell(196, 226, '', 1, 'C', 0, 0, '', '', true);
                            $pdf->ln(0);
                            $pdf->SetFont('', 'B', 6);
                            $pdf->MultiCell(6, 5, '', 0, 'L', 0, 0, '', '', true);
                            $pdf->MultiCell(23, 5, 'SUB - PARTIDA', 1, 'L', 0, 0, '', '', true);
                            $pdf->ln(20);
                            $pdf->SetFont('', 'B', 6);
                            $pdf->StartTransform();
                            $pdf->Rotate(90);
                            $pdf->MultiCell(20, 6, 'PARTIDA', 1, 'L', 0, 0, '', '', true);
                            $pdf->ln(6);
                            $pdf->MultiCell(15, 5, 'GENERICA', 1, 'L', 0, 0, '', '', true);
                            $pdf->MultiCell(5, 5, '', 0, 'L', 0, 0, '', '', true);
                            $pdf->ln(5);
                            $pdf->MultiCell(15, 5, 'ESPECIFICA', 1, 'L', 0, 0, '', '', true);
                            $pdf->MultiCell(5, 5, '', 0, 'L', 0, 0, '', '', true);
                            $pdf->ln(5);
                            $pdf->MultiCell(15, 6, 'SUB ESPECIFICA', 1, 'L', 0, 0, '', '', true);
                            $pdf->MultiCell(5, 5, '', 0, 'L', 0, 0, '', '', true);
                            $pdf->ln(6);
                            $pdf->MultiCell(15, 7, 'SUB SUB ESPECIFICA', 1, 'L', 0, 0, '', '', true);
                            $pdf->MultiCell(5, 5, '', 0, 'L', 0, 0, '', '', true);
                            $pdf->ln(40);
                            $pdf->StopTransform();
                            $pdf->ln(-82);
                            $pdf->SetFont('', 'B', 8);
                            $pdf->setCellHeightRatio(1.2);
                            $pdf->MultiCell(29, 20, '', 0, 'C', 0, 0, '', '', true);
                            $pdf->MultiCell(71, 20, chr(10).chr(10).'DENOMINACIÓN', 1, 'C', 0, 0, '', '', true);
                            $pdf->SetFont('', 'B', 6);
                            $pdf->setCellHeightRatio(1.2);
                            $pdf->MultiCell(20, 20, chr(10).chr(10).'TOTAL PROYECTO Y/O ACCIÓN CENTRALIZADA', 1, 'C', 0, 0, '', '', true);
                            $pdf->MultiCell(80, 5, 'ACCIÓNES ESPECIFICAS', 0, 'C', 0, 0, '', '', true);
                            $pdf->ln(0);
                            $pdf->MultiCell(100, 20, '', 0, 'C', 0, 0, '', '', true);
                            $pdf->ln(5);
                            $pdf->MultiCell(120, 20, '', 0, 'C', 0, 0, '', '', true);

                            $distribucion_ae = vista_distribucion_presupuesto::select('nu_ae', 'de_ae')
                            ->where('ef_uno', '=', $ejercicio)
                            ->where('co_sector', '=', $value_distribucion_uno->co_sector)
                            ->where('nu_original', '=', $value_distribucion_dos->nu_original)
                            ->where('id_ejecutor', '=', $value_distribucion_tres->id_ejecutor)
                            ->where('id_tab_tipo_ejecutor', '=', 1)
                            ->groupBy('nu_ae')
                            ->groupBy('de_ae')
                            ->orderBy('nu_ae', 'ASC')
                            ->get();

                            $a = 4;
                            $b = 0;
                            foreach($distribucion_ae as $key => $value_distribucion_ae) {
                                $b++;
                                $pdf->SetFont('', 'B', 4);
                                $pdf->MultiCell(19, 15, substr($value_distribucion_ae->nu_ae, -2).' - '.mb_strtoupper($value_distribucion_ae->de_ae, 'UTF-8'), 1, 'L', 0, 0, '', '', true);
                                $pdf->SetFont('', 'B', 5);
                                $c = $a - $b;
                            }
                            for ($n=0; $n<$c; $n++) {
                                $pdf->MultiCell(19, 15, '', 1, 'C', 0, 0, '', '', true);
                            }

                            $pdf->ln(15);
                            $pdf->setCellHeightRatio(1);
                            $pdf->MultiCell(6, 206, '', 1, 'C', 0, 0, '', '', true);
                            $pdf->MultiCell(5, 206, '', 1, 'C', 0, 0, '', '', true);
                            $pdf->MultiCell(5, 206, '', 1, 'C', 0, 0, '', '', true);
                            $pdf->MultiCell(6, 206, '', 1, 'C', 0, 0, '', '', true);
                            $pdf->MultiCell(7, 206, '', 1, 'C', 0, 0, '', '', true);
                            $pdf->MultiCell(71, 206, '', 1, 'C', 0, 0, '', '', true);
                            $pdf->MultiCell(20, 206, '', 1, 'C', 0, 0, '', '', true);
                            $pdf->MultiCell(19, 206, '', 1, 'C', 0, 0, '', '', true);
                            $pdf->MultiCell(19, 206, '', 1, 'C', 0, 0, '', '', true);
                            $pdf->MultiCell(19, 206, '', 1, 'C', 0, 0, '', '', true);
                            $pdf->MultiCell(19, 206, '', 1, 'C', 0, 0, '', '', true);
                            $pdf->ln(208);
                            $pdf->SetFont('', '', 7);
                            $pdf->MultiCell(29, 5, 'Pag. '.$pdf->getAliasNumPage().' de '.$pdf->getAliasNbPages(), 0, 'L', 0, 0, '', '', true);
                            $pdf->MultiCell(151, 5, '', 0, 'C', 0, 0, '', '', true);
                            $pdf->MultiCell(16, 5, 'GEZ: '.$ejercicio, 0, 'L', 0, 0, '', '', true);
                            $pdf->ln(-208);
                            $pdf->ln(2);
                            $pdf->SetFont('', '', 7);
                            $pdf->setCellHeightRatio(1);

                        }

                        $distribucion_cinco = vista_distribucion_presupuesto::join('mantenimiento.tab_partidas as t01', 't01.co_partida', '=', DB::raw('left(public.vista_distribucion_presupuesto.co_partida, 3)'))
                        ->join('mantenimiento.tab_partidas as t02', 't02.co_partida', '=', DB::raw('left(public.vista_distribucion_presupuesto.co_partida, 5)'))
                        ->select('t02.co_partida', 't02.tx_nombre', DB::raw('sum(monto) as mo_partida'))
                        ->where('ef_uno', '=', $ejercicio)
                        ->where('t01.id_tab_ejercicio_fiscal', '=', $ejercicio)
                        ->where('t02.id_tab_ejercicio_fiscal', '=', $ejercicio)
                        ->where('co_sector', '=', $value_distribucion_uno->co_sector)
                        ->where('nu_original', '=', $value_distribucion_dos->nu_original)
                        ->where('id_ejecutor', '=', $value_distribucion_tres->id_ejecutor)
                        ->where('t01.co_partida', '=', $value_distribucion_cuatro->co_partida)
                        ->where('id_tab_tipo_ejecutor', '=', 1)
                        ->groupBy('t02.co_partida')
                        ->groupBy('t02.tx_nombre')
                        ->orderBy('co_partida', 'ASC')
                        ->get();

                        foreach ($distribucion_cinco as $key => $value_distribucion_cinco) {

                            $total_distribucion_cinco = vista_distribucion_presupuesto::join('mantenimiento.tab_partidas as t01', 't01.co_partida', '=', DB::raw('left(public.vista_distribucion_presupuesto.co_partida, 3)'))
                              ->join('mantenimiento.tab_partidas as t02', 't02.co_partida', '=', DB::raw('left(public.vista_distribucion_presupuesto.co_partida, 5)'))
                              ->select(DB::raw('sum(monto) as mo_partida'))
                              ->where('ef_uno', '=', $ejercicio)
                              ->where('t01.id_tab_ejercicio_fiscal', '=', $ejercicio)
                              ->where('t02.id_tab_ejercicio_fiscal', '=', $ejercicio)
                              ->where('co_sector', '=', $value_distribucion_uno->co_sector)
                              ->where('nu_original', '=', $value_distribucion_dos->nu_original)
                              ->where('t01.co_partida', '=', $value_distribucion_cuatro->co_partida)
                              ->where('id_tab_tipo_ejecutor', '=', 1)
                            ->where(DB::raw('left(public.vista_distribucion_presupuesto.co_partida::bigint::text::varchar, 5)'), '=', trim($value_distribucion_cinco->co_partida))
                            ->first();


                            $pdf->SetFont('', '', 5);
                            //$pdf->MultiCell(6, 5, substr(trim($value_distribucion_cinco->co_partida), 0, 3), 0, 'L', 0, 0, '', '', true);
                            $pdf->writeHTMLCell(6, 9, '', '', '<u>'.substr(trim($value_distribucion_cinco->co_partida), 0, 3).'</u>', 0, 0, 0, true, 'L', true);
                            //$pdf->MultiCell(5, 5, substr(substr(trim($value_distribucion_cinco->co_partida), 0, 5), 3), 0, 'L', 0, 0, '', '', true);
                            $pdf->writeHTMLCell(5, 9, '', '', '<u>'.substr(substr(trim($value_distribucion_cinco->co_partida), 0, 5), 3).'</u>', 0, 0, 0, true, 'L', true);
                            $pdf->MultiCell(5, 9, '', 0, 'C', 0, 0, '', '', true);
                            $pdf->MultiCell(6, 9, '', 0, 'C', 0, 0, '', '', true);
                            $pdf->MultiCell(7, 9, '', 0, 'C', 0, 0, '', '', true);
                            //$pdf->MultiCell(71, 5, $value_distribucion_cinco->tx_nombre, 0, 'L', 0, 0, '', '', true);
                            $pdf->writeHTMLCell(71, 9, '', '', '<u>'.$value_distribucion_cinco->tx_nombre.'</u>', 0, 0, 0, true, 'L', true);
                            //$pdf->MultiCell(20, 5, number_format($value_distribucion_cinco->mo_partida, 0, ',', '.'), 0, 'R', 0, 0, '', '', true);
                            $pdf->writeHTMLCell(21, 9, '', '', '<u>'.number_format($total_distribucion_cinco->mo_partida, 0, ',', '.').'</u>', 0, 0, 0, true, 'R', true);

                            $distribucion_ae = vista_distribucion_presupuesto::select('nu_ae', 'de_ae')
                            ->where('ef_uno', '=', $ejercicio)
                            ->where('co_sector', '=', $value_distribucion_uno->co_sector)
                            ->where('nu_original', '=', $value_distribucion_dos->nu_original)
                            ->where('id_ejecutor', '=', $value_distribucion_tres->id_ejecutor)
                            ->where('id_tab_tipo_ejecutor', '=', 1)
                            ->groupBy('nu_ae')
                            ->groupBy('de_ae')
                            ->orderBy('nu_ae', 'ASC')
                            ->get();

                            foreach($distribucion_ae as $key => $value_distribucion_ae) {

                                $distribucion_ae_partida = vista_distribucion_presupuesto::join('mantenimiento.tab_partidas as t01', 't01.co_partida', '=', DB::raw('left(public.vista_distribucion_presupuesto.co_partida, 3)'))
                                ->join('mantenimiento.tab_partidas as t02', 't02.co_partida', '=', DB::raw('left(public.vista_distribucion_presupuesto.co_partida, 5)'))
                                ->select('nu_ae', DB::raw('sum(monto) as mo_partida'))
                                ->where('ef_uno', '=', $ejercicio)
                                ->where('t01.id_tab_ejercicio_fiscal', '=', $ejercicio)
                                ->where('t02.id_tab_ejercicio_fiscal', '=', $ejercicio)
                                ->where('co_sector', '=', $value_distribucion_uno->co_sector)
                                ->where('nu_original', '=', $value_distribucion_dos->nu_original)
                                ->where('id_ejecutor', '=', $value_distribucion_tres->id_ejecutor)
                                ->where('nu_ae', '=', $value_distribucion_ae->nu_ae)
                                ->where('t01.co_partida', '=', $value_distribucion_cuatro->co_partida)
                                ->where('t02.co_partida', '=', $value_distribucion_cinco->co_partida)
                                ->where('id_tab_tipo_ejecutor', '=', 1)
                                ->groupBy('nu_ae')
                                ->orderBy('nu_ae', 'ASC')
                                ->get();

                                $i = 0;
                                foreach($distribucion_ae_partida as $key => $value_distribucion_ae_partida) {
                                    $i++;
                                    $pdf->SetFont('', '', 5);
                                    //$pdf->MultiCell(19, 5, number_format($value_distribucion_ae_partida->mo_partida, 0, ',', '.'), 0, 'R', 0, 0, '', '', true);
                                    $pdf->writeHTMLCell(19, 5, '', '', '<u>'.number_format($value_distribucion_ae_partida->mo_partida, 0, ',', '.').'</u>', 0, 0, 0, true, 'R', true);
                                    $pdf->SetFont('', 'B', 5);
                                }
                                if ($i == 0) {
                                    $pdf->MultiCell(19, 5, '', 0, 'C', 0, 0, '', '', true);
                                }
                            }

                            /*$pdf->MultiCell(16, 5, '', 0, 'C', 0, 0, '', '', true);
                            $pdf->MultiCell(16, 5, '', 0, 'C', 0, 0, '', '', true);
                            $pdf->MultiCell(16, 5, '', 0, 'C', 0, 0, '', '', true);
                            $pdf->MultiCell(16, 5, '', 0, 'C', 0, 0, '', '', true);*/
                            //$pdf->ln(4);

                            $condicionPartida = strlen($value_distribucion_cinco->tx_nombre);
                            if ($condicionPartida >= 60) {
                                $pdf->ln(4);
                            } else {
                                $pdf->ln(2);
                            }
                            if ($condicionPartida >= 120) {
                                $pdf->ln(2);
                            }

                            $start_y = $pdf->GetY();

                            if ($start_y >= 260) {

                                // reset font stretching  reset font spacing
                                $pdf->setFontStretching(100);
                                $pdf->setFontSpacing(0);
                                $pdf->SetLineWidth(0.150);
                                $pdf->setCellHeightRatio(2);

                                $pdf->AddPage();

                                $pdf->SetFont('', 'B', 8);
                                $pdf->setCellHeightRatio(1.2);
                                $pdf->MultiCell(30, 5, 'GOBERNACIÓN '.chr(10).'DEL ESTADO ZULIA', 0, 'C', 0, 0, '', '', true);
                                $pdf->MultiCell(25, 5, '', 0, 'C', 0, 0, '', '', true);
                                $pdf->setCellHeightRatio(2);
                                $pdf->SetFont('', 'B', 8);
                                $pdf->setCellHeightRatio(1);
                                $pdf->MultiCell(90, 5, 'CRÉDITOS PRESUPUESTARIOS DEL PROYECTO Y/O ACCIÓN CENTRALIZADA A NIVEL DE PROYECTOS Y/O ACCIÓN CENTRALIZADA', 0, 'C', 0, 0, '', '', true);
                                $pdf->setCellHeightRatio(2);
                                $pdf->ln(8);
                                $pdf->SetFont('', 'B', 8);
                                $pdf->MultiCell(55, 5, 'PRESUPUESTO '.$ejercicio, 0, 'L', 0, 0, '', '', true);
                                $pdf->MultiCell(90, 5, '(EN BOLÍVARES)', 0, 'C', 0, 0, '', '', true);
                                $pdf->ln(-10);
                                $pdf->MultiCell(196, 18, '', 1, 'C', 0, 0, '', '', true);
                                $pdf->ln(19);
                                $pdf->SetFont('', 'B', 7);
                                $pdf->setCellHeightRatio(1.2);

                                $pdf->MultiCell(29, 7, chr(10).'SECTOR', 1, 'L', 0, 0, '', '', true);
                                $pdf->SetFont('', '', 6);
                                $pdf->MultiCell(71, 7, chr(10).$value_distribucion_tres->co_sector.' - '.mb_strtoupper($value_distribucion_tres->tx_descripcion, 'UTF-8'), 1, 'L', 0, 0, '', '', true);
                                $pdf->SetFont('', 'B', 7);
                                $pdf->MultiCell(20, 14, chr(10).'UNIDAD EJECUTORA', 1, 'C', 0, 0, '', '', true);
                                $pdf->SetFont('', '', 7);
                                $pdf->MultiCell(76, 14, chr(10).$value_distribucion_tres->id_ejecutor.' - '.mb_strtoupper($value_distribucion_tres->tx_ejecutor, 'UTF-8'), 1, 'L', 0, 0, '', '', true);
                                $pdf->ln(7);
                                $pdf->SetFont('', 'B', 6);
                                $pdf->MultiCell(29, 7, 'PROYECTO Y/O ACCIÓN CENTRALIZADA', 1, 'L', 0, 0, '', '', true);
                                $pdf->SetFont('', '', 6);
                                $pdf->MultiCell(71, 7, substr($value_distribucion_tres->nu_original, -2).' - '.mb_strtoupper($value_distribucion_tres->de_nombre, 'UTF-8'), 1, 'L', 0, 0, '', '', true);
                                $pdf->ln(7);
                                $pdf->MultiCell(196, 226, '', 1, 'C', 0, 0, '', '', true);
                                $pdf->ln(0);
                                $pdf->SetFont('', 'B', 6);
                                $pdf->MultiCell(6, 5, '', 0, 'L', 0, 0, '', '', true);
                                $pdf->MultiCell(23, 5, 'SUB - PARTIDA', 1, 'L', 0, 0, '', '', true);
                                $pdf->ln(20);
                                $pdf->SetFont('', 'B', 6);
                                $pdf->StartTransform();
                                $pdf->Rotate(90);
                                $pdf->MultiCell(20, 6, 'PARTIDA', 1, 'L', 0, 0, '', '', true);
                                $pdf->ln(6);
                                $pdf->MultiCell(15, 5, 'GENERICA', 1, 'L', 0, 0, '', '', true);
                                $pdf->MultiCell(5, 5, '', 0, 'L', 0, 0, '', '', true);
                                $pdf->ln(5);
                                $pdf->MultiCell(15, 5, 'ESPECIFICA', 1, 'L', 0, 0, '', '', true);
                                $pdf->MultiCell(5, 5, '', 0, 'L', 0, 0, '', '', true);
                                $pdf->ln(5);
                                $pdf->MultiCell(15, 6, 'SUB ESPECIFICA', 1, 'L', 0, 0, '', '', true);
                                $pdf->MultiCell(5, 5, '', 0, 'L', 0, 0, '', '', true);
                                $pdf->ln(6);
                                $pdf->MultiCell(15, 7, 'SUB SUB ESPECIFICA', 1, 'L', 0, 0, '', '', true);
                                $pdf->MultiCell(5, 5, '', 0, 'L', 0, 0, '', '', true);
                                $pdf->ln(40);
                                $pdf->StopTransform();
                                $pdf->ln(-82);
                                $pdf->SetFont('', 'B', 8);
                                $pdf->setCellHeightRatio(1.2);
                                $pdf->MultiCell(29, 20, '', 0, 'C', 0, 0, '', '', true);
                                $pdf->MultiCell(71, 20, chr(10).chr(10).'DENOMINACIÓN', 1, 'C', 0, 0, '', '', true);
                                $pdf->SetFont('', 'B', 6);
                                $pdf->setCellHeightRatio(1.2);
                                $pdf->MultiCell(20, 20, chr(10).chr(10).'TOTAL PROYECTO Y/O ACCIÓN CENTRALIZADA', 1, 'C', 0, 0, '', '', true);
                                $pdf->MultiCell(80, 5, 'ACCIÓNES ESPECIFICAS', 0, 'C', 0, 0, '', '', true);
                                $pdf->ln(0);
                                $pdf->MultiCell(100, 20, '', 0, 'C', 0, 0, '', '', true);
                                $pdf->ln(5);
                                $pdf->MultiCell(120, 20, '', 0, 'C', 0, 0, '', '', true);

                                $distribucion_ae = vista_distribucion_presupuesto::select('nu_ae', 'de_ae')
                                ->where('ef_uno', '=', $ejercicio)
                                ->where('co_sector', '=', $value_distribucion_uno->co_sector)
                                ->where('nu_original', '=', $value_distribucion_dos->nu_original)
                                ->where('id_ejecutor', '=', $value_distribucion_tres->id_ejecutor)
                                ->where('id_tab_tipo_ejecutor', '=', 1)
                                ->groupBy('nu_ae')
                                ->groupBy('de_ae')
                                ->orderBy('nu_ae', 'ASC')
                                ->get();

                                $a = 4;
                                $b = 0;
                                foreach($distribucion_ae as $key => $value_distribucion_ae) {
                                    $b++;
                                    $pdf->SetFont('', 'B', 4);
                                    $pdf->MultiCell(19, 15, substr($value_distribucion_ae->nu_ae, -2).' - '.mb_strtoupper($value_distribucion_ae->de_ae, 'UTF-8'), 1, 'L', 0, 0, '', '', true);
                                    $pdf->SetFont('', 'B', 5);
                                    $c = $a - $b;
                                }
                                for ($n=0; $n<$c; $n++) {
                                    $pdf->MultiCell(19, 15, '', 1, 'C', 0, 0, '', '', true);
                                }

                                $pdf->ln(15);
                                $pdf->setCellHeightRatio(1);
                                $pdf->MultiCell(6, 206, '', 1, 'C', 0, 0, '', '', true);
                                $pdf->MultiCell(5, 206, '', 1, 'C', 0, 0, '', '', true);
                                $pdf->MultiCell(5, 206, '', 1, 'C', 0, 0, '', '', true);
                                $pdf->MultiCell(6, 206, '', 1, 'C', 0, 0, '', '', true);
                                $pdf->MultiCell(7, 206, '', 1, 'C', 0, 0, '', '', true);
                                $pdf->MultiCell(71, 206, '', 1, 'C', 0, 0, '', '', true);
                                $pdf->MultiCell(20, 206, '', 1, 'C', 0, 0, '', '', true);
                                $pdf->MultiCell(19, 206, '', 1, 'C', 0, 0, '', '', true);
                                $pdf->MultiCell(19, 206, '', 1, 'C', 0, 0, '', '', true);
                                $pdf->MultiCell(19, 206, '', 1, 'C', 0, 0, '', '', true);
                                $pdf->MultiCell(19, 206, '', 1, 'C', 0, 0, '', '', true);
                                $pdf->ln(208);
                                $pdf->SetFont('', '', 7);
                                $pdf->MultiCell(29, 5, 'Pag. '.$pdf->getAliasNumPage().' de '.$pdf->getAliasNbPages(), 0, 'L', 0, 0, '', '', true);
                                $pdf->MultiCell(151, 5, '', 0, 'C', 0, 0, '', '', true);
                                $pdf->MultiCell(16, 5, 'GEZ: '.$ejercicio, 0, 'L', 0, 0, '', '', true);
                                $pdf->ln(-208);
                                $pdf->ln(2);
                                $pdf->SetFont('', '', 7);
                                $pdf->setCellHeightRatio(1);

                            }

                            $distribucion_seis = vista_distribucion_presupuesto::join('mantenimiento.tab_partidas as t01', 't01.co_partida', '=', DB::raw('left(public.vista_distribucion_presupuesto.co_partida, 3)'))
                            ->join('mantenimiento.tab_partidas as t02', 't02.co_partida', '=', DB::raw('left(public.vista_distribucion_presupuesto.co_partida, 5)'))
                            ->join('mantenimiento.tab_partidas as t03', 't03.co_partida', '=', DB::raw('left(public.vista_distribucion_presupuesto.co_partida, 7)'))
                            ->select('t03.co_partida', 't03.tx_nombre', DB::raw('sum(monto) as mo_partida'))
                            ->where('ef_uno', '=', $ejercicio)
                            ->where('t01.id_tab_ejercicio_fiscal', '=', $ejercicio)
                            ->where('t02.id_tab_ejercicio_fiscal', '=', $ejercicio)
                            ->where('t03.id_tab_ejercicio_fiscal', '=', $ejercicio)
                            ->where('co_sector', '=', $value_distribucion_uno->co_sector)
                            ->where('nu_original', '=', $value_distribucion_dos->nu_original)
                            ->where('id_ejecutor', '=', $value_distribucion_tres->id_ejecutor)
                            ->where('t01.co_partida', '=', $value_distribucion_cuatro->co_partida)
                            ->where('t02.co_partida', '=', $value_distribucion_cinco->co_partida)
                            ->where('id_tab_tipo_ejecutor', '=', 1)
                            ->groupBy('t03.co_partida')
                            ->groupBy('t03.tx_nombre')
                            ->orderBy('co_partida', 'ASC')
                            ->get();

                            foreach ($distribucion_seis as $key => $value_distribucion_seis) {

                                $total_distribucion_seis = vista_distribucion_presupuesto::join('mantenimiento.tab_partidas as t01', 't01.co_partida', '=', DB::raw('left(public.vista_distribucion_presupuesto.co_partida, 3)'))
                                    ->join('mantenimiento.tab_partidas as t02', 't02.co_partida', '=', DB::raw('left(public.vista_distribucion_presupuesto.co_partida, 5)'))
                                    ->join('mantenimiento.tab_partidas as t03', 't03.co_partida', '=', DB::raw('left(public.vista_distribucion_presupuesto.co_partida, 7)'))
                                    ->select(DB::raw('sum(monto) as mo_partida'))
                                    ->where('ef_uno', '=', $ejercicio)
                                    ->where('t01.id_tab_ejercicio_fiscal', '=', $ejercicio)
                                    ->where('t02.id_tab_ejercicio_fiscal', '=', $ejercicio)
                                    ->where('t03.id_tab_ejercicio_fiscal', '=', $ejercicio)
                                    ->where('co_sector', '=', $value_distribucion_uno->co_sector)
                                    ->where('nu_original', '=', $value_distribucion_dos->nu_original)
                                    ->where('t01.co_partida', '=', $value_distribucion_cuatro->co_partida)
                                    ->where('t02.co_partida', '=', $value_distribucion_cinco->co_partida)
                                  ->where('id_tab_tipo_ejecutor', '=', 1)
                                ->where(DB::raw('left(public.vista_distribucion_presupuesto.co_partida::bigint::text::varchar, 7)'), '=', trim($value_distribucion_seis->co_partida))
                                ->first();

                                $pdf->SetFont('', '', 5);
                                $pdf->MultiCell(6, 9, substr(trim($value_distribucion_seis->co_partida), 0, 3), 0, 'L', 0, 0, '', '', true);
                                $pdf->MultiCell(5, 9, substr(substr(trim($value_distribucion_seis->co_partida), 0, 5), 3), 0, 'L', 0, 0, '', '', true);
                                $pdf->MultiCell(5, 9, substr(substr(trim($value_distribucion_seis->co_partida), 0, 7), 5), 0, 'L', 0, 0, '', '', true);
                                $pdf->MultiCell(6, 9, '', 0, 'C', 0, 0, '', '', true);
                                $pdf->MultiCell(7, 9, '', 0, 'C', 0, 0, '', '', true);
                                $pdf->MultiCell(71, 9, $value_distribucion_seis->tx_nombre, 0, 'L', 0, 0, '', '', true);
                                $pdf->MultiCell(20, 9, number_format($total_distribucion_seis->mo_partida, 0, ',', '.'), 0, 'R', 0, 0, '', '', true);

                                $distribucion_ae = vista_distribucion_presupuesto::select('nu_ae', 'de_ae')
                                ->where('ef_uno', '=', $ejercicio)
                                ->where('co_sector', '=', $value_distribucion_uno->co_sector)
                                ->where('nu_original', '=', $value_distribucion_dos->nu_original)
                                ->where('id_ejecutor', '=', $value_distribucion_tres->id_ejecutor)
                                ->where('id_tab_tipo_ejecutor', '=', 1)
                                ->groupBy('nu_ae')
                                ->groupBy('de_ae')
                                ->orderBy('nu_ae', 'ASC')
                                ->get();

                                foreach($distribucion_ae as $key => $value_distribucion_ae) {

                                    $distribucion_ae_partida = vista_distribucion_presupuesto::join('mantenimiento.tab_partidas as t01', 't01.co_partida', '=', DB::raw('left(public.vista_distribucion_presupuesto.co_partida, 3)'))
                                    ->join('mantenimiento.tab_partidas as t02', 't02.co_partida', '=', DB::raw('left(public.vista_distribucion_presupuesto.co_partida, 5)'))
                                    ->join('mantenimiento.tab_partidas as t03', 't03.co_partida', '=', DB::raw('left(public.vista_distribucion_presupuesto.co_partida, 7)'))
                                    ->select('nu_ae', DB::raw('sum(monto) as mo_partida'))
                                    ->where('ef_uno', '=', $ejercicio)
                                    ->where('t01.id_tab_ejercicio_fiscal', '=', $ejercicio)
                                    ->where('t02.id_tab_ejercicio_fiscal', '=', $ejercicio)
                                    ->where('t03.id_tab_ejercicio_fiscal', '=', $ejercicio)
                                    ->where('co_sector', '=', $value_distribucion_uno->co_sector)
                                    ->where('nu_original', '=', $value_distribucion_dos->nu_original)
                                    ->where('id_ejecutor', '=', $value_distribucion_tres->id_ejecutor)
                                    ->where('nu_ae', '=', $value_distribucion_ae->nu_ae)
                                    ->where('t01.co_partida', '=', $value_distribucion_cuatro->co_partida)
                                    ->where('t02.co_partida', '=', $value_distribucion_cinco->co_partida)
                                    ->where('t03.co_partida', '=', $value_distribucion_seis->co_partida)
                                    ->where('id_tab_tipo_ejecutor', '=', 1)
                                    ->groupBy('nu_ae')
                                    ->orderBy('nu_ae', 'ASC')
                                    ->get();

                                    $i = 0;
                                    foreach($distribucion_ae_partida as $key => $value_distribucion_ae_partida) {
                                        $i++;
                                        $pdf->SetFont('', '', 5);
                                        $pdf->MultiCell(19, 5, number_format($value_distribucion_ae_partida->mo_partida, 0, ',', '.'), 0, 'R', 0, 0, '', '', true);
                                        $pdf->SetFont('', 'B', 5);
                                    }
                                    if ($i == 0) {
                                        $pdf->MultiCell(19, 5, '', 0, 'C', 0, 0, '', '', true);
                                    }
                                }

                                /*$pdf->MultiCell(16, 5, '', 0, 'C', 0, 0, '', '', true);
                                $pdf->MultiCell(16, 5, '', 0, 'C', 0, 0, '', '', true);
                                $pdf->MultiCell(16, 5, '', 0, 'C', 0, 0, '', '', true);
                                $pdf->MultiCell(16, 5, '', 0, 'C', 0, 0, '', '', true);*/
                                //$pdf->ln(4);

                                $condicionPartida = strlen($value_distribucion_seis->tx_nombre);
                                if ($condicionPartida >= 60) {
                                    $pdf->ln(4);
                                } else {
                                    $pdf->ln(2);
                                }
                                if ($condicionPartida >= 120) {
                                    $pdf->ln(2);
                                }

                                $start_y = $pdf->GetY();

                                if ($start_y >= 260) {

                                    // reset font stretching  reset font spacing
                                    $pdf->setFontStretching(100);
                                    $pdf->setFontSpacing(0);
                                    $pdf->SetLineWidth(0.150);
                                    $pdf->setCellHeightRatio(2);

                                    $pdf->AddPage();

                                    $pdf->SetFont('', 'B', 8);
                                    $pdf->setCellHeightRatio(1.2);
                                    $pdf->MultiCell(30, 5, 'GOBERNACIÓN '.chr(10).'DEL ESTADO ZULIA', 0, 'C', 0, 0, '', '', true);
                                    $pdf->MultiCell(25, 5, '', 0, 'C', 0, 0, '', '', true);
                                    $pdf->setCellHeightRatio(2);
                                    $pdf->SetFont('', 'B', 8);
                                    $pdf->setCellHeightRatio(1);
                                    $pdf->MultiCell(90, 5, 'CRÉDITOS PRESUPUESTARIOS DEL PROYECTO Y/O ACCIÓN CENTRALIZADA A NIVEL DE PROYECTOS Y/O ACCIÓN CENTRALIZADA', 0, 'C', 0, 0, '', '', true);
                                    $pdf->setCellHeightRatio(2);
                                    $pdf->ln(8);
                                    $pdf->SetFont('', 'B', 8);
                                    $pdf->MultiCell(55, 5, 'PRESUPUESTO '.$ejercicio, 0, 'L', 0, 0, '', '', true);
                                    $pdf->MultiCell(90, 5, '(EN BOLÍVARES)', 0, 'C', 0, 0, '', '', true);
                                    $pdf->ln(-10);
                                    $pdf->MultiCell(196, 18, '', 1, 'C', 0, 0, '', '', true);
                                    $pdf->ln(19);
                                    $pdf->SetFont('', 'B', 7);
                                    $pdf->setCellHeightRatio(1.2);

                                    $pdf->MultiCell(29, 7, chr(10).'SECTOR', 1, 'L', 0, 0, '', '', true);
                                    $pdf->SetFont('', '', 6);
                                    $pdf->MultiCell(71, 7, chr(10).$value_distribucion_tres->co_sector.' - '.mb_strtoupper($value_distribucion_tres->tx_descripcion, 'UTF-8'), 1, 'L', 0, 0, '', '', true);
                                    $pdf->SetFont('', 'B', 7);
                                    $pdf->MultiCell(20, 14, chr(10).'UNIDAD EJECUTORA', 1, 'C', 0, 0, '', '', true);
                                    $pdf->SetFont('', '', 7);
                                    $pdf->MultiCell(76, 14, chr(10).$value_distribucion_tres->id_ejecutor.' - '.mb_strtoupper($value_distribucion_tres->tx_ejecutor, 'UTF-8'), 1, 'L', 0, 0, '', '', true);
                                    $pdf->ln(7);
                                    $pdf->SetFont('', 'B', 6);
                                    $pdf->MultiCell(29, 7, 'PROYECTO Y/O ACCIÓN CENTRALIZADA', 1, 'L', 0, 0, '', '', true);
                                    $pdf->SetFont('', '', 6);
                                    $pdf->MultiCell(71, 7, substr($value_distribucion_tres->nu_original, -2).' - '.mb_strtoupper($value_distribucion_tres->de_nombre, 'UTF-8'), 1, 'L', 0, 0, '', '', true);
                                    $pdf->ln(7);
                                    $pdf->MultiCell(196, 226, '', 1, 'C', 0, 0, '', '', true);
                                    $pdf->ln(0);
                                    $pdf->SetFont('', 'B', 6);
                                    $pdf->MultiCell(6, 5, '', 0, 'L', 0, 0, '', '', true);
                                    $pdf->MultiCell(23, 5, 'SUB - PARTIDA', 1, 'L', 0, 0, '', '', true);
                                    $pdf->ln(20);
                                    $pdf->SetFont('', 'B', 6);
                                    $pdf->StartTransform();
                                    $pdf->Rotate(90);
                                    $pdf->MultiCell(20, 6, 'PARTIDA', 1, 'L', 0, 0, '', '', true);
                                    $pdf->ln(6);
                                    $pdf->MultiCell(15, 5, 'GENERICA', 1, 'L', 0, 0, '', '', true);
                                    $pdf->MultiCell(5, 5, '', 0, 'L', 0, 0, '', '', true);
                                    $pdf->ln(5);
                                    $pdf->MultiCell(15, 5, 'ESPECIFICA', 1, 'L', 0, 0, '', '', true);
                                    $pdf->MultiCell(5, 5, '', 0, 'L', 0, 0, '', '', true);
                                    $pdf->ln(5);
                                    $pdf->MultiCell(15, 6, 'SUB ESPECIFICA', 1, 'L', 0, 0, '', '', true);
                                    $pdf->MultiCell(5, 5, '', 0, 'L', 0, 0, '', '', true);
                                    $pdf->ln(6);
                                    $pdf->MultiCell(15, 7, 'SUB SUB ESPECIFICA', 1, 'L', 0, 0, '', '', true);
                                    $pdf->MultiCell(5, 5, '', 0, 'L', 0, 0, '', '', true);
                                    $pdf->ln(40);
                                    $pdf->StopTransform();
                                    $pdf->ln(-82);
                                    $pdf->SetFont('', 'B', 8);
                                    $pdf->setCellHeightRatio(1.2);
                                    $pdf->MultiCell(29, 20, '', 0, 'C', 0, 0, '', '', true);
                                    $pdf->MultiCell(71, 20, chr(10).chr(10).'DENOMINACIÓN', 1, 'C', 0, 0, '', '', true);
                                    $pdf->SetFont('', 'B', 6);
                                    $pdf->setCellHeightRatio(1.2);
                                    $pdf->MultiCell(20, 20, chr(10).chr(10).'TOTAL PROYECTO Y/O ACCIÓN CENTRALIZADA', 1, 'C', 0, 0, '', '', true);
                                    $pdf->MultiCell(80, 5, 'ACCIÓNES ESPECIFICAS', 0, 'C', 0, 0, '', '', true);
                                    $pdf->ln(0);
                                    $pdf->MultiCell(100, 20, '', 0, 'C', 0, 0, '', '', true);
                                    $pdf->ln(5);
                                    $pdf->MultiCell(120, 20, '', 0, 'C', 0, 0, '', '', true);

                                    $distribucion_ae = vista_distribucion_presupuesto::select('nu_ae', 'de_ae')
                                    ->where('ef_uno', '=', $ejercicio)
                                    ->where('co_sector', '=', $value_distribucion_uno->co_sector)
                                    ->where('nu_original', '=', $value_distribucion_dos->nu_original)
                                    ->where('id_ejecutor', '=', $value_distribucion_tres->id_ejecutor)
                                    ->where('id_tab_tipo_ejecutor', '=', 1)
                                    ->groupBy('nu_ae')
                                    ->groupBy('de_ae')
                                    ->orderBy('nu_ae', 'ASC')
                                    ->get();

                                    $a = 4;
                                    $b = 0;
                                    foreach($distribucion_ae as $key => $value_distribucion_ae) {
                                        $b++;
                                        $pdf->SetFont('', 'B', 4);
                                        $pdf->MultiCell(19, 15, substr($value_distribucion_ae->nu_ae, -2).' - '.mb_strtoupper($value_distribucion_ae->de_ae, 'UTF-8'), 1, 'L', 0, 0, '', '', true);
                                        $pdf->SetFont('', 'B', 5);
                                        $c = $a - $b;
                                    }
                                    for ($n=0; $n<$c; $n++) {
                                        $pdf->MultiCell(19, 15, '', 1, 'C', 0, 0, '', '', true);
                                    }

                                    $pdf->ln(15);
                                    $pdf->setCellHeightRatio(1);
                                    $pdf->MultiCell(6, 206, '', 1, 'C', 0, 0, '', '', true);
                                    $pdf->MultiCell(5, 206, '', 1, 'C', 0, 0, '', '', true);
                                    $pdf->MultiCell(5, 206, '', 1, 'C', 0, 0, '', '', true);
                                    $pdf->MultiCell(6, 206, '', 1, 'C', 0, 0, '', '', true);
                                    $pdf->MultiCell(7, 206, '', 1, 'C', 0, 0, '', '', true);
                                    $pdf->MultiCell(71, 206, '', 1, 'C', 0, 0, '', '', true);
                                    $pdf->MultiCell(20, 206, '', 1, 'C', 0, 0, '', '', true);
                                    $pdf->MultiCell(19, 206, '', 1, 'C', 0, 0, '', '', true);
                                    $pdf->MultiCell(19, 206, '', 1, 'C', 0, 0, '', '', true);
                                    $pdf->MultiCell(19, 206, '', 1, 'C', 0, 0, '', '', true);
                                    $pdf->MultiCell(19, 206, '', 1, 'C', 0, 0, '', '', true);
                                    $pdf->ln(208);
                                    $pdf->SetFont('', '', 7);
                                    $pdf->MultiCell(29, 5, 'Pag. '.$pdf->getAliasNumPage().' de '.$pdf->getAliasNbPages(), 0, 'L', 0, 0, '', '', true);
                                    $pdf->MultiCell(151, 5, '', 0, 'C', 0, 0, '', '', true);
                                    $pdf->MultiCell(16, 5, 'GEZ: '.$ejercicio, 0, 'L', 0, 0, '', '', true);
                                    $pdf->ln(-208);
                                    $pdf->ln(2);
                                    $pdf->SetFont('', '', 7);
                                    $pdf->setCellHeightRatio(1);

                                }

                                $distribucion_siete = vista_distribucion_presupuesto::join('mantenimiento.tab_partidas as t01', 't01.co_partida', '=', DB::raw('left(public.vista_distribucion_presupuesto.co_partida, 3)'))
                                ->join('mantenimiento.tab_partidas as t02', 't02.co_partida', '=', DB::raw('left(public.vista_distribucion_presupuesto.co_partida, 5)'))
                                ->join('mantenimiento.tab_partidas as t03', 't03.co_partida', '=', DB::raw('left(public.vista_distribucion_presupuesto.co_partida, 7)'))
                                ->join('mantenimiento.tab_partidas as t04', 't04.co_partida', '=', DB::raw('left(public.vista_distribucion_presupuesto.co_partida, 9)'))
                                ->select('public.vista_distribucion_presupuesto.id_tab_ac', 't04.co_partida', 't04.tx_nombre', DB::raw('sum(monto) as mo_partida'))
                                ->where('ef_uno', '=', $ejercicio)
                                ->where('t01.id_tab_ejercicio_fiscal', '=', $ejercicio)
                                ->where('t02.id_tab_ejercicio_fiscal', '=', $ejercicio)
                                ->where('t03.id_tab_ejercicio_fiscal', '=', $ejercicio)
                                ->where('t04.id_tab_ejercicio_fiscal', '=', $ejercicio)
                                ->where('co_sector', '=', $value_distribucion_uno->co_sector)
                                ->where('nu_original', '=', $value_distribucion_dos->nu_original)
                                ->where('id_ejecutor', '=', $value_distribucion_tres->id_ejecutor)
                                ->where('t01.co_partida', '=', $value_distribucion_cuatro->co_partida)
                                ->where('t02.co_partida', '=', $value_distribucion_cinco->co_partida)
                                ->where('t03.co_partida', '=', $value_distribucion_seis->co_partida)
                                ->where('id_tab_tipo_ejecutor', '=', 1)
                                ->groupBy('public.vista_distribucion_presupuesto.id_tab_ac')
                                ->groupBy('t04.co_partida')
                                ->groupBy('t04.tx_nombre')
                                ->orderBy('co_partida', 'ASC')
                                ->get();

                                foreach ($distribucion_siete as $key => $value_distribucion_siete) {

                                    $total_distribucion_siete = vista_distribucion_presupuesto::join('mantenimiento.tab_partidas as t01', 't01.co_partida', '=', DB::raw('left(public.vista_distribucion_presupuesto.co_partida, 3)'))
                                          ->join('mantenimiento.tab_partidas as t02', 't02.co_partida', '=', DB::raw('left(public.vista_distribucion_presupuesto.co_partida, 5)'))
                                          ->join('mantenimiento.tab_partidas as t03', 't03.co_partida', '=', DB::raw('left(public.vista_distribucion_presupuesto.co_partida, 7)'))
                                          ->join('mantenimiento.tab_partidas as t04', 't04.co_partida', '=', DB::raw('left(public.vista_distribucion_presupuesto.co_partida, 9)'))
                                          ->select(DB::raw('sum(monto) as mo_partida'))
                                          ->where('ef_uno', '=', $ejercicio)
                                          ->where('t01.id_tab_ejercicio_fiscal', '=', $ejercicio)
                                          ->where('t02.id_tab_ejercicio_fiscal', '=', $ejercicio)
                                          ->where('t03.id_tab_ejercicio_fiscal', '=', $ejercicio)
                                          ->where('t04.id_tab_ejercicio_fiscal', '=', $ejercicio)
                                          ->where('co_sector', '=', $value_distribucion_uno->co_sector)
                                          ->where('nu_original', '=', $value_distribucion_dos->nu_original)
                                          ->where('t01.co_partida', '=', $value_distribucion_cuatro->co_partida)
                                          ->where('t02.co_partida', '=', $value_distribucion_cinco->co_partida)
                                          ->where('t03.co_partida', '=', $value_distribucion_seis->co_partida)
                                      ->where('id_tab_tipo_ejecutor', '=', 1)
                                    ->where(DB::raw('left(public.vista_distribucion_presupuesto.co_partida::bigint::text::varchar, 9)'), '=', trim($value_distribucion_siete->co_partida))
                                    ->first();


                                    $pdf->SetFont('', '', 5);
                                    $pdf->MultiCell(6, 9, substr(trim($value_distribucion_siete->co_partida), 0, 3), 0, 'L', 0, 0, '', '', true);
                                    $pdf->MultiCell(5, 9, substr(substr(trim($value_distribucion_siete->co_partida), 0, 5), 3), 0, 'L', 0, 0, '', '', true);
                                    $pdf->MultiCell(5, 9, substr(substr(trim($value_distribucion_siete->co_partida), 0, 7), 5), 0, 'L', 0, 0, '', '', true);
                                    $pdf->MultiCell(6, 9, substr(substr(trim($value_distribucion_siete->co_partida), 0, 9), 7), 0, 'L', 0, 0, '', '', true);
                                    $pdf->MultiCell(7, 9, '', 0, 'C', 0, 0, '', '', true);
                                    $pdf->MultiCell(71, 9, $value_distribucion_siete->tx_nombre, 0, 'L', 0, 0, '', '', true);
                                    $pdf->MultiCell(20, 9, number_format($total_distribucion_siete->mo_partida, 0, ',', '.'), 0, 'R', 0, 0, '', '', true);

                                    $distribucion_ae = vista_distribucion_presupuesto::select('nu_ae', 'de_ae')
                                    ->where('ef_uno', '=', $ejercicio)
                                    ->where('co_sector', '=', $value_distribucion_uno->co_sector)
                                    ->where('nu_original', '=', $value_distribucion_dos->nu_original)
                                    ->where('id_ejecutor', '=', $value_distribucion_tres->id_ejecutor)
                                    ->where('id_tab_tipo_ejecutor', '=', 1)
                                    ->groupBy('nu_ae')
                                    ->groupBy('de_ae')
                                    ->orderBy('nu_ae', 'ASC')
                                    ->get();

                                    foreach($distribucion_ae as $key => $value_distribucion_ae) {

                                        $distribucion_ae_partida = vista_distribucion_presupuesto::join('mantenimiento.tab_partidas as t01', 't01.co_partida', '=', DB::raw('left(public.vista_distribucion_presupuesto.co_partida, 3)'))
                                        ->join('mantenimiento.tab_partidas as t02', 't02.co_partida', '=', DB::raw('left(public.vista_distribucion_presupuesto.co_partida, 5)'))
                                        ->join('mantenimiento.tab_partidas as t03', 't03.co_partida', '=', DB::raw('left(public.vista_distribucion_presupuesto.co_partida, 7)'))
                                        ->join('mantenimiento.tab_partidas as t04', 't04.co_partida', '=', DB::raw('left(public.vista_distribucion_presupuesto.co_partida, 9)'))
                                        ->select('nu_ae', DB::raw('sum(monto) as mo_partida'))
                                        ->where('ef_uno', '=', $ejercicio)
                                        ->where('t01.id_tab_ejercicio_fiscal', '=', $ejercicio)
                                        ->where('t02.id_tab_ejercicio_fiscal', '=', $ejercicio)
                                        ->where('t03.id_tab_ejercicio_fiscal', '=', $ejercicio)
                                        ->where('t04.id_tab_ejercicio_fiscal', '=', $ejercicio)
                                        ->where('co_sector', '=', $value_distribucion_uno->co_sector)
                                        ->where('nu_original', '=', $value_distribucion_dos->nu_original)
                                        ->where('id_ejecutor', '=', $value_distribucion_tres->id_ejecutor)
                                        ->where('nu_ae', '=', $value_distribucion_ae->nu_ae)
                                        ->where('t01.co_partida', '=', $value_distribucion_cuatro->co_partida)
                                        ->where('t02.co_partida', '=', $value_distribucion_cinco->co_partida)
                                        ->where('t03.co_partida', '=', $value_distribucion_seis->co_partida)
                                        ->where('t04.co_partida', '=', $value_distribucion_siete->co_partida)
                                        ->where('id_tab_tipo_ejecutor', '=', 1)
                                        ->groupBy('nu_ae')
                                        ->orderBy('nu_ae', 'ASC')
                                        ->get();

                                        $i = 0;
                                        foreach($distribucion_ae_partida as $key => $value_distribucion_ae_partida) {
                                            $i++;
                                            $pdf->SetFont('', '', 5);
                                            $pdf->MultiCell(19, 5, number_format($value_distribucion_ae_partida->mo_partida, 0, ',', '.'), 0, 'R', 0, 0, '', '', true);
                                            $pdf->SetFont('', 'B', 5);
                                        }
                                        if ($i == 0) {
                                            $pdf->MultiCell(19, 5, '', 0, 'C', 0, 0, '', '', true);
                                        }
                                    }

                                    /*$pdf->MultiCell(16, 5, '', 0, 'C', 0, 0, '', '', true);
                                    $pdf->MultiCell(16, 5, '', 0, 'C', 0, 0, '', '', true);
                                    $pdf->MultiCell(16, 5, '', 0, 'C', 0, 0, '', '', true);
                                    $pdf->MultiCell(16, 5, '', 0, 'C', 0, 0, '', '', true);*/
                                    //$pdf->ln(4);

                                    $condicionPartida = strlen($value_distribucion_siete->tx_nombre);
                                    if ($condicionPartida >= 60) {
                                        $pdf->ln(4);
                                    } else {
                                        $pdf->ln(2);
                                    }
                                    if ($condicionPartida >= 120) {
                                        $pdf->ln(2);
                                    }

                                    $start_y = $pdf->GetY();

                                    if ($start_y >= 260) {

                                        // reset font stretching  reset font spacing
                                        $pdf->setFontStretching(100);
                                        $pdf->setFontSpacing(0);
                                        $pdf->SetLineWidth(0.150);
                                        $pdf->setCellHeightRatio(2);

                                        $pdf->AddPage();

                                        $pdf->SetFont('', 'B', 8);
                                        $pdf->setCellHeightRatio(1.2);
                                        $pdf->MultiCell(30, 5, 'GOBERNACIÓN '.chr(10).'DEL ESTADO ZULIA', 0, 'C', 0, 0, '', '', true);
                                        $pdf->MultiCell(25, 5, '', 0, 'C', 0, 0, '', '', true);
                                        $pdf->setCellHeightRatio(2);
                                        $pdf->SetFont('', 'B', 8);
                                        $pdf->setCellHeightRatio(1);
                                        $pdf->MultiCell(90, 5, 'CRÉDITOS PRESUPUESTARIOS DEL PROYECTO Y/O ACCIÓN CENTRALIZADA A NIVEL DE PROYECTOS Y/O ACCIÓN CENTRALIZADA', 0, 'C', 0, 0, '', '', true);
                                        $pdf->setCellHeightRatio(2);
                                        $pdf->ln(8);
                                        $pdf->SetFont('', 'B', 8);
                                        $pdf->MultiCell(55, 5, 'PRESUPUESTO '.$ejercicio, 0, 'L', 0, 0, '', '', true);
                                        $pdf->MultiCell(90, 5, '(EN BOLÍVARES)', 0, 'C', 0, 0, '', '', true);
                                        $pdf->ln(-10);
                                        $pdf->MultiCell(196, 18, '', 1, 'C', 0, 0, '', '', true);
                                        $pdf->ln(19);
                                        $pdf->SetFont('', 'B', 7);
                                        $pdf->setCellHeightRatio(1.2);

                                        $pdf->MultiCell(29, 7, chr(10).'SECTOR', 1, 'L', 0, 0, '', '', true);
                                        $pdf->SetFont('', '', 6);
                                        $pdf->MultiCell(71, 7, chr(10).$value_distribucion_tres->co_sector.' - '.mb_strtoupper($value_distribucion_tres->tx_descripcion, 'UTF-8'), 1, 'L', 0, 0, '', '', true);
                                        $pdf->SetFont('', 'B', 7);
                                        $pdf->MultiCell(20, 14, chr(10).'UNIDAD EJECUTORA', 1, 'C', 0, 0, '', '', true);
                                        $pdf->SetFont('', '', 7);
                                        $pdf->MultiCell(76, 14, chr(10).$value_distribucion_tres->id_ejecutor.' - '.mb_strtoupper($value_distribucion_tres->tx_ejecutor, 'UTF-8'), 1, 'L', 0, 0, '', '', true);
                                        $pdf->ln(7);
                                        $pdf->SetFont('', 'B', 6);
                                        $pdf->MultiCell(29, 7, 'PROYECTO Y/O ACCIÓN CENTRALIZADA', 1, 'L', 0, 0, '', '', true);
                                        $pdf->SetFont('', '', 6);
                                        $pdf->MultiCell(71, 7, substr($value_distribucion_tres->nu_original, -2).' - '.mb_strtoupper($value_distribucion_tres->de_nombre, 'UTF-8'), 1, 'L', 0, 0, '', '', true);
                                        $pdf->ln(7);
                                        $pdf->MultiCell(196, 226, '', 1, 'C', 0, 0, '', '', true);
                                        $pdf->ln(0);
                                        $pdf->SetFont('', 'B', 6);
                                        $pdf->MultiCell(6, 5, '', 0, 'L', 0, 0, '', '', true);
                                        $pdf->MultiCell(23, 5, 'SUB - PARTIDA', 1, 'L', 0, 0, '', '', true);
                                        $pdf->ln(20);
                                        $pdf->SetFont('', 'B', 6);
                                        $pdf->StartTransform();
                                        $pdf->Rotate(90);
                                        $pdf->MultiCell(20, 6, 'PARTIDA', 1, 'L', 0, 0, '', '', true);
                                        $pdf->ln(6);
                                        $pdf->MultiCell(15, 5, 'GENERICA', 1, 'L', 0, 0, '', '', true);
                                        $pdf->MultiCell(5, 5, '', 0, 'L', 0, 0, '', '', true);
                                        $pdf->ln(5);
                                        $pdf->MultiCell(15, 5, 'ESPECIFICA', 1, 'L', 0, 0, '', '', true);
                                        $pdf->MultiCell(5, 5, '', 0, 'L', 0, 0, '', '', true);
                                        $pdf->ln(5);
                                        $pdf->MultiCell(15, 6, 'SUB ESPECIFICA', 1, 'L', 0, 0, '', '', true);
                                        $pdf->MultiCell(5, 5, '', 0, 'L', 0, 0, '', '', true);
                                        $pdf->ln(6);
                                        $pdf->MultiCell(15, 7, 'SUB SUB ESPECIFICA', 1, 'L', 0, 0, '', '', true);
                                        $pdf->MultiCell(5, 5, '', 0, 'L', 0, 0, '', '', true);
                                        $pdf->ln(40);
                                        $pdf->StopTransform();
                                        $pdf->ln(-82);
                                        $pdf->SetFont('', 'B', 8);
                                        $pdf->setCellHeightRatio(1.2);
                                        $pdf->MultiCell(29, 20, '', 0, 'C', 0, 0, '', '', true);
                                        $pdf->MultiCell(71, 20, chr(10).chr(10).'DENOMINACIÓN', 1, 'C', 0, 0, '', '', true);
                                        $pdf->SetFont('', 'B', 6);
                                        $pdf->setCellHeightRatio(1.2);
                                        $pdf->MultiCell(20, 20, chr(10).chr(10).'TOTAL PROYECTO Y/O ACCIÓN CENTRALIZADA', 1, 'C', 0, 0, '', '', true);
                                        $pdf->MultiCell(80, 5, 'ACCIÓNES ESPECIFICAS', 0, 'C', 0, 0, '', '', true);
                                        $pdf->ln(0);
                                        $pdf->MultiCell(100, 20, '', 0, 'C', 0, 0, '', '', true);
                                        $pdf->ln(5);
                                        $pdf->MultiCell(120, 20, '', 0, 'C', 0, 0, '', '', true);

                                        $distribucion_ae = vista_distribucion_presupuesto::select('nu_ae', 'de_ae')
                                        ->where('ef_uno', '=', $ejercicio)
                                        ->where('co_sector', '=', $value_distribucion_uno->co_sector)
                                        ->where('nu_original', '=', $value_distribucion_dos->nu_original)
                                        ->where('id_ejecutor', '=', $value_distribucion_tres->id_ejecutor)
                                        ->where('id_tab_tipo_ejecutor', '=', 1)
                                        ->groupBy('nu_ae')
                                        ->groupBy('de_ae')
                                        ->orderBy('nu_ae', 'ASC')
                                        ->get();

                                        $a = 4;
                                        $b = 0;
                                        foreach($distribucion_ae as $key => $value_distribucion_ae) {
                                            $b++;
                                            $pdf->SetFont('', 'B', 4);
                                            $pdf->MultiCell(19, 15, substr($value_distribucion_ae->nu_ae, -2).' - '.mb_strtoupper($value_distribucion_ae->de_ae, 'UTF-8'), 1, 'L', 0, 0, '', '', true);
                                            $pdf->SetFont('', 'B', 5);
                                            $c = $a - $b;
                                        }
                                        for ($n=0; $n<$c; $n++) {
                                            $pdf->MultiCell(19, 15, '', 1, 'C', 0, 0, '', '', true);
                                        }

                                        $pdf->ln(15);
                                        $pdf->setCellHeightRatio(1);
                                        $pdf->MultiCell(6, 206, '', 1, 'C', 0, 0, '', '', true);
                                        $pdf->MultiCell(5, 206, '', 1, 'C', 0, 0, '', '', true);
                                        $pdf->MultiCell(5, 206, '', 1, 'C', 0, 0, '', '', true);
                                        $pdf->MultiCell(6, 206, '', 1, 'C', 0, 0, '', '', true);
                                        $pdf->MultiCell(7, 206, '', 1, 'C', 0, 0, '', '', true);
                                        $pdf->MultiCell(71, 206, '', 1, 'C', 0, 0, '', '', true);
                                        $pdf->MultiCell(20, 206, '', 1, 'C', 0, 0, '', '', true);
                                        $pdf->MultiCell(19, 206, '', 1, 'C', 0, 0, '', '', true);
                                        $pdf->MultiCell(19, 206, '', 1, 'C', 0, 0, '', '', true);
                                        $pdf->MultiCell(19, 206, '', 1, 'C', 0, 0, '', '', true);
                                        $pdf->MultiCell(19, 206, '', 1, 'C', 0, 0, '', '', true);
                                        $pdf->ln(208);
                                        $pdf->SetFont('', '', 7);
                                        $pdf->MultiCell(29, 5, 'Pag. '.$pdf->getAliasNumPage().' de '.$pdf->getAliasNbPages(), 0, 'L', 0, 0, '', '', true);
                                        $pdf->MultiCell(151, 5, '', 0, 'C', 0, 0, '', '', true);
                                        $pdf->MultiCell(16, 5, 'GEZ: '.$ejercicio, 0, 'L', 0, 0, '', '', true);
                                        $pdf->ln(-208);
                                        $pdf->ln(2);
                                        $pdf->SetFont('', '', 7);
                                        $pdf->setCellHeightRatio(1);

                                    }

                                    /*$distribucion_ocho = vista_distribucion_presupuesto::
                                    join('mantenimiento.tab_partidas as t01', 't01.co_partida', '=', DB::raw('left(public.vista_distribucion_presupuesto.co_partida, 3)'))
                                    ->join('mantenimiento.tab_partidas as t02', 't02.co_partida', '=', DB::raw('left(public.vista_distribucion_presupuesto.co_partida, 5)'))
                                    ->join('mantenimiento.tab_partidas as t03', 't03.co_partida', '=', DB::raw('left(public.vista_distribucion_presupuesto.co_partida, 7)'))
                                    ->join('mantenimiento.tab_partidas as t04', 't04.co_partida', '=', DB::raw('left(public.vista_distribucion_presupuesto.co_partida, 9)'))
                                    ->join('mantenimiento.tab_partidas as t05', 't05.co_partida', '=', DB::raw('left(public.vista_distribucion_presupuesto.co_partida, 12)'))
                                    ->select( 't05.co_partida', 't05.tx_nombre',  DB::raw('sum(monto) as mo_partida') )
                                    ->where('ef_uno', '=', $ejercicio)
                                    ->where('t01.id_tab_ejercicio_fiscal', '=', $ejercicio)
                                    ->where('t02.id_tab_ejercicio_fiscal', '=', $ejercicio)
                                    ->where('t03.id_tab_ejercicio_fiscal', '=', $ejercicio)
                                    ->where('t04.id_tab_ejercicio_fiscal', '=', $ejercicio)
                                    ->where('t05.id_tab_ejercicio_fiscal', '=', $ejercicio)
                                    ->where('co_sector', '=', $value_distribucion_uno->co_sector)
                                    ->where('nu_original', '=', $value_distribucion_dos->nu_original)
                                    ->where('id_ejecutor', '=', $value_distribucion_tres->id_ejecutor)
                                    ->where('t01.co_partida', '=', $value_distribucion_cuatro->co_partida)
                                    ->where('t02.co_partida', '=', $value_distribucion_cinco->co_partida)
                                    ->where('t03.co_partida', '=', $value_distribucion_seis->co_partida)
                                    ->where('t04.co_partida', '=', $value_distribucion_siete->co_partida)
                                    ->where('id_tab_tipo_ejecutor', '=', 1)
                                    ->groupBy('t05.co_partida')
                                    ->groupBy('t05.tx_nombre')
                                    ->orderBy('co_partida','ASC')
                                    ->get();*/

                                    /*$distribucion_ocho = vista_distribucion_presupuesto::
                                    select( 'co_partida', 'de_denominacion',  'monto' )
                                    ->where('ef_uno', '=', $ejercicio)
                                    ->where('co_sector', '=', $value_distribucion_uno->co_sector)
                                    ->where('nu_original', '=', $value_distribucion_dos->nu_original)
                                    ->where('id_ejecutor', '=', $value_distribucion_tres->id_ejecutor)
                                    ->where(DB::raw('left(public.vista_distribucion_presupuesto.co_partida, 9)::numeric'), '=', $value_distribucion_siete->co_partida)
                                    ->where('id_tab_tipo_ejecutor', '=', 1)
                                    ->orderBy('co_partida','ASC')
                                    ->get();*/

                                    $distribucion_ocho = tab_ac_es_partida_desagregado::select('co_partida', 'de_denominacion', 'mo_partida')
                                    ->where('id_tab_ejercicio_fiscal', '=', $ejercicio)
                                    ->where('td_tab_ac', '=', $value_distribucion_siete->id_tab_ac)
                                    //->where('id_tab_ac_ae_predefinida', '=', $value_distribucion_siete->id_tab_ac_ae_predef)
                                    ->where(DB::raw('left(co_partida::bigint::text::varchar, 9)'), '=', trim($value_distribucion_siete->co_partida))
                                    ->orderBy('co_partida', 'ASC')
                                    ->get();

                                    foreach ($distribucion_ocho as $key => $value_distribucion_ocho) {

                                        $total_distribucion_ocho = tab_ac_es_partida_desagregado::select(DB::raw('sum(mo_partida) as mo_partida'))
                                        ->join('t46_acciones_centralizadas as t01', 't01.id', '=', 'tab_ac_es_partida_desagregado.td_tab_ac')
                                        ->join('mantenimiento.tab_sectores as t02', 't02.id', '=', 't01.id_subsector')
                                        ->join('mantenimiento.tab_ac_predefinida as t03', 't03.id', '=', 't01.id_accion')
                                        ->where('id_tab_ejercicio_fiscal', '=', $ejercicio)
                                        ->where('co_sector', '=', $value_distribucion_uno->co_sector)
                                        ->where('nu_original', '=', $value_distribucion_dos->nu_original)
                                        ->where(DB::raw('left(co_partida::bigint::text::varchar, 12)'), '=', trim($value_distribucion_ocho->co_partida))
                                        ->first();

                                        $pdf->SetFont('', '', 5);
                                        $pdf->MultiCell(6, 9, substr(trim($value_distribucion_ocho->co_partida), 0, 3), 0, 'L', 0, 0, '', '', true);
                                        $pdf->MultiCell(5, 9, substr(substr(trim($value_distribucion_ocho->co_partida), 0, 5), 3), 0, 'L', 0, 0, '', '', true);
                                        $pdf->MultiCell(5, 9, substr(substr(trim($value_distribucion_ocho->co_partida), 0, 7), 5), 0, 'L', 0, 0, '', '', true);
                                        $pdf->MultiCell(6, 9, substr(substr(trim($value_distribucion_ocho->co_partida), 0, 9), 7), 0, 'L', 0, 0, '', '', true);
                                        $pdf->MultiCell(7, 9, substr(substr(trim($value_distribucion_ocho->co_partida), 0, 12), 9), 0, 'C', 0, 0, '', '', true);
                                        $pdf->MultiCell(71, 9, $value_distribucion_ocho->de_denominacion, 0, 'L', 0, 0, '', '', true);
                                        $pdf->MultiCell(20, 9, number_format($total_distribucion_ocho->mo_partida, 0, ',', '.'), 0, 'R', 0, 0, '', '', true);

                                        $distribucion_ae = vista_distribucion_presupuesto::select('nu_ae', 'de_ae', 'id_tab_ac_ae_predef')
                                        ->where('ef_uno', '=', $ejercicio)
                                        ->where('co_sector', '=', $value_distribucion_uno->co_sector)
                                        ->where('nu_original', '=', $value_distribucion_dos->nu_original)
                                        ->where('id_ejecutor', '=', $value_distribucion_tres->id_ejecutor)
                                        ->where('id_tab_tipo_ejecutor', '=', 1)
                                        ->groupBy('nu_ae')
                                        ->groupBy('de_ae')
                                        ->groupBy('id_tab_ac_ae_predef')
                                        ->orderBy('nu_ae', 'ASC')
                                        ->get();

                                        foreach($distribucion_ae as $key => $value_distribucion_ae) {

                                            /*$distribucion_ae_partida = vista_distribucion_presupuesto::
                                            join('mantenimiento.tab_partidas as t01', 't01.co_partida', '=', DB::raw('left(public.vista_distribucion_presupuesto.co_partida, 3)'))
                                            ->join('mantenimiento.tab_partidas as t02', 't02.co_partida', '=', DB::raw('left(public.vista_distribucion_presupuesto.co_partida, 5)'))
                                            ->join('mantenimiento.tab_partidas as t03', 't03.co_partida', '=', DB::raw('left(public.vista_distribucion_presupuesto.co_partida, 7)'))
                                            ->join('mantenimiento.tab_partidas as t04', 't04.co_partida', '=', DB::raw('left(public.vista_distribucion_presupuesto.co_partida, 9)'))
                                            ->join('mantenimiento.tab_partidas as t05', 't05.co_partida', '=', DB::raw('left(public.vista_distribucion_presupuesto.co_partida, 12)'))
                                            ->select('nu_ae', DB::raw('sum(monto) as mo_partida') )
                                            ->where('ef_uno', '=', $ejercicio)
                                            ->where('t01.id_tab_ejercicio_fiscal', '=', $ejercicio)
                                            ->where('t02.id_tab_ejercicio_fiscal', '=', $ejercicio)
                                            ->where('t03.id_tab_ejercicio_fiscal', '=', $ejercicio)
                                            ->where('t04.id_tab_ejercicio_fiscal', '=', $ejercicio)
                                            ->where('t05.id_tab_ejercicio_fiscal', '=', $ejercicio)
                                            ->where('co_sector', '=', $value_distribucion_uno->co_sector)
                                            ->where('nu_original', '=', $value_distribucion_dos->nu_original)
                                            ->where('id_ejecutor', '=', $value_distribucion_tres->id_ejecutor)
                                            ->where('nu_ae', '=', $value_distribucion_ae->nu_ae)
                                            ->where('t01.co_partida', '=', $value_distribucion_cuatro->co_partida)
                                            ->where('t02.co_partida', '=', $value_distribucion_cinco->co_partida)
                                            ->where('t03.co_partida', '=', $value_distribucion_seis->co_partida)
                                            ->where('t04.co_partida', '=', $value_distribucion_siete->co_partida)
                                            ->where('t05.co_partida', '=', $value_distribucion_ocho->co_partida)
                                            ->where('id_tab_tipo_ejecutor', '=', 1)
                                            ->groupBy('nu_ae')
                                            ->orderBy('nu_ae','ASC')
                                            ->get();*/

                                            $distribucion_ae_partida = tab_ac_es_partida_desagregado::select('co_partida', 'de_denominacion', 'mo_partida')
                                            ->where('id_tab_ejercicio_fiscal', '=', $ejercicio)
                                            ->where('td_tab_ac', '=', $value_distribucion_siete->id_tab_ac)
                                            ->where('id_tab_ac_ae_predefinida', '=', $value_distribucion_ae->id_tab_ac_ae_predef)
                                            ->where(DB::raw('left(co_partida::bigint::text::varchar, 12)'), '=', trim($value_distribucion_ocho->co_partida))
                                            ->orderBy('co_partida', 'ASC')
                                            ->get();

                                            $i = 0;
                                            foreach($distribucion_ae_partida as $key => $value_distribucion_ae_partida) {
                                                $i++;
                                                $pdf->SetFont('', '', 5);
                                                $pdf->MultiCell(19, 5, number_format($value_distribucion_ae_partida->mo_partida, 0, ',', '.'), 0, 'R', 0, 0, '', '', true);
                                                $pdf->SetFont('', 'B', 5);
                                            }
                                            if ($i == 0) {
                                                $pdf->MultiCell(19, 5, '', 0, 'C', 0, 0, '', '', true);
                                            }
                                        }

                                        /*$pdf->MultiCell(16, 5, '', 0, 'C', 0, 0, '', '', true);
                                        $pdf->MultiCell(16, 5, '', 0, 'C', 0, 0, '', '', true);
                                        $pdf->MultiCell(16, 5, '', 0, 'C', 0, 0, '', '', true);
                                        $pdf->MultiCell(16, 5, '', 0, 'C', 0, 0, '', '', true);*/
                                        //$pdf->ln(4);

                                        $condicionPartida = strlen($value_distribucion_ocho->de_denominacion);
                                        if ($condicionPartida >= 60) {
                                            $pdf->ln(4);
                                        } else {
                                            $pdf->ln(2);
                                        }
                                        if ($condicionPartida >= 120) {
                                            $pdf->ln(2);
                                        }

                                        $start_y = $pdf->GetY();

                                        if ($start_y >= 260) {

                                            // reset font stretching  reset font spacing
                                            $pdf->setFontStretching(100);
                                            $pdf->setFontSpacing(0);
                                            $pdf->SetLineWidth(0.150);
                                            $pdf->setCellHeightRatio(2);

                                            $pdf->AddPage();

                                            $pdf->SetFont('', 'B', 8);
                                            $pdf->setCellHeightRatio(1.2);
                                            $pdf->MultiCell(30, 5, 'GOBERNACIÓN '.chr(10).'DEL ESTADO ZULIA', 0, 'C', 0, 0, '', '', true);
                                            $pdf->MultiCell(25, 5, '', 0, 'C', 0, 0, '', '', true);
                                            $pdf->setCellHeightRatio(2);
                                            $pdf->SetFont('', 'B', 8);
                                            $pdf->setCellHeightRatio(1);
                                            $pdf->MultiCell(90, 5, 'CRÉDITOS PRESUPUESTARIOS DEL PROYECTO Y/O ACCIÓN CENTRALIZADA A NIVEL DE PROYECTOS Y/O ACCIÓN CENTRALIZADA', 0, 'C', 0, 0, '', '', true);
                                            $pdf->setCellHeightRatio(2);
                                            $pdf->ln(8);
                                            $pdf->SetFont('', 'B', 8);
                                            $pdf->MultiCell(55, 5, 'PRESUPUESTO '.$ejercicio, 0, 'L', 0, 0, '', '', true);
                                            $pdf->MultiCell(90, 5, '(EN BOLÍVARES)', 0, 'C', 0, 0, '', '', true);
                                            $pdf->ln(-10);
                                            $pdf->MultiCell(196, 18, '', 1, 'C', 0, 0, '', '', true);
                                            $pdf->ln(19);
                                            $pdf->SetFont('', 'B', 7);
                                            $pdf->setCellHeightRatio(1.2);

                                            $pdf->MultiCell(29, 7, chr(10).'SECTOR', 1, 'L', 0, 0, '', '', true);
                                            $pdf->SetFont('', '', 6);
                                            $pdf->MultiCell(71, 7, chr(10).$value_distribucion_tres->co_sector.' - '.mb_strtoupper($value_distribucion_tres->tx_descripcion, 'UTF-8'), 1, 'L', 0, 0, '', '', true);
                                            $pdf->SetFont('', 'B', 7);
                                            $pdf->MultiCell(20, 14, chr(10).'UNIDAD EJECUTORA', 1, 'C', 0, 0, '', '', true);
                                            $pdf->SetFont('', '', 7);
                                            $pdf->MultiCell(76, 14, chr(10).$value_distribucion_tres->id_ejecutor.' - '.mb_strtoupper($value_distribucion_tres->tx_ejecutor, 'UTF-8'), 1, 'L', 0, 0, '', '', true);
                                            $pdf->ln(7);
                                            $pdf->SetFont('', 'B', 6);
                                            $pdf->MultiCell(29, 7, 'PROYECTO Y/O ACCIÓN CENTRALIZADA', 1, 'L', 0, 0, '', '', true);
                                            $pdf->SetFont('', '', 6);
                                            $pdf->MultiCell(71, 7, substr($value_distribucion_tres->nu_original, -2).' - '.mb_strtoupper($value_distribucion_tres->de_nombre, 'UTF-8'), 1, 'L', 0, 0, '', '', true);
                                            $pdf->ln(7);
                                            $pdf->MultiCell(196, 226, '', 1, 'C', 0, 0, '', '', true);
                                            $pdf->ln(0);
                                            $pdf->SetFont('', 'B', 6);
                                            $pdf->MultiCell(6, 5, '', 0, 'L', 0, 0, '', '', true);
                                            $pdf->MultiCell(23, 5, 'SUB - PARTIDA', 1, 'L', 0, 0, '', '', true);
                                            $pdf->ln(20);
                                            $pdf->SetFont('', 'B', 6);
                                            $pdf->StartTransform();
                                            $pdf->Rotate(90);
                                            $pdf->MultiCell(20, 6, 'PARTIDA', 1, 'L', 0, 0, '', '', true);
                                            $pdf->ln(6);
                                            $pdf->MultiCell(15, 5, 'GENERICA', 1, 'L', 0, 0, '', '', true);
                                            $pdf->MultiCell(5, 5, '', 0, 'L', 0, 0, '', '', true);
                                            $pdf->ln(5);
                                            $pdf->MultiCell(15, 5, 'ESPECIFICA', 1, 'L', 0, 0, '', '', true);
                                            $pdf->MultiCell(5, 5, '', 0, 'L', 0, 0, '', '', true);
                                            $pdf->ln(5);
                                            $pdf->MultiCell(15, 6, 'SUB ESPECIFICA', 1, 'L', 0, 0, '', '', true);
                                            $pdf->MultiCell(5, 5, '', 0, 'L', 0, 0, '', '', true);
                                            $pdf->ln(6);
                                            $pdf->MultiCell(15, 7, 'SUB SUB ESPECIFICA', 1, 'L', 0, 0, '', '', true);
                                            $pdf->MultiCell(5, 5, '', 0, 'L', 0, 0, '', '', true);
                                            $pdf->ln(40);
                                            $pdf->StopTransform();
                                            $pdf->ln(-82);
                                            $pdf->SetFont('', 'B', 8);
                                            $pdf->setCellHeightRatio(1.2);
                                            $pdf->MultiCell(29, 20, '', 0, 'C', 0, 0, '', '', true);
                                            $pdf->MultiCell(71, 20, chr(10).chr(10).'DENOMINACIÓN', 1, 'C', 0, 0, '', '', true);
                                            $pdf->SetFont('', 'B', 6);
                                            $pdf->setCellHeightRatio(1.2);
                                            $pdf->MultiCell(20, 20, chr(10).chr(10).'TOTAL PROYECTO Y/O ACCIÓN CENTRALIZADA', 1, 'C', 0, 0, '', '', true);
                                            $pdf->MultiCell(80, 5, 'ACCIÓNES ESPECIFICAS', 0, 'C', 0, 0, '', '', true);
                                            $pdf->ln(0);
                                            $pdf->MultiCell(100, 20, '', 0, 'C', 0, 0, '', '', true);
                                            $pdf->ln(5);
                                            $pdf->MultiCell(120, 20, '', 0, 'C', 0, 0, '', '', true);

                                            $distribucion_ae = vista_distribucion_presupuesto::select('nu_ae', 'de_ae')
                                            ->where('ef_uno', '=', $ejercicio)
                                            ->where('co_sector', '=', $value_distribucion_uno->co_sector)
                                            ->where('nu_original', '=', $value_distribucion_dos->nu_original)
                                            ->where('id_ejecutor', '=', $value_distribucion_tres->id_ejecutor)
                                            ->where('id_tab_tipo_ejecutor', '=', 1)
                                            ->groupBy('nu_ae')
                                            ->groupBy('de_ae')
                                            ->orderBy('nu_ae', 'ASC')
                                            ->get();

                                            $a = 4;
                                            $b = 0;
                                            foreach($distribucion_ae as $key => $value_distribucion_ae) {
                                                $b++;
                                                $pdf->SetFont('', '', 4);
                                                $pdf->MultiCell(19, 15, substr($value_distribucion_ae->nu_ae, -2).' - '.mb_strtoupper($value_distribucion_ae->de_ae, 'UTF-8'), 1, 'L', 0, 0, '', '', true);
                                                $pdf->SetFont('', 'B', 5);
                                                $c = $a - $b;
                                            }
                                            for ($n=0; $n<$c; $n++) {
                                                $pdf->MultiCell(19, 15, '', 1, 'C', 0, 0, '', '', true);
                                            }

                                            $pdf->ln(15);
                                            $pdf->setCellHeightRatio(1);
                                            $pdf->MultiCell(6, 206, '', 1, 'C', 0, 0, '', '', true);
                                            $pdf->MultiCell(5, 206, '', 1, 'C', 0, 0, '', '', true);
                                            $pdf->MultiCell(5, 206, '', 1, 'C', 0, 0, '', '', true);
                                            $pdf->MultiCell(6, 206, '', 1, 'C', 0, 0, '', '', true);
                                            $pdf->MultiCell(7, 206, '', 1, 'C', 0, 0, '', '', true);
                                            $pdf->MultiCell(71, 206, '', 1, 'C', 0, 0, '', '', true);
                                            $pdf->MultiCell(20, 206, '', 1, 'C', 0, 0, '', '', true);
                                            $pdf->MultiCell(19, 206, '', 1, 'C', 0, 0, '', '', true);
                                            $pdf->MultiCell(19, 206, '', 1, 'C', 0, 0, '', '', true);
                                            $pdf->MultiCell(19, 206, '', 1, 'C', 0, 0, '', '', true);
                                            $pdf->MultiCell(19, 206, '', 1, 'C', 0, 0, '', '', true);
                                            $pdf->ln(208);
                                            $pdf->SetFont('', '', 7);
                                            $pdf->MultiCell(29, 5, 'Pag. '.$pdf->getAliasNumPage().' de '.$pdf->getAliasNbPages(), 0, 'L', 0, 0, '', '', true);
                                            $pdf->MultiCell(151, 5, '', 0, 'C', 0, 0, '', '', true);
                                            $pdf->MultiCell(16, 5, 'GEZ: '.$ejercicio, 0, 'L', 0, 0, '', '', true);
                                            $pdf->ln(-208);
                                            $pdf->ln(2);
                                            $pdf->SetFont('', '', 7);
                                            $pdf->setCellHeightRatio(1);

                                        }

                                    }

                                }

                            }

                        }

                    }

                    $pdf->ln(2);

                    $pdf->SetFont('', 'B', 8);
                    $pdf->MultiCell(6, 5, '', 0, 'L', 0, 0, '', '', true);
                    $pdf->MultiCell(5, 5, '', 0, 'L', 0, 0, '', '', true);
                    $pdf->MultiCell(5, 5, '', 0, 'L', 0, 0, '', '', true);
                    $pdf->MultiCell(6, 5, '', 0, 'L', 0, 0, '', '', true);
                    $pdf->MultiCell(7, 5, '', 0, 'C', 0, 0, '', '', true);
                    $pdf->MultiCell(71, 5, 'TOTAL', 0, 'C', 0, 0, '', '', true);
                    $pdf->SetFont('', 'B', 6);
                    $pdf->MultiCell(20, 5, number_format($movimiento, 0, ',', '.'), 0, 'R', 0, 0, '', '', true);
                    $pdf->SetFont('', 'B', 8);

                    $distribucion_ae_partida = vista_distribucion_presupuesto::select('nu_ae', DB::raw('sum(monto) as mo_partida'))
                    ->where('ef_uno', '=', $ejercicio)
                    ->where('co_sector', '=', $value_distribucion_uno->co_sector)
                    ->where('nu_original', '=', $value_distribucion_dos->nu_original)
                    ->where('id_ejecutor', '=', $value_distribucion_tres->id_ejecutor)
                    ->where('id_tab_tipo_ejecutor', '=', 1)
                    ->groupBy('nu_ae')
                    ->orderBy('nu_ae', 'ASC')
                    ->get();

                    $i = 0;
                    foreach($distribucion_ae_partida as $key => $value_distribucion_ae_partida) {
                        $i++;
                        $pdf->SetFont('', 'B', 5);
                        $pdf->MultiCell(19, 5, number_format($value_distribucion_ae_partida->mo_partida, 0, ',', '.'), 0, 'R', 0, 0, '', '', true);
                        $pdf->SetFont('', 'B', 8);
                    }
                    if ($i == 0) {
                        $pdf->MultiCell(19, 5, '', 0, 'C', 0, 0, '', '', true);
                    }

                    /*$pdf->MultiCell(16, 5, '', 0, 'C', 0, 0, '', '', true);
                    $pdf->MultiCell(16, 5, '', 0, 'C', 0, 0, '', '', true);
                    $pdf->MultiCell(16, 5, '', 0, 'C', 0, 0, '', '', true);
                    $pdf->MultiCell(16, 5, '', 0, 'C', 0, 0, '', '', true);*/

                    // reset font stretching  reset font spacing
                    $pdf->setFontStretching(100);
                    $pdf->setFontSpacing(0);
                    $pdf->SetLineWidth(0.150);
                    $pdf->setCellHeightRatio(2);

                }

            }

        }

        //Cierre de Reporte
        $pdf->lastPage();
        $pdf->output('DISTRIBUCION_DE_PRESUPUESTO_'.$ejercicio.'_'.date("H:i:s").'.pdf', 'D');

    }
}
