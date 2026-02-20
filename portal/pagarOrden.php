<?php
header('Cache-control: private');
include("servicios/conexioni.php");
mysqli_set_charset($mysqli, 'utf8');
@session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
}

$nivel = $_SESSION["nivel"];
$usuario = $_SESSION["usuario"];
if (isset($_GET['id']) && is_string($_GET['id'])) {
    $parametros = $_GET['id'];
}


$parametros = explode("--", $parametros);
$pk_orden = $parametros[0];
$folio = $parametros[1];
$pk_sucursal = $_SESSION["pk_sucursal"];


if ($nivel == 1) {
    $tipo = "Administrador";
    $menu = "fragments/menua.php";
}

if ($nivel == 2) {
    $tipo = "Vendedor";
    $menu = "fragments/menub.php";
}

if ($nivel == 3) {
    $tipo = "Técnico";
    $menu = "fragments/menuc.php";
}

if ($nivel != 1 && $nivel != 2 && $nivel != 3) {
    header('Location: ../index.php');
}

if (!$resultado = $mysqli->query("CALL sp_get_orden_by_sucursal($nivel, $pk_orden, $pk_sucursal)")) {
    $error = 1;
}

if ($resultado->num_rows == 0) {
    header('Location: ../index.php');
}


//ORDEN
#region
$qorden = "SELECT tr_ordenes.*,
	SUM(rt_ordenes_registros.precio) as total
    FROM tr_ordenes, rt_ordenes_registros
    WHERE tr_ordenes.pk_orden = $pk_orden
    AND rt_ordenes_registros.fk_orden = tr_ordenes.pk_orden;";

$mysqli->next_result();
if (!$rorden = $mysqli->query($qorden)) {
    echo "<br>Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

$orden = $rorden->fetch_assoc();
$anticipo = $orden["anticipo"];
$total = $orden["total"];
$saldo = number_format(($total - $anticipo), 2);
#endregion


//PAGOS
#region
$mysqli->next_result();
if (!$rpagos = $mysqli->query("CALL sp_get_pagos()")) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. 2";
    exit;
}
#endregion

?>


<!doctype html>

<html class="no-js" lang="">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Dimantti - Consola de administración</title>
    <link rel="stylesheet" href="vendors/feather/feather.css">
    <link rel="stylesheet" href="vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" href="vendors/typicons/typicons.css">
    <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">

    <link rel="stylesheet" href="js/select.dataTables.min.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="vendors/font-awesome/css/font-awesome.min.css">
    <link href='https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="vendors/select2/select2.min.css">
    <link rel="stylesheet" href="vendors/select2-bootstrap-theme/select2-bootstrap.min.css">

    <link rel="stylesheet" href="css/vertical-layout-light/style.css">
    <link rel="stylesheet" href="css/estilos.css">

    <link rel="shortcut icon" href="images/user-sbg.png" />

</head>

<body>

    <?php include $menu ?>

    <!-- partial -->
    <div class="main-panel">

        <div class="row justify-content-center">
            <div class="col-lg-10 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title">Pagar orden <?php echo "<span class='badge-warning-integra'>$folio</span>" ?></h4>
                            <?php
                            echo "<h4 class='card-title badge-danger-integra p-2'>Saldo actual $$saldo</h4>";
                            echo "<input type='hidden' class='form-control' id='saldo' value='$saldo'>";
                            ?>
                        </div>
                        <form class="forms-sample">

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="monto">Monto pago</label>
                                        <?php echo "<input type='hidden' id='pk_orden' value='$pk_orden'/><input type='hidden' id='fk_usuario' value='$usuario'/>" ?>
                                        <input type="number" class="form-control" id="monto" value="0" autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="tipo_pago">Tipo pago</label>
                                        <select id='tipo_pago' class="form-control">
                                            <option value="0">Seleccione</option>
                                            <?php
                                            while ($pagos = $rpagos->fetch_assoc()) {
                                                echo "<option value='$pagos[pk_pago]'>$pagos[nombre]</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <button id="guardar" type="button" class="btn btn-primary-dast mx-2"><i class="fa fa-save mx-2"></i>Guardar</button>

                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>



    <script src="assets/vendor/jquery-2.1.4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js"></script>
    <script src="assets/plugins.js"></script>
    <script src="assets/main.js"></script>
    <script src="custom/jquery.numeric.js"></script>
    <script src="custom/jquery.maskedinput.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="custom/pagarOrden.js"></script>

</body>

</html>
