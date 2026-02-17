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

if (isset($_POST['fk_metal']) && is_string($_POST['fk_metal'])) {
    $fk_metal = $_POST['fk_metal'];
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

if (isset($_POST['tipo_precio']) && is_numeric($_POST['tipo_precio'])) {
    $tipo_precio =  $_POST['tipo_precio'];
}

if (isset($_POST['precio']) && is_numeric($_POST['precio'])) {
    $precio =  $_POST['precio'];
}

if (isset($_POST['utilidad']) && is_numeric($_POST['utilidad'])) {
    $utilidad =  $_POST['utilidad'];
}

if (isset($_POST['gramaje']) && is_numeric($_POST['gramaje'])) {
    $gramaje =  $_POST['gramaje'];
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




if (!$rsp_set_producto = $mysqli->query("CALL sp_set_producto('$nombre', '$codigo_barras', '$descripcion', $fk_metal, $fk_categoria, $costo, $tipo_precio, $precio, $utilidad, $gramaje, $inventario, $inventariomin, $inventariomax, '$clave_producto_sat', '$clave_unidad_sat')")) {
    $codigo = 201;
    $descripciond = "Error al guardar el registro";
}

$row = $rsp_set_producto->fetch_assoc();
$pk_producto = $row["pk_producto"];



//CODIGO DE BARRAS
if ($codigo == 200 && $codigo_barras == '') {

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

    $mysqli->next_result();
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
