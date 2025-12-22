<?php

header("Access-Control-Allow-Origin: *");
include("conexioni.php");
$codigo = 200;
$descripcion = "";

if (isset($_POST['pk_producto']) && is_numeric($_POST['pk_producto'])) {
    $pk_producto = (int) $_POST['pk_producto'];
}



//VALIDAR QUE NO TENGA EXISTENCIAS
$qexistencias = "SELECT SUM(IFNULL(cantidad,0)) as existencias FROM tr_existencias WHERE fk_producto = $pk_producto AND estado = 1;";

if (!$rexistencias = $mysqli->query($qexistencias)) {
    echo "<br>Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

$existencias = $rexistencias->fetch_assoc();
$existencias_producto = $existencias["existencias"];

if ($existencias_producto > 0) {
    $codigo = 201;
    $descripcion = "El producto no puede ser eliminado ya que cuenta con existencias en almacén";
}



if ($codigo == 200) {
    if (!$mysqli->query("UPDATE ct_productos SET estado = 0 WHERE pk_producto = $pk_producto")) {
        $codigo = 201;
        $descripcion = "Hubo un problema, verifique o intente de nuevo";
    }
}



$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
