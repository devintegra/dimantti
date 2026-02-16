<?php
header('Cache-control: private');
date_default_timezone_set('America/Mexico_City');
include("servicios/conexioni.php");
@session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
}

$nivel = $_SESSION["nivel"];
$usuario = $_SESSION["usuario"];
$fk_sucursal = 0;


if ($nivel == 1) {
    $tipo = "Administrador";
    $menu = "fragments/menua.php";
    $qsucursales = "SELECT * FROM ct_sucursales WHERE estado=1";
    if (!$rsucursales = $mysqli->query($qsucursales)) {
        echo "Lo sentimos, esta aplicación está experimentando problemas. 2";
        exit;
    }
}

if ($nivel == 2) {
    $tipo = "Chofer";
    $menu = "fragments/menub.php";
    $qsucursales = "SELECT * FROM ct_sucursales WHERE estado=1";
    if (!$rsucursales = $mysqli->query($qsucursales)) {
        echo "Lo sentimos, esta aplicación está experimentando problemas. 2";
        exit;
    }
}

if ($nivel != 1) {
    header('Location: ../index.php');
}


//CORTES
#region
$qcorte = "SELECT * FROM tr_cortes ORDER BY pk_corte DESC LIMIT 1;";

if (!$rcorte = $mysqli->query($qcorte)) {
    echo "<br>Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

$corte = $rcorte->fetch_assoc();
$fecha = $corte["fecha"];
#endregion



mysqli_set_charset($mysqli, 'utf8');


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
                            <h4 class="card-title">Seleccione criterios de búsqueda ( <i class="bx bx-user-circle"></i> <?php echo $usuario ?> )</h4>
                            <h4 class="card-title">Último corte el <?php echo $fecha ?> </h4>
                        </div>

                        <form class="forms-sample">

                            <div class="row filter-box">
                                <?php

                                echo "<input type='hidden' id='fk_usuario' value='$usuario'/>";
                                echo "<input type='hidden' id='nivel' value='$nivel'/>";

                                if ($nivel == 1) {

                                    echo "<div class='col-sm-12 col-lg-4'>
                                                <div class='form-group'>
                                                    <label for='sucursal'>Sucursal</label>
                                                    <select id='sucursal' class='form-control'>
                                                        <option value='0'>Todas</option>
                                            ";

                                    while ($sucursales = $rsucursales->fetch_assoc()) {
                                        echo "<option value='$sucursales[pk_sucursal]'><td>$sucursales[nombre]</td></tr>";
                                    }

                                    echo "</select></div></div>";
                                } else {
                                    echo "<input type='hidden' id='sucursal' value='$fk_sucursal'/>";
                                }

                                ?>

                                <div class="col-sm-12 col-lg-2">
                                    <div class="form-group">
                                        <label for="tipo">Tipo de corte</label>
                                        <select class="form-control" id="tipo">
                                            <option value="1">Ventas</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-12 col-lg-2">
                                    <div class="form-group">
                                        <label for="comision">¿Agregar comisión?</label><br>
                                        <input type="checkbox" id="comision" style="width: 20px; height: 20px;">
                                    </div>
                                </div>

                                <div class="col-sm-12 col-lg-4 d-flex justify-content-center align-items-center">
                                    <div class="col-lg-4 form-group">
                                        <button id="buscar" type="button" class="btn btn-info-dast"><i class="fa fa-search mx-2"></i>Buscar</button>
                                    </div>
                                </div>
                            </div>

                        </form>

                        <br>

                        <h4 class="card-title">Caja</h4>

                        <div class="table-responsive overflow-hidden" id="tabla">


                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <div class="modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static" id="modalCorte">
        <div class="modal-dialog modal-md" style="width: 70%;">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-between" style="background-color: #2563EB;">
                    <div class="d-flex justify-content-start align-items-center">
                        <i class='bx bx-file-blank mx-2 fs-1'></i>
                        <h4 class="modal-title ltse" id="titleCorte" style="font-size: 24px!important;">Corte de caja</h4>
                    </div>
                    <div>
                        <button class="btn btn-danger" onclick="$('#modalCorte').modal('hide')">Cerrar</button>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="corte-content">
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
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="custom/agregarCorte.js"></script>

</body>

</html>
