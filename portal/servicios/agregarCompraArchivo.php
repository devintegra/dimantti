<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
mysqli_set_charset($mysqli, 'utf8');
date_default_timezone_set('America/Mexico_City');
$codigo = 200;
$descripcion = "";


if (isset($_POST['pk_contrato']) && is_numeric($_POST['pk_contrato'])) {
    $pk_contrato = (int)$_POST['pk_contrato'];
}






$target_file = basename($_FILES["archivo"]["name"]);
$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
$new_image_name = "compra-" . $pk_contrato . "." . $imageFileType;
$hora_actual = date("H") . ":" . date("i") . ":" . date("s");


if (move_uploaded_file($_FILES["archivo"]["tmp_name"], "compras/" . $new_image_name)) {

    if (!$mysqli->query("UPDATE tr_compras set archivo='$new_image_name' where pk_compra=$pk_contrato")) {
        $codigo = 201;
        $descripcion = "Error al actualizar el registro";
    }
} else {

    $codigo = 201;
    $descripcion = "Error al guardar el archivo";
}



$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
