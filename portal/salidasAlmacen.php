<?php
header('Cache-control: private');
include("servicios/conexioni.php");
@session_start();
mysqli_set_charset($mysqli, 'utf8');

if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
}

$pk_sucursal = 0;
$nivel = $_SESSION["nivel"];
$usuario = $_SESSION["usuario"];


if ($nivel == 1) {
    $tipo = "Administrador";
    $menu = "fragments/menua.php";
}

if ($nivel == 2) {
    $pk_sucursal = $_SESSION["pk_sucursal"];
    $tipo = "Chofer";
    $menu = "fragments/menub.php";
}


if ($nivel != 1) {
    header('Location: ../index.php');
}


//PRODUCTOS
#region
$qproductos = "SELECT pk_producto, clave, codigobarras, nombre, precio FROM ct_productos WHERE estado = 1";

if (!$rproductos = $mysqli->query($qproductos)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. 1";
    exit;
}
#endregion


//MOTIVOS
#region
$qmotivo = "SELECT * FROM ct_motivos_salida WHERE estado=1";

if (!$rmotivo = $mysqli->query($qmotivo)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}
#endregion

?>

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Posmovil - Consola de administración </title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="vendors/feather/feather.css">
    <link rel="stylesheet" href="vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" href="vendors/typicons/typicons.css">
    <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="vendors/font-awesome/css/font-awesome.min.css">
    <link href='https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css' rel='stylesheet'>
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="vendors/select2/select2.min.css">
    <link rel="stylesheet" href="vendors/select2-bootstrap-theme/select2-bootstrap.min.css">
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <link rel="stylesheet" href="css/vertical-layout-light/style.css">
    <link rel="stylesheet" href="css/estilos.css">
    <!-- endinject -->
    <link rel="shortcut icon" href="images/user-sbg.png" />

    <style>
        .select2-selection__choice {
            display: none;
            /* Oculta las opciones seleccionadas en la caja de texto */
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
                            <h4 class="card-title">Salidas de almacén</h4>
                            <h4 class="card-title"><span class="badge-warning-integra" id="lasucursal"></span></h4>
                            <?php echo "
                            <input type='hidden' id='usuario' value='$usuario'/>
                            <input type='hidden' id='fk_sucursal' value='$pk_sucursal'/>";
                            ?>
                        </div>
                        <form class="forms-sample" enctype="multipart/form-data" id="formuploadajax">

                            <div class="row filter-box">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group gap-3">
                                            <label for="clave" style="font-size: 16px; font-weight: bold;">Agregar producto: </label>
                                            <select id="clave" class="select2 form-control select2-hidden-accessible" style="width: 100%;" multiple>
                                                <?php
                                                while ($productos = $rproductos->fetch_assoc()) {
                                                    echo "<option value='$productos[codigobarras]'>$productos[codigobarras] | $productos[nombre] | $$productos[precio]</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <input type="hidden" id="claveo" value="0">
                                    <input type="hidden" id="fk_sucursal" value="0">
                                    <input type="hidden" class="form-control" id="clavee" name="clave" placeholder="Clave">
                                    <input type="hidden" class="form-control" id="nombred" name="nombred" placeholder="Nombre" disabled>
                                    <input type="hidden" class="form-control" id="existencias" name="existencias" placeholder="Existencias" disabled>
                                    <input type="hidden" class="form-control" id="cantidadtmp" disabled>
                                    <input type="hidden" class="form-control" id="costod" name="costod" placeholder="Costo" disabled>
                                    <input type="hidden" class="form-control" id="claveo" name="claveo" placeholder="ID" disabled>
                                    <input type="hidden" class="form-control" id="barcode" name="barcode" placeholder="Barcode" disabled>
                                    <input type="hidden" class="form-control" id="imagen" name="imagen" placeholder="Img" disabled>
                                    <input type="hidden" class="form-control" id="serie" name="serie" placeholder="No.Serie" value="">
                                </div>
                            </div>


                            <div class="row">
                                <div class="col-lg-4 d-none">
                                    <div class="form-group">
                                        <label for="cantidad">Cantidad de salida</label>
                                        <input type="number" class="form-control" id="cantidad" name="cantidad" min="0" placeholder="Cantidad">
                                    </div>
                                </div>
                            </div>

                            <br>

                            <div class="table-responsive overflow-auto scroll-style">
                                <table id="entradas" class="table table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th></th>
                                            <th>Imagen</th>
                                            <th>Producto</th>
                                            <th>Sucursal</th>
                                            <th>Cantidad</th>
                                            <th>Precio</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="6" style="text-align: right;">Total</td>
                                            <td id="total">$0</td>

                                        </tr>
                                    </tfoot>
                                </table>
                            </div>


                            <br>
                            <hr>
                            <br>


                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="motivo_salida">Motivo de salida</label>
                                        <select id="motivo_salida" class="form-control">
                                            <option value="0">Seleccione el motivo</option>
                                            <?php
                                            while ($motivo = $rmotivo->fetch_assoc()) {
                                                echo "<option value='$motivo[pk_motivo_salida]'>$motivo[nombre]</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-8">
                                    <div class="form-group">
                                        <label for="observaciones">Observaciones</label>
                                        <textarea class="form-control h-75" name="observaciones" id="observaciones" rows="3" placeholder="Observaciones"></textarea>
                                    </div>
                                </div>
                            </div>


                            <br>


                            <div class="row d-flex justify-content-center">
                                <div class="row col-lg-3 m-2">
                                    <button id="salida" type="button" class="btn btn-danger m-2"><i class='bx bx-down-arrow-alt'></i>Salida</button>
                                </div>
                            </div>

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
                        <h4 class="modal-title ltse" id="myModalLabel" style="font-size: 24px!important">Seleccione almacén</h4>
                    </div>
                    <div>
                        <button class="btn btn-danger" onclick="$(location).attr('href','verSalidasAlmacen.php')">Cerrar</button>
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

                            $nivel == 1 ? $flsucursal = "" : $flsucursal = " AND rt_sucursales_almacenes.fk_sucursal = $pk_sucursal";

                            $qsucursal = "SELECT ct_sucursales.*,
                                rt_sucursales_almacenes.pk_sucursal_almacen,
                                rt_sucursales_almacenes.nombre as almacen
                            FROM ct_sucursales, rt_sucursales_almacenes
                            WHERE ct_sucursales.estado = 1
                            AND rt_sucursales_almacenes.fk_sucursal = ct_sucursales.pk_sucursal$flsucursal";

                            if (!$rsucursales = $mysqli->query($qsucursal)) {
                                echo "Lo sentimos, esta aplicación está experimentando problemas.";
                                exit;
                            }

                            while ($roweusuario = $rsucursales->fetch_assoc()) {
                                echo <<<HTML
                                    <tr class='odd gradeX fc'>
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



    <div class="modal" id="exito" tabindex="-1" role="dialog" aria-hidden="true" data-bs-keyboard="false" data-bs-backdrop="static">
        <div class="modal-dialog modal-md">

            <div class="modal-content">
                <div class="modal-header text-center" style="background-color: #BFDBFE; color:#000;">
                    <h2 class="text-center exitot">Salida directa <span id="nentrada"></span> </h2>
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
                        <button id="ver_registros" type="button" class="btn btn-sm btn-info">Historial de Salidas</button>
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
    <script src="custom/jquery.numeric.js"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="assets/lib/data-table/datatables.min.js"></script>
    <script src="assets/lib/data-table/dataTables.bootstrap.min.js"></script>
    <script src="assets/lib/data-table/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="assets/loading/loadingoverlay.js"></script>
    <script src="custom/numberFormats.js"></script>
    <script src="custom/salidasAlmacen.js"></script>

    <script>
        $('#dtEmpresa').DataTable({
            responsive: true,
            ordering: true,
            order: [
                [0, 'desc']
            ],
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
