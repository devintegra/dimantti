<?php
//CONFIG
#region
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
date_default_timezone_set('America/Mexico_City');
$codigo = 200;
$descripcion = "";
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


//DATOS
#region
$pk_venta = $decoded['pk_venta'];
$fk_usuario = $decoded['fk_usuario'];
$fk_pago = $decoded['fk_pago'];
$tipo = $decoded['tipo'];
$observaciones = $decoded['observaciones'];
$total = (float)$decoded['total'];
$ahora = date("Y-m-d H:i:s");
$hora_actual = date("H") . ":" . date("i") . ":" . date("s");
$fecha = date("Y");
#endregion



//DATOS DE LA VENTA
#region
$qventa = "SELECT * FROM tr_ventas WHERE pk_venta = $pk_venta";
if (!$rventa = $mysqli->query($qventa)) {
    $codigo = 201;
}

$venta = $rventa->fetch_assoc();
$fk_cliente = $venta["fk_cliente"];
$fk_sucursal = $venta["fk_sucursal"];
$fk_almacen = $venta["fk_almacen"];
$saldo = $venta["saldo"];
$anticipo = $venta["anticipo"];
#endregion


//SI TIENE SALDO PENDIENTE NO PUEDE DEVOLVER LA MERCANCÍA
// if ($saldo > 0) {
//     $codigo = 1;
//     $descripcion = "La devolución no puede ser efectuada debido a que el cliente tiene un saldo pendiente de $" . $saldo;
// }




//ENCABEZADO
if ($codigo == 200) {

    if (!$mysqli->query("INSERT INTO tr_devoluciones (fk_venta, fk_usuario, fk_cliente, fecha, hora, fk_sucursal, fk_pago, observaciones, tipo, subtotal, total) values ($pk_venta, '$fk_usuario', $fk_cliente, CURDATE(), '$hora_actual', $fk_sucursal, $fk_pago, '$observaciones', $tipo, $total, $total)")) {
        $codigo = 201;
        $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
    }

    $pk_devolucion = $mysqli->insert_id;
}





//PRODUCTOS
if ($codigo == 200) {

    foreach ($decoded['productos'] as $key => $value) {

        //DATOS DEL PRODUCTO
        #region
        $qdatos = "SELECT * FROM ct_productos WHERE codigobarras = '$value[codigobarras]' AND estado = 1";

        if (!$rdatos = $mysqli->query($qdatos)) {
            echo "<br>Lo sentimos, esta aplicación está experimentando problemas.";
            exit;
        }

        $datos = $rdatos->fetch_assoc();
        $pk_producto = $datos["pk_producto"];
        #endregion


        //DETALLE
        if ($codigo == 200) {
            $total_detalle = $value['cantidad'] * $value['unitario'];

            if (!$mysqli->query("INSERT INTO tr_devoluciones_detalle(fk_devolucion, fk_venta, fk_producto, serie, cantidad, unitario, total) values ($pk_devolucion, $pk_venta, $pk_producto, '$value[serie]', $value[cantidad], $value[unitario], $total_detalle)")) {
                $codigo = 201;
                $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
            }
        }


        //EXISTENCIAS
        if ($codigo == 200) {
            if (!$mysqli->query("UPDATE tr_existencias SET cantidad = cantidad + $value[cantidad] WHERE fk_sucursal = $fk_sucursal AND fk_producto = $pk_producto AND serie = '$value[serie]' AND fk_almacen = $fk_almacen AND estado = 1")) {
                $codigo = 201;
                $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
            }
        }


        //VENTAS DETALLE
        if ($codigo == 200) {
            if (!$mysqli->query("UPDATE tr_ventas_detalle SET faltante = faltante - $value[cantidad], devuelto = 1 WHERE fk_venta = $pk_venta AND fk_producto = $pk_producto AND serie = '$value[serie]' AND devuelto = 0 AND estado = 1 LIMIT 1")) {
                $codigo = 201;
                $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
            }
        }


        //BITÁCORA DE PRODUCTOS
        #region
        if ($codigo == 200) {
            if (!$mysqli->query("INSERT INTO tr_movimientos (fk_producto, serie, fk_movimiento, fk_movimiento_detalle, fk_usuario, fk_sucursal, fk_almacen, fecha, cantidad, total) values ($pk_producto, '$value[serie]', 7, $pk_venta, '$fk_usuario', $fk_sucursal, $fk_almacen, CURDATE(), $value[cantidad], $total_detalle)")) {
                $codigo = 201;
                $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
            }
        }
        #endregion
    }
}





//NOTA DE CREDITO/RETIRO
if ($codigo == 200) {

    if ($anticipo > 0) {
        if ($tipo == 1) { //Con devolución de dinero

            if (!$mysqli->query("INSERT INTO tr_retiros (fk_sucursal, fk_usuario, fk_retiro, monto, descripcion, fecha, hora, fk_pago) VALUES ($fk_sucursal, '$fk_usuario', 1, $anticipo, 'Devolución de venta', CURDATE(),'$hora_actual', $fk_pago)")) {
                $codigo = 201;
                $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
            }

            if (!$mysqli->query("UPDATE tr_devoluciones SET saldo = 0 WHERE pk_devolucion = $pk_devolucion")) {
                $codigo = 201;
                $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
            }
        } else if ($tipo == 2) { //Sin devolución de dinero

            if (!$mysqli->query("UPDATE tr_devoluciones SET saldo = $anticipo WHERE pk_devolucion = $pk_devolucion")) {
                $codigo = 201;
                $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
            }
        }
    }
}





//REINTEGRAR SALDO A SU CREDITO DISPONIBLE
if ($codigo == 200) {

    $saldo_para_nota = abs($total - $anticipo);

    if (!$mysqli->query("UPDATE ct_clientes SET credito = credito + $saldo_para_nota WHERE pk_cliente = $fk_cliente AND estado = 1")) {
        $codigo = 201;
        $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
    }

    if ($codigo == 200) {
        if (!$mysqli->query("UPDATE tr_ventas SET saldo = saldo - $saldo_para_nota WHERE pk_venta = $pk_venta AND estado = 1")) {
            $codigo = 201;
            $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
        }
    }
}






//MODIFICA ESTATUS VENTA
if ($codigo == 200) {
    if (!$mysqli->query("UPDATE tr_ventas SET modificada = 1 WHERE pk_venta = $pk_venta")) {
        $codigo = 201;
        $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
    }
}




//PONER EN ESTATUS 0 LA VENTA SI YA SE DEVOLVÍO TODO
if ($codigo == 200) {

    $qtdev = "SELECT SUM(cantidad) as total FROM tr_devoluciones_detalle WHERE fk_venta = $pk_venta";

    if (!$rtdev = $mysqli->query($qtdev)) {
        $codigo = 201;
        $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
    }

    $tdev = $rtdev->fetch_assoc();
    $total_devuelto = $tdev["total"];



    $qtventa = "SELECT SUM(cantidad) as total FROM tr_ventas_detalle WHERE fk_venta = $pk_venta AND devuelto = 1";

    if (!$rtventa = $mysqli->query($qtventa)) {
        $codigo = 201;
        $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
    }

    $tventa = $rtventa->fetch_assoc();
    $total_venta = $tventa["total"];




    if ($total_devuelto >= $total_venta) {
        if (!$mysqli->query("UPDATE tr_ventas SET estatus = 2, estado = 0 WHERE pk_venta = $pk_venta")) {
            $codigo = 201;
            $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
        }
    }
}






$mysqli->close();
$detalle = array("pk_devolucion" => $pk_devolucion);
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => $detalle);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
