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
                            <h4 class="card-title">Préstamos</h4>
                            <div class="d-flex gap-2">
                                <a href="agregarPrestamo.php"><button type="button" class="btn btn-social-icon-text btn-add"><i class='bx bx-plus'></i>Nuevo préstamo</button></a>
                            </div>
                        </div>

                        <div class="table-responsive overflow-hidden">
                            <table id='dtEmpresa' class='table table-striped text-center'>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre del empleado</th>
                                        <th>Monto</th>
                                        <th>Frecuencia</th>
                                        <th>Pago</th>
                                        <th>Observaciones</th>
                                        <th>Abonado</th>
                                        <th>Saldo</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tfoot style='display: table-header-group'>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre del empleado</th>
                                        <th>Monto</th>
                                        <th>Frecuencia</th>
                                        <th>Pago</th>
                                        <th>Observaciones</th>
                                        <th>Abonado</th>
                                        <th>Saldo</th>
                                        <th>Acciones</th>
                                    </tr>
                                </tfoot>
                                <tbody>
                                    <?php

                                    if (!$resultado = $mysqli->query("CALL sp_get_prestamos()")) {
                                        echo "Lo sentimos, esta aplicación está experimentando problemas.";
                                        exit;
                                    }

                                    while ($row = $resultado->fetch_assoc()) {

                                        //FRECUENCIA
                                        switch ((int)$row['frecuencia']) {
                                            case 1:
                                                $frecuencia = "Semanal";
                                                break;
                                            case 2:
                                                $frecuencia = "Quincenal";
                                                break;
                                            case 3:
                                                $frecuencia = "Mensual";
                                                break;
                                        }

                                        $monto = number_format($row['monto'], 2);
                                        $saldo = number_format($row['saldo'], 2);
                                        $abonado = number_format($row['monto'] - $row['saldo'], 2);

                                        $btnPDF = "<a target='_blank' href='prestamoPagosPDF.php?id=$row[pk_prestamo]' title='Historial de pagos'> <button class='btn-iniciar-dast'><i class='fa fa-file-text-o'></i></button></a>";

                                        $btnPagar = "";
                                        if ($saldo > 0) {
                                            $btnPagar = "<a href='agregarPrestamoPago.php?id=$row[pk_prestamo]' title='Agregar pago'> <button class='btn-entregar-dast'><i class='fa fa-money'></i></button></a>";
                                        }

                                        echo "
                                        <tr>
                                            <td>$row[pk_prestamo]</td>
                                            <td>$row[nombre_empleado]</td>
                                            <td><p class='badge-success-integra'>$$monto</p></td>
                                            <td>$frecuencia</td>
                                            <td>$row[pago]</td>
                                            <td style='white-space: normal;'>$row[observaciones]</td>
                                            <td><p class='badge-warning-integra'>$$abonado</p></td>
                                            <td><p class='badge-danger-integra'>$$saldo</p></td>
                                            <td>$btnPDF $btnPagar</td>
                                        </tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                            <div class="row">&nbsp;</div>
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
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="assets/lib/data-table/datatables.min.js"></script>
    <script src="assets/lib/data-table/dataTables.bootstrap.min.js"></script>
    <script src="assets/lib/data-table/dataTables.buttons.min.js"></script>
    <script src="assets/lib/data-table/datatables-init.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/lodash@4.17.11/lodash.min.js"></script>
    <script src="assets/loading/loadingoverlay.js"></script>

    <script>
        //INPUTS DE FILTRADO POR COLUMNA
        $('#dtEmpresa tfoot th').each(function() {
            var title = $(this).text().trim();
            if (title != '') {
                $(this).html('<input type="text" class="form-control" placeholder="Buscar ' + title + '" />');
            }
        });

        $('#dtEmpresa').DataTable({
            initComplete: function() {
                // Aplicar la búsqueda
                this.api()
                    .columns()
                    .every(function() {
                        var that = this;

                        $('input', this.footer()).on('keyup change clear', function() {
                            if (that.search() !== this.value) {
                                that.search(this.value).draw();
                            }
                        });
                    });
            },

            responsive: true,
            ordering: true,
            pageLength: 10,
            order: [0, 'desc'],
            dom: '<"dtEmpresa_header"lfp><t><rip>',
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
