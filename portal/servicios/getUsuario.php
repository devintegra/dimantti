<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
$codigo = 200;
$descripcion = "";

if (isset($_POST['usuario']) && is_string($_POST['usuario'])) {
    $usuario = $_POST['usuario'];
}

if (isset($_POST['pass']) && is_string($_POST['pass'])) {
    $pass = $_POST['pass'];
}


$eusuario = "SELECT * FROM ct_usuarios WHERE pk_usuario='$usuario' and pass='$pass' and estado=1";

if (!$resultado = $mysqli->query($eusuario)) {
    $codigo = 201;
}

if ($resultado->num_rows < 1) {
    $codigo = 201;
    $descripcion = "Los datos ingresados son incorrectos";
}



$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
