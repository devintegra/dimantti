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
                            <h4 class="card-title">Seleccione criterios de búsqueda</h4>
                            <i class='bx bxs-report' style="font-size:32px"></i>
                        </div>
                        <form class="forms-sample">

                            <div class="row">
                                <div class="col-lg-6 d-flex justify-content-between">
                                    <div class="col-lg-10 form-group">
                                        <label for="clave">Cliente</label>
                                        <input type="text" id="cliente" name="cliente" placeholder="Cliente" class="form-control" disabled>
                                        <input type="hidden" id="fk_cliente">
                                    </div>
                                    <div class="col-lg-2 d-flex justify-content-center align-items-center">
                                        <span class="fa fa-search btn-rounded-integra" id="buscar" style="cursor: pointer;"></span>
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



    <div class="modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static" id="modalClientes">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-between align-items-center" style="background-color: #5468ff;">
                    <div class="d-flex justify-content-start align-items-center">
                        <i class='bx bx-user-circle mx-2 fs-1'></i>
                        <h4 class="modal-title ltse" id="myModalLabel" style="font-size: 24px!important">Seleccione un cliente</h4>
                    </div>
                    <button class="btn btn-danger" onclick="$('#modalClientes').modal('hide')">Cerrar</button>
                </div>
                <div class="modal-body" id="tablaProductos">
                    <table id='dtProductos' class='table table-striped'>
                        <thead>
                            <tr>
                                <th>Nombre</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $qclientes = "SELECT * FROM ct_clientes WHERE estado = 1";

                            echo "";
                            if (!$resultado = $mysqli->query($qclientes)) {
                                echo "Lo sentimos, esta aplicación está experimentando problemas.";
                                exit;
                            }

                            while ($roweusuario = $resultado->fetch_assoc()) {
                                echo <<<HTML
                                    <tr class='odd gradeX fp' id='$roweusuario[pk_cliente]'>
                                        <td>$roweusuario[nombre]</a></td>
                                    </tr>
                                HTML;
                            }
                            ?>
                        </tbody>
                    </table>
                    <div class='row'>&nbsp;</div>
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

    <script>
        $('#dtProductos').DataTable({
            responsive: true,
            ordering: true,
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

    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="custom/reporteListaPrecios.js"></script>


</body>

</html>
