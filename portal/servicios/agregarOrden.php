<?php
//CONFIG
#region
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
require 'correo/PHPMailerAutoload.php';
date_default_timezone_SET('America/Mexico_City');
$codigo = 200;
$descripcion = "";
$nentrada = 0;
$saldo = 0.00;
mysqli_SET_charSET($mysqli, 'utf8');

//Make sure that it is a POST request.
if (strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0) {
    throw new Exception('Request method must be POST!');
}

//Make sure that the content type of the POST request has been SET to application/json
$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
if (strcasecmp($contentType, 'application/json; charSET=utf-8') != 0) {
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
$fk_sucursal = $decoded['fk_sucursal'];
$fk_usuario = $decoded['fk_usuario'];
$fk_cliente = $decoded['fk_cliente'];
$cliente_nombre = $decoded['cliente_nombre'];
$cliente_telefono = $decoded['cliente_telefono'];
$cliente_correo = $decoded['cliente_correo'];

$asignacion = $decoded['asignacion'];
$valor_estimado = (float)$decoded['valor_estimado'];
$tipo_pago = (int)$decoded['fk_pago'];
$monto_pago = (float)$decoded['monto_pago'];

$ns = $decoded['ns'];
$fk_categoria = $decoded['fk_categoria'];
$marca = $decoded['marca'];
$modelo = $decoded['modelo'];
$observaciones = $decoded['observaciones'];

$currentDate = date('Y-m-d');
$ahora = date("Y-m-d H:i:s");
$hora_actual = date("H") . ":" . date("i") . ":" . date("s");
#endregion




//CLIENTE
#region
$mysqli->next_result();
if (!$rsp_get_cliente = $mysqli->query("CALL sp_get_cliente_by_telefono('$cliente_telefono')")) {
    $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!0";
    $codigo = 201;
}

if ($rsp_get_cliente->num_rows > 0) {

    $rowc = $rsp_get_cliente->fetch_assoc();

    $mysqli->next_result();
    if (!$mysqli->query("CALL sp_update_cliente($rowc[pk_cliente], '$cliente_nombre', '$rowc[telefono]', '$cliente_correo', $rowc[dias_credito], $rowc[limite_credito], $rowc[credito], $rowc[abonos], $rowc[fk_categoria_cliente], '$rowc[cp]', '$rowc[rfc]', $rowc[fk_regimen_fiscal], '$rowc[direccion]', '$rowc[latitud]', '$rowc[longitud]')")) {
        $codigo = 201;
        $descripcion = "Error al editar los datos del cliente";
    }
} else {

    $mysqli->next_result();
    if (!$rsp_set_cliente = $mysqli->query("CALL sp_set_cliente('$cliente_nombre', '$cliente_telefono', $fk_sucursal, '$cliente_correo', 0, 0, 0, 2, 1, '', '', 0, 1, '', '', '')")) {
        $codigo = 201;
        $descripcion = "Error al guardar el registro del cliente";
    }

    $rowc = $rsp_set_cliente->fetch_assoc();
    $fk_cliente = $rowc["pk_cliente"];
}
#endregion




//ENCABEZADO DE LA ORDEN
#region
$mysqli->next_result();
if (!$rsp_set_orden = $mysqli->query("CALL sp_set_orden($fk_sucursal, '$fk_usuario', '$ahora', $fk_cliente, $monto_pago, '$asignacion', '$observaciones')")) {
    $codigo = 201;
    $descripcion = "Error al guardar el encabezado";
}

$rowo = $rsp_set_orden->fetch_assoc();
$nentrada = $rowo["pk_orden"];
#endregion



//REGISTROS
if ($codigo == 200) {
    $mysqli->next_result();
    if (!$mysqli->query("CALL sp_set_orden_registro($nentrada, 1, 'Asignación a $asignacion', '$fk_usuario', 0, '', 0, 0, 0, 0, 0, 0, 0)")) {
        $codigo = 201;
        $descripcion = "Error al guardar registro en la bitácora";
    }


    $mysqli->next_result();
    if (!$mysqli->query("CALL sp_set_orden_registro($nentrada, 3, 'Se registró un accesorio nuevo', '$fk_usuario', 0, '', 0, 0, 0, 0, 0, 0, 0)")) {
        $codigo = 201;
        $descripcion = "Error al guardar registro en la bitácora";
    }
}



//FOLIO
if ($codigo == 200) {

    $mysqli->next_result();
    if (!$rsp_get_sucursal = $mysqli->query("CALL sp_get_sucursal($fk_sucursal)")) {
        $codigo = 201;
        $descripcion = "Hubo un problema, porfavor vuelva a intentarlo!";
    }

    $rows = $rsp_get_sucursal->fetch_assoc();
    $sucursal_inicial = $rows["iniciales"];

    $fecha_folio =  str_replace("-", "", $currentDate);
    $folio = $sucursal_inicial . $fecha_folio . $nentrada;

    $mysqli->next_result();
    if (!$mysqli->query("UPDATE tr_ordenes SET folio='$folio' WHERE pk_orden = $nentrada")) {
        $codigo = 201;
        $descripcion = "Hubo un problemas, porfavor vuelva a intentarlo!";
    }
}



//ABONO
if ($monto_pago > 0 && $codigo == 200 && $tipo_pago > 0) {

    $mysqli->next_result();
    if (!$mysqli->query("CALL sp_set_abono(1, 2, $nentrada, $monto_pago, '$fk_usuario', $fk_sucursal, $tipo_pago, 0)")) {
        $codigo = 201;
        $descripcion = "Error al registrar el abono";
    }


    $mysqli->next_result();
    if (!$mysqli->query("CALL sp_set_orden_registro($nentrada, 1, 'Abono: $$monto_pago', '$fk_usuario', 1, '', 0, 0, 0, 0, 0, 0, 0)")) {
        $codigo = 201;
        $descripcion = "Error al guardar el registro en la bitácora";
    }
}



//DETALLE
if ($codigo == 200) {

    //CATEGORIA
    $mysqli->next_result();
    if (!$rcategoria = $mysqli->query("SELECT * FROM ct_categorias WHERE pk_categoria = $fk_categoria")) {
        echo "<br>Lo sentimos, esta aplicación está experimentando problemas.1";
        exit;
    }

    $rowt = $rcategoria->fetch_assoc();
    $categoria = $rowt["nombre"];


    //DETALLE
    $mysqli->next_result();
    if (!$mysqli->query("CALL sp_set_orden_detalle($nentrada, '$ns', $fk_categoria, '$marca', '$modelo', '$categoria-$marca-$modelo', $valor_estimado, 0, '$observaciones')")) {
        $codigo = 201;
        $descripcion = "Error al guardar los datos del artículo";
    }


    //BITACORA
    $mysqli->next_result();
    if (!$mysqli->query("CALL sp_set_orden_registro($nentrada, 1, 'Datos del accesorio: $categoria, Problema: $observaciones', '$fk_usuario', 0, '', 0, 0, 0, 0, 0, 0, 0)")) {
        $codigo = 201;
        $descripcion = "Error al guardar el registro en la bitácora";
    }
}



//CORREO
if ($cliente_correo != "" && $cliente_correo != " ") {

    if (filter_var($cliente_correo, FILTER_VALIDATE_EMAIL)) {

        $host = "mail.integradesarrollo.com";
        $puerto = "465";
        $correoe = "notificaciones@integradesarrollo.com";
        $pass = "Notificaciones2022!";
        $leyenda = "Dimantti - Todo en computación";
        $tema = "ORDEN #" . $folio;
        $para = "aldogd24@gmail.com";
        $titulom = "Dimantti - ORDEN DE SERVICIO #$folio";


        $link = "<a href='https://dimantti.integracontrol.online/portal/ordenPDF.php?id=$nentrada&ph=$cliente_telefono'>#$folio</a>";
        $linka = "<a href='https://dimantti.integracontrol.online/seguimiento.php?id=$nentrada&ph=$cliente_telefono'>#$folio</a>";


        $txtInfo = "<html
                style='width:100%;font-family: helvetica, arial, sans-serif;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;padding:0;Margin:0;'>

            <head>
                <meta charSET='UTF-8'>
                <meta http-equiv='Content-Type' content='text/html' charSET='utf-8' />
                <meta content='width=device-width, initial-scale=1' name='viewport'>
                <meta name='x-apple-disable-message-reformatting'>
                <meta http-equiv='X-UA-Compatible' content='IE=edge'>
                <meta content='telephone=no' name='format-detection'>
                <title>Orden #$folio</title>
                <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,400i,700,700i' rel='stylesheet'>
                <link href='https://fonts.googleapis.com/css?family=Roboto:400,400i,700,700i' rel='stylesheet'>
                <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/fontawesome.min.css' rel='stylesheet'
                type='text/css' />
                <style type='text/css'>
                    @media only screen and (max-width:600px) {

                        p,
                        ul li,
                        ol li,
                        a {
                        font-size: 16px !important;
                        line-height: 150% !important
                        }

                        h1 {
                        font-size: 30px !important;
                        text-align: center;
                        line-height: 120% !important
                        }

                        h2 {
                        font-size: 26px !important;
                        text-align: center;
                        line-height: 120% !important
                        }

                        h3 {
                        font-size: 20px !important;
                        text-align: center;
                        line-height: 120% !important
                        }

                        h1 a {
                        font-size: 30px !important
                        }

                        h2 a {
                        font-size: 26px !important
                        }

                        h3 a {
                        font-size: 20px !important
                        }

                        .es-menu td a {
                        font-size: 16px !important
                        }

                        .es-header-body p,
                        .es-header-body ul li,
                        .es-header-body ol li,
                        .es-header-body a {
                        font-size: 16px !important
                        }

                        .es-footer-body p,
                        .es-footer-body ul li,
                        .es-footer-body ol li,
                        .es-footer-body a {
                        font-size: 16px !important
                        }

                        .es-infoblock p,
                        .es-infoblock ul li,
                        .es-infoblock ol li,
                        .es-infoblock a {
                        font-size: 12px !important
                        }

                        *[class='gmail-fix'] {
                        display: none !important
                        }

                        .es-m-txt-c,
                        .es-m-txt-c h1,
                        .es-m-txt-c h2,
                        .es-m-txt-c h3 {
                        text-align: center !important
                        }

                        .es-m-txt-r,
                        .es-m-txt-r h1,
                        .es-m-txt-r h2,
                        .es-m-txt-r h3 {
                        text-align: right !important
                        }

                        .es-m-txt-l,
                        .es-m-txt-l h1,
                        .es-m-txt-l h2,
                        .es-m-txt-l h3 {
                        text-align: left !important
                        }

                        .es-m-txt-r img,
                        .es-m-txt-c img,
                        .es-m-txt-l img {
                        display: inline !important
                        }

                        .es-button-border {
                        display: inline-block !important
                        }

                        a.es-button {
                        font-size: 20px !important;
                        display: inline-block !important;
                        border-width: 15px 25px 15px 25px !important
                        }

                        .es-btn-fw {
                        border-width: 10px 0px !important;
                        text-align: center !important
                        }

                        .es-adaptive table,
                        .es-btn-fw,
                        .es-btn-fw-brdr,
                        .es-left,
                        .es-right {
                        width: 100% !important
                        }

                        .es-content table,
                        .es-header table,
                        .es-footer table,
                        .es-content,
                        .es-footer,
                        .es-header {
                        width: 100% !important;
                        max-width: 600px !important
                        }

                        .es-adapt-td {
                        display: block !important;
                        width: 100% !important
                        }

                        .adapt-img {
                        width: 100% !important;
                        height: auto !important
                        }

                        .es-m-p0 {
                        padding: 0px !important
                        }

                        .es-m-p0r {
                        padding-right: 0px !important
                        }

                        .es-m-p0l {
                        padding-left: 0px !important
                        }

                        .es-m-p0t {
                        padding-top: 0px !important
                        }

                        .es-m-p0b {
                        padding-bottom: 0 !important
                        }

                        .es-m-p20b {
                        padding-bottom: 20px !important
                        }

                        .es-mobile-hidden,
                        .es-hidden {
                        display: none !important
                        }

                        .es-desk-hidden {
                        display: table-row !important;
                        width: auto !important;
                        overflow: visible !important;
                        float: none !important;
                        max-height: inherit !important;
                        line-height: inherit !important
                        }

                        .es-desk-menu-hidden {
                        display: table-cell !important
                        }

                        table.es-table-not-adapt,
                        .esd-block-html table {
                        width: auto !important
                        }

                        table.es-social {
                        display: inline-block !important
                        }

                        table.es-social td {
                        display: inline-block !important
                        }
                    }

                    #outlook a {
                        padding: 0;
                    }

                    .ExternalClass {
                        width: 100%;
                    }

                    .ExternalClass,
                    .ExternalClass p,
                    .ExternalClass span,
                    .ExternalClass font,
                    .ExternalClass td,
                    .ExternalClass div {
                        line-height: 100%;
                    }

                    .es-button {
                        mso-style-priority: 100 !important;
                        text-decoration: none !important;
                    }

                    a[x-apple-data-detectors] {
                        color: inherit !important;
                        text-decoration: none !important;
                        font-size: inherit !important;
                        font-family: inherit !important;
                        font-weight: inherit !important;
                        line-height: inherit !important;
                    }

                    .es-desk-hidden {
                        display: none;
                        float: left;
                        overflow: hidden;
                        width: 0;
                        max-height: 0;
                        line-height: 0;
                        mso-hide: all;
                    }
                </style>
            </head>

            <body
                style='width:100%;font-family: helvetica, arial, sans-serif;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;padding:0;Margin:0;'>
                <div class='es-wrapper-color' style='background-color:#F6F6F6;'>
                <!--[if gte mso 9]>
                            <v:background xmlns:v='urn:schemas-microsoft-com:vml' fill='t'>
                                <v:fill type='tile' color='#f6f6f6'></v:fill>
                            </v:background>
                        <![endif]-->
                <table class='es-wrapper' width='100%' cellspacing='0' cellpadding='0'
                    style='mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;padding:0;Margin:0;width:100%;height:100%;background-repeat:repeat;background-position:center top;'>
                    <tr style='border-collapse:collapse;'>
                    <td valign='top' style='padding:0;Margin:0;'>
                        <table class='es-header' cellspacing='0' cellpadding='0' align='center'
                        style='mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;table-layout:fixed !important;width:100%;background-color:transparent;background-repeat:repeat;background-position:center top;'>
                        <tr style='border-collapse:collapse;'>
                            <td
                            style='padding:0;Margin:0;background-position:center top;background-repeat:no-repeat;background-size:cover; background-image: linear-gradient(120deg, #a1c4fd 0%, #c2e9fb 100%);'
                            align='center'>
                            <table class='es-header-body'
                                style='mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:transparent;'
                                width='600' cellspacing='0' cellpadding='0' align='center'>
                                <tr style='border-collapse:collapse;'>
                                <td align='left'
                                    style='padding:0;Margin:0;padding-top:10px;padding-left:20px;padding-right:20px;background-position:center top;'>
                                    <table width='100%' cellspacing='0' cellpadding='0'
                                    style='mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;'>
                                    <tr style='border-collapse:collapse;'>
                                        <td width='560' valign='top' align='center' style='padding:0;Margin:0;'>
                                        <table width='100%' cellspacing='0' cellpadding='0'
                                            style='mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;'>

                                            <tr style='border-collapse:collapse;'>
                                            <td style='height: 50px'></td>
                                            </tr>

                                            <tr style='border-collapse:collapse;'>
                                            <td align='center' style='padding:0;Margin:0;'>
                                                <img src='https://dimantti.integracontrol.online/portal/images/logotipo.png'
                                                style='display:block;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic; width: 50%'
                                                alt='Logo' title='Logo'>
                                            </td>
                                            </tr>
                                            <tr style='border-collapse:collapse;'>
                                            <td style='height: 50px'></td>
                                            </tr>


                                            <tr style='border-collapse:collapse;'>
                                            <td align='center' style='padding:5px;Margin:0;'>
                                                <p
                                                style='Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:24px;font-family:roboto, helvetica neue, helvetica, arial, sans-serif;line-height:36px;color:#FFFFFF;'>
                                                <strong>Orden #$folio</strong></p>
                                            </td>
                                            </tr>
                                            <tr style='border-collapse:collapse;'>
                                            <td style='height: 50px'></td>
                                            </tr>

                                        </table>
                                        </td>
                                    </tr>
                                    </table>
                                </td>
                                </tr>
                            </table>
                            </td>
                        </tr>
                        </table>
                        <table class='es-content' cellspacing='0' cellpadding='0' align='center'
                        style='mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;table-layout:fixed !important;width:100%;'>
                        <tr style='border-collapse:collapse;'>
                            <td align='center' style='padding:0;Margin:0;'>
                            <table class='es-content-body'
                                style='mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:transparent;'
                                width='600' cellspacing='0' cellpadding='0' align='center'>
                                <tr style='border-collapse:collapse;'>
                                <td align='left' style='padding:0;Margin:0;padding-left:20px;padding-right:20px;'>
                                    <table width='100%' cellspacing='0' cellpadding='0'
                                    style='mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;'>
                                    <tr style='border-collapse:collapse;'>
                                        <td width='560' valign='top' align='center' esdev-config='h3' style='padding:0;Margin:0;'>
                                        <table
                                            style='mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:separate;border-spacing:0px;border-radius:3px;background-color:#FFFFFF;background-position:center top;'
                                            width='100%' cellspacing='0' cellpadding='0' bgcolor='#ffffff'>
                                            <tr style='border-collapse:collapse;'>
                                            <td align='center'
                                                style='Margin:0;padding-bottom:5px;padding-left:20px;padding-right:20px;padding-top:25px;'>
                                                <h2
                                                style='Margin:0;line-height:31px;mso-line-height-rule:exactly;font-family: helvetica, arial, sans-serif;font-size:26px;font-style:normal;font-weight:bold;color:#444444;'>
                                                Correo de confirmación</h2>
                                            </td>
                                            </tr>
                                            <tr style='border-collapse:collapse;'>
                                            <td align='center'
                                                style='Margin:0;padding-top:10px;padding-bottom:15px;padding-left:20px;padding-right:20px;'>
                                                <p
                                                style='Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:16px;font-family: helvetica, arial, sans-serif;line-height:24px;color:#999999;'>
                                                <span style='color:#696969;'>Hola, confirmamos la recepción de su(s) accesorio(s) con la orden de servicio #$folio</span> </p>
                                            </td>
                                            </tr>


                                            <tr style='border-collapse:collapse;'>
                                            <td align='center' style='padding:20px;Margin:0;'>
                                                <table border='0' width='100%' height='100%' cellpadding='0' cellspacing='0'
                                                style='mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;'>
                                                <tr style='border-collapse:collapse;'>
                                                    <td
                                                    style='padding:0;Margin:0px 0px 0px 0px;border-bottom:1px solid #CCCCCC;background:none;height:1px;width:100%;margin:0px;'>
                                                    </td>
                                                </tr>
                                                </table>
                                            </td>
                                            </tr>
                                            <tr style='border-collapse:collapse;'>
                                            <td align='center' style='padding:0;Margin:0;'>
                                                <p
                                                style='Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:16px;font-family: helvetica, arial, sans-serif;line-height:24px;color:#000000;'>
                                                <strong>Enlaces de la orden:&nbsp;</strong></p>
                                                <ul>
                                                <li
                                                    style='-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:16px;font-family: helvetica, arial, sans-serif;line-height:24px;Margin-bottom:15px;color:#696969;text-align:left;'>
                                                    <strong>Hoja de servicio: </strong>$link</li>
                                                <li
                                                    style='-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:16px;font-family: helvetica, arial, sans-serif;line-height:24px;Margin-bottom:15px;color:#696969;text-align:left;'>
                                                    <strong>Seguimiento de la orden: </strong>$linka</li>
                                                </ul>
                                                <br>
                                                <br>
                                                <p
                                                    style='Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:16px;font-family: helvetica, arial, sans-serif;line-height:24px;color:#999999;'>
                                                    <span style='color:#696969;'>
                                                        Este es un correo automatizado, favor de no responder
                                                    </span>
                                                </p>
                                                <br><br>
                                            </td>
                                            </tr>




                                        </table>
                                        </td>
                                    </tr>
                                    </table>
                                </td>
                                </tr>
                            </table>
                            <table class='es-content' cellspacing='0' cellpadding='0' align='center'
                                style='mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;table-layout:fixed !important;width:100%;'>
                                <tr style='border-collapse:collapse;'>
                                <td align='center' style='padding:0;Margin:0;'>
                                    <table class='es-content-body'
                                    style='mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:#F6F6F6;'
                                    width='600' cellspacing='0' cellpadding='0' bgcolor='#f6f6f6' align='center'>
                                    <tr style='border-collapse:collapse;'>
                                        <td align='left'
                                        style='padding:0;Margin:0;padding-top:10px;padding-left:20px;padding-right:20px;background-position:center top;'>
                                        <table width='100%' cellspacing='0' cellpadding='0'
                                            style='mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;'>
                                            <tr style='border-collapse:collapse;'>
                                            <td width='560' valign='top' align='center' style='padding:0;Margin:0;'>
                                                <table width='100%' cellspacing='0' cellpadding='0'
                                                style='mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;'>
                                                <tr style='border-collapse:collapse;'>
                                                    <td align='center'
                                                    style='padding:0;Margin:0;padding-top:10px;padding-bottom:10px;'>
                                                    <table width='100%' height='100%' cellspacing='0' cellpadding='0' border='0'
                                                        style='mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;'>
                                                        <tr style='border-collapse:collapse;'>
                                                        <td
                                                            style='padding:0;Margin:0px;border-bottom:1px solid #F6F6F6;background:rgba(0, 0, 0, 0) none repeat scroll 0% 0%;height:1px;width:100%;margin:0px;'>
                                                        </td>
                                                        </tr>
                                                    </table>
                                                    </td>
                                                </tr>
                                                </table>
                                            </td>
                                            </tr>
                                        </table>
                                        </td>
                                    </tr>
                                    </table>
                                </td>
                                </tr>
                            </table>
                            <table class='es-footer' cellspacing='0' cellpadding='0' align='center'
                                style='mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;table-layout:fixed !important;width:100%;background-color:transparent;background-repeat:repeat;background-position:center top;'>
                                <tr style='border-collapse:collapse;'>
                                <td align='center' style='padding:0;Margin:0;'>
                                    <table class='es-footer-body' width='600' cellspacing='0' cellpadding='0' align='center'
                                    style='mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:#F6F6F6;'>
                                    <tr style='border-collapse:collapse;'>
                                        <td align='left'
                                        style='Margin:0;padding-left:20px;padding-right:20px;padding-top:40px;padding-bottom:40px;'>
                                        <table width='100%' cellspacing='0' cellpadding='0'
                                            style='mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;'>
                                            <tr style='border-collapse:collapse;'>
                                            <td width='560' valign='top' align='center' style='padding:0;Margin:0;'>
                                                <table width='100%' cellspacing='0' cellpadding='0'
                                                style='mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;'>
                                                <tr style='border-collapse:collapse;'>
                                                    <td align='center' style='padding:0;Margin:0;padding-bottom:5px;'> <img
                                                        src='https://dimantti.integracontrol.online/portal/images/logotipo.png'
                                                        alt='Logo'
                                                        style='display:block;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic;'
                                                        title='Logo' width='150'></td>

                                                </tr>



                                                </table>
                                            </td>
                                            </tr>
                                        </table>
                                        </td>
                                    </tr>
                                    </table>
                                </td>
                                </tr>
                            </table>
                            <table class='es-content' cellspacing='0' cellpadding='0' align='center'
                                style='mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;table-layout:fixed !important;width:100%;'>
                                <tr style='border-collapse:collapse;'>
                                <td style='padding:0;Margin:0;background-color:#F6F6F6;' bgcolor='#f6f6f6' align='center'>
                                    <table class='es-content-body'
                                    style='mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:transparent;'
                                    width='600' cellspacing='0' cellpadding='0' align='center'>
                                    <tr style='border-collapse:collapse;'>
                                        <td align='left'
                                        style='Margin:0;padding-left:20px;padding-right:20px;padding-top:30px;padding-bottom:30px;'>
                                        <table width='100%' cellspacing='0' cellpadding='0'
                                            style='mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;'>
                                            <tr style='border-collapse:collapse;'>
                                            <td width='560' valign='top' align='center' style='padding:0;Margin:0;'>
                                                <table width='100%' cellspacing='0' cellpadding='0'
                                                style='mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;'>
                                                <tr style='border-collapse:collapse;'>
                                                    <td align='center' style='padding:0;Margin:0;display:none;'></td>
                                                </tr>
                                                </table>
                                            </td>
                                            </tr>
                                        </table>
                                        </td>
                                    </tr>
                                    </table>
                                </td>
                                </tr>
                            </table>
                            </td>
                        </tr>
                        </table>
                </div>
            </body>

            </html>";



        $mail = new PHPMailer;
        $mail->isSMTP();
        $mail->SMTPDebug = 0;
        $mail->Host = $host;
        $mail->Port = $puerto;
        $mail->SMTPAuth = true;
        $mail->Username = $correoe;
        $mail->Password = $pass;
        $mail->SETFrom($correoe, $titulom);
        $mail->addAddress($para);
        $mail->Subject = $tema;
        $mail->IsHTML(true);
        $mail->AltBody = $txtInfo;
        $mail->Body = $txtInfo;
        $mail->CharSet = 'UTF-8';
        $mail->SMTPSecure = "ssl";
        if (!$mail->send()) {
            $codigo = 201;
            $descripcion = "Error al enviar el correo";
        }
    }
}




$mysqli->close();
$detalle = array("nentrada" => $nentrada, "ph" => $cliente_telefono);
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => $detalle);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
