<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
date_default_timezone_set('America/Mexico_City');
$codigo = 200;
$descripcion = "";



if (isset($_POST['pk_ruta']) && is_numeric($_POST['pk_ruta'])) {
    $pk_ruta = (int)$_POST['pk_ruta'];
}

if (isset($_POST['fk_sucursal']) && is_numeric($_POST['fk_sucursal'])) {
    $fk_sucursal = (int)$_POST['fk_sucursal'];
}

if (isset($_POST['clave']) && is_string($_POST['clave'])) {
    $clave = $_POST['clave'];
}

if (isset($_POST['nombre']) && is_string($_POST['nombre'])) {
    $nombre = $_POST['nombre'];
}



if (!$mysqli->query("UPDATE ct_rutas SET fk_sucursal = $fk_sucursal, clave = '$clave', nombre = '$nombre', fecha_modificacion = CURDATE() where pk_ruta = $pk_ruta")) {
    $codigo = 201;
    $descripcion = "Error al guardar el registro";
}






$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
