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
$pk_sucursal = $_SESSION["pk_sucursal"];

if ($nivel == 1) {
    $tipo = "Administrador";
    $menu = "fragments/menua.php";
}

if ($nivel == 2) {
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

?>
<!doctype html>

<html class="no-js" lang="">


<head>
    <!doctype html>
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
                        <h4 class="card-title">Ver ordenes</h4>

                        <div class="table-responsive overflow-hidden">
                            <table id='dtEmpresa' class='table table-striped'>
                                <thead>
                                    <tr>
                                        <th># Orden</th>
                                        <th>Cliente</th>
                                        <th>Telefono</th>
                                        <th>Fecha</th>
                                        <th>Saldo</th>
                                        <th>Pagar</th>
                                    </tr>
                                </thead>
                                <tbody id='tabla'>
                                    <?php
                                    echo "<input type='hidden' id='sucursal' value='$pk_sucursal'>";

                                    if (!$resultado = $mysqli->query("CALL sp_get_ordenes_pago($nivel, $pk_sucursal)")) {
                                        echo "Lo sentimos, esta aplicación está experimentando problemas.";
                                        exit;
                                    }

                                    while ($row = $resultado->fetch_assoc()) {

                                        $boton = "<button type='button' id='$row[id]--$row[folio]' class='btn-iniciar-dast asig' title='Pagar'><i class='bx bx-coin fs-4'></i></button>";
                                        $subtotal = $row["total"];
                                        $anticipo = $row["anticipo"];

                                        if ($anticipo < $subtotal) {

                                            $link_venta = "";
                                            $estatus = "Sin asignar";

                                            if ($row["estatus"] == 2) {
                                                $estatus = "Asignada";
                                            }

                                            if ($row["estatus"] == 3) {
                                                $estatus = "En curso";
                                            }

                                            if ($row["estatus"] == 4) {
                                                $estatus = "Terminada";
                                            }

                                            $saldo = $subtotal - $anticipo;

                                            echo <<<HTML
                                                <tr class='odd gradeX'>
                                                    <td><a href='verOrden.php?id=$row[id]'>$row[folio]</a></td>
                                                    <td style='white-space: normal'>$row[nombre]</td>
                                                    <td>$row[telefono]</td>
                                                    <td>$row[fecha]</td>
                                                    <td><p class='badge-success-integra'>$$saldo</p></td>
                                                    <td>$boton</td>
                                                </tr>
                                            HTML;
                                        }
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
            ordering: true,
            fnDrawCallback: function(oSettings) {
                $('.asig').click(function() {
                    $(location).attr("href", "pagarOrden.php?id=" + this.id);
                });

            },
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
