<?php
// Include the main TCPDF library (search for installation path).
require_once('tcpdf.php');

// create new PDF document
$pdf = new TCPDF('R', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Author');
$pdf->SetTitle('TCPDF HTML Table');
$pdf->SetSubject('TCPDF Tutorial');
$pdf->SetKeywords('TCPDF, PDF, html,table, example, test, guide');

// set default header data
$pdf->SetHeaderData('', '', ' HTML table', '');

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
//$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
// set margins
$pdf->SetMargins(15, 15, 15);
$pdf->SetHeaderMargin(15);
$pdf->SetFooterMargin(15);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, 15);

// set image scale factor
//$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
// ---------------------------------------------------------
// set font
$pdf->SetFont('', '', 10);

// add a page
$pdf->AddPage();

$start = 1;
$end = 30;
$step = 1;

$arr = range($start, $end, $step);


$table_header .= sprintf("<tr><th>%s</th><th>%s</th><th>%s</th><th>%s</th></tr>", 'IP', 'Computer', 'User', 'Fone');

foreach ($arr as $ar) {
    $row[] = $ar;
}
foreach ($row as $r):
    if (($r % 40) === 0):
        $table_header;
    endif;
    $table .= sprintf("<tr>\n<td>%s</td>\n<td>%s</td>\n<td>%s</td>\n<td>%s</td>\n</tr>\n", $r, $r, $r, $r);
endforeach;

$now = date("d/m/Y");
$caption = "<caption>IP addresses <em>$now</em></caption>\n";
$n = "\n";

$tbl = <<<EOD
<style>
table{
    font-family: serif;
    font-size: 11pt;
}
table tr {

}
table tr td {
    padding:3px;
    border:#000000 solid 1px;
}
em {
    font-size: 10pt;
}
tr { white-space:nowrap; }
</style>

        <h1>{$caption}</h1>
        {$table_begin}
        {$table_header}
        {$table}
</table>
EOD;

$pdf->writeHTML($tbl, true, false, false, false, '');


// reset pointer to the last page
//$pdf->lastPage();
// ---------------------------------------------------------
//Close and output PDF document
$pdf->Output('html_table.pdf', 'I');
?>
