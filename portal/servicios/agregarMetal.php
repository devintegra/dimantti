<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
date_default_timezone_set('America/Mexico_City');
$codigo = 200;
$descripcion = "";


if (isset($_POST['nombre']) && is_string($_POST['nombre'])) {
    $nombre = $_POST['nombre'];
}

if (isset($_POST['costo']) && is_numeric($_POST['costo'])) {
    $costo = (float)$_POST['costo'];
}

if (isset($_POST['precio']) && is_numeric($_POST['precio'])) {
    $precio = (float)$_POST['precio'];
}



if (!$mysqli->query("CALL sp_set_metal('$nombre', $costo, $precio)")) {
    $codigo = 201;
    $descripcion = "Error al guardar el registro";
}



$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
