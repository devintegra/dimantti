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

if (isset($_GET['tipo']) && is_numeric($_GET['tipo'])) {
    $tipo = (int)$_GET['tipo'];
}


//FILTROS
#region
$flfechas = "";
$flsucursal = "";
$flcliente = "";
$flusuario = "";
$flpago = "";
$rpfechas = "";
$rpsucursal = "";
$rpcliente = "";
$rpusuario = "";
$rppago = "";

if ($inicio != "" && $fin != "") {
    $flfechas = " AND tr_ventas.fecha BETWEEN '$inicio' AND '$fin'";
    $rpfechas = " AND tr_ordenes.fecha BETWEEN '$inicio' AND '$fin'";
}

if ($sucursal != 0) {
    $flsucursal = " AND tr_ventas.fk_sucursal = $sucursal";
    $rpsucursal = " AND tr_ordenes.fk_sucursal = $sucursal";
}

if ($cliente != 0) {
    $flcliente = " AND tr_ventas.fk_cliente = $cliente";
    $rpcliente = " AND tr_ordenes.fk_cliente = $cliente";
}

$vendedor = "Todos";
if ($fk_usuario != '0') {
    $flusuario = " AND tr_ventas.fk_usuario = '$fk_usuario'";
    $rpusuario = " AND tr_ordenes.fk_usuario = '$fk_usuario'";
    $vendedor = $fk_usuario;
}

if ($pago != 0) {

    switch ($pago) {
        case 1:
            $metodo = "Efectivo. ";
            $flpago = " AND tr_ventas.efectivo > 0";
            break;
        case 2:
            $metodo = "Trans. ";
            $flpago = " AND tr_ventas.transferencia > 0";
            break;
        case 3:
            $metodo = "Debito. ";
            $flpago = " AND tr_ventas.debito > 0";
            break;
        case 4:
            $metodo = "Cheque. ";
            $flpago = " AND tr_ventas.cheque > 0";
            break;
        case 5:
            $metodo = "Credito. ";
            $flpago = " AND tr_ventas.credito > 0";
            break;
    }
}

if ($tipo == 0) {
    $tipo_venta_nom = "Ambos";
} else if ($tipo == 1) {
    $tipo_venta_nom = "Ventas";
} else {
    $tipo_venta_nom = "Órdenes";
}
#endregion





//SUCURSAL
#region
if ($sucursal != 0) {
    $qsucursal = "SELECT * FROM ct_sucursales where pk_sucursal = $sucursal";

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


//CLIENTE
#region
if ($cliente != 0) {
    $eusuario = "SELECT * FROM ct_clientes where pk_cliente = $cliente";

    if (!$resultado = $mysqli->query($eusuario)) {
        echo "<br>Lo sentimos, esta aplicación está experimentando problemas.";
        exit;
    }
    $rowexistead = $resultado->fetch_assoc();
    $cliente_nombre = $rowexistead["nombre"];
} else {
    $cliente_nombre = "Todos";
}
#endregion


//PAGO
#region
if ($pago != 0) {
    $qpago = "SELECT * FROM ct_pagos where pk_pago=$pago";

    if (!$rpago = $mysqli->query($qpago)) {
        echo "<br>Lo sentimos, esta aplicación está experimentando problemas.";
        exit;
    }

    $pago = $rpago->fetch_assoc();
    $pago_nombre = $pago["nombre"];
} else {
    $pago_nombre = "Todos";
}
#endregion



$pdf = new PDF_Invoice('P', 'mm', array(500, 350));
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


$pdf->fact_dev("", "Reporte de ventas");
//$pdf->addFecha( $entrada_fecha);
$pdf->tipo_pago("" . "Ventas");
$pdf->addInfoBox("" . $inicio . " - " . $fin, 35, 16);
$pdf->addInfoBox("Pago: " . $pago_nombre, 35, 26);
$pdf->addInfoBox("Cliente: " . $cliente_nombre, 70, 16);
$pdf->addInfoBox("Vendedor: " . $vendedor, 70, 26);
$pdf->addInfoBox("Tipo: " . $tipo_venta_nom, 105, 26);


$cols = array(
    "Fecha"    => 35,
    "IdVenta"  => 20,
    "Tipo"      => 20,
    "Sucursal"      => 50,
    "Cliente"      => 60,
    "Forma de pago"      => 40,
    "Vendedor"      => 40,
    "Total"      => 22,
    "Utilidad"      => 22,
    "Estatus"      => 30
);
$pdf->addColsInventario($cols, 45);

$cols = array(
    "Fecha"    => "C",
    "IdVenta"  => "C",
    "Tipo"      => "C",
    "Sucursal"      => "C",
    "Cliente"      => "C",
    "Forma de pago"      => "C",
    "Vendedor"      => "C",
    "Total"      => "R",
    "Utilidad"      => "R",
    "Estatus"      => "C"
);
$pdf->addLineFormat($cols);
$pdf->addLineFormat($cols);

$y    = 55;



$paso = 0;
$total_final = 0.00;
$total_utilidad = 0.00;


//VENTAS
if ($tipo == 0 || $tipo == 1) {

    $qventas = "SELECT tr_ventas.pk_venta,
        tr_ventas.fecha,
        tr_ventas.hora,
        ct_sucursales.nombre as sucursal,
        ct_clientes.nombre as cliente,
        tr_ventas.fk_usuario,
        tr_ventas.total,
        tr_ventas.estatus
        FROM tr_ventas, ct_sucursales, ct_clientes
        WHERE tr_ventas.tipo IN(1,2,3,4)
        AND tr_ventas.fecha BETWEEN '$inicio' AND '$fin'
        AND ct_sucursales.pk_sucursal = tr_ventas.fk_sucursal
        AND ct_clientes.pk_cliente = tr_ventas.fk_cliente$flsucursal $flcliente $flusuario $flpago";

    if (!$rventas = $mysqli->query($qventas)) {
        echo "Lo sentimos, esta aplicación está experimentando problemas.1";
        exit;
    }

    while ($row = $rventas->fetch_assoc()) {

        $total_venta = 0;
        $total_costo_productos = 0;

        //SABER QUE TIPO DE DEVOLUCION/CANCELACION FUE
        #region
        if ($row['estatus'] == 2 || $row['estatus'] == 3) {
            $qtipo = "SELECT * FROM tr_devoluciones WHERE fk_venta = $row[pk_venta] AND estado = 1";

            if (!$rtipo = $mysqli->query($qtipo)) {
                echo "<br>Lo sentimos, esta aplicación está experimentando problemas.2";
                exit;
            }

            $tipod = $rtipo->fetch_assoc();
            $tipo_devolucion = $tipod["tipo"]; //1 -> Con dinero,  2 -> Sin dinero
        }
        #endregion


        //MÉTODO DE PAGO
        #region
        $qpago = "SELECT CONCAT(
            CASE WHEN efectivo > 0 THEN 'Efectivo. ' ELSE '' END,
            CASE WHEN credito > 0 THEN 'Crédito. ' ELSE '' END,
            CASE WHEN debito > 0 THEN 'Debito. ' ELSE '' END,
            CASE WHEN cheque > 0 THEN 'Cheque. ' ELSE '' END,
            CASE WHEN transferencia > 0 THEN 'Tran. ' ELSE '' END
        ) AS campos_cumplen
        FROM tr_ventas
        WHERE pk_venta = $row[pk_venta]";

        if (!$rpago = $mysqli->query($qpago)) {
            echo "<br>Lo sentimos, esta aplicación está experimentando problemas.";
            exit;
        }

        $rowpago = $rpago->fetch_assoc();
        $npago = $rowpago["campos_cumplen"];
        #endregion


        //DEVOLUCION
        if ($row['estatus'] == 2) {
            $estatus = "Devuelta";
            $nestatus = "badge-warning-integra";

            if ($tipo_devolucion == 1) {
                $qproductos = "SELECT * FROM tr_ventas_detalle WHERE fk_venta = $row[pk_venta] AND devuelto = 0 AND estado = 1";
            }

            if ($tipo_devolucion == 2) {
                $qproductos = "SELECT * FROM tr_ventas_detalle WHERE fk_venta = $row[pk_venta] AND estado = 1";
            }
        }


        //CANCELACION
        if ($row['estatus'] == 3) {
            $estatus = "Cancelada";
            $nestatus = "badge-danger-integra";

            if ($tipo_devolucion == 1) {
                $total_costo_productos = $row['total'];
            }

            if ($tipo_devolucion == 2) {
                $qproductos = "SELECT * FROM tr_ventas_detalle WHERE fk_venta = $row[pk_venta] AND estado = 1";
            }
        }


        //VENTA NORMAL
        if ($row['estatus'] == 1) {
            $estatus = "Registrada";
            $nestatus = "badge-primary-integra";
            $qproductos = "SELECT * FROM tr_ventas_detalle WHERE fk_venta = $row[pk_venta] AND estado = 1";
        }


        //EJECUTAR CONSULTA
        if ($qproductos) {
            if (!$rproductos = $mysqli->query($qproductos)) {
                echo "<br>Lo sentimos, esta aplicación está experimentando problemas.3" . $mysqli->error;
                exit;
            }

            while ($rowproducto = $rproductos->fetch_assoc()) {

                $total_venta += $rowproducto['total'];

                $qcostos = "SELECT * FROM ct_productos WHERE pk_producto = $rowproducto[fk_producto] AND estado = 1";

                if (!$rcostos = $mysqli->query($qcostos)) {
                    echo "<br>Lo sentimos, esta aplicación está experimentando problemas.4";
                    exit;
                }

                $costos = $rcostos->fetch_assoc();
                $total_costo_productos += ($costos["costo"] * $rowproducto['cantidad']);
            }
        }


        $utilidad = $total_venta - $total_costo_productos;

        $line = array(
            "Fecha"  => $row["fecha"] . " " . $row["hora"],
            "IdVenta"    => $row["pk_venta"],
            "Tipo"  => 'VENTA',
            "Sucursal" => $row["sucursal"],
            "Cliente" => $row["cliente"],
            "Forma de pago" => $npago,
            "Vendedor" => $row["fk_usuario"],
            "Total" => "$" . number_format($total_venta, 2),
            "Utilidad" => "$" . number_format($utilidad, 2),
            "Estatus" => $estatus
        );

        $size = $pdf->addLine($y, $line);
        $y   += $size + 2;

        $paso += 2;

        if ($paso == 72) {
            $pdf->AddPage();
            $y = 30;
            $cols = array(
                "Fecha"    => 35,
                "IdVenta"  => 20,
                "Tipo"      => 20,
                "Sucursal"      => 50,
                "Cliente"      => 60,
                "Forma de pago"      => 40,
                "Vendedor"      => 40,
                "Total"      => 22,
                "Utilidad"      => 22,
                "Estatus"      => 30
            );
            $pdf->addColsRetiro($cols, $y - 10);

            $cols = array(
                "Fecha"    => "C",
                "IdVenta"  => "C",
                "Tipo"      => "C",
                "Sucursal"      => "C",
                "Cliente"      => "C",
                "Forma de pago"      => "C",
                "Vendedor"      => "C",
                "Total"      => "R",
                "Utilidad"      => "R",
                "Estatus"      => "C"
            );
            $pdf->addLineFormat($cols);
            $pdf->addLineFormat($cols);

            $paso = 0;
        }

        $total_final += $total_venta;
        $total_utilidad += $utilidad;
    }
}



$pdf->addCantidades("$" . $total_final, 240, 468, 22);
$pdf->addCantidades("$" . $total_utilidad, 262, 468, 22);


$ny = $y + 9;
$ny = $ny + 9;


$pdf->Output();
