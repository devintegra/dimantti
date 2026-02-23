<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
date_default_timezone_set('America/Mexico_City');
$codigo = 200;
$descripcion = "";



if (isset($_POST['pk_categoria']) && is_numeric($_POST['pk_categoria'])) {
    $pk_categoria = (int)$_POST['pk_categoria'];
}

if (isset($_POST['nombre']) && is_string($_POST['nombre'])) {
    $nombre = $_POST['nombre'];
}

if (isset($_POST['estatus_cliente_venta']) && is_numeric($_POST['estatus_cliente_venta'])) {
    $estatus_cliente_venta = $_POST['estatus_cliente_venta'];
}



if (!$mysqli->query("CALL sp_update_categoria($pk_categoria, '$nombre', $estatus_cliente_venta)")) {
    $codigo = 201;
    $descripcion = "Error al guardar el registro";
}






$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
