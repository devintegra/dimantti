<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
mysqli_set_charset($mysqli, 'utf8');
@session_start();
$codigo = 200;
$descripcion = "";
$pk_ruta = 0;


if (isset($_POST['fk_sucursal']) && is_numeric($_POST['fk_sucursal'])) {
    $fk_sucursal = (int)$_POST['fk_sucursal'];
}

if (isset($_POST['fk_almacen']) && is_numeric($_POST['fk_almacen'])) {
    $fk_sucursal_almacen = (int)$_POST['fk_almacen'];
}

if (isset($_POST['pk_usuario']) && is_string($_POST['pk_usuario'])) {
    $pk_usuario = $_POST['pk_usuario'];
}

if (isset($_POST['fecha']) && is_string($_POST['fecha'])) {
    $fecha = $_POST['fecha'];
}

if (isset($_POST['pk_plantilla']) && is_numeric($_POST['pk_plantilla'])) {
    $pk_plantilla = (int)$_POST['pk_plantilla'];
}

if (isset($_POST['fk_ruta']) && is_numeric($_POST['fk_ruta'])) {
    $fk_ruta = (int)$_POST['fk_ruta'];
}



//ALMACENES PENDIENTES
if ($codigo == 200) {

    $qpendientes = "SELECT * FROM tr_almacen WHERE estatus < 3 AND fk_usuario = '$pk_usuario'";

    $mysqli->next_result();
    if (!$rpendientes = $mysqli->query($qpendientes)) {
        $codigo = 201;
        $descripcion = "Hubo un problema, porfavor vuelva a intentarlo";
    }

    while ($pendientes = $rpendientes->fetch_assoc()) {

        $fk_almacen = $pendientes["pk_almacen"];
        $fk_rutaa = $pendientes["fk_ruta"];
        $fk_usuario = $pendientes["fk_usuario"];


        //TOTAL DE VENTAS
        #region
        $qtotal = "SELECT SUM(total) as total FROM tr_ventas WHERE fk_almacen = $fk_almacen AND fk_corte = 0";

        $mysqli->next_result();
        if (!$rtotal = $mysqli->query($qtotal)) {
            $codigo = 201;
            $descripcion = "Hubo un problema, al obtener el total";
        }

        $eltotal = $rtotal->fetch_assoc();
        $total = $eltotal["total"];
        #endregion


        //CORTE
        #region
        if ($total != null && $total != "null") {
            $mysqli->next_result();
            if (!$mysqli->query("INSERT INTO tr_cortes (fecha, fk_ruta, fk_usuario, total, fk_almacen) VALUES (CURDATE(), $fk_rutaa, '$fk_usuario', $total, $fk_almacen)")) {
                $codigo = 201;
                $descripcion = "Error al insertar el corte";
            }
            $pk_corte = $mysqli->insert_id;

            if ($codigo == 200) {
                $mysqli->next_result();
                if (!$mysqli->query("UPDATE tr_ventas SET fk_corte=$pk_corte WHERE fk_almacen=$fk_almacen and fk_corte=0")) {
                    $codigo = 201;
                    $descripcion = "Error al actualizar ventas";
                }

                $mysqli->next_result();
                if (!$mysqli->query("UPDATE tr_almacen SET estatus=3, estado=0 WHERE pk_almacen=$fk_almacen")) {
                    $codigo = 201;
                    $descripcion = "Error al actualizar almacen";
                }

                $mysqli->next_result();
                if (!$mysqli->query("UPDATE ct_usuarios SET estatus=0 WHERE pk_usuario='$fk_usuario'")) {
                    $codigo = 201;
                    $descripcion = "Error al actualizar vendedor";
                }
            }
        } else {

            $mysqli->next_result();
            if (!$mysqli->query("UPDATE tr_almacen SET estatus=3, estado=0 WHERE pk_almacen=$fk_almacen")) {
                $codigo = 201;
                $descripcion = "Error al actualizar almacen";
            }

            $mysqli->next_result();
            if (!$mysqli->query("UPDATE ct_usuarios SET estatus=0 WHERE pk_usuario='$fk_usuario'")) {
                $codigo = 201;
                $descripcion = "Error al actualizar vendedor";
            }
        }
        #endregion
    }
}



//ENCABEZADO
if ($codigo == 200) {
    $mysqli->next_result();
    if (!$mysqli->query("INSERT INTO tr_almacen (fk_usuario, fecha, fk_ruta, fk_sucursal, estatus) VALUES ('$pk_usuario', '$fecha', $fk_ruta, $fk_sucursal, 1)")) {
        $codigo = 201;
        $descripcion = "Error al guardar el encabezado";
    }

    $pk_almacen = $mysqli->insert_id;
}



//DETALLE
if ($codigo == 200) {

    $mysqli->next_result();
    if (!$rdetalle = $mysqli->query("SELECT * FROM ct_plantillas_detalle WHERE fk_plantilla = $pk_plantilla")) {
        $codigo = 201;
        $descripcion = "Error a obtener el detalle";
    }

    while ($detalle = $rdetalle->fetch_assoc()) {

        //PRODUCTO
        $mysqli->next_result();
        if (!$get_producto = $mysqli->query("SELECT * FROM ct_productos WHERE pk_producto = $detalle[fk_insumo]")) {
            $codigo = 201;
            $descripcion = "Error insertar el insertar el producto en la bitacora";
        }

        $rowp = $get_producto->fetch_assoc();
        $unitario = $rowp['precio'];
        $total_producto = $unitario * $detalle['cantidad'];


        //DETALLE
        $mysqli->next_result();
        if (!$mysqli->query("INSERT INTO tr_almacen_detalle (fk_insumo, cantidad_inicial, cantidad_final, fk_almacen, fk_unidad) VALUES ($detalle[fk_insumo], $detalle[cantidad], $detalle[cantidad], $pk_almacen, 'Pieza')")) {
            $codigo = 201;
            $descripcion = "Error al insertar el detalle";
        }


        //EXISTENCIAS
        if ($codigo == 200) {
            $mysqli->next_result();
            if (!$mysqli->query("CALL sp_update_existencias_salida($fk_sucursal, $fk_sucursal_almacen, $detalle[fk_insumo], $detalle[cantidad])")) {
                $codigo = 201;
                $descripcion = "Error al registrar en la bitácora";
            }
        }


        //MOVIMIENTO
        if ($codigo == 200) {
            $mysqli->next_result();
            if (!$mysqli->query("INSERT INTO tr_movimientos (fk_producto, fk_movimiento, fk_movimiento_detalle, fk_usuario, fk_sucursal, fk_almacen, tipo_venta, fecha, serie, cantidad, total) VALUES($detalle[fk_insumo], 2, $pk_almacen, '$pk_usuario', $fk_sucursal, $fk_sucursal_almacen, 1, CURDATE(), '', $detalle[cantidad], $total_producto)")) {
                $codigo = 201;
                $descripcion = "Error al registrar en la bitácora";
            }
        }
    }
}



//ESTATUS DEL USUARIO
if ($codigo == 200) {
    $mysqli->next_result();
    if (!$mysqli->query("UPDATE ct_usuarios SET estatus = 1 WHERE pk_usuario = '$pk_usuario'")) {
        $codigo = 201;
        $descripcion = "Error al actualizar el estatus del vendedor";
    }
}




$mysqli->close();
$general = array("codigo" => (int)$codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
