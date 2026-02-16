<?php
header('Cache-control: private');
include("servicios/conexioni.php");
@session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
}

$nivel = $_SESSION["nivel"];
$pk_sucursal = 0;
$usuario = $_SESSION['usuario'];

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
$qproductos = "SELECT * FROM ct_productos where estado = 1";

if (!$rproductos = $mysqli->query($qproductos)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

$nivel == 1 ? $flsucursal = "" : $flsucursal = " AND pk_sucursal = $pk_sucursal";


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
                            <h4 class="card-title">Registro de inventario</h4>
                            <h4 class="card-title" id="lafecha"></h4>
                        </div>

                        <div class="row">
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label for="codigo_barras">Código de barras</label>
                                    <select id="codigo_barras" class="select2 form-control select2-hidden-accessible" style="width: 100%;" multiple>
                                        <option value=""></option>
                                        <?php
                                        while ($productos = $rproductos->fetch_assoc()) {
                                            echo "<option value='$productos[codigobarras]'>$productos[codigobarras] | $productos[nombre] | $$productos[precio]</option>";
                                        }
                                        ?>
                                    </select>
                                    <?php
                                    echo <<<HTML
                                        <input type='hidden' id='fk_sucursal' value='$pk_sucursal' class='form-control'>
                                        <input type='hidden' id='fk_usuario' value='$usuario' class='form-control'>
                                        <input type='hidden' id='arrAlmacenes' class='form-control'>
                                        <input type='hidden' id='arrCategorias' class='form-control'>
                                        <input type='hidden' id='arrMarcas' class='form-control'>
                                        <input type='hidden' id='arrProductos' class='form-control'>
                                    HTML;
                                    ?>
                                </div>
                            </div>
                            <div class="col-lg-4"></div>
                            <div class="col-lg-4 d-flex justify-content-end align-items-center">
                                <button id="ajuste_global" type="button" class="btn btn-success-dast mx-2">Ajustar todo</button>
                            </div>
                        </div>

                        <div class="table-responsive overflow-hidden" id="tabla">
                            <table id='dtProductos' class='table table-striped'>
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Código de barras</th>
                                        <th>Producto</th>
                                        <th>Existencias Registradas</th>
                                        <th>Existencias Reales</th>
                                        <th>Diferencia</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>

                        <br>

                        <div class="row d-flex justify-content-center">
                            <div class="row col-lg-4 m-2">
                                <button id="cancelar" type="button" class="btn btn-danger mx-2 d-flex justify-content-center align-items-center"><i class='bx bx-x-circle mx-2' style="font-size: 20px;"></i>Cancelar</button>
                            </div>
                            <div class="row col-lg-4 m-2">
                                <button id="finalizar" type="button" class="btn-primary-dast mx-2"><i class="fa fa-save mx-2"></i>Finalizar</button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>



    <div class="modal" tabindex="-1" role="dialog" aria-hidden="true" data-bs-keyboard="false" data-bs-backdrop="static" id="modalSucursales">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-between" style="background-color: #BFDBFE;">
                    <div class="d-flex justify-content-start align-items-center">
                        <i class='bx bx-filter-alt mx-2 fs-1'></i>
                        <h4 class="modal-title ltse" id="myModalLabel" style="font-size: 24px!important">Filtros de búsqueda</h4>
                    </div>
                    <div>
                        <button class="btn btn-danger" onclick="$(location).attr('href','verInventario.php')">Cerrar</button>
                    </div>
                </div>
                <div class="modal-body" id="tablaSucursales">

                    <div id="tablaSucursales">
                        <h4 class="card-title">Sucursal</h4>
                        <p>*Solo se puede seleccionar una sucursal</p>
                        <table id='dtEmpresa' class='table table-striped'>
                            <thead>
                                <tr>
                                    <th>Sucursal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php

                                $qsucursal = "SELECT * FROM ct_sucursales WHERE estado = 1$flsucursal";

                                if (!$rsucursales = $mysqli->query($qsucursal)) {
                                    echo "Lo sentimos, esta aplicación está experimentando problemas.";
                                    exit;
                                }

                                while ($roweusuario = $rsucursales->fetch_assoc()) {
                                    echo <<<HTML
                                        <tr class='odd gradeX fc' style='cursor:pointer' id='sucursal*-*$roweusuario[pk_sucursal]'>
                                            <td>$roweusuario[nombre]</td>
                                        </tr>
                                    HTML;
                                }
                                ?>
                            </tbody>
                        </table>
                        <div class='row'>&nbsp;</div>
                    </div>


                    <br>
                    <hr><br>


                    <!--Almacénes-->
                    <div class="row" style="background-color: #FFF099; padding: 20px; border-radius:20px;">
                        <h4 class="card-title">Almacén</h4>

                        <div class="col d-flex" id="almacenes">
                            <p>*Seleccione una sucursal para filtrar sus almacénes</p>
                        </div>
                    </div>


                    <br>
                    <hr><br>


                    <!--Categorías-->
                    <div class="row" style="background-color: #BBF7B0; padding: 20px; border-radius:20px;">
                        <div class="d-flex justify-content-start">
                            <h4 class="card-title">Categoría</h4>
                            <button class="mx-4 rounded-circle btn-info-dast" id="toggleButtonCategorias"><i class='bx bx-chevron-down'></i></button>
                        </div>

                        <?php

                        $qcategoria = "SELECT * FROM ct_categorias WHERE estado = 1";

                        if (!$rcategorias = $mysqli->query($qcategoria)) {
                            echo "Lo sentimos, esta aplicación está experimentando problemas.";
                            exit;
                        }

                        echo <<<HTML
                            <div class='col collapse' id='collapseCategorias'>
                                <div class='d-flex flex-wrap'>
                        HTML;

                        while ($rowcategorias = $rcategorias->fetch_assoc()) {

                            echo <<<HTML
                                <div class='form-check form-check-success mx-4'>
                                    <label class='form-check-label fs-6'>
                                    <input type='checkbox' class='form-check-input chk-categorias' value='$rowcategorias[pk_categoria]'>
                                    $rowcategorias[nombre]
                                    </label>
                                </div>
                            HTML;
                        }

                        echo <<<HTML
                                </div>
                            </div>
                        HTML;
                        ?>
                    </div>



                    <br>
                    <hr><br>


                    <!--Productos-->
                    <div class="row" style="background-color: #FECACA; padding: 20px; border-radius:20px;">
                        <div class="d-flex justify-content-start">
                            <h4 class="card-title">Productos</h4>
                            <button class="mx-4 rounded-circle btn-info-dast" id="toggleButtonProductos"><i class='bx bx-chevron-down'></i></button>
                        </div>

                        <?php

                        $qproductos = "SELECT * FROM ct_productos WHERE estado = 1";

                        if (!$rproductos = $mysqli->query($qproductos)) {
                            echo "Lo sentimos, esta aplicación está experimentando problemas.";
                            exit;
                        }

                        echo <<<HTML
                            <div class='col collapse' id='collapseProductos'>
                                <div class='d-flex flex-wrap'>
                        HTML;

                        while ($rowproductos = $rproductos->fetch_assoc()) {

                            echo <<<HTML
                                <div class='form-check form-check-danger mx-4'>
                                    <label class='form-check-label fs-6'>
                                    <input type='checkbox' class='form-check-input chk-productos' value='$rowproductos[pk_producto]'>
                                    $rowproductos[nombre]
                                    </label>
                                </div>
                            HTML;
                        }

                        echo <<<HTML
                                </div>
                            </div>
                        HTML;
                        ?>
                    </div>


                    <br><br>


                    <!--Filtrar-->
                    <div class="row d-flex justify-content-end">
                        <div class="col-lg-4 d-flex justify-content-end">
                            <button id="filtrar" type="button" class="btn btn-lg btn-primary-dast mx-2 fs-6"><i class="bx bx-filter-alt mx-2"></i>Filtrar</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>


    <div class="modal" id="exito" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header text-center" style="background-color: #BFDBFE; color:#000;">
                    <h2 class="text-center exitot">Inventario <span id="nentrada"></span> </h2>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 text-center d-flex" style="gap: 20px;">
                            <a target="_blank" id="pdf" class="btn btn-danger pdfb"><i class="fa fa-file-pdf-o fa-5x" aria-hidden="true"></i><br><br>DESCARGAR PDF</a>
                            <a target="_blank" id="excel" class="btn btn-success pdfb"><i class="fa fa-file-excel-o fa-5x" aria-hidden="true"></i><br><br>DESCARGAR EXCEL</a>
                        </div>
                    </div>
                </div>
                <div class="modal-footer text-center">
                    <div class="col-sm-12">
                        <button id="nueva" type="button" class="btn btn-sm btn-info">Ver historial de inventarios</button>
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

    <script>
        $('#toggleButtonCategorias').click(function() {
            $('#collapseCategorias').collapse('toggle');
        });

        $('#toggleButtonProductos').click(function() {
            $('#collapseProductos').collapse('toggle');
        });
    </script>

    <script src="assets/loading/loadingoverlay.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="custom/registrarInventario.js"></script>

</body>

</html>
