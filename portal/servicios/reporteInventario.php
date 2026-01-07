<?php
require_once "Classes/PHPExcel.php";
require "Classes/PHPExcel/Writer/Excel2007.php";
include("conexioni.php");
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

if (isset($_GET['marca']) && is_numeric($_GET['marca'])) {
    $marca = (int)$_GET['marca'];
}

if (isset($_GET['producto']) && is_string($_GET['producto'])) {
    $clave = $_GET['producto'];
}


//FILTROS
#region
$flfechas = "";
$flsucursal = "";
$flcategoria = "";
$flmarca = "";
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

if (!$rinventario = $mysqli->query($qinventario)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}



$total = 0.00;
// Crea un nuevo objeto PHPExcel
$objPHPExcel = new PHPExcel();

// Establecer propiedades
$objPHPExcel->getProperties()
    ->setCreator("Integra Connective")
    ->setLastModifiedBy("Integra Connective")
    ->setTitle("Reporte Inventario")
    ->setSubject("Inventario")
    ->setDescription("Inventario")
    ->setKeywords("Inventario")
    ->setCategory("Inventario");


// Agregar Informacion
$objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue('A3', 'Sucursal')
    ->setCellValue('B3', 'Código')
    ->setCellValue('C3', 'Producto')
    ->setCellValue('D3', 'Categoría')
    ->setCellValue('E3', 'Fecha')
    ->setCellValue('F3', 'Existencias')
    ->setCellValue('G3', 'Unitario')
    ->setCellValue('H3', 'Total');



$styleArrayTitle = array(
    'font'  => array(
        'bold'  => true,
        'color' => array('rgb' => 'ff7a21'),
        'size'  => 12,
        'name'  => 'Calibri'
    )
);


$styleArrayHeaders = array(
    'font'  => array(
        'bold'  => true,
        'color' => array('rgb' => 'FFFFFF'),
        'size'  => 12,
        'name'  => 'Calibri'
    )
);


$styleArrayHeadersTable = array(
    'font'  => array(
        'bold'  => true,
        'color' => array('rgb' => 'FFFFFF'),
        'size'  => 12,
        'name'  => 'Calibri'
    )
);

$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArrayTitle);
$objPHPExcel->getActiveSheet()->setCellValue('A1', "POSMOVIL. Integra Connective");

$objPHPExcel->getActiveSheet()->getStyle('A3:H3')->applyFromArray($styleArrayHeaders);
$objPHPExcel->getActiveSheet()->getStyle('A3:H3')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('000000');

$paso = 4;

while ($roweusuario = $rinventario->fetch_assoc()) {

    $fecha = explode(' ', $roweusuario["fecha"])[0];
    $subtotal = $roweusuario["cantidad"] * $roweusuario["costo"];

    $objPHPExcel->getActiveSheet()->setCellValue('A' . $paso, $roweusuario["sucursal"] . ". " .  $roweusuario["almacen"]);
    $objPHPExcel->getActiveSheet()->setCellValue('B' . $paso, $roweusuario["codigobarras"]);
    $objPHPExcel->getActiveSheet()->setCellValue('C' . $paso, $roweusuario["descripcion"]);
    $objPHPExcel->getActiveSheet()->setCellValue('D' . $paso, $roweusuario["categoria"]);
    $objPHPExcel->getActiveSheet()->setCellValue('E' . $paso, $fecha);
    $objPHPExcel->getActiveSheet()->setCellValue('F' . $paso, $roweusuario["cantidad"]);
    $objPHPExcel->getActiveSheet()->setCellValue('G' . $paso, "$" . number_format($roweusuario["costo"], 2));
    $objPHPExcel->getActiveSheet()->setCellValue('H' . $paso, "$" . number_format($subtotal, 2));

    $total += $subtotal;

    $paso++;
}


$objPHPExcel->getActiveSheet()->getStyle('G1:G' . $paso)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('H1:H' . $paso)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('G' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('A8F991');
$objPHPExcel->getActiveSheet()->setCellValue('G' . $paso, "TOTAL: ");
$objPHPExcel->getActiveSheet()->setCellValue('H' . $paso, "$" . number_format($total, 2));
$objPHPExcel->getActiveSheet()->getStyle('H' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('A8F991');


$objPHPExcel->getActiveSheet()->setTitle('Reporte inventario');


$objPHPExcel->setActiveSheetIndex(0);


foreach (range('A', 'H') as $columnID) {
    $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
        ->setAutoSize(true);
}

$objPHPExcel->getActiveSheet()->getStyle('A1:F' . $paso)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);


// Se modifican los encabezados del HTTP para indicar que se envia un archivo de Excel.
// Redirect output to a client’s web browser (Excel5)

$ahora = new DateTime();
$id_file = "reporte_inventario_" . $ahora->getTimestamp() . ".xls";





header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment;filename="' . $id_file . '"');
header('Cache-Control: max-age=0');



$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
