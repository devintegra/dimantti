<?php
header('Cache-control: private');
include("servicios/conexioni.php");
@session_start();
mysqli_set_charset($mysqli, 'utf8');


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
    $menu = "fragments/menua.php";
}

if ($nivel != 1) {
    header('Location: ../index.php');
}


?>

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Posmovil - Consola de administración </title>
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

    <div class="main-panel">

        <div class="row justify-content-center">
            <div class="col-lg-10 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title">Tipos de pago</h4>
                            <i class='bx bx-credit-card-alt' style="font-size:32px"></i>
                        </div>
                        <div class="table-responsive overflow-hidden">
                            <?php
                            $qpago = "select * from ct_pagos where estado=1";

                            echo "
                                <table id='dtOrigenes' class='table table-striped text-center'>
                                    <thead>
                                        <tr>
                                            <th>
                                                Descripción del pago
                                            </th>
                                            <th>
                                                Comisión
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>";
                            if (!$resultado = $mysqli->query($qpago)) {
                                echo "Lo sentimos, esta aplicación está experimentando problemas.";
                                exit;
                            }
                            while ($rpago = $resultado->fetch_assoc()) {
                                echo "
                                        <tr>
                                            <td>
                                                <a style='text-decoration:none;' href='editarPago.php?id=$rpago[pk_pago]'>$rpago[nombre]</a>
                                            </td>
                                            <td>
                                                <a style='text-decoration:none;' href='editarPago.php?id=$rpago[pk_pago]'>$rpago[comision]%</a>
                                            </td>
                                        </tr>";
                            }
                            echo "
                                    </tbody>
                                </table>
                            ";
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!--SCRIPTS-->
    <script src="assets/vendor/jquery-2.1.4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js"></script>
    <script src="assets/plugins.js"></script>
    <script src="assets/main.js"></script>
    <script src="assets/lib/data-table/datatables.min.js"></script>
    <script src="assets/lib/data-table/dataTables.bootstrap.min.js"></script>
    <script src="assets/lib/data-table/dataTables.buttons.min.js"></script>
    <script src="assets/lib/data-table/datatables-init.js"></script>

    <script>
        $('#dtOrigenes').DataTable({
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
