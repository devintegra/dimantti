<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
mysqli_set_charset($mysqli, 'utf8');


if (isset($_GET['clave']) && is_string($_GET['clave'])) {
    $clave = $_GET['clave'];
}


$codigo = 200;
$descripcion = "";
$existencia = 0;
$unidad = "";
$nombre = "";
$costo = 0.00;


$qproductos = "SELECT ctp.*,
        (SELECT COALESCE(SUM(cantidad),0) FROM tr_existencias WHERE fk_producto = ctp.pk_producto AND estado = 1 AND cantidad > 0) as existencias,
        (SELECT imagen FROM rt_imagenes_productos WHERE fk_producto = ctp.pk_producto AND estado = 1) as imagen
    FROM ct_productos ctp
    WHERE ctp.pk_producto = $clave
    AND estado=1";

if (!$rproductos = $mysqli->query($qproductos)) {
    $codigo = 201;
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

if ($rproductos->num_rows == 0) {
    $codigo = 201;
}

if ($codigo == 200) {

    $row = $rproductos->fetch_assoc();
    $pk_producto = $row["pk_producto"];
    $clave = $row["clave"];
    $nombre = $row["nombre"];
    $costo = $row["costo"];
    $codigobarras = $row["codigobarras"];
    $existencias = $row["existencias"];

    //IMAGEN
    #region
    $file = "productos/$row[imagen]";

    if (is_file($file)) {
        $fondo = "<img style='border-radius: 7px; width:70px; height:70px; object-fit:cover;' loading='lazy' src='servicios/productos/$row[imagen]'>";
    } else {
        $fondo = "<img style='border-radius: 7px; width:70px; height:70px; object-fit:cover;' loading='lazy' src='images/picture.png'>";
    }
    #endregion

}




$mysqli->close();
$detalle = array(
    "nombre" => $nombre,
    "costo" => number_format($costo, 2),
    "clave" => $clave,
    "codigobarras" => $codigobarras,
    "pk_producto" => $pk_producto,
    "existencias" => $existencias,
    "imagen" => $fondo
);
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => $detalle);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
