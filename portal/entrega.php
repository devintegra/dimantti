<?php
header('Cache-control: private');
include("servicios/conexioni.php");
mysqli_set_charset($mysqli, 'utf8');
@session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
}

$nivel = $_SESSION["nivel"];
$usuario = $_SESSION["usuario"];
$pk_sucursal = $_SESSION["pk_sucursal"];


if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $fk_orden = (int)$_GET['id'];
}


if ($nivel == 1) {
    $tipo = "Administrador";
    $menu = "fragments/menua.php";
    $panel = "fragments/panela.php";
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


//ORDEN
#region
$mysqli->next_result();
if (!$rorden = $mysqli->query("CALL sp_get_orden_by_sucursal($nivel, $fk_orden, $pk_sucursal)")) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.1";
    exit;
}
$orden = $rorden->fetch_assoc();

$estatus = $orden["estatus"];
$anticipo = $orden["anticipo"];
$reabierta = $orden["reabierta"];
#endregion



//REGISTROS
#region
$mysqli->next_result();
if (!$rpdf = $mysqli->query("CALL sp_get_orden_registros_ultimo_pdf($fk_orden)")) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.1";
    exit;
}

$pdf = $rpdf->fetch_assoc();
$pdf_valor = $pdf["pdf"];
#endregion


//PAGOS
#region
$mysqli->next_result();
if (!$rpagos = $mysqli->query("CALL sp_get_pagos()")) {
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

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.18/css/bootstrap-select.min.css" integrity="sha512-ARJR74swou2y0Q2V9k0GbzQ/5vJ2RBSoCWokg4zkfM29Fb3vZEQyv0iWBMW/yvKgyHSR/7D64pFMmU8nYmbRkg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

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
                        <h4 class="card-title">Ver costos orden</h4>

                        <input type="hidden" id="idCurrent">
                        <input type="hidden" id="idQR">

                        <?php
                        echo <<<HTML
                            <input type='hidden' id='pk_orden' value='$fk_orden'/>
                            <input type='hidden' id='nivel' value='$nivel'/>
                            <input type='hidden' id='fk_usuario' value='$usuario'/>
                        HTML;
                        ?>

                        <div class="table-responsive overflow-hidden" id="tabla">

                            <table id='dtEmpresa' class='table table-striped'>
                                <thead>
                                    <tr>
                                        <th>Realizo</th>
                                        <th>Fecha</th>
                                        <th>Descripcion</th>
                                        <th>Archivo</th>
                                        <th>Precio</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php

                                    $mysqli->next_result();
                                    if (!$resultado = $mysqli->query("CALL sp_get_orden_registros_by_pdf($fk_orden, $pdf_valor)")) {
                                        echo "Lo sentimos, esta aplicación está experimentando problemas.2" . $mysqli->error;
                                        exit;
                                    }

                                    $total = 0;
                                    while ($row = $resultado->fetch_assoc()) {

                                        $archivo = "";
                                        if ($row["archivo"] != null && $row["archivo"] != "") {
                                            $archivo = "<a target='_blank' href='https://dimantti.integracontrol.online/portal/servicios/pruebas/$row[archivo]'><i class='fa fa-file-picture-o vimg fa-lg btn-img'></i></a>";
                                        }

                                        $publico = "no";
                                        if ($row["publico"] == 1) {
                                            $publico = "si";
                                        }

                                        $botonp = "<button type='button' id='$row[pk_ordenes_registros]' class='btn-entregar-dast cambiar'>Cambiar</button>";

                                        echo <<<HTML
                                            <tr class='odd gradeX'>
                                                <td>$row[fk_autor]</td>
                                                <td>$row[fecha]</td>
                                                <td style='white-space: normal'>$row[comentarios]</td>
                                                <td>$archivo</td>
                                                <td>$row[precio]</td>
                                            </tr>
                                        HTML;

                                        $total = $total + $row["precio"];
                                    }
                                    ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3"></td>
                                        <td>Total: $</td>
                                        <td>
                                            <input type='number' id='total' class='form-control' value='<?php echo $total ?>' disabled />
                                            <input type='hidden' id='eltotal' class='form-control' value='<?php echo $total ?>' />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3"></td>
                                        <td>Anticipo: $</td>
                                        <td><input type='number' id='anticipo' class='form-control' value='<?php echo $anticipo ?>' disabled /></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3"></td>
                                        <td>Comision: %</td>
                                        <td><input type='number' id='comision' class='form-control' value='0' /></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3"></td>
                                        <td>Descuento: $</td>
                                        <td><input type='number' id='descuento' class='form-control' value='0' /></td>
                                    </tr>
                                </tfoot>
                            </table>
                            <div class='row'>&nbsp;</div>
                        </div>

                        <br><br>

                        <hr>

                        <form class="forms-sample">

                            <div class="row d-flex flex-column">
                                <div class="col-lg-6" id="reader"></div>
                                <h4 class="card-title" id="url"></h4>
                            </div>

                            <br>

                            <?php
                            if ($estatus == 4) {
                                echo <<<HTML
                                    <button type='button' id='guardar' class='btn btn-primary-dast mx-2'>
                                        <i class='fa fa-save mx-2'></i> Confirmar
                                    </button>
                                HTML;
                            }
                            ?>

                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-bs-backdrop="static" id="modalTicket">
        <div class="modal-dialog modal-lg" style="width: 60%;">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-between align-items-center" style="background-color: #71c55b;">
                    <div class="d-flex justify-content-start align-items-center">
                        <i class='bx bx-cart-alt mx-2 fs-1'></i>
                        <h4 class="modal-title ltse" id="myModalLabel" style="font-size: 24px!important">Finalizar orden</h4>
                    </div>
                    <div>
                        <button class="btn btn-danger" onclick="$('#modalTicket').modal('hide')">Cerrar</button>
                    </div>
                </div>
                <div class="modal-body">

                    <div class="row" style="text-align: center;">
                        <div style="text-align: center;">
                            <h5 style="color: #4169e1">Total a pagar</h5>
                            <h1 id="ticket_total" style="color: #4169e1; font-weight: bold;"></h1>
                        </div>

                        <div style="text-align: center;">
                            <h6 style="color: #71c55b">Comisión</h6>
                            <div class="d-flex justify-content-center align-items-center">
                                <h3 id="ticket_comision" style="color: #71c55b; font-weight: bold;">$0.00</h3>

                                <div class='form-check form-check-success mx-4'>
                                    <label class='form-check-label fs-6' data-title="Al marcar la casilla se contabilizará la comisión, de lo contrario se ajustará a $0.00">
                                        <input type='checkbox' class='form-check-input chk-comision' checked value='1'>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <br>

                    <div class="row">
                        <div class="col-12 d-flex justify-content-center gap-3">

                            <?php

                            while ($pagos = $rpagos->fetch_assoc()) {

                                switch ($pagos['nombre']) {
                                    case 'Efectivo':
                                        $id = 'efectivo';
                                        $icon = 'bx-coin';
                                        $btn = '';
                                        break;
                                    case 'Tarjeta de crédito':
                                        $id = 'credito';
                                        $icon = 'bx-credit-card-alt';
                                        $btn = '';
                                        break;
                                    case 'Tarjeta de debito':
                                        $id = 'debito';
                                        $icon = 'bx-credit-card';
                                        $btn = '';
                                        break;
                                    case 'Cheque':
                                        $id = 'cheque';
                                        $icon = 'bx-edit';
                                        $btn = '<input type="text" class="form-control" id="cheque_referencia" placeholder="Referencia">';
                                        break;
                                    case 'Transferencia':
                                        $id = 'transferencia';
                                        $icon = 'bx-transfer-alt';
                                        $btn = '<input type="text" class="form-control" id="transferencia_referencia" placeholder="Referencia">';
                                        break;
                                }
                                echo <<<HTML
                                    <div class='col-sm-3 col-lg-2 d-flex align-items-center flex-column'>
                                        <i class='bx $icon' style='font-size: 28px;'></i>
                                        <h6>$pagos[nombre]</h6>
                                        <input type='number' class='form-control pago_input' id='$id' value='0.00' min='0'>
                                        <input type='hidden' class='form-control' id='comision_$id' value='$pagos[comision]' min='0'>$btn
                                    </div>
                                HTML;
                            }

                            ?>

                        </div>
                    </div>

                    <br><br>

                    <div class="row d-flex justify-content-center">
                        <div class="col-10 d-flex">

                            <div class="col-sm-12 col-lg-6 d-flex justify-content-end align-items-end flex-column">
                                <h5 style="color: #4169e1">Cambio</h5>
                                <h3 id="ticket_cambio" style="font-weight: bold;">
                                </h3>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">

                    <button id="guardar_orden" type="button" class="btn btn-primary-dast mx-2"><i class="fa fa-save mx-2"></i>Registrar venta</button>

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
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js" integrity="sha512-r6rDA7W6ZeQhvl8S7yRVQUKVHdexq+GAlNkNNqVC7YyIV+NwqCTJe2hDWCiffTyRNOeGEzRRJ9ifvRm/HCzGYg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="custom/entregar.js"></script>

</body>

</html>
