<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
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

if (isset($_GET['usuario']) && is_string($_GET['usuario'])) {
    $fk_usuario = $_GET['usuario'];
}

if (isset($_GET['pago']) && is_numeric($_GET['pago'])) {
    $pago = (int)$_GET['pago'];
}



//FILTROS
#region
$flfechas = "";
$flsucursal = "";
$flcliente = "";
$flusuario = "";
$flpago = "";

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
            $metodo = "efectivo";
            break;
        case 2:
            $metodo = "transferencia";
            break;
        case 3:
            $metodo = "debito";
            break;
        case 4:
            $metodo = "cheque";
            break;
        case 5:
            $metodo = "credito";
            break;
    }

    $flpago = " AND tr_ventas.$metodo > 0";
}
#endregion


$qventas = "SELECT tr_ventas.*,
	ct_sucursales.nombre as sucursal,
    ct_clientes.nombre as cliente
    FROM tr_ventas, ct_sucursales, ct_clientes
    WHERE ct_sucursales.pk_sucursal = tr_ventas.fk_sucursal
    AND ct_clientes.pk_cliente = tr_ventas.fk_cliente$flfechas $flsucursal $flcliente $flusuario $flpago";



echo "
<table id='dtEmpresa' class='table table-striped'>
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Sucursal</th>
                                                <th>Folio</th>
                                                <th>Cliente</th>
                                                <th>Vendedor</th>
                                                <th>Estatus</th>
                                                <th>Ticket</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead><tbody>";

if (!$resultado = $mysqli->query($qventas)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

$total_final = 0.00;

while ($roweusuario = $resultado->fetch_assoc()) {

    if ($roweusuario['estatus'] == 2) {
        $estatus = "Devuelta";
        $nestatus = "badge-primary-integra";
    } else if ($roweusuario['estatus'] == 3) {
        $estatus = "Cancelada";
        $nestatus = "badge-danger-integra";
    } else {
        $estatus = "Venta";
        $nestatus = "badge-success-integra";
        //$total_final += $roweusuario['total'];
    }


    //TOTAL
    if ($pago != 0) {

        switch ($pago) {
            case 1:
                $total = number_format($roweusuario['efectivo'], 2);
                (int)$roweusuario['estatus'] == 1 ? $total_final += $roweusuario['efectivo'] : "";
                break;
            case 2:
                $total = number_format($roweusuario['transferencia'], 2);
                (int)$roweusuario['estatus'] == 1 ? $total_final += $roweusuario['transferencia'] : "";
                break;
            case 3:
                $total = number_format($roweusuario['debito'], 2);
                (int)$roweusuario['estatus'] == 1 ? $total_final += $roweusuario['debito'] : "";
                break;
            case 4:
                $total = number_format($roweusuario['cheque'], 2);
                (int)$roweusuario['estatus'] == 1 ? $total_final += $roweusuario['cheque'] : "";
                break;
            case 5:
                $total = number_format($roweusuario['credito'], 2);
                (int)$roweusuario['estatus'] == 1 ? $total_final += $roweusuario['credito'] : "";
                break;
        }
    } else {
        $total = number_format($roweusuario['total'], 2);
        (int)$roweusuario['estatus'] == 1 ? $total_final += $roweusuario['total'] : "";
    }


    echo "<tr class='odd gradeX'>
                                <td><i class='bx bx-calendar'></i>$roweusuario[fecha] $roweusuario[hora]</td>
                                <td>$roweusuario[sucursal]</td>
                                <td>$roweusuario[folio]</td>
                                <td>$roweusuario[cliente]</td>
                                <td>$roweusuario[fk_usuario]</td>
                                <td><p class='$nestatus'>$estatus</p></td>
                                <td>
                                    <a target='_blank' href='ventaPDF.php?id=$roweusuario[pk_venta]'><i class='fa fa-file-pdf-o vpdf fa-lg btn-pdf'></i></a>
                                </td>
                                <td>$$total</td>
            </tr>";
}

$total_final = number_format($total_final, 2);

echo "</tbody>
    <tfoot>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td style='font-weight: bold; background-color: #A8F991'>TOTAL:</td>
            <td style='font-weight: bold; background-color: #A8F991'>$$total_final</td>
        </tr>
    </tfoot></table>";
