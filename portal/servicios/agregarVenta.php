<?php
//CONFIG
#region
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
date_default_timezone_set('America/Mexico_City');
$codigo = 200;
$pk_venta = 0;
$descripcion = "";
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


//DATOS
#region
$pk_venta = $decoded['pk_venta'];
$fk_sucursal = $decoded['fk_sucursal'];
$fk_almacen = $decoded['fk_almacen'];
$fk_usuario = $decoded['fk_usuario'];
$fk_cliente = $decoded['fk_cliente'];
$fk_cotizacion = $decoded['fk_cotizacion'];
$fk_prestamo = $decoded['fk_prestamo'];

$efectivo = (float)$decoded['efectivo'];
$credito = (float)$decoded['credito'];
$debito = (float)$decoded['debito'];
$cheque = (float)$decoded['cheque'];
$transferencia = (float)$decoded['transferencia'];
$cheque_referencia = $decoded['cheque_referencia'];
$transferencia_referencia = $decoded['transferencia_referencia'];

$credito_cliente = (float)$decoded['credito_cliente'];
$credito_fecha = $decoded['credito_fecha'];
$credito_tipo_pago = (int)$decoded['credito_tipo_pago'];
$credito_saldo = (int)$decoded['credito_saldo'];
$fk_devolucion = (float)$decoded['fk_devolucion']; //Algún crédito del cliente

$descuento = (float)$decoded['descuento'];
$comision = (float)$decoded['comision'];
$monto_pago = (float)$decoded['monto'];
$subtotal = (float)$decoded['subtotal'];
$total = (float)$decoded['total'];

$observaciones = $decoded['observaciones'];
$arrSeries = explode("\n", $observaciones);
$arrCount = count($arrSeries);

$ahora = date("Y-m-d H:i:s");
$hora_actual = date("H") . ":" . date("i") . ":" . date("s");
#endregion


//SOLO SE EJECUTA CON EL BOTÓN DE GUARDAR
//A DIFERENCIA DE agregarVentaAutomatico.php ESTE ARCHIVO MANEJA LAS EXISTENCIAS



//TIPO DE PAGO (1.TOTAL, 2.PARCIAL, 3.CREDITO)
if ($codigo == 200) {

    if ($credito_cliente > 0) { //Es a credito
        $monto_pago = $credito_cliente;
    }

    if ($monto_pago == 0 && $credito_cliente == 0) {
        $monto_pago = 0;
    }

    if ($monto_pago >= $total) {
        $tipo_pago = 1;
        //$saldo = $total - $monto_pago;
        $saldo = 0;
    } else if ($monto_pago == 0) {
        $tipo_pago = 3;
        //$saldo = $credito_cliente;
        $saldo = $total - $monto_pago;
    } else if ($monto_pago > 0 && $monto_pago < $total) {
        $tipo_pago = 2;
        $saldo = $total - $monto_pago;
    }
}



//VENTAS
if ($codigo == 200) {

    //Subtotal = Total de la suma de los productos
    //Saldo = Faltante por pagar
    //Anticipo = El dinero que realmente pagó
    //Total = Total de la venta


    /*
    Tipo
    1.Desde punto de venta
    2.Desde web
    3.Desde prestamo
    4.Desde cotización
    */

    $qventa = "SELECT * FROM tr_ventas WHERE pk_venta = $pk_venta AND estado = 1";

    if (!$rventa = $mysqli->query($qventa)) {
        $codigo = 201;
        $descripcion = "Hubo un problema, porfavor vuelva a intentarlo";
        exit;
    }

    if ($rventa->num_rows > 0) {

        if (!$mysqli->query("UPDATE tr_ventas SET fk_usuario = '$fk_usuario', fk_cliente = $fk_cliente, fecha = CURDATE(), fk_sucursal = $fk_sucursal, fk_almacen = $fk_almacen, saldo = $saldo, anticipo = $monto_pago, efectivo = $efectivo, credito = $credito, debito = $debito, cheque = $cheque, transferencia = $transferencia, cheque_referencia = '$cheque_referencia', transferencia_referencia = '$transferencia_referencia', subtotal = $subtotal, total = $total, hora = '$hora_actual', tipo = 1, tipo_pago = $tipo_pago, descuento = $descuento, comision = $comision, observaciones = '$observaciones' WHERE pk_venta = $pk_venta AND estado = 1")) {
            $codigo = 201;
            $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
        }
    } else {

        if (!$mysqli->query("INSERT INTO tr_ventas (folio, fk_usuario, fk_cliente, fecha, fk_sucursal, fk_almacen, saldo, anticipo, subtotal, total, efectivo, credito, debito, cheque, transferencia, cheque_referencia, transferencia_referencia, hora, tipo, tipo_pago, descuento, comision, observaciones) VALUES ($pk_venta, '$fk_usuario', $fk_cliente, CURDATE(), $fk_sucursal, $fk_almacen, $saldo, $monto_pago, $subtotal, $total, $efectivo, $credito, $debito, $cheque, $transferencia, '$cheque_referencia', '$transferencia_referencia', '$hora_actual', 1, $tipo_pago, $descuento, $comision, '$observaciones')")) {
            $codigo = 201;
            $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
        }

        $pk_venta = $mysqli->insert_id;
    }


    //Generar folio
    #region
    $qsucursal = "SELECT * FROM ct_sucursales WHERE pk_sucursal = (SELECT fk_sucursal FROM tr_ventas WHERE pk_venta = $pk_venta)";

    if (!$rsucursal = $mysqli->query($qsucursal)) {
        echo "Lo sentimos, esta aplicación está experimentando problemas1.";
        exit;
    }
    $sucursal = $rsucursal->fetch_assoc();
    $sucursal_inicial = $sucursal["iniciales"];

    $qentrada = "SELECT * FROM tr_ventas WHERE pk_venta = $pk_venta";

    if (!$rentrada = $mysqli->query($qentrada)) {
        echo "Lo sentimos, esta aplicación está experimentando problemas2.";
        exit;
    }
    $entrada = $rentrada->fetch_assoc();
    $entrada_fecha = $entrada["fecha"];

    $fecha_folio =  str_replace("-", "", $entrada_fecha);
    $folio = "M-" . $sucursal_inicial . $fecha_folio . $pk_venta;

    if (!$mysqli->query("UPDATE tr_ventas SET folio = '$folio' WHERE pk_venta = $pk_venta AND estado = 1")) {
        $codigo = 201;
        $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
    }
    #endregion

}



//VENTAS DETALLE
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
        $nombre_producto = $datos["nombre"];
        #endregion



        //VENTAS DETALLE
        #region
        for ($i = 0; $i < $value['cantidad']; $i++) {

            $total_producto = $value['unitario'] * 1;

            if (!$mysqli->query("INSERT INTO tr_ventas_detalle(fk_producto, serie, cantidad, faltante, unitario, total, fk_venta, descripcion, fk_almacen) values ($pk_producto, '', 1, 1, $value[unitario], $total_producto, $pk_venta, '$nombre_producto', $fk_almacen)")) {
                $codigo = 201;
                $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
            }
            $pk_venta_detalle = $mysqli->insert_id;
        }
        #endregion



        //EXISTENCIAS
        #region
        if ($codigo == 200) {
            $mysqli->next_result();
            if (!$mysqli->query("CALL sp_update_existencias_salida($fk_sucursal, $fk_almacen, $pk_producto, $value[cantidad])")) {
                $codigo = 201;
                $descripcion = "Error al actualizar el almacén";
            }
        }
        #endregion



        //ENTREGAR EN TR_VENTAS_DETALLE
        #region
        if ($codigo == 200) {
            $mysqli->next_result();
            if (!$mysqli->query("UPDATE tr_ventas_detalle SET entregado = 1 WHERE fk_venta = $pk_venta AND fk_producto = $pk_producto AND serie = '' AND entregado = 0 AND estado = 1")) {
                $codigo = 201;
                $descripcion = "Error al actualizar el estatus";
            }
        }
        #endregion



        //MOVIMIENTOS
        #region
        if ($codigo == 200) {
            $mysqli->next_result();
            if (!$mysqli->query("INSERT INTO tr_movimientos (fk_producto, fk_movimiento, fk_movimiento_detalle, fk_usuario, fk_sucursal, fk_almacen, tipo_venta, fecha, serie, cantidad, total) VALUES($pk_producto, 5, $pk_venta, '$fk_usuario', $fk_sucursal, $fk_almacen, 1, CURDATE(), '', $value[cantidad], $value[unitario] * $value[cantidad])")) {
                $codigo = 201;
                $descripcion = "Error al registrar en la bitácora";
            }
        }
        #endregion
    }
}



//CREDITO DEL CLIENTE
if ($codigo == 200) {

    //Datos del cliente
    #region
    $qcliente = "SELECT * FROM ct_clientes WHERE pk_cliente = $fk_cliente";

    if (!$rcliente = $mysqli->query($qcliente)) {
        echo "<br>Lo sentimos, esta aplicación está experimentando problemas.";
        exit;
    }

    $cliente = $rcliente->fetch_assoc();
    $dias_credito = $cliente["dias_credito"];
    #endregion

    if ($credito_saldo > 0) {
        if (!$mysqli->query("UPDATE ct_clientes SET credito = credito - $credito_saldo WHERE pk_cliente = $fk_cliente AND estado = 1")) {
            $codigo = 201;
            $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
        }

        if ($codigo == 200) {
            if (!$mysqli->query("INSERT INTO tr_creditos (fk_venta, fk_cliente, total, saldo, fecha_vencimiento) VALUES ($pk_venta, $fk_cliente, $credito_saldo, $credito_saldo, DATE_ADD(CURDATE(), INTERVAL $dias_credito DAY))")) {
                $codigo = 201;
                $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
            }
        }

        if ($codigo == 200) {
            if ($credito_cliente > 0) {
                $credito_tipo_pago == 1 ? $credito_aprobado = 1 : $credito_aprobado = 0;
                if (!$mysqli->query("INSERT INTO tr_abonos (monto, fk_factura, fk_usuario, fecha, hora, fk_sucursal, saldo, tipo, fk_pago, origen, aprobado) VALUES ($credito_cliente, $pk_venta, '$fk_usuario', CURDATE(), '$hora_actual', $fk_sucursal, $saldo, 2, $credito_tipo_pago, 1, $credito_aprobado)")) {
                    $codigo = 201;
                    $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
                }

                if ($codigo == 200) {
                    switch ($credito_tipo_pago) {
                        case 1:
                            $credito_tipo_pago_name = "efectivo";
                            break;
                        case 2:
                            $credito_tipo_pago_name = "transferencia";
                            break;
                        case 3:
                            $credito_tipo_pago_name = "debito";
                            break;
                        case 1:
                            $credito_tipo_pago_name = "cheque";
                            break;
                        case 1:
                            $credito_tipo_pago_name = "credito";
                            break;
                    }

                    if (!$mysqli->query("UPDATE tr_ventas SET $credito_tipo_pago_name = $credito_cliente WHERE pk_venta = $pk_venta")) {
                        $codigo = 201;
                        $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
                    }
                }
            }
        }
    }
}




//DEVOLUCIONES
if ($codigo == 200) {

    if ($fk_devolucion) {

        //DATOS
        #region
        $qdevolucion = "SELECT * FROM tr_devoluciones WHERE pk_devolucion = $fk_devolucion AND estado = 1";

        if (!$rdevolucion = $mysqli->query($qdevolucion)) {
            echo "<br>Lo sentimos, esta aplicación está experimentando problemas.";
            exit;
        }

        $devolucion = $rdevolucion->fetch_assoc();
        $saldo_devolucion = $devolucion["saldo"];
        #endregion


        if (!$mysqli->query("UPDATE tr_devoluciones SET saldo = 0 WHERE pk_devolucion = $fk_devolucion AND estado = 1")) {
            $codigo = 201;
            $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
        }

        if ($codigo == 200) {
            if (!$mysqli->query("UPDATE tr_ventas SET anticipo = anticipo + $saldo_devolucion, saldo = saldo - $saldo_devolucion, nota_credito = $saldo_devolucion WHERE pk_venta = $pk_venta")) {
                $codigo = 201;
                $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
            }
        }
    }
}




//ABONOS
if ($codigo == 200) {

    if ($monto_pago > 0) {

        //ORIGEN -> 1:Venta , 2:Orden de servicio
        //FK_PAGO -> 1:Efectivo, 2:Transferencia, 3:Debito, 4:Cheque, 5:Credito
        //TIPO -> 1:Total, 2:Parcial, 3:Credito

        if ($efectivo > 0) {
            $efectivo >= $total ? $efectivo = $total : $efectivo = $efectivo;
            if (!$mysqli->query("INSERT INTO tr_abonos (monto, fk_factura, fk_usuario, fecha, hora, fk_sucursal, saldo, tipo, fk_pago, origen, aprobado, comision) VALUES ($efectivo, $pk_venta, '$fk_usuario', CURDATE(), '$hora_actual', $fk_sucursal, $saldo, 2, 1, 1, 1, $comision)")) {
                $codigo = 201;
                $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
            }
        }

        if ($transferencia > 0) {
            $transferencia >= $total ? $transferencia = $total : $transferencia = $transferencia;
            if (!$mysqli->query("INSERT INTO tr_abonos (monto, fk_factura, fk_usuario, fecha, hora, fk_sucursal, saldo, tipo, fk_pago, origen, aprobado, comision) VALUES ($transferencia, $pk_venta, '$fk_usuario', CURDATE(), '$hora_actual', $fk_sucursal, $saldo, 2, 2, 1, 0, $comision)")) {
                $codigo = 201;
                $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
            }
        }

        if ($debito > 0) {
            $debito >= $total ? $debito = $total : $debito = $debito;
            if (!$mysqli->query("INSERT INTO tr_abonos (monto, fk_factura, fk_usuario, fecha, hora, fk_sucursal, saldo, tipo, fk_pago, origen, aprobado, comision) VALUES ($debito, $pk_venta, '$fk_usuario', CURDATE(), '$hora_actual', $fk_sucursal, $saldo, 2, 3, 1, 0, $comision)")) {
                $codigo = 201;
                $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
            }
        }

        if ($cheque > 0) {
            $cheque >= $total ? $cheque = $total : $cheque = $cheque;
            if (!$mysqli->query("INSERT INTO tr_abonos (monto, fk_factura, fk_usuario, fecha, hora, fk_sucursal, saldo, tipo, fk_pago, origen, aprobado, comision) VALUES ($cheque, $pk_venta, '$fk_usuario', CURDATE(), '$hora_actual', $fk_sucursal, $saldo, 2, 4, 1, 0, $comision)")) {
                $codigo = 201;
                $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
            }
        }

        if ($credito > 0) {
            $credito >= $total ? $credito = $total : $credito = $credito;
            if (!$mysqli->query("INSERT INTO tr_abonos (monto, fk_factura, fk_usuario, fecha, hora, fk_sucursal, saldo, tipo, fk_pago, origen, aprobado, comision) VALUES ($credito, $pk_venta, '$fk_usuario', CURDATE(), '$hora_actual', $fk_sucursal, $saldo, 2, 5, 1, 0, $comision)")) {
                $codigo = 201;
                $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
            }
        }
    }
}




//PASAR DE COTIZACION A VENTA DE SER NECESARIO
if ($codigo == 200) {

    if ($fk_cotizacion > 0) {

        if (!$mysqli->query("UPDATE tr_cotizaciones SET estatus = 2 WHERE pk_cotizacion = $fk_cotizacion AND estado = 1")) {
            $codigo = 201;
            $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
        }
    }
}




//CAMBIAR ESTATUS DE PRESTAMOS SI ES QUE LA VENTA VIENE DESDE UN PRESTAMO
#region
// if ($codigo == 200) {

//     if ($fk_prestamo > 0) {

//         if (!$mysqli->query("UPDATE tr_prestamos SET estatus = 3 WHERE pk_prestamo = $fk_prestamo AND estado = 1")) {
//             $codigo = 201;
//             $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
//         }
//     }
// }
#endregion




$mysqli->close();
$detalle = array("pk_venta" => $pk_venta);
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => $detalle);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
