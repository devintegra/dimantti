<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
date_default_timezone_set('America/Mexico_City');
mysqli_set_charset($mysqli, 'utf8');
$codigo = 200;
$descripcion = "";
$acuse_cancelacion = "";
$decoded_response = array();


$TOKEN_FACTUROPORTI_DEV = "eyJhbGciOiJodHRwOi8vd3d3LnczLm9yZy8yMDAxLzA0L3htbGRzaWctbW9yZSNobWFjLXNoYTI1NiIsInR5cCI6IkpXVCJ9.eyJodHRwOi8vc2NoZW1hcy54bWxzb2FwLm9yZy93cy8yMDA1LzA1L2lkZW50aXR5L2NsYWltcy9uYW1lIjoialYrdVVUYmtWNmUxRmNZb2cvNWtGQT09IiwibmJmIjoxNjU4MzMxNjU2LCJleHAiOjE2NjA5MjM2NTYsImlzcyI6IlNjYWZhbmRyYVNlcnZpY2lvcyIsImF1ZCI6IlNjYWZhbmRyYSBTZXJ2aWNpb3MiLCJJZEVtcHJlc2EiOiJqVit1VVRia1Y2ZTFGY1lvZy81a0ZBPT0iLCJJZFVzdWFyaW8iOiJidXlaYzFMWUl5VURaSGhGR3NqaGdRPT0ifQ.5vDG7CZmLCU2wC0W6ri1mazNjfEgxVd7udxiFkhgqFw";
$TOKEN_FACTUROPORTI_PROD = "eyJhbGciOiJodHRwOi8vd3d3LnczLm9yZy8yMDAxLzA0L3htbGRzaWctbW9yZSNobWFjLXNoYTI1NiIsInR5cCI6IkpXVCJ9.eyJodHRwOi8vc2NoZW1hcy54bWxzb2FwLm9yZy93cy8yMDA1LzA1L2lkZW50aXR5L2NsYWltcy9uYW1lIjoibUZrWWU5VThTN0tmaFZYWmdlc3UrZz09IiwibmJmIjoxNzE3NzgxNTIzLCJleHAiOjE3MjAzNzM1MjMsImlzcyI6IlNjYWZhbmRyYVNlcnZpY2lvcyIsImF1ZCI6IlNjYWZhbmRyYSBTZXJ2aWNpb3MiLCJJZEVtcHJlc2EiOiJtRmtZZTlVOFM3S2ZoVlhaZ2VzdStnPT0iLCJJZFVzdWFyaW8iOiJtWGhNQlAvN2JzS09Fa2ZXa0dUYUh3PT0ifQ.EosKJ4_xOwsFATrh8zzVoXj_fFv1F6u9l8LQSk9hf2I";
$API_FACTUROPORTI_DEV = "https://testapi.facturoporti.com.mx";
$API_FACTUROPORTI_PROD = "https://api.facturoporti.com.mx";


if (isset($_POST['uuid']) && is_string($_POST['uuid'])) {
    $uuid = $_POST['uuid'];
}

if (isset($_POST['fk_motivo']) && is_string($_POST['fk_motivo'])) {
    $fk_motivo = $_POST['fk_motivo'];
}

if (isset($_POST['folio_fiscal']) && is_string($_POST['folio_fiscal'])) {
    $folio_fiscal = $_POST['folio_fiscal'];
}



//DATOS DE LA EMPRESA
#region
$qempresa = "SELECT * FROM ct_empresas WHERE pk_empresa = 1";

if (!$resultado = $mysqli->query($qempresa)) {
    echo "<br>Lo sentimos, esta aplicación está experimentando problemas. Error al obtener los datos de la empresa. " . $mysqli->error;;
    exit;
}

$rowexistead = $resultado->fetch_assoc();
$empresa_rfc = $rowexistead["rfc"];
$empresa_cer = $rowexistead["cer"];
$empresa_key = $rowexistead["fkey"];
$empresa_pass = base64_decode($rowexistead["pass"]);
#endregion




//DATOS DEL CLIENTE
#region
$qcliente = "SELECT * FROM ct_clientes WHERE pk_cliente = (SELECT fk_cliente FROM tr_facturas WHERE pk_factura = '$uuid');";

if (!$rcliente = $mysqli->query($qcliente)) {
    echo "<br>Lo sentimos, esta aplicación está experimentando problemas. Error al obtener los datos del cliente";
    exit;
}

$rowcliente = $rcliente->fetch_assoc();
$cliente_rfc = $rowcliente["rfc"];
#endregion




//DATOS DE LA FACTURA
#region
$qfactura = "SELECT * FROM tr_facturas WHERE pk_factura = '$uuid';";

if (!$rfactura = $mysqli->query($qfactura)) {
    echo "<br>Lo sentimos, esta aplicación está experimentando problemas. Error al obtener los datos de la factura";
    exit;
}

$rowfactura = $rfactura->fetch_assoc();
$factura_total = $rowfactura["total"];
#endregion





//SERVICIO DE CANCELAR DE FACTURO POR TI
#region
if ($codigo == 200) {

    $url = $API_FACTUROPORTI_DEV . '/servicios/cancelar/csd';

    $data = array(
        'RfcEmisor' => "$empresa_rfc",
        'RfcReceptor' => "$cliente_rfc",
        'Uuid' => "$uuid",
        'Motivo' => "$fk_motivo",
        'FolioFiscalSustitucion' => "$folio_fiscal",
        'Total' => $factura_total,
        'Certificado' => "$empresa_cer",
        'LlavePrivada' => "$empresa_key",
        'Password' => "$empresa_pass"
    );

    $curl = curl_init($url);

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
        "accept: application/json",
        "content-type: application/*+json",
        'Authorization: Bearer ' . $TOKEN_FACTUROPORTI_DEV,
    ]);

    $response = curl_exec($curl);

    // Verificar si hay errores
    if ($response === false) {
        $codigo = 201;
        $descripcion = curl_error($curl);
    } else {

        $decoded_response = json_decode($response, true);

        if (!$decoded_response) {
            $codigo = 201;
            $descripcion = "Hubo un error al cancelar la factura desde el servicio del proveedor";
        } else {

            if ($decoded_response['codigo'] != '000') {
                $codigo = 201;
                $descripcion = $decoded_response['mensaje'];
            }
        }

        file_put_contents('cancelaciones.log', date("Y-m-d H:i:s") . " - UUID: $uuid, Acuse: $decoded_response[acuse]\n", FILE_APPEND);
    }

    curl_close($curl);

    if ($codigo == 200) {
        $acuse_cancelacion = str_replace("'", '"', $decoded_response['acuse']);
        //$acuse_cancelacion = base64_encode($decoded_response['acuse']);
    }
}
#endregion





//CAMBIOS INTERNOS
#region
if ($codigo == 200) {

    if (!$mysqli->query("UPDATE tr_facturas SET estado = 0 WHERE pk_factura = '$uuid'")) {
        $codigo = 201;
        $descripcion = "Error al actualizar el estado de la factura en la base de datos. " . $mysqli->error;
    }

    if (!$mysqli->query("UPDATE tr_facturas SET acuse_cancelacion = '$acuse_cancelacion', estado = 0 WHERE pk_factura = '$uuid'")) {
        $codigo = 201;
        $descripcion = "Error al eliminar la factura de la base de datos. " . $mysqli->error;
    }

    if ($codigo == 200) {
        if (!$mysqli->query("UPDATE tr_ventas SET fk_factura = '' WHERE fk_factura = '$uuid'")) {
            $codigo = 201;
            $descripcion = "Error al actualizar la factura desde la venta. " . $mysqli->error;
        }
    }
}
#endregion





$mysqli->close();
$detalle = array("respuesta" => $decoded_response, "data" => $data, "ac" => $acuse_cancelacion);
$myObj = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => $detalle);
$myJSON = json_encode($myObj);
header('Content-type: application/json');
echo $myJSON;
