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
}

if ($nivel == 2) {
    $fk_sucursal = $_SESSION["pk_sucursal"];
    $tipo = "Chofer";
    $menu = "fragments/menub.php";
}


if ($nivel != 1) {
    header('Location: ../index.php');
}

mysqli_set_charset($mysqli, 'utf8');



$filtro = "";

if ($fk_sucursal != 0) {
    $filtro = " AND tr_ventas.fk_sucursal=$fk_sucursal";
}


//METODOS DE PAGO
#region
$qpagos = "SELECT * FROM ct_pagos where estado=1";

if (!$rpagos = $mysqli->query($qpagos)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. 2";
    exit;
}
#endregion


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


    <div class="main-panel">

        <div class="row justify-content-center">
            <div class="col-lg-10 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title">Ventas móviles</h4>
                        </div>

                        <div class="table-responsive overflow-hidden" id="tabla">
                            <table id='dtEmpresa' class='table table-striped'>
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Folio</th>
                                        <th>Fecha</th>
                                        <th>Cliente</th>
                                        <th>Ruta</th>
                                        <th>Estatus</th>
                                        <th>Acciones</th>
                                        <th>Observaciones</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tfoot style='display: table-header-group'>
                                    <tr>
                                        <th>#</th>
                                        <th>Folio</th>
                                        <th>Fecha</th>
                                        <th>Cliente</th>
                                        <th>Ruta</th>
                                        <th>Estatus</th>
                                        <th></th>
                                        <th>Observaciones</th>
                                        <th>Total</th>
                                    </tr>
                                </tfoot>
                                <tbody>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="8"></td>
                                        <td style='background-color: #FFF7B6' id="total_ventas"></td>
                                    </tr>
                                </tfoot>
                            </table>
                            <div class='row'>&nbsp;</div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>




    <div class="modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static" id="modalReferencia">
        <div class="modal-dialog modal-lg" style="width: 40%;">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-between align-items-center" style="background-color: #64C27B;">
                    <div class="d-flex justify-content-start align-items-center">
                        <i class='bx bx-file mx-2 fs-1'></i>
                        <h4 class="modal-title ltse" id="myModalLabel" style="font-size: 24px!important">Comprobante de pago</h4>
                        <input type="hidden" class="form-control" id="pk_venta_referencia">
                    </div>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="numero_referencia">Número de referencia</label>
                                <input type="text" id="numero_referencia" class="form-control" placeholder="Referencia del pago" autocomplete="off">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="$('#modalReferencia').modal('hide')" class="btn btn-danger mx-2"></i>Cerrar</button>
                    <button type="button" id="enviarReferencia" class="btn btn-primary-dast mx-2"><i class="fa fa-save mx-2"></i>Continuar</button>
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
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="js/loading/loadingoverlay.min.js"></script>
    <script src="custom/verVentasMovil.js"></script>
    <script src="custom/facturarVenta.js"></script>
</body>

</html>
