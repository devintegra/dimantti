<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
$codigo = 200;
$descripcion = "";
mysqli_set_charset($mysqli, 'utf8');


if (isset($_POST['monto']) && is_numeric($_POST['monto'])) {
    $monto = (float)$_POST['monto'];
}

if (isset($_POST['fk_venta']) && is_numeric($_POST['fk_venta'])) {
    $fk_venta = (int)$_POST['fk_venta'];
}

if (isset($_POST['fk_usuario']) && is_string($_POST['fk_usuario'])) {
    $fk_usuario = $_POST['fk_usuario'];
}

if (isset($_POST['fk_pago']) && is_numeric($_POST['fk_pago'])) {
    $fk_pago = (int)$_POST['fk_pago'];
}

if (isset($_POST['fk_sucursal']) && is_numeric($_POST['fk_sucursal'])) {
    $fk_sucursal = (int)$_POST['fk_sucursal'];
}

$hora_actual = date("H") . ":" . date("i") . ":" . date("s");



//DATOS GENERALES
#region
$qventas = "SELECT * FROM tr_ventas WHERE pk_venta = $fk_venta";

if (!$rventas = $mysqli->query($qventas)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

$ventas = $rventas->fetch_assoc();
$fk_cliente = $ventas["fk_cliente"];
$saldo = $ventas["saldo"];
#endregion



if ($monto <= $saldo) {

    $nsaldo = $saldo - $monto;

    if ($nsaldo == 0) { //1.Total, 2.Parcial
        $tipo = 1;
    } else {
        $tipo = 2;
    }

    $fk_pago == 1 ? $credito_aprobado = 1 : $credito_aprobado = 0;

    if (!$mysqli->query("INSERT INTO tr_abonos (monto, fk_factura, fk_usuario, fecha, hora, fk_sucursal, saldo, tipo, fk_pago, origen, aprobado) VALUES ($monto, $fk_venta, '$fk_usuario', CURDATE(), '$hora_actual', $fk_sucursal, $nsaldo, $tipo, $fk_pago, 1, $credito_aprobado)")) {
        $codigo = 201;
        $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
    }

    if ($codigo == 200) {
        if (!$mysqli->query("UPDATE tr_ventas SET saldo = $nsaldo WHERE pk_venta = $fk_venta")) {
            $codigo = 201;
        }
    }

    if ($codigo == 200) {
        if (!$mysqli->query("UPDATE ct_clientes SET credito = credito + $monto WHERE pk_cliente = $fk_cliente AND estado = 1")) {
            $codigo = 201;
            $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
        }
    }
} else {

    $codigo = 201;
    $descripcion = "El monto ingresado es mayor al saldo actual";
}




$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
