<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
date_default_timezone_set('America/Mexico_City');
$codigo = 200;
$descripcion = "";


if (isset($_POST['pk_subcategoria']) && is_numeric($_POST['pk_subcategoria'])) {
    $pk_subcategoria = (int)$_POST['pk_subcategoria'];
}

if (isset($_POST['fk_categoria']) && is_numeric($_POST['fk_categoria'])) {
    $fk_categoria = (int)$_POST['fk_categoria'];
}

if (isset($_POST['nombre']) && is_string($_POST['nombre'])) {
    $nombre = $_POST['nombre'];
}

if (isset($_POST['estado']) && is_numeric($_POST['estado'])) {
    $estado = (int)$_POST['estado'];
}



if (!$mysqli->query("CALL sp_update_subcategoria($pk_subcategoria, '$nombre', $fk_categoria, $estado)")) {
    $codigo = 201;
    $descripcion = "Error al guardar el registro";
}




$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
