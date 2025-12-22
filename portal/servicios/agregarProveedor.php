<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
$codigo = 200;
$descripcion = "";
mysqli_set_charset($mysqli, 'utf8');


if (isset($_POST['rfc']) && is_string($_POST['rfc'])) {
    $rfc = $_POST['rfc'];
}

if (isset($_POST['nombre']) && is_string($_POST['nombre'])) {
    $nombre = $_POST['nombre'];
}

if (isset($_POST['direccion']) && is_string($_POST['direccion'])) {
    $direccion = $_POST['direccion'];
}

if (isset($_POST['telefono']) && is_string($_POST['telefono'])) {
    $telefono = $_POST['telefono'];
}

if (isset($_POST['correo']) && is_string($_POST['correo'])) {
    $correo = $_POST['correo'];
}

if (isset($_POST['contacto']) && is_string($_POST['contacto'])) {
    $contacto = $_POST['contacto'];
}

if (isset($_POST['credito']) && is_numeric($_POST['credito'])) {
    $credito = (int)$_POST['credito'];
}

if (isset($_POST['dias_credito']) && is_numeric($_POST['dias_credito'])) {
    $dias_credito = (int)$_POST['dias_credito'];
} else {
    $dias_credito = 0;
}



if (!$mysqli->query("INSERT INTO ct_proveedores(rfc, nombre, direccion, telefono, correo, contacto, credito, dias_credito, estado) VALUES ('$rfc', '$nombre', '$direccion', '$telefono', '$correo', '$contacto', $credito, $dias_credito, 1)")) {
    $codigo = 201;
    $descripcion = "Error al guardar el registro";
}





$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
