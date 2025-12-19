<?php
header('Cache-control: private');
@session_start();
include("servicios/conexioni.php");

if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
}

$pk_sucursal = 0;
$nivel = $_SESSION["nivel"];
$fk_usuario = $_SESSION['usuario'];

if ($nivel == 1) {
    $tipo = "Administrador";
    $menu = "fragments/menua.php";
    $panel = "fragments/panela.php";
    $dashboard = "fragments/dashboarda.php";
}

if ($nivel == 2) {
    $pk_sucursal = $_SESSION["pk_sucursal"];
    $tipo = "Admin Sucursal";
    $menu = "fragments/menub.php";
    $panel = "fragments/panelb.php";
    $dashboard = "fragments/dashboardb.php";
}

if ($nivel == 3) {
    $pk_sucursal = $_SESSION["pk_sucursal"];
    $tipo = "Técnico";
    $menu = "fragments/menuc.php";
    $panel = "fragments/panelc.php";
    $dashboard = "fragments/dashboardc.php";
}

if ($nivel == 4) {
    $pk_sucursal = $_SESSION["pk_sucursal"];
    $tipo = "Vendedor";
    $menu = "fragments/menud.php";
    $panel = "fragments/paneld.php";
    $dashboard = "fragments/dashboardd.php";
}

if ($nivel != 1 && $nivel != 2 && $nivel != 3 && $nivel != 4) {
    header('Location: ../index.php');
}

?>


<!doctype html>
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

<link rel="stylesheet" href="css/vertical-layout-light/style.css">
<link rel="stylesheet" href="css/estilos.css">

<link rel="shortcut icon" href="images/user-sbg.png" />

<style>
    .card-alert {
        padding: 12px 22px;
        border-bottom: 1px solid #eee;
    }

    .card-alert>span,
    .card-alert>p {
        font-size: 18px;
    }

    .card-alert:hover {
        background-color: #eee;
    }
</style>


</head>

<body>



    <?php include $menu ?>

    <div class="main-panel" style="margin-top: 20px;">
        <div class="content-wrapper">
            <div class="row">
                <div class="col-sm-12">
                    <div class="home-tab">
                        <div class="tab-content tab-content-basic">
                            <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview">

                                <div class="row">
                                    <div class="col-12 d-flex flex-wrap" style="padding-right: 0;">

                                        <div class="row w-100">

                                            <?php include $panel ?>

                                            <?php include $dashboard ?>

                                        </div>

                                    </div>
                                </div>
                            </div>
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
    <script src="assets/lib/data-table/pdfmake.min.js"></script>
    <script src="assets/lib/data-table/vfs_fonts.js"></script>
    <script src="assets/lib/data-table/buttons.html5.min.js"></script>
    <script src="assets/lib/data-table/buttons.print.min.js"></script>
    <script src="assets/lib/data-table/buttons.colVis.min.js"></script>
    <script src="assets/lib/data-table/datatables-init.js"></script>

    <script src="vendors/chart.js/Chart.min.js"></script>
    <script src="vendors/progressbar.js/progressbar.min.js"></script>
    <script src="js/Chart.roundedBarCharts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="custom/index.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="js/jquery.cookie.js" type="text/javascript"></script>

    <script>
        $('#dtClientes').DataTable({
            responsive: true,
            ordering: true,
            pageLength: 3,
            dom: '<"top"i>rt<"bottom"p>',
            //lfptrip
            language: {
                "lengthMenu": "Mostrando _MENU_ registros por pagina",
                "search": "Buscar:",
                "zeroRecords": "No hay registros",
                "info": "Mostrando pagina _PAGE_ de _PAGES_",
                "infoEmpty": "Sin registros disponibles",
                "infoFiltered": "(filtrando de _MAX_ registros totales)",
                "paginate": {
                    "previous": "Anterior",
                    "next": "Siguiente"
                }
            }

        });

        $('#dtSucursales').DataTable({
            responsive: true,
            ordering: true,
            pageLength: 10,
            dom: '<"top"i>rt<"bottom"p>',
            //lfptrip
            language: {
                "lengthMenu": "Mostrando _MENU_ registros por pagina",
                "search": "Buscar:",
                "zeroRecords": "No hay registros",
                "info": "Mostrando pagina _PAGE_ de _PAGES_",
                "infoEmpty": "Sin registros disponibles",
                "infoFiltered": "(filtrando de _MAX_ registros totales)",
                "paginate": {
                    "previous": "Anterior",
                    "next": "Siguiente"
                }
            }

        });

        $('#dtOrdenes').DataTable({
            responsive: true,
            ordering: true,
            pageLength: 10,
            order: [
                [1, 'desc']
            ],
            dom: '<"top"i>rt<"bottom"p>',
            //lfptrip
            language: {
                "lengthMenu": "Mostrando _MENU_ registros por pagina",
                "search": "Buscar:",
                "zeroRecords": "No hay registros",
                "info": "Mostrando pagina _PAGE_ de _PAGES_",
                "infoEmpty": "Sin registros disponibles",
                "infoFiltered": "(filtrando de _MAX_ registros totales)",
                "paginate": {
                    "previous": "Anterior",
                    "next": "Siguiente"
                }
            }

        });
    </script>


</body>

</html>
