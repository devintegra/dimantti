<?php
include("servicios/conexioni.php");
require('servicios/entradaoPDF.php');
mysqli_set_charset($mysqli, 'utf8');

if (isset($_GET['nivel']) && is_numeric($_GET['nivel'])) {
    $nivel = (int)$_GET['nivel'];
}

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

if (isset($_GET['categoria']) && is_numeric($_GET['categoria'])) {
    $categoria = (int)$_GET['categoria'];
}

if (isset($_GET['agrupar']) && is_numeric($_GET['agrupar'])) {
    $agrupar = (int)$_GET['agrupar'];
}

if (isset($_GET['producto']) && is_string($_GET['producto'])) {
    $clave = $_GET['producto'];
}

if (isset($_GET['tipo']) && is_numeric($_GET['tipo'])) {
    $tipo_venta = (int)$_GET['tipo'];
}


//FILTROS
#region
$flfechas = "";
$flsucursal = "";
$flcliente = "";
$flusuario = "";
$flpago = "";
$flcategoria = "";
$flagrupar = "";
$flproducto = "";
$flsum = "tr_ventas_detalle.cantidad";
$flserie = "tr_ventas_detalle.serie";
$oragrupar = "";
$orsum = "rt_ordenes_detalle.cantidad";

if ($inicio != "" && $fin != "") {
    $flfechas = " AND tr_ventas.fecha BETWEEN '$inicio' AND '$fin'";
}

if ($sucursal != 0) {
    $flsucursal = " AND tr_ventas.fk_sucursal = $sucursal";
}

if ($cliente != 0) {
    $flcliente = " AND tr_ventas.fk_cliente = $cliente";
}

$vendedor = "Todos";
if ($fk_usuario != '0') {
    $flusuario = " AND tr_ventas.fk_usuario = '$fk_usuario'";
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

if ($categoria != 0) {
    $flcategoria = " AND ct_productos.fk_categoria = $categoria";
}

if ($agrupar != 0) {
    $flserie = " GROUP_CONCAT(tr_ventas_detalle.serie SEPARATOR ', ') as serie";
    $flsum = "SUM(tr_ventas_detalle.cantidad) as cantidad";
    $orsum = "SUM(rt_ordenes_detalle.cantidad) as cantidad";
    $flagrupar = " GROUP BY tr_ventas_detalle.fk_producto, tr_ventas_detalle.fk_venta";
    $oragrupar = " GROUP BY rt_ordenes_detalle.clave, rt_ordenes_detalle.fk_orden_registro";
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

if ($tipo_venta == 0) {
    $tipo_venta_nom = "Ambos";
} else if ($tipo_venta == 1) {
    $tipo_venta_nom = "Ventas";
} else {
    $tipo_venta_nom = "Órdenes";
}
#endregion



//SUCURSAL
#region
if ($sucursal != 0) {

    $qsucursal = "SELECT * FROM ct_sucursales WHERE pk_sucursal = $sucursal";

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



//CLIENTE
#region
if ($cliente != 0) {

    $eusuario = "SELECT * FROM ct_clientes WHERE pk_cliente = $cliente";

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
    $qpago = "SELECT * FROM ct_pagos WHERE pk_pago = $pago";

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



$pdf = new PDF_Invoice('P', 'mm', array(500, 400));
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

$nivel == 1 ? $col_size_utilidad = '"Utilidad"      => 22' : $col_size_utilidad = '';
$nivel == 1 ? $col_align_utilidad = '"Utilidad"      => "R"' : $col_align_utilidad = '';


//COLS
#region
if ($nivel == 1) {
    $cols = array(
        "IdVenta"    => 15,
        "Fecha"  => 30,
        "Clave"      => 40,
        "Producto"      => 65,
        "Cantidad"      => 20,
        "Precio"      => 22,
        "Descuento"      => 22,
        "Importe"      => 22,
        "Utilidad"    => 22,
        "Sucursal"      => 38,
        "Cliente"      => 38,
        "Pago"      => 29,
        "Vendedor"      => 25
    );
    $pdf->addColsInventario($cols, 45);

    $cols = array(
        "IdVenta"    => "C",
        "Fecha"  => "C",
        "Clave"      => "C",
        "Producto"      => "C",
        "Cantidad"      => "C",
        "Precio"      => "R",
        "Descuento"      => "R",
        "Importe"      => "R",
        "Utilidad"   => "R",
        "Sucursal"      => "C",
        "Cliente"      => "C",
        "Pago"      => "C",
        "Vendedor"      => "C"
    );
} else {
    $cols = array(
        "IdVenta"    => 15,
        "Fecha"  => 30,
        "Clave"      => 40,
        "Producto"      => 65,
        "Cantidad"      => 20,
        "Precio"      => 22,
        "Descuento"      => 22,
        "Importe"      => 22,
        "Sucursal"      => 38,
        "Cliente"      => 38,
        "Pago"      => 29,
        "Vendedor"      => 25
    );
    $pdf->addColsInventario($cols, 45);

    $cols = array(
        "IdVenta"    => "C",
        "Fecha"  => "C",
        "Clave"      => "C",
        "Producto"      => "C",
        "Cantidad"      => "C",
        "Precio"      => "R",
        "Descuento"      => "R",
        "Importe"      => "R",
        "Sucursal"      => "C",
        "Cliente"      => "C",
        "Pago"      => "C",
        "Vendedor"      => "C"
    );
}
#endregion

$pdf->addLineFormat($cols);
$pdf->addLineFormat($cols);

$y    = 55;


//$total_final = 0.00;
$importe_total = 0.00;
$utilidad_total = 0.00;
$id_venta = null;
$productos = 0;


//VENTAS
if ($tipo_venta == 0 || $tipo_venta == 1) {

    $qproductos = "SELECT tr_ventas.pk_venta,
        tr_ventas.fk_usuario,
        ct_sucursales.nombre as sucursal,
        ct_clientes.nombre as cliente,
        tr_ventas.fecha,
        tr_ventas.hora,
        ct_productos.codigobarras,
        ct_productos.nombre as producto,
        ct_productos.costo as costo,
        $flsum,
        tr_ventas_detalle.unitario,
        tr_ventas_detalle.total
        FROM tr_ventas, tr_ventas_detalle, ct_productos, ct_sucursales, ct_clientes
        WHERE tr_ventas.fecha BETWEEN '$inicio' AND '$fin'
        AND tr_ventas_detalle.fk_venta = tr_ventas.pk_venta
        AND tr_ventas.tipo IN(1,2)
        AND tr_ventas.estatus = 1
        AND tr_ventas_detalle.estado = 1
        AND ct_productos.pk_producto = tr_ventas_detalle.fk_producto
        AND ct_sucursales.pk_sucursal = tr_ventas.fk_sucursal
        AND ct_clientes.pk_cliente = tr_ventas.fk_cliente$flsucursal $flcliente $flusuario $flproducto $flcategoria $flpago $flagrupar";

    if (!$rproductos = $mysqli->query($qproductos)) {
        echo "Lo sentimos, esta aplicación está experimentando problemas.";
        exit;
    }

    $paso = 0;

    while ($row = $rproductos->fetch_assoc()) {

        //DESCUENTO
        #region
        if ($row['pk_venta'] != $id_venta) {
            $descuento = number_format($row['descuento'], 2);
            $id_venta = $row['pk_venta'];
        } else {
            $descuento = number_format(0, 2);
        }
        #endregion

        $importe = $row['cantidad'] * $row['unitario'];
        $costo_total = $row['cantidad'] * $row['costo'];
        $utilidad = ($importe + $descuento) - $costo_total;

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


        if ($nivel == 1) {
            $line = array(
                "IdVenta"    => $row["pk_venta"],
                "Fecha"  => $row["fecha"] . " " . $row["hora"],
                "Clave"  => $row["codigobarras"],
                "Producto" => $row["producto"],
                "Cantidad" => $row["cantidad"],
                "Precio" => "$" . number_format($row['unitario'], 2),
                "Descuento" => "$" . number_format($descuento, 2),
                "Importe" => "$" . number_format($importe, 2),
                "Utilidad" => "$" . number_format($utilidad, 2),
                "Sucursal" => $row["sucursal"],
                "Cliente" => $row["cliente"],
                "Pago" => $npago,
                "Vendedor" => $row["fk_usuario"],
            );
        } else {
            $line = array(
                "IdVenta"    => $row["pk_venta"],
                "Fecha"  => $row["fecha"] . " " . $row["hora"],
                "Clave"  => $row["codigobarras"],
                "Producto" => $row["producto"],
                "Cantidad" => $row["cantidad"],
                "Precio" => "$" . number_format($row['unitario'], 2),
                "Descuento" => "$" . number_format($descuento, 2),
                "Importe" => "$" . number_format($importe, 2),
                "Sucursal" => $row["sucursal"],
                "Cliente" => $row["cliente"],
                "Pago" => $npago,
                "Vendedor" => $row["fk_usuario"],
            );
        }

        $size = $pdf->addLine($y, $line);
        $y   += $size + 2;

        $paso += 2;

        if ($paso == 72) {
            $pdf->AddPage();
            $y = 30;

            //HEADERS
            #region
            if ($nivel == 1) {
                $cols = array(
                    "IdVenta"    => 15,
                    "Fecha"  => 30,
                    "Clave"      => 40,
                    "Producto"      => 65,
                    "Cantidad"      => 20,
                    "Precio"      => 22,
                    "Descuento"      => 22,
                    "Importe"      => 22,
                    "Utilidad"    => 22,
                    "Sucursal"      => 38,
                    "Cliente"      => 38,
                    "Pago"      => 29,
                    "Vendedor"      => 25
                );
            } else {
                $cols = array(
                    "IdVenta"    => 15,
                    "Fecha"  => 30,
                    "Clave"      => 40,
                    "Producto"      => 65,
                    "Cantidad"      => 20,
                    "Precio"      => 22,
                    "Descuento"      => 22,
                    "Importe"      => 22,
                    "Sucursal"      => 38,
                    "Cliente"      => 38,
                    "Pago"      => 29,
                    "Vendedor"      => 25
                );
            }
            $pdf->addColsRetiro($cols, $y - 10);
            #endregion

            //COLS
            #region
            if ($nivel == 1) {
                $cols = array(
                    "IdVenta"    => "C",
                    "Fecha"  => "C",
                    "Clave"      => "C",
                    "Producto"      => "C",
                    "Cantidad"      => "C",
                    "Precio"      => "R",
                    "Descuento"      => "R",
                    "Importe"      => "R",
                    "Utilidad"   => "R",
                    "Sucursal"      => "C",
                    "Cliente"      => "C",
                    "Pago"      => "C",
                    "Vendedor"      => "C"
                );
            } else {
                $cols = array(
                    "IdVenta"    => "C",
                    "Fecha"  => "C",
                    "Clave"      => "C",
                    "Producto"      => "C",
                    "Cantidad"      => "C",
                    "Precio"      => "R",
                    "Descuento"      => "R",
                    "Importe"      => "R",
                    "Sucursal"      => "C",
                    "Cliente"      => "C",
                    "Pago"      => "C",
                    "Vendedor"      => "C"
                );
            }
            $pdf->addLineFormat($cols);
            $pdf->addLineFormat($cols);
            #endregion

            $paso = 0;
        }

        $importe_total += $importe;
        $utilidad_total += $utilidad;
        $productos += $row['cantidad'];
    }
}



$pdf->addCantidades($productos, 125, 468, 20);
$pdf->addCantidades("$" . $importe_total, 190, 468, 22);
if ($nivel == 1) {
    $pdf->addCantidades("$" . $utilidad_total, 212, 468, 22);
}


$ny = $y + 9;
$ny = $ny + 9;


$pdf->Output();
