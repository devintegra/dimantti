<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
mysqli_set_charset($mysqli, 'utf8');
$codigo = 200;
$descripcion = "";


if (isset($_POST['pk_retiro']) && is_numeric($_POST['pk_retiro'])) {
    $pk_retiro = $_POST['pk_retiro'];
}


if (!$mysqli->query("UPDATE ct_retiros set estado=0 where pk_retiro=$pk_retiro")) {
    $codigo = 201;
    $descripcion = "Error al eliminar el registro";
}



$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
