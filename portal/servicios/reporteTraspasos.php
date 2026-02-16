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

if (isset($_GET['origen']) && is_numeric($_GET['origen'])) {
    $sucursal_origen = (int)$_GET['origen'];
}

if (isset($_GET['destino']) && is_numeric($_GET['destino'])) {
    $sucursal_destino = (int)$_GET['destino'];
}

if (isset($_GET['producto']) && is_string($_GET['producto'])) {
    $clave = $_GET['producto'];
}


//FILTROS
#region
$flfechas = "";
$florigen = "";
$fldestino = "";
$flproducto = "";

//GENERAL
if ($inicio != "" && $fin != "") {
    $flfechas = " AND tr_transferencias.fecha BETWEEN '$inicio' AND '$fin'";
}

if ($sucursal_origen != 0) {
    $florigen = " AND tr_transferencias.fk_sucursal = $sucursal_origen";
}

if ($sucursal_destino != 0) {
    $fldestino = " AND tr_transferencias.fk_sucursal_destino = $sucursal_destino";
}

//DETALLE
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



$qtransferencia = "SELECT tr_transferencias.pk_transferencia,
	sucursalesa.nombre as origen,
    almacenesa.nombre as almacena,
    sucursalesb.nombre as destino,
    almacenesb.nombre as almacenb,
    tr_transferencias.fk_usuario,
    tr_transferencias.fecha,
    tr_transferencias.total
    FROM tr_transferencias, ct_sucursales as sucursalesa, ct_sucursales as sucursalesb, rt_sucursales_almacenes as almacenesa, rt_sucursales_almacenes as almacenesb
    WHERE tr_transferencias.estado = 1
    AND sucursalesa.pk_sucursal = tr_transferencias.fk_sucursal
    AND sucursalesb.pk_sucursal = tr_transferencias.fk_sucursal_destino
    AND almacenesa.pk_sucursal_almacen = tr_transferencias.fk_almacen
    AND almacenesb.pk_sucursal_almacen = tr_transferencias.fk_almacen_destino$flfechas $florigen $fldestino";



if (!$rtransferencia = $mysqli->query($qtransferencia)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}



$paso = 4;
$subtotal = 0.00;
$estatus = "";
// Crea un nuevo objeto PHPExcel
$objPHPExcel = new PHPExcel();

// Establecer propiedades
$objPHPExcel->getProperties()
    ->setCreator("Integra Connective")
    ->setLastModifiedBy("Integra Connective")
    ->setTitle("Reporte Transferencias")
    ->setSubject("Transferencias")
    ->setDescription("Transferencias")
    ->setKeywords("Transferencias")
    ->setCategory("Transferencias");


// Agregar Informacion
$objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue('A3', 'Fecha')
    ->setCellValue('B3', 'Origen')
    ->setCellValue('C3', 'Destino')
    ->setCellValue('D3', 'Usuario')
    ->setCellValue('E3', 'Código')
    ->setCellValue('F3', 'Producto')
    ->setCellValue('G3', 'Serie')
    ->setCellValue('H3', 'Cantidad')
    ->setCellValue('I3', 'Estatus')
    ->setCellValue('J3', 'Unitario')
    ->setCellValue('K3', 'Total');



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


$objPHPExcel->getActiveSheet()->getStyle('A3:K3')->applyFromArray($styleArrayHeaders);
$objPHPExcel->getActiveSheet()->getStyle('A3:D3')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('000000');
$objPHPExcel->getActiveSheet()->getStyle('E3:K3')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('0293ad');
$objPHPExcel->getActiveSheet()->getStyle('A5:K5')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('F5F5F1');



while ($roweusuario = $rtransferencia->fetch_assoc()) {

    //DETALLE
    #region
    $qdetalle = "SELECT tr_movimientos.pk_movimiento,
        tr_movimientos.fk_producto,
        ct_productos.codigobarras,
        ct_productos.nombre,
        tr_movimientos.serie,
        tr_movimientos.fk_movimiento as estatus,
        tr_movimientos.cantidad,
        (tr_movimientos.total / tr_movimientos.cantidad) as unitario,
        tr_movimientos.total
        FROM tr_movimientos, ct_productos
        WHERE tr_movimientos.fk_movimiento_detalle = $roweusuario[pk_transferencia]
        AND tr_movimientos.fk_movimiento IN(2,9,10)
        AND ct_productos.pk_producto = tr_movimientos.fk_producto$flproducto";


    if (!$rdetalle = $mysqli->query($qdetalle)) {
        echo "Lo sentimos, esta aplicación está experimentando problemas.";
        exit;
    }

    if ($rdetalle->num_rows > 0) {

        $objPHPExcel->getActiveSheet()->setCellValue('A' . $paso, $roweusuario["fecha"]);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $paso, $roweusuario["origen"] . ". " . $roweusuario["almacena"]);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $paso, $roweusuario["destino"] . ". " . $roweusuario["almacenb"]);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $paso, $roweusuario["fk_usuario"]);


        while ($rowdetalle = $rdetalle->fetch_assoc()) {

            //Estatus
            #region
            if ($rowdetalle["estatus"] == 2) {
                $estatus = "Enviado";
            } else if ($rowdetalle["estatus"] == 9) {
                $estatus = "Recibido";
                $objPHPExcel->getActiveSheet()->getStyle('I' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('A8F991');
            } else if ($rowdetalle["estatus"] == 10) {
                $estatus = "Devuelto";
                $objPHPExcel->getActiveSheet()->getStyle('I' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('9032bb');
            }
            #endregion

            $objPHPExcel->getActiveSheet()->setCellValue('E' . $paso, $rowdetalle["codigobarras"]);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $paso, $rowdetalle["nombre"]);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $paso, $rowdetalle["serie"]);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $paso, $rowdetalle["cantidad"]);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $paso, $estatus);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $paso, "$" . number_format($rowdetalle["unitario"], 2));
            $objPHPExcel->getActiveSheet()->setCellValue('K' . $paso, "$" . number_format($rowdetalle["total"], 2));

            if ($rowdetalle["estatus"] == 2) {
                $subtotal += $rowdetalle["total"];
                $objPHPExcel->getActiveSheet()->getStyle('E' . $paso . ':K' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('F5F5F1');
                $objPHPExcel->getActiveSheet()->getStyle('I' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('BFBFBF');
            }

            $paso++;
        }

        $objPHPExcel->getActiveSheet()->setCellValue('K' . $paso, "$" . number_format($subtotal, 2));
        $objPHPExcel->getActiveSheet()->getStyle('K' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('A8F991');
        $subtotal = 0;
        #endregion

        $paso += 2;
        $objPHPExcel->getActiveSheet()->getStyle('A' . $paso . ':K' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('F5F5F1');
    }
}




$objPHPExcel->getActiveSheet()->setTitle('Reporte traspasos');


$objPHPExcel->setActiveSheetIndex(0);


// foreach (range('C', 'M') as $columnID) {
//     $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
//         ->setAutoSize(true);
// }

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

$objPHPExcel->getActiveSheet()->getStyle('A1:K' . $paso)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);


// Se modifican los encabezados del HTTP para indicar que se envia un archivo de Excel.
// Redirect output to a client’s web browser (Excel5)

$ahora = new DateTime();
$id_file = "reporte_traspasos_" . $ahora->getTimestamp() . ".xls";





header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment;filename="' . $id_file . '"');
header('Cache-Control: max-age=0');



$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
