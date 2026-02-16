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

if (isset($_GET['categoria']) && is_numeric($_GET['categoria'])) {
    $categoria = (int)$_GET['categoria'];
}

if (isset($_GET['proveedor']) && is_numeric($_GET['proveedor'])) {
    $proveedor = (int)$_GET['proveedor'];
}

if (isset($_GET['credito']) && is_numeric($_GET['credito'])) {
    $credito = (int)$_GET['credito'];
}

if (isset($_GET['producto']) && is_string($_GET['producto'])) {
    $clave = $_GET['producto'];
}


//FILTROS
#region
$flfechas = "";
$flsucursal = "";
$flcategoria = "";
$flproveedor = "";
$flcredito = "";
$flproducto = "";

//GENERAL
if ($inicio != "" && $fin != "") {
    $flfechas = " AND tr_compras.fecha BETWEEN '$inicio' AND '$fin'";
}

if ($sucursal != 0) {
    $flsucursal = " AND tr_compras.fk_almacen = $sucursal";
}

if ($proveedor != 0) {
    $flproveedor = " AND tr_compras.fk_proveedor = $proveedor";
}

if ($credito != 0) {
    $flcredito = " AND tr_compras.tipo_pago = $credito";
}

//DETALLE
if ($categoria != 0) {
    $flcategoria = " AND ct_productos.fk_categoria = $categoria";
}

if ($clave != "") {

    $claves = array();
    $ex = (explode(',', $clave));

    foreach ($ex as $key => $value) {
        array_push($claves, '"' . $value . '"');
    }

    $join = implode(',', $claves);

    $flproducto = " AND ct_productos.codigobarras in ($join)";
}
#endregion



$qcompra = "SELECT tr_compras.pk_compra,
	ct_proveedores.nombre as proveedor,
    ct_sucursales.nombre as sucursal,
    rt_sucursales_almacenes.nombre as almacen,
    tr_compras.fk_usuario,
    tr_compras.fecha,
    ct_pagos.nombre as pago,
    tr_compras.tipo_pago,
    tr_compras.saldo,
    tr_compras.total,
    tr_compras.estatus
    FROM tr_compras, ct_proveedores, ct_sucursales, rt_sucursales_almacenes, ct_pagos
    WHERE tr_compras.estado = 1
    AND ct_proveedores.pk_proveedor = tr_compras.fk_proveedor
    AND ct_sucursales.pk_sucursal = tr_compras.fk_sucursal
    AND rt_sucursales_almacenes.pk_sucursal_almacen = tr_compras.fk_almacen
    AND ct_pagos.pk_pago = tr_compras.fk_pago$flfechas $flproveedor $flsucursal $flcredito";



//SUCURSAL
#region
if ($sucursal != 0) {

    $qsucursal = "select * from ct_sucursales where pk_sucursal = $sucursal";

    if (!$rsucursal = $mysqli->query($qsucursal)) {
        echo "Lo sentimos, esta aplicación está experimentando problemas.1";
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





$pdf = new PDF_Invoice('P', 'mm', 'A3');
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
$pdf->addImage("servicios/logo.png", 70, 70);


$pdf->fact_dev("", "Reporte");
//$pdf->addFecha( $entrada_fecha);
$pdf->tipo_pago("" . "Compras");
$pdf->contacto("", $inicio . " - " . $fin);


$cols = array(
    "Fecha"    => 15,
    "Sucursal"    => 28,
    "Proveedor"  => 20,
    "Usuario"      => 20,
    "Pago"      => 20,
    "Credito"      => 15,
    "Saldo"      => 23,
    "Total"      => 22,
    "Codigo"      => 25,
    "Producto"      => 40,
    "Cantidad"      => 15,
    "Unitario"      => 22,
    "Subtotal"      => 22
);
$pdf->addColsInventario($cols, 45);

$cols = array(
    "Fecha"    => "C",
    "Sucursal"    => "C",
    "Proveedor"  => "C",
    "Usuario"      => "C",
    "Pago"      => "C",
    "Credito"      => "C",
    "Saldo"      => "C",
    "Total"      => "C",
    "Codigo"      => "C",
    "Producto"      => "C",
    "Cantidad"      => "C",
    "Unitario"      => "R",
    "Subtotal"      => "R"
);
$pdf->addLineFormat($cols);
$pdf->addLineFormat($cols);

$y    = 55;




$total_final = 0.00;


if (!$rcompra = $mysqli->query($qcompra)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.2";
    exit;
}

$paso = 0;

while ($res = $rcompra->fetch_assoc()) {

    $qdetalle = "SELECT tr_compras_detalle.pk_compra_detalle,
        ct_productos.codigobarras,
        ct_productos.nombre,
        ct_productos.descripcion,
        tr_compras_detalle.cantidad,
        tr_compras_detalle.unitario,
        tr_compras_detalle.total
        FROM tr_compras_detalle, ct_productos
        WHERE tr_compras_detalle.fk_compra = $res[pk_compra]
        AND ct_productos.pk_producto = tr_compras_detalle.fk_producto$flcategoria $flmarca $flproducto";


    if (!$rdetalle = $mysqli->query($qdetalle)) {
        echo "Lo sentimos, esta aplicación está experimentando problemas.3";
        exit;
    }

    if ($rdetalle->num_rows > 0) {

        //Tipo de crédito
        #region
        if ($res["tipo_pago"] == 1) {
            $credito = "Total";
        } else if ($res["tipo_pago"] == 2) {
            $credito = "Parcial";
        } else {
            $credito = "Crédito";
        }
        #endregion

        while ($rowdetalle = $rdetalle->fetch_assoc()) {
            $line = array(
                "Fecha"    => $res["fecha"],
                "Sucursal"    => $res["sucursal"] . ". " .  $res["almacen"],
                "Proveedor"  => $res["proveedor"],
                "Usuario" => $res["fk_usuario"],
                "Pago" => $res["pago"],
                "Credito" => $credito,
                "Saldo" => "$" . number_format($res["saldo"], 2),
                "Total" => "$" . number_format($res["total"], 2),
                "Codigo" => $rowdetalle["codigobarras"],
                "Producto" => $rowdetalle["nombre"],
                "Cantidad" => $rowdetalle["cantidad"],
                "Unitario" => "$" . number_format($rowdetalle["unitario"], 2),
                "Subtotal" => "$" . number_format($rowdetalle["total"], 2),
            );

            $size = $pdf->addLine($y, $line);
            $y   += $size + 2;

            $paso += 2;

            if ($paso == 72) {
                $pdf->AddPage();
                $y = 30;
                $cols = array(
                    "Fecha"    => 15,
                    "Sucursal"    => 28,
                    "Proveedor"  => 20,
                    "Usuario"      => 20,
                    "Pago"      => 20,
                    "Credito"      => 15,
                    "Saldo"      => 23,
                    "Total"      => 22,
                    "Codigo"      => 25,
                    "Producto"      => 40,
                    "Cantidad"      => 15,
                    "Unitario"      => 22,
                    "Subtotal"      => 22
                );
                $pdf->addColsRetiro($cols, $y - 10);

                $cols = array(
                    "Fecha"    => "C",
                    "Sucursal"    => "C",
                    "Proveedor"  => "C",
                    "Usuario"      => "C",
                    "Pago"      => "C",
                    "Credito"      => "C",
                    "Saldo"      => "C",
                    "Total"      => "C",
                    "Codigo"      => "C",
                    "Producto"      => "C",
                    "Cantidad"      => "C",
                    "Unitario"      => "R",
                    "Subtotal"      => "R"
                );
                $pdf->addLineFormat($cols);
                $pdf->addLineFormat($cols);

                $paso = 0;
            }
        }
    }
}
#endregion



$ny = $y + 9;
$ny = $ny + 9;


$pdf->Output();
