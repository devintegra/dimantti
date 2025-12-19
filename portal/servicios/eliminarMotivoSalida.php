<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
$codigo = 200;
$descripcion = "";


if (isset($_POST['pk_motivo_salida']) && is_numeric($_POST['pk_motivo_salida'])) {
    $pk_motivo_salida = (int)$_POST['pk_motivo_salida'];
}


if (!$mysqli->query("UPDATE ct_motivos_salida set estado=0 where pk_motivo_salida=$pk_motivo_salida")) {
    $codigo = 201;
    $descripcion = "Error al eliminar el registro";
}


$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
