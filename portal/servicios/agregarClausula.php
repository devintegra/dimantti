<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
date_default_timezone_set('America/Mexico_City');
$codigo = 200;
$descripcion = "";


if (isset($_POST['descripcion']) && is_string($_POST['descripcion'])) {
    $descripciond = $_POST['descripcion'];
}




if (!$mysqli->query("CALL sp_set_clausula('$descripciond')")) {
    $codigo = 201;
    $descripcion = "Error al guardar el registro";
}



$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
