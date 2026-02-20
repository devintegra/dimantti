<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
$codigo = 200;
$descripcion = "";


if (isset($_POST['pk_registro']) && is_numeric($_POST['pk_registro'])) {
    $pk_registro = (int)$_POST['pk_registro'];
}


$target_file = basename($_FILES["archivo"]["name"]);
$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

$new_image_name = $pk_registro . "." . $imageFileType;

if (move_uploaded_file($_FILES["archivo"]["tmp_name"], "pruebas/" . $new_image_name)) {

    $mysqli->next_result();
    if (!$mysqli->query("CALL sp_update_registro_archivo($pk_registro, '$new_image_name')")) {
        $codigo = 201;
        $descripcion = "Error al actualizar el registro";
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
