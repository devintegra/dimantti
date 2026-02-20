<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
mysqli_set_charset($mysqli, 'utf8');
$codigo = 200;
$descripcion = "";



if (isset($_POST['pk_orden']) && is_numeric($_POST['pk_orden'])) {
    $pk_orden = (int)$_POST['pk_orden'];
}

if (isset($_POST['monto']) && is_numeric($_POST['monto'])) {
    $monto = $_POST['monto'];
}

if (isset($_POST['fk_usuario']) && is_string($_POST['fk_usuario'])) {
    $fk_usuario = $_POST['fk_usuario'];
}

if (isset($_POST['tipo_pago']) && is_numeric($_POST['tipo_pago'])) {
    $tipo_pago = $_POST['tipo_pago'];
}

$ahora = date("Y-m-d H:i:s");
$hora_actual = date("H") . ":" . date("i") . ":" . date("s");




//SUCURSAL
#region
$qsucursal = "SELECT * FROM ct_sucursales WHERE pk_sucursal = (SELECT fk_sucursal FROM tr_ordenes WHERE pk_orden = $pk_orden)";

if (!$rsucursal = $mysqli->query($qsucursal)) {
    $codigo = 201;
}
$sucursal = $rsucursal->fetch_assoc();
$fk_sucursal = $sucursal["pk_sucursal"];
#endregion





$mysqli->next_result();
if (!$mysqli->query("CALL sp_set_abono(1, 2, $pk_orden, $monto, '$fk_usuario', $fk_sucursal, $tipo_pago, 0)")) {
    $codigo = 201;
    $descripcion = "Error al registrar el abono";
}


if ($codigo == 200) {
    $mysqli->next_result();
    if (!$mysqli->query("CALL sp_set_orden_registro($pk_orden, 1, 'Abono: $$monto_pago', '$fk_usuario', 0, '', 0, 0, 0, 0, 0, 0, 0)")) {
        $codigo = 201;
        $descripcion = "Error al guardar el registro en la bitácora";
    }
}


if ($codigo == 200) {
    $mysqli->next_result();
    if (!$mysqli->query("CALL sp_update_orden_anticipo($pk_orden, $monto)")) {
        $codigo = 201;
        $descripcion = "Error al actualizar el anticipo";
    }
}



$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
