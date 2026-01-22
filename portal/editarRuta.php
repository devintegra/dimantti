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
    $pk_ruta = (int)$_GET['id'];
}


//DATOS
#region
$qruta = "SELECT * FROM ct_rutas WHERE pk_ruta = $pk_ruta";

if (!$rruta = $mysqli->query($qruta)) {
    echo "<br>Lo sentimos, esta aplicación está experimentando problemas.1";
    exit;
}

$rowr = $rruta->fetch_assoc();
$fk_sucursal = $rowr["fk_sucursal"];
$clave = $rowr["clave"];
$nombre = $rowr["nombre"];
#endregion


//SUCURSALES
#region
$mysqli->next_result();
if (!$get_sucursales = $mysqli->query("SELECT * FROM ct_sucursales WHERE estado = 1")) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. 1";
    exit;
}
#endregion


?>


<!doctype html>

<html class="no-js" lang="">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Posmovil - Consola de administración </title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="vendors/feather/feather.css">
    <link rel="stylesheet" href="vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" href="vendors/typicons/typicons.css">
    <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="vendors/font-awesome/css/font-awesome.min.css">
    <link href='https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css' rel='stylesheet'>
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="vendors/select2/select2.min.css">
    <link rel="stylesheet" href="vendors/select2-bootstrap-theme/select2-bootstrap.min.css">
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <link rel="stylesheet" href="css/vertical-layout-light/style.css">
    <link rel="stylesheet" href="css/estilos.css">
    <!-- endinject -->
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
                            <h4 class="card-title">Editar ruta</h4>
                            <i class='bx bx-trip' style="font-size:32px"></i>
                        </div>
                        <form class="forms-sample" enctype="multipart/form-data" id="formuploadajax">
                            <div class="row">
                                <?php if ($nivel == 1): ?>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="fk_sucursal" class="d-flex align-items-center gap-2"> <i class="bx bx-store-alt fs-5"></i> Sucursal</label>
                                            <select class="form-control" id="fk_sucursal">
                                                <option value="0">SELECCIONE</option>
                                                <?php
                                                while ($rows = $get_sucursales->fetch_assoc()) {
                                                    if ($rows['pk_sucursal'] == $fk_sucursal) {
                                                        echo "<option value='$rows[pk_sucursal]' selected>$rows[nombre]</option>";
                                                    } else {
                                                        echo "<option value='$rows[pk_sucursal]'>$rows[nombre]</option>";
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <input type="hidden" class="form-control" id="fk_sucursal" value="$pk_sucursal">
                                <?php endif; ?>

                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="clave">Clave</label>
                                        <?php
                                        echo <<<HTML
                                            <input type='text' id='clave' name='clave' placeholder='Clave' class='form-control' value='$clave' autocomplete='off'>
                                            <input type='hidden' id='pk_ruta' name='pk_ruta' class='form-control' value='$pk_ruta'>
                                        HTML;
                                        ?>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="nombre">Nombre</label>
                                        <?php
                                        echo <<<HTML
                                            <input type='text' id='nombre' name='nombre' placeholder='Nombre' class='form-control' value='$nombre' autocomplete='off'>
                                        HTML;
                                        ?>
                                    </div>
                                </div>
                            </div>


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

    <script src="assets/vendor/jquery-2.1.4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js"></script>
    <script src="assets/plugins.js"></script>
    <script src="assets/main.js"></script>
    <script src="assets/lib/data-table/datatables.min.js"></script>
    <script src="assets/lib/data-table/dataTables.bootstrap.min.js"></script>
    <script src="assets/lib/data-table/dataTables.buttons.min.js"></script>
    <script src="assets/lib/data-table/datatables-init.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/loading/loadingoverlay.js"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="custom/jquery.confirm.js"></script>
    <script src="custom/editarRuta.js"></script>


</body>

</html>
