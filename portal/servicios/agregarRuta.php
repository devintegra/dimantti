<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
date_default_timezone_set('America/Mexico_City');
@session_start();
$fk_usuario = $_SESSION['usuario'];
$codigo = 200;
$descripcion = "";


if (isset($_POST['fk_sucursal']) && is_numeric($_POST['fk_sucursal'])) {
    $fk_sucursal = (int)$_POST['fk_sucursal'];
}

if (isset($_POST['clave']) && is_string($_POST['clave'])) {
    $clave = $_POST['clave'];
}

if (isset($_POST['nombre']) && is_string($_POST['nombre'])) {
    $nombre = $_POST['nombre'];
}



if (!$mysqli->query("INSERT INTO ct_rutas (fk_sucursal, clave, nombre, fk_usuario, fecha_creacion, fecha_modificacion) VALUES ($fk_sucursal, '$clave', '$nombre', '$fk_usuario', CURDATE(), CURDATE())")) {
    $codigo = 201;
    $descripcion = "Error al guardar el registro";
}




$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
