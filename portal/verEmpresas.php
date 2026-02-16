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

    <link rel="shortcut icon" href="images/user.jpg" />
</head>

<body>
    <?php include $menu ?>

    <div class="main-panel">

        <div class="row justify-content-center">
            <div class="col-lg-10 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title">Empresas</h4>
                        </div>
                        <div class="table-responsive overflow-hidden">
                            <?php

                            $qempresa = "SELECT * FROM ct_empresas where estado=1";

                            echo "
                                <table id='dtOrigenes' class='table table-striped text-center'>
                                    <thead>
                                        <tr>
                                            <th>
                                                Nombre
                                            </th>
                                            <th>
                                                Teléfono
                                            </th>
                                            <th>
                                                Dirección
                                            </th>
                                        </tr>
                                    </thead>
                                    <tfoot style='display: table-header-group'>
                                        <tr>
                                            <th>
                                                Nombre
                                            </th>
                                            <th>
                                                Teléfono
                                            </th>
                                            <th>
                                                Dirección
                                            </th>
                                        </tr>
                                    </tfoot>
                                    <tbody>";
                            if (!$resultado = $mysqli->query($qempresa)) {
                                echo "Lo sentimos, esta aplicación está experimentando problemas.";
                                exit;
                            }
                            while ($rowempresa = $resultado->fetch_assoc()) {
                                echo "
                                        <tr>
                                            <td>
                                                <a style='text-decoration:none;' href='editarEmpresa.php?id=$rowempresa[pk_empresa]'>$rowempresa[nombre]</a>
                                            </td>
                                            <td>
                                                <a style='text-decoration:none;' href='editarEmpresa.php?id=$rowempresa[pk_empresa]'>$rowempresa[telefono]</a>
                                            </td>
                                            <td>
                                                <a style='text-decoration:none;' href='editarEmpresa.php?id=$rowempresa[pk_empresa]'>$rowempresa[direccion]</a>
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
        //INPUTS DE FILTRADO POR COLUMNA
        $('#dtOrigenes tfoot th').each(function() {
            var title = $(this).text().trim();
            $(this).html('<input type="text" class="form-control" placeholder="Buscar ' + title + '" />');
        });

        $('#dtOrigenes').DataTable({
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
