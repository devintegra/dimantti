<?php
header('Cache-control: private');
include("servicios/conexioni.php");
@session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
}

$nivel = $_SESSION["nivel"];
$sucursal = $_SESSION['fk_sucursal'];
$usuario = $_SESSION['usuario'];

if ($nivel == 1) {
    $tipo = "Administrador";
    $menu = "fragments/menua.php";
}

if ($nivel != 1) {
    header('Location: ../index.php');
}

mysqli_set_charset($mysqli, 'utf8');



//EMPLEADOS
#region
$mysqli->next_result();
if (!$rsp_get_empleados = $mysqli->query("SELECT * FROM ct_usuarios WHERE nivel != 1 AND estado = 1")) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. Error al obtener los empleados";
    exit;
}
#endregion


//TIPOS DE PAGO
#region
$mysqli->next_result();
if (!$rsp_get_pagos = $mysqli->query("SELECT * FROM ct_pagos WHERE estado = 1")) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. Error al obtener los tipos de pago";
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

    <!--partial-->
    <div class="main-panel">

        <div class="row justify-content-center">
            <div class="col-lg-10 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title">Nuevo préstamo</h4>
                            <i class='bx bx-dollar' style="font-size:32px"></i>
                            <?php echo "<input type='hidden' id='monto_abono' class='form-control' value='0'>"; ?>
                            <?php echo "<input type='hidden' id='sucursal' class='form-control' value='$sucursal'>"; ?>
                            <?php echo "<input type='hidden' id='usuario' class='form-control' value='$usuario'>"; ?>
                        </div>
                        <form class="forms-sample">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="nombre_empleado" style="margin-bottom: 22px;">Nombre del empleado</label>
                                        <select class="select2 form-control select2-hidden-accessible" style="width: 100%;" id="nombre_empleado">
                                            <option value="0">SELECCIONE</option>
                                            <?php
                                            while ($rows = $rsp_get_empleados->fetch_assoc()) {
                                                echo "<option value='$rows[pk_usuario]'>$rows[nombre]</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="monto">Monto</label>
                                        <input type="number" class="form-control" id="monto" name="monto" placeholder="Monto solicitado" autocomplete="off">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="frecuencia">Frecuencia del pago</label>
                                        <select class="form-control" id="frecuencia">
                                            <option value="0">SELECCIONE</option>
                                            <option value="1">SEMANAL</option>
                                            <option value="2">QUINCENAL</option>
                                            <option value="3">MENSUAL</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="cantidad_pagos">Cantidad de pagos</label>
                                        <input type="number" class="form-control" id="cantidad_pagos" name="cantidad_pagos" min="1" placeholder="1" autocomplete="off">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="pago_visible">Monto de cada abono</label>
                                        <input type="text" class="form-control" id="pago_visible" name="pago_visible" placeholder="0.00" autocomplete="off" disabled>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="pago">Tipo de pago</label>
                                        <select class="form-control" id="pago">
                                            <option value="0">SELECCIONE</option>
                                            <?php
                                            while ($rows = $rsp_get_pagos->fetch_assoc()) {
                                                echo "<option value='$rows[pk_pago]'>$rows[nombre]</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group">
                                    <label for="observaciones" class="d-flex align-items-center gap-2"> <i class="bx bx-edit-alt fs-4"></i>Observaciones</label>
                                    <textarea class="form-control" name="observaciones" id="observaciones" cols="30" rows="10" style="height: 150px;" placeholder="Describe el motivo del préstamo aquí..."></textarea>
                                </div>
                            </div>
                            <br>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="custom/agregarPrestamo.js"></script>

</body>

</html>
