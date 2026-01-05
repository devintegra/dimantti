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

    <!-- partial -->
    <div class="main-panel">

        <div class="row justify-content-center">
            <div class="col-lg-10 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">

                        <div class="d-flex justify-content-between">
                            <h4 class="card-title">Salidas de almacén</h4>
                            <a href="salidasAlmacen.php"><button type="button" class="btn btn-social-icon-text btn-add"><i class='bx bx-plus'></i>Nueva salida de almacén</button></a>
                        </div>

                        <div class="table-responsive overflow-hidden" id="tabla">
                            <table id='dtEmpresa' class='table table-striped'>
                                <thead>
                                    <tr>
                                        <th>#Salida</th>
                                        <th>Fecha</th>
                                        <th>Sucursal/Almacén</th>
                                        <th>Observaciones</th>
                                        <th>Usuario</th>
                                        <th>Total</th>
                                        <th>Ver</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php

                                    $nivel == 1 ? $flsucursal = "" : $flsucursal = " AND tr_salidas.fk_sucursal = $pk_sucursal";

                                    $qsalidas = "SELECT tr_salidas.pk_salida as pk_salida,
                                        ct_motivos_salida.nombre as motivo,
                                        tr_salidas.observaciones as observaciones,
                                        tr_salidas.fk_usuario,
                                        tr_salidas.fecha as fecha,
                                        tr_salidas.total_monetario as total,
                                        ct_sucursales.nombre as sucursal,
                                        rt_sucursales_almacenes.nombre as almacen
                                    FROM tr_salidas, ct_motivos_salida, ct_sucursales, rt_sucursales_almacenes
                                    WHERE tr_salidas.estado = 1
                                    AND tr_salidas.fk_motivo = ct_motivos_salida.pk_motivo_salida$flsucursal
                                    AND ct_sucursales.pk_sucursal = tr_salidas.fk_sucursal
                                    AND rt_sucursales_almacenes.pk_sucursal_almacen = tr_salidas.fk_almacen";

                                    if (!$rsalidas = $mysqli->query($qsalidas)) {
                                        echo "Lo sentimos, esta aplicación está experimentando problemas.";
                                        exit;
                                    }

                                    while ($row = $rsalidas->fetch_assoc()) {

                                        $total = number_format($row["total"], 2);

                                        echo <<<HTML
                                            <tr class='odd gradeX'>
                                                <td>$row[pk_salida]</td>
                                                <td><i class='bx bx-calendar fs-5'></i> $row[fecha] $row[hora]</td>
                                                <td>$row[sucursal] / $row[almacen]</td>
                                                <td style='white-space: normal;'>$row[motivo] - $row[observaciones] </td>
                                                <td><i class='bx bx-user-circle fs-5'></i> $row[fk_usuario]</td>
                                                <td>
                                                    <p class='badge-primary-integra'>$$total</p>
                                                </td>
                                                <td>
                                                    <a target='_blank' href='salidasAlmacenPDF.php?id=$row[pk_salida]'><i class='fa fa-file-pdf-o vpdf btn-pdf' style='font-size:22px;'></i></a>
                                                </td>
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

    <script>
        $('#dtEmpresa').DataTable({
            responsive: true,
            "ordering": true,
            order: [
                [0, 'desc']
            ],
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
            },

        });
    </script>

    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

</body>

</html>
