<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
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

if (isset($_GET['usuario']) && is_string($_GET['usuario'])) {
    $fk_usuario = $_GET['usuario'];
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

if (isset($_GET['clave']) && is_string($_GET['clave'])) {
    $clave = $_GET['clave'];
}

if (isset($_GET['tipo']) && is_numeric($_GET['tipo'])) {
    $tipo_venta = $_GET['tipo'];
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

if ($fk_usuario != '0') {
    $flusuario = " AND tr_ventas.fk_usuario = '$fk_usuario'";
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
#endregion

$nivel == 1 ? $th_utilidad = '<th>Utilidad</th>' : $th_utilidad = '';

echo <<<HTML
    <table id='dtEmpresa' class='table table-striped'>
        <thead>
            <tr>
                <th>ID Venta</th>
                <th>Fecha</th>
                <th>Clave</th>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio</th>
                <th>Descuento</th>
                <th>Importe</th>
                $th_utilidad
                <th>Sucursal</th>
                <th>Cliente</th>
                <th>Forma de pago</th>
                <th>Vendedor</th>
            </tr>
        </thead>
        <tbody>
HTML;


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
        AND tr_ventas.tipo IN(1,2,3,4)
        AND tr_ventas_detalle.estado = 1
        AND tr_ventas.estatus = 1
        AND ct_productos.pk_producto = tr_ventas_detalle.fk_producto
        AND ct_sucursales.pk_sucursal = tr_ventas.fk_sucursal
        AND ct_clientes.pk_cliente = tr_ventas.fk_cliente$flsucursal $flcliente $flusuario $flproducto $flcategoria $flpago $flagrupar";

    if (!$rproductos = $mysqli->query($qproductos)) {
        echo "Lo sentimos, esta aplicación está experimentando problemas.";
        exit;
    }

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

        $utilidadf = number_format($utilidad, 2);
        $nivel == 1 ? $td_utilidad = "<td><p class='badge-success-integra'>$$utilidadf</p></td>" : $td_utilidad = "";

        echo <<< HTML
            <tr class='odd gradeX'>
                <td>$row[pk_venta]</td>
                <td style='white-space: normal;'>$row[fecha] $row[hora]</td>
                <td>$row[codigobarras]</td>
                <td style='white-space: normal;'>$row[producto]</td>
                <td>$row[cantidad]</td>
                <td>$$row[unitario]</td>
                <td>$descuento</td>
                <td><p class='badge-primary-integra'>$$importe</p></td>
                $td_utilidad
                <td>$row[sucursal]</td>
                <td style='white-space: normal;'>$row[cliente]</td>
                <td>$npago</td>
                <td>$row[fk_usuario]</td>
            </tr>
        HTML;

        $importe_total += $importe;
        $utilidad_total += $utilidad;
        $productos += $row['cantidad'];
    }
}




$productos = number_format($productos, 2);
$importe_total = number_format($importe_total, 2);
$utilidad_total = number_format($utilidad_total, 2);

$nivel == 1 ? $ft_utilidad = "<td style='font-weight: bold; background-color: #A8F991; font-size: 16px; font-weight: bold;'>$$utilidad_total</td>" : $ft_utilidad = "";

echo <<<HTML
        </tbody>
        <tfoot>
            <tr>
                <td style='font-size: 16px; font-weight: bold;'>TOTALES</td>
                <td></td>
                <td></td>
                <td></td>
                <td style='font-weight: bold; background-color: #bec8d6; font-size: 16px; font-weight: bold;'>$productos</td>
                <td></td>
                <td></td>
                <td style='font-weight: bold; background-color: #BFDBFE; font-size: 16px; font-weight: bold;'>$$importe_total</td>
                $ft_utilidad
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </tfoot>
    </table>
HTML;
