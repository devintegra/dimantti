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



//CATEGORIAS
#region
$qcategoria = "SELECT * FROM ct_categorias WHERE estado = 1";

if (!$rcategorias = $mysqli->query($qcategoria)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
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

    <link rel="shortcut icon" href="images/user.jpg" />

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
                            <h4 class="card-title">Reporte lista de precios</h4>
                            <i class='bx bxs-report' style="font-size:32px"></i>
                        </div>
                        <form class="forms-sample">

                            <div class="row filter-box">

                                <h4 class="card-title">Seleccione criterios de búsqueda</h4>

                                <div class="row">

                                    <div class="row">
                                        <div class="col-sm-12 col-lg-4">
                                            <div class="form-group">
                                                <label for="text-input" class=" form-control-label">Agrupar por</label>
                                                <select class="form-control" id="agrupar">
                                                    <option value="0">Seleccione</option>
                                                    <option value="2">Por categoría</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row d-none filter-box" id="filterCategorias" style="background-color: #fff;">
                                        <div class="col-sm-12 col-lg-12">
                                            <div class="form-group">
                                                <label for="categorias"><i class='bx bx-shopping-bag fs-5' style="margin-bottom: 16px;"></i>Categorías</label>
                                                <div class='d-flex flex-wrap'>
                                                    <?php
                                                    echo "<div class='form-check form-check-success mx-4'>
                                                            <label class='form-check-label fs-6'>
                                                            <input type='checkbox' class='form-check-input chk-categorias' value='0'>
                                                            Todas
                                                            </label>
                                                        </div>";
                                                    while ($categorias = $rcategorias->fetch_assoc()) {
                                                        echo "<div class='form-check form-check-success mx-4'>
                                                            <label class='form-check-label fs-6'>
                                                            <input type='checkbox' class='form-check-input chk-categorias' value='$categorias[pk_categoria]'>
                                                            $categorias[nombre]
                                                            </label>
                                                        </div>";
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <div class="row" style="margin-top: 20px;">
                                    <div class="col-sm-4 col-lg-2">
                                        <button id="generar" type="button" class="btn btn-primary-dast mx-2"><i class="fa fa-file-excel-o mx-2"></i>Excel</button>
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
    <script src="custom/reportePrecios.js"></script>


</body>

</html>
