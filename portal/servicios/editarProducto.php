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

if (isset($_POST['fk_presentacion']) && is_numeric($_POST['fk_presentacion'])) {
    $fk_presentacion = $_POST['fk_presentacion'];
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

if (isset($_POST['precio']) && is_numeric($_POST['precio'])) {
    $precio = $_POST['precio'];
}

if (isset($_POST['precio2']) && is_numeric($_POST['precio2'])) {
    $precio2 = $_POST['precio2'];
}

if (isset($_POST['precio3']) && is_numeric($_POST['precio3'])) {
    $precio3 = $_POST['precio3'];
}

if (isset($_POST['precio4']) && is_numeric($_POST['precio4'])) {
    $precio4 = $_POST['precio4'];
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
$qproducto = "SELECT * FROM ct_productos WHERE pk_producto = $pk_producto";

if (!$resultado = $mysqli->query($qproducto)) {
    echo "<br>Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

$row = $resultado->fetch_assoc();
$precio_actual = $row["precio"];
$precio_actual2 = $row["precio2"];
$precio_actual3 = $row["precio3"];
$precio_actual4 = $row["precio4"];

//Verificar si el precio fue modificado
$precio_actual != $precio ? $precio_anterior = $precio_actual  : $precio_anterior = $row["precio_anterior"];
$precio_actual2 != $precio2 ? $precio_anterior2 = $precio_actual2  : $precio_anterior2 = $row["precio_anterior2"];
$precio_actual3 != $precio3 ? $precio_anterior3 = $precio_actual3  : $precio_anterior3 = $row["precio_anterior3"];
$precio_actual4 != $precio4 ? $precio_anterior4 = $precio_actual4  : $precio_anterior4 = $row["precio_anterior4"];
#endregion


if (!$mysqli->query("UPDATE ct_productos
        SET nombre = '$nombre',
        descripcion = '$descripcion',
        fk_presentacion = $fk_presentacion,
        fk_categoria = $fk_categoria,
        costo = $costo,
        precio = $precio,
        precio2 = $precio2,
        precio3 = $precio3,
        precio4 = $precio4,
        precio_anterior = $precio_anterior,
        precio_anterior2 = $precio_anterior2,
        precio_anterior3 = $precio_anterior3,
        precio_anterior4 = $precio_anterior4,
        utilidad = $utilidad,
        utilidad2 = $utilidad2,
        utilidad3 = $utilidad3,
        utilidad4 = $utilidad4,
        inventario = $inventario,
        inventariomin = $inventariomin,
        inventariomax = $inventariomax,
        clave_producto_sat = '$clave_producto_sat',
        clave_unidad_sat = '$clave_unidad_sat'
        WHERE pk_producto = $pk_producto")) {

    $codigo = 201;
    $descripcion_error = "Error al guardar el registro";
}



$mysqli->close();
$detalle = array("pk_producto" => $pk_producto);
$general = array("codigo" => $codigo, "descripcion" => $descripcion_error, "objList" => $detalle);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
