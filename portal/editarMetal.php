<?php
header('Cache-control: private');
include("servicios/conexioni.php");
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
    $tipo = "Vendedor";
    $menu = "fragments/menub.php";
}

if ($nivel != 1) {
    header('Location: ../index.php');
}

mysqli_set_charset($mysqli, 'utf8');

if (isset($_GET['id']) && is_string($_GET['id'])) {
    $pk_metal = (int)$_GET['id'];
}


$mysqli->next_result();
if (!$rsp_get_registro = $mysqli->query("CALL sp_get_metal($pk_metal)")) {
    echo "<br>Lo sentimos, esta aplicación está experimentando problemas.1";
    exit;
}

$row = $rsp_get_registro->fetch_assoc();
$nombre = $row["nombre"];
$costo = $row["costo"];
$precio = $row["precio"];

?>


<!doctype html>

<html class="no-js" lang="">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Dimantti - Consola de administración </title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="vendors/feather/feather.css">
    <link rel="stylesheet" href="vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" href="vendors/typicons/typicons.css">
    <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="vendors/font-awesome/css/font-awesome.min.css">
    <link href='https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css' rel='stylesheet'>
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="vendors/select2/select2.min.css">
    <link rel="stylesheet" href="vendors/select2-bootstrap-theme/select2-bootstrap.min.css">
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <link rel="stylesheet" href="css/vertical-layout-light/style.css">
    <link rel="stylesheet" href="css/estilos.css">
    <!-- endinject -->
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
                            <h4 class="card-title">Editar metal</h4>
                            <i class='bx bx-diamond' style="font-size:32px"></i>
                        </div>
                        <form class="forms-sample" enctype="multipart/form-data" id="formuploadajax">

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="nombre" class="d-flex align-items-center gap-2"> <i class="bx bx-text fs-5"></i>Tipo de metal</label>
                                        <?php
                                        echo <<<HTML
                                            <input type='text' id='nombre' name='nombre' placeholder='Ej.Oro' class='form-control' value='$nombre' autocomplete='off'>
                                            <input type='hidden' id='pk_metal' name='pk_metal' class='form-control' value='$pk_metal'>
                                        HTML;
                                        ?>
                                    </div>
                                </div>

                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label for="costo" class="d-flex align-items-center gap-2"> <i class="bx bx-coin fs-5"></i> Costo</label>
                                        <?php
                                        echo <<<HTML
                                            <input type="number" id="costo" name="costo" min="0" placeholder="0.00" value="$costo" class="form-control" autocomplete="off">
                                        HTML;
                                        ?>
                                    </div>
                                </div>

                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label for="precio" class="d-flex align-items-center gap-2"> <i class="bx bx-coin-stack fs-5"></i> Precio x gramo</label>
                                        <?php
                                        echo <<<HTML
                                            <input type="number" id="precio" name="precio" min="0" placeholder="0.00" value="$precio" class="form-control" autocomplete="off">
                                        HTML;
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
    <script src="assets/lib/data-table/datatables.min.js"></script>
    <script src="assets/lib/data-table/dataTables.bootstrap.min.js"></script>
    <script src="assets/lib/data-table/dataTables.buttons.min.js"></script>
    <script src="assets/lib/data-table/datatables-init.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/loading/loadingoverlay.js"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="custom/jquery.confirm.js"></script>
    <script src="custom/editarMetal.js"></script>


</body>

</html>
