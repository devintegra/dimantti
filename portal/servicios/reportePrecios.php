<?php
require_once "Classes/PHPExcel.php";
require "Classes/PHPExcel/Writer/Excel2007.php";
include("conexioni.php");
mysqli_set_charset($mysqli, 'utf8');

if (isset($_GET['agrupar']) && is_numeric($_GET['agrupar'])) {
    $agrupar = (int)$_GET['agrupar'];
}

if (isset($_GET['filtros']) && is_string($_GET['filtros'])) {
    $filtros = $_GET['filtros'];
}


//FILTROS
#region

$flagrupar = "";

if ($agrupar == 2) {
    $flagrupar = " AND ct_productos.fk_categoria IN ($filtros) ORDER BY ct_productos.fk_categoria";
}
#endregion



$qproductos = "SELECT ct_productos.*,
        ct_categorias.nombre as categoria
    FROM ct_productos, ct_categorias
    WHERE ct_productos.estado = 1
    AND ct_categorias.pk_categoria = ct_productos.fk_categoria$flagrupar";

if (!$rproductos = $mysqli->query($qproductos)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}



$paso = 4;
$subtotal = 0.00;
$total_producto = 0.00;
// Crea un nuevo objeto PHPExcel
$objPHPExcel = new PHPExcel();

// Establecer propiedades
$objPHPExcel->getProperties()
    ->setCreator("Integra Connective")
    ->setLastModifiedBy("Integra Connective")
    ->setTitle("Reporte Lista de Precios")
    ->setSubject("Lista de Precios")
    ->setDescription("Lista de Precios")
    ->setKeywords("Lista de Precios")
    ->setCategory("Lista de Precios");


// Agregar Informacion
$objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue('A3', 'Código de barras')
    ->setCellValue('B3', 'Producto')
    ->setCellValue('C3', 'Categoría')
    ->setCellValue('D3', 'Precio 1')
    ->setCellValue('E3', 'Precio 2')
    ->setCellValue('F3', 'Precio 3')
    ->setCellValue('G3', 'Precio 4');



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


$objPHPExcel->getActiveSheet()->getStyle('A3:G3')->applyFromArray($styleArrayHeaders);
$objPHPExcel->getActiveSheet()->getStyle('A3:G3')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('000000');

$prevId = null; // Variable para almacenar el id del registro anterior

while ($row = $rproductos->fetch_assoc()) {

    switch ($agrupar) {
        case 2:
            $currentId = $row['fk_categoria'];
            break;
    }

    if ($prevId !== null && $currentId !== $prevId) {
        $objPHPExcel->getActiveSheet()->getStyle('A' . $paso . ':G' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('F5F5F1');
        $paso++;
    }

    $objPHPExcel->getActiveSheet()->setCellValue('A' . $paso, $row["codigobarras"]);
    $objPHPExcel->getActiveSheet()->setCellValue('B' . $paso, $row["nombre"]);
    $objPHPExcel->getActiveSheet()->setCellValue('C' . $paso, $row["categoria"]);
    $objPHPExcel->getActiveSheet()->setCellValue('D' . $paso, "$" . number_format($row["precio"], 2));
    $objPHPExcel->getActiveSheet()->setCellValue('E' . $paso, "$" . number_format($row["precio2"], 2));
    $objPHPExcel->getActiveSheet()->setCellValue('F' . $paso, "$" . number_format($row["precio3"], 2));
    $objPHPExcel->getActiveSheet()->setCellValue('G' . $paso, "$" . number_format($row["precio4"], 2));

    $objPHPExcel->getActiveSheet()->getCell("A" . $paso)->setDataType(PHPExcel_Cell_DataType::TYPE_STRING2);
    $objPHPExcel->getActiveSheet()->getCell("G" . $paso)->setDataType(PHPExcel_Cell_DataType::TYPE_STRING2);
    $objPHPExcel->getActiveSheet()->getCell("G" . $paso)->getHyperlink()->setUrl(strip_tags($enlace));

    $prevId = $currentId;

    $paso++;
}



$objPHPExcel->getActiveSheet()->setTitle('Reporte lista de precios');


$objPHPExcel->setActiveSheetIndex(0);


foreach (range('C', 'G') as $columnID) {
    $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
        ->setAutoSize(true);
}

$objPHPExcel->getActiveSheet()->getStyle('D4:I' . $paso)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);


// Se modifican los encabezados del HTTP para indicar que se envia un archivo de Excel.
// Redirect output to a client’s web browser (Excel5)

$ahora = new DateTime();
$id_file = "reporte_precios_" . $ahora->getTimestamp() . ".xls";





header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment;filename="' . $id_file . '"');
header('Cache-Control: max-age=0');



$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
