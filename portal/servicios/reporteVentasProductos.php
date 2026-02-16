<?php
require_once "Classes/PHPExcel.php";
require "Classes/PHPExcel/Writer/Excel2007.php";
include("conexioni.php");
mysqli_set_charset($mysqli, 'utf8');

if (isset($_GET['inicio']) && is_string($_GET['inicio'])) {
    $inicio = trim($_GET['inicio']);
}

if (isset($_GET['fin']) && is_string($_GET['fin'])) {
    $fin = trim($_GET['fin']);
}

if (isset($_GET['usuario']) && is_string($_GET['usuario'])) {
    $usuario = trim($_GET['usuario']);
}

if ($usuario == "0") {
    $usuario = "";
}


//FILTROS
#region
if ($usuario == "" || $$usuario == null) {
    $nusuario = "Todos";
} else {
    $nusuario = $usuario;
}
#endregion



$mysqli->next_result();
if (!$rventas = $mysqli->query("call sp_get_reporte_productos('$inicio', '$fin', '$usuario')")) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}


$paso = 4;
// Crea un nuevo objeto PHPExcel
$objPHPExcel = new PHPExcel();

// Establecer propiedades
$objPHPExcel->getProperties()
    ->setCreator("Integra Connective")
    ->setLastModifiedBy("Integra Connective")
    ->setTitle("Reporte Dimantti Ventas Productos")
    ->setSubject("Ventas Productos")
    ->setDescription("Ventas Productos")
    ->setKeywords("Ventas Productos")
    ->setCategory("Ventas Productos");


// Agregar Informacion
$objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue('A3', 'Cantidad')
    ->setCellValue('B3', 'Producto')
    ->setCellValue('C3', 'Venta');



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


$objPHPExcel->getActiveSheet()->getStyle('A3:C3')->applyFromArray($styleArrayHeaders);
$objPHPExcel->getActiveSheet()->getStyle('A3:C3')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('000000');


$total_final = 0.00;
$descuento_final = 0.00;
$subtotal_final = 0.00;


while ($roweusuario = $rventas->fetch_assoc()) {

    $total_final = $total_final + ((float)$roweusuario["total"]);
    $total = number_format((float)$roweusuario["total"], 2);



    $objPHPExcel->getActiveSheet()->setCellValue('A' . $paso, $roweusuario["cantidad"]);
    $objPHPExcel->getActiveSheet()->setCellValue('B' . $paso, $roweusuario["nombre"]);
    $objPHPExcel->getActiveSheet()->setCellValue('C' . $paso, "$" . $total);


    $paso++;
}


$total_final = number_format($total_final, 2);

$paso_inicial = $paso;
$objPHPExcel->getActiveSheet()->setCellValue('B' . $paso, "TOTAL");
$objPHPExcel->getActiveSheet()->setCellValue('C' . $paso, "$" . $total_final);

$paso_final = $paso;

$objPHPExcel->getActiveSheet()->getStyle('B' . $paso_inicial . ":C" . $paso_final)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('A8F991');
$paso++;



$objPHPExcel->getActiveSheet()->setTitle('Reporte productos ventas');


$objPHPExcel->setActiveSheetIndex(0);


foreach (range('A', 'C') as $columnID) {
    $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
        ->setAutoSize(true);
}

$objPHPExcel->getActiveSheet()->getStyle('A1:C' . $paso)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);


// Se modifican los encabezados del HTTP para indicar que se envia un archivo de Excel.
// Redirect output to a client’s web browser (Excel5)

$ahora = new DateTime();
$id_file = "reporte_ventas_productos_" . $ahora->getTimestamp() . ".xls";





header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment;filename="' . $id_file . '"');
header('Cache-Control: max-age=0');



$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
