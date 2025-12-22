<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
$codigo = 200;
$descripcion = "";

if (isset($_POST['pk_cliente']) && is_numeric($_POST['pk_cliente'])) {
    $pk_cliente = (int) $_POST['pk_cliente'];
}


//VALIDAR QUE NO TENGA EXISTENCIAS LA SUCURSAL
#region
$qexistencias = "SELECT SUM(IFNULL(cantidad,0)) as productos FROM tr_existencias WHERE fk_sucursal = $pk_cliente AND estado = 1;";

if (!$rexistencias = $mysqli->query($qexistencias)) {
    echo "<br>Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

$existencias = $rexistencias->fetch_assoc();
$productos = $existencias["productos"];
#endregion


if ($productos > 0) {
    $codigo = 201;
    $descripcion = "La sucursal no puede ser eliminada ya que cuenta con existencias en almacén";
} else {

    if (!$mysqli->query("UPDATE ct_sucursales set estado=0 where pk_sucursal=$pk_cliente")) {
        $codigo = 201;
        $descripcion = "Hubo un error al eliminar el registro, verifique o vuelva a intentarlo";
    }
}



$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
