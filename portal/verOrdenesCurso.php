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
$fk_usuario = $_SESSION['usuario'];
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
    $tipo = "Tecnico";
    $menu = "fragments/menuc.php";
}

if ($nivel != 1 && $nivel != 2 && $nivel != 3) {
    header('Location: ../index.php');
}


//SUCURSALES
#region
$mysqli->next_result();
if (!$rsucursales = $mysqli->query("CALL sp_get_sucursales()")) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}
#endregion

$verde = "2 days";
$amarillo = "5 days";
$naranja = "8 days";


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
                        <h4 class="card-title">Ordenes en curso</h4>

                        <div class="table-responsive overflow-hidden" id="latabla">
                            <table id='dtEmpresa' class='table table-striped'>
                                <thead>
                                    <tr>
                                        <th># Orden</th>
                                        <th>Cliente</th>
                                        <th>Telefono</th>
                                        <th>Fecha</th>
                                        <th>Técnico</th>
                                        <th>Estatus</th>
                                        <th>Ver</th>
                                    </tr>
                                </thead>
                                <tbody id='tabla'>
                                    <?php

                                    $mysqli->next_result();
                                    if (!$resultado = $mysqli->query("CALL sp_get_ordenes_curso($nivel, $pk_sucursal, '$fk_usuario')")) {
                                        echo "Lo sentimos, esta aplicación está experimentando problemas.";
                                        exit;
                                    }

                                    while ($row = $resultado->fetch_assoc()) {

                                        $fecha_folio =  str_replace("-", "", $row["fecha"]);
                                        $folio = $row["iniciales"] . $fecha_folio . $row['id'];
                                        $tecnico = "";
                                        $reabierta = "";

                                        //ESTATUS
                                        #region
                                        if ($row["reabierta"] > 0) {
                                            $reabierta = "(Reabierta) ";
                                        }

                                        switch ((int)$row["estatus"]) {
                                            case 2:
                                                $tecnico = "$row[tecnico]";
                                                break;
                                            case 3:
                                                $tecnico = "$row[tecnico]";
                                                $estatus = $reabierta . "En curso";
                                                break;
                                            case 4:
                                                $estatus = "Asignada";
                                                break;
                                        }
                                        #endregion


                                        //COLORES Y FECHAS
                                        #region
                                        $hoy = date("Y-m-d");

                                        $fecha_verde = date('Y-m-d', strtotime($row["fecha"] . ' + ' . $verde));
                                        $fecha_amarilla = date('Y-m-d', strtotime($row["fecha"] . ' + ' . $amarillo));
                                        $fecha_naranja = date('Y-m-d', strtotime($row["fecha"] . ' + ' . $naranja));
                                        $color = "";

                                        if ($hoy < $fecha_verde) {
                                            $color = "style='background-color:#9fc985'";
                                        }

                                        if ($hoy >= $fecha_verde && $hoy < $fecha_amarilla) {
                                            $color = "style='background-color:#ebe499'";
                                        }

                                        if ($hoy >= $fecha_amarilla && $hoy < $fecha_naranja) {
                                            $color = "style='background-color:#ebc299'";
                                        }

                                        if ($hoy > $fecha_naranja) {
                                            $color = "style='background-color:#c37d62'";
                                        }
                                        #endregion


                                        echo <<<HTML
                                            <tr $color class='odd gradeX'>
                                                <td><a href='verOrden.php?id=$row[id]' style='color: black!important; text-decoration:none'>$row[folio]</a></td>
                                                <td><a href='verOrden.php?id=$row[id]' style='color: black!important; text-decoration:none; white-space: normal'>$row[nombre]</a></td>
                                                <td><a href='verOrden.php?id=$row[id]' style='color: black!important; text-decoration:none'>$row[telefono]</a></td>
                                                <td><a href='verOrden.php?id=$row[id]' style='color: black!important; text-decoration:none'>$row[fecha]</a></td>
                                                <td style='white-space: normal'>$tecnico</td>
                                                <td><a href='verOrden.php?id=$row[id]' style='color: black!important; text-decoration:none'>$estatus</a></td>
                                                <td><a target='_blank' href='ordenPDF.php?id=$row[id]&ph=$row[telefono]' style='text-decoration:none'><i class='fa fa-file-pdf-o vpdf fa-lg'></i></a></td>
                                            </tr>
                                        HTML;
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
        var paso = 0;
        $(document).ready(function() {
            var table = $('#dtEmpresa').DataTable({
                responsive: true,
                ordering: false,
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

            // Get the page info, so we know what the last is
            var pageInfo = table.page.info(),

                // Set the ending interval to the last page
                endInt = pageInfo.pages,

                // Current page
                currentInt = 0,

                // Start an interval to go to the "next" page every 3 seconds
                interval = setInterval(function() {

                    if (paso === 1) {
                        location.reload();
                    }

                    // "Next" ...
                    table.page(currentInt).draw('page');

                    // Increment the current page int
                    currentInt++;
                    //  alert(currentInt+"--"+endInt);
                    // If were on the last page, reset the currentInt to the first page #
                    if (currentInt === endInt) {
                        currentInt = 0;
                        paso = 1;
                    }
                }, 5000); // 3 seconds
        });
    </script>


    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

</body>

</html>
