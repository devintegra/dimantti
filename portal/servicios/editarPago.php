<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
$codigo = 200;


if (isset($_POST['pk_pago']) && is_numeric($_POST['pk_pago'])) {
    $pk_pago = (int)$_POST['pk_pago'];
}

if (isset($_POST['nombre']) && is_string($_POST['nombre'])) {
    $nombre = $_POST['nombre'];
}

if (isset($_POST['comision']) && is_numeric($_POST['comision'])) {
    $comision = (float)$_POST['comision'];
}


if (!$mysqli->query("UPDATE ct_pagos set nombre='$nombre', comision = $comision where pk_pago=$pk_pago")) {
    $codigo = 201;
    $descripcion = "Error al guardar el registro";
}


$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
