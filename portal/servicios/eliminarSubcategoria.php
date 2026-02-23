<?php

header("Access-Control-Allow-Origin: *");
include("conexioni.php");
$codigo = 200;


if (isset($_POST['pk_subcategoria']) && is_numeric($_POST['pk_subcategoria'])) {
    $pk_subcategoria = (int)$_POST['pk_subcategoria'];
}


if (!$mysqli->query("CALL sp_delete_subcategoria($pk_subcategoria)")) {
    $codigo = 201;
    $descripcion = "Error al eliminar el registro";
}


$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
