<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
mysqli_set_charset($mysqli, 'utf8');
$codigo = 200;
$descripcion = "";



if (isset($_POST['pk_cliente']) && is_numeric($_POST['pk_cliente'])) {
    $pk_cliente = (int) $_POST['pk_cliente'];
}

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

if (isset($_POST['lunes']) && is_numeric($_POST['lunes'])) {
    $lunes = (int)$_POST['lunes'];
}

if (isset($_POST['martes']) && is_numeric($_POST['martes'])) {
    $martes = (int)$_POST['martes'];
}

if (isset($_POST['miercoles']) && is_numeric($_POST['miercoles'])) {
    $miercoles = (int)$_POST['miercoles'];
}

if (isset($_POST['jueves']) && is_numeric($_POST['jueves'])) {
    $jueves = (int)$_POST['jueves'];
}

if (isset($_POST['viernes']) && is_numeric($_POST['viernes'])) {
    $viernes = (int)$_POST['viernes'];
}

if (isset($_POST['sabado']) && is_numeric($_POST['sabado'])) {
    $sabado = (int)$_POST['sabado'];
}

if (isset($_POST['domingo']) && is_numeric($_POST['domingo'])) {
    $domingo = (int)$_POST['domingo'];
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

$fk_regimen_fiscal ? $fk_regimen_fiscal = $fk_regimen_fiscal : $fk_regimen_fiscal = 0;




if (!$mysqli->query("UPDATE ct_clientes SET nombre='$nombre', telefono='$telefono', correo='$correo', dias_credito=$dias_credito, limite_credito=$limite_credito, credito=$credito, abonos=$abonos, fk_categoria_cliente=$fk_categoria, cp='$cp', rfc='$rfc', fk_regimen_fiscal=$fk_regimen_fiscal, fk_ruta = $fk_ruta, direccion = '$direccion', latitud = '$latitud', longitud = '$longitud', lunes = $lunes, martes = $martes, miercoles = $miercoles, jueves = $jueves, viernes = $viernes, sabado = $sabado, domingo = $domingo WHERE pk_cliente=$pk_cliente")) {
    $codigo = 201;
    $descripcion = "Error al guardar el registro";
}




$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
