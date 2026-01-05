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
$pk_contrato = $decoded['pk_contrato'];
$total = (float)$decoded['total'];
$observaciones = $decoded['observaciones'];
$ahora = date("Y-m-d H:i:s");
$hora_actual = date("H") . ":" . date("i") . ":" . date("s");
//$fecha=date("Y").date("m").date("d");
$fecha = date("Y");



//DATOS
#region
$qcontratos = "SELECT * FROM tr_compras WHERE pk_compra = $pk_contrato";

if (!$rcontratos = $mysqli->query($qcontratos)) {
    $codigo = 201;
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

$contratos = $rcontratos->fetch_assoc();
$fk_sucursal = $contratos["fk_sucursal"];
$fk_almacen = $contratos["fk_almacen"];
#endregion



//ENTRADA
#region
$mysqli->next_result();
if (!$mysqli->query("INSERT INTO tr_entradas (fk_compra ,fk_sucursal, fk_almacen, fk_usuario, fecha, hora, total, observaciones) VALUES ($pk_contrato, $fk_sucursal, $fk_almacen, '$fk_usuario', CURDATE(), '$hora_actual', $total, '$observaciones')")) {
    $codigo = 201;
    $descripcion = "Error al guardar la entrada. ";
}

$pk_entrada = $mysqli->insert_id;
#endregion



//PRODUCTOS
if ($codigo == 200) {

    foreach ($decoded['productos'] as $key => $value) {

        //DATOS DEL PRODUCTO
        #region
        $qdatos = "SELECT * FROM ct_productos WHERE pk_producto = '$value[fk_producto]' AND estado = 1";

        $mysqli->next_result();
        if (!$rdatos = $mysqli->query($qdatos)) {
            echo "<br>Lo sentimos, esta aplicación está experimentando problemas.";
            exit;
        }

        $datos = $rdatos->fetch_assoc();
        $pk_producto = $datos["pk_producto"];
        $nombre_producto = $datos["nombre"];
        $costo_producto = $datos["costo"];
        #endregion



        //DETALLE
        if ($codigo == 200) {
            $mysqli->next_result();
            if (!$mysqli->query("UPDATE tr_compras_detalle SET faltante=faltante-$value[cantidad] WHERE fk_producto = $pk_producto AND fk_compra=$pk_contrato")) {
                $codigo = 201;
                $descripcion = "Error al actualizar el faltante. ";
            }
        }

        if ($codigo == 200) {
            $mysqli->next_result();
            if (!$mysqli->query("INSERT INTO tr_entradas_detalle (fk_entrada, fk_producto, fk_producto_nombre, cantidad, serie, unitario, total, fk_compra) VALUES ($pk_entrada, $pk_producto, '$nombre_producto', $value[cantidad], '$value[serie]', $value[unitario], $value[total], $pk_contrato)")) {
                $codigo = 201;
                $descripcion = "Error al guardar el detalle";
            }
        }



        //EXISTENCIAS
        if ($codigo == 200) {
            $mysqli->next_result();
            if (!$rexistencia = $mysqli->query("CALL sp_update_existencias_entrada($fk_sucursal, $fk_almacen, $pk_producto, $value[cantidad], $pk_contrato)")) {
                $codigo = 201;
                $descripcion = "Error al actualizar las existencias";
            }
        }



        //BITÁCORA DE MOVIMIENTOS
        if ($codigo == 200) {
            $mysqli->next_result();
            if (!$mysqli->query("INSERT INTO tr_movimientos (fk_producto, serie, fk_movimiento, fk_movimiento_detalle, fk_usuario, fk_sucursal, fk_almacen, fecha, cantidad, total) VALUES ($pk_producto, '$value[serie]', 1, $pk_entrada, '$fk_usuario', $fk_sucursal, $fk_almacen, CURDATE(), $value[cantidad], $value[total])")) {
                $codigo = 201;
                $descripcion = "Error al guardar el movimiento. ";
            }
        }
    }
}




//FALTANTES
if ($codigo == 200) {
    $qfaltantes = "SELECT SUM(faltante) as total FROM tr_compras_detalle WHERE fk_compra = $pk_contrato";

    if (!$rfaltantes = $mysqli->query($qfaltantes)) {
        $codigo = 201;
    }

    $faltantes = $rfaltantes->fetch_assoc();

    if ($faltantes["total"] > 0) {
        if (!$mysqli->query("UPDATE tr_compras SET estatus=2 WHERE pk_compra = $pk_contrato")) {
            $codigo = 201;
            $descripcion = "Error al actualizar el estatus. ";
        }
    } else {
        if (!$mysqli->query("UPDATE tr_compras SET estatus=3 WHERE pk_compra = $pk_contrato")) {
            $codigo = 201;
            $descripcion = "Error al actualizar el estado. ";
        }
    }
}




$mysqli->close();
$detalle = array("pk_entrada" => $pk_entrada);
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => $detalle);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
