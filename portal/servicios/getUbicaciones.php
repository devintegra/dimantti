<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
mysqli_set_charset($mysqli, 'utf8');
$codigo = 200;
$descripcion = "";
$productosa = array();


$qproductos = "SELECT trv.*,
        ctc.fk_ruta,
        ctc.latitud,
        ctc.longitud
    FROM tr_ventas trv
    JOIN ct_clientes ctc ON ctc.pk_cliente = trv.fk_cliente
    WHERE trv.fecha = CURDATE()
    AND trv.estatus IN(1,2)
    AND trv.estado = 1;";

if (!$rproductos = $mysqli->query($qproductos)) {
    $codigo = 201;
    $descripcion = "Hubo un problema, al obtener los usuarios";
}

while ($productos = $rproductos->fetch_assoc()) {

    $total = "$" . number_format($productos["total"], 2);

    $productosa[] = array(
        'latitud' => $productos["latitud"],
        'longitud' => $productos["longitud"],
        'pk_almacen' => $productos["fk_almacen"],
        'usuario' => $productos["fk_usuario"],
        'ruta' => $productos["fk_ruta"],
        'total' => $total
    );
}


$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => $productosa);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
