<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
$codigo = 200;
$descripcion = "";
$registros = array();


if (isset($_GET['nivel']) && is_numeric($_GET['nivel'])) {
    $nivel = (int)$_GET['nivel'];
}

if (isset($_GET['inicio']) && is_string($_GET['inicio'])) {
    $inicio = $_GET['inicio'];
}

if (isset($_GET['fin']) && is_string($_GET['fin'])) {
    $fin = $_GET['fin'];
}

if (isset($_GET['fk_almacen']) && is_numeric($_GET['fk_almacen'])) {
    $fk_almacen = (int)$_GET['fk_almacen'];
}





$fa = "";
$fp = "";

if ($fk_almacen != 0) {
    $fa = " AND (tr_transferencias.fk_sucursal = $fk_almacen OR tr_transferencias.fk_sucursal_destino = $fk_almacen)";
}

if ($inicio != '' && $fin != '') {
    $fp = " AND tr_transferencias.fecha between '$inicio' AND '$fin'";
}



$qregistros = "SELECT tr_transferencias.*,
    sucursalesa.nombre as origen,
    almacenesa.nombre as almacen_origen,
    sucursalesb.nombre as destino,
    almacenesb.nombre as almacen_destino
    FROM tr_transferencias
    JOIN ct_sucursales sucursalesa ON sucursalesa.pk_sucursal = tr_transferencias.fk_sucursal
    JOIN ct_sucursales sucursalesb ON sucursalesb.pk_sucursal = tr_transferencias.fk_sucursal_destino
    JOIN rt_sucursales_almacenes almacenesa ON almacenesa.pk_sucursal_almacen = tr_transferencias.fk_almacen
    JOIN rt_sucursales_almacenes almacenesb ON almacenesb.pk_sucursal_almacen = tr_transferencias.fk_almacen_destino
    WHERE tr_transferencias.estado = 1$fa $fp
    GROUP BY tr_transferencias.pk_transferencia;";


if (!$resultado = $mysqli->query($qregistros)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}


while ($row = $resultado->fetch_assoc()) {

    $registros[] = array(
        "pk_transferencia" => $row['pk_transferencia'],
        "origen" => $row['origen'],
        "almacen_origen" => $row['almacen_origen'],
        "fk_sucursal_destino" => $row['fk_sucursal_destino'],
        "destino" => $row['destino'],
        "almacen_destino" => $row['almacen_destino'],
        "fecha" => $row['fecha'],
        "hora" => $row['hora'],
        "fk_usuario" => $row['fk_usuario'],
        "observaciones" => $row['observaciones'],
        "estatus" => $row['estatus'],
        "total" => $row['total']
    );
}


$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => $registros);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
