<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
mysqli_set_charset($mysqli, 'utf8');
date_default_timezone_set('America/Mexico_City');
$codigo = 200;
$descripcion = "";


$ahora = date("Y-m-d H:i:s");

//$dayofweek = date('w', strtotime($ahora));
$days = array('Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado');
$ventas = array();


$paso = 0;
for ($i = 7; $i > 0; $i--) {
    $qventa = "SELECT SUM(total) as total, fecha as fecha FROM tr_ventas WHERE fecha=(CURDATE() - INTERVAL $paso DAY)";

    if (!$rventa = $mysqli->query($qventa)) {
        $codigo = 201;
        $descripcion = "Hubo un problema, porfavor vuelva a intentarlo";
    }

    $venta = $rventa->fetch_assoc();
    $lafecha = $venta["fecha"];
    $eltotal = $venta["total"];

    if ($lafecha != null && $lafecha != 'null' && $eltotal != null && $eltotal != 'null') {
        $dayofweek = date('w', strtotime($venta["fecha"]));
    } else {
        $fecha_actual = date("Y-m-d");

        $lafecha = date("Y-m-d", strtotime($fecha_actual . "- $paso days"));
        $eltotal = 0;
        $dayofweek = date('w', strtotime($lafecha));
    }




    $ventas[] = array('fecha' => $lafecha, 'dia' => $days[$dayofweek], 'total' => $eltotal, 'tipo' => 1);
    $paso++;
}



for ($i = 7; $i < 14; $i++) {
    $qventa = "SELECT SUM(total) as total, fecha as fecha FROM tr_ventas WHERE fecha=(CURDATE() - INTERVAL $i DAY)";

    if (!$rventa = $mysqli->query($qventa)) {
        $codigo = 201;
        $descripcion = "Hubo un problema, porfavor vuelva a intentarlo";
    }

    $venta = $rventa->fetch_assoc();
    $lafecha = $venta["fecha"];
    $eltotal = $venta["total"];

    if ($lafecha != null && $lafecha != 'null' && $eltotal != null && $eltotal != 'null') {
        $dayofweek = date('w', strtotime($venta["fecha"]));
    } else {
        $fecha_actual = date("Y-m-d");

        $lafecha = date("Y-m-d", strtotime($fecha_actual . "- $i days"));
        $eltotal = 0;
        $dayofweek = date('w', strtotime($lafecha));
    }



    $ventas[] = array('fecha' => $lafecha, 'dia' => $days[$dayofweek], 'total' => $eltotal, 'tipo' => 2);
}



$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => $ventas);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
