<?php
header('Cache-control: private');
include("servicios/conexioni.php");
mysqli_set_charset($mysqli, 'utf8');
@session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
}

$sucursal = 0;
$nivel = $_SESSION["nivel"];
$usuario = $_SESSION["usuario"];

if ($nivel == 1) {
    $tipo = "Administrador";
    $menu = "fragments/menua.php";
}

if ($nivel == 2) {
    $sucursal = $_SESSION["pk_sucursal"];
    $tipo = "Chofer";
    $menu = "fragments/menub.php";
}

if ($nivel != 1) {
    header('Location: ../index.php');
}


//PROVEEDORES
#region
$qproveedores = "SELECT * FROM ct_proveedores where estado=1";

if (!$rproveedores = $mysqli->query($qproveedores)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}
#endregion


//PAGOS
#region
$qpagos = "SELECT * FROM ct_pagos where estado=1";

if (!$rpagos = $mysqli->query($qpagos)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. 2";
    exit;
}
#endregion


//PRODUCTOS
#region
$qproductos = "SELECT * FROM ct_productos where estado = 1";

if (!$rproductos = $mysqli->query($qproductos)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. 1";
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

    <link rel="shortcut icon" href="images/user.jpg" />

    <style>
        .select2-selection__choice {
            display: none;
            /* Oculta las opciones seleccionadas en la caja de texto */
        }

        .swal2-html-container {
            overflow-y: hidden !important;
        }
    </style>

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
                            <h4 class="card-title">Nueva compra <span class="badge-warning-integra" id="lasucursal"></span> </h4>
                        </div>
                        <form class="forms-sample" method="post" enctype="multipart/form-data" id="formuploadajax">

                            <div class="row d-none">
                                <div class="col-lg-6 d-flex justify-content-between">
                                    <div class="col-lg-10 form-group">
                                        <label for="clave">Código de barras</label>
                                        <input type="text" id="" name="clave" placeholder="Clave" class="form-control">
                                        <input type="hidden" id="claveo">
                                        <?php echo "<input type='hidden' id='usuario' value='$usuario'/>
                                        <input type='hidden' id='fk_sucursal' value='$sucursal'/>";
                                        ?>
                                    </div>
                                    <div class="col-lg-2 d-flex justify-content-center align-items-center">
                                        <span class="fa fa-search" id="buscar"></span>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="nombre">Nombre del producto</label>
                                        <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre del producto" disabled>
                                    </div>
                                </div>
                            </div>

                            <div class="row d-none">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="cantidad">Cantidad</label>
                                        <input type="text" class="form-control" id="cantidad" name="cantidad" placeholder="Cantidad">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="costo">Costo</label>
                                        <input type="text" class="form-control" id="costo" name="costo" placeholder="Costo">
                                    </div>
                                </div>
                            </div>

                            <div class="row filter-box">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="form-group gap-3">
                                            <label for="clave" style="font-size: 16px; font-weight: bold;"><i class='bx bx-shopping-bag fs-5'></i>Agregar producto: </label>
                                            <select id="clave" class="select2 form-control select2-hidden-accessible" style="width: 100%;" multiple>
                                                <?php
                                                while ($rowp = $rproductos->fetch_assoc()) {
                                                    echo "<option value='$rowp[pk_producto]' data-precio='$rowp[precio]'>$rowp[codigobarras] | $rowp[nombre] | $$rowp[precio]</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                </div>
                            </div>


                            <br>


                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="proveedor">Proveedor</label>
                                        <select id='proveedor' class="form-control">
                                            <option value="0">Seleccione</option>
                                            <?php
                                            while ($proveedores = $rproveedores->fetch_assoc()) {
                                                echo "<option value='$proveedores[pk_proveedor]*-*$proveedores[dias_credito]'>$proveedores[nombre]</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="fecha_inicio" class="form-control-label"><i class='bx bx-calendar fs-5'></i>Inicio</label>
                                        <input type="text" id="fecha_inicio" name="fecha_inicio" placeholder="Inicio" class="form-control datepicker" autocomplete="off" disabled>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="fecha_fin" class="form-control-label"><i class='bx bx-calendar fs-5'></i>Fin</label>
                                        <input type="text" id="fecha_fin" name="fecha_fin" placeholder="Inicio" class="form-control datepicker" autocomplete="off" disabled>
                                    </div>
                                </div>
                            </div>


                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="tipo" class="form-control-label">Tipo</label>
                                        <select class="form-control" id="tipo">
                                            <option value="1">Factura</option>
                                            <option value="2">Remisión</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="archivo">Archivo</label>
                                        <input type="file" id="archivo" name="archivo" class="form-control">
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="factura" class="form-control-label">Factura</label>
                                        <input type="text" class="form-control" id="factura" placeholder="Factura proveedor" autocomplete="off">
                                    </div>
                                </div>

                            </div>

                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="fk_pago" class="form-control-label">Método de pago</label>
                                        <select id='fk_pago' class="form-control">
                                            <option value="0">Seleccione</option>
                                            <?php
                                            while ($pagos = $rpagos->fetch_assoc()) {
                                                echo "<option value='$pagos[pk_pago]'>$pagos[nombre]</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="monto" class="form-control-label" style="color: #2CA880; font-weight: bold;"><i class='bx bx-coin-stack fs-5'></i>Monto a pagar</label>
                                        <input type="text" id="monto" name="monto" placeholder="$0.00" class="form-control" min="0">
                                    </div>
                                </div>
                            </div>

                            <br>

                            <!--TABLA-->
                            <div class="table-responsive overflow-hidden">
                                <table id="entradas" class="table table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th></th>
                                            <th>Producto</th>
                                            <th>Costo</th>
                                            <th>Costo final</th>
                                            <th>Cantidad</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="5"></td>
                                            <td id="total">$0</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>


                            <br><br>


                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="observaciones"><i class='bx bx-edit fs-5'></i>Observaciones</label>
                                        <textarea type="text" class="form-control" id="observaciones" name="observaciones" placeholder="Observaciones" style="height: 100px;"></textarea>
                                    </div>
                                </div>
                            </div>


                            <br><br>


                            <button id="guardar" type="button" class="btn btn-primary-dast mx-2"><i class="fa fa-save mx-2"></i>Guardar</button>

                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>


    <div class="modal" tabindex="-1" role="dialog" aria-hidden="true" data-bs-keyboard="false" data-bs-backdrop="static" id="modalSucursales">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-between align-items-center" style="background-color: #16A34A;">
                    <div class="d-flex justify-content-start align-items-center">
                        <i class='bx bx-store-alt mx-2 fs-1'></i>
                        <h4 class="modal-title ltse" id="myModalLabel" style="font-size: 24px!important">Seleccione el almacén de entrada</h4>
                    </div>
                    <div>
                        <button class="btn btn-danger" onclick="$(location).attr('href','verCompras.php')">Cerrar</button>
                    </div>
                </div>
                <div class="modal-body" id="tablaSucursales">
                    <table id='dtEmpresa' class='table table-striped'>
                        <thead>
                            <tr>
                                <th>ID Almacén</th>
                                <th>Sucursal</th>
                                <th>Almacén</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $nivel != 1 ? $flsucursal = " AND rt_sucursales_almacenes.fk_sucursal = $sucursal" : $flsucursal = "";

                            $qsucursal = "SELECT ct_sucursales.*,
                                rt_sucursales_almacenes.pk_sucursal_almacen,
                                rt_sucursales_almacenes.nombre as almacen
                            FROM ct_sucursales, rt_sucursales_almacenes
                            WHERE ct_sucursales.estado = 1$flsucursal
                            AND rt_sucursales_almacenes.fk_sucursal = ct_sucursales.pk_sucursal";

                            if (!$rsucursales = $mysqli->query($qsucursal)) {
                                echo "Lo sentimos, esta aplicación está experimentando problemas.";
                                exit;
                            }

                            while ($roweusuario = $rsucursales->fetch_assoc()) {

                                echo <<<HTML
                                    <tr class='odd gradeX fc' style='cursor: pointer;'>
                                        <td>$roweusuario[pk_sucursal_almacen]</td>
                                        <td>$roweusuario[nombre]</td>
                                        <td>$roweusuario[almacen]</td>
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


    <div class="modal" id="exito" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
        <div class="modal-dialog modal-md">

            <div class="modal-content">
                <div class="modal-header text-center" style="background-color: #BFDBFE; color:#000;">
                    <h2 class="text-center exitot">Orden de compra <span id="nentrada"></span> </h2>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <a target="_blank" id="pdf" class="btn btn-danger pdfb"><i class="fa fa-file-pdf-o fa-5x" aria-hidden="true"></i><br><br>DESCARGAR COMPROBANTE</a>
                        </div>
                    </div>
                </div>
                <div class="modal-footer text-center">
                    <div class="col-sm-12">
                        <button id="nueva" type="button" class="btn btn-sm btn-warning">Nueva</button>
                        <button id="ver_registros" type="button" class="btn btn-sm btn-info">Ver historial de compras</button>
                        <button id="inicio" type="button" class="btn btn-sm btn-success">Inicio</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script src="assets/vendor/jquery-2.1.4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js"></script>
    <script src="assets/vendor/bootstrap.min.js"></script>
    <script src="assets/plugins.js"></script>
    <script src="assets/main.js"></script>
    <script src="assets/lib/data-table/datatables.min.js"></script>
    <script src="assets/lib/data-table/dataTables.bootstrap.min.js"></script>
    <script src="assets/lib/data-table/dataTables.buttons.min.js"></script>
    <script src="assets/loading/loadingoverlay.js"></script>
    <script src="custom/jquery.numeric.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <script>
        $('#dtProductos').DataTable({
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

    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
    <script src="custom/numberFormats.js"></script>
    <script src="custom/agregarCompra.js"></script>

</body>

</html>
