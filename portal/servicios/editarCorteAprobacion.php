<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
$codigo = 200;
$descripciona = "";
$descripcionb = "";

if (isset($_POST['fk_corte']) && is_numeric($_POST['fk_corte'])) {
    $fk_corte = (int)$_POST['fk_corte'];
}

if (isset($_POST['tipo']) && is_numeric($_POST['tipo'])) {
    $tipo = (int)$_POST['tipo'];
}




if ($tipo == 2) { //Aprobado

    if (!$mysqli->query("UPDATE tr_cortes SET estatus = $tipo WHERE pk_corte = $fk_corte")) {
        $codigo = 201;
        $descripcionb = "Hubo un problema, porfavor vuelva a intentarlo!";
    } else {
        $descripciona = "El corte se aprobó correctamente";
    }
}


if ($tipo == 0) { //No aprobado

    if (!$mysqli->query("UPDATE tr_retiros SET fk_corte = 0 WHERE fk_corte = $fk_corte")) {
        $codigo = 201;
        $descripcionb = "Hubo un problema, porfavor vuelva a intentarlo!";
    }

    if ($codigo == 200) {
        if (!$mysqli->query("UPDATE tr_cargos SET fk_corte = 0 WHERE fk_corte = $fk_corte")) {
            $codigo = 201;
            $descripcionb = "Hubo un problema, porfavor vuelva a intentarlo!";
        }
    }

    if ($codigo == 200) {
        if (!$mysqli->query("UPDATE tr_abonos SET fk_corte = 0 WHERE fk_corte = $fk_corte")) {
            $codigo = 201;
            $descripcionb = "Hubo un problema, porfavor vuelva a intentarlo!";
        }
    }

    if ($codigo == 200) {
        if (!$mysqli->query("UPDATE tr_saldos_iniciales SET fk_corte = 0 WHERE fk_corte = $fk_corte")) {
            $codigo = 201;
            $descripcionb = "Hubo un problema, porfavor vuelva a intentarlo!";
        }
    }

    if ($codigo == 200) {
        if (!$mysqli->query("UPDATE rt_corte_venta SET estado = 0 WHERE fk_corte = $fk_corte")) {
            $codigo = 201;
            $descripcionb = "Hubo un problema, porfavor vuelva a intentarlo!";
        }
    }

    if ($codigo == 200) {

        if (!$mysqli->query("UPDATE tr_cortes SET estatus = $tipo WHERE pk_corte = $fk_corte")) {
            $codigo = 201;
            $descripcionb = "Hubo un problema, porfavor vuelva a intentarlo!";
        }
    }

    if ($codigo == 200) {
        $descripciona = "El corte fue cancelado correctamente";
    }
}








$mysqli->close();
$detalle = array("fk_corte" => $fk_corte);
$general = array("codigo" => $codigo, "descripcion" => $descripcionb, "objList" => $detalle);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
