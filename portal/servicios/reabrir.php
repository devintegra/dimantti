<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
require 'correo/PHPMailerAutoload.php';
mysqli_set_charset($mysqli, 'utf8');
date_default_timezone_set('America/Mexico_City');
$norden = 0;
$codigo = 200;
$descripcion = "";



if (isset($_POST['pk_orden']) && is_numeric($_POST['pk_orden'])) {
    $pk_orden = (int)$_POST['pk_orden'];
}

if (isset($_POST['fk_usuario']) && is_string($_POST['fk_usuario'])) {
    $fk_usuario = $_POST['fk_usuario'];
}

$hora_actual = date("H") . ":" . date("i") . ":" . date("s");



//ORDEN
$mysqli->next_result();
if (!$rorden = $mysqli->query("CALL sp_get_orden($pk_orden)")) {
    $codigo = 201;
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

$orden = $rorden->fetch_assoc();
$reabierta = $orden["reabierta"] + 1;
$fk_cliente = $orden["fk_cliente"];
$folio = $orden["folio"];
$cliente_telefono = $orden["telefono"];
$cliente_correo = $orden["correo"];




//ESTATUS
$mysqli->next_result();
if (!$mysqli->query("UPDATE tr_ordenes SET estatus = 3, reabierta = $reabierta WHERE pk_orden = $pk_orden")) {
    $codigo = 201;
    $descripcion = "Error al actualizar la órden";
}




//REGISTRO
$mysqli->next_result();
if (!$rsp_set_registro = $mysqli->query("CALL sp_set_orden_registro($pk_orden, 2, 'Reapertura de orden', '$fk_usuario', 1, '', 0, 0, 0, 0, 0, 1, 0)")) {
    $codigo = 201;
    $descripcion = "Error al guardar el registro en la bitácora";
}

$rowr = $rsp_set_registro->fetch_assoc();
$nregistro = $rowr["pk_registro"];





//CORREO
if (filter_var($cliente_correo, FILTER_VALIDATE_EMAIL)) {

    $host = "mail.integradesarrollo.com";
    $puerto = "465";
    $correoe = "notificaciones@integradesarrollo.com";
    $pass = "Notificaciones2022!";
    $leyenda = "Dimantti - Un detalle inolvidable";
    $tema = "ORDEN #" . $folio;
    $para = $cliente_correo;
    $titulom = "Se ha hecho un registro de la órden de servicio #$folio";


    $txtInfo = "<html
        style='width:100%;font-family: helvetica, arial, sans-serif;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;padding:0;Margin:0;'>

        <head>
            <meta charset='UTF-8'>
            <meta http-equiv='Content-Type' content='text/html' charset='utf-8' />
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
                                            Descripción</h2>
                                        </td>
                                        </tr>
                                        <tr style='border-collapse:collapse;'>
                                        <td align='center'
                                            style='Margin:0;padding-top:10px;padding-bottom:15px;padding-left:20px;padding-right:20px;'>
                                            <p
                                            style='Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:16px;font-family: helvetica, arial, sans-serif;line-height:24px;color:#999999;'>
                                            <span style='color:#696969;'>$descripcion</span> </p>
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
    $mail->setFrom($correoe, $titulom);
    $mail->addAddress($para);
    $mail->Subject = $tema;
    $mail->IsHTML(true);
    $mail->AltBody = $txtInfo;
    $mail->Body = $txtInfo;
    $mail->CharSet = 'UTF-8';
    $mail->SMTPSecure = "ssl";
    $mail->send();
}








$mysqli->close();
$detalle = array("orden" => $nregistro);
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => $detalle);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
