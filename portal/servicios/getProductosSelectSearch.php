<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
mysqli_set_charset($mysqli, 'utf8');
$registros = array();


if (isset($_GET['busqueda']) && is_string($_GET['busqueda'])) {
    $busqueda = $_GET['busqueda'];
}


$qproductos = "SELECT pk_producto, codigobarras, nombre, costo, precio, tipo_precio
    FROM ct_productos
    WHERE estado = 1
    AND (codigobarras LIKE '%$busqueda%' OR nombre LIKE '%$busqueda%' OR descripcion LIKE '%$busqueda%' OR precio LIKE '%$busqueda%')";

if (!$rproductos = $mysqli->query($qproductos)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

while ($productos = $rproductos->fetch_assoc()) {

    $productos_precio = ($productos["tipo_precio"] == 1) ? round($productos['precio']) : round($productos["costo"]);

    $descripcion = $productos['codigobarras'] . '|' . $productos['nombre'] . '| $' . $productos_precio;

    $registros[] = array(
        "id" => (int)$productos['pk_producto'],
        "text" => $descripcion
    );
}

$mysqli->close();
$myJSON = json_encode($registros);
header('Content-type: application/json');
echo $myJSON;
