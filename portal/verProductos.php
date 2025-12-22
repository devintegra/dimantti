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
    $tipo = "Chofer";
    $menu = "fragments/menub.php";
}


if ($nivel != 1) {
    header('Location: ../index.php');
}

mysqli_set_charset($mysqli, 'utf8');

?>
<!doctype html>

<html class="no-js" lang="">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Posmovil - Consola de administración</title>
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
    <link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />

    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/gh/kenwheeler/slick@1.8.1/slick/slick-theme.css" />
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.css" />

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
                            <h4 class="card-title">Productos</h4>
                            <div class="d-flex gap-2">
                                <a href='servicios/formatoImportacionProductos.php'><button type='button' class='btn btn-social-icon-text btn-add-blue'><i class='bx bx-download'></i>Formato de importación</button></a>
                                <a href='importarProductos.php'><button type='button' class='btn btn-social-icon-text btn-add-orange'><i class='bx bx-import'></i>Importar desde Excel</button></a>
                                <a href="agregarProducto.php"><button type="button" class="btn btn-social-icon-text btn-add"><i class='bx bx-plus'></i>Nuevo producto</button></a>
                            </div>
                        </div>

                        <div class="row filter-box">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="form-group col-lg-3">
                                    <label for="estado" class="d-flex justify-content-start align-items-center gap-2"> <i class='bx bx-filter-alt fs-4'></i> Filtrar por estado</label>
                                    <select class="form-control" id="estado">
                                        <option value="2">Todos</option>
                                        <option value="1" selected>Activos</option>
                                        <option value="0">Inactivos</option>
                                    </select>
                                </div>

                                <div class="col-lg-3 mx-2">
                                    <button class="btn btn-success-dast" id="filtrar">Filtrar</button>
                                    <?php
                                    echo <<<HTML
                                        <input type='hidden' id='nivel' class='form-control' value='$nivel'>
                                        <input type='hidden' id='ventanaActiva' value='1'/>
                                    HTML;
                                    ?>
                                </div>

                                <div class="col-lg-6"></div>

                            </div>
                        </div>

                        <div class="table-responsive overflow-hidden scroll-style" id="tablaProductos">

                            <table id='dtEmpresa' class='table table-striped'>
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Clave</th>
                                        <th>Nombre</th>
                                        <th>Categoría</th>
                                        <th>Presentación</th>
                                        <th>Existencias</th>
                                        <th>Costo</th>
                                        <th>Precio</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tfoot style='display: table-header-group'>
                                    <tr>
                                        <th></th>
                                        <th>Clave</th>
                                        <th>Nombre</th>
                                        <th>Categoría</th>
                                        <th>Presentación</th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                                <tbody>
                                </tbody>
                            </table>
                            <div class='row'>&nbsp;</div>
                        </div>
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
    <script src="assets/lib/data-table/buttons.bootstrap.min.js"></script>
    <script src="assets/lib/data-table/jszip.min.js"></script>
    <script src="assets/lib/data-table/pdfmake.min.js"></script>
    <script src="assets/lib/data-table/vfs_fonts.js"></script>
    <script src="assets/lib/data-table/buttons.html5.min.js"></script>
    <script src="assets/lib/data-table/buttons.print.min.js"></script>
    <script src="assets/lib/data-table/buttons.colVis.min.js"></script>
    <script src="assets/lib/data-table/datatables-init.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="assets/loading/loadingoverlay.js"></script>
    <script src="custom/verProductos.js"></script>

</body>

</html>
