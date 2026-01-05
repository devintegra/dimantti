<?php
//CONFIG
#region
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
date_default_timezone_set('America/Mexico_City');
$codigo = 200;
$descripcion = "";
$nentrada = 0;
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
$fk_almacen = $decoded["fk_almacen"];
$observaciones = $decoded["observaciones"];
$total = (float)$decoded['total'];
$ahora = date("Y-m-d H:i:s");
$hora_actual = date("H") . ":" . date("i") . ":" . date("s");
$fecha = date("Y");



//SUCURSAL
#region
$qsucursal = "SELECT * FROM rt_sucursales_almacenes WHERE pk_sucursal_almacen = $fk_almacen";

if (!$rsucursal = $mysqli->query($qsucursal)) {
    echo "<br>Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}
$rowsucursal = $rsucursal->fetch_assoc();
$fk_sucursal = $rowsucursal["fk_sucursal"];
#endregion



//ENCABEZADO
#region
$mysqli->next_result();
if (!$mysqli->query("INSERT INTO tr_entradas (fk_compra ,fk_sucursal, fk_almacen, fk_usuario, fecha, hora, total, observaciones) VALUES (0, $fk_sucursal, $fk_almacen, '$fk_usuario', CURDATE(), '$hora_actual', $total, '$observaciones')")) {
    $codigo = 201;
    $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
}

$pk_entrada = $mysqli->insert_id;
#endregion



//PRODUCTOS
if ($codigo == 200) {

    foreach ($decoded['productos'] as $key => $value) {

        //DATOS DEL PRODUCTO
        #region
        $qdatos = "SELECT * FROM ct_productos WHERE pk_producto = $value[fk_producto] AND estado = 1";

        $mysqli->next_result();
        if (!$rdatos = $mysqli->query($qdatos)) {
            echo "<br>Lo sentimos, esta aplicación está experimentando problemas.";
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
        if ($codigo == 200) {
            $mysqli->next_result();
            if (!$mysqli->query("INSERT INTO tr_entradas_detalle (fk_entrada, fk_producto, fk_producto_nombre, cantidad, serie, unitario, total, fk_compra) values ($pk_entrada, $value[fk_producto], '$nombre_producto', $value[cantidad], '$value[serie]', $value[unitario], $value[total], 0)")) {
                $codigo = 201;
                $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
            }
        }


        //EXISTENCIAS
        if ($codigo == 200) {
            $mysqli->next_result();
            if (!$rexistencia = $mysqli->query("CALL sp_update_existencias_entrada($fk_sucursal, $fk_almacen, $value[fk_producto], $value[cantidad], 0)")) {
                $codigo = 201;
                $descripcion = "Error al actualizar las existencias";
            }
        }


        //BITÁCORA DE MOVIMIENTOS
        if ($codigo == 200) {
            if (!$mysqli->query("INSERT INTO tr_movimientos (fk_producto, serie, fk_movimiento, fk_movimiento_detalle, fk_usuario, fk_sucursal, fk_almacen, fecha, cantidad, total) VALUES ($value[fk_producto], '$value[serie]', 1, $pk_entrada, '$fk_usuario', $fk_sucursal, $fk_almacen, CURDATE(), $value[cantidad], $value[total])")) {
                $codigo = 201;
                $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
            }
        }


        //COSTO
        if ($codigo == 200) {
            if (!$mysqli->query("UPDATE ct_productos SET costo = $value[unitario] WHERE pk_producto = $pk_producto")) {
                $codigo = 201;
                $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
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
                    $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
                }
            }
            if ($utilidad2) {
                $nuevo_precio_2 = $value['unitario'] + ($value['unitario'] * ($utilidad2 / 100));
                if (!$mysqli->query("UPDATE ct_productos SET precio2 = $nuevo_precio_2 WHERE pk_producto = $pk_producto")) {
                    $codigo = 201;
                    $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
                }
            }
            if ($utilidad3) {
                $nuevo_precio_3 = $value['unitario'] + ($value['unitario'] * ($utilidad3 / 100));
                if (!$mysqli->query("UPDATE ct_productos SET precio3 = $nuevo_precio_3 WHERE pk_producto = $pk_producto")) {
                    $codigo = 201;
                    $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
                }
            }
            if ($utilidad3) {
                $nuevo_precio_4 = $value['unitario'] + ($value['unitario'] * ($utilidad4 / 100));
                if (!$mysqli->query("UPDATE ct_productos SET precio4 = $nuevo_precio_4 WHERE pk_producto = $pk_producto")) {
                    $codigo = 2010;
                    $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
                }
            }
            #endregion
        }
    }
}






$mysqli->close();
$detalle = array("pk_entrada" => $pk_entrada);
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => $detalle);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
