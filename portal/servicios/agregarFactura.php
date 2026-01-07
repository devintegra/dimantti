<?php
//CONFIG
#region
header("Access-Control-Allow-Origin: *");
include("conexioni.php");

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


@session_start();

$codigo = 200;
$descripcion = "";
$ahora = date("Y-m-d H:i:s");


if (isset($_SESSION['usuario'])) {

    $pk_venta = $decoded['pk_venta'];
    $fk_cliente = $decoded['fk_cliente'];
    $uid = $decoded['uid'];
    $metodo_pago = $decoded['metodo_pago'];
    $forma_pago = $decoded['forma_pago'];
    $pdf = $decoded['pdf'];
    $xml = $decoded['xml'];


    //INSERTAR EN TR_FACTURAS
    if ($codigo == 200) {

        $factura_desc = "";
        $factura_cantidad = 0;
        $factura_costo = 0;
        $factura_iva = 0;
        $factura_total = 0;

        foreach ($decoded['productos'] as $key => $value) {
            $factura_desc .= $value['descripcion'] . ". ";
            $factura_cantidad += $value['cantidad'];
            $factura_costo += $value['costo'];
            $factura_iva += $value['iva'];
            $factura_total += $value['total'];
        }

        if (!$mysqli->query("INSERT tr_facturas (pk_factura, fk_cliente, descripcion, forma_pago, metodo_pago, factura_pdf, factura_xml, fecha, cantidad, costo, iva, total) VALUES ('$uid', $fk_cliente, '$factura_desc', '$forma_pago', '$metodo_pago', '$pdf', '$xml', '$ahora', $factura_cantidad, $factura_costo, $factura_iva, $factura_total)")) {
            $codigo = 201;
            $descripcion = "Hubo un error al generar el registro de la factura. " . $mysqli->error;
        }

        //$fk_factura = $mysqli->insert_id;

        if ($codigo == 200) {
            foreach ($pk_venta as $pk) {

                if (!$mysqli->query("UPDATE tr_ventas SET fk_factura = '$uid' WHERE pk_venta = $pk AND estado = 1")) {
                    $codigo = 201;
                    $descripcion = "Hubo un error al actualizar el encabezado de la venta. " . $mysqli->error;
                }
            }
        }

        //Guardar el PDF
        if ($codigo == 200) {
            $factura_pdf = base64_decode($pdf);
            $pdf_name = $uid . ".pdf";

            if (!file_put_contents("facturas/" . $pdf_name, $factura_pdf)) {
                $codigo = 201;
                $descripcion = "Hubo un error al guardar el PDF de la factura.";
            }
        }

        //Guardar el XML
        if ($codigo == 200) {
            $xml_name = $uid . '.xml';

            if (!file_put_contents("facturas_xml/" . $xml_name, $xml)) {
                $codigo = 201;
                $descripcion = "Hubo un error al guardar el XML de la factura.";
            }
        }
    }
} else {

    $codigo = 201;
    $descripcion = "No has iniciado sesión";
}



$myObj = array(
    "codigo" => $codigo,
    "descripcion" => $descripcion,
);



$myJSON = json_encode($myObj);

header('Content-type: application/json');

echo $myJSON;
