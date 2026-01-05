<?php
//CONFIG
#region
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
$codigo = 200;
$descripcion = "";
$nentrada = 0;
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
$fk_almacen = $decoded['fk_almacen'];
$fk_usuario = $decoded['fk_usuario'];
$fk_almacen_destino = $decoded['fk_almacen_destino'];
$observaciones = $decoded['observaciones'];
$total = (float)$decoded['total'];
$hora_actual = date("h:i:s");




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



//SUCURSAL DESTINO
#region
$qsucursald = "SELECT * FROM rt_sucursales_almacenes where pk_sucursal_almacen = $fk_almacen_destino";

if (!$rsucursald = $mysqli->query($qsucursald)) {
    echo "<br>Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}
$rowsucursald = $rsucursald->fetch_assoc();
$fk_sucursal_destino = $rowsucursald["fk_sucursal"];
#endregion



//ENCABEZADO DE LA TRANSFERENCIA
#region
$mysqli->next_result();
if (!$mysqli->query("INSERT INTO tr_transferencias (fk_sucursal, fk_almacen, fk_usuario, fk_sucursal_destino, fk_almacen_destino, observaciones, fecha, hora, total) values ($fk_sucursal, $fk_almacen, '$fk_usuario', $fk_sucursal_destino, $fk_almacen_destino, '$observaciones', CURDATE(), '$hora_actual', $total)")) {
    $codigo = 201;
    $descripcion = "Hubo un problemas, porfavor vuelva a intentarlo!";
}
#endregion



//DETALLE DE LA TRANSFERENCIA
if ($codigo == 200) {

    $nentrada = $mysqli->insert_id;

    foreach ($decoded['productos'] as $key => $value) {

        //DATOS DEL PRODUCTO
        #region
        $qdatos = "SELECT * FROM ct_productos where codigobarras = '$value[codigobarras]' and estado = 1";

        $mysqli->next_result();
        if (!$rdatos = $mysqli->query($qdatos)) {
            echo "<br>Lo sentimos, esta aplicación está experimentando problemas.";
            exit;
        }

        $datos = $rdatos->fetch_assoc();
        $pk_producto = $datos["pk_producto"];
        $nombre_producto = $datos["nombre"];
        #endregion


        /*
        Estatus
        1.Enviado
        2.Recibido
        3.Devuelto
        */


        //DETALLE
        #region
        $mysqli->next_result();
        if (!$mysqli->query("INSERT INTO tr_transferencias_detalle(fk_transferencia, fk_producto, serie, cantidad, faltante, unitario, total, estatus) values ($nentrada, $pk_producto, '$value[serie]', $value[cantidad], $value[cantidad], $value[unitario], $value[total], 1)")) {
            $codigo = 201;
            $descripcion = "Hubo un problemas, porfavor vuelva a intentarlo!";
        }
        #endregion



        //EXISTENCIAS
        #region
        if ($codigo == 200) {
            //RESTAR A LA SUCURSAL ORIGEN
            $mysqli->next_result();
            if (!$mysqli->query("CALL sp_update_existencias_salida($fk_sucursal, $fk_almacen, $pk_producto, $value[cantidad])")) {
                $codigo = 201;
                $descripcion = "Error al registrar en la bitácora";
            }
        }
        #endregion




        //BITÁCORA DE PRODUCTOS
        #region
        if ($codigo == 200) {
            $mysqli->next_result();
            if (!$mysqli->query("INSERT INTO tr_movimientos (fk_producto, serie, fk_movimiento, fk_movimiento_detalle, fk_usuario, fk_sucursal, fk_almacen, fecha, cantidad, total) values ($pk_producto, '$value[serie]', 2, $nentrada, '$fk_usuario', $fk_sucursal, $fk_almacen, CURDATE(), $value[cantidad], $value[total])")) {
                $codigo = 201;
                $descripcion = "Hubo un problemas, porfavor vuelva a intentarlo!";
            }
        }
        #endregion

    }
}






$mysqli->close();
$detalle = array("nentrada" => $nentrada);
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => $detalle);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
