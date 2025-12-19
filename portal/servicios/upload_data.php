<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
mysqli_set_charset($mysqli, 'utf8');
$codigo = 200;
$descripcion = "";



$upload_dir = "firmas/";
$img = $_POST['hidden_data'];
$pk_guia = $_POST['pk_guia'];
$img = str_replace('data:image/png;base64,', '', $img);
$img = str_replace(' ', '+', $img);
$data = base64_decode($img);
$file = $upload_dir . "firma-" . $pk_guia . ".png";
$nfile = "firma-" . $pk_guia . ".png";



if (!$success = file_put_contents($file, $data)) {
    $codigo = 201;
    $descripcion = "Lo sentimos, esta aplicación está experimentando problemas.";
}


if ($codigo == 200) {
    if (!$mysqli->query("UPDATE tr_ordenes set firma='$nfile' where pk_orden=$pk_guia")) {
        $codigo = 201;
        $descripcion = "Lo sentimos, esta aplicación está experimentando problemas.";
    }
}





$mysqli->close();
$detalle = array("nentrada" => $pk_guia);
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => $detalle);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
