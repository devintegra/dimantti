<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
mysqli_set_charset($mysqli, 'utf8');
$codigo = 200;
$descripcion = "";

$countfiles = 0;
$arreglo = array();



if (isset($_POST['pk_producto']) && is_numeric($_POST['pk_producto'])) {
    $pk_producto = $_POST['pk_producto'];
}



//IMAGENES
if (!empty($_FILES['file']['name'])) {

    $mysqli->next_result();
    if (!$rsp_get_producto = $mysqli->query("SELECT MAX(orden) as orden FROM rt_imagenes_productos WHERE fk_producto = $pk_producto AND estado = 1")) {
        $codigo = 201;
        $descripcion = "Error al obtener el consecutivo";
    }

    $rowc = $rsp_get_producto->fetch_assoc();
    $paso = ($rsp_get_producto->num_rows > 0) ? ($rowc['orden'] + 1) : 1;

    $file_count = count($_FILES['file']['name']);


    for ($i = 0; $i < $file_count; $i++) {

        $file_name = $_FILES['file']['name'][$i];
        $nombre_archivo = $pk_producto . "-" . $paso . ".jpg";
        $destination = 'productos/' . $nombre_archivo;

        // MOVER IMAGEN A LA RUTA
        if (move_uploaded_file($_FILES['file']['tmp_name'][$i], $destination)) {

            $mysqli->next_result();
            if (!$mysqli->query("CALL sp_set_producto_imagen($pk_producto, '$nombre_archivo', $paso)")) {
                $codigo = 201;
                $descripcion = "Error al actualizar la imagen en BD." . $mysqli->error;
            }
        } else {

            $codigo = 201;
            $descripcion = "Error al subir la imagen. " . $_FILES["file"]["error"];
        }

        $paso++;
    }
}





$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
