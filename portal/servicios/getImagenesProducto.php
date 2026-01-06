<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
mysqli_set_charset($mysqli, 'utf8');

if (isset($_GET['pk_producto']) && is_numeric($_GET['pk_producto'])) {
    $pk_producto = (int)$_GET['pk_producto'];
}

$imagenes = array();

$qimagenes = "SELECT * FROM rt_imagenes_productos WHERE fk_producto = $pk_producto AND estado = 1";


if (!$rimagenes = $mysqli->query($qimagenes)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

if ($rimagenes->num_rows > 0) {

    while ($img = $rimagenes->fetch_assoc()) {
        array_push($imagenes, $img['imagen']);
    }
}

$myJSON = json_encode($imagenes);
header('Content-type: application/json');
echo $myJSON;
