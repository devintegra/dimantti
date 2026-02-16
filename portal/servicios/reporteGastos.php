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

if (isset($_GET['gasto']) && is_numeric($_GET['gasto'])) {
    $gasto = (int)$_GET['gasto'];
}

if (isset($_GET['usuario']) && is_string($_GET['usuario'])) {
    $fk_usuario = $_GET['usuario'];
}


//FILTROS
#region
$flfechas = "";
$flsucursal = "";
$flgasto = "";
$flusuario = "";

if ($inicio != "" && $fin != "") {
    $flfechas = " AND tr_retiros.fecha BETWEEN '$inicio' AND '$fin'";
}

if ($sucursal != 0) {
    $flsucursal = " AND tr_retiros.fk_sucursal = $sucursal";
}

if ($gasto != 0) {
    $flgasto = " AND tr_retiros.fk_retiro = $gasto";
}

if ($fk_usuario != '0') {
    $flusuario = " AND tr_retiros.fk_usuario = '$fk_usuario'";
}
#endregion



$qgastos = "SELECT tr_retiros.*,
    ct_sucursales.nombre as sucursal,
	ct_retiros.nombre as gasto,
    ct_pagos.nombre as pago
    FROM tr_retiros, ct_sucursales, ct_retiros, ct_pagos
    WHERE tr_retiros.estado = 1
    AND ct_retiros.pk_retiro = tr_retiros.fk_retiro
    AND ct_sucursales.pk_sucursal = tr_retiros.fk_sucursal
    AND ct_pagos.pk_pago = tr_retiros.fk_pago$flfechas $flsucursal $flgasto $flusuario";



if (!$rgastos = $mysqli->query($qgastos)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}



$paso = 4;
$total = 0.00;
// Crea un nuevo objeto PHPExcel
$objPHPExcel = new PHPExcel();

// Establecer propiedades
$objPHPExcel->getProperties()
    ->setCreator("Integra Connective")
    ->setLastModifiedBy("Integra Connective")
    ->setTitle("Reporte Gastos")
    ->setSubject("Gastos")
    ->setDescription("Gastos")
    ->setKeywords("Gastos")
    ->setCategory("Gastos");


// Agregar Informacion
$objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue('A3', 'Sucursal')
    ->setCellValue('B3', 'Usuario')
    ->setCellValue('C3', 'Gasto')
    ->setCellValue('D3', 'Observaciones')
    ->setCellValue('E3', 'Pago')
    ->setCellValue('F3', 'Fecha')
    ->setCellValue('G3', 'Monto');



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


$objPHPExcel->getActiveSheet()->getStyle('A3:G3')->applyFromArray($styleArrayHeaders);
$objPHPExcel->getActiveSheet()->getStyle('A3:G3')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('000000');


while ($roweusuario = $rgastos->fetch_assoc()) {

    $objPHPExcel->getActiveSheet()->setCellValue('A' . $paso, $roweusuario["sucursal"]);
    $objPHPExcel->getActiveSheet()->setCellValue('B' . $paso, $roweusuario["fk_usuario"]);
    $objPHPExcel->getActiveSheet()->setCellValue('C' . $paso, $roweusuario["gasto"]);
    $objPHPExcel->getActiveSheet()->setCellValue('D' . $paso, $roweusuario["descripcion"]);
    $objPHPExcel->getActiveSheet()->setCellValue('E' . $paso, $roweusuario["pago"]);
    $objPHPExcel->getActiveSheet()->setCellValue('F' . $paso, $roweusuario["fecha"] . " " . $roweusuario["hora"]);
    $objPHPExcel->getActiveSheet()->setCellValue('G' . $paso, "$" . number_format($roweusuario["monto"], 2));

    $total += $roweusuario["monto"];

    $paso++;
}


$objPHPExcel->getActiveSheet()->getStyle('G1:G' . $paso)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('F' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('A8F991');
$objPHPExcel->getActiveSheet()->setCellValue('F' . $paso, "TOTAL: ");
$objPHPExcel->getActiveSheet()->setCellValue('G' . $paso, "$" . number_format($total, 2));
$objPHPExcel->getActiveSheet()->getStyle('G' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('A8F991');



$objPHPExcel->getActiveSheet()->setTitle('Reporte gastos');


$objPHPExcel->setActiveSheetIndex(0);


foreach (range('A', 'G') as $columnID) {
    $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
        ->setAutoSize(true);
}

$objPHPExcel->getActiveSheet()->getStyle('A1:G' . $paso)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);


// Se modifican los encabezados del HTTP para indicar que se envia un archivo de Excel.
// Redirect output to a client’s web browser (Excel5)

$ahora = new DateTime();
$id_file = "reporte_gastos_" . $ahora->getTimestamp() . ".xls";





header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment;filename="' . $id_file . '"');
header('Cache-Control: max-age=0');



$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
