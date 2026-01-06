<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
mysqli_set_charset($mysqli, 'utf8');
$descripcion = "";
$codigo = 200;


if (isset($_POST['pk_venta']) && is_numeric($_POST['pk_venta'])) {
    $pk_venta = (int) $_POST['pk_venta'];
}

if (isset($_POST['fk_usuario_cancela']) && is_string($_POST['fk_usuario_cancela'])) {
    $fk_usuario_cancela = $_POST['fk_usuario_cancela'];
}

if (isset($_POST['fk_pago']) && is_numeric($_POST['fk_pago'])) {
    $fk_pago = (int) $_POST['fk_pago'];
}

if (isset($_POST['tipo']) && is_numeric($_POST['tipo'])) {
    $tipo = (int) $_POST['tipo'];
}

$hora_actual = date("H") . ":" . date("i") . ":" . date("s");





//DATOS DE LA VENTA
#region
$qventas = "SELECT * FROM tr_ventas WHERE pk_venta = $pk_venta";

if (!$rventas = $mysqli->query($qventas)) {
    $codigo = 201;
    $descripcion = "Error al verificar la venta";
}

$ventas = $rventas->fetch_assoc();
$fk_sucursal = $ventas["fk_sucursal"];
$fk_almacen = $ventas["fk_almacen"];
$saldo = $ventas["saldo"];
//$anticipo = $ventas["anticipo"];
$cambio = ($ventas["total"] - $ventas["anticipo"]);
$cambio >= 0 ? $cambio = 0 : $cambio = abs($cambio);
$monto = $anticipo - $cambio;
$fk_cliente = $ventas["fk_cliente"];
$total = $ventas["total"];
#endregion




//ANTICIPO
#region
$qabonos = "SELECT SUM(monto) as anticipo FROM tr_abonos WHERE fk_factura = $pk_venta AND origen = 1 AND estado = 1";

if (!$rabonos = $mysqli->query($qabonos)) {
    $codigo = 201;
    $descripcion = "Error al obtener el anticipo";
}

$abonos = $rabonos->fetch_assoc();
$anticipo = $abonos["anticipo"];
#endregion




//SI TIENE SALDO PENDIENTE NO PUEDE DEVOLVER LA MERCANCÍA
// if ($saldo > 0) {
//     $codigo = 1;
//     $descripcion = "La cancelación no puede ser efectuada debido a que el cliente tiene un saldo pendiente de $" . $saldo;
// }




//DEVOLUCION
#region
if (!$mysqli->query("INSERT INTO tr_devoluciones (fk_venta, fk_usuario, fk_cliente, fk_pago, observaciones, tipo, fecha, hora, fk_sucursal, saldo, subtotal, total) values ($pk_venta, '$fk_usuario_cancela', $fk_cliente, $fk_pago, 'Cancelación de venta', $tipo, CURDATE(), '$hora_actual', $fk_sucursal, 0, $total, $total)")) {
    $codigo = 201;
    $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
}

$pk_devolucion = $mysqli->insert_id;
#endregion




//ACCIONES DEPENDIENDO DEL TIPO DE CANCELACIÓN
if ($codigo == 200) {
    if ($anticipo > 0) {
        //CON DEVOLUCIÓN (retiro de caja)
        if ($tipo == 1) {

            if (!$mysqli->query("INSERT INTO tr_retiros (fk_sucursal, fk_usuario, fk_retiro, monto, descripcion, fecha, hora, fk_pago) values ($fk_sucursal, '$fk_usuario_cancela', 1, $anticipo, 'Devolución por cancelación de venta', CURDATE(),'$hora_actual', $fk_pago)")) {
                $codigo = 201;
                $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
            }
        }

        //SIN DEVOLUCIÓN (nota de crédito)
        if ($tipo == 2) {

            if (!$mysqli->query("UPDATE tr_devoluciones SET saldo = $anticipo WHERE pk_devolucion = $pk_devolucion")) {
                $codigo = 201;
                $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
            }
        }
    }
}




//PRODUCTOS
if ($codigo == 200) {
    $qventasd = "SELECT * FROM tr_ventas_detalle WHERE fk_venta = $pk_venta";

    if (!$rventasd = $mysqli->query($qventasd)) {
        $codigo = 201;
    }

    $total_devolucion = 0.00;

    while ($ventasd = $rventasd->fetch_assoc()) {

        $total_detalle = $ventasd['cantidad'] * $ventasd['unitario'];

        //DETALLE
        if ($codigo == 200) {
            if (!$mysqli->query("INSERT INTO tr_devoluciones_detalle(fk_devolucion, fk_venta, fk_producto, serie, cantidad, unitario, total) values ($pk_devolucion, $pk_venta, $ventasd[fk_producto], '$ventasd[serie]', $ventasd[cantidad], $ventasd[unitario], $total_detalle)")) {
                $codigo = 201;
                $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
            }
        }

        $total_devolucion += $total_detalle;


        //EXISTENCIAS
        if ($codigo == 200) {

            if (!$mysqli->query("UPDATE tr_existencias SET cantidad = cantidad + $ventasd[cantidad] WHERE fk_sucursal = $fk_sucursal AND fk_producto = $ventasd[fk_producto] AND serie = '$ventasd[serie]' AND fk_almacen = $fk_almacen AND estado = 1")) {
                $codigo = 201;
                $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
            }
        }

        //BITÁCORA DE PRODUCTOS
        #region
        if ($codigo == 200) {
            if (!$mysqli->query("INSERT INTO tr_movimientos (fk_producto, serie, fk_movimiento, fk_movimiento_detalle, fk_usuario, fk_sucursal, fk_almacen, fecha, cantidad, total) values ($ventasd[fk_producto], '$ventasd[serie]', 8, $pk_venta, '$fk_usuario_cancela', $fk_sucursal, $fk_almacen, CURDATE(), $ventasd[cantidad], $ventasd[total])")) {
                $codigo = 201;
                $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
            }
        }
        #endregion
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




//CANCELAR LA VENTA
if ($codigo == 200) {
    if (!$mysqli->query("UPDATE tr_ventas SET estatus = 3, estado = 0, saldo = 0, fk_usuario_cancela = '$fk_usuario_cancela' WHERE pk_venta = $pk_venta")) {
        $codigo = 201;
        $descripcion = "Hubo un problemas, porfavor vuelva a intentarlo!";
    }
}




$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
