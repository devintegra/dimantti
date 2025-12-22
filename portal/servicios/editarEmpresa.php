<?php

header("Access-Control-Allow-Origin: *");
include("conexioni.php");
mysqli_set_charset($mysqli, 'utf8');
$codigo = 200;
$descripcion = "";


if (isset($_POST['pk_empresa']) && is_numeric($_POST['pk_empresa'])) {
    $pk_empresa = (int)$_POST['pk_empresa'];
}

if (isset($_POST['nombre']) && is_string($_POST['nombre'])) {
    $nombre = $_POST['nombre'];
}

if (isset($_POST['responsable']) && is_string($_POST['responsable'])) {
    $responsable = $_POST['responsable'];
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

if (isset($_POST['rfc']) && is_string($_POST['rfc'])) {
    $rfc = $_POST['rfc'];
}

if (isset($_POST['regimen_fiscal']) && is_numeric($_POST['regimen_fiscal'])) {
    $regimen_fiscal = (int)$_POST['regimen_fiscal'];
}

if (isset($_POST['cp']) && is_string($_POST['cp'])) {
    $cp = (int)$_POST['cp'];
}

if (isset($_POST['password']) && is_string($_POST['password'])) {
    $password = $_POST['password'];
}



if (!$mysqli->query("UPDATE ct_empresas SET nombre='$nombre', direccion='$direccion', telefono='$telefono', correo='$correo', responsable='$responsable', cp=$cp, rfc='$rfc', pass='$password', fk_regimen_fiscal=$regimen_fiscal where pk_empresa=$pk_empresa")) {
    $codigo = 201;
    $descripcion = "Error al guardar el registro";
}





$mysqli->close();
$detalle = array("pk_empresa" => $pk_empresa);
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => $detalle);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
