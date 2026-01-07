<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
mysqli_set_charset($mysqli, 'utf8');
$codigo = 200;
$descripcion = "";
$registros = array();


if (isset($_GET['fk_inventario']) && is_numeric($_GET['fk_inventario'])) {
    $fk_inventario = (int)$_GET['fk_inventario'];
}



//FILTROS DEL INVENTARIO
#region
$qinventario = "SELECT * FROM tr_inventario WHERE pk_inventario = $fk_inventario AND estado = 1";

if (!$rinventario = $mysqli->query($qinventario)) {
    echo "<br>Lo sentimos, esta aplicación está experimentando problemas.1";
    exit;
}

$inventario = $rinventario->fetch_assoc();
$fk_sucursal = $inventario["fk_sucursal"];
$almacenes = $inventario["almacenes"];
$categorias = $inventario["categorias"];
$marcas = $inventario["marcas"];
$productos = $inventario["productos"];

$filtro_almacenes = "";
if ($almacenes) {
    $filtro_almacenes = " AND tr_existencias.fk_almacen IN ($almacenes)";
}


$filtro_categorias = "";
if ($categorias) {
    $filtro_categorias = " AND ct_productos.fk_categoria IN ($categorias)";
}


$filtro_marcas = "";
if ($marcas) {
    $filtro_marcas = " AND ct_productos.marca IN ($marcas)";
}


$filtro_productos = "";
if ($productos > 0) {
    $filtro_productos = " AND tr_existencias.fk_producto IN ($productos)";
}
#endregion





//ACTUALIZAR LAS EXISTENCIAS REALES EN TR_INVENTARIO_DETALLE DE ACUERDO AL ALMÁCEN ACTUAL
#region
$qproductos = "SELECT ct_productos.pk_producto as pk_producto,
    ct_sucursales.nombre as sucursal,
    rt_sucursales_almacenes.nombre as almacen,
    SUM(tr_existencias.cantidad) as cantidad,
    ct_productos.clave as clave,
    tr_existencias.fk_producto_nombre as descripcion,
    tr_existencias.serie as serie
    FROM ct_productos, tr_existencias, ct_sucursales, rt_sucursales_almacenes
    WHERE tr_existencias.fk_producto = ct_productos.pk_producto
    AND ct_sucursales.pk_sucursal = tr_existencias.fk_sucursal
    AND tr_existencias.fk_sucursal = $fk_sucursal
    AND rt_sucursales_almacenes.pk_sucursal_almacen = tr_existencias.fk_almacen
    AND tr_existencias.cantidad > 0$filtro_almacenes$filtro_categorias$filtro_marcas$filtro_productos
    GROUP BY tr_existencias.fk_producto";


if (!$rproductos = $mysqli->query($qproductos)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.2. ";
    exit;
}

while ($rowproducto = $rproductos->fetch_assoc()) {

    $qexiste = "SELECT * FROM tr_inventario_detalle WHERE fk_inventario = $fk_inventario AND fk_producto = $rowproducto[pk_producto] AND estado = 1";

    $existencias = $rowproducto['cantidad'];

    if (!$rexiste = $mysqli->query($qexiste)) {
        $error = 1;
    }

    if ($rexiste->num_rows > 0) {

        $rowinventario = $rexiste->fetch_assoc();
        $escaneadas = $rowinventario["escaneadas"];

        if ($escaneadas != '') {

            $existencia_real = $existencias - $escaneadas;

            if (!$mysqli->query("UPDATE tr_inventario_detalle SET existencia_real = '$existencia_real' WHERE fk_inventario = $fk_inventario and fk_producto = $rowproducto[pk_producto] and estado = 1")) {
                $error = 2;
            }
        }
    } else {

        if (!$mysqli->query("INSERT INTO tr_inventario_detalle(fk_inventario, fk_producto, existencias) VALUES($fk_inventario, $rowproducto[pk_producto], $existencias)")) {
            $error = 3;
        }
    }
}
#endregion





//CONSTRUIR LA TABLA PARA LA VISTA
#region
$qproductodetalle = "SELECT ct_productos.pk_producto as pk_producto,
        ct_productos.nombre as nombre_producto,
        ct_sucursales.nombre as sucursal,
        rt_sucursales_almacenes.nombre as almacen,
        SUM(tr_existencias.cantidad) as cantidad,
        ct_productos.clave as clave,
        tr_existencias.fk_producto_nombre as descripcion,
        tr_existencias.serie as serie,
        (SELECT imagen FROM rt_imagenes_productos WHERE fk_producto = ct_productos.pk_producto AND estado = 1 LIMIT 1) as imagen
    FROM ct_productos, tr_existencias, ct_sucursales, rt_sucursales_almacenes
    WHERE tr_existencias.fk_producto = ct_productos.pk_producto
    AND ct_sucursales.pk_sucursal = tr_existencias.fk_sucursal
    AND tr_existencias.fk_sucursal = $fk_sucursal
    AND rt_sucursales_almacenes.pk_sucursal_almacen = tr_existencias.fk_almacen$filtro_almacenes$filtro_categorias$filtro_marcas$filtro_productos
    GROUP BY tr_existencias.fk_producto";

if (!$rproductodetalle = $mysqli->query($qproductodetalle)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.3";
    exit;
}

while ($row = $rproductodetalle->fetch_assoc()) {

    //DATOS DEL PRODUCTO EN TR_INVENTARIO_DETALLE
    #region
    $qinventariodetalle = "SELECT * FROM tr_inventario_detalle WHERE fk_inventario = $fk_inventario AND fk_producto = $row[pk_producto] AND estado = 1";

    if (!$rinventariodetalle = $mysqli->query($qinventariodetalle)) {
        echo "<br>Lo sentimos, esta aplicación está experimentando problemas.4";
        exit;
    }

    $inventariodetalle = $rinventariodetalle->fetch_assoc();
    $inventario_escaneadas = $inventariodetalle["escaneadas"];
    $inventario_existencia_real = $inventariodetalle["existencia_real"];
    #endregion


    $file = "productos/$row[imagen]";
    $pathFile = is_file($file) ? "servicios/productos/$row[imagen]" : "images/picture.png";


    $registros[] = array(
        "pk_producto" => $row['pk_producto'],
        "clave" => $row['clave'],
        "nombre" => $row['nombre_producto'],
        "cantidad" => $row['cantidad'],
        "imagen" => $pathFile,
        "inventario_escaneadas" => $inventario_escaneadas,
        "inventario_existencia_real" => $inventario_existencia_real
    );
}
#endregion



$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => $registros);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
