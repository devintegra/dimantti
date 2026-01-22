<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
$codigo = 200;
$descripcion = "";


if (isset($_POST['pk_pago']) && is_numeric($_POST['pk_pago'])) {
    $pk_pago = (int)$_POST['pk_pago'];
}

if (isset($_POST['estatus']) && is_numeric($_POST['estatus'])) {
    $estatus = (int)$_POST['estatus'];
}



if (!$mysqli->query("UPDATE ct_pagos SET ruta = $estatus WHERE pk_pago = $pk_pago")) {
    $codigo = 201;
    $descripcion = "Error al guardar el registro";
}



$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
