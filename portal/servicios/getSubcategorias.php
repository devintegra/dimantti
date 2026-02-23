<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
mysqli_set_charset($mysqli, 'utf8');
$codigo = 200;
$descripcion = "";
$registros = array();


if (isset($_GET['pk_categoria']) && is_numeric($_GET['pk_categoria'])) {
    $pk_categoria = $_GET['pk_categoria'];
}



$mysqli->next_result();
if (!$get_registros = $mysqli->query("CALL sp_get_subcategorias_by_categoria($pk_categoria)")) {
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
