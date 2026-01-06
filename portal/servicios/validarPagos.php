<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
mysqli_set_charset($mysqli, 'utf8');
$codigo = 200;
$descripcion = "";


if (isset($_POST['pk_abono']) && is_numeric($_POST['pk_abono'])) {
    $pk_abono = (int) $_POST['pk_abono'];
}

if (isset($_POST['fk_usuario_valida']) && is_string($_POST['fk_usuario_valida'])) {
    $fk_usuario_valida = $_POST['fk_usuario_valida'];
}

$ahora = date("Y-m-d H:i:s");
$hora_actual = date("H") . ":" . date("i") . ":" . date("s");



//VALIDAR EL PAGO
if ($codigo == 200) {
    if (!$mysqli->query("UPDATE tr_abonos SET aprobado = 1, fecha_validado = '$ahora', fk_usuario_valida = '$fk_usuario_valida' where pk_abono = $pk_abono")) {
        $codigo = 201;
        $descripcion = "Error al actualizar el registro";
    }
}




$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
