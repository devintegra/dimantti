<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
$codigo = 200;
$descripcion = "";


if (isset($_POST['pk_usuario']) && is_string($_POST['pk_usuario'])) {
    $pk_usuario = $_POST['pk_usuario'];
}

if (isset($_POST['nombre']) && is_string($_POST['nombre'])) {
    $nombre = $_POST['nombre'];
}

if (isset($_POST['correo']) && is_string($_POST['correo'])) {
    $correo = $_POST['correo'];
}

if (isset($_POST['pass']) && is_string($_POST['pass'])) {
    $pass = $_POST['pass'];
}

if (isset($_POST['nivel']) && is_numeric($_POST['nivel'])) {
    $nivel = (int)$_POST['nivel'];
}

if (isset($_POST['fk_sucursal']) && is_numeric($_POST['fk_sucursal'])) {
    $fk_sucursal = (int)$_POST['fk_sucursal'];
}

if (isset($_POST['avatar']) && is_string($_POST['avatar'])) {
    $avatar = $_POST['avatar'];
}

if (isset($_POST['sueldo']) && is_numeric($_POST['sueldo'])) {
    $sueldo = (float)$_POST['sueldo'];
}

if (isset($_POST['comision']) && is_numeric($_POST['comision'])) {
    $comision = (float)$_POST['comision'];
}

if ($nivel == 1) {
    $fk_sucursal = 0;
}



if (!$mysqli->query("UPDATE ct_usuarios set nombre='$nombre', pass='$pass', correo='$correo', nivel=$nivel, fk_sucursal=$fk_sucursal, sueldo=$sueldo, comision=$comision, imagen='$avatar' where pk_usuario='$pk_usuario'")) {
    $codigo = 201;
    $descripcion = "Error al guardar el registro";
}



$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
