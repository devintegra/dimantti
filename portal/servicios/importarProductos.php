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
        $codigobarras = trim($sheet->getCell('B' . $row)->getValue());
        $fk_presentacion = trim($sheet->getCell('C' . $row)->getValue()) ?? 0;
        $fk_categoria = trim($sheet->getCell('D' . $row)->getValue()) ?? 0;
        $descripcion = trim($sheet->getCell('E' . $row)->getValue());
        $costo = trim($sheet->getCell('F' . $row)->getValue()) ?? 0;
        $precio = trim($sheet->getCell('G' . $row)->getValue()) ?? 0;
        $precio2 = trim($sheet->getCell('H' . $row)->getValue()) ?? 0;
        $precio3 = trim($sheet->getCell('I' . $row)->getValue()) ?? 0;
        $precio4 = trim($sheet->getCell('J' . $row)->getValue()) ?? 0;
        $inventario = trim($sheet->getCell('K' . $row)->getValue()) ?? 2;
        $inventariomin = trim($sheet->getCell('L' . $row)->getValue()) ?? 0;
        $inventariomax = trim($sheet->getCell('M' . $row)->getValue()) ?? 0;
        $clave_sat = trim($sheet->getCell('N' . $row)->getValue());
        $unidad_sat = trim($sheet->getCell('O' . $row)->getValue());


        //VERIFICAR SI EXISTE
        $mysqli->next_result();
        if (!$rsp_get_producto = $mysqli->query("SELECT * FROM ct_productos WHERE clave = '$codigobarras' AND estado = 1")) {
            echo "Lo sentimos, esta aplicación está experimentando problemas.";
            exit;
        }

        if ($rsp_get_producto->num_rows == 0) {
            $pk_producto = 0;
        } else {
            $rowi = $rsp_get_producto->fetch_assoc();
            $pk_producto = $rowi['pk_producto'];
        }



        //GUARDAR
        if ($pk_producto == 0) {
            $mysqli->next_result();
            if (!$mysqli->query("INSERT INTO ct_productos (nombre, clave, descripcion, costo, inventario, inventariomin, inventariomax, clave_producto_sat, clave_unidad_sat, fk_presentacion, fk_categoria, precio, precio2, precio3, precio4, precio_anterior, precio_anterior2, precio_anterior3, precio_anterior4, utilidad, utilidad2, utilidad3, utilidad4, codigobarras) VALUES ('$nombre', '$codigobarras', '$descripcion', $costo, $inventario, $inventariomin, $inventariomax, '$clave_sat', '$unidad_sat', $fk_presentacion, $fk_categoria, $precio, $precio2, $precio3, $precio4, $precio, $precio2, $precio3, $precio4, 0, 0, 0, 0, '$codigobarras')")) {
                array_push($registrosNoImportados, $nombre);
            }
        } else {
            if (!$mysqli->query(
                "UPDATE ct_productos
                SET nombre = '$nombre',
                descripcion = '$descripcion',
                fk_presentacion = $fk_presentacion,
                fk_categoria = $fk_categoria,
                costo = $costo,
                precio = $precio,
                precio2 = $precio2,
                precio3 = $precio3,
                precio4 = $precio4,
                inventario = $inventario,
                inventariomin = $inventariomin,
                inventariomax = $inventariomax,
                clave_producto_sat = '$clave_sat',
                clave_unidad_sat = '$unidad_sat'
                WHERE pk_producto = $pk_producto"
            )) {
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
