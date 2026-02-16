<?php
header("Access-Control-Allow-Origin: *");
require_once "Classes/PHPExcel.php";
require "Classes/PHPExcel/Writer/Excel2007.php";
include("conexioni.php");
mysqli_set_charset($mysqli, 'utf8');



if (isset($_GET['fk_cliente']) && is_string($_GET['fk_cliente'])) {
    $fk_cliente = $_GET['fk_cliente'];
}


$qcliente = "SELECT * FROM ct_clientes WHERE pk_cliente = $fk_cliente";

if (!$rcliente = $mysqli->query($qcliente)) {
    echo "<br>Lo sentimos, esta aplicación está experimentando problemas.1";
    exit;
}

$cliente = $rcliente->fetch_assoc();
$fk_categoria = $cliente["fk_categoria_cliente"];

if ($fk_categoria == 1) {
    $fk_categoria = "";
}


$qproductos = "SELECT ct_productos.*,
    ct_productos.precio$fk_categoria as precio,
    ct_categorias.nombre as categoria
    FROM ct_productos, ct_categorias
    WHERE ct_productos.estado = 1
    AND ct_categorias.pk_categoria = ct_productos.fk_categoria";

if (!$rproductos = $mysqli->query($qproductos)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.2";
    exit;
}


$paso = 4;
// Crea un nuevo objeto PHPExcel
$objPHPExcel = new PHPExcel();

// Establecer propiedades
$objPHPExcel->getProperties()
    ->setCreator("Integra Connective")
    ->setLastModifiedBy("Integra Connective")
    ->setTitle("Lista de Precios")
    ->setSubject("Lista de precios")
    ->setDescription("Lista de precios")
    ->setKeywords("Lista de precios")
    ->setCategory("Lista de precios");


// Agregar Informacion
$objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue('A3', 'Producto')
    ->setCellValue('B3', 'Descripción')
    ->setCellValue('C3', 'Categoría')
    ->setCellValue('D3', 'Precio');

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
$objPHPExcel->getActiveSheet()->setCellValue('A1', "DIMANTTI. Integra Connective");


$objPHPExcel->getActiveSheet()->getStyle('A3:D3')->applyFromArray($styleArrayHeaders);
$objPHPExcel->getActiveSheet()->getStyle('A3:D3')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('000000');

$objPHPExcel->getActiveSheet()->getStyle('D1:D100')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);



//ORDEN
while ($productos = $rproductos->fetch_assoc()) {

    $precio = number_format($productos['precio'], 2);

    $objPHPExcel->getActiveSheet()->setCellValue('A' . $paso, $productos["nombre"]);
    $objPHPExcel->getActiveSheet()->setCellValue('B' . $paso, $productos["descripcion"]);
    $objPHPExcel->getActiveSheet()->setCellValue('C' . $paso, $productos["categoria"]);
    $objPHPExcel->getActiveSheet()->setCellValue('D' . $paso, "$" . $precio);

    $paso++;
}





$objPHPExcel->getActiveSheet()->getColumnDimension('A')
    ->setAutoSize(true);

$objPHPExcel->getActiveSheet()->getColumnDimension('B')
    ->setAutoSize(true);

$objPHPExcel->getActiveSheet()->getColumnDimension('C')
    ->setAutoSize(true);

$objPHPExcel->getActiveSheet()->getColumnDimension('D')
    ->setAutoSize(true);





$objPHPExcel->getActiveSheet()->setTitle('Lista de precios');


$objPHPExcel->setActiveSheetIndex(0);

// Se modifican los encabezados del HTTP para indicar que se envia un archivo de Excel.
// Redirect output to a client’s web browser (Excel5)

$ahora = new DateTime();
$id_file = "reporte_lista_precios_" . $ahora->getTimestamp() . ".xls";



header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment;filename="' . $id_file . '"');
header('Cache-Control: max-age=0');



$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
