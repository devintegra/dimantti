<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
$codigo = 200;
$descripcion = "";
mysqli_set_charset($mysqli, 'utf8');


if (isset($_POST['pk_prestamo']) && is_numeric($_POST['pk_prestamo'])) {
    $pk_prestamo = (int)$_POST['pk_prestamo'];
}

if (isset($_POST['fk_sucursal']) && is_numeric($_POST['fk_sucursal'])) {
    $fk_sucursal = (int)$_POST['fk_sucursal'];
}

$fk_sucursal = (!$fk_sucursal || $fk_sucursal == 0) ? 1 : $fk_sucursal;

if (isset($_POST['monto']) && is_numeric($_POST['monto'])) {
    $monto = (float)$_POST['monto'];
}

if (isset($_POST['fk_pago']) && is_numeric($_POST['fk_pago'])) {
    $fk_pago = (int)$_POST['fk_pago'];
}

if (isset($_POST['fk_usuario']) && is_string($_POST['fk_usuario'])) {
    $fk_usuario = $_POST['fk_usuario'];
}

$hora_actual = date("H") . ":" . date("i") . ":" . date("s");



//PRESTAMO
$mysqli->next_result();
if (!$rsp_get_prestamo = $mysqli->query("CALL sp_get_prestamo($pk_prestamo)")) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.1";
    exit;
}

$rowc = $rsp_get_prestamo->fetch_assoc();
$saldo = $rowc["saldo"];
$nsaldo = $saldo - $monto;



//ABONO
if ($codigo == 200) {
    $mysqli->next_result();
    if (!$mysqli->query("INSERT INTO tr_abonos (fk_factura, fk_usuario, origen, monto, saldo, aprobado, fecha, hora, fk_sucursal, tipo, fk_pago, fk_corte) values($pk_prestamo, '$fk_usuario', 4, $monto, $nsaldo, 1, CURDATE(), '$hora_actual', $fk_sucursal, 4, $fk_pago, 0)")) {
        $codigo = 201;
        $descripcion = "Error al guardar el abono";
    }
}



//SALDO
if ($codigo == 200) {
    $mysqli->next_result();
    if (!$mysqli->query("CALL sp_update_prestamo_saldo($pk_prestamo, $nsaldo)")) {
        $codigo = 201;
        $descripcion = "Error al actualizar el saldo del prestamo";
    }
}






$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
