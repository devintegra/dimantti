<?php
header('Cache-control: private');
date_default_timezone_set('America/Mexico_City');
include("servicios/conexioni.php");
@session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
}

$nivel = $_SESSION["nivel"];

$filtro = "";

if ($nivel == 1) {
    $pk_sucursal = 0;
    $tipo = "Administrador";
    $menu = "fragments/menua.php";
    $qsucursales = "SELECT * FROM ct_sucursales where estado=1";
    if (!$rsucursales = $mysqli->query($qsucursales)) {
        echo "Lo sentimos, esta aplicación está experimentando problemas. 2";
        exit;
    }
}


if ($nivel == 3) {
    $pk_sucursal = $_SESSION["pk_sucursal"];
    $tipo = "Técnico";
    $menu = "fragments/menuc.php";
}

if ($nivel == 2) {
    $pk_sucursal = 0;
    $tipo = "Admin sucursal";
    $menu = "fragments/menub.php";
    $qsucursales = "SELECT * FROM ct_sucursales where estado=1";
    if (!$rsucursales = $mysqli->query($qsucursales)) {
        echo "Lo sentimos, esta aplicación está experimentando problemas. 2";
        exit;
    }
}

if ($nivel != 1) {
    header('Location: ../index.php');
}




?>


<!doctype html>

<html class="no-js" lang="">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Dast - Consola de administración</title>
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

    <link rel="shortcut icon" href="images/user.jpg" />

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
                            <h4 class="card-title">Seleccione criterios de búsqueda</h4>
                            <i class='bx bxs-report' style="font-size:32px"></i>
                        </div>
                        <form class="forms-sample">

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="text-input" class=" form-control-label">Inicio</label>
                                        <input type="text" id="inicio" name="inicio" placeholder="Inicio" class="form-control datepicker" autocomplete="off">
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="text-input" class=" form-control-label">Fin</label>
                                        <input type="text" id="fin" name="fin" placeholder="Inicio" class="form-control datepicker" autocomplete="off">
                                        <?php echo "<input type='hidden' value='$pk_sucursal' id='fk_sucursal'/>"; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="row">

                                <?php
                                if ($nivel == 1 || $nivel == 4) {
                                    echo "
                                        <div class='col-lg-6'>
                                            <div class='form-group'>
                                                <label for='sucursal' class='form-control-label'>Sucursal</label>
                                                <select id='sucursal' class='form-control'>
                                                    <option value='0'>Todas</option>";
                                    while ($sucursales = $rsucursales->fetch_assoc()) {
                                        echo "<option value='$sucursales[pk_sucursal]'><td>$sucursales[nombre]</td></tr>";
                                    }
                                    echo "
                                                </select>
                                            </div>
                                        </div>
                                        ";
                                }

                                if ($nivel == 3) {
                                    echo "<input type='hidden' class='form-control' value='$pk_sucursal' id='sucursal' disabled>";
                                }
                                ?>


                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="tipo" class=" form-control-label">Tipo</label>
                                        <select id="tipo" class="form-control">
                                            <option value="0">Seleccione</option>
                                            <option value="1">Ingresos</option>
                                            <option value="2">Egresos</option>
                                            <option value="3">Balance</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <button id="generar" type="button" class="btn btn-primary-dast mx-2"><i class="fa fa-file-excel-o mx-2"></i>Generar</button>

                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>



    <script src="assets/js/vendor/jquery-2.1.4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js"></script>
    <script src="assets/js/plugins.js"></script>
    <script src="assets/js/main.js"></script>

    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="custom/reporteIngresosEgresos.js"></script>


</body>

</html>
