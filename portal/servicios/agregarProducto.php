<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
$codigo = 200;
$descripciond = "";
mysqli_set_charset($mysqli, 'utf8');


if (isset($_POST['nombre']) && is_string($_POST['nombre'])) {
    $nombre = $_POST['nombre'];
}

if (isset($_POST['codigo_barras']) && is_string($_POST['codigo_barras'])) {
    $codigo_barras =  $_POST['codigo_barras'];
}

if (isset($_POST['fk_presentacion']) && is_string($_POST['fk_presentacion'])) {
    $fk_presentacion = $_POST['fk_presentacion'];
}

if (isset($_POST['fk_categoria']) && is_numeric($_POST['fk_categoria'])) {
    $fk_categoria =  $_POST['fk_categoria'];
}

if (isset($_POST['descripcion']) && is_string($_POST['descripcion'])) {
    $descripcion =  $_POST['descripcion'];
}

if (isset($_POST['costo']) && is_numeric($_POST['costo'])) {
    $costo =  $_POST['costo'];
}

if (isset($_POST['precio']) && is_numeric($_POST['precio'])) {
    $precio =  $_POST['precio'];
}

if (isset($_POST['precio2']) && is_numeric($_POST['precio2'])) {
    $precio2 =  $_POST['precio2'];
}

if (isset($_POST['precio3']) && is_numeric($_POST['precio3'])) {
    $precio3 =  $_POST['precio3'];
}

if (isset($_POST['precio4']) && is_numeric($_POST['precio4'])) {
    $precio4 =  $_POST['precio4'];
}

if (isset($_POST['utilidad']) && is_numeric($_POST['utilidad'])) {
    $utilidad =  $_POST['utilidad'];
}

if (isset($_POST['utilidad2']) && is_numeric($_POST['utilidad2'])) {
    $utilidad2 =  $_POST['utilidad2'];
}

if (isset($_POST['utilidad3']) && is_numeric($_POST['utilidad3'])) {
    $utilidad3 =  $_POST['utilidad3'];
}

if (isset($_POST['utilidad4']) && is_numeric($_POST['utilidad4'])) {
    $utilidad4 =  $_POST['utilidad4'];
}

if (isset($_POST['inventario']) && is_numeric($_POST['inventario'])) {
    $inventario =  $_POST['inventario'];
}

if (isset($_POST['inventariomin']) && is_numeric($_POST['inventariomin'])) {
    $inventariomin =  $_POST['inventariomin'];
}

if (isset($_POST['inventariomax']) && is_numeric($_POST['inventariomax'])) {
    $inventariomax =  $_POST['inventariomax'];
}

if (isset($_POST['clave_producto_sat']) && is_string($_POST['clave_producto_sat'])) {
    $clave_producto_sat =  $_POST['clave_producto_sat'];
}

if (isset($_POST['clave_unidad_sat']) && is_string($_POST['clave_unidad_sat'])) {
    $clave_unidad_sat =  $_POST['clave_unidad_sat'];
}




if (!$mysqli->query("INSERT INTO ct_productos (nombre, clave, descripcion, costo, inventario, inventariomin, inventariomax, clave_producto_sat, clave_unidad_sat, fk_presentacion, fk_categoria, precio, precio2, precio3, precio4, precio_anterior, precio_anterior2, precio_anterior3, precio_anterior4, utilidad, utilidad2, utilidad3, utilidad4, codigobarras) VALUES ('$nombre', '$codigo_barras', '$descripcion', $costo, $inventario, $inventariomin, $inventariomax, '$clave_producto_sat', '$clave_unidad_sat', $fk_presentacion, $fk_categoria, $precio, $precio2, $precio3, $precio4, $precio, $precio2, $precio3, $precio4, $utilidad, $utilidad2, $utilidad3, $utilidad4, '$codigo_barras')")) {
    $codigo = 201;
    $descripciond = "Error al guardar el registro";
}

$pk_producto = $mysqli->insert_id;



if ($codigo_barras == '') {

    //Codigo de 6 con 0 y el pk. ej.000005
    $codigob = array();
    $codigo_split = (string)$pk_producto;
    $codigo_length = strlen($codigo_split);
    $length_zeros = 6 - $codigo_length;

    $codigob = array_fill(0, $length_zeros, 0);

    $count = count($codigob);

    $x = 0;
    for ($i = $count; $i < 6; $i++) {
        $codigob[$i] = $codigo_split[$x];
        $x++;
    }

    $codigobarras = implode($codigob);

    if (!$mysqli->query("UPDATE ct_productos SET clave = '$codigobarras', codigobarras = '$codigobarras' WHERE pk_producto = $pk_producto AND estado = 1")) {
        $codigo = 201;
        $descripcion = "Error al generar el código de barras";
    }
}




$mysqli->close();
$detalle = array("pk_producto" => $pk_producto);
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => $detalle);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
