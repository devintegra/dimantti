<?php
header("Access-Control-Allow-Origin: *");
include("servicios/conexioni.php");
require_once('servicios/tcpdf/tcpdf.php');
require('servicios/phpqrcode/qrlib.php');
setlocale(LC_ALL, 'es_ES');
mysqli_set_charset($mysqli, 'utf8');

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
}

$tr_table = "";


//ENCABEZADO
#region
$qentrada = "SELECT * FROM tr_cortes WHERE pk_corte = $id";

if (!$rentrada = $mysqli->query($qentrada)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas2.";
    exit;
}
$contrato = $rentrada->fetch_assoc();
$contrato_fecha = $contrato["fecha"];
$contrato_hora = $contrato["hora"];
$contrato_tipo = $contrato["origen"];
$contrato_sucursal = $contrato["fk_sucursal"];
$contrato_usuario = $contrato["fk_usuario"];
$contrato_add_comision = $contrato["add_comision"];
$contrato_efectivo = $contrato["efectivo"];
$contrato_transferencia = $contrato["transferencia"];
$contrato_tarjeta = $contrato["tarjeta"];
$contrato_cheque = $contrato["cheque"];
$contrato_comision = $contrato["comision"];
$contrato_total = $contrato["total"];

if ($contrato_tipo == 1) {
    $tipo = "Ventas";
} else {
    $tipo = "Reparación";
}
#endregion



//SUCURSAL
#region
if ($contrato_sucursal != 0) {

    $qsucursal = "SELECT * FROM ct_sucursales WHERE pk_sucursal = $contrato_sucursal";

    if (!$rsucursal = $mysqli->query($qsucursal)) {
        echo "Lo sentimos, esta aplicación está experimentando problemas1.";
        exit;
    }

    $empresa = $rsucursal->fetch_assoc();
    $empresa_nombre = $empresa["nombre"];
    $empresa_id = $empresa["pk_sucursal"];
    $empresa_direccion = $empresa["direccion"];
    $empresa_telefono = $empresa["telefono"];
    $empresa_correo = $empresa["correo"];
} else {

    $empresa_nombre = "Dimantti";
    $empresa_id = "";
    $empresa_direccion = "Todas las sucursales";
    $empresa_telefono = "";
    $empresa_correo = "";
}
#endregion



//USUARIO
#region
$eusuario = "SELECT * FROM ct_usuarios WHERE pk_usuario = '$contrato_usuario'";

if (!$resultado = $mysqli->query($eusuario)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}
$rowexistead = $resultado->fetch_assoc();

$nivel = $rowexistead["nivel"];
#endregion



//CALCULAR LA ALTURA DEL TICKET
#region
$mysqli->next_result();

$qdetalle = "SELECT SUM(registros) AS registros
    FROM (
        SELECT COUNT(*) as registros FROM tr_abonos WHERE fk_corte = $id AND monto > 0 AND estado = 1
        UNION ALL
        SELECT COUNT(*) as registros FROM tr_retiros WHERE fk_corte = $id AND monto > 0 AND estado = 1
        UNION ALL
        SELECT COUNT(*) as registros FROM tr_cargos WHERE fk_corte = $id AND cantidad > 0 AND estado = 1
    ) AS subconsulta;";

if (!$venta_detalle_registros = $mysqli->query($qdetalle)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. Error al calcular las dimensiones del ticket";
    exit;
}

$rowvdr = $venta_detalle_registros->fetch_assoc();
$total_registros = $rowvdr['registros'];

$page_height = ($total_registros + 11) * 10 + 140;
#endregion




//CREAR PDF
#region
$pageLayout = array(80, $page_height);
$pdf = new TCPDF('P', PDF_UNIT, $pageLayout, true, 'UTF-8', false);

$pdf->SetCreator("DIMANTTI");
$pdf->SetAuthor('DIMANTTI');
$pdf->SetTitle("CORTE DE CAJA #" . $id);

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(5, 5, 5);

$pdf->SetAutoPageBreak(FALSE, 5);

$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
    require_once(dirname(__FILE__) . '/lang/eng.php');
    $pdf->setLanguageArray($l);
}

$fontname = TCPDF_FONTS::addTTFfont('../ws/tcpdf/fonts/quicksand/Quicksand-Regular.ttf', 'TrueTypeUnicode', '');
$pdf->SetFont('dejavusans', '', 10);
$pdf->AddPage();
#endregion




//HEADER
#region
$parrafo_header = <<<HTML
    <table border="0" cellpadding="1">
        <tr>
            <td colspan="2">
                <h3 style="text-align: center;">CORTE #$id</h3>
                <h5 style="text-align: center; font-weight: light;">$empresa_direccion</h5>
                <h5 style="text-align: center; font-weight: light;">Tel $empresa_telefono</h5>
                <h5 style="text-align: center;">Tipo: $tipo</h5>
                <h5 style="text-align: center;">Usuario: $contrato_usuario</h5>
                <h5 style="text-align: center;">Fecha: $contrato_fecha $contrato_hora</h5>
            </td>
        </tr>
    </table>
    HTML;
#endregion




//VARIABLES TOTALES
#region
$total_efectivo = 0.00;
$total_transferencia = 0.00;
$total_credito = 0.00;
$total_debito = 0.00;
$total_cheque = 0.00;


$total_efectivo_retiros = 0.00;
$total_transferencia_retiros = 0.00;
$total_credito_retiros = 0.00;
$total_debito_retiros = 0.00;
$total_cheque_retiros = 0.00;

$total_final_abonos = 0.00;
$total_final_retiros = 0.00;
$total_final = 0.00;



$filtro = "";
$filtro_saldos = "";
$filtro_retiro = "";
$filtro_cargo = "";
$filtro_usuario_abonos = "";
$filtro_usuario_retiros = "";

if ($contrato_sucursal != 0) {
    $filtro = " AND tr_abonos.fk_sucursal = $contrato_sucursal";
    $filtro_saldos = " AND tr_saldos_iniciales.fk_sucursal = $contrato_sucursal";
    $filtro_retiro = " AND tr_retiros.fk_sucursal = $contrato_sucursal";
    $filtro_cargo = " AND tr_cargos.fk_sucursal = $contrato_sucursal";
}

if ($nivel != 1 && $nivel != 2) {
    $filtro_usuario_abonos = " AND tr_abonos.fk_usuario = '$contrato_usuario'";
    $filtro_usuario_retiros = " AND tr_retiros.fk_usuario = '$contrato_usuario'";
}
#endregion




//SALDOS INICIALES
#region
$registros_saldos = 0;
$qsaldos = "SELECT ct_sucursales.nombre as sucursal,
            tr_abonos.monto as cantidad,
            tr_abonos.fecha as fecha,
            tr_abonos.fk_pago,
            ct_pagos.nombre as pago,
            tr_saldos_iniciales.observaciones as observaciones
            FROM ct_sucursales, tr_abonos, tr_saldos_iniciales, ct_pagos
            WHERE tr_abonos.origen = 3
                AND tr_abonos.estado = 1
                AND tr_abonos.fk_sucursal = ct_sucursales.pk_sucursal
                AND tr_abonos.fk_factura = tr_saldos_iniciales.pk_saldo_inicial
                AND tr_abonos.fk_pago = ct_pagos.pk_pago
                AND tr_saldos_iniciales.estado = 1
                AND tr_abonos.monto > 0$filtro_saldos$filtro_usuario_abonos
                AND tr_saldos_iniciales.fk_corte = $id
                AND tr_abonos.fk_corte = $id
                ORDER BY tr_abonos.fk_pago";

if (!$rsaldos = $mysqli->query($qsaldos)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

while ($row = $rsaldos->fetch_assoc()) {

    $registros_saldos = 1;
    $total_efectivo += $row["cantidad"];

    $cantidadf = number_format($row["cantidad"], 2);
    $descripcion = "(Saldo inicial)" . $row['fecha'] . ". " . utf8_decode($row["observaciones"]);

    $tr_table .= <<<HTML
        <tr>
            <td style="width: 50%;">$descripcion</td>
            <td style="width: 25%; text-align: right;">$row[pago]</td>
            <td style="width: 25%; text-align: right;">$$cantidadf</td>
        </tr>
    HTML;
}

$tr_table .= <<<HTML
    <tr>
        <td colspan="3"></td>
    </tr>
HTML;
#endregion




//VENTAS -> ABONOS
#region
$registros_ventas = 0;
if ($contrato_tipo == 1) { //Venta

    $qventasd = "SELECT ct_sucursales.nombre as sucursal,
    tr_abonos.monto as cantidad,
    tr_abonos.fecha as fecha,
    tr_abonos.fk_pago,
    ct_pagos.nombre as pago,
    ct_clientes.nombre as cliente,
    tr_ventas.folio as folio
    FROM ct_sucursales, tr_abonos, tr_ventas, ct_clientes, ct_pagos
    WHERE tr_abonos.origen IN (1, 4)
        AND tr_abonos.estado = 1
        AND tr_abonos.fk_sucursal=ct_sucursales.pk_sucursal
        AND tr_abonos.fk_factura=tr_ventas.pk_venta
        AND tr_abonos.fk_pago = ct_pagos.pk_pago
        AND tr_ventas.fk_cliente=ct_clientes.pk_cliente
        AND tr_abonos.monto>0$filtro$filtro_usuario_abonos
        AND tr_abonos.fk_corte = $id
        ORDER BY tr_abonos.fk_pago";
} else {

    $qventasd = "SELECT ct_sucursales.nombre as sucursal,
        tr_abonos.monto as cantidad,
        tr_abonos.fecha as fecha,
        tr_abonos.fk_pago,
        ct_pagos.nombre as pago,
        ct_clientes.nombre as cliente,
        tr_ordenes.folio as folio
        FROM ct_sucursales, tr_ventas, tr_abonos, tr_ordenes, ct_clientes, ct_pagos
        WHERE tr_abonos.origen IN (2, 4)
            AND tr_abonos.estado = 1
            AND tr_abonos.fk_sucursal = ct_sucursales.pk_sucursal
            AND tr_abonos.fk_factura = tr_ventas.pk_venta
            AND tr_ordenes.fk_venta = tr_ventas.pk_venta
            AND tr_abonos.fk_pago = ct_pagos.pk_pago
            AND tr_ordenes.fk_cliente = ct_clientes.pk_cliente
            AND tr_abonos.monto > 0$filtro$filtro_usuario_abonos
            AND tr_abonos.fk_corte = $id
            ORDER BY tr_abonos.fk_pago";
}

if (!$rventasd = $mysqli->query($qventasd)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

while ($row = $rventasd->fetch_assoc()) {

    $registros_ventas = 1;

    if ($row["fk_pago"] == 1) {
        $total_efectivo += $row["cantidad"];
    }

    if ($row["fk_pago"] == 2) {
        $total_transferencia += $row["cantidad"];
    }

    if ($row["fk_pago"] == 3) {
        $total_debito += $row["cantidad"];
    }

    if ($row["fk_pago"] == 4) {
        $total_cheque += $row["cantidad"];
    }

    if ($row["fk_pago"] == 5) {
        $total_credito += $row["cantidad"];
    }

    $cantidadf = number_format($row["cantidad"], 2);
    $descripcion = "(Venta Mostrador) " . $row['fecha'] . "." . $row['folio'] . " (" . $row['cliente'] . ")";

    $tr_table .= <<<HTML
        <tr>
            <td style="width: 50%;">$descripcion</td>
            <td style="width: 25%; text-align: right;">$row[pago]</td>
            <td style="width: 25%; text-align: right;">$$cantidadf</td>
        </tr>
    HTML;
}

$tr_table .= <<<HTML
    <tr>
        <td colspan="3"></td>
    </tr>
HTML;
#endregion




//RETIROS
#region
$registros_retiros = 0;

$qretiros = "SELECT ct_sucursales.nombre as sucursal,
        ct_retiros.nombre as motivo,
        tr_retiros.descripcion as descripcion,
        tr_retiros.fecha as fecha,
        tr_retiros.monto as cantidad,
        tr_retiros.fk_pago as pago
    FROM tr_retiros
    LEFT JOIN ct_sucursales ON ct_sucursales.pk_sucursal = tr_retiros.fk_sucursal
    LEFT JOIN ct_retiros ON ct_retiros.pk_retiro = tr_retiros.fk_retiro
    WHERE tr_retiros.fk_corte = $id
    $filtro_retiro$filtro_usuario_retiros
    ORDER BY tr_retiros.fk_pago";


if (!$rretiros = $mysqli->query($qretiros)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

while ($row = $rretiros->fetch_assoc()) {

    $registros_retiros = 1;
    $npago = "";

    if ($row["pago"] == 1) {
        $total_efectivo_retiros = $total_efectivo_retiros + $row["cantidad"];
        $npago = "Efectivo";
    }

    if ($row["pago"] == 2) {
        $total_transferencia_retiros = $total_transferencia_retiros + $row["cantidad"];
        $npago = "Transferencia";
    }

    if ($row["pago"] == 3) {
        $total_debito_retiros = $total_debito_retiros + $row["cantidad"];
        $npago = "Tarjeta de debito";
    }

    if ($row["pago"] == 4) {
        $total_cheque_retiros = $total_cheque_retiros + $row["cantidad"];
        $npago = "Cheque";
    }

    if ($row["pago"] == 5) {
        $total_credito_retiros = $total_credito_retiros + $row["cantidad"];
        $npago = "Tarjeta de crédito";
    }

    $cantidadf = number_format($row["cantidad"], 2);
    $descripcion = "(Retiro) " . $row['fecha'] . "." . $row['motivo'] . " (" . $row['descripcion'] . ")";

    $tr_table .= <<<HTML
        <tr>
            <td style="width: 50%;">$descripcion</td>
            <td style="width: 25%; text-align: right;">$npago</td>
            <td style="width: 25%; text-align: right;">$$cantidadf</td>
        </tr>
    HTML;
}

$tr_table .= <<<HTML
    <tr>
        <td colspan="3"></td>
    </tr>
HTML;
#endregion




//TOTALES
#region
$total_efectivo = $total_efectivo;
$total_transferencia = $total_transferencia;
$total_credito = $total_credito;
$total_debito = $total_debito;
$total_cheque = $total_cheque;
$total_final_abonos = $total_efectivo + $total_transferencia + $total_credito + $total_debito + $total_cheque;


$total_efectivo_retiros = $total_efectivo_retiros;
$total_transferencia_retiros = $total_transferencia_retiros;
$total_credito_retiros = $total_credito_retiros;
$total_debito_retiros = $total_debito_retiros;
$total_cheque_retiros = $total_cheque_retiros;
$total_final_retiros = $total_efectivo_retiros + $total_transferencia_retiros + $total_credito_retiros + $total_debito_retiros + $total_cheque_retiros;

if ($contrato_add_comision == 0) {
    $total_final = $total_final_abonos - $total_final_retiros;
} else {
    $total_final = $total_final_abonos + $contrato_comision - $total_final_retiros;
}

$total_efectivo_retiros = number_format($total_efectivo_retiros, 2);
$total_efectivo = number_format($total_efectivo, 2);
$total_transferencia_retiros = number_format($total_transferencia_retiros, 2);
$total_transferencia = number_format($total_transferencia, 2);
$total_credito_retiros = number_format($total_credito_retiros, 2);
$total_credito = number_format($total_credito, 2);
$total_debito_retiros = number_format($total_debito_retiros, 2);
$total_debito = number_format($total_debito, 2);
$total_cheque_retiros = number_format($total_cheque_retiros, 2);
$total_cheque = number_format($total_cheque, 2);

$tr_table .= <<<HTML
    <tr style="font-weight: bold;">
        <td style="width: 50%;">METODO</td>
        <td style="width: 25%; text-align: right;">SALIDA</td>
        <td style="width: 25%; text-align: right;">ENTRADA</td>
    </tr>
HTML;

$tr_table .= <<<HTML
    <tr>
        <td style="width: 50%; font-weight: bold;">EFECTIVO</td>
        <td style="width: 25%; text-align: right;">-$total_efectivo_retiros</td>
        <td style="width: 25%; text-align: right;">$total_efectivo</td>
    </tr>
    <tr>
        <td style="width: 50%; font-weight: bold;">TRANSFERENCIA</td>
        <td style="width: 25%; text-align: right;">-$total_transferencia</td>
        <td style="width: 25%; text-align: right;">$total_transferencia</td>
    </tr>
    <tr>
        <td style="width: 50%; font-weight: bold;">T.CREDITO</td>
        <td style="width: 25%; text-align: right;">-$total_credito_retiros</td>
        <td style="width: 25%; text-align: right;">$total_credito</td>
    </tr>
    <tr>
        <td style="width: 50%; font-weight: bold;">T.DEBITO</td>
        <td style="width: 25%; text-align: right;">-$total_debito_retiros</td>
        <td style="width: 25%; text-align: right;">$total_debito</td>
    </tr>
    <tr>
        <td style="width: 50%; font-weight: bold;">CHEQUE</td>
        <td style="width: 25%; text-align: right;">-$total_cheque_retiros</td>
        <td style="width: 25%; text-align: right;">$total_cheque</td>
    </tr>
HTML;

if ($contrato_add_comision == 1) {
    $contrato_comision = number_format($contrato_comision, 2);
    $tr_table .= <<<HTML
        <tr>
            <td style="width: 50%; font-weight: bold;">COMISION</td>
            <td style="width: 25%; text-align: right;"></td>
            <td style="width: 25%; text-align: right;">$contrato_comision</td>
        </tr>
    HTML;
}

$total_final = number_format($total_final, 2);
$tr_table .= <<<HTML
    <tr style="font-size: 10px;">
        <td colspan="3"></td>
    </tr>
    <tr style="font-size: 10px;">
        <td style="width: 50%; font-weight: bold;">TOTAL</td>
        <td style="width: 50%; text-align: right;" colspan="2">$total_final</td>
    </tr>
HTML;
#endregion




//TABLA
#region
$content_table = <<<HTML
    <table border="1" cellpadding="2" style="text-align: center; font-size: 8px;">
        <thead>
            <tr style="font-weight: bold;">
                <th style="width: 50%;">DESCRIPCION</th>
                <th style="width: 25%;">PAGO</th>
                <th style="width: 25%;">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            $tr_table
        </tbody>
    </table>
HTML;
#endregion




//IMPRIMIR
#region
$pdf->Image('@' . file_get_contents('servicios/logo.png'), 30, 5, 0, 24);
$pdf->writeHTMLCell(70, '', 5, 25, $parrafo_header);
$pdf->writeHTMLCell(76, '', 2, 70, $content_table);
#endregion




//DETALLES FINALES
#region
// reset pointer to the last page
$pdf->lastPage();

// Obtener el ancho y alto de la página, teniendo en cuenta los márgenes
$anchoPagina = $pdf->getPageWidth() - 8 - 8;
$altoPagina = $pdf->getPageHeight() - 8 - 8;

//Close and output PDF document
$fileName = "Corte_" . $id . ".pdf";
$pdf->Output($fileName, 'I');
#endregion
