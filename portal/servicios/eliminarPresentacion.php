<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
$codigo = 200;
$descripcion = "";


if (isset($_POST['pk_presentacion']) && is_numeric($_POST['pk_presentacion'])) {
    $pk_presentacion = (int)$_POST['pk_presentacion'];
}


if (!$mysqli->query("UPDATE ct_presentaciones set estado = 0 where pk_presentacion = $pk_presentacion")) {
    $codigo = 201;
    $descripcion = "Error al eliminar el registro";
}


$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
