<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");



if (isset($_GET['pk_venta']) && is_numeric($_GET['pk_venta'])) {
    $pk_venta = (int)$_GET['pk_venta'];
}


$qventas = "SELECT tr_ventas_detalle.*,
    ct_productos.codigobarras,
	ct_productos.clave,
    ct_productos.nombre,
    SUM(tr_ventas_detalle.faltante) as cantidad_faltante,
    SUM(tr_ventas_detalle.total) as total_faltante
    FROM tr_ventas_detalle, ct_productos
    WHERE tr_ventas_detalle.fk_venta = $pk_venta
    AND tr_ventas_detalle.devuelto = 0
    AND ct_productos.pk_producto = tr_ventas_detalle.fk_producto
    GROUP BY tr_ventas_detalle.fk_producto, tr_ventas_detalle.serie";


if (!$rventas = $mysqli->query($qventas)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}



echo <<<HTML
    <table id='dtDetalles' class="table table-striped">
        <thead>
            <tr>
                <th>Clave</th>
                <th>Producto</th>
                <th>Serie</th>
                <th>Cantidad</th>
                <th>Precio</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
HTML;

$productos = 0;
$totalf = 0;


while ($row = $rventas->fetch_assoc()) {

    $total = number_format($row["total_faltante"], 2);
    $productos += $row["cantidad_faltante"];
    $totalf += $row["total_faltante"];


    echo <<<HTML
        <tr class='odd gradeX' data-id='$row[codigobarras]*-*$row[serie]' data-ventad='$row[pk_venta_detalle]'>
            <td>
                $row[clave]
            </td>
            <td>
                $row[nombre]
            </td>
            <td>
                $row[serie]
            </td>
            <td>
                $row[cantidad_faltante]
            </td>
            <td>
                <input type='number' class='form-control input-precio' min='0' value='$row[unitario]'>
            </td>
            <td>
                $$total
            </td>
        </tr>
    HTML;
}

$totalf = number_format($totalf, 2);

echo <<<HTML
        </tbody>
        <tfoot>
            <tr style='background-color: #A8F991;'>
                <td colspan="3"></td>
                <td style='font-weight:bold;' id='detalles_productos'>$productos</td>
                <td></td>
                <td style='font-weight:bold;' id='detalles_total'>$$totalf</td>
            </tr>
        </tfoot>
    </table><div class="row">&nbsp;</div>
HTML;
