<?php
//CONFIG
#region
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
date_default_timezone_set('America/Mexico_City');
@session_start();
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
$pk_plantilla = (int)$decoded["pk_plantilla"];
$nombre = $decoded["nombre"];
$fk_usuario = $_SESSION['usuario'];



//ENCABEZADO
#region
if ($pk_plantilla == 0) {

    $mysqli->next_result();
    if (!$rsp_set_plantilla = $mysqli->query("INSERT INTO ct_plantillas(nombre, fk_usuario, fecha) VALUES('$nombre', '$fk_usuario', CURDATE())")) {
        $codigo = 201;
        $descripcion = "Error al guardar el encabezado";
    }

    $pk_plantilla = $mysqli->insert_id;
} else {

    $mysqli->next_result();
    if (!$mysqli->query("UPDATE ct_plantillas SET nombre = '$nombre' WHERE pk_plantilla = $pk_plantilla")) {
        $codigo = 201;
        $descripcion = "Error al guardar el encabezado";
    }
}
#endregion



//DETALLE
#region
if ($codigo == 200) {

    foreach ($decoded['insumos'] as $key => $value) {

        $mysqli->next_result();
        if (!$rsp_get_detalle = $mysqli->query("SELECT * FROM ct_plantillas_detalle WHERE fk_plantilla = $pk_plantilla AND fk_insumo = $value[id] AND estado = 1")) {
            $codigo = 201;
            $descripcion = "Error al verificar el detalle";
        }

        if ($rsp_get_detalle->num_rows == 0) {
            $mysqli->next_result();
            if (!$rsp_get_detalle = $mysqli->query("INSERT INTO ct_plantillas_detalle(fk_plantilla, fk_insumo, fk_insumo_nombre, cantidad) VALUES($pk_plantilla, $value[id], '$value[nombre]', $value[cantidad])")) {
                $codigo = 201;
                $descripcion = "Error al guardar el detalle";
            }
        } else {
            $mysqli->next_result();
            if (!$rsp_get_detalle = $mysqli->query("UPDATE ct_plantillas_detalle SET cantidad = $value[cantidad] WHERE fk_plantilla = $pk_plantilla AND fk_insumo = $value[id] AND estado = 1")) {
                $codigo = 201;
                $descripcion = "Error al guardar el detalle";
            }
        }
    }
}
#endregion





$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
