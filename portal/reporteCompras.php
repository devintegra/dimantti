<?php
header('Cache-control: private');
date_default_timezone_set('America/Mexico_City');
include("servicios/conexioni.php");
@session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
}

$nivel = $_SESSION["nivel"];

$filtro = "";

if ($nivel == 1) {
    $tipo = "Administrador";
    $menu = "fragments/menua.php";
}

if ($nivel == 2) {
    $tipo = "Chofer";
    $pk_sucursal = $_SESSION["pk_sucursal"];
    $menu = "fragments/menub.php";
}

if ($nivel != 1) {
    header('Location: ../index.php');
}


//SUCURSALES
#region
$nivel == 1 ? $flsucursal = "" : $flsucursal = " AND rt_sucursales_almacenes.fk_sucursal = $pk_sucursal";

$qsucursal = "SELECT ct_sucursales.*,
    rt_sucursales_almacenes.pk_sucursal_almacen,
    rt_sucursales_almacenes.nombre as almacen
    FROM ct_sucursales, rt_sucursales_almacenes
    WHERE ct_sucursales.estado = 1
    AND rt_sucursales_almacenes.fk_sucursal = ct_sucursales.pk_sucursal$flsucursal";

if (!$rsucursales = $mysqli->query($qsucursal)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}
#endregion


//CATEGORIAS
#region
$qcategoria = "SELECT * FROM ct_categorias WHERE estado = 1";

if (!$rcategorias = $mysqli->query($qcategoria)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}
#endregion


//PROVEEDORES
#region
$qproveedores = "SELECT * FROM ct_proveedores WHERE estado = 1";

if (!$rproveedores = $mysqli->query($qproveedores)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}
#endregion


//PRODUCTOS
#region
$qproductos = "SELECT * FROM ct_productos WHERE estado = 1";

if (!$rproductos = $mysqli->query($qproductos)) {
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

    <style>
        .select2-selection__choice {
            background-color: #FFCA1A !important;
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
                            <h4 class="card-title">Reporte de compras</h4>
                            <i class='bx bxs-report' style="font-size:32px"></i>
                        </div>
                        <form class="forms-sample">

                            <div class="row filter-box">

                                <h4 class="card-title">Seleccione criterios de búsqueda</h4>

                                <div class="row">

                                    <div class="row">
                                        <div class="col-sm-12 col-lg-4">
                                            <div class="form-group">
                                                <label for="text-input" class=" form-control-label"><i class='bx bx-calendar fs-5'></i>Fecha inicio</label>
                                                <input type="text" id="inicio" name="inicio" placeholder="Inicio" class="form-control datepicker" autocomplete="off">
                                            </div>
                                        </div>

                                        <div class="col-sm-12 col-lg-4">
                                            <div class="form-group">
                                                <label for="text-input" class=" form-control-label"><i class='bx bx-calendar fs-5'></i>Fecha fin</label>
                                                <input type="text" id="fin" name="fin" placeholder="Fin" class="form-control datepicker" autocomplete="off">
                                                <?php echo "<input type='hidden' value='$pk_sucursal' id='fk_sucursal'/>"; ?>
                                            </div>
                                        </div>

                                        <div class="col-sm-12 col-lg-4">
                                            <div class="form-group">
                                                <label for="sucursal">Almacén</label>
                                                <select class='form-control' id='sucursal'>
                                                    <option value="0">Todos</option>
                                                    <?php
                                                    while ($sucursales = $rsucursales->fetch_assoc()) {
                                                        echo "<option value='$sucursales[pk_sucursal_almacen]'>$sucursales[nombre] - $sucursales[almacen]</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-sm-12 col-lg-4">
                                            <div class="form-group">
                                                <label for="proveedor">Proveedor</label>
                                                <select class='form-control' id='proveedor'>
                                                    <option value="0">Seleccione</option>
                                                    <?php
                                                    while ($proveedores = $rproveedores->fetch_assoc()) {
                                                        echo "<option value='$proveedores[pk_proveedor]'>$proveedores[nombre]</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-sm-12 col-lg-4">
                                            <div class="form-group">
                                                <label for="credito">Crédito</label>
                                                <select class='form-control' id='credito'>
                                                    <option value="0">Seleccione</option>
                                                    <option value="1">Total</option>
                                                    <option value="2">Parcial</option>
                                                    <option value="3">Crédito</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-sm-12 col-lg-4">
                                            <div class="form-group">
                                                <label for="clave" class=" form-control-label"><i class='bx bxs-devices fs-5'></i>Producto</label>
                                                <select id="clave" class="select2 form-control select2-hidden-accessible" style="width: 100%;" multiple>
                                                    <option value=""></option>
                                                    <?php
                                                    while ($productos = $rproductos->fetch_assoc()) {

                                                        echo "<option value='$productos[codigobarras]'>$productos[codigobarras] | $productos[nombre] | $$productos[precio]</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-sm-12 col-lg-4">
                                            <div class="form-group">
                                                <label for="categoria">Categoría</label>
                                                <select class='form-control' id='categoria'>
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

                                </div>

                                <div class="row">
                                    <div class="col-sm-4 col-lg-2">
                                        <button id="generar" type="button" class="btn btn-primary-dast mx-2"><i class="fa fa-file-excel-o mx-2"></i>Excel</button>
                                    </div>
                                    <div class="col-sm-4 col-lg-2">
                                        <button id="pdf" type="button" class="btn btn-danger-dast mx-2"><i class="fa fa-file-pdf-o mx-2"></i>PDF</button>
                                    </div>
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="custom/reporteCompras.js"></script>


</body>

</html>
