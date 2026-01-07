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

if (isset($_GET['gasto']) && is_numeric($_GET['gasto'])) {
    $gasto = (int)$_GET['gasto'];
}

if (isset($_GET['usuario']) && is_string($_GET['usuario'])) {
    $fk_usuario = $_GET['usuario'];
}


//FILTROS
#region
$flfechas = "";
$flsucursal = "";
$flgasto = "";
$flusuario = "";

if ($inicio != "" && $fin != "") {
    $flfechas = " AND tr_retiros.fecha BETWEEN '$inicio' AND '$fin'";
}

if ($sucursal != 0) {
    $flsucursal = " AND tr_retiros.fk_sucursal = $sucursal";
}

if ($gasto != 0) {
    $flgasto = " AND tr_retiros.fk_retiro = $gasto";
}

if ($fk_usuario != '0') {
    $flusuario = " AND tr_retiros.fk_usuario = '$fk_usuario'";
}
#endregion



$qgastos = "SELECT tr_retiros.*,
    ct_sucursales.nombre as sucursal,
	ct_retiros.nombre as gasto,
    ct_pagos.nombre as pago
    FROM tr_retiros, ct_sucursales, ct_retiros, ct_pagos
    WHERE tr_retiros.estado = 1
    AND ct_retiros.pk_retiro = tr_retiros.fk_retiro
    AND ct_sucursales.pk_sucursal = tr_retiros.fk_sucursal
    AND ct_pagos.pk_pago = tr_retiros.fk_pago$flfechas $flsucursal $flgasto $flusuario";



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
} else {

    $empresa_nombre = "Posmovil";
    $empresa_id = "";
    $empresa_direccion = "Todas las sucursales";
    $empresa_telefono = "";
    $empresa_correo = "";
}
#endregion





$pdf = new PDF_Invoice('P', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetAutoPageBreak(false);

$direccionex = explode(",", $empresa_direccion);


$pdf->addSociete(
    $empresa_nombre,
    "$direccionex[0] $direccionex[1] \n" .
        "$direccionex[2] $direccionex[3] \n" .
        "Telefono: $empresa_telefono\n" .
        "Correo: $empresa_correo"
);
$pdf->addImage("servicios/logo.png", 160, 50);


$pdf->fact_dev("", "Reporte");
//$pdf->addFecha( $entrada_fecha);
$pdf->tipo_pago("" . "Gastos");
$pdf->contacto("", $inicio . " - " . $fin);


$cols = array(
    "Sucursal"    => 30,
    "Usuario"  => 25,
    "Gasto"      => 35,
    "Observaciones"      => 45,
    "Pago"      => 20,
    "Fecha"      => 23,
    "Monto"      => 20
);
$pdf->addColsInventario($cols, 45);

$cols = array(
    "Sucursal"    => "C",
    "Usuario"  => "C",
    "Gasto"      => "C",
    "Observaciones"      => "C",
    "Pago"      => "C",
    "Fecha"      => "C",
    "Monto"      => "R"
);
$pdf->addLineFormat($cols);
$pdf->addLineFormat($cols);

$y    = 55;




$total_final = 0.00;

if (!$rgastos = $mysqli->query($qgastos)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

$paso = 0;

while ($res = $rgastos->fetch_assoc()) {

    $line = array(
        "Sucursal"    => $res["sucursal"],
        "Usuario"  => $res["fk_usuario"],
        "Gasto" => $res["gasto"],
        "Observaciones" => $res["descripcion"],
        "Pago" => $res["pago"],
        "Fecha" => $res["fecha"] . " " . $res["hora"],
        "Monto" => "$" . number_format($res["monto"], 2),
    );

    $size = $pdf->addLine($y, $line);
    $y   += $size + 2;

    $paso += 2;

    if ($paso == 72) {
        $pdf->AddPage();
        $y = 30;
        $cols = array(
            "Sucursal"    => 30,
            "Usuario"  => 25,
            "Gasto"      => 35,
            "Observaciones"      => 45,
            "Pago"      => 20,
            "Fecha"      => 23,
            "Monto"      => 20
        );
        $pdf->addColsRetiro($cols, $y - 10);

        $cols = array(
            "Sucursal"    => "C",
            "Usuario"  => "C",
            "Gasto"      => "C",
            "Observaciones"      => "C",
            "Pago"      => "C",
            "Fecha"      => "C",
            "Monto"      => "R"
        );
        $pdf->addLineFormat($cols);
        $pdf->addLineFormat($cols);

        $paso = 0;
    }

    $total_final += $res["monto"];
}
#endregion


$pdf->addTotalGeneral("$" . $total_final, 135, 265);


$ny = $y + 9;
$ny = $ny + 9;


$pdf->Output();


/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
