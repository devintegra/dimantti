<?php
header('Cache-control: private');
include("servicios/conexioni.php");
@session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
}

$nivel = $_SESSION["nivel"];
$empresa = $_SESSION["empresa"];
$fk_usuario = $_SESSION["usuario"];
$sucursal = 0;

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

$fa = "";
if ($sucursal != 0) {
    $fa = " AND tr_transferencias.fk_sucursal = $sucursal OR tr_transferencias.fk_sucursal_destino = $sucursal";
}


//SUCURSALES
$qsucursales = "SELECT * FROM ct_sucursales where estado=1";

if (!$rsucursales = $mysqli->query($qsucursales)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

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
                            <h4 class="card-title">Transferencias</h4>
                            <?php
                            if ($nivel == 1) {
                                echo "<a href='agregarTransferencia.php'><button type='button' class='btn btn-social-icon-text btn-add'><i class='bx bx-plus'></i>Nueva transferencia</button></a>";
                            }
                            ?>
                        </div>

                        <form class="forms-sample">

                            <div class="row filter-box">
                                <h4 class="card-title">Seleccione criterios de búsqueda</h4>
                                <div class="row">
                                    <?php
                                    echo "<input type='hidden' id='fk_usuario' value='$fk_usuario'>";
                                    if ($nivel == 1) {
                                        echo "
                                            <div class='col col-lg-4'>
                                                <div class='form-group'>
                                                    <label for='sucursal' class='d-flex align-items-center gap-2'><i class='bx bx-store-alt fs-5'></i>Sucursal</label>
                                                    <select id='sucursal' class='form-control'>
                                                        <option value='0'>Todas</option>";

                                        while ($sucursales = $rsucursales->fetch_assoc()) {
                                            echo "<option value='$sucursales[pk_sucursal]'>$sucursales[nombre]</option>";
                                        }

                                        echo " </select></div></div>";
                                    } else {
                                        echo "<input id='sucursal' type='hidden' value='$sucursal'>";
                                    }

                                    ?>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="inicio" class="d-flex align-items-center gap-2"><i class='bx bx-calendar fs-5'></i>Fecha de Inicio</label>
                                            <input type="date" id="inicio" name="inicio" placeholder="Inicio" class="form-control" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="fin" class="d-flex align-items-center gap-2"><i class='bx bx-calendar fs-5'></i>Fecha de Fin</label>
                                            <input type="date" id="fin" name="fin" placeholder="Inicio" class="form-control" autocomplete="off">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-4">
                                        <button id="buscar" type="button" class="btn btn-info-dast"><i class="bx bx-search mx-2"></i>Buscar</button>
                                    </div>
                                </div>

                            </div>

                            <div class="table-responsive overflow-hidden" id="tabla">
                                <table id="dtEmpresa" class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Origen</th>
                                            <th>Destino</th>
                                            <th>Fecha</th>
                                            <th>Usuario</th>
                                            <th>Observaciones</th>
                                            <th>Acciones</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tfoot style='display: table-header-group'>
                                        <tr>
                                            <th>Origen</th>
                                            <th>Destino</th>
                                            <th>Fecha</th>
                                            <th>Usuario</th>
                                            <th>Observaciones</th>
                                            <th></th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                    <tbody>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="5"></td>
                                            <td style='background-color: #A8F991; font-weight:bold;'>TOTAL:</td>
                                            <td style='background-color: #A8F991; font-weight:bold;' id="total">0.00</td>
                                        </tr>
                                    </tfoot>
                                </table>
                                <div class="row">&nbsp;</div>
                            </div>

                            <button id="imprimir" type="button" class="btn btn-primary-dast"><i class="bx bx-printer mx-2"></i>Imprimir</button>

                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>



    <div class="modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-bs-backdrop="static" id="modalTransferencia">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-between align-items-center" style="background-color: #5468ff;">
                    <div class="d-flex justify-content-start align-items-center">
                        <i class='bx bx-chevrons-down mx-2 fs-1'></i>
                        <h4 class="modal-title ltse" id="myModalLabel" style="font-size: 24px!important">Seguimiento de la transferencia</h4>
                    </div>
                    <div>
                        <button class="btn btn-danger" onclick="$('#modalTransferencia').modal('hide')">Cerrar</button>
                    </div>
                </div>
                <div class="modal-body">

                    <div class="row">
                        <div class="col-sm-12 col-lg-6">
                            <div class="form-group">
                                <label for="clave" class="form-label"> <i class="bx bx-barcode fs-4"></i> Código del producto</label>
                                <input type="text" class="form-control" id="clave" name="clave" placeholder="Ingrese el código de barras del producto">
                                <input type="hidden" class="form-control" id="fk_transferencia">
                                <input type="hidden" class="form-control" id="accion">
                                <input type="hidden" class="form-control" id="fk_sucursal_origen">
                                <input type="hidden" class="form-control" id="fk_almacen_origen">
                                <input type="hidden" class="form-control" id="fk_sucursal_destino">
                                <input type="hidden" class="form-control" id="fk_almacen_destino">
                            </div>
                        </div>

                        <div class="col-sm-12 col-lg-6">
                            <div class="form-group">
                                <label for="clave" class="form-label"> <i class="bx bx-list-ul fs-4"></i> N°de serie</label>
                                <input type="text" class="form-control" id="serie" name="serie" placeholder="Ingrese la serie del producto">
                            </div>
                        </div>
                    </div>

                    <br>

                    <h4 class="card-title text-center">Transferidos</h4>
                    <div class="table-responsive overflow-hidden filter-box" id="tablaPrestamos">
                    </div>

                    <br>

                    <h4 class="card-title text-center">A recibir o devolver</h4>
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

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label for="observaciones">Observaciones</label>
                                <textarea class="form-control" id="observaciones" cols="30" rows="10" style='height: 100px;' placeholder="Escribe algunas observaciones importantes aquí..."></textarea>
                            </div>
                        </div>
                    </div>

                    <br><br>

                </div>

                <div class="modal-footer">

                    <button id="guardarDevolucion" type="button" class="btn btn-primary-dast mx-2"><i class="fa fa-save mx-2"></i>Devolver productos</button>

                </div>
            </div>
        </div>
    </div>


    <div class="modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-bs-backdrop="static" id="modalHistorial">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-between align-items-center" style="background-color: #9032bb;">
                    <div class="d-flex justify-content-start align-items-center">
                        <i class='bx bx-history mx-2 fs-1'></i>
                        <h4 class="modal-title ltse" id="myModalLabel" style="font-size: 24px!important">Historial de transferencia</h4>
                    </div>
                    <div>
                        <button class="btn btn-danger" onclick="$('#modalHistorial').modal('hide')">Cerrar</button>
                    </div>
                </div>
                <div class="modal-body">

                    <div class="table-responsive overflow-y-hidden overflow-x-auto scroll-style" id="tablaHistorial">

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
    <script src="js/loading/loadingoverlay.min.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="custom/verTransferencias.js"></script>

    <script>
        //INPUTS DE FILTRADO POR COLUMNA
        $('#dtEmpresa tfoot th').each(function() {
            var title = $(this).text().trim();
            if (title != '') {
                $(this).html('<input type="text" class="form-control" placeholder="Buscar ' + title + '" />');
            }
        });

        $('#dtEmpresa').DataTable({
            initComplete: function() {
                // Aplicar la búsqueda
                this.api()
                    .columns()
                    .every(function() {
                        var that = this;

                        $('input', this.footer()).on('keyup change clear', function() {
                            if (that.search() !== this.value) {
                                that.search(this.value).draw();
                            }
                        });
                    });
            },

            responsive: true,
            ordering: true,
            pageLength: 10,
            "order": [
                [2, "desc"]
            ],
            dom: '<"dtEmpresa_header"lf><t><rip>',
            //lfptrip
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

</html>
