<?php
//CONFIG
#region
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
require 'correo/PHPMailerAutoload.php';
date_default_timezone_set('America/Mexico_City');
$codigo = 200;
$descripcion = "";
mysqli_set_charset($mysqli, 'utf8');

//Make sure that it is a POST request.
if (strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0) {
    throw new Exception('Request method must be POST!');
}

//Make sure that the content type of the POST request has been set to application/json
$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
if (strcasecmp($contentType, 'application/json; charset=utf-8') != 0) {
    throw new Exception('Content type must be: application/json');
}

//Receive the RAW post data.
$content = trim(file_get_contents("php://input"));

//Attempt to decode the incoming RAW post data from JSON.
$decoded = json_decode($content, true);

//If json_decode failed, the JSON is invalid.
if (!is_array($decoded)) {
    throw new Exception('Received content contained invalid JSON!');
}
#endregion





//REGISTROS
#region
$registros = array();

foreach ($decoded['productos'] as $key => $value) {

    $qproductos = "SELECT p.pk_producto, p.codigobarras, p.nombre, p.descripcion, p.precio, p.tipo_precio, p.gramaje,
            m.precio as precio_gramaje,
            (SELECT imagen FROM rt_imagenes_productos WHERE fk_producto = p.pk_producto and estado = 1 LIMIT 1) as imagen
        FROM ct_productos p
        LEFT JOIN ct_metales m ON m.pk_metal = p.fk_metal
        WHERE p.pk_producto = $value[fk_producto]
        AND p.estado = 1";

    if (!$rproductos = $mysqli->query($qproductos)) {
        echo "Lo sentimos, esta aplicación está experimentando problemas. 1";
        exit;
    }

    $producto = $rproductos->fetch_assoc();
    $pk_producto = $producto["pk_producto"];
    $codigobarras = $producto["codigobarras"];
    $nombre = $producto["nombre"];
    $descripcion = $producto["descripcion"];
    $tipo_precio = $producto["tipo_precio"];
    $gramaje = $producto["gramaje"];
    $precio_gramaje = $producto["precio_gramaje"];
    $imagen = $producto["imagen"];
    $file = "productos/$imagen";
    $pathImage = is_file($file) ? "servicios/productos/$imagen" : "images/picture.png";

    $precio = ($tipo_precio == 1) ? round($producto["precio"]) : round($precio_gramaje * $gramaje);
    $cantidad = $value["cantidad"];
    $total = $precio * $cantidad;


    $registros[] = array(
        "pk_producto" => $pk_producto,
        "codigobarras" => $codigobarras,
        "nombre" => $nombre,
        "imagen" => $pathImage,
        "precio" => number_format($precio, 2),
        "cantidad" => $cantidad,
        "total" => number_format($total, 2)
    );
}
#endregion



$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => $registros);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
