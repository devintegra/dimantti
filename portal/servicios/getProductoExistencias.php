<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
mysqli_set_charset($mysqli, 'utf8');
$codigo = 200;
$descripcion = "";


if (isset($_GET['pk_producto']) && is_numeric($_GET['pk_producto'])) {
    $pk_producto = (int)$_GET['pk_producto'];
}



//EXISTENCIAS
#region
$qexistencias = "SELECT ct_sucursales.nombre as sucursal,
    rt_sucursales_almacenes.nombre as almacen,
    sum(cantidad) as cantidad
    FROM tr_existencias, ct_sucursales, rt_sucursales_almacenes
    WHERE tr_existencias.fk_producto = $pk_producto
    AND tr_existencias.cantidad >= 0
    AND ct_sucursales.pk_sucursal = tr_existencias.fk_sucursal
    AND rt_sucursales_almacenes.pk_sucursal_almacen = tr_existencias.fk_almacen
    AND tr_existencias.estado = 1
    GROUP BY tr_existencias.fk_almacen";

if (!$rexistencias = $mysqli->query($qexistencias)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. Error al obtener las existencias.";
    exit;
}

$cantidades = "EXISTENCIAS ";
while ($existencias = $rexistencias->fetch_assoc()) {
    $cantidades .= "(" . $existencias['sucursal']  . '/' . $existencias['almacen'] . ": " . $existencias['cantidad'] . ")\n";
}
#endregion



//TRANSFERENCIAS
#region
$qtransferencias = "SELECT SUM(td.faltante) as cantidad
    FROM tr_transferencias_detalle td, tr_transferencias t
    WHERE td.fk_producto = $pk_producto
    AND td.faltante > 0
    AND td.estado = 1
    AND t.pk_transferencia = td.fk_transferencia";

if (!$rtransferencias = $mysqli->query($qtransferencias)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. Error al obtener las transferencias";
    exit;
}

$transferencia = $rtransferencias->fetch_assoc();
$transferencia_cantidad = $transferencia["cantidad"];

if ($transferencia_cantidad) {
    $cantidades .= "(EN TRANSFERENCIA: " . $transferencia_cantidad . ")\n";
}
#endregion



//PRESTAMOS
#region
// $qprestamos = "SELECT SUM(td.faltante) as cantidad
//     FROM tr_prestamos_detalle td, tr_prestamos t
//     WHERE td.fk_producto = $pk_producto
//     AND td.faltante > 0
//     AND td.estado = 1
//     AND t.pk_prestamo = td.fk_prestamo";

// if (!$rprestamos = $mysqli->query($qprestamos)) {
//     echo "Lo sentimos, esta aplicación está experimentando problemas. Error al obtener los prestamos";
//     exit;
// }

// $prestamo = $rprestamos->fetch_assoc();
// $prestamo_cantidad = $prestamo["cantidad"];

// if ($prestamo_cantidad) {
//     $cantidades .= "(EN PRESTAMO: " . $prestamo_cantidad . ")\n";
// }
#endregion



$mysqli->close();
$detalle = array("cantidades" => $cantidades);
$general = array("error" => $error, "descripcion" => $descripcion, "objList" => $detalle);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
