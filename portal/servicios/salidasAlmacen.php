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


$fk_usuario = $decoded['fk_usuario'];
$fk_almacen = $decoded['fk_almacen'];
$motivo_salida = (int)$decoded["motivo_salida"];
$observaciones = $decoded["observaciones"];
$total = (float)$decoded['total'];

$ahora = date("Y-m-d H:i:s");
$hora_actual = date("H") . ":" . date("i") . ":" . date("s");
$fecha = date("Y");




//SUCURSAL
#region
$qsucursal = "SELECT * FROM rt_sucursales_almacenes where pk_sucursal_almacen = $fk_almacen";

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
if (!$mysqli->query("INSERT INTO tr_salidas (fk_sucursal, fk_almacen, fk_usuario, fecha, hora, total_monetario, fk_motivo, observaciones) values ($fk_sucursal, $fk_almacen, '$fk_usuario', CURDATE(), '$hora_actual', $total, $motivo_salida, '$observaciones')")) {
    $codigo = 201;
    $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
}

$pk_salida = $mysqli->insert_id;
#endregion



//PRODUCTOS
if ($codigo == 200) {

    foreach ($decoded['productos'] as $key => $value) {

        //DATOS DEL PRODUCTO
        #region
        $qdatos = "SELECT * FROM ct_productos where pk_producto = $value[fk_producto] and estado = 1";

        $mysqli->next_result();
        if (!$rdatos = $mysqli->query($qdatos)) {
            echo "<br>Lo sentimos, esta aplicación está experimentando problemas.";
            exit;
        }

        $datos = $rdatos->fetch_assoc();
        $pk_producto = $datos["pk_producto"];
        $nombre_producto = $datos["nombre"];
        #endregion



        //SUCURSAL
        #region
        $qsucursal = "SELECT * FROM rt_sucursales_almacenes where pk_sucursal_almacen = $value[fk_sucursal_almacen]";

        $mysqli->next_result();
        if (!$rsucursal = $mysqli->query($qsucursal)) {
            echo "<br>Lo sentimos, esta aplicación está experimentando problemas.";
            exit;
        }
        $rowsucursal = $rsucursal->fetch_assoc();
        $fk_sucursal = $rowsucursal["fk_sucursal"];
        #endregion



        //DETALLE
        $mysqli->next_result();
        if (!$mysqli->query("INSERT INTO tr_salidas_detalle (fk_salida, fk_producto, serie, fk_sucursal, fk_almacen, cantidad, unitario, total) values ($pk_salida, $value[fk_producto], '$value[serie]', $fk_sucursal, $value[fk_sucursal_almacen], $value[cantidad], $value[unitario], $value[cantidad] * $value[unitario])")) {
            $codigo = 201;
            $descripcion = "Hubo un problemas, porfavor vuelva a intentarlo!";
        }



        //EXISTENCIAS
        if ($codigo == 200) {
            $mysqli->next_result();
            if (!$mysqli->query("CALL sp_update_existencias_salida($fk_sucursal, $value[fk_sucursal_almacen], $value[fk_producto], $value[cantidad])")) {
                $codigo = 201;
                $descripcion = "Error al registrar en la bitácora";
            }
        }



        //BITACORA
        if ($codigo == 200) {
            $mysqli->next_result();
            if (!$mysqli->query("INSERT INTO tr_movimientos (fk_producto, serie, fk_movimiento, fk_movimiento_detalle, fk_usuario, fk_sucursal, fk_almacen, fecha, cantidad, total) values($value[fk_producto], '$value[serie]', 4, $pk_salida, '$fk_usuario', $fk_sucursal, $value[fk_sucursal_almacen], CURDATE(), $value[cantidad], $value[cantidad] * $value[unitario])")) {
                $codigo = 201;
                $descripcion = "Error al registrar la bitácora";
            }
        }
    }
}




$mysqli->close();
$detalle = array("pk_salida" => $pk_salida);
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => $detalle);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
