<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
mysqli_set_charset($mysqli, 'utf8');
$codigo = 200;
$descripcion = "";
$registros = array();


if (isset($_GET['tipo']) && is_numeric($_GET['tipo'])) {
    $tipo = (int)$_GET['tipo'];
}

if (isset($_GET['pk_sucursal']) && is_numeric($_GET['pk_sucursal'])) {
    $pk_sucursal = (int)$_GET['pk_sucursal'];
}

if (isset($_GET['fk_usuario']) && is_string($_GET['fk_usuario'])) {
    $fk_usuario = $_GET['fk_usuario'];
}

if (isset($_GET['nivel']) && is_numeric($_GET['nivel'])) {
    $nivel = (int)$_GET['nivel'];
}

if (isset($_GET['inicio']) && is_string($_GET['inicio'])) {
    $inicio = $_GET['inicio'];
}

if (isset($_GET['fin']) && is_string($_GET['fin'])) {
    $fin = $_GET['fin'];
}

if (isset($_GET['fecha']) && is_numeric($_GET['fecha'])) {
    $fecha = (int)$_GET['fecha'];
}




if (!$rsp_get_registros = $mysqli->query("CALL sp_get_ordenes_filtros($nivel, $tipo, $pk_sucursal, '$inicio', '$fin', '$fk_usuario')")) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

while ($row = $rsp_get_registros->fetch_assoc()) {

    $telefonoOutFormat = preg_replace("/[^0-9]/", "", $row["telefono"]);
    $fecha_entrega = "";


    //OBTENER FECHA DE ENTREGA
    if ($row["estatus"] == 5) {
        $mysqli->next_result();
        if (!$rventa = $mysqli->query("SELECT fecha, hora FROM tr_ventas WHERE pk_venta = (SELECT fk_venta FROM tr_ordenes WHERE pk_orden = $row[id])")) {
            echo "Error al obtener la fecha de entrega";
            exit;
        }

        $rowv = $rventa->fetch_assoc();
        $fecha_entrega = $rowv['fecha'] . " " . $rowv['hora'];
    }


    $registros[] = array(
        "id" => $row['id'],
        "fk_venta" => $row['fk_venta'],
        "folio" => $row['folio'],
        "estimado" => $row['estimado'],
        "iniciales" => $row['iniciales'],
        "nombre" => $row['nombre'],
        "reabierta" => $row['reabierta'],
        "fecha" => $row['fecha'],
        "fecha_entrega" => $fecha_entrega,
        "estatus" => $row['estatus'],
        "espera" => $row['espera'],
        "fk_tecnico" => $row['fk_tecnico'],
        "tecnico" => $row['tecnico'],
        "telefono" => $row['telefono'],
        "telefonoOutFormat" => $telefonoOutFormat,
        "nombre" => $row['nombre'],
    );
}


$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => $registros);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
