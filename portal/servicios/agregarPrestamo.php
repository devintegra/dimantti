<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
$codigo = 200;
$descripcion = "";

if (isset($_POST['nombre_empleado']) && is_string($_POST['nombre_empleado'])) {
    $nombre_empleado = $_POST['nombre_empleado'];
}

if (isset($_POST['monto']) && is_numeric($_POST['monto'])) {
    $monto = $_POST['monto'];
}

if (isset($_POST['frecuencia']) && is_numeric($_POST['frecuencia'])) {
    $frecuencia = (int)$_POST['frecuencia'];
}

if (isset($_POST['cantidad_pagos']) && is_numeric($_POST['cantidad_pagos'])) {
    $cantidad_pagos = (int)$_POST['cantidad_pagos'];
}

if (isset($_POST['monto_abono']) && is_string($_POST['monto_abono'])) {
    $monto_abono = $_POST['monto_abono'];
}

if (isset($_POST['pago']) && is_numeric($_POST['pago'])) {
    $pago = (int)$_POST['pago'];
}

if (isset($_POST['observaciones']) && is_string($_POST['observaciones'])) {
    $observaciones = $_POST['observaciones'];
}

if (isset($_POST['sucursal']) && is_string($_POST['sucursal'])) {
    $sucursal = $_POST['sucursal'];
}

$sucursal = (!$sucursal || $sucursal == 0) ? 1 : $sucursal;

if (isset($_POST['usuario']) && is_string($_POST['usuario'])) {
    $usuario = $_POST['usuario'];
}

$fecha = date('Y-m-d');
$hora_actual = date("H") . ":" . date("i") . ":" . date("s");



//ENCABEZADO
$mysqli->next_result();
$resultado = $mysqli->query("CALL sp_set_prestamo('$nombre_empleado', $monto, $frecuencia, $cantidad_pagos, $monto_abono, $pago, '$observaciones')");

if ($resultado) {
    $fila = $resultado->fetch_assoc();
    $pk_prestamo = $fila['pk_prestamo'] ?? 0;
} else {
    $codigo = 201;
    $descripcion = "Error al guardar el préstamo";
}



//RETIRO
if ($codigo == 200) {
    $mysqli->next_result();
    if (!$mysqli->query("INSERT INTO tr_retiros (fk_sucursal, fk_usuario, tipo, fk_retiro, monto, descripcion, fecha, hora, fk_pago) VALUES ($sucursal, '$usuario', 4, $pk_prestamo, $monto, '$observaciones', CURDATE(),'$hora_actual', $pago)")) {
        $codigo = 201;
        $descripciond = "Hubo un problema, verifique o intente de nuevo";
    }
}




$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
