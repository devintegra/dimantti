<?php
header('Cache-control: private');
include("servicios/conexioni.php");
mysqli_set_charset($mysqli, 'utf8');
@session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
}

if (isset($_GET['id']) && is_string($_GET['id'])) {
    $pk_usuario = $_GET['id'];
}


$nivel = $_SESSION["nivel"];
$empresa = $_SESSION["empresa"];

if ($nivel == 1) {
    $tipo = "Administrador";
    $menu = "fragments/menua.php";
}

if ($nivel != 1) {
    header('Location: ../index.php');
}


//DATOS
#region
$eusuario = "SELECT * FROM ct_usuarios WHERE pk_usuario = '$pk_usuario'";

if (!$resultado = $mysqli->query($eusuario)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

$rowexistead = $resultado->fetch_assoc();
$nombre = $rowexistead["nombre"];
$correo = $rowexistead["correo"];
$pass = base64_decode($rowexistead["pass"]);
$nivelu = $rowexistead["nivel"];
$sucursal = $rowexistead["fk_sucursal"];
$sueldo = $rowexistead["sueldo"];
$comision = $rowexistead["comision"];
$avatar_usuario = $rowexistead["imagen"];
#endregion


//SUCURSALES
#region
$qsucursales = "SELECT * FROM ct_sucursales WHERE estado=1";

if (!$rsucursales = $mysqli->query($qsucursales)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}
#endregion


//USUARIOS
#region
$arrayUsuarios = array(
    array('id' => 1, 'nombre' => 'Administrador'),
    array('id' => 2, 'nombre' => 'Vendedor')
);
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
                            <h4 class="card-title">Editar usuario</h4>
                            <i class='bx bx-user' style="font-size:32px"></i>
                        </div>
                        <form class="forms-sample">

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="tipo">Tipo</label>
                                        <select class="form-control" id='tipo'>
                                            <option value="0">SELECCIONE</option>
                                            <?php
                                            foreach ($arrayUsuarios as $rowu) {
                                                if ($rowu['id'] == $nivelu) {
                                                    echo "<option value='$rowu[id]' selected>$rowu[nombre]</option>";
                                                } else {
                                                    echo "<option value='$rowu[id]'>$rowu[nombre]</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                        <?php
                                        echo "<input type=\"hidden\" id=\"empresa\" name=\"text-input\" value=\"$empresa\">";
                                        ?>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="nombre">Nombre</label>
                                        <?php
                                        echo "<input type='text' id='nombre' name='text-input' placeholder='Nombre' class='form-control' value='$nombre' autocomplete='off'>";
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="correo">Correo</label>
                                        <?php
                                        echo "<input type='email' id='correo' name='text-input' placeholder='Correo' class='form-control' value='$correo'>";
                                        ?>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="usuario">Usuario</label>
                                        <?php
                                        echo "<input type='text' id='usuario' name='text-input' placeholder='Usuario' class='form-control' disabled value='$pk_usuario' autocomplete='off'>";
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="pass">Contraseña</label>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <?php
                                            echo "<input type='password' id='pass' name='text-input' placeholder='Contraseña' class='form-control' value='$pass'>";
                                            ?>
                                            <div class="d-flex justify-content-center align-items-center"><i class='bx bx-info-circle' title='La contraseña debe contener al menos 5 caracteres' style="font-size:24px; color: #918D8D; cursor:pointer;"></i></div>
                                            <div class="d-flex justify-content-center align-items-center"><i class='bx bx-low-vision' title='Ver contraseña' id="ver_password" style="font-size:24px; color: #918D8D; cursor:pointer;"></i></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="passc">Confirmar contraseña</label>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <?php
                                            echo "<input type='password' id='passc' name='text-input' placeholder='Confirmar' class='form-control' value='$pass'>";
                                            ?>
                                            <div class="d-flex justify-content-center align-items-center"><i class='bx bx-low-vision' title='Ver contraseña' id="ver_passwordc" style="font-size:24px; color: #918D8D; cursor:pointer;"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row d-none" id="contentNomina">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="sueldo" class="d-flex align-items-center gap-2"> <i class="bx bx-money fs-5"></i> Sueldo semanal</label>
                                        <?php
                                        echo <<<HTML
                                            <input type="number" class="form-control" id="sueldo" name="sueldo" value="$sueldo" min="0" placeholder="0.00">
                                        HTML;
                                        ?>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="comision">% de comisión por venta</label>
                                        <?php
                                        echo <<<HTML
                                            <input type="number" class="form-control" id="comision" name="comision" value="$comision" min="0" placeholder="0.00">
                                        HTML;
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <?php

                            if ($nivel == 1) {
                                echo "<div class='row'>
                                            <div class='col-lg-6'>
                                                <div class='form-group'>
                                                <label for='text-input' class='form-control-label'>Sucursal</label>
                                                <select id='sucursal' class='form-control' disabled>
                                        ";
                                while ($sucursales = $rsucursales->fetch_assoc()) {
                                    if ($sucursales['pk_sucursal'] == $sucursal) {
                                        echo "<option value='$sucursales[pk_sucursal]' selected>$sucursales[nombre]</option>";
                                    } else {
                                        echo "<option value='$sucursales[pk_sucursal]'>$sucursales[nombre]</option>";
                                    }
                                }
                                echo "</select>
                                            </div>
                                        </div>
                                    </div>";
                            } else {
                                echo "<input type='hidden' id='sucursal' value='$pk_sucursal'>";
                            }

                            ?>

                            <div class="row d-flex justify-content-center">
                                <?php
                                echo "<input type='hidden' id='id' value='$pk_usuario'>";
                                ?>
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
    <script src="custom/jquery.confirm.js"></script>
    <script src="assets/loading/loadingoverlay.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="custom/editarUsuario.js"></script>

</body>

</html>
