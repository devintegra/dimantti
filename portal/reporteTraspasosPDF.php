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

if (isset($_GET['origen']) && is_numeric($_GET['origen'])) {
    $sucursal_origen = (int)$_GET['origen'];
}

if (isset($_GET['destino']) && is_numeric($_GET['destino'])) {
    $sucursal_destino = (int)$_GET['destino'];
}

if (isset($_GET['producto']) && is_string($_GET['producto'])) {
    $clave = $_GET['producto'];
}


//FILTROS
#region
$flfechas = "";
$florigen = "";
$fldestino = "";
$flproducto = "";

//GENERAL
if ($inicio != "" && $fin != "") {
    $flfechas = " AND tr_transferencias.fecha BETWEEN '$inicio' AND '$fin'";
}

if ($sucursal_origen != 0) {
    $florigen = " AND tr_transferencias.fk_sucursal = $sucursal_origen";
}

if ($sucursal_destino != 0) {
    $fldestino = " AND tr_transferencias.fk_sucursal_destino = $sucursal_destino";
}

//DETALLE
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



$qtransferencia = "SELECT tr_transferencias.pk_transferencia,
	sucursalesa.nombre as origen,
    almacenesa.nombre as almacena,
    sucursalesb.nombre as destino,
    almacenesb.nombre as almacenb,
    tr_transferencias.fk_usuario,
    tr_transferencias.fecha,
    tr_transferencias.total
    FROM tr_transferencias, ct_sucursales as sucursalesa, ct_sucursales as sucursalesb, rt_sucursales_almacenes as almacenesa, rt_sucursales_almacenes as almacenesb
    WHERE tr_transferencias.estado = 1
    AND sucursalesa.pk_sucursal = tr_transferencias.fk_sucursal
    AND sucursalesb.pk_sucursal = tr_transferencias.fk_sucursal_destino
    AND almacenesa.pk_sucursal_almacen = tr_transferencias.fk_almacen
    AND almacenesb.pk_sucursal_almacen = tr_transferencias.fk_almacen_destino$flfechas $florigen $fldestino";



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

    $empresa_nombre = "Tectron";
    $empresa_id = "";
    $empresa_direccion = "Traspaso entre sucursales";
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
$pdf->addImage("servicios/logo.png", 160, 50);


$pdf->fact_dev("", "Reporte");
//$pdf->addFecha( $entrada_fecha);
$pdf->tipo_pago("" . "Traspasos");
$pdf->contacto("", $inicio . " - " . $fin);


$cols = array(
    "Fecha"    => 22,
    "Origen"  => 30,
    "Destino"      => 30,
    "Usuario"      => 20,
    "Codigo"      => 25,
    "Producto"      => 43,
    "Serie"      => 25,
    "Cantidad"      => 18,
    "Estatus"      => 25,
    "Unitario"      => 25,
    "Total"      => 25
);
$pdf->addColsInventario($cols, 45);

$cols = array(
    "Fecha"    => "C",
    "Origen"  => "C",
    "Destino"      => "C",
    "Usuario"      => "C",
    "Codigo"      => "C",
    "Producto"      => "C",
    "Serie"      => "C",
    "Cantidad"      => "C",
    "Estatus"      => "C",
    "Unitario"      => "R",
    "Total"      => "R"
);
$pdf->addLineFormat($cols);
$pdf->addLineFormat($cols);

$y    = 55;




$total_final = 0.00;


if (!$rtransferencia = $mysqli->query($qtransferencia)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.2";
    exit;
}

$paso = 0;

while ($res = $rtransferencia->fetch_assoc()) {

    $qdetalle = "SELECT tr_movimientos.pk_movimiento,
        tr_movimientos.fk_producto,
        ct_productos.codigobarras,
        ct_productos.nombre,
        tr_movimientos.serie,
        tr_movimientos.fk_movimiento as estatus,
        tr_movimientos.cantidad,
        (tr_movimientos.total / tr_movimientos.cantidad) as unitario,
        tr_movimientos.total
        FROM tr_movimientos, ct_productos
        WHERE tr_movimientos.fk_movimiento_detalle = $res[pk_transferencia]
        AND tr_movimientos.fk_movimiento IN(2,9,10)
        AND ct_productos.pk_producto = tr_movimientos.fk_producto$flproducto";


    if (!$rdetalle = $mysqli->query($qdetalle)) {
        echo "Lo sentimos, esta aplicación está experimentando problemas.3";
        exit;
    }

    if ($rdetalle->num_rows > 0) {

        while ($rowdetalle = $rdetalle->fetch_assoc()) {

            //Estatus
            #region
            if ($rowdetalle["estatus"] == 2) {
                $estatus = "Enviado";
            } else if ($rowdetalle["estatus"] == 9) {
                $estatus = "Recibido";
            } else if ($rowdetalle["estatus"] == 10) {
                $estatus = "Devuelto";
            }
            #endregion


            $line = array(
                "Fecha"    => $res["fecha"],
                "Origen"    => $res["origen"] . ". " . $res["almacena"],
                "Destino"  => $res["destino"] . ". " . $res["almacenb"],
                "Usuario" => $res["fk_usuario"],
                "Codigo" => $rowdetalle["codigobarras"],
                "Producto" => $rowdetalle["nombre"],
                "Serie" => $rowdetalle["serie"],
                "Cantidad" => $rowdetalle["cantidad"],
                "Estatus" => $estatus,
                "Unitario" => "$" . number_format($rowdetalle["unitario"], 2),
                "Total" => "$" . number_format($rowdetalle["total"], 2),
            );

            if ($rowdetalle["estatus"] == 2) {
                $size = $pdf->addLineFont($y, $line, 'B', 9);
            } else {
                $size = $pdf->addLineFont($y, $line, '', 8);
            }
            $y   += $size + 2;

            $paso += 2;

            if ($paso == 72) {
                $pdf->AddPage();
                $y = 30;
                $cols = array(
                    "Fecha"    => 22,
                    "Origen"  => 30,
                    "Destino"      => 30,
                    "Usuario"      => 20,
                    "Codigo"      => 25,
                    "Producto"      => 43,
                    "Serie"      => 25,
                    "Cantidad"      => 18,
                    "Estatus"      => 25,
                    "Unitario"      => 25,
                    "Total"      => 25
                );
                $pdf->addColsRetiro($cols, $y - 10);

                $cols = array(
                    "Fecha"    => "C",
                    "Origen"  => "C",
                    "Destino"      => "C",
                    "Usuario"      => "C",
                    "Codigo"      => "C",
                    "Producto"      => "C",
                    "Serie"      => "C",
                    "Cantidad"      => "C",
                    "Estatus"      => "C",
                    "Unitario"      => "R",
                    "Total"      => "R"
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
