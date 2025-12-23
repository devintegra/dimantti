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
                            <h4 class="card-title">Almacenes móviles</h4>
                            <a href="agregarAlmacen.php"><button type="button" class="btn btn-social-icon-text btn-add"><i class='bx bx-plus'></i>Iniciar almacén</button></a>
                        </div>

                        <div class="table-responsive overflow-hidden">
                            <table id='dtEmpresa' class='table table-striped'>
                                <thead>
                                    <tr>
                                        <th>Ruta</th>
                                        <th>Fecha Inicio</th>
                                        <th>Vendedor</th>
                                        <th>Estatus</th>
                                        <th>Venta</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php

                                    if (!$resultado = $mysqli->query("CALL sp_get_almacenes()")) {
                                        echo "Lo sentimos, esta aplicación está experimentando problemas.";
                                        exit;
                                    }
                                    while ($row = $resultado->fetch_assoc()) {

                                        $pk_almacen = $row["pk"];

                                        //TOTAL DE VENTA
                                        #region
                                        $qventa = "SELECT SUM(total) AS total FROM tr_ventas WHERE fk_almacen = $pk_almacen";

                                        $mysqli->next_result();
                                        if (!$rventa = $mysqli->query($qventa)) {
                                            $codigo = 201;
                                            $descripcion = "Hubo un problema, al obtener el total";
                                        }

                                        $rowv = $rventa->fetch_assoc();
                                        $total = $rowv["total"] ?? 0;
                                        #endregion


                                        //ESTATUS
                                        #region
                                        $estatus = $row["estatus"];
                                        $nestatus = "<p class='badge-warning-integra'>En curso</p>";
                                        $boton = "";

                                        if ($estatus == 3) {
                                            $nestatus = "<p class='badge-success-integra'>Corte realizado</p>";
                                            $boton = "<button type='button' onclick='confirmar($pk_almacen)' class='btn-entregar-dast' title='Reactivar'><i class='fa fa-play'></i></button>";
                                        }
                                        #endregion

                                        echo <<<HTML
                                            <tr class='odd gradeX'>
                                                <td>$row[ruta]</td>
                                                <td>$row[fecha]</td>
                                                <td>$row[vendedor]</td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        $nestatus $boton
                                                    </div>
                                                </td>
                                                <td>$$total</td>
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


    <script src="assets/js/vendor/jquery-2.1.4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js"></script>
    <script src="assets/js/plugins.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/lib/data-table/datatables.min.js"></script>
    <script src="assets/lib/data-table/dataTables.bootstrap.min.js"></script>
    <script src="assets/lib/data-table/dataTables.buttons.min.js"></script>
    <script src="assets/lib/data-table/datatables-init.js"></script>
    <script src="assets/loading/loadingoverlay.js"></script>
    <script src="custom/jquery.confirm.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script type="text/javascript" src="https://maps.google.com/maps/api/js?key=AIzaSyAyzxxx9sOTmkGCHGgrK_Xy86eQxB-AxuI&libraries=places"></script>
    <script src="custom/verAlmacenes.js"></script>

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
