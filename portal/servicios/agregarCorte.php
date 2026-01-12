<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
$codigo = 200;
$descripcion = "";

if (isset($_POST['fk_sucursal']) && is_numeric($_POST['fk_sucursal'])) {
    $fk_sucursal = (int)$_POST['fk_sucursal'];
}

if (isset($_POST['fk_usuario']) && is_string($_POST['fk_usuario'])) {
    $fk_usuario = $_POST['fk_usuario'];
}

if (isset($_POST['tipo']) && is_string($_POST['tipo'])) {
    $tipo = $_POST['tipo'];
}

if (isset($_POST['comision']) && is_numeric($_POST['comision'])) {
    $addComision = (int)$_POST['comision'];
}

if (isset($_POST['nivel']) && is_numeric($_POST['nivel'])) {
    $nivel = (int)$_POST['nivel'];
}



$filtro = "";
$filtro_abonos = "";
$filtro_ventas = "";
$filtro_retiro = "";
$filtro_usuario = "";
$filtro_usuario_abonos = "";

if ($fk_sucursal != 0) {
    $filtro_abonos = " AND tr_abonos.fk_sucursal = $fk_sucursal";
    $filtro = " AND fk_sucursal = $fk_sucursal";
    $filtro_ventas = " AND fk_sucursal = $fk_sucursal";
    $filtro_retiro = " AND fk_sucursal = $fk_sucursal";
}

if ($nivel != 1 && $nivel != 2) {
    $filtro_usuario = " AND fk_usuario = '$fk_usuario'";
    $filtro_usuario_abonos = " AND tr_abonos.fk_usuario = '$fk_usuario'";
}


$hora_actual = date("H") . ":" . date("i") . ":" . date("s");


//abonos
$efectivo = 0;
$transferencia = 0;
$credito = 0;
$debito = 0;
$cheque = 0;
$comision = 0;
//retiros
$efectivor = 0;
$transferenciar = 0;
$creditor = 0;
$debitor = 0;
$chequer = 0;
//totales
$efectivot = 0;
$transferenciat = 0;
$creditot = 0;
$debitot = 0;
$chequet = 0;
$total = 0;



//ABONOS TOTAL
#region
if ($tipo == 1) {
    $qabonos = "SELECT tr_abonos.*
        FROM tr_abonos, tr_ventas
        WHERE tr_abonos.fk_corte = 0
        AND tr_abonos.origen IN (4, $tipo)$filtro_abonos$filtro_usuario_abonos
        AND tr_abonos.fk_factura = tr_ventas.pk_venta
        AND tr_abonos.estado = 1";
} else if ($tipo == 2) {
    $qabonos = "SELECT tr_abonos.*
        FROM tr_abonos
        LEFT JOIN tr_ventas ON tr_ventas.pk_venta = tr_abonos.fk_factura
        LEFT JOIN tr_ordenes ON tr_ordenes.fk_venta = tr_ventas.pk_venta
        WHERE tr_abonos.fk_corte = 0
        AND tr_abonos.origen IN (4, $tipo)$filtro_abonos$filtro_usuario_abonos
        AND tr_abonos.estado = 1";
}

if (!$rabonos = $mysqli->query($qabonos)) {
    $codigo = 201;
}

while ($abonos = $rabonos->fetch_assoc()) {

    $pago = $abonos["fk_pago"];

    if ($pago == 1) {
        $efectivo += $abonos["monto"];
    }

    if ($pago == 2) {
        $transferencia += $abonos["monto"];
    }

    if ($pago == 3) {
        $debito += $abonos["monto"];
    }

    if ($pago == 4) {
        $cheque += $abonos["monto"];
    }

    if ($pago == 5) {
        $credito += $abonos["monto"];
    }

    $comision += $abonos["comision"];
}
#endregion



//SALDOS INICIALES
#region
$qsaldos = "SELECT * FROM tr_abonos WHERE estado = 1 AND fk_corte = 0 AND origen = 3$filtro$filtro_usuario";

if (!$rsaldos = $mysqli->query($qsaldos)) {
    $codigo = 201;
}

while ($saldos = $rsaldos->fetch_assoc()) {
    $efectivo += $saldos["monto"];
}
#endregion



//RETIROS TOTAL
#region
$qretiros = "SELECT * FROM tr_retiros WHERE fk_corte = 0 AND estado = 1$filtro$filtro_usuario";

if (!$rretiros = $mysqli->query($qretiros)) {
    $codigo = 201;
}

while ($retiros = $rretiros->fetch_assoc()) {

    $pago = $retiros["fk_pago"];

    if ($pago == 1) {
        $efectivor += $retiros["monto"];
    }

    if ($pago == 2) {
        $transferenciar += $retiros["monto"];
    }

    if ($pago == 3) {
        $debitor += $retiros["monto"];
    }

    if ($pago == 4) {
        $chequer += $retiros["monto"];
    }

    if ($pago == 5) {
        $creditor += $retiros["monto"];
    }
}
#endregion



//TOTALES
#region
$efectivot = $efectivo - $efectivor;
$transferenciat = $transferencia - $transferenciar;
$creditot = $credito - $creditor;
$debitot = $debito - $debitor;
$chequet = $cheque - $chequer;
if ($addComision == 0) {
    $total = $efectivot + $transferenciat + $creditot + $debitot + $chequet;
    $comision = 0;
} else {
    $total = $efectivot + $transferenciat + $creditot + $debitot + $chequet + $comision;
}
#endregion



//INSERCIÓN EN TR_CORTES
#region
if (!$mysqli->query("INSERT INTO tr_cortes (fk_sucursal, fk_usuario, fecha, hora, origen, add_comision, efectivo, transferencia, credito, debito, cheque, comision, total) VALUES ($fk_sucursal, '$fk_usuario', CURDATE(), '$hora_actual', $tipo, $addComision, $efectivot, $transferenciat, $creditot, $debitot, $chequet, $comision, $total)")) {
    $codigo = 201;
    $descripcion = "Error al guardar el registro";
}

$pk_corte = $mysqli->insert_id;


$rabonos->data_seek(0);

while ($abonos = $rabonos->fetch_assoc()) {

    if (!$mysqli->query("INSERT INTO rt_corte_venta (fk_venta, fk_corte) VALUES ($abonos[fk_factura], $pk_corte)")) {
        $codigo = 201;
        $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
    }


    if (!$mysqli->query("UPDATE tr_abonos SET fk_corte = $pk_corte WHERE pk_abono = $abonos[pk_abono]")) {
        $codigo = 201;
        $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
    }
}


//CORTE EN SALDOS INICIALES
$rsaldos->data_seek(0);

while ($saldos = $rsaldos->fetch_assoc()) {

    if (!$mysqli->query("UPDATE tr_abonos SET fk_corte = $pk_corte WHERE pk_abono = $saldos[pk_abono]")) {
        $codigo = 201;
        $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
    }

    if (!$mysqli->query("UPDATE tr_saldos_iniciales SET fk_corte = $pk_corte WHERE pk_saldo_inicial = $saldos[fk_factura]")) {
        $codigo = 201;
        $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
    }
}


//CORTES EN RETIROS
$rretiros->data_seek(0);

while ($retiros = $rretiros->fetch_assoc()) {

    if (!$mysqli->query("UPDATE tr_retiros SET fk_corte = $pk_corte WHERE pk_retiro = $retiros[pk_retiro]")) {
        $codigo = 201;
        $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
    }
}
#endregion





$mysqli->close();
$detalle = array("fk_sucursal" => $fk_sucursal);
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => $detalle);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
