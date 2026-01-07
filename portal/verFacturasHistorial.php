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


//MOTIVOS DE CANCELACION
#region
$qmotivos = "SELECT * FROM ct_motivos_cancelacion WHERE estado = 1;";

if (!$rmotivos = $mysqli->query($qmotivos)) {
    echo "<br>Lo sentimos, esta aplicación está experimentando problemas.3";
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
                            <h4 class="card-title">Facturas</h4>
                            <?php
                            if ($nivel == 1 || $nivel == 2 || $nivel == 3 || $nivel == 4) {
                                echo "<a href='facturarVenta.php?id=0&tipo=2'><button type='button' class='btn btn-social-icon-text btn-add'><i class='bx bx-plus'></i>Facturar varios tickets</button></a>";
                            }
                            ?>
                        </div>

                        <?php
                        echo <<<HTML
                            <input type='hidden' id='sucursal' value='$fk_sucursal'/>
                            <input type='hidden' id='nivel' value='$nivel'/>
                            <input type='hidden' id='fk_usuario' value='$usuario'/>
                        HTML;
                        ?>

                        <div class="table-responsive overflow-hidden" id="tabla">

                            <table id='dtEmpresa' class='table table-striped'>
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>#Venta</th>
                                        <th>Cliente</th>
                                        <th>Forma de pago</th>
                                        <th>Método de pago</th>
                                        <th>Estatus</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tfoot style='display: table-header-group'>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>#Venta</th>
                                        <th>Cliente</th>
                                        <th>Forma de pago</th>
                                        <th>Método de pago</th>
                                        <th>Estatus</th>
                                        <th>Acciones</th>
                                    </tr>
                                </tfoot>
                                <tbody>
                                </tbody>
                            </table>

                            <div class='row'>&nbsp;</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-bs-backdrop="static" id="modalCancelacionFactura">
        <div class="modal-dialog modal-lg" style="width: 40%;">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-between align-items-center" style="background-color: #F95F53;">
                    <div class="d-flex justify-content-start align-items-center">
                        <i class='bx bx-x-circle mx-2 fs-1'></i>
                        <h4 class="modal-title ltse" id="myModalLabel" style="font-size: 24px!important">Motivo de la cancelación</h4>
                        <?php
                        echo "<input type='hidden' id='uuid_cancelacion' class='form-control'>";
                        echo "<input type='hidden' id='fk_venta_cancelacion' class='form-control'>";
                        ?>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="motivo_cancelacion">Motivo</label>
                                <select class="form-control" id="motivo_cancelacion">
                                    <option value="0">Seleccione</option>
                                    <?php
                                    while ($rowm = $rmotivos->fetch_assoc()) {
                                        echo "<option value='$rowm[pk_motivo_cancelacion]'>$rowm[nombre]</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 folio_fiscal_content d-none">
                            <div class="form-group">
                                <label for="folio_fiscal">Folio fiscal</label>
                                <input type="text" id="folio_fiscal" class="form-control" placeholder="Folio fiscal de sustitución" autocomplete="off">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="$('#modalCancelacionFactura').modal('hide')" class="btn btn-danger mx-2"></i>Cerrar</button>
                    <button type="button" id="guardarCancelacion" class="btn btn-primary-dast mx-2"><i class="fa fa-save mx-2"></i>Continuar</button>
                </div>
            </div>
        </div>
    </div>


    <div class="modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-bs-backdrop="static" id="modalCorreo">
        <div class="modal-dialog modal-lg" style="width: 40%;">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-between align-items-center" style="background-color: #BBF7B0;">
                    <div class="d-flex justify-content-start align-items-center">
                        <i class='bx bx-envelope mx-2 fs-1'></i>
                        <h4 class="modal-title ltse" id="myModalLabel" style="font-size: 24px!important">Confirmación de reenvío de factura</h4>
                        <?php
                        echo "<input type='hidden' id='uuid_correo' class='form-control'>";
                        ?>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="correo_cliente">Correo electrónico</label>
                                <input type="text" id="correo_cliente" class="form-control" placeholder="Correo electrónico" autocomplete="off">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="$('#modalCorreo').modal('hide')" class="btn btn-danger mx-2"></i>Cerrar</button>
                    <button type="button" id="enviarCorreo" class="btn btn-primary-dast mx-2"><i class="fa fa-save mx-2"></i>Continuar</button>
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
    <script src="custom/verFacturasHistorial.js"></script>
    <script src="custom/facturarVenta.js"></script>
</body>

</html>
