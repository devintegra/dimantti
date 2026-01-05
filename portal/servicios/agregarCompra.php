<?php
//CONFIG
#region
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
date_default_timezone_set('America/Mexico_City');
$codigo = 200;
$descripcion = "";
$pk_compra = 0;
$saldo = 0.00;
$monto_pago = 0.00;
mysqli_set_charset($mysqli, 'utf8');

//Make sure that it is a POST request.
if (strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0) {
    throw new Exception('Request method must be POST!');
}

//Make sure that the content type of the POST request has been set to application/json
$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
if (strcasecmp($contentType, 'application/json; charset=utf-8') != 0) {
    throw new Exception('Content type must be: application/json');
}

//Receive the RAW post data.
$content = trim(file_get_contents("php://input"));

//Attempt to decode the incoming RAW post data from JSON.
$decoded = json_decode($content, true);

//If json_decode failed, the JSON is invalid.
if (!is_array($decoded)) {
    throw new Exception('Received content contained invalid JSON!');
}
#endregion


$fk_usuario = $decoded['fk_usuario'];
$fk_proveedor = $decoded['fk_proveedor'];
$fk_almacen = $decoded['fk_sucursal'];
$total = (float)$decoded['total'];
$monto_pago = (float)$decoded['monto'];
$fk_pago = $decoded['fk_pago'];
$inicio = $decoded['inicio'];
$fin = $decoded['fin'];
$tipo = $decoded['tipo']; //factura o remisión
$factura = $decoded['factura'];
$observaciones = $decoded['observaciones'];

$ahora = date("Y-m-d H:i:s");
$hora_actual = date("H") . ":" . date("i") . ":" . date("s");



//SUCURSAL
#region
$qsucursal = "SELECT * FROM rt_sucursales_almacenes WHERE pk_sucursal_almacen = $fk_almacen";

if (!$rsucursal = $mysqli->query($qsucursal)) {
    echo "<br>Lo sentimos, esta aplicación está experimentando problemas.1";
    exit;
}
$rowsucursal = $rsucursal->fetch_assoc();
$fk_sucursal = $rowsucursal["fk_sucursal"];
#endregion





//TIPO DE PAGO - SALDO
#region
$saldo = $total - $monto_pago;
if ($saldo == 0) {
    $tipo_pago = 1; //total
} else if ($saldo > 0 && $saldo < $total) {
    $tipo_pago = 2; //parcial
} else {
    $tipo_pago = 3; //credito
}
#endregion






//ENCABEZADO
#region
if (!$mysqli->query("INSERT INTO tr_compras (fk_proveedor, fk_usuario, fecha, hora, fecha_inicio, fecha_limite, total, saldo, fk_pago, tipo_pago, tipo, factura, aprobado, fk_sucursal, fk_almacen, observaciones) VALUES ($fk_proveedor, '$fk_usuario', CURDATE(), '$hora_actual', '$inicio', '$fin', $total, $saldo, $fk_pago, $tipo_pago, $tipo, '$factura', 0, $fk_sucursal, $fk_almacen, '$observaciones')")) {
    $codigo = 201;
    $descripcion = "Error al guardar el encabezado de la compra.";
}

$pk_compra = $mysqli->insert_id;
#endregion





//PRODUCTOS
if ($codigo == 200) {

    foreach ($decoded['productos'] as $key => $value) {

        //DATOS DEL PRODUCTO
        #region
        $qdatos = "SELECT * FROM ct_productos WHERE pk_producto = '$value[fk_producto]' AND estado = 1";

        if (!$rdatos = $mysqli->query($qdatos)) {
            echo "<br>Lo sentimos, esta aplicación está experimentando problemas.2";
            exit;
        }

        $datos = $rdatos->fetch_assoc();
        $pk_producto = $datos["pk_producto"];
        $nombre_producto = $datos["nombre"];
        $utilidad1 = $datos["utilidad"];
        $utilidad2 = $datos["utilidad2"];
        $utilidad3 = $datos["utilidad3"];
        $utilidad4 = $datos["utilidad4"];
        #endregion


        if (!$mysqli->query("INSERT INTO tr_compras_detalle(fk_compra, fk_producto, fk_producto_nombre, cantidad, unitario, total, faltante) VALUES ($pk_compra, $pk_producto, '$nombre_producto', $value[cantidad], $value[unitario], $value[total], $value[cantidad])")) {
            $codigo = 201;
            $descripcion = "Error al guardar el detalle de la compra";
        }

        if (!$mysqli->query("UPDATE ct_productos SET costo = $value[unitario] WHERE pk_producto = $pk_producto")) {
            $codigo = 201;
            $descripcion = "Error al actualizar el costo del producto";
        }

        //UTILIDADES
        #region
        $nuevo_precio_1 = "";
        $nuevo_precio_2 = "";
        $nuevo_precio_3 = "";
        $nuevo_precio_4 = "";
        if ($utilidad1) {
            $nuevo_precio_1 = $value['unitario'] + ($value['unitario'] * ($utilidad1 / 100));
            if (!$mysqli->query("UPDATE ct_productos SET precio = $nuevo_precio_1 WHERE pk_producto = $pk_producto")) {
                $codigo = 201;
                $descripcion = "Error al actualizar el precio 1 del producto";
            }
        }
        if ($utilidad2) {
            $nuevo_precio_2 = $value['unitario'] + ($value['unitario'] * ($utilidad2 / 100));
            if (!$mysqli->query("UPDATE ct_productos SET precio2 = $nuevo_precio_2 WHERE pk_producto = $pk_producto")) {
                $codigo = 201;
                $descripcion = "Error al actualizar el precio 2 del producto";
            }
        }
        if ($utilidad3) {
            $nuevo_precio_3 = $value['unitario'] + ($value['unitario'] * ($utilidad3 / 100));
            if (!$mysqli->query("UPDATE ct_productos SET precio3 = $nuevo_precio_3 WHERE pk_producto = $pk_producto")) {
                $codigo = 201;
                $descripcion = "Error al actualizar el precio 3 del producto";
            }
        }
        if ($utilidad3) {
            $nuevo_precio_4 = $value['unitario'] + ($value['unitario'] * ($utilidad4 / 100));
            if (!$mysqli->query("UPDATE ct_productos SET precio4 = $nuevo_precio_4 WHERE pk_producto = $pk_producto")) {
                $codigo = 201;
                $descripcion = "Error al actualizar el precio 4 del producto";
            }
        }
        #endregion

    }
}



//ABONOS
if ($monto_pago > 0) {
    if ($codigo == 200) {
        if (!$mysqli->query("INSERT INTO tr_cargos (tipo, fk_documento, fk_sucursal, cantidad, fecha, hora, fk_usuario, saldo, fk_pago) VALUES (1, $pk_compra, $fk_sucursal, $monto_pago, CURDATE(), '$hora_actual', '$fk_usuario', $saldo, $fk_pago)")) {
            $codigo = 201;
            $descripcion = "Error al guardar el cargo de la compra";
        }
    }
}




$mysqli->close();
$detalle = array("pk_compra" => $pk_compra);
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => $detalle);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
