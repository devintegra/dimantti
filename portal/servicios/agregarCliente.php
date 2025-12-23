<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
mysqli_set_charset($mysqli, 'utf8');
$codigo = 200;
$descripcion = "";



if (isset($_POST['nombre']) && is_string($_POST['nombre'])) {
    $nombre = $_POST['nombre'];
}

if (isset($_POST['telefono']) && is_string($_POST['telefono'])) {
    $telefono = $_POST['telefono'];
}

if (isset($_POST['correo']) && is_string($_POST['correo'])) {
    $correo = $_POST['correo'];
}

if (isset($_POST['dias_credito']) && is_numeric($_POST['dias_credito'])) {
    $dias_credito = (int)$_POST['dias_credito'];
}

if (isset($_POST['limite_credito']) && is_numeric($_POST['limite_credito'])) {
    $limite_credito = (float)$_POST['limite_credito'];
}

if (isset($_POST['credito']) && is_numeric($_POST['credito'])) {
    $credito = (float)$_POST['credito'];
}

if (isset($_POST['abonos']) && is_numeric($_POST['abonos'])) {
    $abonos = (int)$_POST['abonos'];
}

if (isset($_POST['fk_categoria']) && is_numeric($_POST['fk_categoria'])) {
    $fk_categoria = (int)$_POST['fk_categoria'];
}

if (isset($_POST['cp']) && is_string($_POST['cp'])) {
    $cp = $_POST['cp'];
}

if (isset($_POST['rfc']) && is_string($_POST['rfc'])) {
    $rfc = $_POST['rfc'];
}

if (isset($_POST['fk_regimen_fiscal']) && is_numeric($_POST['fk_regimen_fiscal'])) {
    $fk_regimen_fiscal = (int)$_POST['fk_regimen_fiscal'];
}

if (isset($_POST['fk_ruta']) && is_numeric($_POST['fk_ruta'])) {
    $fk_ruta = (int)$_POST['fk_ruta'];
}

if (isset($_POST['dia']) && is_numeric($_POST['dia'])) {
    $dia = (int)$_POST['dia'];
}

if (isset($_POST['tipo']) && is_numeric($_POST['tipo'])) {
    $tipo = (int)$_POST['tipo'];
}

if (isset($_POST['latitud']) && is_string($_POST['latitud'])) {
    $latitud = $_POST['latitud'];
}

if (isset($_POST['longitud']) && is_string($_POST['longitud'])) {
    $longitud = $_POST['longitud'];
}

if (isset($_POST['direccion']) && is_string($_POST['direccion'])) {
    $direccion = $_POST['direccion'];
}

$ahora = new DateTime();
$id_file = $ahora->getTimestamp();

$arrayDias = array("Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sábado", "Domingo");
$diaNombre = $arrayDias[$dia - 1];



//RUTA
#region
if (!$rruta = $mysqli->query("SELECT * FROM ct_rutas WHERE pk_ruta = $fk_ruta AND estado = 1")) {
    $codigo = 201;
    $descripcion = "Error al insertar el encabezado";
}

$rowr = $rruta->fetch_assoc();
$ruta = $rowr["clave"];
$clave = "R" . $ruta . $id_file;
#endregion




if (!$mysqli->query("INSERT INTO ct_clientes(clave, nombre, telefono, fk_sucursal, correo, dias_credito, limite_credito, credito, abonos, fk_categoria_cliente, cp, rfc, fk_regimen_fiscal, tipo, direccion, latitud, longitud, fk_ruta, dia_numero, dia) values ('$clave', '$nombre', '$telefono', 1, '$correo', $dias_credito, $limite_credito, $credito, $abonos, $fk_categoria, '$cp', '$rfc', $fk_regimen_fiscal, $tipo, '$direccion', '$latitud', '$longitud', $fk_ruta, $dia, '$diaNombre')")) {
    $codigo = 201;
    $descripcion = "Error al guardar el registro";
}





$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
