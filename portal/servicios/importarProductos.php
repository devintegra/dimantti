<?php
require_once "Classes/PHPExcel.php";
include("conexioni.php");

$archivo = $_FILES["archivo_productos"]["tmp_name"];
$codigo = 200;
$descripcion = "";
$registrosNoImportados = array();


$inputFileType = PHPExcel_IOFactory::identify($archivo);
$objReader = PHPExcel_IOFactory::createReader($inputFileType);
$objPHPExcel = $objReader->load($archivo);
$sheet = $objPHPExcel->getSheet(0);
$highestRow = $sheet->getHighestRow();
$highestColumn = $sheet->getHighestColumn();



for ($row = 2; $row <= $highestRow; $row++) {

    $stylecode = $sheet->getCell('A' . $row)->getDataType();

    if ($stylecode == "s") {

        $nombre = trim($sheet->getCell('A' . $row)->getValue());
        $codigo_barras = trim($sheet->getCell('B' . $row)->getValue());
        $fk_metal = trim($sheet->getCell('C' . $row)->getValue()) ?? 0;
        $fk_categoria = trim($sheet->getCell('D' . $row)->getValue()) ?? 0;
        $descripcion = trim($sheet->getCell('E' . $row)->getValue());
        $costo = trim($sheet->getCell('F' . $row)->getValue()) ?? 0;
        $tipo_precio = trim($sheet->getCell('G' . $row)->getValue()) ?? 0;
        $precio = trim($sheet->getCell('H' . $row)->getValue()) ?? 0;
        $gramaje = trim($sheet->getCell('I' . $row)->getValue()) ?? 0;
        $inventario = trim($sheet->getCell('J' . $row)->getValue()) ?? 2;
        $inventariomin = trim($sheet->getCell('K' . $row)->getValue()) ?? 0;
        $inventariomax = trim($sheet->getCell('L' . $row)->getValue()) ?? 0;
        $clave_producto_sat = trim($sheet->getCell('M' . $row)->getValue());
        $clave_unidad_sat = trim($sheet->getCell('N' . $row)->getValue());


        //VERIFICAR SI EXISTE
        #region
        $mysqli->next_result();
        if (!$rsp_get_producto = $mysqli->query("CALL sp_get_producto_by_clave('$codigo_barras')")) {
            echo "Lo sentimos, esta aplicación está experimentando problemas.";
            exit;
        }

        if ($rsp_get_producto->num_rows == 0) {
            $pk_producto = 0;
        } else {
            $rowi = $rsp_get_producto->fetch_assoc();
            $pk_producto = $rowi['pk_producto'];
            $precio_anterior = $rowi['precio_anterior'];
            $utilidad = $rowi['utilidad'];
        }
        #endregion



        //GUARDAR
        if ($pk_producto == 0) {
            $mysqli->next_result();
            if (!$mysqli->query("CALL sp_set_producto('$nombre', '$codigo_barras', '$descripcion', $fk_metal, $fk_categoria, $costo, $tipo_precio, $precio, 0, $gramaje, $inventario, $inventariomin, $inventariomax, '$clave_producto_sat', '$clave_unidad_sat')")) {
                array_push($registrosNoImportados, $nombre);
            }
        } else {
            $mysqli->next_result();
            if (!$mysqli->query("CALL sp_update_producto($pk_producto, '$nombre', '$descripcion', $fk_metal, $fk_categoria, $costo, $tipo_precio, $precio, $precio_anterior, $utilidad, $gramaje, $inventario, $inventariomin, $inventariomax, '$clave_producto_sat', '$clave_unidad_sat')")) {
                array_push($registrosNoImportados, $nombre);
            }
        }
    }
}



if (count($registrosNoImportados) > 0) {
    $codigo = 201;
    $descripcion = "Error al guardar los siguientes registros: " . $registrosNoImportados . ". Vuelve a intentarlo, si el problema persiste puedes guardarlos manualmente";
}



$mysqli->close();
$detalle = array("registrosNoImportados" => $registrosNoImportados);
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => $detalle);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
