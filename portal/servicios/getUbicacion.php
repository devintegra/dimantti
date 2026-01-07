<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
mysqli_set_charset($mysqli, 'utf8');
$codigo = 200;
$descripcion = "";
$latitud = "";
$longitud = "";


if (isset($_GET['usuario']) && is_string($_GET['usuario'])) {
    $usuario = $_GET['usuario'];
}

if (isset($_GET['pk_almacen']) && is_numeric($_GET['pk_almacen'])) {
    $pk_almacen = (int)$_GET['pk_almacen'];
}



//USUARIO
#region
$qestatus = "SELECT * FROM ct_usuarios WHERE pk_usuario='$usuario'";

if (!$restatus = $mysqli->query($qestatus)) {
    $codigo = 201;
    $descripcion = "Hubo un problema, porfavor vuelva a intentarlo";
}

if ($restatus->num_rows > 0) {

    $estatus = $restatus->fetch_assoc();
    $latitud = $estatus["latitud"];
    $longitud = $estatus["longitud"];
}
#endregion



//VENTAS
#region
$qventas = "SELECT * FROM tr_ventas WHERE fk_almacen = $pk_almacen";

if (!$rventas = $mysqli->query($qventas)) {
    $codigo = 201;
    $descripcion = "Hubo un problema, porfavor vuelva a intentarlo";
}

$ventasa = array();

while ($ventas = $rventas->fetch_assoc()) {

    $ventasa[] = array('latitud' => $ventas["latitud"], 'longitud' => $ventas["longitud"]);
}
#endregion



$detalle = array("latitud" => $latitud, "longitud" => $longitud, "ventas" => $ventasa);
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => $detalle);
$myJSON = json_encode($general);
echo $myJSON;
