<?php
require_once "Classes/PHPExcel.php";
require "Classes/PHPExcel/Writer/Excel2007.php";
include("conexioni.php");
mysqli_set_charset($mysqli, 'utf8');




$mysqli->next_result();
if (!$rsp_get_registros = $mysqli->query("CALL sp_get_productos()")) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}




//CREACION DEL EXCEL
#region
// Crea un nuevo objeto PHPExcel
$objPHPExcel = new PHPExcel();

// Establecer propiedades
$objPHPExcel->getProperties()
    ->setCreator("Integra Connective")
    ->setLastModifiedBy("Integra Connective")
    ->setTitle("Formato Importación Productos")
    ->setSubject("Productos")
    ->setDescription("Productos")
    ->setKeywords("Productos")
    ->setCategory("Productos");
#endregion



//ESTILOS
#region
$styleArrayTitle = array(
    'font'  => array(
        'bold'  => true,
        'color' => array('rgb' => 'FFFFFF'),
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
#endregion




//HEADER
#region
// Agregar Informacion
$objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue('A1', 'nombre')
    ->setCellValue('B1', 'codigo_barras')
    ->setCellValue('C1', 'id_metal')
    ->setCellValue('D1', 'id_categoria')
    ->setCellValue('E1', 'id_subcategoria')
    ->setCellValue('F1', 'descripcion')
    ->setCellValue('G1', 'costo')
    ->setCellValue('H1', 'tipo_precio')
    ->setCellValue('I1', 'precio')
    ->setCellValue('J1', 'gramaje')
    ->setCellValue('K1', '¿inventario?')
    ->setCellValue('L1', 'inventario_minimo')
    ->setCellValue('M1', 'inventario_maximo')
    ->setCellValue('N1', 'clave_sat')
    ->setCellValue('O1', 'unidad_sat');

$objPHPExcel->getActiveSheet()->getStyle('A1:O1')->applyFromArray($styleArrayHeaders);
$objPHPExcel->getActiveSheet()->getStyle('A1:O1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('000000');
#endregion



//IMPRIMIR REGISTROS
#region
$paso = 2;

while ($row = $rsp_get_registros->fetch_assoc()) {

    $objPHPExcel->getActiveSheet()->setCellValue('A' . $paso, $row["nombre"]);
    $objPHPExcel->getActiveSheet()->setCellValue('B' . $paso, $row["codigobarras"]);
    $objPHPExcel->getActiveSheet()->setCellValue('C' . $paso, $row["fk_metal"]);
    $objPHPExcel->getActiveSheet()->setCellValue('D' . $paso, $row["fk_categoria"]);
    $objPHPExcel->getActiveSheet()->setCellValue('E' . $paso, $row["fk_subcategoria"]);
    $objPHPExcel->getActiveSheet()->setCellValue('F' . $paso, $row["descripcion"]);
    $objPHPExcel->getActiveSheet()->setCellValue('G' . $paso, $row["costo"]);
    $objPHPExcel->getActiveSheet()->setCellValue('H' . $paso, $row["tipo_precio"]);
    $objPHPExcel->getActiveSheet()->setCellValue('I' . $paso, $row["precio"]);
    $objPHPExcel->getActiveSheet()->setCellValue('J' . $paso, $row["gramaje"]);
    $objPHPExcel->getActiveSheet()->setCellValue('K' . $paso, $row["inventario"]);
    $objPHPExcel->getActiveSheet()->setCellValue('L' . $paso, $row["inventariomin"]);
    $objPHPExcel->getActiveSheet()->setCellValue('M' . $paso, $row["inventariomax"]);
    $objPHPExcel->getActiveSheet()->setCellValue('N' . $paso, $row["clave_producto_sat"]);
    $objPHPExcel->getActiveSheet()->setCellValue('O' . $paso, $row["clave_unidad_sat"]);

    $paso++;
}
#endregion



//INDICACIONES
#region
$objPHPExcel->getActiveSheet()->setCellValue('S7', "id_metal: Ir a pestaña Configuración/Tipos de metales/Columna ID");
$objPHPExcel->getActiveSheet()->setCellValue('S8', "id_categoria: Ir a pestaña Configuración/Categorías/Columna ID");
$objPHPExcel->getActiveSheet()->setCellValue('S9', "id_subcategoria: Ir a pestaña Configuración/Subcategorías/Columna ID");
$objPHPExcel->getActiveSheet()->setCellValue('S10', "tipo_precio: 1 = Precio fijo, 2 = Precio dinámico");
$objPHPExcel->getActiveSheet()->setCellValue('S11', "precio: En caso de ser tipo_precio = 1 indicar este campo, de lo contrario poner 0");
$objPHPExcel->getActiveSheet()->setCellValue('S12', "gramaje: En caso de ser tipo_precio = 2 indicar el gramaje, a partir del tipo de metal en la venta será calculado el precio");
$objPHPExcel->getActiveSheet()->setCellValue('S13', "¿inventario?: Indica si el producto maneja inventario. 1:No, 2:Si");
#endregion



//EXPORTACION
#region
$objPHPExcel->setActiveSheetIndex(0);
$objPHPExcel->getActiveSheet()->setTitle('PRODUCTOS');

foreach (range('A', 'N') as $columnID) {
    $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
        ->setAutoSize(true);
}


$ahora = new DateTime();
$id_file = $ahora->getTimestamp();
$name_file = "formato_importacion_productos_" . $id_file . ".xlsx";


header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment;filename="' . $name_file . '"');
header('Cache-Control: max-age=0');


$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
#endregion
