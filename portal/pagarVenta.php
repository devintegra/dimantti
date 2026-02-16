<?php
header('Cache-control: private');
include("servicios/conexioni.php");
@session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
}


$nivel = $_SESSION["nivel"];
$empresa = $_SESSION["empresa"];
$usuario = $_SESSION["usuario"];
$fk_sucursal = $_SESSION["pk_sucursal"];


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

if (isset($_GET['id']) && is_string($_GET['id'])) {
    $pk_venta = (int)$_GET['id'];
}

//DATOS GENERALES
#region
$qventa = "SELECT tr_ventas.*,
    ct_clientes.nombre as cliente
    FROM tr_ventas, ct_clientes
    WHERE tr_ventas.pk_venta = $pk_venta
    AND ct_clientes.pk_cliente = tr_ventas.fk_cliente";

if (!$rventas = $mysqli->query($qventa)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.1";
    exit;
}

$ventas = $rventas->fetch_assoc();
$folio = $ventas["folio"];
$cliente = $ventas["cliente"];
$saldot = $ventas["saldo"];
$saldof = number_format($saldot, 2);
$total = number_format($ventas["total"], 2);

$qpagos = "SELECT tr_abonos.*,
    ct_pagos.nombre as pago
    FROM tr_abonos, ct_pagos
    WHERE tr_abonos.estado = 1
    AND tr_abonos.fk_factura = $pk_venta
    AND tr_abonos.origen = 1
    AND ct_pagos.pk_pago = tr_abonos.fk_pago";

if (!$rpagos = $mysqli->query($qpagos)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.2";
    exit;
}
#endregion


//PAGOS
#region
$qtipospagos = "SELECT * FROM ct_pagos WHERE estado = 1";

if (!$rtipospagos = $mysqli->query($qtipospagos)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. 2";
    exit;
}
#endregion


//SUCURSALES
#region
$qsucursales = "SELECT * FROM ct_sucursales WHERE estado = 1";

if (!$rsucursales = $mysqli->query($qsucursales)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. 2";
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
                            <h4 class="card-title">Agregar un pago
                                <?php
                                echo "<span class='badge-warning-integra mx-2'>$folio</span>";
                                echo "<span class='badge-primary-integra mx-2'>$cliente</span>";
                                echo "<span class='badge-success-integra mx-2'>$$total</span>";
                                ?>
                            </h4>
                            <h4 class="card-title p-2 badge-danger-integra">Saldo actual: $ <?php echo $saldof ?> </h4>
                        </div>

                        <form class="forms-sample" action="" method="post" enctype="multipart/form-data" id="formuploadajax">

                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="monto">Monto</label>
                                        <input type="text" class="form-control" id="monto" name="monto" placeholder="Monto">
                                        <?php
                                        echo "<input type='hidden' id='fk_venta' name='text-input' value='$pk_venta'>
                                        <input type='hidden' id='fk_usuario' name='text-input' value='$usuario'>
                                        <input type='hidden' id='saldo' name='text-input' value='$saldot'>";
                                        ?>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="fk_pago" class="form-control-label">Método de pago</label>
                                        <select id='fk_pago' class="form-control">
                                            <option value="0">Seleccione</option>
                                            <?php
                                            while ($tipospagos = $rtipospagos->fetch_assoc()) {
                                                echo "<option value='$tipospagos[pk_pago]' data-comision='$tipospagos[comision]'>$tipospagos[nombre]</option>";
                                            }
                                            ?>
                                        </select>
                                        <div class="comision-content"></div>
                                    </div>
                                </div>

                                <?php
                                if ($fk_sucursal == 0) {
                                    echo "
                                            <div class='col-lg-4'>
                                                <div class='form-group'>
                                                    <label for='fk_sucursal' class='form-control-label'>Sucursal que recibe</label>
                                                    <select id='fk_sucursal' class='form-control'>
                                                        <option value='0'>Seleccione</option>";
                                    while ($sucursales = $rsucursales->fetch_assoc()) {
                                        echo "<option value='$sucursales[pk_sucursal]'>$sucursales[nombre]</option>";
                                    }
                                    echo "</select>
                                                </div>
                                            </div>
                                        ";
                                } else {
                                    echo "<input type='hidden' id='fk_sucursal' name='text-input' value='$fk_sucursal'>";
                                }
                                ?>

                            </div>

                            <button id="agregar" type="button" class="btn btn-success-dast mx-2"><i class="fa fa-save mx-2"></i>Guardar</button>

                            <br>
                            <hr>

                            <div class="table-responsive overflow-hidden">
                                <table id="entradas" class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Entrada</th>
                                            <th>Fecha</th>
                                            <th>Método de pago</th>
                                            <th>Saldo</th>
                                            <th>Monto</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $total = 0.00;
                                        $npago = 1;
                                        while ($pagos = $rpagos->fetch_assoc()) {
                                            if ($pagos["monto"] > 0) {
                                                $total = $total + $pagos['monto'];
                                                $monto = number_format($pagos['monto'], 2);
                                                $saldo = number_format($pagos['saldo'], 2);
                                                echo "<tr class=\"odd gradeX\">
                                                    <td>$npago</td>
                                                    <td>$pagos[fecha] $pagos[hora]</td>
                                                    <td>$pagos[pago]</td>
                                                    <td>$$saldo</td>
                                                    <td>$$monto</td>
                                                </tr>";
                                                $npago++;
                                            }
                                        }
                                        ?>
                                    </tbody>
                                    <tfoot>
                                        <?php
                                        $total = number_format($total, 2);
                                        echo "<tr>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td id='total'>$$total</td>
                                            </tr>";
                                        ?>
                                    </tfoot>
                                </table>

                            </div>

                            <button id="imprimir" type="button" class="btn btn-primary-dast mx-2"><i class="bx bx-printer mx-2"></i>Imprimir</button>


                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>


    <div class="modal" id="exito" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header text-center">
                    <h2 class="text-center exitot">Recibo de pago</h2>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <a target="_blank" id="pdf" class="btn btn-danger pdfb"><i class="fa fa-file-pdf-o fa-5x" aria-hidden="true"></i><br><br>DESCARGAR PDF</a>
                        </div>
                    </div>
                </div>
                <div class="modal-footer text-center">
                    <div class="col-sm-12">
                        <button id="nueva" type="button" class="btn btn-sm btn-info">Generar Nuevo Pago</button>
                        <button id="inicio" type="button" class="btn btn-sm btn-success">Inicio</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script src="assets/vendor/jquery-2.1.4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js"></script>
    <script src="assets/vendor/bootstrap.min.js"></script>
    <script src="assets/plugins.js"></script>
    <script src="assets/main.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="custom/jquery.numeric.js"></script>
    <script src="custom/pagarVenta.js"></script>

</body>

</html>
