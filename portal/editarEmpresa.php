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

if ($nivel != 1) {
    header('Location: ../index.php');
}


if (isset($_GET['id']) && is_string($_GET['id'])) {
    $pk_empresa = (int)$_GET['id'];
}


//DATOS DE LA EMPRESA
#region
$qempresa = "SELECT cte.*,
        ctr.clave as regimen
    FROM ct_empresas cte
    LEFT JOIN ct_regimenes_fiscales ctr ON ctr.pk_regimen_fiscal = cte.fk_regimen_fiscal
    WHERE cte.pk_empresa = $pk_empresa";

if (!$resultado = $mysqli->query($qempresa)) {
    echo "<br>Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

$rowe = $resultado->fetch_assoc();
$nombre = $rowe["nombre"];
$direccion = $rowe["direccion"];
$telefono = $rowe["telefono"];
$correo = $rowe["correo"];
$responsable = $rowe["responsable"];
$cp = $rowe["cp"];
$rfc = $rowe["rfc"];
$pass = base64_decode($rowe["pass"]);
$fk_regimen_fiscal = $rowe["fk_regimen_fiscal"];
$clave_regimen = $rowe["clave"];
#endregion


//Regímenes
#region
$qregimen = "SELECT * FROM ct_regimenes_fiscales where estado=1";

if (!$rregimen = $mysqli->query($qregimen)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.2";
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
                            <h4 class="card-title">Editar empresa <span class="badge-warning-integra"> <?php echo $nombre ?> </span></h4>
                            <i class='bx bx-store' style="font-size:32px"></i>
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
                                        <label for="responsable">Responsable</label>
                                        <?php
                                        echo "<input type='text' id='responsable' name='responsable' placeholder='Responsable' class='form-control' value='$responsable' autocomplete='off'>";
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="correo">Correo</label>
                                        <?php
                                        echo "<input type='email' id='correo' name='correo' placeholder='Correo' class='form-control' value='$correo'>";
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
                                        <label for="rfc">RFC</label>
                                        <?php
                                        echo "<input type='text' id='rfc' name='rfc' placeholder='RFC' class='form-control' value='$rfc'>";
                                        ?>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="regimen_fiscal">Regímen fiscal</label>
                                        <select class='form-control' id='regimen_fiscal'>
                                            <option value='0'>Seleccione</option>
                                            <?php
                                            while ($regimen = $rregimen->fetch_assoc()) {
                                                if ($regimen['pk_regimen_fiscal'] == $fk_regimen_fiscal) {
                                                    echo "<option value='$regimen[pk_regimen_fiscal]' selected>$regimen[clave]-$regimen[descripcion]</option>";
                                                } else {
                                                    echo "<option value='$regimen[pk_regimen_fiscal]'>$regimen[clave]-$regimen[descripcion]</option>";
                                                }
                                            }
                                            ?>
                                        </select>
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
                                        <label for="cp">Código postal</label>
                                        <?php
                                        echo "<input type='text' id='cp' name='cp' placeholder='Código postal' class='form-control' value='$cp'>";
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="cer">Formato CER</label>
                                        <input type="file" class="form-control" id="cer" name="cer" placeholder="Formato CER">
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="key">Formato KEY</label>
                                        <input type="file" class="form-control" id="key" name="key" placeholder="Formato KEY">
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="password">Contraseña de datos físcales</label>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <?php
                                            echo "<input type='password' id='pass' name='password' placeholder='Contraseña' class='form-control' value='$pass'>";
                                            echo "<input type='hidden' id='pk_empresa' name='text-input' value='$pk_empresa'>";
                                            ?>
                                            <div class="d-flex justify-content-center align-items-center"><i class='bx bx-low-vision' data-title='Ver contraseña' id="ver_password" style="font-size:24px; color: #918D8D; cursor:pointer;"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <br>

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
    <script src="custom/jquery.confirm.js"></script>
    <script src="custom/jquery.maskedinput.js"></script>
    <script src="assets/loading/loadingoverlay.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="custom/editarEmpresa.js"></script>
</body>

</html>
