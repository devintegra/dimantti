<?php
header('Cache-control: private');
include("servicios/conexioni.php");
mysqli_set_charset($mysqli, 'utf8');
@session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
}

$nivel = $_SESSION["nivel"];
$nempresa = $_SESSION["nempresa"];

if ($nivel == 1) {
    $tipo = "Administrador";
    $menu = "fragments/menua.php";
}

if ($nivel == 2) {
    $tipo = "Chofer";
    $menu = "fragments/menuc.php";
}

if ($nivel != 1) {
    header('Location: ../index.php');
}



if (isset($_GET['id']) && is_string($_GET['id'])) {
    $pk_producto = (int)$_GET['id'];
}



//PRODUCTO
#region
$qproducto = "SELECT * FROM ct_productos WHERE pk_producto = $pk_producto";

if (!$rproducto = $mysqli->query($qproducto)) {
    echo "<br>Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

$row = $rproducto->fetch_assoc();
$nombre = $row["nombre"];
$clave = $row["clave"];
$codigobarras = $row["codigobarras"];
$fk_presentacion = $row["fk_presentacion"];
$fk_categoria = $row["fk_categoria"];
$descripcion = $row["descripcion"];
$costo = $row["costo"];
$precio = $row["precio"];
$precio2 = $row["precio2"];
$precio3 = $row["precio3"];
$precio4 = $row["precio4"];
$utilidad = $row["utilidad"];
$utilidad2 = $row["utilidad2"];
$utilidad3 = $row["utilidad3"];
$utilidad4 = $row["utilidad4"];
$inventario = $row["inventario"];
$inventariomin = $row["inventariomin"];
$inventariomax = $row["inventariomax"];
$clave_producto_sat = $row["clave_producto_sat"];
$clave_unidad_sat = $row["clave_unidad_sat"];
#endregion



//IMAGENES
#region
$qimagenes = "SELECT * FROM rt_imagenes_productos WHERE fk_producto = $pk_producto AND estado=1";

if (!$rimagenes = $mysqli->query($qimagenes)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

$imagenes_totales = $rimagenes->num_rows;
#endregion



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



//INVENTARIO
#region
$arrayInventario = array(
    array('id' => 1, 'nombre' => 'No'),
    array('id' => 2, 'nombre' => 'Si')
);
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

    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/gh/kenwheeler/slick@1.8.1/slick/slick-theme.css" />
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.css" />

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
                            <h4 class="card-title">Editar producto</h4>
                            <i class='bx bx-shopping-bag' style="font-size:32px"></i>
                        </div>
                        <form class="forms-sample" enctype="multipart/form-data" id="formuploadajax">

                            <div class='row'>
                                <div class='items'>
                                    <?php
                                    for ($x = 0; $x < $imagenes_totales; $x++) {

                                        $imagenes = $rimagenes->fetch_assoc();
                                        $file = "servicios/productos/$imagenes[imagen]";
                                        $fondo = is_file($file) ? "servicios/productos/$imagenes[imagen]" : "images/picture.png";

                                        echo "
                                            <div>
                                                <i class='bx bx-x fs-6 eliminar-imagen' id='$imagenes[pk_imagen_producto]' style='background-color: #F95F53; color:#fff; padding: 5px; border-radius:50%; position:relative; top:15px; font-size: 20px !important; cursor:pointer;'></i>
                                                <img src='$fondo' style='width:150px; height:150px; border-radius:20px; background-size:cover;'>
                                            </div>
                                        ";
                                    }
                                    ?>
                                </div>
                            </div>


                            <div class="w-100">
                                <div class="dropzone">
                                    <div class="dz-default dz-message d-flex flex-column align-items-center">
                                        <i class='bx bx-cloud-upload' style="font-size: 78px; color: #5d33b8"></i>
                                        <span class="fs-5">Arrastra tus imágenes</span>
                                        <p>(Máximo 4 imágenes de 2mb cada una)</p>
                                    </div>
                                </div>
                            </div>


                            <br>

                            <!--DATOS GENERALES-->
                            <div class="w-100">
                                <h4 class="card-title fs-4 d-flex justify-content-start align-items-center gap-2" style="color: #2563EB; font-weight: bold;"><i class='bx bx-text fs-4'></i>Datos generales</h4>

                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="nombre">*Nombre</label>
                                            <?php
                                            echo "<input type='text' id='nombre' name='nombre' placeholder='Nombre de producto' class='form-control' value='$nombre'>
                                            <input type='hidden' id='pk_producto' class='form-control' value='$pk_producto'>";
                                            ?>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="codigobarras">Código de barras</label>
                                            <?php
                                            echo "<input type='text' id='codigobarras' name='codigobarras' placeholder='Código de barras' class='form-control' value='$codigobarras' disabled>";
                                            ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="fk_presentacion">Presentación</label>
                                            <select class='form-control' id='fk_presentacion'>
                                                <option value='0'>Seleccione</option>
                                                <?php
                                                while ($rowp = $rpresentaciones->fetch_assoc()) {
                                                    if ($rowp['pk_presentacion'] == $fk_presentacion) {
                                                        echo "<option value='$rowp[pk_presentacion]' selected>$rowp[descripcion]</option>";
                                                    } else {
                                                        echo "<option value='$rowp[pk_presentacion]'>$rowp[descripcion]</option>";
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="fk_categoria">Categoría</label>
                                            <select class='form-control' id='fk_categoria'>
                                                <option value='0'>Seleccione</option>
                                                <?php
                                                while ($categorias = $rcategorias->fetch_assoc()) {
                                                    if ($categorias['pk_categoria'] == $fk_categoria) {
                                                        echo "<option value='$categorias[pk_categoria]' selected>$categorias[nombre]</option>";
                                                    } else {
                                                        echo "<option value='$categorias[pk_categoria]'>$categorias[nombre]</option>";
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="descripcion">Descripción</label>
                                            <?php
                                            echo "<textarea id='descripcion' class='form-control' cols='30' rows='5' style='height:100px' placeholder='Escriba aquí más detalles sobre el producto'>$descripcion</textarea>";
                                            ?>
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
                                            <?php
                                            echo "<input type='number' id='costo' name='costo' placeholder='Costo' class='form-control' value='$costo'>";
                                            ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="precio1">Precio 1 $</label>
                                            <?php
                                            echo "<input type='number' id='precio1' name='precio1' placeholder='Precio N°1' class='form-control' value='$precio'>";
                                            ?>
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="utilidad_1">Utilidad 1 %</label>
                                            <?php
                                            echo "<input type='number' class='form-control utilidad' id='utilidad_1' name='utilidad' value='$utilidad' min='0' placeholder='Utilidad N°1'>";
                                            ?>
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="precio2">Precio 2 $</label>
                                            <?php
                                            echo "<input type='number' id='precio2' name='precio2' placeholder='Precio N°2' class='form-control' value='$precio2'>";
                                            ?>
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="utilidad_2">Utilidad 2 %</label>
                                            <?php
                                            echo "<input type='number' class='form-control utilidad' id='utilidad_2' name='utilidad' value='$utilidad2' min='0' placeholder='Utilidad N°2'>";
                                            ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="precio3">Precio 3 $</label>
                                            <?php
                                            echo "<input type='number' id='precio3' name='precio3' placeholder='Precio N°3' class='form-control' value='$precio3'>";
                                            ?>
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="utilidad_3">Utilidad 3 %</label>
                                            <?php
                                            echo "<input type='number' class='form-control utilidad' id='utilidad_3' name='utilidad' value='$utilidad3' min='0' placeholder='Utilidad N°3'>";
                                            ?>
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="precio4">Precio 4 $</label>
                                            <?php
                                            echo "<input type='number' id='precio4' name='precio4' placeholder='Precio N°4' class='form-control' value='$precio4'>";
                                            ?>
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="utilidad_4">Utilidad 4 %</label>
                                            <?php
                                            echo "<input type='number' class='form-control utilidad' id='utilidad_4' name='utilidad' value='$utilidad4' min='0' placeholder='Utilidad N°4'>";
                                            ?>
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
                                                <option value="0">Seleccione</option>
                                                <?php
                                                foreach ($arrayInventario as $rowi) {
                                                    if ($rowi['id'] == $inventario) {
                                                        echo "<option value='$rowi[id]' selected>$rowi[nombre]</option>";
                                                    } else {
                                                        echo "<option value='$rowi[id]'>$rowi[nombre]</option>";
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="inventariomin">Inventario mínimo</label>
                                            <?php
                                            echo "<input type='number' id='inventariomin' name='inventariomin' placeholder='Cantidad mínima en inventario' class='form-control' value='$inventariomin'>";
                                            ?>
                                        </div>
                                    </div>

                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="inventariomax">Inventario máximo</label>
                                            <?php
                                            echo "<input type='number' id='inventariomax' name='inventariomax' placeholder='Cantidad máxima en inventario' class='form-control' value='$inventariomax'>";
                                            ?>
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
                                            <?php
                                            echo "<input type='text' class='form-control' id='clave_producto_sat' name='clave_producto_sat' value='$clave_producto_sat' placeholder='Clave SAT del producto' autocomplete='off'>";
                                            ?>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="clave_unidad_sat">Clave Unidad SAT</label>
                                            <select class="form-control" id="clave_unidad_sat">
                                                <option value="0">Seleccione</option>
                                                <?php
                                                while ($rowu = $runidadessat->fetch_assoc()) {
                                                    if ($rowu['pk_unidad_sat'] == $clave_unidad_sat) {
                                                        echo "<option value='$rowu[pk_unidad_sat]' selected>$rowu[nombre]</option>";
                                                    } else {
                                                        echo "<option value='$rowu[pk_unidad_sat]'>$rowu[nombre]</option>";
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <br><br>


                            <div class="row d-flex justify-content-center">
                                <?php
                                if ($nivel == 1) {
                                    echo <<<HTML
                                        <div class='row col-lg-4 m-2'>
                                            <button id='eliminar' type='button' class='btn btn-danger mx-2 d-flex justify-content-center align-items-center'><i class='bx bx-x-circle mx-2' style='font-size: 20px;'></i>Eliminar</button>
                                        </div>
                                    HTML;
                                }
                                ?>
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


    <script src="assets/vendor/jquery-2.1.4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js"></script>
    <script src="assets/plugins.js"></script>
    <script src="assets/main.js"></script>
    <script src="custom/jquery.confirm.js"></script>
    <script src="custom/jquery.maskedinput.js"></script>
    <script src="assets/loading/loadingoverlay.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <script src="custom/numberFormats.js"></script>
    <script src="custom/editarProducto.js"></script>

</body>

</html>
