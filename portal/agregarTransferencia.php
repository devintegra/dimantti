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


//SUCURSALES
#region
$filtro_sucursal = "";
if ($sucursal > 0) {
    $filtro_sucursal = " AND rt_sucursales_almacenes.fk_sucursal = $sucursal";
}

$qproveedores = "SELECT ct_sucursales.*,
    rt_sucursales_almacenes.pk_sucursal_almacen,
    rt_sucursales_almacenes.nombre as almacen
    FROM ct_sucursales, rt_sucursales_almacenes
    WHERE ct_sucursales.estado = 1
    AND rt_sucursales_almacenes.fk_sucursal = ct_sucursales.pk_sucursal";

if (!$rproveedores = $mysqli->query($qproveedores)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}
#endregion


//PRODUCTOS
#region
$qproductos = "SELECT * FROM ct_productos where estado = 1";

if (!$rproductos = $mysqli->query($qproductos)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
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
                            <h4 class="card-title">Nueva transferencia</h4>
                            <h4 class="card-title"><span id="lasucursal" class="badge-warning-integra"></span></h4>
                        </div>
                        <form class="forms-sample">

                            <div class="row">
                                <div class="col-sm-12 col-md-3">
                                    <div class="form-group">
                                        <label for="proveedor">Almacén de destino</label>
                                        <select id='proveedor' class="form-control">
                                            <option value="0">Seleccione almacén destino</option>
                                            <?php
                                            while ($proveedores = $rproveedores->fetch_assoc()) {
                                                echo "<option value='$proveedores[pk_sucursal_almacen]'>$proveedores[nombre] - $proveedores[almacen]</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <br>
                            <div class="line-primary-integra"></div>

                            <div class="row filter-box">
                                <div class="row">
                                    <div class="col-lg-6 d-flex justify-content-between">
                                        <div class="col-lg-10 form-group">
                                            <label for="clave">Código de barras</label>
                                            <select id="clave" class="select2 form-control select2-hidden-accessible" style="width: 100%;" multiple>

                                            </select>

                                            <input type="hidden" class="form-control" id="codigobarras" name="codigobarras" placeholder="Código de barras del producto">
                                            <input type="hidden" id="claveo" value="0">
                                            <input type="hidden" id="imagen" value="0">
                                            <input type="hidden" class="form-control" id="nombre" name="nombre" placeholder="Nombre del producto" disabled>
                                            <input type="hidden" id="serie" name="serie">
                                            <input type="hidden" id="existencias" name="existencias" class="form-control">
                                            <input type="hidden" id="cantidad" name="text-input" class="form-control">
                                            <input type="hidden" id="precio" name="precio" placeholder="Precio" class="form-control" disabled>
                                            <?php
                                            echo "<input type='hidden' id='sucursal' value='$sucursal'>
                                            <input type='hidden' id='usuario' value='$usuario'>";
                                            ?>
                                        </div>
                                        <div class="col-lg-2 d-flex justify-content-center align-items-center"><i class='bx bx-barcode' style="font-size: 36px;"></i></div>
                                    </div>
                                </div>
                            </div>

                            <br>
                            <div class="line-primary-integra"></div><br>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="observaciones">Observaciones</label>
                                        <textarea class="form-control" id="observaciones" cols="30" rows="5" style="height: 80px;" placeholder="Escribe una observación de ser necesario"></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive overflow-hidden">
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
                                            <td colspan="5" style="text-align: right;">Total: $</td>
                                            <td>
                                                <input type="text" id="total" class="form-control" disabled>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>


                            <br>


                            <button id="guardar" type="button" class="btn btn-primary-dast mx-2"><i class="fa fa-save mx-2"></i>Guardar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>



    <div class="modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-bs-backdrop="static" id="modalEmpresa">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-between align-items-center" style="background-color: #16A34A;">
                    <div class="d-flex justify-content-start align-items-center">
                        <i class='bx bx-store-alt mx-2 fs-1'></i>
                        <h4 class="modal-title ltse" id="myModalLabel" style="font-size: 24px!important">Seleccione almacén de origen</h4>
                    </div>
                    <div>
                        <button class="btn btn-danger" onclick="$(location).attr('href','verTransferencias.php')">Cerrar</button>
                    </div>
                </div>
                <div class="modal-body" id="tablaEmpresa">

                    <?php
                    $qsucursal = "SELECT ct_sucursales.*,
                        rt_sucursales_almacenes.pk_sucursal_almacen,
                        rt_sucursales_almacenes.nombre as almacen
                        FROM ct_sucursales, rt_sucursales_almacenes
                        WHERE ct_sucursales.estado = 1
                        AND rt_sucursales_almacenes.fk_sucursal = ct_sucursales.pk_sucursal$filtro_sucursal";

                    echo "<table id='dtSucursales' class='table table-striped'>
                                            <thead>
                                                <tr>
                                                    <th>Sucursal - Almacén</th>
                                                </tr>
                                            </thead><tbody>";
                    if (!$rsucursales = $mysqli->query($qsucursal)) {
                        echo "Lo sentimos, esta aplicación está experimentando problemas.";
                        exit;
                    }


                    while ($rowsucursal = $rsucursales->fetch_assoc()) {

                        echo "<tr class='odd gradeX fs' id='sucursal*-*$rowsucursal[pk_sucursal_almacen]'>
                                                        <td>$rowsucursal[nombre] - $rowsucursal[almacen]</td>
                                            </tr>";
                    }


                    echo "</tbody></table><div class='row'>&nbsp;</div>";

                    ?>

                </div>

            </div>
        </div>
    </div>


    <div class="modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static" id="modalProductos">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title ltse" id="myModalLabel" style="font-size: 14px!important">Seleccione producto</h4>
                </div>
                <div class="modal-body" id="tablaProductos">

                    <table id='dtProductos' class='table table-striped'>
                        <thead>
                            <tr>
                                <th>Existencia</th>
                                <th>Clave</th>
                                <th>Nombre</th>
                                <th>Serie</th>
                                <th>Precio</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


    <div class="modal" id="exito" tabindex="-1" role="dialog" aria-hidden="true" data-bs-keyboard="false" data-bs-backdrop="static">
        <div class="modal-dialog modal-md">

            <div class="modal-content">
                <div class="modal-header text-center" style="background-color: #BFDBFE; color:#000;">
                    <h2 class="text-center exitot">Transferencia <span id="nentrada"></span> </h2>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <a target="_blank" id="pdf" class="btn btn-danger pdfb"><i class="fa fa-file-pdf-o fa-5x" aria-hidden="true"></i><br><br>DESCARGAR PDF</a>
                        </div>
                    </div>
                </div>
                <div class="modal-footer text-center">
                    <div class="col-sm-12">
                        <button id="nueva" type="button" class="btn btn-sm btn-warning">Nueva</button>
                        <button id="ver_registros" type="button" class="btn btn-sm btn-info">Historial de transferencias</button>
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
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="assets/lib/data-table/datatables.min.js"></script>
    <script src="assets/lib/data-table/dataTables.bootstrap.min.js"></script>
    <script src="assets/lib/data-table/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="custom/jquery.numeric.js"></script>
    <script src="assets/loading/loadingoverlay.js"></script>
    <script src="custom/agregarTransferencia.js"></script>

    <script>
        $('#dtSucursales').DataTable({
            responsive: true,
            ordering: true,
            language: {
                "lengthMenu": "Mostrando _MENU_ registros por pagina",
                "search": "Buscar:",
                "zeroRecords": "No hay registroa",
                "info": "Mostrando pagina _PAGE_ de _PAGES_",
                "infoEmpty": "Sin registros disponibles",
                "infoFiltered": "(filtrando de _MAX_ registros totales)",
                "paginate": {
                    "previous": "Anterior",
                    "next": "Siguiente"
                }
            }

        });

        $('#dtProductos').DataTable({
            responsive: true,
            ordering: true,
            language: {
                "lengthMenu": "Mostrando _MENU_ registros por pagina",
                "search": "Buscar:",
                "zeroRecords": "No hay registroa",
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

</html>
