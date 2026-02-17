<?php
header("Access-Control-Allow-Origin: *");
include("servicios/conexioni.php");
require('servicios/tcpdf/tcpdf.php');
require('servicios/phpqrcode/qrlib.php');
mysqli_set_charset($mysqli, 'utf8');

if (isset($_GET['id']) && is_string($_GET['id'])) {
    $id = $_GET['id'];
}


//PRODUCTO
#region
$mysqli->next_result();
if (!$rsp_get_producto = $mysqli->query("CALL sp_get_producto_by_clave('$id')")) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. Error al obtener los datos del producto";
    exit;
}

$rowp = $rsp_get_producto->fetch_assoc();
$nombre = $rowp["nombre"];
#endregion




//CREAR PDF
#region
$pageLayout = array(80, 80);
$pdf = new TCPDF('P', PDF_UNIT, $pageLayout, true, 'UTF-8', false);
//$pdf = new TCPDF('P', PDF_UNIT, $pageLayout, false, 'ISO-8859-1', false);

$pdf->SetCreator("Dimantti");
$pdf->SetAuthor('Dimantti');
$pdf->SetTitle($nombre);

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(5, 5, 5);

$pdf->SetAutoPageBreak(TRUE, 5);

$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
    require_once(dirname(__FILE__) . '/lang/eng.php');
    $pdf->setLanguageArray($l);
}

$fontname = TCPDF_FONTS::addTTFfont('servicios/tcpdf/fonts/quicksand/Quicksand-Regular.ttf', 'TrueTypeUnicode', '');
$pdf->SetFont('dejavusans', '', 10);

$pdf->AddPage();
#endregion




//QR
#region
$url = "https://dimantti.integracontrol.online/portal/verProducto.php?id=$id";
QRcode::png($url, "qrcode.png");

$parrafo_info = <<<HTML
    <table border="0" cellpadding="0" style="text-align: center; font-size: 12px; font-weight: bold;">
        <tr>
            <td>
                $nombre
            </td>
        </tr>
        <tr>
            <td>
                <img src="qrcode.png" style="width: 200px; height: auto;">
            </td>
        </tr>
    </table>
HTML;
#endregion




//IMPRIMIR CONTENIDO
#region
$pdf->writeHTMLCell(76, '', 2, 5, $parrafo_info);
#endregion




//DETALLES FINALES
#region
// reset pointer to the last page
$pdf->lastPage();

// Obtener el ancho y alto de la página, teniendo en cuenta los márgenes
$anchoPagina = $pdf->getPageWidth() - 8 - 8;
$altoPagina = $pdf->getPageHeight() - 8 - 8;

//Close and output PDF document
$fileName = "PRODUCTO_#" . $id . ".pdf";
$pdf->Output($fileName, 'I');
#endregion
