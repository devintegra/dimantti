<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
$codigo = 200;
$descripciond = "";
mysqli_set_charset($mysqli, 'utf8');


if (isset($_POST['pk_registro']) && is_numeric($_POST['pk_registro'])) {
    $pk_registro = (int)$_POST['pk_registro'];
}




$mysqli->next_result();
if (!$mysqli->query("CALL sp_update_orden_registro_privacidad($pk_registro)")) {
    $codigo = 201;
    $descripcion = "Error al actualizar la privacidad";
}





$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
