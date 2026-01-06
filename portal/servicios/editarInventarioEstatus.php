<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
$codigo = 200;
$descripcion = "";


if (isset($_POST['fk_inventario']) && is_numeric($_POST['fk_inventario'])) {
    $fk_inventario = (int)$_POST['fk_inventario'];
}

if (isset($_POST['tipo']) && is_numeric($_POST['tipo'])) {
    $tipo = (int)$_POST['tipo'];
}


/*
ESTATUS
1.En proceso
2.Finalizado
3.Cancelado
*/


if (!$mysqli->query("UPDATE tr_inventario set estatus = $tipo where pk_inventario = $fk_inventario and estado = 1")) {
    $codigo = 201;
    $descripcion = "Error al actualizar el registro";
}


$mysqli->close();
$detalle = array("pk_inventario" => $fk_inventario);
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => $detalle);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
