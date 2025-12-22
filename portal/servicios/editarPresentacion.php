<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
date_default_timezone_set('America/Mexico_City');
$codigo = 200;
$descripcion = "";



if (isset($_POST['pk_presentacion']) && is_numeric($_POST['pk_presentacion'])) {
    $pk_presentacion = (int)$_POST['pk_presentacion'];
}

if (isset($_POST['nombre']) && is_string($_POST['nombre'])) {
    $nombre = $_POST['nombre'];
}



if (!$mysqli->query("UPDATE ct_presentaciones set descripcion = '$nombre' where pk_presentacion = $pk_presentacion")) {
    $codigo = 201;
    $descripcion = "Error al guardar el registro";
}






$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
