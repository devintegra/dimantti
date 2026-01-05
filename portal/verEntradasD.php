<?php
header('Cache-control: private');
include("servicios/conexioni.php");
@session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
}

$nivel = $_SESSION["nivel"];
$usuario = $_SESSION["usuario"];

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
                            <h4 class="card-title">Entradas directas</h4>
                            <a href="agregarEntradaD.php"><button type="button" class="btn btn-social-icon-text btn-add"><i class='bx bx-plus'></i>Nueva entrada</button></a>
                        </div>

                        <div class="table-responsive overflow-hidden" id="tabla">
                            <table id='dtEmpresa' class='table table-striped'>
                                <thead>
                                    <tr>
                                        <th>#Entrada</th>
                                        <th>Fecha</th>
                                        <th>Sucursal/Almacén</th>
                                        <th>Usuario</th>
                                        <th>Observaciones</th>
                                        <th>Ver</th>
                                    </tr>
                                </thead>
                                <tfoot style='display: table-header-group'>
                                    <tr>
                                        <th>#Entrada</th>
                                        <th>Fecha</th>
                                        <th>Sucursal/Almacén</th>
                                        <th>Usuario</th>
                                        <th>Observaciones</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                                <tbody>
                                    <?php

                                    $nivel == 1 ? $flsucursal = "" : $flsucursal = " AND tr_entradas.fk_sucursal = $pk_sucursal";

                                    $eusuario = "SELECT ct_sucursales.nombre as sucursal,
                                            rt_sucursales_almacenes.nombre as almacen,
                                            tr_entradas.pk_entrada as pk_entrada,
                                            tr_entradas.fk_usuario,
                                            tr_entradas.fecha as fecha,
                                            tr_entradas.hora,
                                            tr_entradas.observaciones
                                        FROM ct_sucursales, tr_entradas, rt_sucursales_almacenes
                                        WHERE tr_entradas.fk_sucursal = ct_sucursales.pk_sucursal$flsucursal
                                        AND rt_sucursales_almacenes.pk_sucursal_almacen = tr_entradas.fk_almacen
                                        AND tr_entradas.estado = 1
                                        AND tr_entradas.fk_compra = 0";

                                    if (!$resultado = $mysqli->query($eusuario)) {
                                        echo "Lo sentimos, esta aplicación está experimentando problemas.";
                                        exit;
                                    }

                                    while ($row = $resultado->fetch_assoc()) {

                                        echo <<<HTML
                                            <tr class='odd gradeX'>
                                                <td>$row[pk_entrada]</td>
                                                <td><i class='bx bx-calendar fs-5'></i>$row[fecha] $row[hora]</td>
                                                <td>$row[sucursal] / $row[almacen]</td>
                                                <td><i class='bx bx-user-circle fs-5'></i>$row[fk_usuario]</td>
                                                <td style='white-space: normal'>$row[observaciones]</td>
                                                <td><a target='_blank' href='entradaPDF.php?id=$row[pk_entrada]'><i class='fa fa-file-pdf-o vpdf fa-lg btn-pdf'></i></a></td>
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
    <script src="assets/lib/data-table/pdfmake.min.js"></script>
    <script src="assets/lib/data-table/vfs_fonts.js"></script>
    <script src="assets/lib/data-table/buttons.html5.min.js"></script>
    <script src="assets/lib/data-table/buttons.print.min.js"></script>
    <script src="assets/lib/data-table/buttons.colVis.min.js"></script>
    <script src="assets/lib/data-table/datatables-init.js"></script>

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
            order: [
                [0, 'desc']
            ],
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
