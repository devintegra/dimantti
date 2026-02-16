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

$pk_sucursal = 0;

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

//RETIROS
#region
$qtipos = "SELECT * FROM ct_retiros where estado=1";

if (!$rtipos = $mysqli->query($qtipos)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}
#endregion


//SUCURSALES
#region
if ($pk_sucursal != 0) {
    $qsucursal = "SELECT * FROM ct_sucursales WHERE pk_sucursal = $pk_sucursal";

    if (!$resultado = $mysqli->query($qsucursal)) {
        echo "<br>Lo sentimos, esta aplicación está experimentando problemas.";
        exit;
    }
    $rowexistead = $resultado->fetch_assoc();
    $nombre_sucursal = $rowexistead["nombre"];
}
#endregion


//PAGOS
#region
$qpagos = "SELECT * FROM ct_pagos WHERE estado=1";

if (!$rpagos = $mysqli->query($qpagos)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
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

    <!-- partial -->
    <div class="main-panel">

        <div class="row justify-content-center">
            <div class="col-lg-10 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title">Nuevo retiro caja <span id="lasucursal" class="badge-warning-integra"> <?php echo $nombre_sucursal ?> </span> </h4>
                            <i class='bx bx-coin' style="font-size:32px"></i>
                        </div>
                        <form class="forms-sample">

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <?php
                                        echo "<input type='hidden' id='sucursal' value='$pk_sucursal'><input type='hidden' id='fk_usuario' value='$usuario'>";
                                        ?>
                                        <label for="tipo">Tipo</label>
                                        <select id="tipo" class="form-control">
                                            <option value="0">Seleccione</option>

                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="descripcion">Descripción</label>
                                        <input type="text" id="descripcion" name="descripcion" placeholder="Descripción" class="form-control" autocomplete="off">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="tipo_pago">Tipo pago</label>
                                        <select id='tipo_pago' class="form-control">
                                            <option value="0">Seleccione</option>
                                            <?php
                                            while ($pagos = $rpagos->fetch_assoc()) {
                                                echo "<option value='$pagos[pk_pago]'>$pagos[nombre]</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="cantidad">Monto</label>
                                        <input type="text" id="cantidad" name="cantidad" placeholder="Monto" class="form-control" autocomplete="off">
                                    </div>
                                </div>
                            </div>

                            <button id="guardar" type="button" class="btn btn-primary-dast mx-2"><i class="fa fa-save mx-2"></i>Guardar</button>

                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>




    <div class="modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-bs-backdrop="static" id="modalSucursales">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-between align-items-center" style="background-color: #16A34A;">
                    <div class="d-flex justify-content-start align-items-center">
                        <i class='bx bx-store-alt mx-2 fs-1'></i>
                        <h4 class="modal-title ltse" id="myModalLabel" style="font-size: 24px!important">Seleccione sucursal</h4>
                    </div>
                    <div>
                        <button class="btn btn-danger" onclick="$(location).attr('href','verRetiros.php')">Cerrar</button>
                    </div>
                </div>
                <div class="modal-body">
                    <table id="tablaClientes" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php

                            $qsucursales = "SELECT * FROM ct_sucursales where estado=1";

                            if (!$rsucursales = $mysqli->query($qsucursales)) {
                                echo "Lo sentimos, esta aplicación está experimentando problemas.";
                                exit;
                            }

                            while ($sucursales = $rsucursales->fetch_assoc()) {
                                echo "<tr id='$sucursales[pk_sucursal]' class='suc'><td>$sucursales[nombre]</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


    <div class="modal" id="exito" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
        <div class="modal-dialog modal-md">

            <div class="modal-content">
                <div class="modal-header text-center" style="background-color: #BFDBFE; color:#000;">
                    <h2 class="text-center exitot">Retiro <span id="nentrada"></span> </h2>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <a target="_blank" id="pdf" class="btn btn-danger pdfb"><i class="fa fa-file-pdf-o fa-5x" aria-hidden="true"></i><br><br>DESCARGAR RETIRO</a>
                        </div>
                    </div>
                </div>
                <div class="modal-footer text-center">
                    <div class="col-sm-12">
                        <button id="nueva" type="button" class="btn btn-sm btn-warning">Nuevo</button>
                        <button id="ver_registros" type="button" class="btn btn-sm btn-info">Historial de retiros</button>
                        <button id="inicio" type="button" class="btn btn-sm btn-success">Inicio</button>
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
    <script src="assets/lib/data-table/dataTables.buttons.min.js"></script>
    <script src="assets/lib/data-table/datatables-init.js"></script>
    <script src="custom/jquery.numeric.js"></script>
    <script src="custom/jquery.maskedinput.js"></script>
    <script src="assets/loading/loadingoverlay.js"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="custom/numberFormats.js"></script>
    <script src="custom/agregarRetiro.js"></script>

    <script>
        $('#tablaClientes').DataTable({
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
</body>

</html>
