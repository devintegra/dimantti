<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
mysqli_set_charset($mysqli, 'utf8');
$codigo = 200;
$descripcion = "";



if (isset($_POST['pk_almacen']) && is_numeric($_POST['pk_almacen'])) {
    $pk_almacen = (int)$_POST['pk_almacen'];
}



if (!$mysqli->query("CALL sp_reactivar_almacen($pk_almacen);")) {
    $codigo = 201;
    $descripcion = "Error al reactivar el almacen";
}




$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
