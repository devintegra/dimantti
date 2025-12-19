<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
$codigo = 200;
$descripcion = "";


if (isset($_POST['pk_motivo_salida']) && is_numeric($_POST['pk_motivo_salida'])) {
    $pk_motivo_salida = (int)$_POST['pk_motivo_salida'];
}

if (isset($_POST['nombre']) && is_string($_POST['nombre'])) {
    $nombre = $_POST['nombre'];
}


if (!$mysqli->query("UPDATE ct_motivos_salida set nombre='$nombre' where pk_motivo_salida=$pk_motivo_salida")) {
    $codigo = 201;
    $descripcion = "Error al guardar el registro";
}


$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
