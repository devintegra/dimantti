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



//OBTENER LA CATEGORÍA DEL CLIENTE
#region
$fk_cliente = $decoded['fk_cliente'];

$qcliente = "SELECT * FROM ct_clientes WHERE pk_cliente = $fk_cliente AND estado = 1";

if (!$rcliente = $mysqli->query($qcliente)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. 1";
    exit;
}

$cliente = $rcliente->fetch_assoc();
$fk_categoria = $cliente["fk_categoria_cliente"];
#endregion




//REGISTROS
#region
$registros = array();

foreach ($decoded['productos'] as $key => $value) {

    $qproductos = "SELECT pk_producto, codigobarras, nombre, descripcion, precio, precio2, precio3, precio4,
            (SELECT imagen FROM rt_imagenes_productos WHERE fk_producto = ct_productos.pk_producto and estado = 1 LIMIT 1) as imagen
        FROM ct_productos
        WHERE ct_productos.pk_producto = $value[fk_producto]
        AND ct_productos.estado = 1";

    if (!$rproductos = $mysqli->query($qproductos)) {
        echo "Lo sentimos, esta aplicación está experimentando problemas. 1";
        exit;
    }

    $producto = $rproductos->fetch_assoc();
    $pk_producto = $producto["pk_producto"];
    $codigobarras = $producto["codigobarras"];
    $nombre = $producto["nombre"];
    $descripcion = $producto["descripcion"];
    $imagen = $producto["imagen"];
    $file = "productos/$imagen";
    $pathImage = is_file($file) ? "servicios/productos/$imagen" : "images/picture.png";

    switch ($fk_categoria) {
        case 1:
            $precio = round($producto["precio"]);
            break;

        case 2:
            $precio = round($producto["precio2"]);
            break;

        case 3:
            $precio = round($producto["precio3"]);
            break;

        case 4:
            $precio = round($producto["precio4"]);
            break;

        default:
            $precio = round($producto["precio"]);
            break;
    }

    $cantidad = $value["cantidad"];
    $total = $precio * $cantidad;


    $registros[] = array(
        "pk_producto" => $pk_producto,
        "codigobarras" => $codigobarras,
        "nombre" => $nombre,
        "imagen" => $pathImage,
        "precio" => $precio,
        "cantidad" => $cantidad,
        "total" => $total
    );
}
#endregion



$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => $registros);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
