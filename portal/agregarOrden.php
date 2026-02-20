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
    $qusuarios = "SELECT * FROM ct_usuarios where estado=1";
}

if ($nivel == 2) {
    $tipo = "Admin sucursal";
    $menu = "fragments/menub.php";
    $sucursal = $_SESSION["pk_sucursal"];
    $qusuarios = "SELECT * FROM ct_usuarios where estado=1 and fk_sucursal=$sucursal";
}

if ($nivel == 3) {
    $tipo = "Técnico";
    $menu = "fragments/menuc.php";
    $sucursal = $_SESSION["pk_sucursal"];
    $qusuarios = "SELECT * FROM ct_usuarios where estado=1 and fk_sucursal=$sucursal";
}

if ($nivel == 4) {
    $tipo = "Vendedor";
    $menu = "fragments/menud.php";
    $sucursal = $_SESSION["pk_sucursal"];
    $qusuarios = "SELECT * FROM ct_usuarios where estado=1 and fk_sucursal=$sucursal";
}

if ($nivel != 1 && $nivel != 3 && $nivel != 4) {
    header('Location: ../index.php');
}

mysqli_set_charset($mysqli, 'utf8');


//CLIENTES
#region
$mysqli->next_result();
if (!$rsp_get_clientes = $mysqli->query("CALL sp_get_clientes()")) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}
#endregion



//PAGOS
#region
$mysqli->next_result();
if (!$rsp_get_pagos = $mysqli->query("CALL sp_get_pagos()")) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. 2";
    exit;
}
#endregion



//CATEGORIAS
#region
$mysqli->next_result();
if (!$rsp_get_categorias = $mysqli->query("CALL sp_get_categorias()")) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. 4";
    exit;
}
#endregion



//SUCURSALES
#region
$mysqli->next_result();
if (!$rsp_get_sucursales = $mysqli->query("CALL sp_get_sucursales()")) {
    echo "<br>Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}
#endregion



//SUCURSAL
#region
$mysqli->next_result();
if (!$rsp_get_sucursal = $mysqli->query("CALL sp_get_sucursal($sucursal)")) {
    echo "<br>Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

$rows = $rsp_get_sucursal->fetch_assoc();
$sucursal_nom = $rows["nombre"];
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
    <link rel="stylesheet" href="css/estilosWizard.css">

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
                            <h4 class="card-title">Nueva orden</h4>
                            <h4 class="card-title badge-warning-integra" id="lasucursal"><?php echo $sucursal_nom ?></h4>
                            <?php
                            echo <<<HTML
                                <input type='hidden' id='sucursal' value='$sucursal'/>
                                <input type='hidden' id='usuario' value='$usuario'/>
                            HTML;
                            ?>
                        </div>
                        <form class="forms-sample form" enctype="multipart/form-data" id="formuploadajax">

                            <!-- Progress bar -->
                            <div class="progressbar">
                                <div class="progress" id="progress"></div>
                                <div class="progress-step progress-step-active"></div>
                                <div class="progress-step"></div>
                                <div class="progress-step"></div>
                            </div>

                            <!--SETP 1-->
                            <div class="form-step form-step-active">
                                <div class="row input-group">
                                    <div class="col-lg-6 d-flex justify-content-between">
                                        <div class="col-lg-10 form-group">
                                            <label for="clientenombre">Nombre</label>
                                            <input type="hidden" id="cliente">
                                            <input type="text" id="clientenombre" name="nombrec" placeholder="Nombre del cliente" class="form-control" autocomplete="off">
                                        </div>
                                        <div class="col-lg-2 d-flex justify-content-center align-items-center">
                                            <span class="fa fa-search btn-rounded-integra" id="buscarc" style="cursor: pointer;"></span>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="telefono">Teléfono</label>
                                            <input type="text" class="form-control" id="telefono" name="telefono" placeholder="Teléfono" autocomplete="off">
                                        </div>
                                    </div>
                                </div>

                                <div class="row input group">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="correo">Correo</label>
                                            <input type="text" class="form-control" id="correo" name="correo" placeholder="Correo" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="valor_estimado">Valor estimado</label>
                                            <input type="text" class="form-control" id="valor_estimado" name="valor_estimado" value="0" placeholder="0.00">
                                        </div>
                                    </div>
                                </div>

                                <div class="row input group">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="tipo_pago">Forma de pago</label>
                                            <select id='tipo_pago' class="form-control">
                                                <option value="0">Seleccione</option>
                                                <?php
                                                while ($pagos = $rsp_get_pagos->fetch_assoc()) {
                                                    echo "<option value='$pagos[pk_pago]'>$pagos[nombre]</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="montopago">Monto anticipo</label>
                                            <input type="text" class="form-control" id="montopago" name="montopago" value="0" placeholder="0.00" disabled>
                                        </div>
                                    </div>
                                </div>

                                <div class="btns-group">
                                    <a href="#" class="btn-steps btn-next width-50 ml-auto" id="paso1" onclick="validarPaso1()">Siguiente</a>
                                </div>

                            </div>


                            <!--STEP 2-->
                            <div class="form-step">
                                <div class="row input-group">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="ns">NS</label>
                                            <input type="text" id="ns" name="ns" placeholder="Numero de Serie" class="form-control" autocomplete="off">
                                            <input type="hidden" id="claveo">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="fk_categoria">Categoria</label>
                                            <select class="form-control" id="fk_categoria">
                                                <option value="0">SELECCIONE</option>
                                                <?php
                                                while ($rowc = $rsp_get_categorias->fetch_assoc()) {
                                                    echo "<option value='$rowc[pk_categoria]'>$rowc[nombre]</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row input-group">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="marca">Marca</label>
                                            <input type="text" id="marca" name="marca" placeholder="Marca" class="form-control" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="modelo">Modelo</label>
                                            <input type="text" class="form-control" id="modelo" name="equipo" placeholder="Modelo" autocomplete="off">
                                        </div>
                                    </div>
                                </div>

                                <div class="row input-group">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="observaciones">Describe la causa de la reparación</label>
                                            <textarea class="form-control" id="observaciones" placeholder="Observaciones" cols="30" rows="10" style="height: 100px;"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="btns-group">
                                    <a href="#" class="btn-steps btn-prev">Regresar</a>
                                    <a href="#" class="btn-steps btn-next width-50 ml-auto" id="paso2" onclick="validarPaso2()">Siguiente</a>
                                </div>

                            </div>


                            <!--STEP 3-->
                            <div class="form-step">
                                <div class="row input-group d-flex justify-content-center">
                                    <h5 class="text-center">Agregar imágenes</h5>
                                    <div class="preview">
                                        <label for="imagen_uno" id="label_img_uno"></label>
                                        <input type="file" class="form-control" id="imagen_uno" name="archivo[]">
                                    </div>
                                    <div class="preview">
                                        <label for="imagen_dos" id="label_img_dos"></label>
                                        <input type="file" class="form-control" id="imagen_dos" name="archivo[]">
                                    </div>
                                    <div class="preview">
                                        <label for="imagen_tres" id="label_img_tres"></label>
                                        <input type="file" class="form-control" id="imagen_tres" name="archivo[]">
                                    </div>
                                    <div class="preview">
                                        <label for="imagen_cuatro" id="label_img_cuatro"></label>
                                        <input type="file" class="form-control" id="imagen_cuatro" name="archivo[]">
                                    </div>

                                    <div class="btns-group">
                                        <a href="#" class="btn-steps btn-prev">Regresar</a>
                                        <button id="guardar" type="button" class="btn btn-primary-dast mx-2"><i class="fa fa-save mx-2"></i>Guardar</button>
                                    </div>
                                </div>

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
                        <h4 class="modal-title ltse" id="myModalLabel" style="font-size: 24px!important">Seleccione sucursal</h4>
                    </div>
                    <div>
                        <button class="btn btn-danger" onclick="$(location).attr('href','verOrdenes.php')">Cerrar</button>
                    </div>
                </div>
                <div class="modal-body" id="tablaEmpresa">
                    <table id="dtEmpresa" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            while ($rows = $rsp_get_sucursales->fetch_assoc()) {
                                echo <<<HTML
                                    <tr class="odd gradeX">
                                        <td class="empresa" id="$rows[pk_sucursal]" title="$rows[nombre]">
                                            <span id="s-$rows[pk_sucursal]">$rows[nombre]</span>
                                        </td>
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


    <div class="modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static" id="modalClientes">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-between align-items-center" style="background-color: #FFD94D; color:#000;">
                    <div class="d-flex justify-content-start align-items-center">
                        <i class='bx bx-user-circle mx-2 fs-1'></i>
                        <h4 class="modal-title ltse" id="myModalLabel" style="font-size: 24px!important">Seleccione un cliente</h4>
                    </div>
                    <button class="btn btn-sm btn-danger" id="cerrarm" style="margin-left: 70%">Cerrar</button>
                </div>
                <div class="modal-body">
                    <table id="tablaClientes" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Telefono</th>
                                <th>Correo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            while ($rowl = $rsp_get_clientes->fetch_assoc()) {
                                echo <<<HTML
                                    <tr id='c-$rowl[pk_cliente]' class='cliente'>
                                        <td>$rowl[nombre]</td>
                                        <td>$rowl[telefono]</td>
                                        <td>$rowl[correo]</td>
                                    </tr>
                                HTML;
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
                <div class="modal-header text-center">
                    <h2 class="text-center exitot">Órden <span id="nentrada"></span> </h2>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <a target="_blank" id="pdf" class="btn btn-danger pdfb"><i class="fa fa-file-pdf-o fa-5x" aria-hidden="true"></i><br><br>DESCARGAR ORDEN</a>
                        </div>
                    </div>
                </div>
                <div class="modal-footer text-center">
                    <div class="col-sm-12">
                        <button id="ver_ordenes" type="button" class="btn btn-sm btn-info">Ver órdenes</button>
                        <button id="nueva" type="button" class="btn btn-sm btn-warning">Generar Nueva Orden</button>
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

    <script src="custom/numberFormats.js"></script>
    <script src="custom/agregarOrden.js?v=<?= time(); ?>"></script>
    <script src="assets/lib/data-table/datatables.min.js"></script>
    <script src="assets/lib/data-table/dataTables.bootstrap.min.js"></script>
    <script src="assets/lib/data-table/dataTables.buttons.min.js"></script>
    <script src="assets/loading/loadingoverlay.js"></script>
    <script src="custom/jquery.maskedinput.js"></script>

    <script>
        $('#modalEmpresa #dtEmpresa').DataTable({
            responsive: true,
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

        $('#modalClientes #tablaClientes').DataTable({
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
