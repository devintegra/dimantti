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
$qsucursal = "SELECT * FROM ct_sucursales where pk_sucursal = (select fk_sucursal from tr_entradas where pk_entrada=$id)";

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
$qsalida = "SELECT tr_salidas.pk_salida as pk_salida,
    tr_salidas.total_monetario as total_monetario,
    tr_salidas.fk_usuario,
    tr_salidas.fecha as fecha,
    tr_salidas.hora,
    ct_motivos_salida.nombre as motivo_salida,
    tr_salidas.observaciones as observaciones
    FROM tr_salidas, ct_motivos_salida
    WHERE tr_salidas.pk_salida = $id
    AND tr_salidas.estado = 1
    AND tr_salidas.fk_motivo = ct_motivos_salida.pk_motivo_salida";

if (!$rsalida = $mysqli->query($qsalida)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas2.";
    exit;
}

$salida = $rsalida->fetch_assoc();
$salida_fk_salida = $salida["pk_salida"];
$salida_total = $salida["total_monetario"];
$salida_fk_usuario = $salida["fk_usuario"];
$salida_fecha = $salida["fecha"];
$salida_hora = $salida["hora"];
$salida_motivo = $salida["motivo_salida"];
$salida_observaciones = $salida["observaciones"];
#endregion



//CALCULAR LA ALTURA DEL TICKET
#region
$mysqli->next_result();

if (!$venta_detalle_registros = $mysqli->query("SELECT COUNT(*) as registros FROM tr_salidas_detalle WHERE fk_salida = $id AND estado = 1")) {
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

$pdf->SetCreator("POSMOVIL");
$pdf->SetAuthor('POSMOVIL');
$pdf->SetTitle("SALIDA #" . $id);

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
                <h3 style="text-align: center;">SALIDA #$id</h3>
                <h5 style="text-align: center; font-weight: light;">$empresa_direccion</h5>
                <h5 style="text-align: center; font-weight: light;">Tel $empresa_telefono</h5>
                <h5 style="text-align: center; font-weight: light;">$salida_fecha $salida_hora</h5>
            </td>
        </tr>
    </table>
    HTML;
#endregion




//CODIGO QR
#region
$url = "https://posmovil.integracontrol.online/portal/salidaPDF.php?id=$id";
$qr_filename = "entrada_qr.png";
QRcode::png($url, $qr_filename);
#endregion




//REGISTROS
#region
$qentradasd = "SELECT tr_salidas_detalle.pk_salida_detalle as pk_salida_detalle,
    tr_salidas_detalle.fk_producto as fk_insumo,
    ct_productos.clave as clave,
    ct_productos.nombre as descripcion,
    tr_salidas_detalle.serie,
    ct_sucursales.nombre as sucursal,
    tr_salidas_detalle.cantidad as cantidad,
    tr_salidas_detalle.unitario as costo,
    tr_salidas_detalle.total as total
    FROM tr_salidas_detalle, ct_productos, ct_sucursales
    WHERE tr_salidas_detalle.fk_salida = $id
    AND tr_salidas_detalle.estado = 1
    AND ct_productos.pk_producto = tr_salidas_detalle.fk_producto
    AND ct_sucursales.pk_sucursal = tr_salidas_detalle.fk_sucursal";

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
            <td style="width: 60%;">($row[clave]). $row[descripcion]</td>
            <td style="width: 25%; text-align: right;">$$row[costo]</td>
        </tr>
    HTML;
}

$contrato_total = number_format($salida_total, 2);

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
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" style="width: 75%; text-align: right;">TOTAL:</td>
                <td colspan="1" style="width: 25%; text-align: right;">$$contrato_total</td>
            </tr>
            <tr>
                <td colspan="3" style="text-align: left;">OBSERVACIONES: $salida_observaciones</td>
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
$fileName = "Salida_" . $id . ".pdf";
$pdf->Output($fileName, 'I');
#endregion
