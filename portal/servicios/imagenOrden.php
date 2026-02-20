<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
date_default_timezone_set('America/Mexico_City');
$codigo = 200;
$descripcion = "";
$codigod = "Hubo un problema por favor vuelva a intentarlo";



if (isset($_POST['pk_orden']) && is_numeric($_POST['pk_orden'])) {
    $pk_orden = (int)$_POST['pk_orden'];
}

if (isset($_POST['pk_usuario']) && is_string($_POST['pk_usuario'])) {
    $pk_usuario = $_POST['pk_usuario'];
}




$ahora = new DateTime();
$new_image_name = $ahora->getTimestamp() . ".jpg";
$hora_actual = date("H") . ":" . date("i") . ":" . date("s");

if (move_uploaded_file($_FILES["archivo"]["tmp_name"], "pruebas/" . $new_image_name)) {
    $mysqli->next_result();
    if (!$mysqli->query("CALL sp_set_orden_registro($pk_orden, 1, 'Imagen orden $paso', '$pk_usuario', 0, '$new_image_name', 0, 0, 0)")) {
        $codigo = 201;
        $descripcion = "Error al insertar la imagen en bd";
    }
} else {
    $codigo = 201;
    $descripcion = "Error al subir la imagen";
}



$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
