<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
mysqli_set_charset($mysqli, 'utf8');
$codigo = 200;
$descripcion = "";




if (isset($_POST['nombre']) && is_string($_POST['nombre'])) {
   $nombre = $_POST['nombre'];
}

if (isset($_POST['recurrente']) && is_numeric($_POST['recurrente'])) {
   $recurrente = (int)$_POST['recurrente'];
}

if (isset($_POST['variable']) && is_numeric($_POST['variable'])) {
   $variable = (int)$_POST['variable'];
}

if (isset($_POST['dia_pago']) && is_numeric($_POST['dia_pago'])) {
   $dia_pago = (int)$_POST['dia_pago'];
} else {
   $dia_pago = 0;
}

if (isset($_POST['cantidad']) && is_numeric($_POST['cantidad'])) {
   $cantidad = (float)$_POST['cantidad'];
}





if (!$mysqli->query("INSERT INTO ct_retiros (nombre, recurrente, variable, dia_pago, cantidad) values ('$nombre', $recurrente, $variable, $dia_pago, $cantidad)")) {
   $codigo = 201;
   $descripcion = "Error al guardar el registro";
}




$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
