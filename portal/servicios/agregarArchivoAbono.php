<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
mysqli_set_charset($mysqli, 'utf8');
date_default_timezone_set('America/Mexico_City');


if (isset($_POST['pk_abono']) && is_numeric($_POST['pk_abono'])) {
    $pk_abono = (int)$_POST['pk_abono'];
}


$codigo = 200;
$descripcion = "";


$target_file = basename($_FILES["archivo"]["name"]);
$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

//print_r($_FILES);
$new_image_name = "abono-" . $pk_abono . "." . $imageFileType;
$hora_actual = date("H") . ":" . date("i") . ":" . date("s");

if (move_uploaded_file($_FILES["archivo"]["tmp_name"], "abonos/" . $new_image_name)) {
    if (!$mysqli->query("UPDATE tr_abonos SET archivo='$new_image_name' where pk_abono = $pk_abono")) {
        $codigo = 201;
        $descripcion = "Hubo un problema al subir el archivo";
    }
} else {
    $codigo = 201;
    $descripcion = "Error" . $_FILES["archivo"]["error"];
}




$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
