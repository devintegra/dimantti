<?php

require_once "Classes/PHPExcel.php";
require "Classes/PHPExcel/Writer/Excel2007.php";
include("conexioni.php");
mysqli_set_charset($mysqli, 'utf8');

if (isset($_GET['inicio']) && is_string($_GET['inicio'])) {
    $inicio = $_GET['inicio'];
}

if (isset($_GET['fin']) && is_string($_GET['fin'])) {
    $fin = $_GET['fin'];
}

if (isset($_GET['sucursal']) && is_numeric($_GET['sucursal'])) {
    $sucursal = (int)$_GET['sucursal'];
}

if (isset($_GET['categoria']) && is_numeric($_GET['categoria'])) {
    $categoria = (int)$_GET['categoria'];
}

if (isset($_GET['proveedor']) && is_numeric($_GET['proveedor'])) {
    $proveedor = (int)$_GET['proveedor'];
}

if (isset($_GET['credito']) && is_numeric($_GET['credito'])) {
    $credito = (int)$_GET['credito'];
}

if (isset($_GET['producto']) && is_string($_GET['producto'])) {
    $clave = $_GET['producto'];
}


//FILTROS
#region
$flfechas = "";
$flsucursal = "";
$flcategoria = "";
$flproveedor = "";
$flcredito = "";
$flproducto = "";

//GENERAL
if ($inicio != "" && $fin != "") {
    $flfechas = " AND tr_compras.fecha BETWEEN '$inicio' AND '$fin'";
}

if ($sucursal != 0) {
    $flsucursal = " AND tr_compras.fk_almacen = $sucursal";
}

if ($proveedor != 0) {
    $flproveedor = " AND tr_compras.fk_proveedor = $proveedor";
}

if ($credito != 0) {
    $flcredito = " AND tr_compras.tipo_pago = $credito";
}

//DETALLE
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



$qcompra = "SELECT tr_compras.pk_compra,
	ct_proveedores.nombre as proveedor,
    ct_sucursales.nombre as sucursal,
    rt_sucursales_almacenes.nombre as almacen,
    tr_compras.fk_usuario,
    tr_compras.fecha,
    ct_pagos.nombre as pago,
    tr_compras.tipo_pago,
    tr_compras.saldo,
    tr_compras.total,
    tr_compras.estatus
    FROM tr_compras, ct_proveedores, ct_sucursales, rt_sucursales_almacenes, ct_pagos
    WHERE tr_compras.estado = 1
    AND ct_proveedores.pk_proveedor = tr_compras.fk_proveedor
    AND ct_sucursales.pk_sucursal = tr_compras.fk_sucursal
    AND rt_sucursales_almacenes.pk_sucursal_almacen = tr_compras.fk_almacen
    AND ct_pagos.pk_pago = tr_compras.fk_pago$flfechas $flproveedor $flsucursal $flcredito";



if (!$rcompra = $mysqli->query($qcompra)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}



$paso = 4;
$subtotal = 0.00;
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
    ->setCellValue('A3', 'Fecha')
    ->setCellValue('B3', 'Sucursal')
    ->setCellValue('C3', 'Proveedor')
    ->setCellValue('D3', 'Usuario')
    ->setCellValue('E3', 'Pago')
    ->setCellValue('F3', 'Crédito')
    ->setCellValue('G3', 'Saldo')
    ->setCellValue('H3', 'Total')
    ->setCellValue('I3', 'Códgio')
    ->setCellValue('J3', 'Producto')
    ->setCellValue('K3', 'Cantidad')
    ->setCellValue('L3', 'Unitario')
    ->setCellValue('M3', 'Subtotal');



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
        'color' => array('rgb' => 'ffffff'),
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
$objPHPExcel->getActiveSheet()->setCellValue('A1', "DIMANTTI. Integra Connective");


$objPHPExcel->getActiveSheet()->getStyle('A3:M3')->applyFromArray($styleArrayHeaders);
$objPHPExcel->getActiveSheet()->getStyle('A3:H3')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('000000');
$objPHPExcel->getActiveSheet()->getStyle('I3:M3')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('0293ad');
$objPHPExcel->getActiveSheet()->getStyle('A5:M5')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('F5F5F1');



while ($roweusuario = $rcompra->fetch_assoc()) {

    //DETALLE
    #region
    $qdetalle = "SELECT tr_compras_detalle.pk_compra_detalle,
        ct_productos.codigobarras,
        ct_productos.nombre,
        ct_productos.descripcion,
        tr_compras_detalle.cantidad,
        tr_compras_detalle.unitario,
        tr_compras_detalle.total
        FROM tr_compras_detalle, ct_productos
        WHERE tr_compras_detalle.fk_compra = $roweusuario[pk_compra]
        AND ct_productos.pk_producto = tr_compras_detalle.fk_producto$flcategoria $flproducto";


    if (!$rdetalle = $mysqli->query($qdetalle)) {
        echo "Lo sentimos, esta aplicación está experimentando problemas.";
        exit;
    }

    if ($rdetalle->num_rows > 0) {

        //Tipo de crédito
        #region
        if ($roweusuario["tipo_pago"] == 1) {
            $credito = "Total";
        } else if ($roweusuario["tipo_pago"] == 2) {
            $credito = "Parcial";
        } else {
            $credito = "Crédito";
        }
        #endregion


        //Saldo
        #region
        if ($roweusuario["saldo"] > 0) {
            $objPHPExcel->getActiveSheet()->getStyle('G' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FF7676');
        } else {
            $objPHPExcel->getActiveSheet()->getStyle('G' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('fef58d');
        }
        #endregion


        $objPHPExcel->getActiveSheet()->setCellValue('A' . $paso, $roweusuario["fecha"]);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $paso, $roweusuario["sucursal"] . ". " .  $roweusuario["almacen"]);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $paso, $roweusuario["proveedor"]);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $paso, $roweusuario["fk_usuario"]);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $paso, $roweusuario["pago"]);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $paso, $credito);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $paso, "$" . number_format($roweusuario["saldo"], 2));
        $objPHPExcel->getActiveSheet()->setCellValue('H' . $paso, "$" . number_format($roweusuario["total"], 2));

        $objPHPExcel->getActiveSheet()->getStyle('H' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('A8F991');



        while ($rowdetalle = $rdetalle->fetch_assoc()) {

            $objPHPExcel->getActiveSheet()->setCellValue('I' . $paso, $rowdetalle["codigobarras"]);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $paso, $rowdetalle["nombre"]);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . $paso, $rowdetalle["cantidad"]);
            $objPHPExcel->getActiveSheet()->setCellValue('L' . $paso, "$" . number_format($rowdetalle["unitario"], 2));
            $objPHPExcel->getActiveSheet()->setCellValue('M' . $paso, "$" . number_format($rowdetalle["total"], 2));

            $subtotal += $rowdetalle["total"];

            $paso++;
        }
        $objPHPExcel->getActiveSheet()->setCellValue('M' . $paso, "$" . number_format($subtotal, 2));
        $objPHPExcel->getActiveSheet()->getStyle('M' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('A8F991');
        $subtotal = 0;
        #endregion

        $paso += 2;
        $objPHPExcel->getActiveSheet()->getStyle('A' . $paso . ':M' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('F5F5F1');
    }
}




$objPHPExcel->getActiveSheet()->setTitle('Reporte compras');


$objPHPExcel->setActiveSheetIndex(0);

$objPHPExcel->getActiveSheet()->getColumnDimension('A')
    ->setAutoSize(true);

$objPHPExcel->getActiveSheet()->getColumnDimension('B')
    ->setAutoSize(true);

$objPHPExcel->getActiveSheet()->getColumnDimension('C')
    ->setAutoSize(true);

$objPHPExcel->getActiveSheet()->getColumnDimension('D')
    ->setAutoSize(true);

$objPHPExcel->getActiveSheet()->getColumnDimension('E')
    ->setAutoSize(true);

$objPHPExcel->getActiveSheet()->getColumnDimension('F')
    ->setAutoSize(true);

$objPHPExcel->getActiveSheet()->getColumnDimension('G')
    ->setAutoSize(true);

$objPHPExcel->getActiveSheet()->getColumnDimension('H')
    ->setAutoSize(true);

$objPHPExcel->getActiveSheet()->getColumnDimension('I')
    ->setAutoSize(true);

$objPHPExcel->getActiveSheet()->getColumnDimension('J')
    ->setAutoSize(true);

$objPHPExcel->getActiveSheet()->getColumnDimension('K')
    ->setAutoSize(true);

$objPHPExcel->getActiveSheet()->getColumnDimension('L')
    ->setAutoSize(true);

$objPHPExcel->getActiveSheet()->getColumnDimension('M')
    ->setAutoSize(true);

$objPHPExcel->getActiveSheet()->getStyle('A1:M' . $paso)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);


// Se modifican los encabezados del HTTP para indicar que se envia un archivo de Excel.
// Redirect output to a client’s web browser (Excel5)

$ahora = new DateTime();
$id_file = "reporte_compras_" . $ahora->getTimestamp() . ".xls";





header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment;filename="' . $id_file . '"');
header('Cache-Control: max-age=0');



$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
