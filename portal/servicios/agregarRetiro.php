<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
$codigo = 200;
$descripciond = "";
$existe = 0;
mysqli_set_charset($mysqli, 'utf8');


if (isset($_POST['fk_sucursal']) && is_numeric($_POST['fk_sucursal'])) {
    $fk_sucursal = $_POST['fk_sucursal'];
}

if (isset($_POST['fk_usuario']) && is_string($_POST['fk_usuario'])) {
    $fk_usuario = $_POST['fk_usuario'];
}

if (isset($_POST['fk_tipo']) && is_numeric($_POST['fk_tipo'])) {
    $fk_tipo = $_POST['fk_tipo'];
}

if (isset($_POST['monto']) && is_numeric($_POST['monto'])) {
    $monto = $_POST['monto'];
}

if (isset($_POST['descripcion']) && is_string($_POST['descripcion'])) {
    $descripcion = $_POST['descripcion'];
}

if (isset($_POST['fk_pago']) && is_numeric($_POST['fk_pago'])) {
    $fk_pago = $_POST['fk_pago'];
}

$ahora = date("Y-m-d H:i:s");
$hora_actual = date("H") . ":" . date("i") . ":" . date("s");



//LA CAJA TIENE DINERO?
#region
$qabonos = "SELECT IFNULL(SUM(monto),0) as total FROM tr_abonos WHERE fk_sucursal = $fk_sucursal AND fk_usuario = '$fk_usuario' AND fk_corte = 0 AND estado = 1";

if (!$rabonos = $mysqli->query($qabonos)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

$abono = $rabonos->fetch_assoc();
$total = $abono["total"];
#endregion



//DATOS DEL RETIRO
#region
$qmotivo = "SELECT * FROM ct_retiros where pk_retiro = $fk_tipo";

if (!$rmotivo = $mysqli->query($qmotivo)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

$motivo = $rmotivo->fetch_assoc();
$recurrente = $motivo["recurrente"];
$dia_pago = $motivo["dia_pago"];
#endregion


if ($recurrente == 1) { //Es un gasto recurrente

    $qretiro = "SELECT * FROM tr_retiros
        WHERE YEAR(fecha) = YEAR(CURRENT_DATE())
        AND MONTH(fecha)  = MONTH(CURRENT_DATE())
        AND fk_retiro = $fk_tipo
        AND fk_sucursal = $fk_sucursal
        AND estado = 1";


    if (!$rretiro = $mysqli->query($qretiro)) {
        $codigo = 201;
    }

    if ($rretiro->num_rows > 0) {

        $existe = 1; //Ya se realizó ese pago en este mes

    }
}


if ($codigo == 200) {
    if ($total < $monto) {
        $codigo = 201;
        $descripciond = "El retiro no pudo ser completado ya que la caja no cuenta con el suficiente dinero para realizar la operación. SALDO ACTUAL DE LA CAJA $" . $total;
    }
}


if ($codigo == 200) {

    if ($existe == 1) {
        $codigo = 201;
        $descripciond = "El gasto que desea efectuar ya fue realizado anteriormente para el mes actual, no puede ser realizado de nuevo";
    } else {

        if (!$mysqli->query("INSERT INTO tr_retiros (fk_sucursal, fk_usuario, fk_retiro, monto, descripcion, fecha, hora, fk_pago) VALUES ($fk_sucursal, '$fk_usuario', $fk_tipo, $monto, '$descripcion', CURDATE(),'$hora_actual', $fk_pago)")) {
            $codigo = 201;
            $descripciond = "Hubo un problema, verifique o intente de nuevo";
        }

        $pk_retiro = $mysqli->insert_id;
    }
}





$mysqli->close();
$detalle = array("existe" => $existe, "pk_retiro" => $pk_retiro);
$general = array("codigo" => $codigo, "descripcion" => $descripciond, "objList" => $detalle);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
