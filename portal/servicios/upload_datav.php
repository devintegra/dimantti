<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
mysqli_set_charset($mysqli, 'utf8');
$error = 0;
$descripcion = "";



$upload_dir = "firmas/";
$img = $_POST['hidden_data'];
$pk_guia = $_POST['pk_guia'];
$img = str_replace('data:image/png;base64,', '', $img);
$img = str_replace(' ', '+', $img);
$data = base64_decode($img);
$file = $upload_dir . "firmaentrega-" . $pk_guia . ".png";
$nfile = "firmaentrega-" . $pk_guia . ".png";


if (!$success = file_put_contents($file, $data)) {
    $error = 1;
    $descripcion = "Lo sentimos, esta aplicación está experimentando problemas.";
}

if ($error == 0) {
    if (!$mysqli->query("UPDATE tr_ventas set firmav='$nfile' where pk_venta=$pk_guia")) {
        $error = 1;
        $descripcion = "Lo sentimos, esta aplicación está experimentando problemas.";
    }
}





$mysqli->close();
$general = array("error" => $error, "mensajea" => $descripcion, "nentrada" => $pk_guia);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
