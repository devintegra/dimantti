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
    $pk_motivo_salida = (int)$_GET['id'];
}

$qmotivo = "SELECT * FROM ct_motivos_salida where pk_motivo_salida=$pk_motivo_salida";

if (!$rmotivo = $mysqli->query($qmotivo)) {
    echo "<br>Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

$motivo = $rmotivo->fetch_assoc();
$nombre = $motivo["nombre"];


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
    <link rel="stylesheet" href="css/estilos.css">

    <link rel="stylesheet" href="css/vertical-layout-light/style.css">

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
                            <h4 class="card-title">Editar motivo de salida</h4>
                            <i class='bx bx-right-arrow-alt' style="font-size:32px"></i>
                        </div>
                        <form class="forms-sample">
                            <div class="row d-flex justify-content-center">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="nombre">Descripción</label>
                                        <?php
                                        echo "<input type='text' id='nombre' name='nombre' placeholder='Descripción del motivo de salida' class='form-control' value='$nombre'>
                                            <input type='hidden' id='pk_motivo_salida' value='$pk_motivo_salida'/>";
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <div class="row d-flex justify-content-center">
                                <div class="row col-lg-4 m-2">
                                    <button id="eliminar" type="button" class="btn btn-danger mx-2 d-flex justify-content-center align-items-center"><i class='bx bx-x-circle mx-2' style="font-size: 20px;"></i>Eliminar</button>
                                </div>
                                <div class="row col-lg-4 m-2">
                                    <button id="guardar" type="button" class="btn-primary-dast mx-2"><i class="fa fa-save mx-2"></i>Guardar</button>
                                </div>
                            </div>

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
    <script src="custom/editarMotivoSalida.js"></script>

</body>

</html>
