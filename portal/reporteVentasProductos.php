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
}

if ($nivel == 2) {
    $pk_sucursal = 0;
    $tipo = "Chofer";
    $menu = "fragments/menub.php";
}


//USUARIOS
#region
$qusuarios = "SELECT * FROM ct_usuarios WHERE nivel = 2 AND estado = 1";

if (!$rsucursales = $mysqli->query($qusuarios)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

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

    <style>
        .select2-selection__choice {
            background-color: #FFCA1A !important;
        }
    </style>

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
                            <h4 class="card-title">Reporte Venta Productos</h4>
                            <i class='bx bxs-report' style="font-size:32px"></i>
                        </div>
                        <form class="forms-sample">

                            <div class="row filter-box">

                                <h4 class="card-title">Seleccione criterios de búsqueda</h4>

                                <div class="row">

                                    <div class="row">
                                        <div class="col-sm-12 col-lg-4">
                                            <div class="form-group">
                                                <label for="text-input" class=" form-control-label"><i class='bx bx-calendar fs-5'></i>Fecha inicio</label>
                                                <input type="text" id="inicio" name="inicio" placeholder="Inicio" class="form-control datepicker" autocomplete="off">
                                            </div>
                                        </div>

                                        <div class="col-sm-12 col-lg-4">
                                            <div class="form-group">
                                                <label for="text-input" class=" form-control-label"><i class='bx bx-calendar fs-5'></i>Fecha fin</label>
                                                <input type="text" id="fin" name="fin" placeholder="Fin" class="form-control datepicker" autocomplete="off">
                                                <?php echo "<input type='hidden' value='$pk_sucursal' id='fk_sucursal'/>"; ?>
                                            </div>
                                        </div>

                                        <div class="col-sm-12 col-lg-4">
                                            <div class="form-group">
                                                <label for="origen">Vendedor</label>
                                                <select class='form-control' id='usuario'>
                                                    <option value="0">Seleccione</option>
                                                    <?php
                                                    while ($sucursales = $rsucursales->fetch_assoc()) {
                                                        echo "<option value='$sucursales[pk_usuario]'>$sucursales[nombre] ($sucursales[pk_usuario])</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>


                                </div>

                                <div class="row">
                                    <div class="col-sm-4 col-lg-2">
                                        <button id="generar" type="button" class="btn btn-primary-dast mx-2"><i class="fa fa-file-excel-o mx-2"></i>Excel</button>
                                    </div>

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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="custom/reporteVentasProductos.js"></script>

</body>

</html>
