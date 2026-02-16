<?php
header("Access-Control-Allow-Origin: *");
include("servicios/conexioni.php");
require_once('servicios/tcpdf/tcpdf.php');
setlocale(LC_ALL, 'es_ES');
mysqli_set_charset($mysqli, 'utf8');

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
}


//SUCURSAL
#region
$qsucursal = "SELECT * FROM ct_sucursales where pk_sucursal = (select fk_sucursal from tr_inventario where pk_inventario=$id)";

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



//DATOS
#region
$qinventario = "SELECT * FROM tr_inventario WHERE pk_inventario = $id AND estado = 1";

if (!$rinventario = $mysqli->query($qinventario)) {
    echo "<br>Lo sentimos, esta aplicación está experimentando problemas.1";
    exit;
}

$inventario = $rinventario->fetch_assoc();
$inventario_fk_sucursal = $inventario["fk_sucursal"];
$inventario_almacenes = $inventario["almacenes"];
$inventario_categorias = $inventario["categorias"];
$inventario_marcas = $inventario["marcas"];
$inventario_productos = $inventario["productos"];
$inventario_fecha = $inventario["fecha"];
$inventario_hora = $inventario["hora"];
#endregion



//ALMACENES
#region
$almacenes_nombre = "";
if ($inventario_almacenes) {

    $qalmacen = "SELECT GROUP_CONCAT(nombre SEPARATOR ', ') as almacenes FROM rt_sucursales_almacenes where pk_sucursal_almacen in ($inventario_almacenes) and estado = 1;";

    if (!$ralmacen = $mysqli->query($qalmacen)) {
        echo "Lo sentimos, esta aplicación está experimentando problemas1.";
        exit;
    }

    $almacen = $ralmacen->fetch_assoc();
    $almacenes_nombre = $almacen["almacenes"];
}
#endregion



//CREAR PDF
#region
$pageLayout = array(216, 279);
$pdf = new TCPDF('P', PDF_UNIT, $pageLayout, true, 'UTF-8', false);

$pdf->SetCreator("DIMANTTI");
$pdf->SetAuthor('DIMANTTI');
$pdf->SetTitle("REGISTRO DE INVENTARIO #" . $id);

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
                        <h1 style="text-align: center;">REGISTRO DE INVENTARIO</h1>
                    </td>
                    <td>
                        <h4 style="text-align: right; font-weight: light; line-height: 12px;">ID: <span style="font-weight: bold;">$id</span></h4>
                        <h4 style="text-align: right; font-weight: light; line-height: 12px;">Fecha: <span style="font-weight: bold;">$inventario_fecha $inventario_hora</span></h4>
                        <h4 style="text-align: right; font-weight: light; line-height: 12px;">Sucursal: <span style="font-weight: bold;">$empresa_nombre. $almacenes_nombre</span></h4>
                    </td>
                </tr>
            </table>
        </div>
    HTML;
#endregion




//REGISTROS
#region
$qentradasd = "SELECT tr_inventario_detalle.*,
    ct_productos.clave,
    ct_productos.nombre,
    ct_productos.costo
    FROM tr_inventario_detalle, ct_productos
    WHERE tr_inventario_detalle.fk_inventario = $id
    AND ct_productos.pk_producto = tr_inventario_detalle.fk_producto";

if (!$rentradasd = $mysqli->query($qentradasd)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas6.";
    exit;
}

$tr_table = "";
$total = 0.00;

while ($row = $rentradasd->fetch_assoc()) {

    if ($row["existencia_real"] == 0) {
        $estatus = "Convalidado";
        $costo = $row['escaneadas'] * $row['costo'];
    } else if ($row["existencia_real"] > 0) {
        $estatus = "Sobrante";
        $costo = $row['escaneadas'] * $row['costo'];
    } else {
        $estatus = "Faltante";
        $costo = abs((float)$row['existencia_real']) * $row['costo'];
    }

    $costot = number_format($costo, 2);

    $tr_table .= <<<HTML
        <tr>
            <td>$row[clave]</td>
            <td>$row[nombre]</td>
            <td>$row[existencias]</td>
            <td>$row[escaneadas]</td>
            <td>$row[existencia_real]</td>
            <td>$estatus</td>
            <td style="text-align: right;">$$costot</td>
        </tr>
    HTML;

    $total += $costo;
}
#endregion


$total = number_format($total, 2);


$content_table = <<<HTML
    <table border="1" cellpadding="5" style="text-align: center;">
        <thead>
            <tr style="font-weight: bold;">
                <th>CLAVE</th>
                <th>DESC</th>
                <th>E.REGISTRADAS</th>
                <th>E.REALES</th>
                <th>DIFERENCIA</th>
                <th>ESTATUS</th>
                <th>TOTAL</th>
            </tr>
        </thead>
        <tbody>
            $tr_table
        </tbody>
        <tfoot>
            <tr style="text-align: right; font-weight: bold;">
                <td colspan="6">TOTAL</td>
                <td>$$total</td>
            </tr>
        </tfoot>
    </table>
HTML;




$pdf->Image('@' . file_get_contents('servicios/logo.png'), 7, 5, 0, 24);
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
$fileName = "Registro_Inventario_" . $id . ".pdf";
$pdf->Output($fileName, 'I');
#endregion
