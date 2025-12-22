<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
$codigo = 200;
$descripcion = "";



if (isset($_POST['fk_imagen_producto']) && is_numeric($_POST['fk_imagen_producto'])) {
    $fk_imagen_producto = (int) $_POST['fk_imagen_producto'];
}




$qimagen = "SELECT * FROM rt_imagenes_productos where pk_imagen_producto = $fk_imagen_producto";

if (!$rimagen = $mysqli->query($qimagen)) {
    echo "<br>Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

$imagen = $rimagen->fetch_assoc();
$filename = $imagen["imagen"];



if (!$mysqli->query("UPDATE rt_imagenes_productos set estado = 0 where pk_imagen_producto = $fk_imagen_producto")) {
    $codigo = 201;
    $descripcion = "Error al eliminar el registro";
}


$file = "productos/$filename";

if (is_file($file)) {
    unlink($file);
}



$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
