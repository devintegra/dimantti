<?php
header('Cache-control: private');
include("servicios/conexioni.php");
mysqli_set_charset($mysqli, 'utf8');
@session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
}

$nivel = $_SESSION["nivel"];
$fk_sucursal = $_SESSION["fk_sucursal"];

if ($nivel == 1) {
    $tipo = "Administrador";
    $menu = "fragments/menua.php";
}

if ($nivel != 1) {
    header('Location: ../index.php');
}


//SUCURSALES
#region
$qsucursales = "SELECT * FROM ct_sucursales WHERE estado = 1";

if (!$rsucursales = $mysqli->query($qsucursales)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}
#endregion



//PLANTILLAS
#region
$qplantillas = "SELECT * FROM ct_plantillas WHERE estado = 1";

if (!$rplantillas = $mysqli->query($qplantillas)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}
#endregion



//USUARIOS
#region
$qvendedores = "SELECT * FROM ct_usuarios WHERE nivel = 2 and estado = 1";

if (!$rvendedores = $mysqli->query($qvendedores)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}
#endregion



//RUTAS
#region
$qrutas = "SELECT * FROM ct_rutas WHERE estado = 1";

if (!$rrutas = $mysqli->query($qrutas)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}
#endregion


?>

<!doctype html>

<html class="no-js" lang="">
<!--<![endif]-->

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
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
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
                            <h4 class="card-title">Iniciar almacen</h4>
                            <i class='bx bx-package' style="font-size:32px"></i>
                        </div>
                        <form class="forms-sample">

                            <div class="row">
                                <?php if ($fk_sucursal == 0): ?>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="fk_sucursal" class="d-flex align-items-center gap-2"> <i class="bx bx-store-alt fs-4"></i> Sucursal de salida</label>
                                            <select id="fk_sucursal" class="form-control">
                                                <option value="0">Seleccione</option>
                                                <?php
                                                while ($rows = $rsucursales->fetch_assoc()) {
                                                    echo "<option value='$rows[pk_sucursal]'>$rows[nombre]</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <?php
                                    echo "<input type='hidden' class='form-control' id='fk_sucursal' value='$fk_sucursal'>";
                                    ?>
                                <?php endif; ?>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="fk_almacen" class="d-flex align-items-center gap-2"> <i class="bx bx-store-alt fs-4"></i> Almacén de salida</label>
                                        <select class="form-control" id="fk_almacen">
                                            <option value="0">Seleccione</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="plantilla" class="d-flex align-items-center gap-2"> <i class="bx bx-area fs-4"></i> Plantilla</label>
                                        <select id="plantilla" class="form-control">
                                            <option value="0">Seleccione</option>
                                            <?php
                                            while ($plantilla = $rplantillas->fetch_assoc()) {
                                                echo "<option value='$plantilla[pk_plantilla]'>$plantilla[nombre]</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="ruta" class="d-flex align-items-center gap-2"> <i class="bx bx-trip fs-4"></i>Ruta</label>
                                        <select id="ruta" class="form-control">
                                            <option value="0">Seleccione</option>
                                            <?php
                                            while ($ruta = $rrutas->fetch_assoc()) {
                                                echo "<option value='$ruta[pk_ruta]'>$ruta[nombre]</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="usuario" class="d-flex align-items-center gap-2"> <i class="bx bx-user fs-4"></i>Usuario</label>
                                        <select id="usuario" class="form-control">
                                            <option value="0">Seleccione</option>
                                            <?php
                                            while ($vendedores = $rvendedores->fetch_assoc()) {
                                                echo "<option value='$vendedores[pk_usuario]'>$vendedores[nombre]</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="usuario" class="d-flex align-items-center gap-2"> <i class="bx bx-calendar fs-4"></i>Fecha</label>
                                        <input type='text' class='form-control datepicker' id='fecha' name='fecha' placeholder='Fecha' autocomplete='off'>
                                    </div>
                                </div>
                            </div>

                            <br>

                            <div class="w-100 d-flex align-items-center justify-content-center">
                                <button id="guardar" type="button" class="btn btn-primary-dast mx-2"><i class="fa fa-play mx-2"></i>Iniciar</button>
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
    <script src="custom/jquery.maskedinput.js"></script>
    <script src="assets/loading/loadingoverlay.js"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="custom/agregarAlmacen.js"></script>

</body>

</html>
