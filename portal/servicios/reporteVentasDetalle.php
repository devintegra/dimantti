<?php
require_once "Classes/PHPExcel.php";
require "Classes/PHPExcel/Writer/Excel2007.php";
include("conexioni.php");
mysqli_set_charset($mysqli, 'utf8');

if (isset($_GET['nivel']) && is_numeric($_GET['nivel'])) {
    $nivel = (int)$_GET['nivel'];
}

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

if (isset($_GET['categoria']) && is_numeric($_GET['categoria'])) {
    $categoria = (int)$_GET['categoria'];
}

if (isset($_GET['agrupar']) && is_numeric($_GET['agrupar'])) {
    $agrupar = (int)$_GET['agrupar'];
}

if (isset($_GET['producto']) && is_string($_GET['producto'])) {
    $clave = $_GET['producto'];
}

if (isset($_GET['tipo']) && is_numeric($_GET['tipo'])) {
    $tipo_venta = (int)$_GET['tipo'];
}


//FILTROS
#region
$flfechas = "";
$flsucursal = "";
$flcliente = "";
$flusuario = "";
$flpago = "";
$flcategoria = "";
$flagrupar = "";
$flproducto = "";
$flsum = "tr_ventas_detalle.cantidad";
$flserie = "tr_ventas_detalle.serie";
$oragrupar = "";
$orsum = "rt_ordenes_detalle.cantidad";

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
            $metodo = "Efectivo. ";
            $flpago = " AND tr_ventas.efectivo > 0";
            break;
        case 2:
            $metodo = "Trans. ";
            $flpago = " AND tr_ventas.transferencia > 0";
            break;
        case 3:
            $metodo = "Debito. ";
            $flpago = " AND tr_ventas.debito > 0";
            break;
        case 4:
            $metodo = "Cheque. ";
            $flpago = " AND tr_ventas.cheque > 0";
            break;
        case 5:
            $metodo = "Credito. ";
            $flpago = " AND tr_ventas.credito > 0";
            break;
    }
}

if ($categoria != 0) {
    $flcategoria = " AND ct_productos.fk_categoria = $categoria";
}

if ($agrupar != 0) {
    $flserie = " GROUP_CONCAT(tr_ventas_detalle.serie SEPARATOR ', ') as serie";
    $flsum = "SUM(tr_ventas_detalle.cantidad) as cantidad";
    $orsum = "SUM(rt_ordenes_detalle.cantidad) as cantidad";
    $flagrupar = " GROUP BY tr_ventas_detalle.fk_producto, tr_ventas_detalle.fk_venta";
    $oragrupar = " GROUP BY rt_ordenes_detalle.clave, rt_ordenes_detalle.fk_orden_registro";
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



// Crea un nuevo objeto PHPExcel
$objPHPExcel = new PHPExcel();

// Establecer propiedades
$objPHPExcel->getProperties()
    ->setCreator("Integra Connective")
    ->setLastModifiedBy("Integra Connective")
    ->setTitle("Reporte Tectron Ventas Detalle")
    ->setSubject("Ventas Detalle")
    ->setDescription("Ventas Detalle")
    ->setKeywords("Ventas Detalle")
    ->setCategory("Ventas Detalle");


// Agregar Informacion
#region
if ($nivel == 1) {
    $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A5', 'ID Venta')
        ->setCellValue('B5', 'Fecha')
        ->setCellValue('C5', 'Clave')
        ->setCellValue('D5', 'Producto')
        ->setCellValue('E5', 'Cantidad')
        ->setCellValue('F5', 'Precio')
        ->setCellValue('G5', 'Descuento')
        ->setCellValue('H5', 'Importe')
        ->setCellValue('I5', 'Utilidad')
        ->setCellValue('J5', 'Sucursal')
        ->setCellValue('K5', 'Cliente')
        ->setCellValue('L5', 'Forma de pago')
        ->setCellValue('M5', 'Vendedor');
} else {
    $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A5', 'ID Venta')
        ->setCellValue('B5', 'Fecha')
        ->setCellValue('C5', 'Clave')
        ->setCellValue('D5', 'Producto')
        ->setCellValue('E5', 'Cantidad')
        ->setCellValue('F5', 'Precio')
        ->setCellValue('G5', 'Descuento')
        ->setCellValue('H5', 'Importe')
        ->setCellValue('I5', 'Sucursal')
        ->setCellValue('J5', 'Cliente')
        ->setCellValue('K5', 'Forma de pago')
        ->setCellValue('L5', 'Vendedor');
}
#endregion


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


$objPHPExcel->getActiveSheet()->setCellValue('A1', "POSMOVIL. Integra Desarrollo");

$nivel == 1 ? $columnLetter = 'M' : $columnLetter = 'L';

$objPHPExcel->getActiveSheet()->getStyle('A5:' . $columnLetter . '5')->applyFromArray($styleArrayHeaders);
$objPHPExcel->getActiveSheet()->getStyle('A5:' . $columnLetter . '5')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('000000');
$objPHPExcel->getActiveSheet()->getStyle('A6:' . $columnLetter . '6')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('F5F5F1');


//$total_final = 0.00;
$paso = 6;
$importe_total = 0.00;
$utilidad_total = 0.00;
$id_venta = null;
$productos = 0;


//VENTAS
if ($tipo_venta == 0 || $tipo_venta == 1) {

    $qproductos = "SELECT tr_ventas.pk_venta,
        tr_ventas.fk_usuario,
        ct_sucursales.nombre as sucursal,
        ct_clientes.nombre as cliente,
        tr_ventas.fecha,
        tr_ventas.hora,
        ct_productos.codigobarras,
        ct_productos.nombre as producto,
        ct_productos.costo as costo,
        $flsum,
        tr_ventas_detalle.unitario,
        tr_ventas_detalle.total
        FROM tr_ventas, tr_ventas_detalle, ct_productos, ct_sucursales, ct_clientes
        WHERE tr_ventas.fecha BETWEEN '$inicio' AND '$fin'
        AND tr_ventas_detalle.fk_venta = tr_ventas.pk_venta
        AND tr_ventas.tipo IN(1,2,3,4)
        AND tr_ventas.estatus = 1
        AND tr_ventas_detalle.estado = 1
        AND ct_productos.pk_producto = tr_ventas_detalle.fk_producto
        AND ct_sucursales.pk_sucursal = tr_ventas.fk_sucursal
        AND ct_clientes.pk_cliente = tr_ventas.fk_cliente$flsucursal $flcliente $flusuario $flproducto $flcategoria $flpago $flagrupar";

    if (!$rproductos = $mysqli->query($qproductos)) {
        echo "Lo sentimos, esta aplicación está experimentando problemas.";
        exit;
    }

    while ($row = $rproductos->fetch_assoc()) {

        //DESCUENTO
        #region
        if ($row['pk_venta'] != $id_venta) {
            $descuento = number_format($row['descuento'], 2);
            $id_venta = $row['pk_venta'];
        } else {
            $descuento = number_format(0, 2);
        }
        #endregion

        $importe = $row['cantidad'] * $row['unitario'];
        $costo_total = $row['cantidad'] * $row['costo'];
        $utilidad = ($importe + $descuento) - $costo_total;

        //MÉTODO DE PAGO
        #region
        $qpago = "SELECT CONCAT(
            CASE WHEN efectivo > 0 THEN 'Efectivo. ' ELSE '' END,
            CASE WHEN credito > 0 THEN 'Crédito. ' ELSE '' END,
            CASE WHEN debito > 0 THEN 'Debito. ' ELSE '' END,
            CASE WHEN cheque > 0 THEN 'Cheque. ' ELSE '' END,
            CASE WHEN transferencia > 0 THEN 'Tran. ' ELSE '' END
        ) AS campos_cumplen
        FROM tr_ventas
        WHERE pk_venta = $row[pk_venta]";

        if (!$rpago = $mysqli->query($qpago)) {
            echo "<br>Lo sentimos, esta aplicación está experimentando problemas.";
            exit;
        }

        $rowpago = $rpago->fetch_assoc();
        $npago = $rowpago["campos_cumplen"];
        #endregion


        if ($nivel == 1) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $paso, $row["pk_venta"]);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $paso, $row["fecha"] . " " . $row["hora"]);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $paso, $row["codigobarras"]);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $paso, $row["producto"]);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $paso, $row["cantidad"]);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $paso, "$" . number_format($row["unitario"], 2));
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $paso, "$" . number_format($descuento, 2));
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $paso, "$" . number_format($importe, 2));
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $paso, "$" . number_format($utilidad, 2));
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $paso, $row["sucursal"]);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . $paso, $row["cliente"]);
            $objPHPExcel->getActiveSheet()->setCellValue('L' . $paso, $npago);
            $objPHPExcel->getActiveSheet()->setCellValue('M' . $paso, $row["fk_usuario"]);
        } else {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $paso, $row["pk_venta"]);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $paso, $row["fecha"] . " " . $row["hora"]);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $paso, $row["codigobarras"]);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $paso, $row["producto"]);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $paso, $row["cantidad"]);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $paso, "$" . number_format($row["unitario"], 2));
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $paso, "$" . number_format($descuento, 2));
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $paso, "$" . number_format($importe, 2));
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $paso, $row["sucursal"]);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $paso, $row["cliente"]);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . $paso, $npago);
            $objPHPExcel->getActiveSheet()->setCellValue('L' . $paso, $row["fk_usuario"]);
        }

        $importe_total += $importe;
        $utilidad_total += $utilidad;
        $productos += $row['cantidad'];

        $paso++;
    }
}



$objPHPExcel->getActiveSheet()->getStyle('F1:I' . $paso)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

$objPHPExcel->getActiveSheet()->getStyle('E' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('bec8d6');
$objPHPExcel->getActiveSheet()->getStyle('H' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('BFDBFE');
if ($nivel == 1) {
    $objPHPExcel->getActiveSheet()->getStyle('I' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('A8F991');
}

$objPHPExcel->getActiveSheet()->setCellValue('E' . $paso, number_format($productos, 2));
$objPHPExcel->getActiveSheet()->setCellValue('H' . $paso, "$" . number_format($importe_total, 2));
if ($nivel == 1) {
    $objPHPExcel->getActiveSheet()->setCellValue('I' . $paso, "$" . number_format($utilidad_total, 2));
}



$objPHPExcel->getActiveSheet()->setTitle('Reporte ventas detalle');


$objPHPExcel->setActiveSheetIndex(0);


foreach (range('A', 'P') as $columnID) {
    $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
        ->setAutoSize(true);
}

$objPHPExcel->getActiveSheet()->getStyle('A1:E' . $paso)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);


// Se modifican los encabezados del HTTP para indicar que se envia un archivo de Excel.
// Redirect output to a client’s web browser (Excel5)

$ahora = new DateTime();
$id_file = "reporte_ventas_detalle_" . $ahora->getTimestamp() . ".xls";





header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment;filename="' . $id_file . '"');
header('Cache-Control: max-age=0');



$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
