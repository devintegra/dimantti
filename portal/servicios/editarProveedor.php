<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
mysqli_set_charset($mysqli, 'utf8');
$codigo = 200;
$descripcion = "";



if (isset($_POST['pk_cliente']) && is_numeric($_POST['pk_cliente'])) {
    $pk_cliente = (int) $_POST['pk_cliente'];
}

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
}



if (!$mysqli->query("UPDATE ct_proveedores set rfc='$rfc', nombre='$nombre', direccion='$direccion', telefono='$telefono', correo='$correo', contacto='$contacto', credito = $credito, dias_credito = $dias_credito where pk_proveedor=$pk_cliente")) {
    $codigo = 201;
    $descripcion = "Error al guardar el registro";
}




$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
