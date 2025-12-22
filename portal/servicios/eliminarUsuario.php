<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
$codigo = 200;
$descripcion = "";



if (isset($_POST['pk_usuario']) && is_string($_POST['pk_usuario'])) {
    $pk_usuario = $_POST['pk_usuario'];
}



if (!$mysqli->query("UPDATE ct_usuarios set estado=0 where pk_usuario='$pk_usuario'")) {
    $codigo = 201;
    $descripcion = "Error al eliminar el registro";
}



$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
