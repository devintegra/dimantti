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


//DATOS
$pk_inventario = $decoded['pk_inventario'];
$fk_sucursal = $decoded['fk_sucursal'];
$fk_usuario = $decoded['fk_usuario'];
$almacenes = $decoded['almacenes'];
$categorias = $decoded['categorias'];
$productos = $decoded['productosfl'];

$hora_actual = date("H") . ":" . date("i") . ":" . date("s");


//ENCABEZADO
if ($error == 0) {

    /*
    ESTATUS
    1.En proceso
    2.Terminado
    3.Cancelado
    */

    $qexiste = "SELECT * FROM tr_inventario where pk_inventario = $pk_inventario";

    if (!$rexiste = $mysqli->query($qexiste)) {
        $codigo = 201;
        $descripcion = "Error al verificar el inventario";
    }

    if ($rexiste->num_rows == 0) {

        if (!$mysqli->query("INSERT INTO tr_inventario (fk_sucursal, fk_usuario, almacenes, categorias, marcas, productos, estatus, fecha, hora) values ($fk_sucursal, '$fk_usuario', '$almacenes', '$categorias', '', '$productos', 1, CURDATE(), '$hora_actual')")) {
            $codigo = 201;
            $descripcion = "Error al guardar el registro";
        }
    }
}



//INVENTARIO DETALLE
if ($error == 0) {
    foreach ($decoded['productos'] as $key => $value) {

        $qproducto = "SELECT * FROM tr_inventario_detalle where fk_inventario = $pk_inventario and fk_producto = $value[fk_producto] and estado = 1";

        if (!$rproducto = $mysqli->query($qproducto)) {
            $codigo = 201;
            $descripcion = "Error al verificar el producto";
        }

        if ($rproducto->num_rows > 0) {

            if (!$mysqli->query("UPDATE tr_inventario_detalle set escaneadas = '$value[escaneadas]', existencia_real = '$value[existencia_real]' where fk_inventario = $pk_inventario and fk_producto = $value[fk_producto] and estado = 1")) {
                $codigo = 201;
                $descripcion = "Error al actualizar el producto";
            }
        } else {

            if (!$mysqli->query("INSERT INTO tr_inventario_detalle (fk_inventario, fk_producto, existencias, escaneadas, existencia_real) values ($pk_inventario, $value[fk_producto], $value[existencias], '$value[escaneadas]', '$value[existencia_real]')")) {
                $codigo = 201;
                $descripcion = "Error al guardar el producto";
            }
        }
    }
}







$mysqli->close();
$detalle = array("pk_inventario" => $pk_inventario);
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => $detalle);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
