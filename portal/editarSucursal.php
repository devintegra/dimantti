<?php
header('Cache-control: private');
include("servicios/conexioni.php");
mysqli_set_charset($mysqli, 'utf8');
@session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
}

$nivel = $_SESSION["nivel"];

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

if (isset($_GET['id']) && is_string($_GET['id'])) {
    $pk_cliente = (int)$_GET['id'];
}


//SUCURSAL
#region
$eusuario = "SELECT * FROM ct_sucursales WHERE pk_sucursal = $pk_cliente";

if (!$resultado = $mysqli->query($eusuario)) {
    echo "<br>Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

$rowexistead = $resultado->fetch_assoc();
$nombre = $rowexistead["nombre"];
$direccion = $rowexistead["direccion"];
$latitud = $rowexistead["latitud"];
$longitud = $rowexistead["longitud"];
$telefono = $rowexistead["telefono"];
$correo = $rowexistead["correo"];
$clave = $rowexistead["iniciales"];
#endregion


//RETIROS DE LA SUCURSAL
#region
$qinsumos = "SELECT rt_sucursales_motivos.pk_sucursal_motivo as pk_sucursal_motivo,
        rt_sucursales_motivos.fk_retiro as fk_retiro,
        ct_retiros.nombre as nombre
    FROM rt_sucursales_motivos, ct_retiros
    WHERE rt_sucursales_motivos.fk_sucursal = $pk_cliente
    AND rt_sucursales_motivos.estado = 1
    AND ct_retiros.pk_retiro = rt_sucursales_motivos.fk_retiro";
#endregion


//ALMACENES
#region
$qalmacen = "SELECT * FROM rt_sucursales_almacenes WHERE fk_sucursal = $pk_cliente and estado = 1";
#endregion


//MOTIVOS DE RETIRO
#region
$qmotivos = "SELECT * FROM ct_retiros where estado=1";
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

    <!-- partial -->
    <div class="main-panel">

        <div class="row justify-content-center">
            <div class="col-lg-10 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title">Editar sucursal</h4>
                            <i class='bx bx-store' style="font-size:32px"></i>
                        </div>
                        <form class="forms-sample" enctype="multipart/form-data" id="formuploadajax">

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="nombre">Nombre</label>
                                        <?php
                                        echo "<input type='text' id='nombre' name='nombre' placeholder='Nombre' class='form-control' value='$nombre' autocomplete='off'>";
                                        ?>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="clave">Clave</label>
                                        <?php
                                        echo "<input type='text' id='clave' name='clave' placeholder='Clave (3 caracteres)' class='form-control' value='$clave' autocomplete='off'>";
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="correo">Correo</label>
                                        <?php
                                        echo "<input type='email' id='correo' name='correo' placeholder='Correo' class='form-control' value='$correo'>";
                                        ?>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="telefono">Teléfono</label>
                                        <?php
                                        echo "<input type='text' id='telefono' name='telefono' placeholder='Teléfono' class='form-control' value='$telefono'>";
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="direccion">Dirección</label>
                                        <?php
                                        echo "<input type='text' id='direccion' name='direccion' placeholder='Dirección' class='form-control' value='$direccion'>";
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12 form-group" style="height: 200px;">
                                    <?php
                                    echo "<input type='hidden' id='latitud' name='text-input' value='$latitud'>
                                            <input type='hidden' id='longitud' name='text-input' value='$longitud'>
                                            <input type='hidden' id='id' name='text-input' value='$pk_cliente'>";
                                    ?>
                                    <div id="mapa" style="height: 100%;"></div>
                                </div>
                            </div>

                            <div class="line-primary-integra"></div>

                            <div class="row d-flex">
                                <h4 class="card-title col-lg-4" style="color: #2563EB; font-weight: bold;"><i class='bx bx-coin-stack fs-5'></i>Gastos asignados</h4>
                                <span class="fa fa-search btn-rounded-integra col-lg-4" id="buscar" style="cursor: pointer;"></span>
                            </div>

                            <p class="fs-6">Selecciona los gastos monetarios que puede realizar esta sucursal</p>

                            <br>

                            <div class="table-responsive overflow-auto scroll-style" style="width: 102%;">
                                <table id='entradas' class='table table-striped'>
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>Motivo de gasto</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if (!$rinsumos = $mysqli->query($qinsumos)) {
                                            echo "Lo sentimos, esta aplicación está experimentando problemas.1";
                                            exit;
                                        }

                                        while ($insumos = $rinsumos->fetch_assoc()) {
                                            echo <<<HTML
                                                <tr id='$insumos[fk_retiro]'>
                                                    <td>
                                                        <i class='bx bx-trash eliminar' style='background-color: red; padding: 3px; color: white; cursor:pointer;' onclick='eliminarInsumoTabla(`$insumos[fk_retiro]`)'></i>
                                                    </td>
                                                    <td>
                                                        $insumos[nombre]
                                                    </td>
                                                </tr>
                                            HTML;
                                        }
                                        ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>


                            <div id="contentAlmacenes" class="d-none">
                                <br>
                                <div class="line-success-integra"></div>
                                <br>

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
                                    <table id='entradasAlmacen' class='table table-striped'>
                                        <thead>
                                            <tr>
                                                <th></th>
                                                <th>Nombre</th>
                                                <th>Descripción del almacén</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            if (!$ralmacen = $mysqli->query($qalmacen)) {
                                                echo "Lo sentimos, esta aplicación está experimentando problemas.1";
                                                exit;
                                            }

                                            while ($almacen = $ralmacen->fetch_assoc()) {

                                                $btn_eliminar = "<i class='bx bx-trash eliminar' style='background-color: red; padding: 3px; color: white; cursor:pointer;' onclick='eliminarInsumoTablaAlmacen($almacen[pk_sucursal_almacen])'></i>";

                                                echo <<<HTML
                                                    <tr>
                                                        <td>

                                                        </td>
                                                        <td>
                                                            $almacen[nombre]
                                                        </td>
                                                        <td>
                                                            $almacen[descripcion]
                                                        </td>
                                                    </tr>
                                                HTML;
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>


                            <br><br>


                            <div class="row d-flex justify-content-center">
                                <div class="row col-lg-4 m-2">
                                    <button id="eliminar" type="button" class="btn btn-danger mx-2 d-flex justify-content-center align-items-center"><i class='bx bx-x-circle mx-2' style="font-size: 20px;"></i>Eliminar</button>
                                </div>
                                <div class="row col-lg-4 m-2">
                                    <button id="guardar" type="button" class="btn btn-primary-dast mx-2"><i class="fa fa-save mx-2"></i>Guardar</button>
                                </div>
                            </div>

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
    <script src="custom/jquery.confirm.js"></script>
    <script src="custom/jquery.maskedinput.js"></script>
    <script type="text/javascript" src="https://maps.google.com/maps/api/js?key=AIzaSyAyzxxx9sOTmkGCHGgrK_Xy86eQxB-AxuI&libraries=places"></script>
    <script src="assets/loading/loadingoverlay.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="custom/editarSucursal.js"></script>

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
