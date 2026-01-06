<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
mysqli_set_charset($mysqli, 'utf8');
$codigo = 200;
$descripcion = "";
$registros = array();

if (isset($_GET['pk_venta']) && is_numeric($_GET['pk_venta'])) {
    $pk_venta = (int)$_GET['pk_venta'];
}


$qproductos = "SELECT ct_productos.codigobarras,
        ct_productos.nombre as descripcion,
        SUM(tr_ventas_detalle.cantidad) as cantidad,
        tr_ventas_detalle.unitario,
        SUM(tr_ventas_detalle.total) as total,
        tr_ventas.descuento
    FROM ct_productos, tr_ventas, tr_ventas_detalle
    WHERE tr_ventas_detalle.fk_venta = $pk_venta
    AND tr_ventas.pk_venta = $pk_venta
    AND ct_productos.pk_producto = tr_ventas_detalle.fk_producto
    AND tr_ventas_detalle.estado = 1
    GROUP BY tr_ventas_detalle.fk_producto;";


if (!$resultado = $mysqli->query($qproductos)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

while ($row = $resultado->fetch_assoc()) {

    $registros[] = $row;
}


$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => $registros);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
