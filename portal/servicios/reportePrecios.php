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
        ct_categorias.nombre as categoria,
        ct_metales.precio as precio_gramaje
    FROM ct_productos, ct_categorias, ct_metales
    WHERE ct_productos.estado = 1
    AND ct_categorias.pk_categoria = ct_productos.fk_categoria
    AND ct_metales.pk_metal = ct_productos.fk_metal$flagrupar";

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
    ->setCellValue('D3', 'Precio')
    ->setCellValue('E3', 'Tipo de precio');



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
$objPHPExcel->getActiveSheet()->setCellValue('A1', "DIMANTTI. Integra Desarrollo");


$objPHPExcel->getActiveSheet()->getStyle('A3:E3')->applyFromArray($styleArrayHeaders);
$objPHPExcel->getActiveSheet()->getStyle('A3:E3')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('000000');

$prevId = null; // Variable para almacenar el id del registro anterior

while ($row = $rproductos->fetch_assoc()) {

    switch ($agrupar) {
        case 2:
            $currentId = $row['fk_categoria'];
            break;
    }

    if ($prevId !== null && $currentId !== $prevId) {
        $objPHPExcel->getActiveSheet()->getStyle('A' . $paso . ':E' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('F5F5F1');
        $paso++;
    }

    $tipo_precio = ($row["tipo_precio"] == 1) ? "Precio fijo" : "Precio dinámico";
    $precio = ($row["tipo_precio"] == 1) ? round($row["precio"]) : round($row["precio_gramaje"] * $row["gramaje"]);

    $objPHPExcel->getActiveSheet()->setCellValue('A' . $paso, $row["codigobarras"]);
    $objPHPExcel->getActiveSheet()->setCellValue('B' . $paso, $row["nombre"]);
    $objPHPExcel->getActiveSheet()->setCellValue('C' . $paso, $row["categoria"]);
    $objPHPExcel->getActiveSheet()->setCellValue('D' . $paso, "$" . number_format($precio, 2));
    $objPHPExcel->getActiveSheet()->setCellValue('E' . $paso, $tipo_precio);

    $prevId = $currentId;

    $paso++;
}



$objPHPExcel->getActiveSheet()->setTitle('Reporte lista de precios');


$objPHPExcel->setActiveSheetIndex(0);


foreach (range('C', 'E') as $columnID) {
    $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
        ->setAutoSize(true);
}

$objPHPExcel->getActiveSheet()->getStyle('D4:D' . $paso)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);


// Se modifican los encabezados del HTTP para indicar que se envia un archivo de Excel.
// Redirect output to a client’s web browser (Excel5)

$ahora = new DateTime();
$id_file = "reporte_precios_" . $ahora->getTimestamp() . ".xls";





header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment;filename="' . $id_file . '"');
header('Cache-Control: max-age=0');



$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
