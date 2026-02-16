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


    <div class="main-panel">

        <div class="row justify-content-center">
            <div class="col-lg-10 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title">Ventas</h4>
                            <?php
                            if ($nivel == 1) {
                                echo "<a href='puntoVenta.php'><button type='button' class='btn btn-social-icon-text btn-add'><i class='bx bx-plus'></i>Nueva venta</button></a>";
                            }
                            ?>
                        </div>

                        <?php
                        echo <<<HTML
                            <input type='hidden' id='sucursal' value='$fk_sucursal'/>
                            <input type='hidden' id='nivel' value='$nivel'/>
                            <input type='hidden' id='fk_usuario' value='$usuario'/>
                            <input type='hidden' id='empresa' value='$empresa'/>
                        HTML;
                        ?>

                        <div class="table-responsive overflow-hidden" id="tabla">

                            <table id='dtEmpresa' class='table table-striped'>
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Folio</th>
                                        <th>Fecha</th>
                                        <th>Sucursal</th>
                                        <th>Cliente</th>
                                        <th>Vendedor</th>
                                        <th>Origen</th>
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
                                        <th>Sucursal</th>
                                        <th>Cliente</th>
                                        <th>Vendedor</th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th>Observaciones</th>
                                        <th>Total</th>
                                    </tr>
                                </tfoot>
                                <tbody>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td style='background-color: #FFF7B6' id="total_ventas"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>



        <div class="modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static" id="cancelarModal">
            <div class="modal-dialog modal-md">

                <div class="modal-content">
                    <div class="modal-header d-flex justify-content-between" style="background-color: #E0544A;">
                        <h2 class="text-center exitot">Cancelar venta <span id="nentrada"></span> </h2>
                        <button class='btn btn-sm btn-danger' onclick='$("#cancelarModal").modal("hide")'>Cerrar</button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <input type="hidden" id='fk_venta'>
                            <h5 style='font-weight: bold;'>Fecha: <span id="fecha_txt"></span> </h5>
                            <h5 style='font-weight: bold;'>Cliente: <span id="cliente_txt"></span> </h5>
                        </div>

                        <br>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-check">
                                    <label class="form-check-label">
                                        <input type="radio" class="form-check-input" name="tipo_devolucion" value="1" checked>
                                        Con devolución de dinero
                                    </label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <label class="form-check-label">
                                        <input type="radio" class="form-check-input" name="tipo_devolucion" value="2">
                                        Sin devolución de dinero
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 text-center">
                                <div class="form-group">
                                    <label for="tipo_pago">Tipo de pago</label>
                                    <select class="form-control" id="tipo_pago">
                                        <option value="0">Seleccione</option>
                                        <?php
                                        while ($pagos = $rpagos->fetch_assoc()) {
                                            echo "<option value='$pagos[pk_pago]'>$pagos[nombre]</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <h4 style="font-weight: bold; text-align:right;">Anticipo: <span id="anticipo_txt"></span> </h4>
                            <h4 style="font-weight: bold; text-align:right;">Total de venta: <span id="total_txt"></span> </h4>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <div class="col-sm-12 d-flex justify-content-end">
                            <button id="guardar_cancelacion" type="button" class="btn btn-primary-dast">Confirmar cancelación</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-bs-backdrop="static" id="modalDevolucion">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header d-flex justify-content-between align-items-center" style="background-color: #5468ff;">
                        <div class="d-flex justify-content-start align-items-center">
                            <i class='bx bx-chevrons-down mx-2 fs-1'></i>
                            <h4 class="modal-title ltse" id="myModalLabel" style="font-size: 24px!important">Devolución de productos</h4>
                        </div>
                        <div>
                            <button class="btn btn-danger" onclick="$('#modalDevolucion').modal('hide')">Cerrar</button>
                        </div>
                    </div>
                    <div class="modal-body">

                        <div class="row">
                            <div class="col-sm-12 col-lg-6">
                                <div class="form-group">
                                    <label for="clave" class="form-label"> <i class="bx bx-barcode fs-4"></i> Código del producto</label>
                                    <input type="text" class="form-control" id="clave" name="clave" placeholder="Ingrese el código de barras del producto">
                                    <input type="hidden" class="form-control" id="fk_venta">
                                    <input type="hidden" class="form-control" id="fk_sucursal_devolucion">
                                    <input type="hidden" class="form-control" id="fk_almacen_devolucion">
                                </div>
                            </div>

                            <div class="col-sm-12 col-lg-6 d-none">
                                <div class="form-group">
                                    <label for="clave" class="form-label"> <i class="bx bx-list-ul fs-4"></i> N°de serie</label>
                                    <input type="text" class="form-control" id="serie" name="serie" placeholder="Ingrese la serie del producto">
                                </div>
                            </div>
                        </div>

                        <br>

                        <h4 class="card-title text-center">Vendidos</h4>
                        <div class="table-responsive overflow-hidden filter-box" id="tablaPrestamos">
                        </div>

                        <br>

                        <h4 class="card-title text-center">A devolver</h4>
                        <div class="table-responsive overflow-hidden filter-box" id="tablaDevueltos">
                            <table id='dtDevueltos' class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Clave</th>
                                        <th>Producto</th>
                                        <th>Serie</th>
                                        <th>Cantidad</th>
                                        <th>Precio</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot>
                                    <tr style='background-color: #A8F991;'>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td style='font-weight:bold;' id="devueltos_productos">0</td>
                                        <td></td>
                                        <td style='font-weight:bold;' id="devueltos_total">$0.00</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <br><br>

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <div class="col-sm-12">
                                            <h4>Anticipo: $ <span id="anticipo_devolucion"></span></h4>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <div class="col-sm-6">
                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="radio" class="form-check-input" name="tipo_devolucion" value="1" checked>
                                                    Con devolución de dinero
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="radio" class="form-check-input" name="tipo_devolucion" value="2">
                                                    Sin devolución de dinero
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-8 text-center">
                                        <div class="form-group">
                                            <label for="tipo_pago">Tipo de pago</label>
                                            <select class="form-control" id="tipo_pago">
                                                <option value="0">Seleccione</option>
                                                <?php
                                                $qpagoss = "select * from ct_pagos where estado=1";

                                                if (!$rpagoss = $mysqli->query($qpagoss)) {
                                                    echo "Lo sentimos, esta aplicación está experimentando problemas. 2";
                                                    exit;
                                                }
                                                while ($pagoss = $rpagoss->fetch_assoc()) {
                                                    echo "<option value='$pagoss[pk_pago]'>$pagoss[nombre]</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="col-lg-6">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="observaciones">Observaciones</label>
                                            <textarea class="form-control" id="observaciones" cols="30" rows="10" style='height: 100px;' placeholder="Escribe el motivo de la devolución"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>

                    <div class="modal-footer">

                        <button id="guardarDevolucion" type="button" class="btn btn-primary-dast mx-2"><i class="fa fa-save mx-2"></i>Devolver productos</button>

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
        <script src="custom/verVentas.js"></script>
</body>

</html>
