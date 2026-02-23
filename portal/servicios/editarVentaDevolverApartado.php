<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
mysqli_set_charset($mysqli, 'utf8');
@session_start();
$fk_usuario = $_SESSION["usuario"];
$descripcion = "";
$codigo = 200;


if (isset($_POST['pk_venta']) && is_numeric($_POST['pk_venta'])) {
    $pk_venta = (int) $_POST['pk_venta'];
}





//DATOS DE LA VENTA
#region
$mysqli->next_result();
if (!$rsp_get_venta = $mysqli->query("CALL sp_get_venta($pk_venta)")) {
    $codigo = 201;
    $descripcion = "Error al verificar la venta";
}

$rowv = $rsp_get_venta->fetch_assoc();
$fk_sucursal = $rowv["fk_sucursal"];
$fk_almacen = $rowv["fk_almacen"];
#endregion




$mysqli->next_result();
if (!$rsp_get_productos = $mysqli->query("CALL sp_get_venta_detalle($pk_venta)")) {
    $codigo = 201;
    $descripcion = "Error al obtener los productos";
}

while ($row = $rsp_get_productos->fetch_assoc()) {

    //ESTATUS
    if ($codigo == 200) {
        $mysqli->next_result();
        if (!$mysqli->query("CALL sp_update_venta_detalle_entregado($pk_venta, $row[fk_producto])")) {
            $codigo = 201;
            $descripcion = "Error al actualizar el estatus";
        }
    }


    //EXISTENCIAS
    if ($codigo == 200) {
        $mysqli->next_result();
        if (!$mysqli->query("CALL sp_update_existencias_devolver_apartado($fk_sucursal, $fk_almacen, $row[fk_producto], $row[cantidad])")) {
            $codigo = 201;
            $descripcion = "Error al actualizar el almacén";
        }
    }


    //BITÁCORA DE PRODUCTOS
    if ($codigo == 200) {
        $mysqli->next_result();
        if (!$mysqli->query("INSERT INTO tr_movimientos (fk_producto, fk_movimiento, fk_movimiento_detalle, fk_usuario, fk_sucursal, fk_almacen, tipo_venta, fecha, serie, cantidad, total) VALUES($row[fk_producto], 1, $pk_venta, '$fk_usuario', $fk_sucursal, $fk_almacen, 0, CURDATE(), '', $row[cantidad], $row[total])")) {
            $codigo = 201;
            $descripcion = "Error al registrar en la bitácora";
        }
    }
}



$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => null);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
