<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
$codigo = 200;
$descripcion = "";
mysqli_set_charset($mysqli, 'utf8');



if (isset($_POST['pk_plantilla']) && is_numeric($_POST['pk_plantilla'])) {
    $pk_plantilla = (int)$_POST['pk_plantilla'];
}

if (isset($_POST['fk_producto']) && is_numeric($_POST['fk_producto'])) {
    $fk_producto = (int)$_POST['fk_producto'];
}



if (!$mysqli->query("UPDATE ct_plantillas_detalle SET estado = 0 WHERE fk_plantilla = $pk_plantilla AND fk_insumo = $fk_producto AND estado = 1")) {
    $codigo = 201;
    $descripcion = "Error al eliminar el registro";
}




$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
