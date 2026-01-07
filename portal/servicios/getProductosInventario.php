<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
mysqli_set_charset($mysqli, 'utf8');
$codigo = 200;
$descripcion = "";
$registros = array();
$almacenes = [];
$categorias = [];
$marcas = [];
$productos = [];

if (isset($_GET['fk_sucursal']) && is_numeric($_GET['fk_sucursal'])) {
    $fk_sucursal = (int)$_GET['fk_sucursal'];
}

if (isset($_GET['almacenes']) && is_array($_GET['almacenes'])) {
    $almacenes = $_GET['almacenes'];
}

if (isset($_GET['categorias']) && is_array($_GET['categorias'])) {
    $categorias = $_GET['categorias'];
}

if (isset($_GET['productos']) && is_array($_GET['productos'])) {
    $productos = $_GET['productos'];
}


$count_almacenes = count($almacenes);
$count_categorias = count($categorias);
$count_productos = count($productos);


$filtro_almacenes = "";
if ($count_almacenes > 0) {

    $arrAlmacenes = implode(",", $almacenes);

    $filtro_almacenes = " AND tr_existencias.fk_almacen IN ($arrAlmacenes)";
}


$filtro_categorias = "";
if ($count_categorias > 0) {

    $arrCategorias = implode(",", $categorias);

    $filtro_categorias = " AND ct_productos.fk_categoria IN ($arrCategorias)";
}


$filtro_productos = "";
if ($count_productos > 0) {

    $arrProductos = implode(",", $productos);

    $filtro_productos = " AND tr_existencias.fk_producto IN ($arrProductos)";
}


$qproductos = "SELECT ct_productos.pk_producto as pk_producto,
        ct_sucursales.nombre as sucursal,
        rt_sucursales_almacenes.nombre as almacen,
        SUM(tr_existencias.cantidad) as cantidad,
        ct_productos.clave as clave,
        ct_productos.codigobarras as codigobarras,
        ct_productos.nombre as descripcion,
        tr_existencias.serie as serie,
        (SELECT imagen FROM rt_imagenes_productos WHERE fk_producto = ct_productos.pk_producto AND estado = 1 LIMIT 1) as imagen
    FROM ct_productos, tr_existencias, ct_sucursales, rt_sucursales_almacenes
    WHERE tr_existencias.fk_producto = ct_productos.pk_producto
    AND ct_sucursales.pk_sucursal = tr_existencias.fk_sucursal
    AND tr_existencias.fk_sucursal = $fk_sucursal
    AND rt_sucursales_almacenes.pk_sucursal_almacen = tr_existencias.fk_almacen$filtro_almacenes$filtro_categorias$filtro_productos
    GROUP BY tr_existencias.fk_producto";

if (!$resultado = $mysqli->query($qproductos)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

while ($row = $resultado->fetch_assoc()) {

    $file = "productos/$row[imagen]";
    $pathImage = is_file($file) ? "servicios/productos/$row[imagen]" : "images/picture.png";

    $registros[] = array(
        "pk_producto" => $row['pk_producto'],
        "codigobarras" => $row['codigobarras'],
        "descripcion" => $row['descripcion'],
        "cantidad" => $row['cantidad'],
        "imagen" => $pathImage,
    );
}


$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => $registros);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
