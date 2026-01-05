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



//DATOS
$ahora = date("Y-m-d H:i:s");
$hora_actual = date("H") . ":" . date("i") . ":" . date("s");
$pk_compra = 0;
$subtotal = 0;


if ($codigo == 200) {

    foreach ($decoded['productos'] as $key => $value) {

        //DATOS DE LA COMPRA
        #region
        $qdatos = "SELECT tr_compras.*,
            tr_compras_detalle.fk_producto
            FROM tr_compras, tr_compras_detalle
            WHERE tr_compras_detalle.pk_compra_detalle = $value[fk_compra_detalle]
            AND tr_compras.pk_compra = tr_compras_detalle.fk_compra;";

        if (!$rdatos = $mysqli->query($qdatos)) {
            echo "<br>Lo sentimos, esta aplicación está experimentando problemas.";
            exit;
        }

        $datos = $rdatos->fetch_assoc();
        $pk_compra = $datos["pk_compra"];
        $total = $datos["total"];
        $saldo = $datos["saldo"];
        $tipo_pago = $datos["tipo_pago"];
        $pk_producto = $datos["fk_producto"];
        #endregion


        if (!$mysqli->query("UPDATE tr_compras_detalle SET unitario = $value[unitario], total = $value[total] WHERE pk_compra_detalle = $value[fk_compra_detalle] AND estado = 1")) {
            $codigo = 201;
            $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
        }

        $subtotal += $value['total'];


        if ($codigo == 200 && $value['actualizar_precio'] == 1) {

            //DATOS DEL PRODUCTO
            #region
            $qdatos = "SELECT * FROM ct_productos WHERE pk_producto = $pk_producto AND estado = 1";

            if (!$rdatos = $mysqli->query($qdatos)) {
                echo "<br>Lo sentimos, esta aplicación está experimentando problemas.2";
                exit;
            }

            $datos = $rdatos->fetch_assoc();
            $nombre_producto = $datos["nombre"];
            $utilidad1 = $datos["utilidad"];
            $utilidad2 = $datos["utilidad2"];
            $utilidad3 = $datos["utilidad3"];
            $utilidad4 = $datos["utilidad4"];
            #endregion

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
                    $codigo = 201;
                    $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
                }
            }
            #endregion


        }
    }
}



//ACTUALIZAR ENCABEZADO DE LA COMPRA
if ($codigo == 200) {

    if (!$mysqli->query("UPDATE tr_compras SET total = $subtotal WHERE pk_compra = $pk_compra AND estado = 1")) {
        $codigo = 201;
        $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
    }

    $diferencia = $total - $subtotal; //$60,000 -> $55,000    ||    $60,000 > $65,000

    //Saldo
    if ($tipo_pago > 1) {
        if (!$mysqli->query("UPDATE tr_compras SET saldo = saldo - $diferencia WHERE pk_compra = $pk_compra AND estado = 1")) {
            $codigo = 201;
            $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
        }
    }
}



$mysqli->close();
$detalle = array("pk_compra" => $pk_compra);
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
