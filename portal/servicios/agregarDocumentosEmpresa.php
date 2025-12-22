<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
mysqli_set_charset($mysqli, 'utf8');
$codigo = 200;
$descripcion = "";
$countfiles = 0;
$nombre_archivo  = "";
$arreglo = array();


if (isset($_POST['pk_empresa']) && is_numeric($_POST['pk_empresa'])) {
    $pk_empresa = $_POST['pk_empresa'];
}



//FORMATO CER
#region
$files = $_FILES['cer'];

$target_file = basename($_FILES["cer"]["name"]);
$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

$hora_actual = date("H") . ":" . date("i") . ":" . date("s");


if ($_FILES['cer']['name'] != "") {

    $nombre_archivo = $pk_empresa . "-" . $imageFileType;

    if (move_uploaded_file($_FILES["cer"]["tmp_name"], "sat/" . $nombre_archivo)) {

        $cer_path = "sat/$nombre_archivo";
        $csd_contents = file_get_contents("$cer_path");
        $csd = base64_encode($csd_contents);

        if (!$mysqli->query("UPDATE ct_empresas set cer = '$csd' where pk_empresa = $pk_empresa")) {
            $codigo = 201;
            $descripcion = "Hubo un problema al insertar el documento en bd";
        }
    } else {

        $codigo = 201;
        $descripcion = "Hubo un problema al subir el documento";
    }
}
#endregion


//FORMATO KEY
#region
$nombre_archivo = "";
$files = $_FILES['key'];

$target_file = basename($_FILES["key"]["name"]);
$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

$hora_actual = date("H") . ":" . date("i") . ":" . date("s");


if ($_FILES['key']['name'] != "") {

    $nombre_archivo = $pk_empresa . "-" . $imageFileType;

    if (move_uploaded_file($_FILES["key"]["tmp_name"], "sat/" . $nombre_archivo)) {

        $key_path = "sat/$nombre_archivo";
        $key_contents = file_get_contents("$key_path");
        $key = base64_encode($key_contents);

        if (!$mysqli->query("UPDATE ct_empresas set fkey = '$key' where pk_empresa = $pk_empresa")) {
            $codigo = 201;
            $descripcion = "Hubo un problema al insertar el documento en bd";
        }
    } else {

        $codigo = 201;
        $descripcion = "Hubo un problema al subir el documento";
    }
}
#endregion




$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
