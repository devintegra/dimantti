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
if ($saldo <= 0) {
    $saldo = ($saldo < 0) ? 0 : $saldo;
    $tipo_pago = 1; //total
} else if ($saldo > 0 && $saldo < $total) {
    $tipo_pago = 2; //parcial
} else {
    $tipo_pago = 3; //credito
}
#endregion






//ENCABEZADO
#region
$mysqli->next_result();
if (!$rsp_set_compra = $mysqli->query("CALL sp_set_compra($fk_proveedor, '$fk_usuario', '$inicio', '$fin', $total, $saldo, $fk_pago, $tipo_pago, $tipo, '$factura', $fk_sucursal, $fk_almacen, '$observaciones')")) {
    $codigo = 201;
    $descripcion = "Error al guardar el encabezado de la compra.";
}

$row = $rsp_set_compra->fetch_assoc();
$pk_compra = $row["pk_compra"];
#endregion





//PRODUCTOS
if ($codigo == 200) {

    foreach ($decoded['productos'] as $key => $value) {

        //DATOS DEL PRODUCTO
        #region
        $mysqli->next_result();
        if (!$rdatos = $mysqli->query("CALL sp_get_producto($value[fk_producto])")) {
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


        //DETALLE
        $mysqli->next_result();
        if (!$mysqli->query("CALL sp_set_compra_detalle($pk_compra, $pk_producto, '$nombre_producto', $value[cantidad], $value[unitario], $value[total])")) {
            $codigo = 201;
            $descripcion = "Error al guardar el detalle de la compra";
        }


        //COSTO DEL PRODUCTO
        $mysqli->next_result();
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
            $mysqli->next_result();
            if (!$mysqli->query("UPDATE ct_productos SET precio = $nuevo_precio_1 WHERE pk_producto = $pk_producto")) {
                $codigo = 201;
                $descripcion = "Error al actualizar el precio 1 del producto";
            }
        }
        if ($utilidad2) {
            $nuevo_precio_2 = $value['unitario'] + ($value['unitario'] * ($utilidad2 / 100));
            $mysqli->next_result();
            if (!$mysqli->query("UPDATE ct_productos SET precio2 = $nuevo_precio_2 WHERE pk_producto = $pk_producto")) {
                $codigo = 201;
                $descripcion = "Error al actualizar el precio 2 del producto";
            }
        }
        if ($utilidad3) {
            $nuevo_precio_3 = $value['unitario'] + ($value['unitario'] * ($utilidad3 / 100));
            $mysqli->next_result();
            if (!$mysqli->query("UPDATE ct_productos SET precio3 = $nuevo_precio_3 WHERE pk_producto = $pk_producto")) {
                $codigo = 201;
                $descripcion = "Error al actualizar el precio 3 del producto";
            }
        }
        if ($utilidad3) {
            $nuevo_precio_4 = $value['unitario'] + ($value['unitario'] * ($utilidad4 / 100));
            $mysqli->next_result();
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
        $mysqli->next_result();
        if (!$mysqli->query("CALL sp_set_cargo(1, $fk_sucursal, $pk_compra, $monto_pago, '$fk_usuario', $saldo, $fk_pago)")) {
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
