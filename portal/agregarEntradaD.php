<?php
header('Cache-control: private');
include("servicios/conexioni.php");
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

mysqli_set_charset($mysqli, 'utf8');



//SUCURSALES
#region
$nivel != 1 ? $flsucursal = " AND rt_sucursales_almacenes.fk_sucursal = $sucursal" : $flsucursal = "";

$qsucursales = "SELECT ct_sucursales.*,
    rt_sucursales_almacenes.pk_sucursal_almacen,
    rt_sucursales_almacenes.nombre as almacen
    FROM ct_sucursales, rt_sucursales_almacenes
    WHERE ct_sucursales.estado = 1$flsucursal
    AND rt_sucursales_almacenes.fk_sucursal = ct_sucursales.pk_sucursal";

if (!$rsucursales = $mysqli->query($qsucursales)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. 2";
    exit;
}
#endregion



//PRODUCTOS
#region
$qproductos = "SELECT pk_producto, clave, codigobarras, nombre, precio FROM ct_productos WHERE estado = 1";

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
    <link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />

    <link rel="stylesheet" href="css/vertical-layout-light/style.css">
    <link rel="stylesheet" href="css/estilos.css">

    <style>
        .select2-selection__choice {
            display: none;
            /* Oculta las opciones seleccionadas en la caja de texto */
        }
    </style>

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
                            <h4 class="card-title">Nueva entrada directa</h4>
                        </div>

                        <form class="forms-sample">

                            <div class="row filter-box">
                                <div class="row">
                                    <div class="col-lg-6 d-flex justify-content-between d-none">
                                        <div class="col-lg-10 form-group">
                                            <label for="clave">Código de barras</label>
                                            <input type="text" id="clavee" name="clave" placeholder="Código de barras del producto" class="form-control">
                                            <?php echo "<input type='hidden' id='usuario' value='$usuario'/>
                                                <input type='hidden' id='fk_sucursal' value='$sucursal'/>
                                                <input type='hidden' id='ventanaActiva' value='2'/>"; ?>
                                        </div>
                                        <div class="col-lg-2 d-flex justify-content-center align-items-center">
                                            <i class='bx bx-barcode' style="font-size: 36px;"></i>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="form-group gap-3">
                                                <label for="clave" style="font-size: 16px; font-weight: bold;">Agregar producto: </label>
                                                <select id="clave" class="select2 form-control select2-hidden-accessible" style="width: 100%;" multiple>
                                                    <?php
                                                    while ($productos = $rproductos->fetch_assoc()) {
                                                        echo "<option value='$productos[clave]'>$productos[codigobarras] | $productos[nombre] | $$productos[precio]</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label for="">Producto</label>
                                                <input type="text" class="form-control" id="nombred" name="nombred" placeholder="Nombre del producto" disabled>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label for="cantidad">Cantidad</label>
                                                <input type="number" class="form-control" id="cantidad" name="cantidad" placeholder="Cantidad" value="1" min="0">
                                            </div>
                                        </div>
                                    </div>

                                    <input type="hidden" class="form-control" id="costod" name="costod" placeholder="Costo" disabled>
                                    <input type="hidden" class="form-control" id="claveo" name="claveo" placeholder="ID" disabled>
                                    <input type="hidden" class="form-control" id="barcode" name="barcode" placeholder="Barcode" disabled>
                                    <input type="hidden" class="form-control" id="imagen" name="imagen" placeholder="Img" disabled>
                                    <input type="hidden" class="form-control" id="seried" name="seried" disabled>
                                </div>
                            </div>


                            <br>


                            <div class="row">
                                <div class='col-sm-12 col-md-3'>
                                    <div class='form-group'>
                                        <label for='sucursal'><i class='bx bx-chevrons-right fs-5'></i>Almacén de destino</label>
                                        <select id='sucursal' class='form-control'>
                                            <option value='0'>Seleccione</option>
                                            <?php
                                            while ($sucursales = $rsucursales->fetch_assoc()) {
                                                echo "<option value='$sucursales[pk_sucursal_almacen]'>$sucursales[nombre] - $sucursales[almacen]</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>


                            <!--TABLA-->
                            <div class="table-responsive overflow-auto scroll-style">
                                <table id="entradas" class="table table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th></th>
                                            <th>Imagen</th>
                                            <th>Producto</th>
                                            <th>Cantidad</th>
                                            <th>Unitario</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="5" style="text-align: right;">Total</td>
                                            <td id="total">$0</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>


                            <br>


                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="observaciones"><i class='bx bx-edit fs-5'></i>Observaciones</label>
                                        <textarea type="text" class="form-control" id="observaciones" name="observaciones" placeholder="Observaciones" style="height: 100px;"></textarea>
                                    </div>
                                </div>
                            </div>


                            <br>


                            <button id="guardar" type="button" class="btn btn-primary-dast mx-2"><i class="fa fa-save mx-2"></i>Guardar</button>

                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>




    <div class="modal" id="exito" tabindex="-1" role="dialog" aria-hidden="true" data-bs-keyboard="false" data-bs-backdrop="static">
        <div class="modal-dialog modal-md">

            <div class="modal-content">
                <div class="modal-header text-center" style="background-color: #BFDBFE; color:#000;">
                    <h2 class="text-center exitot">Entrada directa <span id="nentrada"></span> </h2>
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
                        <button id="ver_registros" type="button" class="btn btn-sm btn-info">Historial de Entradas</button>
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
    </script>

    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
    <script src="custom/numberFormats.js"></script>
    <script src="custom/agregarEntradaD.js"></script>

</body>

</html>
