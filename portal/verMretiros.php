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
                            <h4 class="card-title">Motivos de retiro</h4>
                            <a href="agregarMretiro.php"><button type="button" class="btn btn-social-icon-text btn-add"><i class='bx bx-plus'></i>Nuevo motivo de retiro</button></a>
                        </div>

                        <form class="forms-sample">

                            <div class="table-responsive overflow-hidden">

                                <?php

                                $qmotivos = "SELECT * FROM ct_retiros WHERE estado IN(1,2)";

                                if (!$rmotivos = $mysqli->query($qmotivos)) {
                                    echo "Lo sentimos, esta aplicación está experimentando problemas.";
                                    exit;
                                }

                                echo "<table id='dtEmpresa' class='table table-striped text-center'>
                                            <thead>
                                                <tr>
                                                    <th>Descripción</th>
                                                    <th>¿Recurrente?</th>
                                                    <th>¿Variable?</th>
                                                    <th>Día de pago</th>
                                                    <th>Cantidad</th>
                                                </tr>
                                            </thead><tbody>";

                                while ($motivos = $rmotivos->fetch_assoc()) {

                                    //RECURRENTE
                                    #region
                                    $recurrente = "<p class='badge-primary-integra'>Si</p>";
                                    if ($motivos['recurrente'] == 2) {
                                        $recurrente = "<p class='badge-warning-integra'>No</p>";
                                    }
                                    #endregion

                                    //VARIABLE
                                    #region
                                    $variable = "<p class='badge-success-integra'>Si</p>";
                                    if ($motivos['variable'] == 2) {
                                        $variable = "<p class='badge-danger-integra'>No</p>";
                                    }
                                    #endregion

                                    $cantidad = number_format($motivos['cantidad'], 2);

                                    if ($motivos['estado'] == 1) {

                                        echo "<tr class='odd gradeX'>
                                                    <td><a href='editarMretiro.php?id=$motivos[pk_retiro]'>$motivos[nombre]</a></td>
                                                    <td><a href='editarMretiro.php?id=$motivos[pk_retiro]'>$recurrente</a></td>
                                                    <td><a href='editarMretiro.php?id=$motivos[pk_retiro]'>$variable</a></td>
                                                    <td><a href='editarMretiro.php?id=$motivos[pk_retiro]'>$motivos[dia_pago]</a></td>
                                                    <td><a href='editarMretiro.php?id=$motivos[pk_retiro]'>$$cantidad</a></td>
                                                </tr>";
                                    } else {
                                        echo "<tr class='odd gradeX'>
                                                    <td>$motivos[nombre]</td>
                                                    <td>$recurrente</td>
                                                    <td>$variable</td>
                                                    <td>$motivos[dia_pago]</td>
                                                    <td>$$cantidad</td>
                                                </tr>";
                                    }
                                }

                                echo "</tbody></table>";
                                ?>

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
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

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

    <script>
        $('#dtEmpresa').DataTable({
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

</body>

</html>
