<?php
header("Access-Control-Allow-Origin: *");
require_once "Classes/PHPExcel.php";
require "Classes/PHPExcel/Writer/Excel2007.php";
include("conexioni.php");
mysqli_set_charset($mysqli, 'utf8');



if (isset($_GET['id']) && is_string($_GET['id'])) {
    $pk_inventario = $_GET['id'];
}


//ENCABEZADO
#region
$qinventario = "SELECT * FROM tr_inventario WHERE pk_inventario = $pk_inventario AND estado = 1";

if (!$rinventario = $mysqli->query($qinventario)) {
    echo "<br>Lo sentimos, esta aplicación está experimentando problemas.1";
    exit;
}

$inventario = $rinventario->fetch_assoc();
$inventario_fk_sucursal = $inventario["fk_sucursal"];
$inventario_usuario = $inventario["fk_usuario"];
$inventario_fecha = $inventario["fecha"];
$inventario_hora = $inventario["hora"];
#endregion



//SUCURSAL
#region
$qsucursal = "select * from ct_sucursales where pk_sucursal = $inventario_fk_sucursal";

if (!$rsucursal = $mysqli->query($qsucursal)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas1.";
    exit;
}
$empresa = $rsucursal->fetch_assoc();
$empresa_nombre = $empresa["nombre"];
#endregion



//REGISTROS
#region
$qproductos = "SELECT tr_inventario_detalle.*,
    ct_productos.codigobarras,
    ct_productos.nombre,
    ct_productos.costo
    FROM tr_inventario_detalle, ct_productos
    WHERE tr_inventario_detalle.fk_inventario = $pk_inventario
    AND ct_productos.pk_producto = tr_inventario_detalle.fk_producto";


if (!$rproductos = $mysqli->query($qproductos)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.2";
    exit;
}
#endregion



//CREAR
#region
$objPHPExcel = new PHPExcel();

// Establecer propiedades
$objPHPExcel->getProperties()
    ->setCreator("Integra Connective")
    ->setLastModifiedBy("Integra Connective")
    ->setTitle("Tecttron Registro de inventario")
    ->setSubject("Registro de inventario")
    ->setDescription("Registro de inventario")
    ->setKeywords("Registro de inventario")
    ->setCategory("Registro de inventario");
#endregion



//ESTILOS
#region
$styleArrayTitle = array(
    'font'  => array(
        'bold'  => true,
        'color' => array('rgb' => '00caee'),
        'size'  => 12,
        'name'  => 'Calibri'
    )
);


$styleArrayHeaders = array(
    'font'  => array(
        'bold'  => true,
        'color' => array('rgb' => '000000'),
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
#endregion



//HEADER
#region
//$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArrayTitle);
$objPHPExcel->getActiveSheet()->getStyle('A1:A3')->applyFromArray($styleArrayHeaders);


$objPHPExcel->getActiveSheet()->setCellValue('A1', "Fecha: " . $inventario_fecha . " " . $inventario_hora);
$objPHPExcel->getActiveSheet()->setCellValue('A2', "Sucursal: " . $empresa_nombre);
$objPHPExcel->getActiveSheet()->setCellValue('A3', "Usuario: " . $inventario_usuario);


$objPHPExcel->getActiveSheet()->getStyle('A5:E3')->applyFromArray($styleArrayHeaders);
$objPHPExcel->getActiveSheet()->getStyle('A5:E5')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('00caee');

$objPHPExcel->getActiveSheet()->getStyle('D1:E100')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

$objPHPExcel->getActiveSheet()->getStyle('A5:E5')->applyFromArray($styleArrayHeaders);
#endregion



//TABLE HEADER
#region
$objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue('A5', 'Producto')
    ->setCellValue('B5', 'Existencias Registradas')
    ->setCellValue('C5', 'Existencias reales')
    ->setCellValue('D5', 'Diferencia')
    ->setCellValue('E5', 'TOTAL');
#endregion



//IMRPIMIR
#region
$paso = 6;
$total = 0;

//ORDEN
while ($productos = $rproductos->fetch_assoc()) {

    if ($productos["existencia_real"] < 0) {
        $objPHPExcel->getActiveSheet()->getStyle('D' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FF9EA2');
        $costo = abs($productos['existencia_real']) * $productos['costo'];
    } else if ($productos["existencia_real"] > 0) {
        $objPHPExcel->getActiveSheet()->getStyle('D' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFFCC');
        $costo = $productos['escaneadas'] * $productos['costo'];
    } else {
        $objPHPExcel->getActiveSheet()->getStyle('D' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('D0FDD7');
        $costo = $productos['escaneadas'] * $productos['costo'];
    }

    $descripcion = $productos["codigobarras"] . ". " . $productos["nombre"];

    $objPHPExcel->getActiveSheet()->setCellValue('A' . $paso, $descripcion);
    $objPHPExcel->getActiveSheet()->setCellValue('B' . $paso, $productos["existencias"]);
    $objPHPExcel->getActiveSheet()->setCellValue('C' . $paso, $productos["escaneadas"]);
    $objPHPExcel->getActiveSheet()->setCellValue('D' . $paso, $productos["existencia_real"]);
    $objPHPExcel->getActiveSheet()->setCellValue('E' . $paso, "$" . number_format($costo, 2));

    $total += $costo;

    $paso++;
}

$objPHPExcel->getActiveSheet()->getStyle('E1:E' . $paso)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('D' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('A8F991');
$objPHPExcel->getActiveSheet()->setCellValue('D' . $paso, "TOTAL: ");
$objPHPExcel->getActiveSheet()->setCellValue('E' . $paso, "$" . number_format($total, 2));
$objPHPExcel->getActiveSheet()->getStyle('E' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('A8F991');
#endregion



//EXPORTAR
#region
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


$objPHPExcel->getActiveSheet()->setTitle('Registro de Inventario');


$objPHPExcel->setActiveSheetIndex(0);

// Se modifican los encabezados del HTTP para indicar que se envia un archivo de Excel.
// Redirect output to a client’s web browser (Excel5)

$ahora = new DateTime();
$id_file = "registro_inventario_" . $ahora->getTimestamp() . ".xls";



header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment;filename="' . $id_file . '"');
header('Cache-Control: max-age=0');



$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
#endregion
