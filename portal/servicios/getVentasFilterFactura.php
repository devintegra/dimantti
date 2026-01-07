<?php
header('Access-Control-Allow-Origin: *');
include('conexioni.php');
mysqli_set_charset($mysqli, 'utf8');
$codigo = 200;
$descripcion = "";


if (isset($_GET['inicio']) && is_string($_GET['inicio'])) {
    $inicio = $_GET['inicio'];
}

if (isset($_GET['fin']) && is_string($_GET['fin'])) {
    $fin = $_GET['fin'];
}

if (isset($_GET['fk_sucursal']) && is_numeric($_GET['fk_sucursal'])) {
    $fk_sucursal = (int)$_GET['fk_sucursal'];
}

if (isset($_GET['fk_vendedor']) && is_string($_GET['fk_vendedor'])) {
    $fk_vendedor = $_GET['fk_vendedor'];
}

if (isset($_GET['fk_pago']) && is_numeric($_GET['fk_pago'])) {
    $fk_pago = (int)$_GET['fk_pago'];
}

if (isset($_GET['fk_cliente']) && is_numeric($_GET['fk_cliente'])) {
    $fk_cliente = (int)$_GET['fk_cliente'];
}


//FILTROS
#region
$flfechas = '';
$flsucursal = '';
$flvendedor = '';
$flpago = '';
$flcliente = '';

if ($inicio != "" && $fin != "") {
    $flfechas = " AND tr_ventas.fecha BETWEEN '$inicio' AND '$fin'";
}

if ($fk_sucursal != 0) {
    $flsucursal = " AND tr_ventas.fk_sucursal = $fk_sucursal";
}

if ($fk_cliente != 0) {
    $flcliente = " AND tr_ventas.fk_cliente = $fk_cliente";
}

if ($fk_vendedor != '0') {
    $flvendedor = " AND tr_ventas.fk_usuario = '$fk_vendedor'";
}

if ($fk_pago != 0) {
    switch ($fk_pago) {
        case 1:
            $flpago = " AND tr_ventas.efectivo > 0";
            break;
        case 2:
            $flpago = " AND tr_ventas.transferencia > 0";
            break;
        case 3:
            $flpago = " AND tr_ventas.debito > 0";
            break;
        case 4:
            $flpago = " AND tr_ventas.cheque > 0";
            break;
        case 5:
            $flpago = " AND tr_ventas.credito > 0";
            break;
    }
}
#endregion



//VENTAS
#region
$qventas = "SELECT tr_ventas.*,
    ct_sucursales.nombre as sucursal,
    ct_clientes.nombre as cliente,
    (SELECT CONCAT(
        CASE WHEN tr_ventas.efectivo > 0 THEN 'Efectivo.' ELSE '' END,
        CASE WHEN tr_ventas.credito > 0 THEN 'Crédito. ' ELSE '' END,
        CASE WHEN tr_ventas.debito > 0 THEN 'Debito. ' ELSE '' END,
        CASE WHEN tr_ventas.cheque > 0 THEN 'Cheque. ' ELSE '' END,
        CASE WHEN tr_ventas.transferencia > 0 THEN 'Tran. ' ELSE '' END,
        CASE WHEN tr_ventas.efectivo = 0 AND
                      tr_ventas.credito = 0 AND
                      tr_ventas.debito = 0 AND
                      tr_ventas.cheque = 0 AND
                      tr_ventas.transferencia = 0
                 THEN 'Venta a crédito' ELSE '' END
    )) AS metodos_pago
    FROM tr_ventas, ct_sucursales, ct_clientes
    WHERE tr_ventas.estado = 1
    AND tr_ventas.fk_factura IS NULL
    AND ct_sucursales.pk_sucursal = tr_ventas.fk_sucursal
    AND ct_clientes.pk_cliente = tr_ventas.fk_cliente$flfechas$flsucursal$flvendedor$flcliente$flpago";


if (!$rventas = $mysqli->query($qventas)) {
    $descripcion = "Error al obtener las ventas";
    echo "Lo sentimos, la aplicación está experimentando problemas. Error al obtener las ventas";
    exit;
}

if (mysqli_num_rows($rventas) > 0) {
    while ($row = mysqli_fetch_assoc($rventas)) {
        $elementos[] = $row;
    }
}
#endregion





$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => $elementos);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
