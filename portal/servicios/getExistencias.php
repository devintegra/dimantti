<?php
header('Access-Control-Allow-Origin: *');
include('conexioni.php');
mysqli_set_charset($mysqli, 'utf8');
@session_start();
$pk_sucursal = $_SESSION["pk_sucursal"];
$codigo = 200;
$descripcion = "";
$elementos = array();


if (isset($_GET['nivel']) && is_numeric($_GET['nivel'])) {
    $nivel = (int)$_GET['nivel'];
}

if (isset($_GET['tipo_inventario']) && is_numeric($_GET['tipo_inventario'])) {
    $tipo_inventario = (int)$_GET['tipo_inventario'];
}




if (!$rsp_get_existencias = $mysqli->query("CALL sp_get_existencias($pk_sucursal, $tipo_inventario)")) {
    $descripcion = "Error al obtener las existencias";
    echo "Lo sentimos, la aplicación está experimentando problemas. Error al obtener las existencias";
    exit;
}

if (mysqli_num_rows($rsp_get_existencias) > 0) {
    while ($row = mysqli_fetch_assoc($rsp_get_existencias)) {
        $elementos[] = $row;
    }
}





$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => $elementos);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
