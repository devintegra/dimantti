<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
mysqli_set_charset($mysqli, 'utf8');
$codigo = 200;
$descripcion = "";


if (isset($_POST['fk_compra']) && is_numeric($_POST['fk_compra'])) {
    $fk_compra = (int)$_POST['fk_compra'];
}




//Estatus actual
#region
$qcompra = "SELECT * FROM tr_compras WHERE pk_compra = $fk_compra";

if (!$resultado = $mysqli->query($qcompra)) {
    echo "<br>Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

$rowexistead = $resultado->fetch_assoc();
$aprobado = $rowexistead["aprobado"];
#endregion


if ($aprobado == 0) {
    $aprobacion = 1;
    $descipcion = "Se aprobó la compra correctamente";
} else {
    $aprobacion = 0;
    $descipcion = "Se rechazó la compra correctamente";
}




if (!$mysqli->query("UPDATE tr_compras set aprobado = $aprobacion where pk_compra = $fk_compra")) {
    $codigo = 201;
    $descipcion = "Error al actualizar el registro";
}





$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
