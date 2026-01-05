<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
mysqli_set_charset($mysqli, 'utf8');
$codigo = 200;
$descripcion = "";
$registros = array();


if (isset($_GET['fk_compra']) && is_numeric($_GET['fk_compra'])) {
    $fk_compra = $_GET['fk_compra'];
}



$qregistros = "SELECT trc.*,
        ctp.nombre,
        ctp.codigobarras
    FROM tr_compras_detalle trc
    JOIN ct_productos ctp ON ctp.pk_producto = trc.fk_producto
    WHERE trc.fk_compra = $fk_compra
    AND trc.estado = 1";

if (!$get_registros = $mysqli->query($qregistros)) {
    $codigo = 201;
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

while ($row = $get_registros->fetch_assoc()) {

    $registros[] = $row;
}



$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => $registros);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
