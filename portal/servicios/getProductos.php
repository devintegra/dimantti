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

if (isset($_GET['estado']) && is_numeric($_GET['estado'])) {
    $estado = (int)$_GET['estado'];
}



//FILTROS
#region
$flestado = $estado < 2 ? " WHERE p.estado = $estado" : " WHERE p.estado IN (0,1)";
$flsearch = "";
$flclave = "";
$flpresentacion = "";
$flnombre = "";
$flcategoria = "";

if ($search_value != '') {
    $flsearch = " AND (p.clave LIKE '%$search_value%' OR p.nombre LIKE '%$search_value%' OR ctp.descripcion LIKE '%$search_value%' OR ctc.nombre LIKE '%$search_value%' OR p.costo LIKE '%$search_value%' OR p.precio LIKE '%$search_value%')";
}

if (!empty($column_search[0])) {
    $flclave = " AND p.clave LIKE '%" . $column_search[0] . "%'";
}

if (!empty($column_search[1])) {
    $flnombre = " AND p.nombre LIKE '%" . $column_search[1] . "%'";
}

if (!empty($column_search[2])) {
    $flcategoria = " AND ctc.nombre LIKE '%" . $column_search[2] . "%'";
}

if (!empty($column_search[3])) {
    $flpresentacion = " AND ctp.descripcion LIKE '%" . $column_search[3] . "%'";
}
#endregion


$qproductos = "SELECT p.pk_producto,
        p.clave,
        p.nombre as producto,
        ctp.descripcion as presentacion,
        ctc.nombre as categoria,
        p.costo,
        p.precio,
        (SELECT COALESCE(SUM(faltante), 0) FROM tr_transferencias_detalle tfd WHERE tfd.fk_producto = p.pk_producto AND tfd.faltante > 0 AND tfd.estado = 1) as transferencias,
        (SELECT imagen FROM rt_imagenes_productos WHERE fk_producto = p.pk_producto AND estado = 1 LIMIT 1) as imagen,
        p.estado
    FROM ct_productos p
    LEFT JOIN ct_presentaciones ctp ON ctp.pk_presentacion = p.fk_presentacion
    LEFT JOIN ct_categorias ctc ON ctc.pk_categoria = p.fk_categoria
    $flestado $flsearch $flclave $flpresentacion $flnombre $flcategoria
    ORDER BY p.pk_producto ASC
    LIMIT $start, $length";


if (!$rproductos = $mysqli->query($qproductos)) {
    $codigo = 201;
    $descripcion = "Error al obtener los productos";
    echo "Error al obtener los productos. " . $mysqli->error;
    exit;
}


while ($row = $rproductos->fetch_assoc()) {

    $imagen = is_file("productos/$row[imagen]") ? "servicios/productos/$row[imagen]" : "images/picture.png";
    $imagen_label = "<img style='border-radius: 7px; width:70px; height:70px; object-fit:cover;' loading='lazy' src='$imagen'>";


    //EXISTENCIAS
    #region
    $qexistencias = "SELECT ct_sucursales.nombre as sucursal,
            rt_sucursales_almacenes.nombre as almacen,
            COALESCE(SUM(tr_existencias.cantidad), 0) as cantidad
        FROM tr_existencias, ct_sucursales, rt_sucursales_almacenes
        WHERE tr_existencias.fk_producto = $row[pk_producto]
        AND tr_existencias.estado = 1
        AND ct_sucursales.pk_sucursal = tr_existencias.fk_sucursal
        AND rt_sucursales_almacenes.pk_sucursal_almacen = tr_existencias.fk_almacen
        GROUP BY tr_existencias.fk_sucursal, tr_existencias.fk_almacen;";

    if (!$rexistencias = $mysqli->query($qexistencias)) {
        $codigo = 201;
        $descripcion = "Error al obtener las existencias";
        echo "Lo sentimos, esta aplicación está experimentando problemas. Error al obtener las existencias";
        exit;
    }

    $existencias_producto = "";
    while ($rowe = $rexistencias->fetch_assoc()) {
        $existencias_producto .= "<strong>" . $rowe['sucursal'] . ". " . $rowe['almacen'] . ": </strong>" . $rowe['cantidad'] . "\n";
    }

    if ((int)$row['transferencias'] > 0) {
        $existencias_producto .= "<strong>Transferencias: " . $row['transferencias'] . "\n";
    }
    #endregion


    //BOTONES
    #region
    $btn_editar = $nivel <= 2 ? "<a href='editarProducto.php?id=$row[pk_producto]' class='btn-editar-dast' title='Editar'><i class='bx bx-edit-alt'></i></a>" : "";
    $btn_barcode = "<a target='_blank' class='btn-entregar-dast' href='codigobarrasProductoPDF.php?id=$row[pk_producto]' title='Código de barras'><i class='bx bx-barcode'></i></a>";
    $btn_acciones = $btn_editar . $btn_barcode;
    #endregion


    $costo = $nivel == 1 ? $row['costo'] : 0;


    $data[] = array(
        "pk_producto" => (int)$row['pk_producto'],
        "imagen" => $imagen_label,
        "clave" => $row['clave'],
        "nombre" => $row['producto'],
        "presentacion" => $row['presentacion'],
        "categoria" => $row['categoria'],
        "costo" => "$" . (float)$row['costo'],
        "precio" => "$" . (float)$row['precio'],
        "existencias" => $existencias_producto,
        "transferencias" => (int)$row['transferencias'],
        "estado" => (int)$row['estado'],
        "acciones" => $btn_acciones
    );
}



//REGISTROS TOTALES
#region
$qtotal = "SELECT COUNT(*) AS total FROM ct_productos WHERE estado = 1";

if (!$rtotal = $mysqli->query($qtotal)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. Error al obtener los registros totales";
    exit;
}

$total = $rtotal->fetch_assoc();
$total_records = $total["total"];
#endregion




//REGISTROS FILTRADOS
#region
$qfiltrados = "SELECT COUNT(*) AS total FROM ct_productos p$flestado";

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
