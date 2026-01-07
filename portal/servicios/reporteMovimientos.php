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

if (isset($_GET['movimiento']) && is_numeric($_GET['movimiento'])) {
    $fk_movimiento = (int)$_GET['movimiento'];
}

if (isset($_GET['producto']) && is_string($_GET['producto'])) {
    $clave = $_GET['producto'];
}


//FILTROS
#region
$flfechas = "";
$flsucursal = "";
$flmovimiento = "";
$flproducto = "";

if ($inicio != "" && $fin != "") {
    $flfechas = " AND tr_movimientos.fecha BETWEEN '$inicio' AND '$fin'";
}

if ($sucursal != 0) {
    $flsucursal = " AND tr_movimientos.fk_sucursal = $sucursal";
}

if ($fk_movimiento != 0) {
    $flmovimiento = " AND tr_movimientos.fk_movimiento = $fk_movimiento";
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



$qmovimientos = "SELECT tr_movimientos.*,
	ct_sucursales.nombre as sucursal,
    rt_sucursales_almacenes.nombre as almacen,
    ct_productos.codigobarras,
    ct_productos.nombre,
    ct_productos.descripcion
    FROM tr_movimientos, ct_sucursales, ct_productos, rt_sucursales_almacenes
    WHERE tr_movimientos.estado = 1
    AND ct_sucursales.pk_sucursal = tr_movimientos.fk_sucursal
    AND rt_sucursales_almacenes.pk_sucursal_almacen = tr_movimientos.fk_almacen
    AND ct_productos.pk_producto = tr_movimientos.fk_producto$flfechas $flsucursal $flmovimiento $flproducto";



if (!$rmovimientos = $mysqli->query($qmovimientos)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}



$paso = 4;
$total_final = 0.00;
// Crea un nuevo objeto PHPExcel
$objPHPExcel = new PHPExcel();

// Establecer propiedades
$objPHPExcel->getProperties()
    ->setCreator("Integra Connective")
    ->setLastModifiedBy("Integra Connective")
    ->setTitle("Reporte Movimientos")
    ->setSubject("Movimientos")
    ->setDescription("Movimientos")
    ->setKeywords("Movimientos")
    ->setCategory("Movimientos");


// Agregar Informacion
$objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue('A3', 'Movimiento')
    ->setCellValue('B3', 'Fecha')
    ->setCellValue('C3', 'Sucursal')
    ->setCellValue('D3', 'Almacén')
    ->setCellValue('E3', 'Usuario')
    ->setCellValue('F3', 'Código')
    ->setCellValue('G3', 'Producto')
    ->setCellValue('H3', 'Serie')
    ->setCellValue('I3', 'Cantidad')
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
$objPHPExcel->getActiveSheet()->setCellValue('A1', "POSMOVIL. Integra Desarrollo");


$objPHPExcel->getActiveSheet()->getStyle('A3:K3')->applyFromArray($styleArrayHeaders);
$objPHPExcel->getActiveSheet()->getStyle('A3:E3')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('000000');
$objPHPExcel->getActiveSheet()->getStyle('F3:K3')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('0293ad');


while ($roweusuario = $rmovimientos->fetch_assoc()) {

    //Movimiento
    #region
    switch ($roweusuario['fk_movimiento']) {
        case 1:
            $movimiento = "Alta almacén";
            $objPHPExcel->getActiveSheet()->getStyle('A' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('BBF7B0');
            break;
        case 2:
            $movimiento = "Transferencia";
            $objPHPExcel->getActiveSheet()->getStyle('A' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FEF08A');
            break;
        case 3:
            $movimiento = "Prestamo";
            $objPHPExcel->getActiveSheet()->getStyle('A' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('BFDBFE');
            break;
        case 4:
            $movimiento = "Baja";
            $objPHPExcel->getActiveSheet()->getStyle('A' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FECACA');
            break;
        case 5:
            if ($roweusuario['tipo_venta'] == 1) {
                $movimiento = "Venta en mostrador";
            } else {
                $movimiento = "Venta en línea";
            }
            $objPHPExcel->getActiveSheet()->getStyle('A' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFBA6A');
            break;
        case 6:
            $movimiento = "Devolución";
            $objPHPExcel->getActiveSheet()->getStyle('A' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('CB9BDE');
            break;
        case 7:
            $movimiento = "Devolución de venta";
            $objPHPExcel->getActiveSheet()->getStyle('A' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('CB9BDE');
            break;
        case 8:
            $movimiento = "Devolución por cancelación de venta";
            $objPHPExcel->getActiveSheet()->getStyle('A' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('CB9BDE');
            break;
        case 9:
            $movimiento = "Recepción desde transferencia";
            $objPHPExcel->getActiveSheet()->getStyle('A' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('BBF7B0');
            break;
        case 10:
            $movimiento = "Devolución por transferencia";
            $objPHPExcel->getActiveSheet()->getStyle('A' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FEF08A');
            break;
        case 11:
            $movimiento = "Devolución por prestamo";
            $objPHPExcel->getActiveSheet()->getStyle('A' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('ffd19b');
            break;
        case 12:
            $movimiento = "Venta de prestamo";
            $objPHPExcel->getActiveSheet()->getStyle('A' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('BBF7B0');
            break;
    }
    #endregion

    $unitario = $roweusuario["total"] / $roweusuario["cantidad"];

    $objPHPExcel->getActiveSheet()->setCellValue('A' . $paso, $movimiento);
    $objPHPExcel->getActiveSheet()->setCellValue('B' . $paso, $roweusuario["fecha"]);
    $objPHPExcel->getActiveSheet()->setCellValue('C' . $paso, $roweusuario["sucursal"]);
    $objPHPExcel->getActiveSheet()->setCellValue('D' . $paso, $roweusuario["almacen"]);
    $objPHPExcel->getActiveSheet()->setCellValue('E' . $paso, $roweusuario["fk_usuario"]);
    $objPHPExcel->getActiveSheet()->setCellValue('F' . $paso, $roweusuario["codigobarras"]);
    $objPHPExcel->getActiveSheet()->setCellValue('G' . $paso, $roweusuario["nombre"]);
    $objPHPExcel->getActiveSheet()->setCellValue('H' . $paso, $roweusuario["serie"]);
    $objPHPExcel->getActiveSheet()->setCellValue('I' . $paso, $roweusuario["cantidad"]);
    $objPHPExcel->getActiveSheet()->setCellValue('J' . $paso, "$" . number_format($unitario, 2));
    $objPHPExcel->getActiveSheet()->setCellValue('K' . $paso, "$" . number_format($roweusuario["total"], 2));

    $total_final += $roweusuario["total"];

    $paso++;
}


$objPHPExcel->getActiveSheet()->setTitle('Reporte movimientos');


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

$objPHPExcel->getActiveSheet()->getStyle('A1:I' . $paso)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
$objPHPExcel->getActiveSheet()->getStyle('J1:K' . $paso)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);


// Se modifican los encabezados del HTTP para indicar que se envia un archivo de Excel.
// Redirect output to a client’s web browser (Excel5)

$ahora = new DateTime();
$id_file = "reporte_movimientos_" . $ahora->getTimestamp() . ".xls";





header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment;filename="' . $id_file . '"');
header('Cache-Control: max-age=0');



$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
