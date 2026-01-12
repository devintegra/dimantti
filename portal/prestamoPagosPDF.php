<?php
header("Access-Control-Allow-Origin: *");
include("servicios/conexioni.php");
require_once('servicios/tcpdf/tcpdf.php');
setlocale(LC_ALL, 'es_ES');
mysqli_set_charset($mysqli, 'utf8');

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
}


//DATOS DE LA SUCURSAL
#region
$mysqli->next_result();
if (!$rsucursal = $mysqli->query("SELECT * FROM ct_empresas WHERE pk_empresa = 1")) {
    echo "Lo sentimos, esta aplicación está experimentando problemas1.";
    exit;
}

$empresa = $rsucursal->fetch_assoc();
$empresa_nombre = $empresa["nombre"];
$empresa_id = $empresa["pk_empresa"];
$empresa_direccion = $empresa["direccion"];
$empresa_telefono = $empresa["telefono"];
$empresa_correo = $empresa["correo"];
#endregion



//DATOS DEL PRESTAMO
#region
$mysqli->next_result();
if (!$rsp_get_prestamo = $mysqli->query("CALL sp_get_prestamo($id)")) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.2";
    exit;
}

$rowc = $rsp_get_prestamo->fetch_assoc();
$empleado = $rowc["nombre_empleado"];
$fecha_creacion = $rowc["fecha_creacion"];
$monto = number_format($rowc["monto"], 2);
$cantidad_pagos = $rowc["cantidad_pagos"];
$monto_abono = $rowc["monto_abono"];
$observaciones = $rowc["observaciones"];
$saldo = number_format($rowc['saldo'], 2);
$abonado = number_format($rowc['monto'] - $rowc['saldo'], 2);
#endregion




//CREAR PDF
#region
$pageLayout = array(216, 279);
$pdf = new TCPDF('P', PDF_UNIT, $pageLayout, true, 'UTF-8', false);

$pdf->SetCreator("Cilo");
$pdf->SetAuthor('Cilo');
$pdf->SetTitle("ABONOS DEL PRESTAMO #" . $id);

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(5, 5, 5);

$pdf->SetAutoPageBreak(TRUE, 5);

$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
    require_once(dirname(__FILE__) . '/lang/eng.php');
    $pdf->setLanguageArray($l);
}

$fontname = TCPDF_FONTS::addTTFfont('../ws/tcpdf/fonts/quicksand/Quicksand-Regular.ttf', 'TrueTypeUnicode', '');
$pdf->SetFont('dejavusans', '', 10);
$pdf->AddPage();
#endregion



//HEADER
#region
$parrafo_header = <<<HTML
        <div>
            <table border="0">
                <tr>
                    <td>

                    </td>
                    <td>
                        <h1 style="text-align: center;">ABONOS DEL PRESTAMO #$id</h1>
                    </td>
                    <td>
                        <h4 style="text-align: right; font-weight: light; line-height: 12px;">Empleado: <span style="font-weight: bold;">$empleado</span></h4>
                        <h4 style="text-align: right; font-weight: light; line-height: 12px;">Fecha: <span style="font-weight: bold;">$fecha_creacion</span></h4>
                        <h4 style="text-align: right; font-weight: light; line-height: 12px;">Monto: <span style="font-weight: bold;">$$monto</span></h4>
                    </td>
                </tr>
            </table>
        </div>
    HTML;
#endregion




//REGISTROS
#region
$qabonos = "SELECT tra.*,
        ctp.nombre as pago
    FROM tr_abonos tra
    LEFT JOIN ct_pagos ctp ON ctp.pk_pago = tra.fk_pago
    WHERE tra.origen = 4
    AND tra.fk_factura = $id
    AND tra.estado = 1";

$mysqli->next_result();
if (!$rentradasd = $mysqli->query($qabonos)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. Error al obtener los abonos";
    exit;
}

$tr_table = "";

while ($row = $rentradasd->fetch_assoc()) {

    $cantidad = number_format($row['monto'], 2);
    $estatus = $row['estatus'] == 0 ? '<span style="color: #EFB810; font-weight: bold;">PENDIENTE</span>' : '<span style="color: #2CA880; font-weight: bold;">ABONADO</span>';

    $tr_table .= <<<HTML
        <tr>
            <td>$row[fecha]</td>
            <td>$row[pago]</td>
            <td style="text-align: right;">$$cantidad</td>
        </tr>
    HTML;
}
#endregion


$content_table = <<<HTML
    <table border="1" cellpadding="5" style="text-align: center;">
        <thead>
            <tr style="font-weight: bold;">
                <th>FECHA</th>
                <th>METODO DE PAGO</th>
                <th>MONTO</th>
            </tr>
        </thead>
        <tbody>
            $tr_table
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" style="text-align: right; font-weight: bold;">MONTO:</td>
                <td colspan="1" style="text-align: right; font-weight: bold;">$$monto</td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: right; font-weight: bold;">ABONADO:</td>
                <td colspan="1" style="text-align: right; font-weight: bold;">$$abonado</td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: right; font-weight: bold;">SALDO:</td>
                <td colspan="1" style="text-align: right; font-weight: bold;">$$saldo</td>
            </tr>
        </tfoot>
    </table>
HTML;




$pdf->Image('@' . file_get_contents('servicios/logo.png'), 7, 5, 0, 13);
$pdf->writeHTMLCell(207, '', 5, 5, $parrafo_header);
$pdf->writeHTMLCell(207, '', 5, 30, $content_table);
$currentPage = $pdf->getAliasNumPage();
$totalPages = $pdf->getAliasNbPages();
$footer = '<table width="100%"><tr><td align="right">Página ' . $currentPage . ' de ' . $totalPages . '</td></tr></table>';
$pdf->SetY(-15);
$pdf->writeHTML($footer, true, false, true, false, '');





//DETALLES FINALES
#region
// reset pointer to the last page
$pdf->lastPage();

// Obtener el ancho y alto de la página, teniendo en cuenta los márgenes
$anchoPagina = $pdf->getPageWidth() - 8 - 8;
$altoPagina = $pdf->getPageHeight() - 8 - 8;

//Close and output PDF document
$fileName = "AbonosPrestamo_" . $id . ".pdf";
$pdf->Output($fileName, 'I');
#endregion
