<?php
header('Cache-control: private');
include("servicios/conexioni.php");
mysqli_set_charset($mysqli, 'utf8');
@session_start();

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
    $pk_motivo = $_GET['id'];
}




$qmotivo = "SELECT * FROM ct_retiros where pk_retiro=$pk_motivo";

if (!$rmotivo = $mysqli->query($qmotivo)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

$motivo = $rmotivo->fetch_assoc();
$nombre = $motivo["nombre"];
$recurrente = $motivo["recurrente"];
$variable = $motivo["variable"];
$dia_pago = $motivo["dia_pago"];
$cantidad = $motivo["cantidad"];


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
                            <h4 class="card-title">Editar motivo retiro</h4>
                            <i class='bx bx-coin' style="font-size:32px"></i>
                        </div>
                        <form class="forms-sample">

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="nombre_comercial">Nombre</label>
                                        <?php
                                        echo "<input type='text' id='nombre' name='nombre' placeholder='Nombre' class='form-control' value='$nombre' autocomplete='off'>
                                            <input type='hidden' id='pk_retiro' value='$pk_motivo'/>";
                                        ?>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="recurrente">¿Es recurrente?</label>
                                        <select class='form-control' id='recurrente'>
                                            <?php
                                            if ($recurrente == 1) {
                                                echo "<option value='0'>Seleccione</option>
                                                <option value='1' selected>Si</option>
                                                <option value='2'>No</option>";
                                            } else {
                                                echo "<option value='0'>Seleccione</option>
                                                <option value='1'>Si</option>
                                                <option value='2' selected>No</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>


                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="variable">¿Es variable?</label>
                                        <select class='form-control' id='variable'>
                                            <?php
                                            if ($variable == 1) {
                                                echo "<option value='0'>Seleccione</option>
                                                <option value='1' selected>Si</option>
                                                <option value='2'>No</option>";
                                            } else {
                                                echo "<option value='0'>Seleccione</option>
                                                <option value='1'>Si</option>
                                                <option value='2' selected>No</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="cantidad">Cantidad $</label>
                                        <?php
                                        echo "<input type='text' id='cantidad' name='cantidad' placeholder='Cantidad monetaria' class='form-control' value='$cantidad' min='0' autocomplete='off'>";
                                        ?>
                                    </div>
                                </div>

                            </div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="dia_pago">Día de pago</label>
                                        <?php
                                        echo "<input type='number' id='dia_pago' name='dia_pago' placeholder='Día del mes que se tiene que pagar' class='form-control' value='$dia_pago' min='0' max='31' autocomplete='off'>";
                                        ?>
                                    </div>
                                </div>

                            </div>

                            <div class="row d-flex justify-content-center">
                                <div class="row col-lg-4 m-2">
                                    <button id="eliminar" type="button" class="btn btn-danger mx-2 d-flex justify-content-center align-items-center"><i class='bx bx-x-circle mx-2' style="font-size: 20px;"></i>Eliminar</button>
                                </div>
                                <div class="row col-lg-4 m-2">
                                    <button id="guardar" type="button" class="btn btn-primary-dast mx-2"><i class="fa fa-save mx-2"></i>Guardar</button>
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
    <script src="custom/jquery.numeric.js"></script>
    <script src="custom/jquery.maskedinput.js"></script>
    <script src="assets/loading/loadingoverlay.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="custom/jquery.confirm.js"></script>
    <script src="custom/numberFormats.js"></script>
    <script src="custom/editarMretiro.js"></script>
</body>

</html>
