<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
$codigo = 200;
$descripcion = "";


if (isset($_POST['pk_categoria']) && is_numeric($_POST['pk_categoria'])) {
    $pk_categoria = (int)$_POST['pk_categoria'];
}


if (!$mysqli->query("CALL sp_delete_categoria($pk_categoria)")) {
    $codigo = 201;
    $descripcion = "Error al eliminar el registro";
}


$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
