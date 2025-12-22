<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
$codigo = 200;
$descripcion = "";

if (isset($_POST['pk_cliente']) && is_numeric($_POST['pk_cliente'])) {
    $pk_cliente = (int) $_POST['pk_cliente'];
}


//VALIDAR QUE NO TENGA SALDO PENDIENTE
#region
$qsaldos = "SELECT SUM(IFNULL(saldo,0)) as saldo FROM tr_ventas WHERE fk_cliente = $pk_cliente AND estado = 1;";

if (!$rsaldos = $mysqli->query($qsaldos)) {
    echo "<br>Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

$saldos = $rsaldos->fetch_assoc();
$saldo_pendiente = $saldos["saldo"];

if ($saldo_pendiente > 0) {
    $codigo = 201;
    $descripcion = "El cliente no puede ser eliminado ya que tiene un saldo pendiente";
}
#endregion



//VALIDAR QUE NO TENGA PRESTAMOS PENDIENTE
#region
if ($codigo == 200) {
    $qprestamos = "SELECT COUNT(*) as prestamos FROM tr_prestamos WHERE fk_cliente_destino = $pk_cliente AND estatus = 1 AND estado = 1;";

    if (!$rprestamos = $mysqli->query($qprestamos)) {
        echo "<br>Lo sentimos, esta aplicación está experimentando problemas.";
        exit;
    }

    $prestamos = $rprestamos->fetch_assoc();
    $prestamos_pendiente = $prestamos["prestamos"];

    if ($prestamos_pendiente > 0) {
        $codigo = 201;
        $descripcion = "El cliente no puede ser eliminado ya que tiene prestamos pendientes";
    }
}
#endregion


if ($codigo == 200) {
    if (!$mysqli->query("UPDATE ct_clientes SET estado = 0 WHERE pk_cliente = $pk_cliente")) {
        $codigo = 203;
        $descripcion = "Hubo un problema, verifique o vuelva a intentarlo";
    }
}




$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
