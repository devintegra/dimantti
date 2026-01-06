<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
$codigo = 200;
$descripcion = "";
$nentrada = 0;
mysqli_set_charset($mysqli, 'utf8');



if (isset($_POST['fk_cliente']) && is_numeric($_POST['fk_cliente'])) {
    $fk_cliente = (int)$_POST['fk_cliente'];
}



$qcliente = "SELECT * FROM ct_clientes where pk_cliente = $fk_cliente";

if (!$rcliente = $mysqli->query($qcliente)) {
    echo "<br>Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

$cliente = $rcliente->fetch_assoc();

$dias_credito = $cliente["dias_credito"];
$limite_credito = $cliente["limite_credito"];
$credito = $cliente["credito"];






$mysqli->close();
$detalle = array(
    "dias_credito" => $dias_credito,
    "limite" => $limite_credito,
    "credito" => $credito
);
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => $detalle);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
