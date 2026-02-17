<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
mysqli_set_charset($mysqli, 'utf8');
$codigo = 200;
$descripcion_error = "";


if (isset($_POST['pk_producto']) && is_numeric($_POST['pk_producto'])) {
    $pk_producto = (int) $_POST['pk_producto'];
}

if (isset($_POST['nombre']) && is_string($_POST['nombre'])) {
    $nombre = $_POST['nombre'];
}

if (isset($_POST['fk_metal']) && is_numeric($_POST['fk_metal'])) {
    $fk_metal = $_POST['fk_metal'];
}

if (isset($_POST['fk_categoria']) && is_numeric($_POST['fk_categoria'])) {
    $fk_categoria = $_POST['fk_categoria'];
}

if (isset($_POST['descripcion']) && is_string($_POST['descripcion'])) {
    $descripcion = $_POST['descripcion'];
}

if (isset($_POST['costo']) && is_numeric($_POST['costo'])) {
    $costo = $_POST['costo'];
}

if (isset($_POST['tipo_precio']) && is_numeric($_POST['tipo_precio'])) {
    $tipo_precio = $_POST['tipo_precio'];
}

if (isset($_POST['precio']) && is_numeric($_POST['precio'])) {
    $precio = $_POST['precio'];
}

if (isset($_POST['utilidad']) && is_numeric($_POST['utilidad'])) {
    $utilidad = $_POST['utilidad'];
}

if (isset($_POST['gramaje']) && is_numeric($_POST['gramaje'])) {
    $gramaje = $_POST['gramaje'];
}

if (isset($_POST['inventario']) && is_numeric($_POST['inventario'])) {
    $inventario = $_POST['inventario'];
}

if (isset($_POST['inventariomin']) && is_numeric($_POST['inventariomin'])) {
    $inventariomin = $_POST['inventariomin'];
}

if (isset($_POST['inventariomax']) && is_numeric($_POST['inventariomax'])) {
    $inventariomax = $_POST['inventariomax'];
}

if (isset($_POST['clave_producto_sat']) && is_string($_POST['clave_producto_sat'])) {
    $clave_producto_sat = $_POST['clave_producto_sat'];
}

if (isset($_POST['clave_unidad_sat']) && is_string($_POST['clave_unidad_sat'])) {
    $clave_unidad_sat = $_POST['clave_unidad_sat'];
}


//OBTENER LOS PRECIOS ANTERIORES
#region
$mysqli->next_result();
if (!$resultado = $mysqli->query("CALL sp_get_producto($pk_producto)")) {
    echo "<br>Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

$row = $resultado->fetch_assoc();
$precio_actual = $row["precio"];
$precio_actual != $precio ? $precio_anterior = $precio_actual  : $precio_anterior = $row["precio_anterior"];
#endregion



//PRODUCTO
$mysqli->next_result();
if (!$mysqli->query("CALL sp_update_producto($pk_producto, '$nombre', '$descripcion', $fk_metal, $fk_categoria, $costo, $tipo_precio, $precio, $precio_anterior, $utilidad, $gramaje, $inventario, $inventariomin, $inventariomax, '$clave_producto_sat', '$clave_unidad_sat')")) {
    $codigo = 201;
    $descripcion_error = "Error al guardar el registro";
}



$mysqli->close();
$detalle = array("pk_producto" => $pk_producto);
$general = array("codigo" => $codigo, "descripcion" => $descripcion_error, "objList" => $detalle);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
