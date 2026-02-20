<?php
header('Cache-control: private');
include("servicios/conexioni.php");
mysqli_set_charset($mysqli, 'utf8');
@session_start();
$pk_sucursal = 0;

if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
}

$nivel = $_SESSION["nivel"];
$fk_usuario = $_SESSION['usuario'];


if ($nivel == 1) {
    $tipo = "Administrador";
    $menu = "fragments/menua.php";
}

if ($nivel == 2) {
    $pk_sucursal = $_SESSION["pk_sucursal"];
    $tipo = "Vendedor";
    $menu = "fragments/menub.php";
}

if ($nivel == 3) {
    $pk_sucursal = $_SESSION["pk_sucursal"];
    $tipo = "Tecnico";
    $menu = "fragments/menuc.php";
}

if ($nivel != 1 && $nivel != 2 && $nivel != 3) {
    header('Location: ../index.php');
}



//SUCURSALES
#region
$mysqli->next_result();
if (!$rsucursales = $mysqli->query("CALL sp_get_sucursales()")) {
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
                            <h4 class="card-title">Seleccione los criterios de búsqueda</h4>
                            <a href="agregarOrden.php"><button type="button" class="btn btn-social-icon-text btn-add"><i class='bx bx-plus'></i>Nueva orden</button></a>
                        </div>
                        <form class="forms-sample">

                            <div class="row filter-box">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="nombre">Fecha de Inicio</label>
                                            <input type='hidden' value="0" id='lafecha'>
                                            <input type="text" id="inicio" name="inicio" placeholder="Inicio" class="form-control datepicker" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="nombre">Fecha de Fin</label>
                                            <input type="text" id="fin" name="fin" placeholder="Inicio" class="form-control datepicker" autocomplete="off">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="tipo">Estatus</label>
                                            <?php echo "<input type='hidden' id='fk_usuario' value='$fk_usuario'>"; ?>
                                            <?php echo "<input type='hidden' id='nivel' value='$nivel'>"; ?>
                                            <select class='form-control' id='tipo'>
                                                <option value='0'>Seleccione tipo</option>
                                                <option value='1'>Sin asignar</option>
                                                <option value='2'>Asignada</option>
                                                <option value='3'>En curso</option>
                                                <option value='4'>Terminada</option>
                                                <option value='5'>Entregada</option>

                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <?php
                                            if ($nivel == 1) {
                                                echo "<label for='sucursal'>Sucursal</label>" .
                                                    "<select id='sucursal' class='form-control'>" .
                                                    "<option value='0'>Todas</option>";

                                                while ($sucursales = $rsucursales->fetch_assoc()) {
                                                    echo "<option value='$sucursales[pk_sucursal]'>$sucursales[nombre]</option>";
                                                }

                                                echo " </select>";
                                            }

                                            if ($nivel == 2 || $nivel == 3) {
                                                echo "<input id='sucursal' type='hidden' value='$pk_sucursal'>";
                                            }

                                            ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="row d-flex justify-content-start align-items-center">
                                    <div class="col-4">
                                        <button id="buscar" type="button" class="btn btn-primary-dast mx-2"><i class="bx bx-search mx-2"></i>Buscar</button>
                                    </div>
                                </div>
                            </div>

                            <br>

                            <!--TABLA DE OREDENES-->
                            <h4 class="card-title">Órdenes</h4>

                            <div class="table-responsive overflow-hidden" id="latabla">
                                <table id='dtEmpresa' class='table table-striped'>
                                    <thead>
                                        <tr>
                                            <th># Orden</th>
                                            <th>Cliente</th>
                                            <th>Telefono</th>
                                            <th>Fecha</th>
                                            <th>Artículo</th>
                                            <th>Acciones</th>
                                            <th>Estatus</th>
                                            <th>Ver</th>
                                            <th>Enviar</th>
                                        </tr>
                                    </thead>
                                    <tbody id='tabla'>
                                    </tbody>
                                </table>
                                <div class='row'>&nbsp;</div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>


    <div class="modal" id="exito" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
        <div class="modal-dialog modal-md">

            <div class="modal-content">
                <div class="modal-header text-center">
                    <h2 class="text-center exitot">Venta <span id="nentrada"></span> </h2>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <a target="_blank" id="pdf" class="btn btn-danger pdfb"><i class="fa fa-file-pdf-o fa-5x" aria-hidden="true"></i><br><br>DESCARGAR ORDEN</a>
                        </div>
                    </div>
                </div>
                <div class="modal-footer text-center">
                    <div class="col-sm-12">

                        <button id="iniciob" type="button" class="btn btn-sm btn-success">Inicio</button>
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
    <script src="assets/loading/loadingoverlay.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="custom/numberFormats.js"></script>
    <script src="custom/verOrdenes.js?v=<?= time(); ?>"></script>


</body>

</html>
