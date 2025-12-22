<?php
header('Cache-control: private');
include("servicios/conexioni.php");
mysqli_set_charset($mysqli, 'utf8');
@session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
}

$nivel = $_SESSION["nivel"];
$empresa = $_SESSION["empresa"];

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

    <!-- partial -->
    <div class="main-panel">

        <div class="row justify-content-center">
            <div class="col-lg-10 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title">Nueva sucursal</h4>
                            <i class='bx bx-store' style="font-size:32px"></i>
                        </div>
                        <form class="forms-sample" enctype="multipart/form-data" id="formuploadajax">

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="nombre">Nombre</label>
                                        <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre" autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="clave">Clave</label>
                                        <input type="text" class="form-control" id="clave" name="clave" placeholder="Clave (3 caracteres)" autocomplete="off">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="correo">Correo</label>
                                        <input type="text" class="form-control" id="correo" name="correo" placeholder="Correo" autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="telefono">Teléfono</label>
                                        <input type="text" class="form-control" id="telefono" name="telefono" placeholder="Teléfono" autocomplete="off">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="direccion">Dirección</label>
                                        <input type="text" class="form-control" id="direccion" name="direccion" placeholder="Dirección" autocomplete="off">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12 form-group" style="height: 200px;">
                                    <?php
                                    echo "<input type='hidden' id='empresa' name='text-input' value='$empresa'>";
                                    ?>
                                    <div id="mapa" style="height: 100%;"></div>
                                </div>
                            </div>

                            <br>
                            <div class="line-primary-integra"></div><br>

                            <div class="row d-flex">
                                <h4 class="card-title col-lg-4" style="color: #2563EB; font-weight: bold;"><i class='bx bx-coin-stack fs-5'></i>Gastos asignados</h4>
                                <span class="fa fa-search btn-rounded-integra col-lg-4" id="buscar" style="cursor: pointer;"></span>
                            </div>

                            <p class="fs-6">Selecciona los gastos monetarios que puede realizar esta sucursal</p>

                            <br>

                            <div class="table-responsive overflow-auto scroll-style" style="width: 102%;">
                                <table id="entradas" class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>Motivo de gasto</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>



                            <br>
                            <div class="line-success-integra"></div><br>

                            <div class="row d-flex flex-column">
                                <h4 class="card-title col-lg-4" style="color: #2CA880; font-weight: bold;"><i class='bx bx-store-alt fs-5'></i>Almácenes</h4>
                                <p class="fs-6">Agregar un nuevo almacén interno a la sucursal</p>
                                <br>
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <input type="text" class="form-control" id="almacensc" placeholder="Nombre del almacén" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <input type="text" class="form-control" id="descripcion_almacen" placeholder="Descripción del almacén" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <button id="agregar" type="button" class="btn btn-success-dast mx-2"><i class="fa fa-plus mx-2"></i>Agregar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive overflow-auto scroll-style" style="width: 102%;">
                                <table id="entradasAlmacen" class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>Nombre</th>
                                            <th>Descripción del almácen</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        <tr>
                                            <td></td>
                                            <td>Principal</td>
                                            <td>Almacén generado por defecto</td>
                                        </tr>

                                    </tbody>
                                </table>
                            </div>

                            <br><br>

                            <button id="guardar" type="button" class="btn btn-primary-dast mx-2"><i class="fa fa-save mx-2"></i>Guardar</button>

                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>



    <div class="modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static" id="modalMotivos">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-between align-items-center" style="background-color: #16A34A;">
                    <div class="d-flex justify-content-start align-items-center">
                        <i class='bx bx-coin-stack mx-2 fs-1'></i>
                        <h4 class="modal-title ltse" id="myModalLabel" style="font-size: 24px!important">Seleccione el motivo de gasto</h4>
                    </div>
                    <div>
                        <button class="btn btn-primary btn-sm" type="button" onclick="$('#modalMotivos').modal('hide');">X</button>
                    </div>
                </div>
                <div class="modal-body" id="tablaInsumos">
                    <table id="dtInsumos" class="table table-striped">
                        <thead class='table-light'>
                            <tr>
                                <th>Motivo de gasto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $qmotivos = "SELECT * FROM ct_retiros where estado=1";

                            if (!$rmotivos = $mysqli->query($qmotivos)) {
                                echo "Lo sentimos, esta aplicación está experimentando problemas.";
                                exit;
                            }

                            while ($motivos = $rmotivos->fetch_assoc()) {
                                echo <<<HTML
                                    <tr class="odd gradeX fp" id='$motivos[pk_retiro]'>
                                        <td style='white-space:normal'>$motivos[nombre]</td>
                                    </tr>
                                HTML;
                            }
                            ?>
                        </tbody>
                    </table>
                    <div class="row">&nbsp;</div>
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
    <script src="assets/lib/data-table/datatables-init.js"></script>

    <script src="custom/jquery.maskedinput.js"></script>
    <script type="text/javascript" src="https://maps.google.com/maps/api/js?key=AIzaSyAyzxxx9sOTmkGCHGgrK_Xy86eQxB-AxuI&libraries=places"></script>
    <script src="assets/loading/loadingoverlay.js"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="custom/agregarSucursal.js"></script>

    <script>
        $('#dtInsumos').DataTable({
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
        })
    </script>

</body>

</html>
