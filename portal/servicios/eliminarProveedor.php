<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
$codigo = 200;
$descripcion = "";

if (isset($_POST['pk_cliente']) && is_numeric($_POST['pk_cliente'])) {
    $pk_cliente = (int) $_POST['pk_cliente'];
}




//VALIDAR QUE NO TENGA SALDO PENDIENTE
$qsaldos = "SELECT SUM(IFNULL(saldo,0)) as saldo FROM tr_compras WHERE fk_proveedor = $pk_cliente AND estado = 1;";

if (!$rsaldos = $mysqli->query($qsaldos)) {
    echo "<br>Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

$saldos = $rsaldos->fetch_assoc();
$saldo_pendiente = $saldos["saldo"];

if ($saldo_pendiente > 0) {
    $codigo = 201;
    $descripcion = "El proveedor no puede ser eliminado ya que se tiene un saldo pendiente con el";
}



if ($codigo == 200) {
    if (!$mysqli->query("UPDATE ct_proveedores SET estado = 0 WHERE pk_proveedor = $pk_cliente")) {
        $codigo = 201;
        $descripcion = "Hubo un problema, verifique o intente de nuevo";
    }
}



$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
