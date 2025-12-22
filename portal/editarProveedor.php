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



if (isset($_GET['id']) && is_string($_GET['id'])) {
    $pk_proveedor = (int)$_GET['id'];
}


$eusuario = "SELECT * FROM ct_proveedores WHERE pk_proveedor = $pk_proveedor";

if (!$resultado = $mysqli->query($eusuario)) {
    echo "<br>Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

$row = $resultado->fetch_assoc();
$rfc = $row["rfc"];
$nombre = $row["nombre"];
$direccion = $row["direccion"];
$telefono = $row["telefono"];
$correo = $row["correo"];
$contacto = $row["contacto"];
$credito = $row["credito"];
$dias_credito = $row["dias_credito"];

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
                            <h4 class="card-title">Editar proveedor</h4>
                            <i class='bx bx-group' style="font-size:32px"></i>
                        </div>
                        <form class="forms-sample" enctype="multipart/form-data" id="formuploadajax">

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="nombre">Nombre</label>
                                        <?php
                                        echo "<input type='text' id='nombre' name='nombre' placeholder='Nombre' class='form-control' value='$nombre' autocomplete='off'>";
                                        ?>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="telefono">Teléfono</label>
                                        <?php
                                        echo "<input type='text' id='telefono' name='telefono' placeholder='Teléfono' class='form-control' value='$telefono'>";
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="direccion">Dirección</label>
                                        <?php
                                        echo "<input type='text' id='direccion' name='direccion' placeholder='Dirección' class='form-control' value='$direccion'>";
                                        ?>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="correo">Correo</label>
                                        <?php
                                        echo "<input type='email' id='correo' name='correo' placeholder='Correo' class='form-control' value='$correo'>";
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="contacto">Nombre del contacto</label>
                                        <?php
                                        echo "<input type='text' id='contacto' name='contacto' placeholder='Contacto' class='form-control' value='$contacto' autocomplete='off'>";
                                        ?>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="rfc">RFC</label>
                                        <?php
                                        echo "<input type='text' id='rfc' name='rfc' placeholder='RFC' class='form-control' value='$rfc'>
                                        <input type='hidden' id='id' name='text-input' value='$pk_proveedor'>";
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="credito">Crédito</label>
                                        <select class="form-control" id="credito">
                                            <?php
                                            if ($credito == 1) {
                                                echo "<option value='0'>No</option>
                                                    <option value='1' selected>Si</option>";
                                            } else {
                                                echo "<option value='0' selected>No</option>
                                                    <option value='1'>Si</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="dias_credito">Días de crédito</label>
                                        <?php
                                        echo "<input type='number' id='dias_credito' name='dias_credito' placeholder='Días de crédito' min='0' class='form-control' value='$dias_credito'>";
                                        ?>
                                    </div>
                                </div>
                            </div>


                            <div class="row d-flex justify-content-center">
                                <?php
                                if ($nivel == 1 || $nivel == 2) {
                                    echo "
                                        <div class='row col-lg-4 m-2'>
                                            <button id='eliminar' type='button' class='btn btn-danger mx-2 d-flex justify-content-center align-items-center'><i class='bx bx-x-circle mx-2' style='font-size: 20px;'></i>Eliminar</button>
                                        </div>
                                        ";
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
    <script src="custom/editarProveedor.js"></script>
</body>

</html>
