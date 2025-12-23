<?php
header('Access-Control-Allow-Origin: *');
include('conexioni.php');
mysqli_set_charset($mysqli, 'utf8');
$codigo = 200;
$descripcion = "";
$registros = array();


if (isset($_GET['fk_sucursal']) && is_numeric($_GET['fk_sucursal'])) {
    $fk_sucursal = (int)$_GET['fk_sucursal'];
}



$qregistros = "SELECT * FROM rt_sucursales_almacenes WHERE fk_sucursal = $fk_sucursal AND estado = 1";

if (!$get_registros = $mysqli->query($qregistros)) {
    $descripcion = "Error al obtener las existencias";
    echo "Lo sentimos, la aplicación está experimentando problemas. Error al obtener las existencias";
    exit;
}

while ($row = mysqli_fetch_assoc($get_registros)) {
    $registros[] = $row;
}




$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => $registros);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
