<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
date_default_timezone_set('America/Mexico_City');
$codigo = 200;
$descripcion = "";


if (isset($_POST['nombre']) && is_string($_POST['nombre'])) {
    $nombre = $_POST['nombre'];
}



if (!$mysqli->query("INSERT INTO ct_categorias (nombre) VALUES ('$nombre')")) {
    $codigo = 201;
    $descripcion = "Error al guardar el registro";
}

$pk_categoria = $mysqli->insert_id;



$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
