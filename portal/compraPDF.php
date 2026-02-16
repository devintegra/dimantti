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
$qsucursal = "SELECT * FROM ct_sucursales where pk_sucursal = (select fk_sucursal from tr_compras where pk_compra=$id)";

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
$qentrada = "SELECT tr_compras.*,
    ct_pagos.nombre as pago
    FROM tr_compras, ct_pagos
    WHERE tr_compras.pk_compra = $id
    AND ct_pagos.pk_pago = tr_compras.fk_pago";

if (!$rentrada = $mysqli->query($qentrada)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas2.";
    exit;
}
$contrato = $rentrada->fetch_assoc();
$contrato_fecha = $contrato["fecha"];
$contrato_hora = $contrato["hora"];
$contrato_proveedor = $contrato["fk_proveedor"];
$contrato_usuario = $contrato["fk_usuario"];
$contrato_total = $contrato["total"];
$contrato_saldo = $contrato["saldo"];
$npago = $contrato["pago"];


$anticipo = 0.00;

if ($contrato_pago != null && $contrato_pago != 0) {
    $anticipo = $contrato_pago;
}
#endregion



//PROVEEDOR
#region
$qproveedor = "SELECT * FROM ct_proveedores where pk_proveedor=$contrato_proveedor";

if (!$rproveedor = $mysqli->query($qproveedor)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas4.";
    exit;
}
$proveedor = $rproveedor->fetch_assoc();
$proveedor_nombre = $proveedor["nombre"];
$proveedor_telefono = $proveedor["telefono"];
$proveedor_correo = $proveedor["correo"];
#endregion




//CALCULAR LA ALTURA DEL TICKET
#region
$mysqli->next_result();

if (!$venta_detalle_registros = $mysqli->query("SELECT COUNT(*) as registros FROM tr_compras_detalle WHERE fk_compra = $id AND estado = 1")) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. Error al calcular las dimensiones del ticket";
    exit;
}

$rowvdr = $venta_detalle_registros->fetch_assoc();
$total_registros = $rowvdr['registros'];

$page_height = ($total_registros + 11) * 10 + 170;
#endregion




//CREAR PDF
#region
$pageLayout = array(80, $page_height);
$pdf = new TCPDF('P', PDF_UNIT, $pageLayout, true, 'UTF-8', false);

$pdf->SetCreator("DIMANTTI");
$pdf->SetAuthor('DIMANTTI');
$pdf->SetTitle("COMPRA #" . $id);

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
                <h3 style="text-align: center;">COMPRA #$id</h3>
                <h5 style="text-align: center; font-weight: light;">$empresa_direccion</h5>
                <h5 style="text-align: center; font-weight: light;">Tel $empresa_telefono</h5>
                <h5 style="text-align: center; font-weight: light;">$contrato_fecha $contrato_hora</h5>
                <h5 style="text-align: center; font-weight: light;"> <span style="font-weight: bold;">PROVEEDOR:</span> $proveedor_nombre ($proveedor_telefono)</h5>
            </td>
        </tr>
    </table>
    HTML;
#endregion




//CODIGO QR
#region
$url = "https://dimantti.integracontrol.online/portal/compraPDF.php?id=$id";
$qr_filename = "compra_qr.png";
QRcode::png($url, $qr_filename);
#endregion




//REGISTROS
#region
$qentradasd = "SELECT tr_compras_detalle.*,
        ct_productos.clave
    FROM tr_compras_detalle, ct_productos
    WHERE tr_compras_detalle.fk_compra = $id
    AND ct_productos.pk_producto = tr_compras_detalle.fk_producto";

$mysqli->next_result();
if (!$rentradasd = $mysqli->query($qentradasd)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. Error al obtener el detalle de la compra";
    exit;
}

$cantidad_total = 0;
$tr_table = "";
while ($row = $rentradasd->fetch_assoc()) {

    $cantidad_total += $row['cantidad'];

    $tr_table .= <<<HTML
        <tr>
            <td style="width: 15%;">$row[cantidad]</td>
            <td style="width: 60%;">$row[fk_producto_nombre]</td>
            <td style="width: 25%; text-align: right;">$$row[unitario]</td>
        </tr>
    HTML;
}

$contrato_total = number_format($contrato_total, 2);

$content_table = <<<HTML
    <table border="1" cellpadding="2" style="text-align: center; font-size: 10px;">
        <thead>
            <tr style="font-weight: bold;">
                <th style="width: 15%;">#</th>
                <th style="width: 60%;">PRODUCTO</th>
                <th style="width: 25%;">PRECIO</th>
            </tr>
        </thead>
        <tbody>
            $tr_table
            <tr>
                <td colspan="3" style="text-align: left;">Cantidad de productos: $cantidad_total</td>
            </tr>
            <tr>
                <td colspan="3" style="text-align: left;">Método de pago: $npago</td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" style="width: 75%; text-align: right;">TOTAL:</td>
                <td colspan="1" style="width: 25%; text-align: right;">$$contrato_total</td>
            </tr>
            <tr>
                <td colspan="2" style="width: 75%; text-align: right;">SALDO:</td>
                <td colspan="1" style="width: 25%; text-align: right;">$$contrato_saldo</td>
            </tr>
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
$pdf->writeHTMLCell(70, '', 5, 70, $content_table);
#endregion




//DETALLES FINALES
#region
// reset pointer to the last page
$pdf->lastPage();

// Obtener el ancho y alto de la página, teniendo en cuenta los márgenes
$anchoPagina = $pdf->getPageWidth() - 8 - 8;
$altoPagina = $pdf->getPageHeight() - 8 - 8;

//Close and output PDF document
$fileName = "Compra_" . $id . ".pdf";
$pdf->Output($fileName, 'I');
#endregion
