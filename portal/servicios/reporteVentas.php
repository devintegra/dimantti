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

if (isset($_GET['cliente']) && is_numeric($_GET['cliente'])) {
    $cliente = (int)$_GET['cliente'];
}

if (isset($_GET['vendedor']) && is_string($_GET['vendedor'])) {
    $fk_usuario = $_GET['vendedor'];
}

if (isset($_GET['pago']) && is_numeric($_GET['pago'])) {
    $pago = (int)$_GET['pago'];
}


//FILTROS
#region
$flfechas = "";
$flsucursal = "";
$flcliente = "";
$flusuario = "";
$flpago = "";

if ($inicio != "" && $fin != "") {
    $flfechas = " AND tr_ventas.fecha BETWEEN '$inicio' AND '$fin'";
}

if ($sucursal != 0) {
    $flsucursal = " AND tr_ventas.fk_sucursal = $sucursal";
}

if ($cliente != 0) {
    $flcliente = " AND tr_ventas.fk_cliente = $cliente";
}

if ($fk_usuario != '0') {
    $flusuario = " AND tr_ventas.fk_usuario = '$fk_usuario'";
}

if ($pago != 0) {

    switch ($pago) {
        case 1:
            $metodo = "efectivo";
            break;
        case 2:
            $metodo = "transferencia";
            break;
        case 3:
            $metodo = "debito";
            break;
        case 4:
            $metodo = "cheque";
            break;
        case 5:
            $metodo = "credito";
            break;
    }

    $flpago = " AND tr_ventas.$metodo > 0";
}
#endregion



$qventas = "SELECT tr_ventas.*,
	ct_sucursales.nombre as sucursal,
    ct_clientes.nombre as cliente
    FROM tr_ventas, ct_sucursales, ct_clientes
    WHERE ct_sucursales.pk_sucursal = tr_ventas.fk_sucursal
    AND ct_clientes.pk_cliente = tr_ventas.fk_cliente$flfechas $flsucursal $flcliente $flusuario $flpago";



if (!$rventas = $mysqli->query($qventas)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas." . $mysqli->error;
    exit;
}



$paso = 4;
// Crea un nuevo objeto PHPExcel
$objPHPExcel = new PHPExcel();

// Establecer propiedades
$objPHPExcel->getProperties()
    ->setCreator("Integra Connective")
    ->setLastModifiedBy("Integra Connective")
    ->setTitle("Reporte Dimantti Ventas")
    ->setSubject("Ventas")
    ->setDescription("Ventas")
    ->setKeywords("Ventas")
    ->setCategory("Ventas");


// Agregar Informacion
$objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue('A3', 'Fecha')
    ->setCellValue('B3', 'Sucursal')
    ->setCellValue('C3', 'Folio')
    ->setCellValue('D3', 'Cliente')
    ->setCellValue('E3', 'Vendedor')
    ->setCellValue('F3', 'Estatus')
    ->setCellValue('G3', 'Efectivo')
    ->setCellValue('H3', 'Crédito')
    ->setCellValue('I3', 'Debito')
    ->setCellValue('J3', 'Transferencia')
    ->setCellValue('K3', 'Cheque')
    ->setCellValue('L3', 'Subtotal')
    ->setCellValue('M3', 'Descuento')
    ->setCellValue('N3', 'Comisión')
    ->setCellValue('O3', 'Anticipo')
    ->setCellValue('P3', 'Total');



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
$objPHPExcel->getActiveSheet()->getStyle('A2:A3')->applyFromArray($styleArrayHeaders);


$objPHPExcel->getActiveSheet()->setCellValue('A1', "DIMANTTI. Integra Connective");


$objPHPExcel->getActiveSheet()->getStyle('A3:P3')->applyFromArray($styleArrayHeaders);
$objPHPExcel->getActiveSheet()->getStyle('A3:P3')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('000000');


$total_efectivo = 0.00;
$total_credito = 0.00;
$total_debito = 0.00;
$total_transferencia = 0.00;
$total_cheque = 0.00;
$total_final = 0.00;


while ($roweusuario = $rventas->fetch_assoc()) {

    if ($roweusuario['estatus'] == 2) {
        $estatus = "Devuelta";
        $objPHPExcel->getActiveSheet()->getStyle('F' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('BFDBFE');
    } else if ($roweusuario['estatus'] == 3) {
        $estatus = "Cancelada";
        $objPHPExcel->getActiveSheet()->getStyle('F' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FECACA');
    } else {
        $estatus = "Venta";
        $objPHPExcel->getActiveSheet()->getStyle('F' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('A8F991');

        $total_efectivo += $roweusuario["efectivo"];
        $total_credito += $roweusuario["credito"];
        $total_debito += $roweusuario["debito"];
        $total_transferencia += $roweusuario["transferencia"];
        $total_cheque += $roweusuario["cheque"];
        $total_final += $roweusuario["total"];
    }

    $objPHPExcel->getActiveSheet()->setCellValue('A' . $paso, $roweusuario["fecha"] . " " . $roweusuario["hora"]);
    $objPHPExcel->getActiveSheet()->setCellValue('B' . $paso, $roweusuario["sucursal"]);
    $objPHPExcel->getActiveSheet()->setCellValue('C' . $paso, $roweusuario["folio"]);
    $objPHPExcel->getActiveSheet()->setCellValue('D' . $paso, $roweusuario["cliente"]);
    $objPHPExcel->getActiveSheet()->setCellValue('E' . $paso, $roweusuario["fk_usuario"]);
    $objPHPExcel->getActiveSheet()->setCellValue('F' . $paso, $estatus);
    $objPHPExcel->getActiveSheet()->setCellValue('G' . $paso, "$" . number_format($roweusuario["efectivo"], 2));
    $objPHPExcel->getActiveSheet()->setCellValue('H' . $paso, "$" . number_format($roweusuario["credito"], 2));
    $objPHPExcel->getActiveSheet()->setCellValue('I' . $paso, "$" . number_format($roweusuario["debito"], 2));
    $objPHPExcel->getActiveSheet()->setCellValue('J' . $paso, "$" . number_format($roweusuario["transferencia"], 2));
    $objPHPExcel->getActiveSheet()->setCellValue('K' . $paso, "$" . number_format($roweusuario["cheque"], 2));
    $objPHPExcel->getActiveSheet()->setCellValue('L' . $paso, "$" . number_format($roweusuario["subtotal"], 2));
    $objPHPExcel->getActiveSheet()->setCellValue('M' . $paso, "$" . number_format($roweusuario["descuento"], 2));
    $objPHPExcel->getActiveSheet()->setCellValue('N' . $paso, "$" . number_format($roweusuario["comision"], 2));
    $objPHPExcel->getActiveSheet()->setCellValue('O' . $paso, "$" . number_format($roweusuario["anticipo"], 2));
    $objPHPExcel->getActiveSheet()->setCellValue('P' . $paso, "$" . number_format($roweusuario["total"], 2));

    $paso++;
}


$objPHPExcel->getActiveSheet()->getStyle('G1:P' . $paso)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

$objPHPExcel->getActiveSheet()->getStyle('G' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('BFDBFE');
$objPHPExcel->getActiveSheet()->getStyle('H' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('BBF7B0');
$objPHPExcel->getActiveSheet()->getStyle('I' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FEF08A');
$objPHPExcel->getActiveSheet()->getStyle('J' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('CB9BDE');
$objPHPExcel->getActiveSheet()->getStyle('K' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFBA6A');
$objPHPExcel->getActiveSheet()->getStyle('O' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('A8F991');
$objPHPExcel->getActiveSheet()->getStyle('P' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('A8F991');

$objPHPExcel->getActiveSheet()->setCellValue('O' . $paso, "TOTAL: ");

$objPHPExcel->getActiveSheet()->setCellValue('G' . $paso, "$" . number_format($total_efectivo, 2));
$objPHPExcel->getActiveSheet()->setCellValue('H' . $paso, "$" . number_format($total_credito, 2));
$objPHPExcel->getActiveSheet()->setCellValue('I' . $paso, "$" . number_format($total_debito, 2));
$objPHPExcel->getActiveSheet()->setCellValue('J' . $paso, "$" . number_format($total_transferencia, 2));
$objPHPExcel->getActiveSheet()->setCellValue('K' . $paso, "$" . number_format($total_cheque, 2));
$objPHPExcel->getActiveSheet()->setCellValue('P' . $paso, "$" . number_format($total_final, 2));



$objPHPExcel->getActiveSheet()->setTitle('Reporte ventas');


$objPHPExcel->setActiveSheetIndex(0);


foreach (range('A', 'P') as $columnID) {
    $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
        ->setAutoSize(true);
}

$objPHPExcel->getActiveSheet()->getStyle('A1:F' . $paso)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);


// Se modifican los encabezados del HTTP para indicar que se envia un archivo de Excel.
// Redirect output to a client’s web browser (Excel5)

$ahora = new DateTime();
$id_file = "reporte_ventas_" . $ahora->getTimestamp() . ".xls";





header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment;filename="' . $id_file . '"');
header('Cache-Control: max-age=0');



$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');



/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
