<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
$codigo = 200;
$descripcion = "";


if (isset($_POST['pk_ruta']) && is_numeric($_POST['pk_ruta'])) {
    $pk_ruta = (int)$_POST['pk_ruta'];
}


if (!$mysqli->query("UPDATE ct_rutas set estado = 0 where pk_ruta = $pk_ruta")) {
    $codigo = 201;
    $descripcion = "Error al eliminar el registro";
}


$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
