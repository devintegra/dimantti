<?php
header('Cache-control: private');
include("servicios/conexioni.php");
@session_start();
mysqli_set_charset($mysqli, 'utf8');


if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
}

$nivel = $_SESSION["nivel"];

if ($nivel == 1) {
    $tipo = "Administrador";
    $menu = "fragments/menua.php";
}

if ($nivel == 2) {
    $pk_sucursal = $_SESSION["pk_sucursal"];
    $tipo = "Chofer";
    $menu = "fragments/menub.php";
}


if ($nivel != 1) {
    header('Location: ../index.php');
}


if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $pk_pago = (int)$_GET['id'];
}

$qpago = "SELECT * FROM ct_pagos WHERE pk_pago = $pk_pago";

if (!$rpago = $mysqli->query($qpago)) {
    echo "<br>Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

$pago = $rpago->fetch_assoc();
$nombre = $pago["nombre"];
$comision = $pago["comision"];


?>


<!doctype html>

<html class="no-js" lang="">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Posmovil - Consola de administración </title>
    <link rel="stylesheet" href="vendors/feather/feather.css">
    <link rel="stylesheet" href="vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" href="vendors/typicons/typicons.css">
    <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="vendors/font-awesome/css/font-awesome.min.css">
    <link href='https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">

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
                            <h4 class="card-title">Editar tipo de pago</h4>
                            <i class='bx bx-credit-card-alt' style="font-size:32px"></i>
                        </div>
                        <form class="forms-sample">
                            <div class="row d-flex justify-content-center">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="nombre">Descripción</label>
                                        <?php
                                        echo "<input type='text' id='nombre' name='nombre' placeholder='Descripción del pago' class='form-control' value='$nombre' disabled>
                                            <input type='hidden' id='pk_pago' value='$pk_pago'/>";
                                        ?>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="comision">Comisión %</label>
                                        <?php
                                        echo "<input type='number' id='comision' name='comision' placeholder='Comisión (opcional)' class='form-control' value='$comision'>";
                                        ?>
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
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="custom/editarPago.js"></script>

</body>

</html>
