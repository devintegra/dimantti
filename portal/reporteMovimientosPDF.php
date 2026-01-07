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

if (isset($_GET['movimiento']) && is_numeric($_GET['movimiento'])) {
    $fk_movimiento = (int)$_GET['movimiento'];
}

if (isset($_GET['producto']) && is_string($_GET['producto'])) {
    $clave = $_GET['producto'];
}


//FILTROS
#region
$flfechas = "";
$flsucursal = "";
$flmovimiento = "";
$flproducto = "";

if ($inicio != "" && $fin != "") {
    $flfechas = " AND tr_movimientos.fecha BETWEEN '$inicio' AND '$fin'";
}

if ($sucursal != 0) {
    $flsucursal = " AND tr_movimientos.fk_sucursal = $sucursal";
}

if ($fk_movimiento != 0) {
    $flmovimiento = " AND tr_movimientos.fk_movimiento = $fk_movimiento";
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



$qmovimientos = "SELECT tr_movimientos.*,
	ct_sucursales.nombre as sucursal,
    rt_sucursales_almacenes.nombre as almacen,
    ct_productos.codigobarras,
    ct_productos.nombre,
    ct_productos.descripcion
    FROM tr_movimientos, ct_sucursales, ct_productos, rt_sucursales_almacenes
    WHERE tr_movimientos.estado = 1
    AND ct_sucursales.pk_sucursal = tr_movimientos.fk_sucursal
    AND rt_sucursales_almacenes.pk_sucursal_almacen = tr_movimientos.fk_almacen
    AND ct_productos.pk_producto = tr_movimientos.fk_producto$flfechas $flsucursal $flmovimiento $flproducto";



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
    $empresa_direccion = "Traspaso entre sucursales";
    $empresa_telefono = "";
    $empresa_correo = "";
    $direccionTxt = $empresa_direccion;
}
#endregion





$pdf = new PDF_Invoice('P', 'mm', 'A3');
$pdf->AddPage();
$pdf->SetAutoPageBreak(false);

$direccionex = explode(",", $empresa_direccion);


$pdf->addSociete(
    $empresa_nombre,
    $direccionTxt .
        "Telefono: $empresa_telefono\n" .
        "Correo: $empresa_correo"
);
$pdf->addImage("servicios/logo.png", 160, 50);


$pdf->fact_dev("", "Reporte");
//$pdf->addFecha( $entrada_fecha);
$pdf->tipo_pago("" . "Movimientos");
$pdf->contacto("", $inicio . " - " . $fin);


$cols = array(
    "Movimiento"    => 22,
    "Fecha"    => 22,
    "Sucursal"  => 30,
    "Almacen"      => 30,
    "Usuario"      => 20,
    "Codigo"      => 25,
    "Producto"      => 43,
    "Serie"      => 25,
    "Cantidad"      => 18,
    "Unitario"      => 25,
    "Total"      => 25
);
$pdf->addColsInventario($cols, 45);

$cols = array(
    "Movimiento"    => "C",
    "Fecha"    => "C",
    "Sucursal"  => "C",
    "Almacen"      => "C",
    "Usuario"      => "C",
    "Codigo"      => "C",
    "Producto"      => "C",
    "Serie"      => "C",
    "Cantidad"      => "C",
    "Unitario"      => "R",
    "Total"      => "R"
);
$pdf->addLineFormat($cols);
$pdf->addLineFormat($cols);

$y    = 55;




$total_final = 0.00;


if (!$rmovimientos = $mysqli->query($qmovimientos)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.2";
    exit;
}

$paso = 0;

while ($res = $rmovimientos->fetch_assoc()) {

    //Movimiento
    #region
    $movimiento = "";
    switch ($res['fk_movimiento']) {
        case 1:
            $movimiento = "Alta almacén";
            break;
        case 2:
            $movimiento = "Transferencia";
            break;
        case 3:
            $movimiento = "Prestamo";
            break;
        case 4:
            $movimiento = "Baja";
            break;
        case 5:
            if ($roweusuario['tipo_venta'] == 1) {
                $movimiento = "Venta en mostrador";
            } else {
                $movimiento = "Venta en línea";
            }
            break;
        case 6:
            $movimiento = "Devolución";
            break;
        case 7:
            $movimiento = "Devolución de venta";
            break;
        case 8:
            $movimiento = "Devolución por cancelación de venta";
            break;
    }
    #endregion

    $unitario = $res["total"] / $res["cantidad"];

    $line = array(
        "Movimiento"    => $movimiento,
        "Fecha"    => $res["fecha"],
        "Sucursal"    => $res["sucursal"],
        "Almacen"  => $res["almacen"],
        "Usuario" => $res["fk_usuario"],
        "Codigo" => $res["codigobarras"],
        "Producto" => $res["nombre"],
        "Serie" => $res["serie"],
        "Cantidad" => $res["cantidad"],
        "Unitario" => "$" . number_format($unitario, 2),
        "Total" => "$" . number_format($res["total"], 2),
    );

    $size = $pdf->addLine($y, $line);
    $y   += $size + 2;

    $paso += 2;

    if ($paso == 66) {
        $pdf->AddPage();
        $y = 30;
        $cols = array(
            "Movimiento"    => 22,
            "Fecha"    => 22,
            "Sucursal"  => 30,
            "Almacen"      => 30,
            "Usuario"      => 20,
            "Codigo"      => 25,
            "Producto"      => 43,
            "Serie"      => 25,
            "Cantidad"      => 18,
            "Unitario"      => 25,
            "Total"      => 25
        );
        $pdf->addColsRetiro($cols, $y - 10);

        $cols = array(
            "Movimiento"    => "C",
            "Fecha"    => "C",
            "Sucursal"  => "C",
            "Almacen"      => "C",
            "Usuario"      => "C",
            "Codigo"      => "C",
            "Producto"      => "C",
            "Serie"      => "C",
            "Cantidad"      => "C",
            "Unitario"      => "R",
            "Total"      => "R"
        );
        $pdf->addLineFormat($cols);
        $pdf->addLineFormat($cols);

        $paso = 0;
    }

    $total_final += $unitario;
}
#endregion

//$pdf->addTotalGeneral("$" . $total_final, 222, 368);


$ny = $y + 9;
$ny = $ny + 9;


$pdf->Output();
