<?php
header('Cache-control: private');
include("servicios/conexioni.php");
@session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
}

$nivel = $_SESSION["nivel"];

if ($nivel == 1) {
    $tipo = "SuperAdmin";
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


if (isset($_GET['id']) && is_string($_GET['id'])) {
    $pk_plantilla = (int)$_GET['id'];
}


//DATOS
#region
$nombre = "";
if ($pk_plantilla > 0) {

    $mysqli->next_result();
    if (!$rsp_get_plantilla = $mysqli->query("SELECT * FROM ct_plantillas WHERE pk_plantilla = $pk_plantilla AND estado = 1")) {
        echo "Lo sentimos, esta aplicación está experimentando problemas.";
        exit;
    }

    $row = $rsp_get_plantilla->fetch_assoc();
    $nombre = $row['nombre'];

    $qdetalle = "SELECT ctd.*,
            ctp.nombre,
            ctt.descripcion as presentacion
        FROM ct_plantillas_detalle ctd
        JOIN ct_productos ctp ON ctp.pk_producto = ctd.fk_insumo
        LEFT JOIN ct_presentaciones ctt ON ctt.pk_presentacion = ctp.fk_presentacion
        WHERE ctd.fk_plantilla = $pk_plantilla
        AND ctd.estado = 1
        ORDER BY ctp.nombre";

    $mysqli->next_result();
    if (!$rsp_get_detalle = $mysqli->query($qdetalle)) {
        echo "Lo sentimos, esta aplicación está experimentando problemas.";
        exit;
    }
}
#endregion



//PRODUCTOS
#region
$qproductos = "SELECT ctp.*,
        ctt.descripcion as presentacion
    FROM ct_productos ctp
    LEFT JOIN ct_presentaciones ctt ON ctt.pk_presentacion = ctp.fk_presentacion
    WHERE ctp.estado = 1
    ORDER BY ctp.nombre";

$mysqli->next_result();
if (!$rsp_get_productos = $mysqli->query($qproductos)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}
#endregion

?>

<!DOCTYPE html>
<html lang="en">

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
                            <h4 class="card-title">Agregar plantilla</h4>
                            <i class='bx bx-area' style="font-size:32px"></i>
                            <?php
                            echo <<<HTML
                                <input type="hidden" id="pk_plantilla" value="$pk_plantilla">
                            HTML;
                            ?>
                        </div>
                        <form class="forms-sample" enctype="multipart/form-data" id="formuploadajax">

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="nombre" class="d-flex gap-2 align-items-center"> <i class="bx bx-text fs-5"></i> Nombre de la plantilla</label>
                                        <?php
                                        echo <<<HTML
                                            <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre de la plantilla" value="$nombre" autocomplete="off">
                                        HTML;
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="fk_insumo" class="d-flex gap-2 align-items-center" style="margin-bottom: 16px;"> <i class="bx bx-cube fs-5"></i> Agregar productos</label>
                                        <select class="select2 form-control select2-hidden-accessible" style="width: 100%;" id="fk_insumo">
                                            <option value="0">SELECCIONE</option>
                                            <?php
                                            while ($rowi = $rsp_get_productos->fetch_assoc()) {
                                                echo "<option value='$rowi[pk_producto]' data-presentacion='$rowi[presentacion]'>$rowi[nombre]</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive overflow-auto scroll-style">
                                <table id='dtInsumos' class='table table-striped'>
                                    <thead class="table-dark">
                                        <tr>
                                            <th></th>
                                            <th>NOMBRE</th>
                                            <th>CANTIDAD</th>
                                            <th>PRESENTACION</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if ($pk_plantilla > 0) {
                                            while ($rowd = $rsp_get_detalle->fetch_assoc()) {

                                                echo <<<HTML
                                                    <tr data-id="$rowd[fk_insumo]">
                                                        <td><i class='bx bx-x fs-3 eliminar-insumo' style='padding: 3px; color: red; cursor: pointer;'></i></td>
                                                        <td>$rowd[nombre]</td>
                                                        <td><input type="number" class="form-control input-cantidad" min="1" value="$rowd[cantidad]" autocomplete="off" style="width: 100px;"></td>
                                                        <td>$rowd[presentacion]</td>
                                                    </tr>
                                                HTML;
                                            }
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>


                            <br><br>


                            <div class="row d-flex justify-content-center">
                                <?php if ($pk_plantilla > 0): ?>
                                    <div class="row col-lg-4 m-2">
                                        <button id="eliminar" type="button" class="btn btn-danger mx-2 d-flex justify-content-center align-items-center"><i class='bx bx-x-circle mx-2' style="font-size: 20px;"></i>Eliminar</button>
                                    </div>
                                <?php endif; ?>
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
    <script src="assets/loading/loadingoverlay.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="custom/agregarPlantilla.js"></script>

</body>
