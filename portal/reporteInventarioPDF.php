<?php
header("Access-Control-Allow-Origin: *");
include("servicios/conexioni.php");
require('servicios/tcpdf/tcpdf.php');
require('servicios/phpqrcode/qrlib.php');
mysqli_set_charset($mysqli, 'utf8');



// if (isset($_GET['inicio']) && is_string($_GET['inicio'])) {
//     $inicio = $_GET['inicio'];
// }

// if (isset($_GET['fin']) && is_string($_GET['fin'])) {
//     $fin = $_GET['fin'];
// }

if (isset($_GET['sucursal']) && is_numeric($_GET['sucursal'])) {
    $sucursal = (int)$_GET['sucursal'];
}

if (isset($_GET['categoria']) && is_numeric($_GET['categoria'])) {
    $categoria = (int)$_GET['categoria'];
}

if (isset($_GET['producto']) && is_string($_GET['producto'])) {
    $clave = $_GET['producto'];
}


//FILTROS
#region
$flfechas = "";
$flsucursal = "";
$flcategoria = "";
$flproducto = "";

// if ($inicio != "" && $fin != "") {
//     $flfechas = " AND tr_existencias.fecha BETWEEN '$inicio' AND '$fin'";
// }

if ($sucursal != 0) {
    $flsucursal = " AND tr_existencias.fk_sucursal = $sucursal";
}

if ($categoria != 0) {
    $flcategoria = " AND ct_productos.fk_categoria = $categoria";
}

if ($clave != "") {

    $claves = array();
    $ex = (explode(',', $clave));

    foreach ($ex as $key => $value) {
        array_push($claves, '"' . $value . '"');
    }

    $join = implode(',', $claves);

    $flproducto = " AND ct_productos.codigobarras in ($join)";
}
#endregion



$qinventario = "SELECT ct_productos.pk_producto as pk_producto,
    ct_productos.clave as clave,
    ct_productos.nombre as descripcion,
    ct_productos.codigobarras,
    ct_productos.costo,
    ct_categorias.nombre as categoria,
    ct_sucursales.nombre as sucursal,
    rt_sucursales_almacenes.nombre as almacen,
    SUM(tr_existencias.cantidad) as cantidad,
    tr_existencias.serie as serie,
    tr_existencias.fecha
    FROM ct_productos, tr_existencias, ct_sucursales, rt_sucursales_almacenes, ct_categorias
    WHERE tr_existencias.fk_producto = ct_productos.pk_producto
    AND tr_existencias.cantidad >= 0
    AND ct_sucursales.pk_sucursal = tr_existencias.fk_sucursal
    AND rt_sucursales_almacenes.pk_sucursal_almacen = tr_existencias.fk_almacen
    AND ct_categorias.pk_categoria = ct_productos.fk_categoria$flsucursal $flcategoria $flproducto
    GROUP BY tr_existencias.fk_producto, tr_existencias.fk_almacen
    ORDER BY tr_existencias.fk_sucursal, tr_existencias.fk_almacen";



//SUCURSAL
#region
if ($sucursal != 0) {

    $qsucursal = "SELECT * FROM ct_sucursales where pk_sucursal = $sucursal";

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
} else {

    $empresa_nombre = "Tectron";
    $empresa_id = "";
    $empresa_direccion = "Todas las sucursales";
    $empresa_telefono = "";
    $empresa_correo = "";
}
#endregion






//CREAR PDF
#region
$pageLayout = array(216, 279);
$pdf = new TCPDF('L', PDF_UNIT, $pageLayout, true, 'UTF-8', false);

$pdf->SetCreator("POSMOVIL");
$pdf->SetAuthor('POSMOVIL');
$pdf->SetTitle("REPORTE INVENTARIO");

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
$currentPage = $pdf->getAliasNumPage();
$totalPages = $pdf->getAliasNbPages();
#endregion






//REGISTROS
if (!$rinventario = $mysqli->query($qinventario)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

$registro_tabla = "";

$total_final = 0;

while ($row = $rinventario->fetch_assoc()) {

    $fecha = explode(' ', $row["fecha"])[0];
    $costo = number_format($row['costo'], 2);
    $subtotal = number_format($row["cantidad"] * $row["costo"], 2);

    $registro_tabla .= <<<HTML
        <tr>
            <td style="width: 9.2%;">$row[sucursal]. $row[almacen]</td>
            <td style="width: 9.2%;">$row[codigobarras]</td>
            <td style="width: 30%;">$row[descripcion]</td>
            <td style="width: 13.8%;">$row[categoria]</td>
            <td style="width: 13.8%;">$fecha</td>
            <td style="width: 8%;">$row[cantidad]</td>
            <td style="width: 8%;">$$costo</td>
            <td style="width: 8%;">$$subtotal</td>
        </tr>
    HTML;

    $total_final += ($row["cantidad"] * $row["costo"]);
}

$total_final = number_format($total_final, 2);

$parrafo_table = <<<HTML
    <div>
        <table border="1" cellpadding="7" style="font-size: 9px;">
            <thead>
                <tr style="font-weight: bold;">
                    <td style="width: 9.2%;">Sucursal</td>
                    <td style="width: 9.2%;">Código</td>
                    <td style="width: 30%;">Producto</td>
                    <td style="width: 13.8%;">Categoría</td>
                    <td style="width: 13.8%;">Fecha</td>
                    <td style="width: 8%;">Cant.</td>
                    <td style="width: 8%;">Unitario</td>
                    <td style="width: 8%;">Total</td>
                </tr>
            </thead>
            <tbody>
                $registro_tabla
            </tbody>
            <tfoot>
                <tr style="font-weight: bold; text-align: right;">
                    <td colspan="6">TOTAL:</td>
                    <td colspan="2">$$total_final</td>
                </tr>
            </tfoot>
        </table>
    </div>
HTML;



//IMPRIMIR CONTENIDO
#region
$pdf->Image('@' . file_get_contents('servicios/logo.png'), 20, 5, 0, 15);
$pdf->writeHTMLCell(269, '', 5, 25, $parrafo_table);
#endregion




//DETALLES FINALES
#region
// reset pointer to the last page
$pdf->lastPage();

// Obtener el ancho y alto de la página, teniendo en cuenta los márgenes
$anchoPagina = $pdf->getPageWidth() - 8 - 8;
$altoPagina = $pdf->getPageHeight() - 8 - 8;

//Close and output PDF document
$fileName = "Reporte_Inventario.pdf";
$pdf->Output($fileName, 'I');
#endregion
