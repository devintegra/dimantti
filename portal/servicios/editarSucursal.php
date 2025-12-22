<?php
//HEADER
#region
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
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
$pk_cliente = $decoded['pk_cliente'];
$nombre = $decoded['nombre'];
$direccion = $decoded['direccion'];
$latitud = $decoded['latitud'];
$longitud = $decoded['longitud'];
$telefono = $decoded['telefono'];
$correo = $decoded['correo'];
$clave = $decoded['clave'];




if (!$mysqli->query("UPDATE ct_sucursales set nombre='$nombre', direccion='$direccion', latitud='$latitud', longitud='$longitud', telefono='$telefono', correo='$correo', iniciales='$clave' where pk_sucursal=$pk_cliente")) {
    $codigo = 201;
    $descripcion = "Error al guardar el registro";
}




//MOTIVOS DE GASTO
if ($codigo == 200) {

    foreach ($decoded['motivos'] as $key => $value) {

        $qmotivo = "SELECT * FROM rt_sucursales_motivos where fk_sucursal = $pk_cliente and fk_retiro = $value[fk_retiro] and estado = 1";

        if (!$rmotivo = $mysqli->query($qmotivo)) {
            $codigo = 201;
            $descripcion = "Error al verificar el motivo de retiro";
        }

        if ($rmotivo->num_rows == 0) {

            if (!$mysqli->query("INSERT INTO rt_sucursales_motivos (fk_sucursal, fk_retiro, nombre) values ($pk_cliente, $value[fk_retiro], '$value[nombre]')")) {
                $codigo = 201;
                $descripcion = "Error al guardar el motivo de retiro";
            }
        }
    }
}




//ALMACENES
if ($codigo == 200) {

    foreach ($decoded['almacenes'] as $key => $value) {

        $qalmacen = "SELECT * FROM rt_sucursales_almacenes where fk_sucursal = $pk_cliente and nombre = '$value[nombre]' and estado = 1";

        if (!$ralmacen = $mysqli->query($qalmacen)) {
            $codigo = 201;
            $descripcion = "Error al verificar el almacén";
        }

        if ($ralmacen->num_rows == 0) {

            if (!$mysqli->query("INSERT INTO rt_sucursales_almacenes (fk_sucursal, nombre, descripcion) values ($pk_cliente, '$value[nombre]', '$value[descripcion]')")) {
                $codigo = 201;
                $descripcion = "Error al guardar el almacén";
            }
        }
    }
}




$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
