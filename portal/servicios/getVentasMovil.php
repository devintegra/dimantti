<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
mysqli_set_charset($mysqli, 'utf8');
$codigo = 200;
$descripcion = "";
$data = array();


if (isset($_GET['start']) && is_numeric($_GET['start'])) {
    $start = (int)$_GET['start'];
}

if (isset($_GET['length']) && is_numeric($_GET['length'])) {
    $length = (int)$_GET['length'];
}

$search_value = $_GET['search']['value'];

$column_search = [];
foreach ($_GET['columns'] as $index => $column) {
    $column_search[$index] = $column['search']['value'];
}

if (isset($_GET['nivel']) && is_numeric($_GET['nivel'])) {
    $nivel = (int)$_GET['nivel'];
}



//FILTROS
#region
//$nivel == 3 || $nivel == 4 ? $flusuario = " AND trv.fk_usuario = '$fk_usuario'" : $flusuario = "";

if ($search_value != '') {
    $flsearch = " AND (tr_ventas.fecha LIKE '%$search_value%' OR tr_ventas.folio LIKE '%$search_value%' OR ct_clientes.nombre LIKE '%$search_value%' OR ct_rutas.nombre LIKE '%$search_value%' OR tr_ventas.fk_usuario LIKE '%$search_value%' OR tr_ventas.observaciones LIKE '%$search_value%' OR tr_ventas.total LIKE '%$search_value%')";
}

if (!empty($column_search[0])) {
    $flventa = " AND tr_ventas.pk_venta LIKE '%" . $column_search[0] . "%'";
}

if (!empty($column_search[1])) {
    $flfolio = " AND tr_ventas.folio LIKE '%" . $column_search[1] . "%'";
}

if (!empty($column_search[2])) {
    $flfecha = " AND tr_ventas.fecha LIKE '%" . $column_search[2] . "%'";
}

if (!empty($column_search[3])) {
    $flsucursalnom = " AND ct_clientes.nombre LIKE '%" . $column_search[3] . "%'";
}

if (!empty($column_search[4])) {
    $flcliente = " AND ct_rutas.nombre LIKE '%" . $column_search[4] . "%'";
}

if (!empty($column_search[5])) {
    $flusuario = " AND tr_ventas.fk_usuario LIKE '%" . $column_search[5] . "%'";
}

if (!empty($column_search[9])) {
    $flobservaciones = " AND tr_ventas.observaciones LIKE '%" . $column_search[9] . "%'";
}

if (!empty($column_search[10])) {
    $fltotal = " AND tr_ventas.subtotal LIKE '%" . $column_search[10] . "%'";
}
#endregion


$qregistros = "SELECT tr_ventas.pk_venta as pk_venta,
        tr_ventas.tipo as tipo,
        tr_ventas.folio as folio,
        tr_ventas.fecha as fecha,
        tr_ventas.hora,
        tr_ventas.fk_sucursal,
        tr_ventas.fk_almacen,
        ct_rutas.nombre as ruta,
        tr_ventas.fk_usuario,
        ct_clientes.nombre as cliente,
        ct_clientes.telefono as telefono,
        ct_clientes.dias_credito,
        ct_clientes.correo,
        tr_ventas.fk_factura,
        tr_ventas.subtotal as total,
        tr_ventas.anticipo,
        tr_ventas.saldo,
        tr_ventas.tipo_pago,
        tr_ventas.descuento as descuento,
        tr_ventas.modificada,
        tr_ventas.observaciones,
        tr_ventas.estatus
        FROM tr_ventas
        LEFT JOIN ct_clientes ON ct_clientes.pk_cliente = tr_ventas.fk_cliente
        LEFT JOIN ct_rutas ON ct_rutas.pk_ruta = tr_ventas.fk_ruta
        WHERE tr_ventas.tipo = 2
        AND tr_ventas.subtotal > 0
    $flsucursal $flsearch $flventa $flfolio $flfecha $flsucursalnom $flcliente $flusuario $flobservaciones $fltotal
    ORDER BY tr_ventas.pk_venta DESC
    LIMIT $start, $length";


if (!$rregistros = $mysqli->query($qregistros)) {
    $codigo = 201;
    $descripcion = "Error al obtener los registros";
    exit;
}

$totalVentasVisible = 0;

while ($row = $rregistros->fetch_assoc()) {

    $total = number_format($row["total"], 2);
    $totalVentasVisible += $row['total'];
    $anticipo = number_format($row['anticipo'], 2);
    $fecha = $row['fecha'] . ' ' . $row['hora'];
    $cliente = $row['cliente'] . ' ' . $row['telefono'];

    //TIPO DE VENTA
    #region
    switch ($row['tipo']) {
        case 1:
            $origen = "<p class='badge-primary-integra'>Punto de venta</p>";
            break;
        case 2:
            $origen = "<p class='badge-success-integra'>Móvil</p>";
            break;
        case 3:
            $origen = "<p class='badge-orange-integra'>Desde prestamo</p>";
            break;
        case 4:
            $origen = "<p class='badge-purple-integra'>Cotización</p>";
            break;
        case 5:
            $origen = "<p class='badge-warning-integra'>Reparación</p>";
            break;
    }
    #endregion


    //TIPO DE PAGO
    #region
    $badge_tipo_pago = '';
    if ($row['tipo_pago'] == 3) {
        $badge_tipo_pago = "<p class='badge-primary-integra'>A crédito</p>";

        $dias_credito = $row["dias_credito"];
        $fecha_a_vencer = date('Y-m-d', strtotime($row['fecha'] . ' + ' . $dias_credito . ' days'));
        $fecha_actual = date('Y-m-d');

        if ($row['saldo'] > 0 && ($fecha_a_vencer < $fecha_actual)) {
            $badge_tipo_pago = "<p class='badge-danger-integra'>Crédito vencido</p>";
        }
    }
    #endregion


    //ESTATUS
    #region
    $badge_estatus = "";
    if ($row['estatus'] == 1) {
        $badge_estatus = "<p class='badge-success-integra'>Generada</p>";
    } else if ($row['estatus'] == 2) {
        $badge_estatus = "<p class='badge-warning-integra'>Devuelta</p>";
    } else if ($row['estatus'] == 3) {
        $badge_estatus = "<p class='badge-danger-integra'>Cancelada</p>";
    }
    #endregion


    //BOTONES
    #region
    $btn_acciones = "<a target='_blank' href='ventaPDF.php?id=$row[pk_venta]' class='btn-reabrir-dast' title='Detalles'><i class='fa fa-file-pdf-o'></i></a>";

    $btn_acciones = $btn_acciones . "<a target='_blank' href='abonosVentaPDF.php?id=$row[pk_venta]' class='btn-iniciar-dast' title='Abonos'><i class='fa fa-file-pdf-o'></i></a>";

    $badge_factura = "";
    if ($row['fk_factura'] == null || $row['fk_factura'] == '') {
        if ($row['estatus'] < 2) {
            $btn_acciones = $btn_acciones . "
                <button type='button' class='btn-editar-dast facturarVenta' title='Facturar venta' data-id='$row[pk_venta]'>
                    <i class='fa fa-sticky-note-o mx-2'></i>
                </button>";
        }
    } else {
        $badge_factura = "<p class='badge-orange-integra'>Facturado</p>";
        $btn_acciones = $btn_acciones . "
            <button type='button' class='btn-entregar-dast enviar_factura' title='Enviar por correo' data-uuid='$row[fk_factura]' data-correo='$row[correo]'>
                <i class='fa fa-envelope mx-2'></i>
            </button>";
    }

    if ($row["saldo"] > 0 && ($nivel == 1 || $nivel == 4)) {
        $btn_acciones = $btn_acciones . "
            <button type='button' class='btn-entregar-dast btnSaldarVenta' data-id='$row[pk_venta]' title='Saldo pendiente ($$row[saldo])'>
                <i class='fa fa-money mx-2'></i>
            </button>";
    }
    #endregion


    $estatus = $badge_estatus . $badge_tipo_pago . $badge_factura;

    $totald = <<<HTML
        <p class='badge-success-integra'>$$total</p>
        <input type="hidden" class="form-control" value="$anticipo">
    HTML;

    $data[] = array(
        "pk_venta" => (int)$row['pk_venta'],
        "#" => (int)$row['pk_venta'],
        "folio" => $row['folio'],
        "fecha" => $fecha,
        "ruta" => $row["ruta"],
        "cliente" => $cliente,
        "vendedor" => $row['fk_usuario'],
        "origen" => $origen,
        "estatus" => $estatus,
        "acciones" => $btn_acciones,
        "observaciones" => $row['observaciones'],
        "total" => $totald
    );
}



//REGISTROS TOTALES
#region
$qtotal = "SELECT COUNT(*) AS total FROM tr_ventas WHERE tipo = 2";

if (!$rtotal = $mysqli->query($qtotal)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. Error al obtener los registros totales";
    exit;
}

$total = $rtotal->fetch_assoc();
$total_records = $total["total"];
#endregion




//REGISTROS FILTRADOS
#region
$qfiltrados = "SELECT COUNT(*) AS total, SUM(total) AS totalVentas FROM tr_ventas WHERE tipo = 2 AND estado = 1";

if (!$rfiltrados = $mysqli->query($qfiltrados)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. Error al obtener los registros filtrados";
    exit;
}

$filtrados = $rfiltrados->fetch_assoc();
$filtered_records = $filtrados["total"];
$totalVentas = $filtrados["totalVentas"];
#endregion




echo json_encode(array(
    "draw" => intval($_GET['draw']),
    "recordsTotal" => $total_records, // Total de registros en la tabla (sin paginación)
    "recordsFiltered" => $filtered_records, // Total de registros después de aplicar filtros (en este caso, igual que el total)
    "data" => $data,
    "columns" => $column_search,
    "totalVentasVisible" => $totalVentasVisible,
    "totalVentas" => $totalVentas
));
