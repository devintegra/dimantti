<?php
header('Cache-control: private');
include("servicios/conexioni.php");
mysqli_set_charset($mysqli, 'utf8');
@session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
}

$nivel = $_SESSION["nivel"];
$usuario = $_SESSION["usuario"];

if (isset($_GET['id']) && is_string($_GET['id'])) {
    $pk_orden = (int)$_GET['id'];
}

if ($nivel == 1) {
    $tipo = "Administrador";
    $menu = "fragments/menua.php";
}

if ($nivel == 2) {
    $pk_sucursal = $_SESSION["pk_sucursal"];
    $tipo = "Vendedor";
    $menu = "fragments/menub.php";


    $qorden = "SELECT * FROM tr_ordenes where pk_orden=$pk_orden and fk_sucursal=$pk_sucursal";

    if (!$rorden = $mysqli->query($qorden)) {
        $error = 1;
    }

    if ($rorden->num_rows == 0) {
        header('Location: ../index.php');
    }
}

if ($nivel == 3) {
    $pk_sucursal = $_SESSION["pk_sucursal"];
    $tipo = "Tecnico";
    $menu = "fragments/menuc.php";


    $qorden = "SELECT * FROM tr_ordenes where pk_orden=$pk_orden and fk_sucursal=$pk_sucursal";

    if (!$rorden = $mysqli->query($qorden)) {
        $error = 1;
    }

    if ($rorden->num_rows == 0) {
        header('Location: ../index.php');
    }
}


if ($nivel != 1 && $nivel != 2 && $nivel != 3) {
    header('Location: ../index.php');
}


//ARCHIVOS
#region
$mysqli->next_result();
if (!$rimagenes = $mysqli->query("CALL sp_get_orden_archivos($pk_orden)")) {
    $error = 1;
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
                            <h4 class="card-title">Agregar imágenes</h4>
                            <i class='bx bx-image-add' style="font-size:32px"></i>
                        </div>
                        <form class="forms-sample" method="post" enctype="multipart/form-data" id="formuploadajax">

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <?php echo "<input type='hidden' id='pk_orden' value='$pk_orden'/><input type='hidden' id='usuario' value='$usuario'/>"; ?>
                                        <label for="archivo">Imagen</label>
                                        <input type="file" id="archivo" name="archivo" class="form-control" accept="image/*">
                                    </div>
                                </div>
                            </div>

                            <button id="guardar" type="button" class="btn btn-primary-dast mx-2"><i class="fa fa-save mx-2"></i>Guardar</button>

                            <br><br>

                            <hr>

                            <h4 class="card-title">Imágenes</h4>

                            <div class="table-responsive overflow-hidden">

                                <table id="entradas" class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Imagen</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        <?php

                                        while ($imagenes = $rimagenes->fetch_assoc()) {

                                            echo <<<HTML
                                                <tr>
                                                    <td>
                                                        <img class='imreg' style='width:120px; height:120px; border-radius:10px' src='servicios/pruebas/$imagenes[archivo]'/>
                                                    </td>
                                                </tr>
                                            HTML;
                                        }

                                        ?>

                                    </tbody>

                                </table>

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
    <script src="custom/jquery.numeric.js"></script>
    <script src="custom/jquery.maskedinput.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="assets/loading/loadingoverlay.js"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="custom/fotos.js"></script>

    <script>
        function archivo(evt) {
            var files = evt.target.files; // FileList object

            // Obtenemos la imagen del campo "file".
            for (var i = 0, f; f = files[i]; i++) {
                //Solo admitimos imágenes.
                if (!f.type.match('image.*')) {
                    continue;
                }

                var reader = new FileReader();

                reader.onload = (function(theFile) {
                    return function(e) {
                        // Insertamos la imagen
                        document.getElementById("list").innerHTML = ['<img class="thumb" src="', e.target.result, '" title="', escape(theFile.name), '"/>'].join('');
                    };
                })(f);

                reader.readAsDataURL(f);
            }
        }

        document.getElementById('archivo').addEventListener('change', archivo, false);
    </script>

</body>

</html>
