<?php
header('Cache-control: private');
include("servicios/conexioni.php");
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
    $tipo = "Vendedor";
    $menu = "fragments/menub.php";
}


if ($nivel != 1) {
    header('Location: ../index.php');
}


//REGIMENES FISCALES
#region
$qregimen = "SELECT * FROM ct_regimenes_fiscales where estado=1";

if (!$rregimen = $mysqli->query($qregimen)) {
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
                            <h4 class="card-title">Nuevo cliente</h4>
                            <i class='bx bx-group' style="font-size:32px"></i>
                        </div><br>
                        <form class="forms-sample" enctype="multipart/form-data" id="formuploadajax">

                            <h4 class="card-title col-lg-4 fs-4 d-flex justify-content-start align-items-center gap-2" style="color: #2563EB; font-weight: bold;"><i class='bx bx-user-circle fs-4'></i>Datos generales</h4>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="nombre">Razón social</label>
                                        <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Razón social" autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="telefono">Teléfono</label>
                                        <input type="text" class="form-control" id="telefono" name="telefono" placeholder="Teléfono" autocomplete="off">
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
                                        <label for="cp">Código postal</label>
                                        <input type="text" class="form-control" id="cp" name="cp" placeholder="Código postal" autocomplete="off">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="rfc">RFC</label>
                                        <input type="text" class="form-control" id="rfc" name="rfc" placeholder="RFC" autocomplete="off">
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="regimen_fiscal">Regímen fiscal</label>
                                        <select class="form-control" id="regimen_fiscal">
                                            <option value="0">Seleccione</option>
                                            <?php
                                            while ($regimen = $rregimen->fetch_assoc()) {
                                                echo "<option value='$regimen[pk_regimen_fiscal]'>$regimen[clave]-$regimen[descripcion]</option>";
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
                                        <input type="text" class="form-control" id="direccion" name="direccion" placeholder="Dirección" autocomplete="off">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12 form-group" style="height: 200px;">
                                    <?php
                                    echo "<input type='hidden' id='empresa' name='text-input' value='$empresa'>";
                                    ?>
                                    <div id="mapa" style="height: 100%;"></div>
                                </div>
                            </div>



                            <br>
                            <div class="line-success-integra"></div><br>

                            <h4 class="card-title col-lg-4 fs-4 d-flex justify-content-start align-items-center gap-2" style="color: #2CA880; font-weight: bold;"><i class='bx bx-data fs-4'></i>Crédito</h4>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="abonos">¿Se aceptan abonos?</label>
                                        <select class="form-control" id="abonos">
                                            <option value="0">Seleccione</option>
                                            <option value="1">Si</option>
                                            <option value="2">No</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="dias_credito">Días de crédito</label>
                                        <input type="number" class="form-control" id="dias_credito" name="dias_credito" placeholder="Días de credito" value="0" min="0" autocomplete="off">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="limite_credito">$ Límite de crédito</label>
                                        <input type="text" class="form-control" id="limite_credito" name="limite_credito" placeholder="Límite de credito" value="0" min="0" autocomplete="off">
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="credito">$ Crédito disponible</label>
                                        <input type="text" class="form-control" id="credito" name="credito" placeholder="Crédito disponible" value="0" min="0" autocomplete="off">
                                    </div>
                                </div>
                            </div>


                            <br><br>


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
    <script src="custom/jquery.maskedinput.js"></script>
    <script src="assets/loading/loadingoverlay.js"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script type="text/javascript" src="https://maps.google.com/maps/api/js?key=AIzaSyAyzxxx9sOTmkGCHGgrK_Xy86eQxB-AxuI&libraries=places"></script>
    <script src="custom/numberFormats.js"></script>
    <script src="custom/agregarCliente.js?v=<?= time(); ?>"></script>

</body>

</html>
