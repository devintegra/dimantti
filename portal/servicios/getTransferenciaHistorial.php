<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");


if (isset($_GET['pk_transferencia']) && is_numeric($_GET['pk_transferencia'])) {
    $pk_transferencia = (int)$_GET['pk_transferencia'];
}


//GENERALES
#region
$qdatos = "SELECT * FROM tr_transferencias WHERE pk_transferencia = $pk_transferencia";

if (!$rdatos = $mysqli->query($qdatos)) {
    echo "<br>Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

$datos = $rdatos->fetch_assoc();
$usuario = $datos["fk_usuario"];
$fecha = $datos["fecha"];
$hora = $datos["hora"];
#endregion


//TRANSFERENCIAS
#region
$qtransferencias = "SELECT tr_movimientos.*,
    ct_productos.clave,
    ct_productos.nombre as producto
    FROM tr_movimientos, ct_productos
    WHERE tr_movimientos.fk_movimiento_detalle = $pk_transferencia
    AND tr_movimientos.fk_producto = ct_productos.pk_producto
    AND tr_movimientos.fk_movimiento = 2
    AND tr_movimientos.estado = 1";

if (!$rtransferencias = $mysqli->query($qtransferencias)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.1";
    exit;
}

echo "
    <p class='badge-primary-integra d-flex justify-content-center align-items-center gap-2 my-2 fs-6'> <i class='bx bx-time-five'></i> $fecha $hora</p>
    <div class='row d-flex justify-content-between align-items-center mt-4'>
        <div class='d-flex justify-content-start align-items-center gap-2' style='color: #9032bb;'>
            <i class='bx bxs-right-top-arrow-circle fs-2'></i>
            <h4>Registro de transferencia</h4>
            <p>registrado por $usuario</p>
        </div>
    </div>
";

echo "<table id='dtDetalles' class=\"table table-striped\">
        <thead class='table-info'>
            <tr>
                <th>Clave</th>
                <th>Producto</th>
                <th>Serie</th>
                <th>Cantidad</th>
                <th>Precio</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>";

$productos = 0;
$totalf = 0;
$unitario = 0;

while ($row = $rtransferencias->fetch_assoc()) {

    $total = number_format($row["total"], 2);
    $productos += $row["cantidad"];
    $totalf += $row["total"];
    $unitario = number_format(($row['total'] / $row['cantidad']), 2);

    echo "<tr class='odd gradeX'>
            <td>$row[clave]</td>
            <td style='white-space: normal'>$row[producto]</td>
            <td>$row[serie]</td>
            <td>$row[cantidad]</td>
            <td>$unitario</td>
            <td>$$total</td>
        </tr>";
}

$totalf = number_format($totalf, 2);

echo "</tbody>
    <tfoot>
        <tr style='background-color: #A8F991;'>
                <td></td>
                <td></td>
                <td></td>
                <td style='font-weight:bold;'>$productos</td>
                <td></td>
                <td style='font-weight:bold;'>$$totalf</td>
            </tr>
    </tfoot>
    </table><div class=\"row\">&nbsp;</div>";
#endregion



//RECIBIDOS
#region
$qrecibidos = "SELECT tr_movimientos.*,
    ct_productos.clave,
    ct_productos.nombre as producto
    FROM tr_movimientos, ct_productos
    WHERE tr_movimientos.fk_movimiento_detalle = $pk_transferencia
    AND tr_movimientos.fk_producto = ct_productos.pk_producto
    AND tr_movimientos.fk_movimiento = 9
    AND tr_movimientos.estado = 1";

if (!$rrecibidos = $mysqli->query($qrecibidos)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.2";
    exit;
}

if ($rrecibidos->num_rows > 0) {
    echo "
        <div class='row d-flex justify-content-between align-items-center mt-4'>
            <div class='d-flex justify-content-start align-items-center gap-2' style='color: #16A34A;'>
                <i class='bx bxs-right-down-arrow-circle fs-2'></i>
                <h4>Productos recibidos</h4>
            </div>
        </div>
    ";

    echo "<table id='dtRecibidos' class=\"table table-striped\">
            <thead class='table-success'>
                <tr>
                    <th>Fecha</th>
                    <th>Recibió</th>
                    <th>Clave</th>
                    <th>Producto</th>
                    <th>Serie</th>
                    <th>Cantidad</th>
                    <th>Precio</th>
                    <th>Observaciones</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>";

    $productos = 0;
    $totalf = 0;
    $unitario = 0;

    while ($row = $rrecibidos->fetch_assoc()) {

        $total = number_format($row["total"], 2);
        $productos += $row["cantidad"];
        $totalf += $row["total"];
        $unitario = number_format(($row['total'] / $row['cantidad']), 2);

        echo "<tr class='odd gradeX'>
                <td>$row[fecha_creacion]</td>
                <td>$row[fk_usuario]</td>
                <td>$row[clave]</td>
                <td style='white-space: normal'>$row[producto]</td>
                <td>$row[serie]</td>
                <td>$row[cantidad]</td>
                <td>$unitario</td>
                <td style='white-space: normal'>$row[observaciones]</td>
                <td>$$total</td>
            </tr>";
    }

    $totalf = number_format($totalf, 2);

    echo "</tbody>
        <tfoot>
            <tr style='background-color: #A8F991;'>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td style='font-weight:bold;'>$productos</td>
                    <td></td>
                    <td></td>
                    <td style='font-weight:bold;'>$$totalf</td>
                </tr>
        </tfoot>
        </table><div class=\"row\">&nbsp;</div>";
}

#endregion



//DEVUELTOS
#region
$qdevueltos = "SELECT tr_movimientos.*,
    ct_productos.clave,
    ct_productos.nombre as producto
    FROM tr_movimientos, ct_productos
    WHERE tr_movimientos.fk_movimiento_detalle = $pk_transferencia
    AND tr_movimientos.fk_producto = ct_productos.pk_producto
    AND tr_movimientos.fk_movimiento = 10
    AND tr_movimientos.estado = 1";

if (!$rdevueltos = $mysqli->query($qdevueltos)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.3";
    exit;
}

if ($rdevueltos->num_rows > 0) {
    echo "
        <div class='row d-flex justify-content-between align-items-center mt-4'>
            <div class='d-flex justify-content-start align-items-center gap-2' style='color: #DC2626;'>
                <i class='bx bxs-left-top-arrow-circle fs-2'></i>
                <h4>Productos devueltos</h4>
            </div>
        </div>
    ";

    echo "<table id='dtDevueltos' class=\"table table-striped\">
            <thead class='table-danger'>
                <tr>
                    <th>Fecha</th>
                    <th>Recibió</th>
                    <th>Clave</th>
                    <th>Producto</th>
                    <th>Serie</th>
                    <th>Cantidad</th>
                    <th>Precio</th>
                    <th>Observaciones</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>";

    $productos = 0;
    $totalf = 0;
    $unitario = 0;

    while ($row = $rdevueltos->fetch_assoc()) {

        $total = number_format($row["total"], 2);
        $productos += $row["cantidad"];
        $totalf += $row["total"];
        $unitario = number_format(($row['total'] / $row['cantidad']), 2);

        echo "<tr class='odd gradeX'>
                <td>$row[fecha_creacion]</td>
                <td>$row[fk_usuario]</td>
                <td>$row[clave]</td>
                <td style='white-space: normal'>$row[producto]</td>
                <td>$row[serie]</td>
                <td>$row[cantidad]</td>
                <td>$unitario</td>
                <td style='white-space: normal'>$row[observaciones]</td>
                <td>$$total</td>
            </tr>";
    }

    $totalf = number_format($totalf, 2);

    echo "</tbody>
        <tfoot>
            <tr style='background-color: #A8F991;'>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td style='font-weight:bold;'>$productos</td>
                    <td></td>
                    <td></td>
                    <td style='font-weight:bold;'>$$totalf</td>
                </tr>
        </tfoot>
        </table><div class=\"row\">&nbsp;</div>";
}
#endregion
