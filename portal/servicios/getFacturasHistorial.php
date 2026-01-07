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

if (isset($_GET['fk_sucursal']) && is_numeric($_GET['fk_sucursal'])) {
    $fk_sucursal = (int)$_GET['fk_sucursal'];
}

if (isset($_GET['fk_usuario']) && is_string($_GET['fk_usuario'])) {
    $fk_usuario = $_GET['fk_usuario'];
}

if (isset($_GET['nivel']) && is_numeric($_GET['nivel'])) {
    $nivel = (int)$_GET['nivel'];
}



//FILTROS
#region
$fk_sucursal != 0 ? $flsucursal = " AND trv.fk_sucursal = $fk_sucursal" : $flsucursal = "";

$nivel == 3 || $nivel == 4 ? $flusuario = " AND trv.fk_usuario = '$fk_usuario'" : $flusuario = "";

if ($search_value != '') {
    $flsearch = " AND (trf.fecha LIKE '%$search_value%' OR ctc.nombre LIKE '%$search_value%' OR ctf.nombre LIKE '%$search_value%' OR trf.metodo_pago LIKE '%$search_value%')";
}

if (!empty($column_search[0])) {
    $flfecha = " AND trf.fecha LIKE '%" . $column_search[0] . "%'";
}

if (!empty($column_search[1])) {
    $flventa = " AND trv.pk_venta LIKE '%" . $column_search[1] . "%'";
}

if (!empty($column_search[2])) {
    $flcliente = " AND ctc.nombre LIKE '%" . $column_search[2] . "%'";
}

if (!empty($column_search[3])) {
    $flformapago = " AND ctf.nombre LIKE '%" . $column_search[3] . "%'";
}

if (!empty($column_search[4])) {
    $flmetodopago = " AND trf.metodo_pago LIKE '%" . $column_search[4] . "%'";
}
#endregion


$qregistros = "SELECT trf.*,
        GROUP_CONCAT(trv.pk_venta SEPARATOR ',') as ventas,
        ctc.nombre as cliente,
        ctc.correo,
        ctf.nombre as forma_pago_nombre
        FROM tr_facturas trf, ct_clientes ctc, tr_ventas trv, ct_formas_pago_sat ctf
        WHERE ctc.pk_cliente = trf.fk_cliente
        AND ctf.pk_forma = trf.forma_pago
        AND trf.pk_factura = trv.fk_factura
        AND trf.estado = 1
    $flsucursal $flsearch $flfecha $flventa $flcliente $flformapago $flmetodopago
    GROUP BY trv.fk_factura
    ORDER BY trf.fecha DESC
    LIMIT $start, $length";


if (!$rregistros = $mysqli->query($qregistros)) {
    $codigo = 201;
    $descripcion = "Error al obtener los registros";
    exit;
}


while ($row = $rregistros->fetch_assoc()) {

    $xml = base64_encode($row['factura_xml']);

    $btn_pdf = "<a href='servicios/facturas/$row[pk_factura].pdf' download><button type='button' class='btn-iniciar-dast' title='Descargar factura PDF' data-factura='$row[factura_pdf]'>
            <i class='fa fa-file-pdf-o mx-2'></i>
        </button></a>";

    $btn_xml2 = "<button type='button' class='btn-reasignar-dast' onclick='verFacturaXML(event)' title='Descargar factura XML' data-factura='$xml'>
            <i class='fa fa-file-text-o mx-2'></i>
        </button>";

    $btn_xml = "<a href='servicios/facturas_xml/$row[pk_factura].xml' download='$row[pk_factura]'><button type='button' class='btn-reasignar-dast' title='Descargar factura XML'>
            <i class='fa fa-file-text-o mx-2'></i>
        </button></a>";

    $btn_cancelar = "<button type='button' class='btn-reabrir-dast cancelar_factura' title='Cancelar factura' data-uuid='$row[pk_factura]'>
            <i class='fa fa-ban mx-2'></i>
        </button>";

    $btn_correo = "<button type='button' class='btn-entregar-dast enviar_factura' title='Enviar por correo' data-uuid='$row[pk_factura]' data-correo='$row[correo]'>
            <i class='fa fa-envelope mx-2'></i>
        </button>";


    //ESTATUS
    #region
    $badge_estatus = "<p class='badge-success-integra'>Facturada</p>";
    $btn_xml_acuse = "";
    if ($row['acuse_cancelacion']) {
        $badge_estatus = "<p class='badge-danger-integra'>Cancelada</p>";

        $xml_acuse = base64_encode($row['acuse_cancelacion']);

        $btn_xml_acuse = "<button type='button' class='btn-reasignar-dast' onclick='verAcuseXML(event)' title='Descargar acuse de cancelación XML' data-factura='$xml_acuse'>
                <i class='fa fa-file-text-o mx-2'></i>
            </button>";

        $btn_cancelar = "";
    }
    #endregion

    $btn_acciones = $btn_pdf . $btn_xml . $btn_correo . $btn_cancelar;


    $data[] = array(
        "pk_factura" => (int)$row['pk_factura'],
        "fecha" => $row['fecha'],
        "venta" => $row['ventas'],
        "cliente" => $row['cliente'],
        "forma_pago" => $row['forma_pago_nombre'],
        "metodo_pago" => $row['metodo_pago'],
        "estatus" => $badge_estatus,
        "acciones" => $btn_acciones
    );
}



//REGISTROS TOTALES
#region
$qtotal = "SELECT COUNT(*) AS total FROM tr_facturas";

if (!$rtotal = $mysqli->query($qtotal)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. Error al obtener los registros totales";
    exit;
}

$total = $rtotal->fetch_assoc();
$total_records = $total["total"];
#endregion




//REGISTROS FILTRADOS
#region
$qfiltrados = "SELECT COUNT(*) AS total FROM tr_facturas WHERE estado = 1";

if (!$rfiltrados = $mysqli->query($qfiltrados)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. Error al obtener los registros filtrados";
    exit;
}

$filtrados = $rfiltrados->fetch_assoc();
$filtered_records = $filtrados["total"];
#endregion




echo json_encode(array(
    "draw" => intval($_GET['draw']),
    "recordsTotal" => $total_records, // Total de registros en la tabla (sin paginación)
    "recordsFiltered" => $filtered_records, // Total de registros después de aplicar filtros (en este caso, igual que el total)
    "data" => $data,
    "columns" => $column_search
));
