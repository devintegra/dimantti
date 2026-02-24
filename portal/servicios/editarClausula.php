<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
date_default_timezone_set('America/Mexico_City');
$codigo = 200;
$descripcion = "";


if (isset($_POST['pk_clausula']) && is_numeric($_POST['pk_clausula'])) {
    $pk_clausula = (int)$_POST['pk_clausula'];
}

if (isset($_POST['descripcion']) && is_string($_POST['descripcion'])) {
    $descripciond = $_POST['descripcion'];
}

if (isset($_POST['estado']) && is_numeric($_POST['estado'])) {
    $estado = (int)$_POST['estado'];
}



if (!$mysqli->query("CALL sp_update_clausula($pk_clausula, '$descripciond', $estado)")) {
    $codigo = 201;
    $descripcion = "Error al guardar el registro";
}




$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
