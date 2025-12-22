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



$eusuario = "SELECT * FROM ct_usuarios WHERE pk_usuario = '$pk_usuario'";

if (!$resultado = $mysqli->query($eusuario)) {
    $codigo = 201;
    $descripcion = "Hubo un error al verificar la existencia del usuario.";
}

if ($resultado->num_rows > 0) {
    $codigo = 201;
    $descripcion = "El usuario ya existe en el sistema.";
}


if ($nivel == 1) {
    $fk_sucursal = 0;
}


if ($codigo == 200) {
    if (!$mysqli->query("INSERT INTO ct_usuarios(pk_usuario, nombre, pass, correo, nivel, fk_sucursal, imagen) values ('$pk_usuario', '$nombre', '$pass', '$correo', $nivel, $fk_sucursal, '$avatar')")) {
        $codigo = 201;
        $descripcion = "Hubo un error al guardar al usuario.";
    }
}



$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
