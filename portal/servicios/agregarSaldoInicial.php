<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
date_default_timezone_set('America/Mexico_City');
$codigo = 200;
$descripcion = "";


if (isset($_POST['fk_sucursal']) && is_numeric($_POST['fk_sucursal'])) {
    $fk_sucursal = (int)$_POST['fk_sucursal'];
}

if (isset($_POST['fk_usuario']) && is_string($_POST['fk_usuario'])) {
    $fk_usuario = $_POST['fk_usuario'];
}

if (isset($_POST['saldo']) && is_numeric($_POST['saldo'])) {
    $saldo = (float)$_POST['saldo'];
}

if (isset($_POST['observaciones']) && is_string($_POST['observaciones'])) {
    $observaciones = $_POST['observaciones'];
}

$hora_actual = date("H") . ":" . date("i") . ":" . date("s");




if (!$mysqli->query("INSERT INTO tr_saldos_iniciales (fk_sucursal, fk_usuario, saldo, observaciones) values ($fk_sucursal, '$fk_usuario', $saldo, '$observaciones')")) {
    $codigo = 201;
    $descripcion = "Error al guardar el registro";
}

$pk_saldo_inicial = $mysqli->insert_id;


//Agregar el abono
if ($codigo == 200) {

    if (!$mysqli->query("INSERT INTO tr_abonos (fk_factura, fk_usuario, origen, monto, saldo, aprobado, fecha, hora, fk_sucursal, tipo, fk_pago, fk_corte) values($pk_saldo_inicial, '$fk_usuario', 3, $saldo, 0, 1, CURDATE(), '$hora_actual', $fk_sucursal, 3, 1, 0)")) {
        $codigo = 201;
        $descripcion = "Error al guardar el abono";
    }
}




$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
