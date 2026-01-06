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


$flsucursal = ($fk_sucursal != 0) ? " AND rts.fk_sucursal = $fk_sucursal" : "";

$qregistros = "SELECT rts.*,
    cts.nombre as sucursal
FROM rt_sucursales_almacenes rts
JOIN ct_sucursales cts ON cts.pk_sucursal = rts.fk_sucursal
WHERE rts.estado = 1$flsucursal";

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
