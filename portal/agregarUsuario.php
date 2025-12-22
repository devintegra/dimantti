<?php
header('Cache-control: private');
include("servicios/conexioni.php");
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

mysqli_set_charset($mysqli, 'utf8');


//SUCURSALES
$qsucursales = "SELECT * FROM ct_sucursales WHERE estado = 1";

if (!$rsucursales = $mysqli->query($qsucursales)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}


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
                            <h4 class="card-title">Nuevo usuario</h4>
                            <i class='bx bx-user' style="font-size:32px"></i>
                        </div>
                        <form class="forms-sample">

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="tipo">Tipo</label>
                                        <select class='form-control' id='tipo'>
                                            <option value='0'>SELECCIONE</option>
                                            <option value='1'>Administrador</option>
                                            <option value='2'>Chofer</option>
                                        </select>

                                        <?php
                                        echo "<input type=\"hidden\" id=\"empresa\" name=\"text-input\" value=\"$empresa\">";
                                        ?>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="nombre">Nombre</label>
                                        <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre" autocomplete="off">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="correo">Correo</label>
                                        <input type="email" class="form-control" id="correo" name="correo" placeholder="Correo" autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="usuario">Usuario</label>
                                        <input type="text" class="form-control" id="usuario" name="usuario" placeholder="Usuario" autocomplete="off">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="pass">Contraseña</label>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <input type="password" class="form-control" id="pass" name="pass" placeholder="Contraseña">
                                            <div class="d-flex justify-content-center align-items-center"><i class='bx bx-info-circle' title='La contraseña debe contener al menos 5 caracteres' style="font-size:24px; color: #918D8D; cursor:pointer;"></i></div>
                                            <div class="d-flex justify-content-center align-items-center"><i class='bx bx-low-vision' title='Ver contraseña' id="ver_password" style="font-size:24px; color: #918D8D; cursor:pointer;"></i></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="passc">Confirmar contraseña</label>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <input type="password" class="form-control" id="passc" name="passc" placeholder="Contraseña">
                                            <div class="d-flex justify-content-center align-items-center"><i class='bx bx-low-vision' title='Ver contraseña' id="ver_passwordc" style="font-size:24px; color: #918D8D; cursor:pointer;"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php

                            if ($nivel == 1) {
                                echo "<div class='row'>
                                            <div class='col-lg-6'>
                                                <div class='form-group'>
                                                    <label for='sucursal'>Sucursal</label>
                                                    <select id='sucursal' class='form-control' disabled>
                                            ";
                                while ($sucursales = $rsucursales->fetch_assoc()) {
                                    echo "<option value='$sucursales[pk_sucursal]'>$sucursales[nombre]</option>";
                                }
                                echo "</select>
                                            </div>
                                        </div>
                                    </div>";
                            } else {
                                echo "<input type='hidden' id='sucursal' value='$pk_sucursal'>";
                            }

                            ?>

                            <button id="guardar" type="button" class="btn btn-primary-dast mx-2"><i class="fa fa-save mx-2"></i>Guardar</button>

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
    <script src="assets/loading/loadingoverlay.js"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="custom/agregarUsuario.js"></script>

</body>

</html>
