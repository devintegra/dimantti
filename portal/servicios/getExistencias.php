<?php
header('Access-Control-Allow-Origin: *');
include('conexioni.php');
mysqli_set_charset($mysqli, 'utf8');
$codigo = 200;
$descripcion = "";


if (isset($_GET['nivel']) && is_numeric($_GET['nivel'])) {
    $nivel = (int)$_GET['nivel'];
}

if (isset($_GET['tipo_inventario']) && is_numeric($_GET['tipo_inventario'])) {
    $tipo_inventario = (int)$_GET['tipo_inventario'];
}



$currentDate = date('Y-m-d');
$currentDateFormat = new DateTime($currentDate);
$elementos = array();


//FILTROS
#region
$flinventario = '';

if ($tipo_inventario == 1) { //inventario minimo
    $flinventario = " AND tr_existencias.cantidad < ct_productos.inventariomin";
} elseif ($tipo_inventario == 2) { //inventario maximo
    $flinventario = " AND tr_existencias.cantidad > ct_productos.inventariomax";
}
#endregion



//EXISTENCIAS
#region
$qexistencias = "SELECT ct_productos.inventariomin as inventariomin,
    ct_productos.nombre as producto_nombre,
    ct_productos.pk_producto as pk_producto,
    ct_productos.descripcion as descripcion_producto,
    ct_productos.inventariomax as inventariomax,
    ct_sucursales.nombre as sucursal,
    rt_sucursales_almacenes.nombre as almacen,
    SUM(tr_existencias.cantidad) as cantidad,
    ct_productos.codigobarras,
    ct_productos.clave as clave,
    tr_existencias.fk_producto_nombre as descripcion_existencias,
    tr_existencias.pk_existencia as pk_existencia,
    tr_existencias.serie as serie,
    (select imagen from rt_imagenes_productos where fk_producto = ct_productos.pk_producto and estado = 1 limit 1) as imagen
    FROM ct_productos, tr_existencias, ct_sucursales, rt_sucursales_almacenes
    WHERE tr_existencias.fk_producto = ct_productos.pk_producto
    AND ct_sucursales.pk_sucursal = tr_existencias.fk_sucursal$flinventario
    AND rt_sucursales_almacenes.pk_sucursal_almacen = tr_existencias.fk_almacen
    GROUP BY tr_existencias.fk_producto, tr_existencias.serie, tr_existencias.fk_almacen
    ORDER BY tr_existencias.fk_sucursal, tr_existencias.fk_almacen";

if (!$rexistencias = $mysqli->query($qexistencias)) {
    $descripcion = "Error al obtener las existencias";
    echo "Lo sentimos, la aplicación está experimentando problemas. Error al obtener las existencias";
    exit;
}

if (mysqli_num_rows($rexistencias) > 0) {
    while ($row = mysqli_fetch_assoc($rexistencias)) {
        $elementos[] = $row;
    }
}
#endregion





$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => $elementos);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
