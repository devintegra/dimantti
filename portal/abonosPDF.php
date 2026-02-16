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
$qentrada = "SELECT * FROM tr_compras WHERE pk_compra = $id";

if (!$rentrada = $mysqli->query($qentrada)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.1";
    exit;
}

$entrada = $rentrada->fetch_assoc();
$entrada_fecha = $entrada["fecha"];
$entrada_hora = $entrada["hora"];
$entrada_pago = $entrada["fk_pago"];
$entrada_fk_sucursal = $entrada["fk_sucursal"];
$entrada_proveedor = $entrada["fk_proveedor"];
$entrada_total = $entrada["total"];
#endregion



//PROVEEDOR
#region
$qproveedor = "SELECT * FROM ct_proveedores where pk_proveedor = $entrada_proveedor";

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

if (!$venta_detalle_registros = $mysqli->query("SELECT COUNT(*) as registros FROM tr_cargos WHERE fk_documento = $id AND estado = 1")) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. Error al calcular las dimensiones del ticket";
    exit;
}

$rowvdr = $venta_detalle_registros->fetch_assoc();
$total_registros = $rowvdr['registros'];

$page_height = ($total_registros + 11) * 10 + 140;
#endregion




//CREAR PDF
#region
$pageLayout = array(80, $page_height);
$pdf = new TCPDF('P', PDF_UNIT, $pageLayout, true, 'UTF-8', false);

$pdf->SetCreator("DIMANTTI");
$pdf->SetAuthor('DIMANTTI');
$pdf->SetTitle("ABONOS COMPRA #" . $id);

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
                <h3 style="text-align: center;">ABONOS COMPRA #$id</h3>
                <h5 style="text-align: center; font-weight: light;">$empresa_direccion</h5>
                <h5 style="text-align: center; font-weight: light;">Tel $empresa_telefono</h5>
                <h5 style="text-align: center; font-weight: light;">$entrada_fecha $entrada_hora</h5>
                <h5 style="text-align: center; font-weight: light;"> <span style="font-weight: bold;">PROVEEDOR:</span> $proveedor_nombre ($proveedor_telefono)</h5>
            </td>
        </tr>
    </table>
    HTML;
#endregion




//REGISTROS
#region
$qentradasd = "SELECT * FROM tr_cargos WHERE fk_documento = $id AND estado = 1 ORDER BY pk_cargo";

$mysqli->next_result();
if (!$rentradasd = $mysqli->query($qentradasd)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. Error al obtener el detalle de la compra";
    exit;
}

$total = 0;
$npago = 1;
$sb = 0;
$tr_table = "";

while ($row = $rentradasd->fetch_assoc()) {

    if ($npago == 1) {
        $sb = number_format($entrada_total, 2);
    }

    $saldo = number_format($row['saldo'], 2);
    $cantidad = number_format($row['cantidad'], 2);

    $tr_table .= <<<HTML
        <tr>
            <td style="width: 25%;">$row[fecha]</td>
            <td style="width: 25%; text-align: right;">$$sb</td>
            <td style="width: 25%; text-align: right;">$$saldo</td>
            <td style="width: 25%; text-align: right;">$$cantidad</td>
        </tr>
    HTML;

    $npago++;
    $sb = $row["saldo"];
    $total = $total + $row["cantidad"];
}

$contrato_total = number_format($total, 2);
$contrato_saldo = number_format($sb, 2);

$content_table = <<<HTML
    <table border="1" cellpadding="2" style="text-align: center; font-size: 10px;">
        <thead>
            <tr style="font-weight: bold;">
                <th style="width: 25%;">FECHA</th>
                <th style="width: 25%;">SALDO ANT.</th>
                <th style="width: 25%;">SALDO ACT.</th>
                <th style="width: 25%;">MONTO</th>
            </tr>
        </thead>
        <tbody>
            $tr_table
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="width: 75%; text-align: right;">TOTAL:</td>
                <td colspan="1" style="width: 25%; text-align: right;">$$contrato_total</td>
            </tr>
            <tr>
                <td colspan="3" style="width: 75%; text-align: right;">SALDO:</td>
                <td colspan="1" style="width: 25%; text-align: right;">$$contrato_saldo</td>
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
$fileName = "Abonos_Compra_" . $id . ".pdf";
$pdf->Output($fileName, 'I');
#endregion
