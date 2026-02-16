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
    $tipo = "Chofer";
    $menu = "fragments/menub.php";
}

if ($nivel != 1) {
    header('Location: ../index.php');
}


//CATEGORIAS
#region
$qcategorias = "SELECT * FROM ct_categorias WHERE estado = 1 order by nombre";

if (!$rcategorias = $mysqli->query($qcategorias)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas";
    exit;
}
#endregion



//PRESENTACIONES
#region
$qpresentacion = "SELECT * FROM ct_presentaciones WHERE estado = 1 order by descripcion";

if (!$rpresentaciones = $mysqli->query($qpresentacion)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas";
    exit;
}
#endregion



//UNIDADES SAT
#region
$qunidadessat = "SELECT * FROM ct_unidades_sat WHERE estado = 1 order by nombre";

if (!$runidadessat = $mysqli->query($qunidadessat)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas";
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
    <link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />

    <link rel="stylesheet" href="css/vertical-layout-light/style.css">
    <link rel="stylesheet" href="css/estilos.css">

    <link rel="shortcut icon" href="images/user.jpg" />


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
                            <h4 class="card-title">Nuevo producto</h4>
                            <i class='bx bx-shopping-bag' style="font-size:32px"></i>
                        </div>
                        <form class="forms-sample" enctype="multipart/form-data" id="formuploadajax">

                            <div class="w-100">
                                <div class="dropzone">
                                    <div class="dz-default dz-message d-flex flex-column align-items-center">
                                        <i class='bx bx-cloud-upload' style="font-size: 78px; color: #5d33b8"></i>
                                        <span class="fs-5">Arrastra tus imágenes</span>
                                        <p>(Máximo 4 imágenes de 2mb cada una)</p>
                                    </div>
                                </div>
                            </div>


                            <!--DATOS GENERALES-->
                            <div class="w-100">
                                <h4 class="card-title fs-4 d-flex justify-content-start align-items-center gap-2" style="color: #2563EB; font-weight: bold;"><i class='bx bx-text fs-4'></i>Datos generales</h4>

                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="nombre">Nombre</label>
                                            <input type="text" id="nombre" name="nombre" placeholder="Nombre del producto" class="form-control">
                                            <?php
                                            echo "<input type='hidden' id='empresa' name='text-input' value='$empresa'>";
                                            ?>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="codigo_barras">Código de barras</label>
                                            <input type="text" id="codigo_barras" name="codigo_barras" placeholder="Ingrese el código de barras" class="form-control">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="fk_presentacion">Presentación</label>
                                            <select class='form-control' id='fk_presentacion'>
                                                <option value="0">Seleccione</option>
                                                <?php
                                                while ($rowp = $rpresentaciones->fetch_assoc()) {
                                                    echo "<option value='$rowp[pk_presentacion]'>$rowp[descripcion]</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="fk_categoria">Categoría</label>
                                            <select class='form-control' id='fk_categoria'>
                                                <option value="0">Seleccione</option>
                                                <?php
                                                while ($categorias = $rcategorias->fetch_assoc()) {
                                                    echo "<option value='$categorias[pk_categoria]'>$categorias[nombre]</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="descripcion">Descripción</label>
                                            <textarea class="form-control" id="descripcion" cols="30" rows="5" style="height: 100px;" placeholder="Escriba aquí más detalles sobre el producto"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <br>


                            <!--COSTOS-->
                            <div class="w-100">
                                <h4 class="card-title fs-4 d-flex justify-content-start align-items-center gap-2" style="color: #2563EB; font-weight: bold;"><i class='bx bx-coin fs-4'></i>Costos</h4>

                                <div class="row">
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="costo">Costo $</label>
                                            <input type="number" class="form-control" id="costo" name="costo" placeholder="$0.00">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="precio">Precio 1 $</label>
                                            <input type="number" class="form-control" id="precio1" name="precio1" value="0" min="0" placeholder="Precio N°1">
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="precio">Utilidad 1 %</label>
                                            <input type="number" class="form-control utilidad" id="utilidad_1" name="utilidad" value="0" min="0" placeholder="Utilidad N°1">
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="precio2">Precio 2 $</label>
                                            <input type="number" class="form-control" id="precio2" name="precio2" value="0" min="0" placeholder="Precio N°2">
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="precio">Utilidad 2 %</label>
                                            <input type="number" class="form-control utilidad" id="utilidad_2" name="utilidad2" value="0" min="0" placeholder="Utilidad N°2">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="precio3">Precio 3 $</label>
                                            <input type="number" class="form-control" id="precio3" name="precio3" value="0" min="0" placeholder="Precio N°3">
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="precio">Utilidad 3 %</label>
                                            <input type="number" class="form-control utilidad" id="utilidad_3" name="utilidad3" value="0" min="0" placeholder="Utilidad N°3">
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="precio4">Precio 4 $</label>
                                            <input type="number" class="form-control" id="precio4" name="precio4" value="0" min="0" placeholder="Precio N°4">
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="precio">Utilidad 4 %</label>
                                            <input type="number" class="form-control utilidad" id="utilidad_4" name="utilidad4" value="0" min="0" placeholder="Utilidad N°4">
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <br>


                            <!--INVENTARIO-->
                            <div class="w-100">
                                <h4 class="card-title fs-4 d-flex justify-content-start align-items-center gap-2" style="color: #2563EB; font-weight: bold;"><i class='bx bx-package fs-4'></i>Inventario</h4>

                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="inventario">Maneja inventario</label>
                                            <select class="form-control" id='inventario'>
                                                <option value='0'>Seleccione</option>
                                                <option value='1'>No</option>
                                                <option value='2'>Si</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="inventariomin">Inventario mínimo</label>
                                            <input type="number" class="form-control" id="inventariomin" name="inventariomin" value="0" placeholder="Cantidad mínima en inventario" autocomplete="off" disabled>
                                        </div>
                                    </div>

                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="inventariomax">Inventario máximo</label>
                                            <input type="number" class="form-control" id="inventariomax" name="inventariomax" value="0" placeholder="Cantidad máxima en inventario" autocomplete="off" disabled>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <br>


                            <!--FACTURACION-->
                            <div class="w-100">
                                <h4 class="card-title fs-4 d-flex justify-content-start align-items-center gap-2" style="color: #2563EB; font-weight: bold;"><i class='bx bx-file fs-4'></i>Facturación</h4>

                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="clave_producto_sat">Clave SAT del producto</label>
                                            <input type="text" class="form-control" id="clave_producto_sat" name="clave_producto_sat" placeholder="Clave SAT del producto" autocomplete="off">
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="clave_unidad_sat">Clave Unidad SAT</label>
                                            <select class="form-control" id="clave_unidad_sat">
                                                <option value="0">Seleccione</option>
                                                <?php
                                                while ($rowu = $runidadessat->fetch_assoc()) {
                                                    echo "<option value='$rowu[pk_unidad_sat]'>$rowu[nombre]</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <br><br>

                            <div class="w-100 d-flex align-items-center justify-content-center">
                                <button id="guardar" type="button" class="btn btn-primary-dast m-2"><i class="fa fa-save mx-2"></i>Guardar</button>
                            </div>


                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>


    <script src="assets/vendor/jquery-2.1.4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js"></script>
    <script src="assets/plugins.js"></script>
    <script src="assets/main.js"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="assets/loading/loadingoverlay.js"></script>
    <script src="custom/jquery.numeric.js"></script>
    <script src="custom/numberFormats.js"></script>
    <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
    <script src="custom/agregarProducto.js"></script>


</body>

</html>
