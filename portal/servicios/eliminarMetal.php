<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
date_default_timezone_set('America/Mexico_City');
$codigo = 200;
$descripcion = "";


if (isset($_POST['pk_metal']) && is_numeric($_POST['pk_metal'])) {
    $pk_metal = (int)$_POST['pk_metal'];
}



if (!$mysqli->query("CALL sp_delete_metal($pk_metal)")) {
    $codigo = 201;
    $descripcion = "Error al eliminar el registro";
}



$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
