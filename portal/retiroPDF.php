<?php
header("Access-Control-Allow-Origin: *");
include("servicios/conexioni.php");
require_once('servicios/tcpdf/tcpdf.php');
require('servicios/phpqrcode/qrlib.php');
setlocale(LC_ALL, 'es_ES');
mysqli_set_charset($mysqli, 'utf8');

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
}



//SUCURSAL
#region
$qsucursal = "SELECT * FROM ct_sucursales WHERE pk_sucursal = (SELECT fk_sucursal FROM tr_retiros WHERE pk_retiro=$id)";

if (!$rsucursal = $mysqli->query($qsucursal)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas1.";
    exit;
}
$empresa = $rsucursal->fetch_assoc();
$empresa_nombre = $empresa["nombre"];
$empresa_id = $empresa["pk_sucursal"];
$empresa_direccion = $empresa["direccion"];
$empresa_telefono = $empresa["telefono"];
$empresa_correo = $empresa["correo"];
#endregion



//ENCABEZADO
#region
$qretiros = "SELECT tr_retiros.*,
    ct_pagos.nombre as pago
    FROM tr_retiros, ct_pagos
    WHERE tr_retiros.pk_retiro = $id
    AND ct_pagos.pk_pago = tr_retiros.fk_pago";

if (!$rretiros = $mysqli->query($qretiros)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas2.";
    exit;
}

$retiros = $rretiros->fetch_assoc();
$retiro_id = $retiros["pk_retiro"];
$retiro_usuario = $retiros["fk_usuario"];
$retiro_motivo_id = $retiros["fk_retiro"];
$retiro_descripcion = $retiros["descripcion"];
$retiro_cantidad = $retiros["monto"];
$retiro_fecha = $retiros["fecha"];
$retiro_hora = $retiros["hora"];
$retiro_pago = $retiros["pago"];
#endregion



//MOTIVO
#region
$qmotivos = "SELECT * FROM ct_retiros WHERE pk_retiro = $retiro_motivo_id";

if (!$rmotivos = $mysqli->query($qmotivos)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas2.";
    exit;
}

$motivos = $rmotivos->fetch_assoc();
$motivo_id = $motivos["pk_retiro"];
$motivo_nombre = $motivos["nombre"];
#endregion




//CREAR PDF
#region
$pageLayout = array(80, 120);
$pdf = new TCPDF('P', PDF_UNIT, $pageLayout, true, 'UTF-8', false);

$pdf->SetCreator("DIMANTTI");
$pdf->SetAuthor('DIMANTTI');
$pdf->SetTitle("RETIRO DE CAJA #" . $id);

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(5, 5, 5);

$pdf->SetAutoPageBreak(FALSE, 5);

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
    <table border="0" cellpadding="1">
        <tr>
            <td colspan="2">
                <h3 style="text-align: center;">RETIRO #$id</h3>
                <h5 style="text-align: center; font-weight: light;">$empresa_direccion</h5>
                <h5 style="text-align: center; font-weight: light;">Tel $empresa_telefono</h5>
                <h5 style="text-align: center; font-weight: light;">$retiro_fecha $retiro_hora</h5>
                <h5 style="text-align: center; font-weight: light;"> <span style="font-weight: bold;">USUARIO:</span> $retiro_usuario</h5>
            </td>
        </tr>
    </table>
    HTML;
#endregion




//CODIGO QR
#region
$url = "https://dimantti.integracontrol.online/portal/retiroPDF.php?id=$id";
$qr_filename = "compra_qr.png";
QRcode::png($url, $qr_filename);
#endregion




//REGISTROS
#region
$cantidad = number_format($retiro_cantidad, 2);

$content_table = <<<HTML
    <table border="1" cellpadding="2" style="text-align: center; font-size: 10px;">
        <thead>
            <tr style="font-weight: bold;">
                <th style="width: 25%;">MOTIVO</th>
                <th style="width: 50%;">DESCRIPCION</th>
                <th style="width: 25%;">MONTO</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="width: 25%;">$motivo_nombre</td>
                <td style="width: 50%;">$retiro_descripcion</td>
                <td style="width: 25%; text-align: right;">$$cantidad</td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="text-align: center;">
                    <img src="$qr_filename" width="90" height="90" />
                </td>
            </tr>
        </tfoot>
    </table>
HTML;
#endregion




//IMPRIMIR
#region
$pdf->Image('@' . file_get_contents('servicios/logo.png'), 30, 5, 0, 24);
$pdf->writeHTMLCell(70, '', 5, 25, $parrafo_header);
$pdf->writeHTMLCell(76, '', 2, 70, $content_table);
#endregion




//DETALLES FINALES
#region
// reset pointer to the last page
$pdf->lastPage();

// Obtener el ancho y alto de la página, teniendo en cuenta los márgenes
$anchoPagina = $pdf->getPageWidth() - 8 - 8;
$altoPagina = $pdf->getPageHeight() - 8 - 8;

//Close and output PDF document
$fileName = "Retiro_Caja_" . $id . ".pdf";
$pdf->Output($fileName, 'I');
#endregion
