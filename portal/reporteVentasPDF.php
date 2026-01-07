<?php
include("servicios/conexioni.php");
require('servicios/entradaoPDF.php');
mysqli_set_charset($mysqli, 'utf8');


if (isset($_GET['inicio']) && is_string($_GET['inicio'])) {
    $inicio = $_GET['inicio'];
}

if (isset($_GET['fin']) && is_string($_GET['fin'])) {
    $fin = $_GET['fin'];
}

if (isset($_GET['sucursal']) && is_numeric($_GET['sucursal'])) {
    $sucursal = (int)$_GET['sucursal'];
}

if (isset($_GET['cliente']) && is_numeric($_GET['cliente'])) {
    $cliente = (int)$_GET['cliente'];
}

if (isset($_GET['vendedor']) && is_string($_GET['vendedor'])) {
    $fk_usuario = $_GET['vendedor'];
}

if (isset($_GET['pago']) && is_numeric($_GET['pago'])) {
    $pago = (int)$_GET['pago'];
}


//FILTROS
#region
$flfechas = "";
$flsucursal = "";
$flcliente = "";
$flusuario = "";
$flpago = "";

if ($inicio != "" && $fin != "") {
    $flfechas = " AND tr_ventas.fecha BETWEEN '$inicio' AND '$fin'";
}

if ($sucursal != 0) {
    $flsucursal = " AND tr_ventas.fk_sucursal = $sucursal";
}

if ($cliente != 0) {
    $flcliente = " AND tr_ventas.fk_cliente = $cliente";
}

if ($fk_usuario != '0') {
    $flusuario = " AND tr_ventas.fk_usuario = '$fk_usuario'";
}

if ($pago != 0) {

    switch ($pago) {
        case 1:
            $metodo = "efectivo";
            break;
        case 2:
            $metodo = "transferencia";
            break;
        case 3:
            $metodo = "debito";
            break;
        case 4:
            $metodo = "cheque";
            break;
        case 5:
            $metodo = "credito";
            break;
    }

    $flpago = " AND tr_ventas.$metodo > 0";
}
#endregion



$qventas = "SELECT tr_ventas.*,
	ct_sucursales.nombre as sucursal,
    ct_clientes.nombre as cliente
    FROM tr_ventas, ct_sucursales, ct_clientes
    WHERE ct_sucursales.pk_sucursal = tr_ventas.fk_sucursal
    AND ct_clientes.pk_cliente = tr_ventas.fk_cliente$flfechas $flsucursal $flcliente $flusuario $flpago";


//SUCURSAL
#region
if ($sucursal != 0) {

    $qsucursal = "select * from ct_sucursales where pk_sucursal = $sucursal";

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
    $direccionex = explode(",", $empresa_direccion);
    $direccionTxt = "$direccionex[0] $direccionex[1] \n" .
        "$direccionex[2] $direccionex[3] \n";
} else {

    $empresa_nombre = "Tectron";
    $empresa_id = "";
    $empresa_direccion = "Todas las sucursales";
    $empresa_telefono = "";
    $empresa_correo = "";
    $direccionTxt = $empresa_direccion;
}
#endregion



//CLIENTE
#region
if ($cliente != 0) {

    $eusuario = "select * from ct_clientes where pk_cliente = $cliente";

    if (!$resultado = $mysqli->query($eusuario)) {
        echo "<br>Lo sentimos, esta aplicación está experimentando problemas.";
        exit;
    }
    $rowexistead = $resultado->fetch_assoc();
    $cliente_nombre = $rowexistead["nombre"];
} else {
    $cliente_nombre = "";
}
#endregion


//PAGO
if ($pago != 0) {
    $qpago = "select * from ct_pagos where pk_pago=$pago";

    if (!$rpago = $mysqli->query($qpago)) {
        echo "<br>Lo sentimos, esta aplicación está experimentando problemas.";
        exit;
    }

    $pago = $rpago->fetch_assoc();
    $pago_nombre = $pago["nombre"];
} else {
    $pago_nombre = "";
}



$pdf = new PDF_Invoice('P', 'mm', array(500, 400));
$pdf->AddPage();
$pdf->SetAutoPageBreak(false);


$pdf->addSociete(
    $empresa_nombre,
    $direccionTxt .
        "Telefono: $empresa_telefono\n" .
        "Correo: $empresa_correo"
);
$pdf->addImage("servicios/logo.png", 160, 50);


$pdf->fact_dev("", "Reporte de ventas");
//$pdf->addFecha( $entrada_fecha);
$pdf->tipo_pago("" . "Ventas");
$pdf->addInfoBox("" . $inicio . " - " . $fin, 35, 16);
$pdf->addInfoBox("Pago: " . $pago_nombre, 35, 26);
$pdf->addInfoBox("Cliente: " . $cliente_nombre, 70, 16);
$pdf->addInfoBox("Vendedor: " . $fk_usuario, 70, 26);


$cols = array(
    "Fecha"    => 25,
    "Sucursal"  => 30,
    "Folio"      => 40,
    "Cliente"      => 30,
    "Vendedor"      => 25,
    "Estatus"      => 23,
    "Efectivo"      => 22,
    "Credito"      => 22,
    "Debito"      => 22,
    "Transferencia"      => 22,
    "Cheque"      => 22,
    "Subtotal"      => 22,
    "Descuento"      => 22,
    "Comision"      => 22,
    "Anticipo"      => 22,
    "Total"      => 22
);
$pdf->addColsInventario($cols, 45);

$cols = array(
    "Fecha"    => "C",
    "Sucursal"  => "C",
    "Folio"      => "C",
    "Cliente"      => "C",
    "Vendedor"      => "C",
    "Estatus"      => "C",
    "Efectivo"      => "R",
    "Credito"      => "R",
    "Debito"      => "R",
    "Transferencia"      => "R",
    "Cheque"      => "R",
    "Subtotal"      => "R",
    "Descuento"      => "R",
    "Comision"      => "R",
    "Anticipo"      => "R",
    "Total"      => "R"
);
$pdf->addLineFormat($cols);
$pdf->addLineFormat($cols);

$y    = 55;




$total_efectivo = 0.00;
$total_credito = 0.00;
$total_debito = 0.00;
$total_transferencia = 0.00;
$total_cheque = 0.00;
$total_final = 0.00;


if (!$rventas = $mysqli->query($qventas)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

$paso = 0;

while ($res = $rventas->fetch_assoc()) {

    if ($res['estatus'] == 2) {
        $estatus = "Devuelta";
    } else if ($res['estatus'] == 3) {
        $estatus = "Cancelada";
    } else {
        $estatus = "Venta";

        $total_efectivo += $res["efectivo"];
        $total_credito += $res["credito"];
        $total_debito += $res["debito"];
        $total_transferencia += $res["transferencia"];
        $total_cheque += $res["cheque"];
        $total_final += $res["total"];
    }

    $line = array(
        "Fecha"  => $res["fecha"] . " " . $res["hora"],
        "Sucursal"    => $res["sucursal"],
        "Folio"  => $res["folio"],
        "Cliente" => $res["cliente"],
        "Vendedor" => $res["fk_usuario"],
        "Estatus" => $estatus,
        "Efectivo" => "$" . number_format($res["efectivo"], 2),
        "Credito" => "$" . number_format($res["credito"], 2),
        "Debito" => "$" . number_format($res["debito"], 2),
        "Transferencia" => "$" . number_format($res["transferencia"], 2),
        "Cheque" => "$" . number_format($res["cheque"], 2),
        "Subtotal" => "$" . number_format($res["subtotal"], 2),
        "Descuento" => "$" . number_format($res["descuento"], 2),
        "Comision" => "$" . number_format($res["comision"], 2),
        "Anticipo" => "$" . number_format($res["anticipo"], 2),
        "Total" => "$" . number_format($res["total"], 2),
    );

    $size = $pdf->addLine($y, $line);
    $y   += $size + 2;

    $paso += 2;

    if ($paso == 72) {
        $pdf->AddPage();
        $y = 30;
        $cols = array(
            "Fecha"    => 25,
            "Sucursal"  => 30,
            "Folio"      => 40,
            "Cliente"      => 30,
            "Vendedor"      => 25,
            "Estatus"      => 23,
            "Efectivo"      => 22,
            "Credito"      => 22,
            "Debito"      => 22,
            "Transferencia"      => 22,
            "Cheque"      => 22,
            "Subtotal"      => 22,
            "Descuento"      => 22,
            "Comision"      => 22,
            "Anticipo"      => 22,
            "Total"      => 22
        );
        $pdf->addColsRetiro($cols, $y - 10);

        $cols = array(
            "Fecha"    => "C",
            "Sucursal"  => "C",
            "Folio"      => "C",
            "Cliente"      => "C",
            "Vendedor"      => "C",
            "Estatus"      => "C",
            "Efectivo"      => "R",
            "Credito"      => "R",
            "Debito"      => "R",
            "Transferencia"      => "R",
            "Cheque"      => "R",
            "Subtotal"      => "R",
            "Descuento"      => "R",
            "Comision"      => "R",
            "Anticipo"      => "R",
            "Total"      => "R"
        );
        $pdf->addLineFormat($cols);
        $pdf->addLineFormat($cols);

        $paso = 0;
    }
}
#endregion


$pdf->addTotalGeneral("$" . $total_final, 325, 468);


$ny = $y + 9;
$ny = $ny + 9;


$pdf->Output();


/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
