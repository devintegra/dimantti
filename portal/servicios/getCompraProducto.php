<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
$codigo = 200;
$descripciond = "";
$existe = 1;


if (isset($_POST['fk_compra']) && is_numeric($_POST['fk_compra'])) {
    $fk_compra = (int)$_POST['fk_compra'];
}

if (isset($_POST['pk_producto'][0]) && is_numeric($_POST['pk_producto'][0])) {
    $pk_producto = (int)$_POST['pk_producto'][0];
}




$qcompra = "SELECT tr_compras_detalle.*,
    ct_productos.pk_producto,
    ct_productos.codigobarras,
    ct_productos.nombre
    from tr_compras_detalle, ct_productos
    where tr_compras_detalle.faltante > 0
    and tr_compras_detalle.fk_compra = $fk_compra
    and tr_compras_detalle.fk_producto = $pk_producto
    and ct_productos.pk_producto = tr_compras_detalle.fk_producto";

if (!$rcompra = $mysqli->query($qcompra)) {
    echo "<br>Lo sentimos, esta aplicación está experimentando problemas." . $mysqli->error;
    exit;
}

if ($rcompra->num_rows > 0) {

    $compra = $rcompra->fetch_assoc();
    $codigobarras = $compra["codigobarras"];
    $descripcion = $compra["codigobarras"] . ". " . $compra["nombre"];
    $nombre = $compra["nombre"];
    $cantidad = $compra["cantidad"];
    $faltante = $compra["faltante"];
    $precio = $compra["unitario"];


    //IMAGEN
    #region
    $eimagenes = "SELECT rt_imagenes_productos.imagen as imagen FROM rt_imagenes_productos WHERE fk_producto=$pk_producto AND estado=1";

    if (!$rimagenes = $mysqli->query($eimagenes)) {
        echo "Lo sentimos, esta aplicación está experimentando problemas.2";
        exit;
    }
    $imagenes = $rimagenes->fetch_assoc();
    $imagen = $imagenes["imagen"];
    $file = "productos/$imagen";

    if (is_file($file)) {
        $fondo = "<img style='border-radius: 7px; width:70px; height:70px; object-fit:cover;' loading='lazy' src='servicios/productos/$imagen'>";
    } else {
        $fondo = "<img style='border-radius: 7px; width:70px; height:70px; object-fit:cover;' loading='lazy' src='images/picture.png'>";
    }
    #endregion

} else {

    $existe = 0;
}




$mysqli->close();
$detalle = array(
    "existe" => $existe,
    "pk_producto" => $pk_producto,
    "codigobarras" => $codigobarras,
    "nombre" => $nombre,
    "descripcion" => $descripcion,
    "cantidad" => $cantidad,
    "faltante" => $faltante,
    "precio" => $precio,
    "imagen" => $fondo
);
$general = array("codigo" => $codigo, "descripcion" => $descripciond, "objList" => $detalle);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
