<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
$codigo = 200;
$descripcion = "";
$existe = 0;

if (isset($_POST['usuario']) && is_string($_POST['usuario'])) {
    $usuario = $_POST['usuario'];
}

if (isset($_POST['pass']) && is_string($_POST['pass'])) {
    $pass = $_POST['pass'];
}


$eusuario = "SELECT * FROM ct_usuarios WHERE pk_usuario = '$usuario' AND pass = '$pass' AND estado = 1";
if (!$resultado = $mysqli->query($eusuario)) {
    $codigo = 201;
}

if ($resultado->num_rows > 0) {
    $existe = 1;
    $principal = $resultado->fetch_assoc();
    session_start();
    $_SESSION['nivel'] = $principal["nivel"];
    $_SESSION['usuario'] = $usuario;
    $_SESSION['pass'] = $pass;
    $_SESSION['imagen'] = $principal["imagen"];
    $_SESSION['pk_sucursal'] = $principal["fk_sucursal"];
    $_SESSION['pk_empresa'] = $principal["fk_empresa"];

    $_SESSION['tipo'] = 1;
    $_SESSION['invitado'] = false;
    $_SESSION['nombre'] = $principal["nombre"];
    $_SESSION['correo'] = $principal["correo"];
    $_SESSION['tipo_precio'] = 1;
}



$mysqli->close();
$detalle = array("existe" => $existe);
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => $detalle);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
