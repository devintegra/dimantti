<?php
header('Cache-control: private');
include("servicios/conexioni.php");
@session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
}

$sucursal = 0;
$nivel = $_SESSION["nivel"];
$empresa = $_SESSION["empresa"];

$usuario = $_SESSION["usuario"];


if (isset($_GET['id']) && is_string($_GET['id'])) {
    $pk_contrato = (int)$_GET['id'];
}


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


//DATOS
#region
$qdatos = "SELECT tr_compras.*,
	ct_sucursales.nombre as sucursal,
	rt_sucursales_almacenes.nombre as almacen,
    ct_proveedores.nombre as proveedor
    FROM tr_compras, ct_sucursales, rt_sucursales_almacenes, ct_proveedores
    WHERE tr_compras.pk_compra = $pk_contrato
    AND ct_sucursales.pk_sucursal = tr_compras.fk_sucursal
    AND rt_sucursales_almacenes.pk_sucursal_almacen = tr_compras.fk_almacen
    AND ct_proveedores.pk_proveedor = tr_compras.fk_proveedor;";

if (!$rdatos = $mysqli->query($qdatos)) {
    echo "<br>Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

$datos = $rdatos->fetch_assoc();
$sucursal = $datos["sucursal"];
$almacen = $datos["almacen"];
$proveedor = $datos["proveedor"];
#endregion


//PRODUCTOS
#region
$qproductos = "SELECT tr_compras_detalle.*,
    ct_productos.pk_producto,
    ct_productos.codigobarras,
    ct_productos.nombre,
    ct_productos.precio
    FROM tr_compras_detalle, ct_productos
    WHERE tr_compras_detalle.faltante > 0
    AND tr_compras_detalle.fk_compra = $pk_contrato
    AND ct_productos.pk_producto = tr_compras_detalle.fk_producto";

if (!$rproductos = $mysqli->query($qproductos)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. 1";
    exit;
}
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
                            <h4 class="card-title">Surtir compra <span class="badge-warning-integra"> <?php echo $sucursal . "(" . $almacen . ")" ?> </span> </h4>
                            <i class='bx bx-package' style="font-size:32px"></i>
                        </div>
                        <form class="forms-sample">

                            <div class="row filter-box">
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="form-group gap-3">
                                            <label for="clave" style="font-size: 16px; font-weight: bold;">Agregar producto: </label>
                                            <select id="clave" class="select2 form-control select2-hidden-accessible" style="width: 100%;" multiple>
                                                <option value=""></option>
                                                <?php
                                                while ($productos = $rproductos->fetch_assoc()) {
                                                    echo "<option value='$productos[pk_producto]'>$productos[codigobarras] | $productos[nombre] | $$productos[precio]</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="">Producto</label>
                                            <input type="text" class="form-control" id="nombred" name="nombred" placeholder="Nombre del producto" disabled>
                                        </div>
                                    </div>
                                </div>


                                <div class="row d-none">
                                    <?php echo "<input type='hidden' id='compra' value='$pk_contrato'/>
                                        <input type='hidden' id='usuario' value='$usuario'/>";
                                    ?>
                                    <input type="hidden" class="form-control" id="costod" name="costod">
                                    <input type="hidden" class="form-control" id="imagen" name="imagen">
                                    <input type="hidden" class="form-control" id="claveo" name="claveo">
                                    <input type="hidden" class="form-control" id="barcode" name="barcode">
                                    <input type="hidden" class="form-control" id="seried" name="seried" value="">
                                    <input type="hidden" class="form-control" id="cantidad" name="cantidad">
                                    <input type="hidden" class="form-control" id="cantidadtmp">
                                </div>
                            </div>


                            <br>


                            <div class="table-responsive overflow-hidden">
                                <table id="entradas" class="table table-striped overflow-x-auto">
                                    <thead class="table-dark">
                                        <tr>
                                            <th></th>
                                            <th>Imagen</th>
                                            <th>Producto</th>
                                            <th>Cantidad</th>
                                            <th>Precio final</th>
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


                            <br>


                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="observaciones">Observaciones</label>
                                        <textarea class="form-control" id="observaciones" cols="30" rows="10" placeholder="Observaciones" style="height: 100px;"></textarea>
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


    <div class="modal" id="exito" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
        <div class="modal-dialog modal-md">

            <div class="modal-content">
                <div class="modal-header text-center" style="background-color: #BFDBFE; color:#000;">
                    <h2 class="text-center exitot">Entrada <span id="nentrada"></span> </h2>
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
                        <button id="nueva" type="button" class="btn btn-sm btn-info">Historial de Compras</button>
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
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
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
    <script src="custom/numberFormats.js"></script>
    <script src="custom/surtirCompra.js"></script>
</body>

</html>
