<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
$codigo = 200;
$descripcion = "";
$existe = 0;
$nentrada = 0;
$fecha = "";
mysqli_set_charset($mysqli, 'utf8');



$qinventario = "SELECT * FROM tr_inventario where estatus = 1 and estado = 1";

if (!$rinventario = $mysqli->query($qinventario)) {
    $codigo = 201;
    $descripcion = "Error al obtener el inventario";
}

if ($rinventario->num_rows > 0) {

    $inventario = $rinventario->fetch_assoc();
    $pk_inventario = $inventario["pk_inventario"];
    $fecha = $inventario["fecha"];
    $existe = 1;
} else {

    $qinventario = "SELECT `AUTO_INCREMENT` AS pk_inventario
            FROM  INFORMATION_SCHEMA.TABLES
            WHERE TABLE_SCHEMA = 'rober248_posmovil'
            AND   TABLE_NAME   = 'tr_inventario'";


    if (!$rinventario = $mysqli->query($qinventario)) {
        echo "<br>Lo sentimos, esta aplicación está experimentando problemas.1";
        exit;
    }

    $inventario = $rinventario->fetch_assoc();

    $pk_inventario = $inventario["pk_inventario"];
}





$mysqli->close();
$detalle = array("existe" => $existe, "pk_inventario" => $pk_inventario, "fecha" => $fecha);
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => $detalle);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
