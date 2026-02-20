<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
require 'correo/PHPMailerAutoload.php';
mysqli_set_charset($mysqli, 'utf8');
date_default_timezone_set('America/Mexico_City');
$norden = 0;
$codigo = 200;
$tipoo = 0;
$descripcionn = "";


if (isset($_POST['pk_orden']) && is_numeric($_POST['pk_orden'])) {
    $pk_orden = (int)$_POST['pk_orden'];
}

if (isset($_POST['fk_usuario']) && is_string($_POST['fk_usuario'])) {
    $fk_usuario = $_POST['fk_usuario'];
}

if (isset($_POST['descripcion']) && is_string($_POST['descripcion'])) {
    $descripcion = $_POST['descripcion'];
}

if (isset($_POST['publico']) && is_numeric($_POST['publico'])) {
    $publico = (int)$_POST['publico'];
}

if (isset($_POST['precio']) && is_numeric($_POST['precio'])) {
    $precio = $_POST['precio'];
}

if (isset($_POST['tipo']) && is_numeric($_POST['tipo'])) {
    $tipo = (int)$_POST['tipo'];
}

if (isset($_POST['estimado']) && is_numeric($_POST['estimado'])) {
    $estimado = (float)$_POST['estimado'];
}

if (isset($_POST['costo']) && is_numeric($_POST['costo'])) {
    $costo = (float)$_POST['costo'];
}


$hora_actual = date("H") . ":" . date("i") . ":" . date("s");


//ESTATUS
#region
if ($tipo == 2) {
    $mysqli->next_result();
    if (!$mysqli->query("CALL sp_update_orden_estatus($pk_orden, 4)")) {
        $codigo = 201;
        $descripcionn = "Error al actualizar el estatus de la órden";
    }
    $tipoo = 4;
}


if ($tipo == 3) {
    $mysqli->next_result();
    if (!$mysqli->query("CALL sp_update_orden_estatus($pk_orden, 3)")) {
        $codigo = 201;
        $descripcionn = "Error al actualizar el estatus de la órden";
    }
}


if ($tipo == 4) {
    $mysqli->next_result();
    if (!$mysqli->query("CALL sp_update_orden_estatus($pk_orden, 5)")) {
        $codigo = 201;
        $descripcionn = "Error al actualizar el estatus de la órden";
    }
}


if ($tipo == 5) {
    $mysqli->next_result();
    if (!$mysqli->query("CALL sp_update_orden_estatus_espera($pk_orden, 1)")) {
        $codigo = 201;
        $descripcionn = "Error al actualizar el estatus de la órden";
    }
}


if ($tipo == 6) {
    $mysqli->next_result();
    if (!$mysqli->query("CALL sp_update_orden_estatus_espera($pk_orden, 0)")) {
        $codigo = 201;
        $descripcionn = "Error al actualizar el estatus de la órden";
    }
}


if ($tipo == 7) {
    $tipoo = 9;
}
#endregion




//OBTENER EL VALOR DE REABIERTA
#region
$mysqli->next_result();
if (!$rorden = $mysqli->query("CALL sp_get_orden($pk_orden)")) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

$orden = $rorden->fetch_assoc();
$reabierta = $orden["reabierta"];
#endregion




//INSERTAR EL REGISTRO EN RT_ORDENES_REGISTROS
#region
if ($codigo == 200) {

    if ($tipo == 1 || $tipo == 2 || $tipo == 5 || $tipo == 7) {

        $mysqli->next_result();
        if (!$rexiste = $mysqli->query("CALL sp_get_orden_registro_ultimo($pk_orden)")) {
            echo "Lo sentimos, esta aplicación está experimentando problemas.";
            exit;
        }

        if ($rexiste->num_rows == 0) {

            $mysqli->next_result();
            if (!$rsp_set_registro = $mysqli->query("CALL sp_set_orden_registro($pk_orden, $tipoo, '$descripcion', '$fk_usuario', 1, '', $publico, $precio, $costo, $precio, 1, $reabierta, 0)")) {
                $codigo = 201;
                $descripcion = "Error al guardar registro en la bitácora";
            }
        } else {

            $existe = $rexiste->fetch_assoc();
            $entrega = $existe["entrega"];
            $pdf = $existe["pdf"];

            if ($entrega == 0) {

                $mysqli->next_result();
                if (!$rsp_set_registro = $mysqli->query("CALL sp_set_orden_registro($pk_orden, $tipoo, '$descripcion', '$fk_usuario', $pdf, '', $publico, $precio, $costo, $precio, 1, $reabierta, 0)")) {
                    $codigo = 201;
                    $descripcion = "Error al guardar registro en la bitácora";
                }
            } else {

                $pdf++;
                $mysqli->next_result();
                if (!$rsp_set_registro = $mysqli->query("CALL sp_set_orden_registro($pk_orden, $tipoo, '$descripcion', '$fk_usuario', $pdf, '', $publico, $precio, $costo, $precio, 1, $reabierta, 0)")) {
                    $codigo = 201;
                    $descripcion = "Error al guardar registro en la bitácora";
                }
            }
        }
    } else {

        $mysqli->next_result();
        if (!$rsp_set_registro = $mysqli->query("CALL sp_set_orden_registro($pk_orden, $tipoo, '$descripcion', '$fk_usuario', 1, '', $publico, $precio, $costo, $precio, 1, $reabierta, 0)")) {
            $codigo = 201;
            $descripcion = "Error al guardar registro en la bitácora";
        }
    }

    $rowr = $rsp_set_registro->fetch_assoc();
    $pkregistro = $rowr["pk_registro"];
}
#endregion





//ACTUALIZAR EL COSTO DE LA ORDEN
#region
if ($codigo == 200) {
    $mysqli->next_result();
    if (!$mysqli->query("CALL sp_update_orden_costo($pk_orden, $precio)")) {
        $codigo = 201;
        $descripcionn = "Error al actualizar los datos de la órden";
    }
}
#endregion




//ACTUALIZAR EL VALOR ESTIMADO
#region
if ($codigo == 200) {
    if ($estimado != null || $estimado != '') {

        $mysqli->next_result();
        if (!$mysqli->query("CALL sp_update_orden_estimado($pk_orden, $estimado)")) {
            $codigo = 201;
            $descripcionn = "Error al actualizar los datos de la órden";
        }
    }
}
#endregion







$mysqli->close();
$detalle = array("orden" => $pk_orden, "pk_registro" => $pkregistro, "precio" => $precio);
$general = array("codigo" => $codigo, "descripcion" => $descripcionn, "objList" => $detalle);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
