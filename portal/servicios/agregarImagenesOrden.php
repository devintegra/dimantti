<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
mysqli_set_charset($mysqli, 'utf8');
$codigo = 200;
$descripcion = "";
$countfiles = 0;
$arreglo = array();


if (isset($_POST['pk_orden']) && is_numeric($_POST['pk_orden'])) {
    $pk_orden = $_POST['pk_orden'];
}

if (isset($_POST['usuario']) && is_string($_POST['usuario'])) {
    $usuario = $_POST['usuario'];
}

for ($x = 0; $x < 4; $x++) {
    if ($_FILES['archivo']['name'][$x] != "") {
        array_push($arreglo, $_FILES['archivo']['name'][$x]);
    }
}

$countfiles = count($arreglo);

$hora_actual = date("H") . ":" . date("i") . ":" . date("s");

$paso = 1;

for ($i = 0; $i < 4; $i++) {

    if ($_FILES['archivo']['name'][$i] != "") {

        $nombre_archivo = $pk_orden . "-" . $paso . ".jpg";


        if (move_uploaded_file($_FILES["archivo"]["tmp_name"][$i], "pruebas/" . $nombre_archivo)) {

            $mysqli->next_result();
            if (!$mysqli->query("CALL sp_set_orden_registro($pk_orden, 1, 'Imagen orden $paso', '$usuario', 0, '$nombre_archivo', 0, 0, 0, 0, 0, 0, 0)")) {
                $codigo = 201;
                $descripcion = "Hubo un problema al insertar la imagen en bd";
            }
        } else {

            $codigo = 201;
            $descripcion = "Hubo un problema al subir la imagen";
        }

        $paso++;
    }
}





$mysqli->close();
$detalle = array("imgs" => $arreglo);
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
