<?php
header('Cache-control: private');
date_default_timezone_set('America/Mexico_City');
include("servicios/conexioni.php");
@session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
}

$nivel = $_SESSION["nivel"];
$usuario = $_SESSION["usuario"];
$fk_sucursal = 0;


if ($nivel == 1) {
    $tipo = "Administrador";
    $menu = "fragments/menua.php";
}

if ($nivel == 2) {
    $tipo = "Chofer";
    $menu = "fragments/menub.php";
    $fk_sucursal = $_SESSION["pk_sucursal"];
}

if ($nivel != 1) {
    header('Location: ../index.php');
}

mysqli_set_charset($mysqli, 'utf8');


$dias_credito = 0;
$limite_credito = 0;
$credito = 0;
$pk_cotizacion = 0;



//FILTROS
#region
$filtro = "";
$filtroexi = "";
if ($fk_sucursal != 0) {
    $filtro = " AND ct_usuarios.fk_sucursal in ($fk_sucursal, 0)";
    $filtroexi = " AND tr_existencias.fk_sucursal = $fk_sucursal";
}
#endregion



//USUARIOS
#region
$qusuarioss = "SELECT * FROM ct_usuarios WHERE estado = 1$filtro";

if (!$rusuarioss = $mysqli->query($qusuarioss)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. 1";
    exit;
}
#endregion



//CLIENTES
#region
$qclientes = "SELECT * FROM ct_clientes where estado = 1";

if (!$rclientes = $mysqli->query($qclientes)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. 1";
    exit;
}
#endregion



//PRODUCTOS
#region
$qproductos = "SELECT * FROM ct_productos where estado = 1";

if (!$rproductos = $mysqli->query($qproductos)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. 1";
    exit;
}
#endregion



//MÉTODOS DE PAGOS
#region
$qpagos = "SELECT * FROM ct_pagos where estado=1";

if (!$rpagos = $mysqli->query($qpagos)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. 2";
    exit;
}
#endregion



//SALDOS INICIALES
#region
$qsaldos = "SELECT * FROM tr_saldos_iniciales WHERE fk_usuario = '$usuario' AND estado = 1 ORDER BY pk_saldo_inicial DESC LIMIT 1";

if (!$rsaldos = $mysqli->query($qsaldos)) {
    echo "<br>Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

if ($rsaldos->num_rows == 0) {
    $existe_corte = 0;
} else {
    $saldos = $rsaldos->fetch_assoc();
    $saldo_corte = $saldos['fk_corte'];
    if ($saldo_corte > 0) {
        $existe_corte = 0;
    } else {
        $existe_corte = 1;
    }
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

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.18/css/bootstrap-select.min.css" integrity="sha512-ARJR74swou2y0Q2V9k0GbzQ/5vJ2RBSoCWokg4zkfM29Fb3vZEQyv0iWBMW/yvKgyHSR/7D64pFMmU8nYmbRkg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="css/vertical-layout-light/style.css">
    <link rel="stylesheet" href="css/estilos.css">

    <link rel="shortcut icon" href="images/user-sbg.png" />

    <style>
        .select2-selection__choice {
            display: none;
            /* Oculta las opciones seleccionadas en la caja de texto */
        }
    </style>

</head>

<body>



    <?php include $menu ?>


    <div class="main-panel">

        <div class="row justify-content-center px-4">


            <div class="col-sm-12 col-xl-8 grid-margin stretch-card" style="height: fit-content;">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex flex-column flex-lg-row justify-content-between">
                            <h4 class="card-title">Punto de venta</h4><br>
                            <h6 class="card-title badge-warning-integra" id="lasucursal"></h6>
                            <div>
                                <a href="agregarCorte.php" class="w-auto"><button type="button" class="btn btn-social-icon-text btn-linkedin"><i class='bx bx-calculator'></i>Corte de caja</button></a>
                                <a href="agregarRetiro.php" class="w-auto"><button type="button" class="btn btn-social-icon-text btn-google"><i class='bx bx-exit'></i>Salida de caja</button></a>
                                <?php
                                echo <<<HTML
                                    <input type='hidden' id='sucursal' class='form-control' value='$fk_sucursal'>
                                    <input type='hidden' id='fk_almacen' class='form-control' value=''>
                                    <input type='hidden' id='usuario_sesion' class='form-control' value='$usuario'>
                                    <input type='hidden' id='fk_cotizacion' class='form-control' value='$pk_cotizacion'>
                                    <input type='hidden' id='existe_corte' class='form-control' value='$existe_corte'>
                                    <input type='hidden' id='fk_usuario' class='form-control' value='$usuario'>
                                HTML;
                                ?>
                            </div>
                        </div>


                        <br><br>

                        <div class="row d-flex">
                            <div class="col-lg-6">
                                <div class="form-group gap-3 d-flex flex-column">
                                    <label for="cliente" style="font-size: 16px; font-weight: bold;">Cliente: </label>
                                    <?php
                                    if ($pk_cotizacion) {
                                        echo "<input type='hidden' id='cliente' class='form-control' value='$fk_cliente'>
                                        <input type='text' id='clientenom' style='margin-top: -17px; padding: 16px;' class='form-control' value='$cliente' disabled>";
                                    } else {
                                        echo "<select id='cliente' class='select2 form-control'>";
                                        while ($clientes = $rclientes->fetch_assoc()) {
                                            echo "<option value='$clientes[pk_cliente]'>$clientes[nombre]</option>";
                                        }
                                        echo "</select>";
                                    }

                                    echo <<<HTML
                                        <input type='hidden' id='cliente_dias' class='form-control' value='$dias_credito'>
                                        <input type='hidden' id='cliente_limite' class='form-control' value='$limite_credito'>
                                        <input type='hidden' id='cliente_credito' class='form-control' value='$credito'>
                                    HTML;
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group gap-3">
                                    <label for="clave" style="font-size: 16px; font-weight: bold;">Agregar producto: </label>
                                    <select id="clave" class="select2 form-control select2-hidden-accessible" style="width: 100%;" autocomplete="off" multiple>
                                    </select>
                                </div>
                            </div>

                        </div>

                        <div class="table-responsive overflow-auto scroll-style latabla">
                            <table id='entradas' class='table table-striped'>
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Imagen</th>
                                        <th>Producto</th>
                                        <th>Precio</th>
                                        <th>Precio final</th>
                                        <th>Cantidad</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>



            <div class="col-sm-12 col-xl-4 grid-margin stretch-card" style="height: fit-content;">
                <div class="card" style="height: auto; border-top: 3px solid #71c55b;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title" style="color: #4ea93b; font-weight: bold;">Check-in</h4>
                        </div>

                        <div class="d-none d-lg-block">

                            <div class="row">
                                <div class="col-sm-6 col-lg-4 p-0">
                                    <button class="btn-billete">
                                        <img id="billete-20" src="images/billetes/20pesos.jpg">
                                    </button>
                                </div>
                                <div class="col-sm-6 col-lg-4 p-0">
                                    <button class="btn-billete">
                                        <img id="billete-50" src="images/billetes/50pesos.jpg">
                                    </button>
                                </div>
                                <div class="col-sm-6 col-lg-4 p-0">
                                    <button class="btn-billete">
                                        <img id="billete-100" src="images/billetes/100pesos.jpg">
                                    </button>
                                </div>
                            </div>

                            <br>

                            <div class="row">
                                <div class="col-sm-6 col-lg-4 p-0">
                                    <button class="btn-billete">
                                        <img id="billete-200" src="images/billetes/200pesos.jpg">
                                    </button>
                                </div>
                                <div class="col-sm-6 col-lg-4 p-0">
                                    <button class="btn-billete">
                                        <img id="billete-500" src="images/billetes/500pesos.jpg">
                                    </button>
                                </div>
                                <div class="col-sm-6 col-lg-4 p-0">
                                    <button class="btn-billete">
                                        <img id="billete-1000" src="images/billetes/1000pesos.jpg">
                                    </button>
                                </div>
                            </div>

                        </div>

                        <br><br>

                        <div class="row">
                            <div class="d-flex justify-content-between">
                                <h6 style="font-weight: bold;">Subtotal</h6>
                                <h6 id="subtotal">$0.00</h6>
                            </div>

                            <br><br>

                            <div class="d-flex justify-content-between">
                                <div class="col-sm-12 col-lg-6">
                                    <h6 style="font-weight: bold;">Descuento</h6>
                                </div>
                                <div class="col-sm-12 col-lg-6 d-flex justify-content-end gap-2">
                                    <i class='bx bxs-lock-open-alt fs-4 btnModalDescuento' style="cursor: pointer;" title="Desbloquear descuento"></i>
                                    <input type="hidden" class="form-control" id="descuento" min="0" value="0" disabled>
                                </div>
                            </div>



                            <br><br><br>

                            <hr style="height: 3px; background-color: #4169e1">

                            <br><br><br>



                            <div class="d-flex justify-content-between">
                                <div class="col-sm-12 col-lg-6">
                                    <h6 style="font-weight: bold;">Recibe</h6>
                                </div>
                                <div class="col-sm-12 col-lg-6">
                                    <input type="number" class="form-control" id="recibe" min="0" value="0">
                                </div>
                            </div>

                            <br><br>

                            <div class="d-flex justify-content-between">
                                <h6 style="font-weight: bold;">Cambio</h6>
                                <h6 id="cambio">$0.00</h6>
                            </div>



                            <br><br><br>

                            <hr style="height: 3px; background-color: #4169e1">

                            <br><br><br>



                            <div class="d-flex justify-content-between">
                                <h3 style="font-weight: bold;">TOTAL</h3>
                                <h3 id="total" style="color: #4169e1">$0.00</h4>
                            </div>

                            <br><br><br>

                            <div class="d-flex justify-content-center">
                                <button id="registrar" type="button" class="btn btn-primary-dast mx-2"><i class="fa fa-save mx-2"></i>Registrar venta</button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>


        </div>
    </div>




    <div class="modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-bs-backdrop="static" id="modalEmpresa">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-between align-items-center" style="background-color: #16A34A;">
                    <div class="d-flex justify-content-start align-items-center">
                        <i class='bx bx-store-alt mx-2 fs-1'></i>
                        <h4 class="modal-title ltse" id="myModalLabel" style="font-size: 24px!important">Seleccione almacén</h4>
                    </div>
                    <div>
                        <button class="btn btn-danger" onclick="$(location).attr('href','verVentas.php')">Cerrar</button>
                    </div>
                </div>
                <div class="modal-body" id="tablaEmpresa">
                    <table id="dtEmpresa" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Sucursal - Almacén</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


    <div class="modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-bs-backdrop="static" id="modalTicket">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-between align-items-center" style="background-color: #71c55b;">
                    <div class="d-flex justify-content-start align-items-center">
                        <i class='bx bx-cart-alt mx-2 fs-1'></i>
                        <h4 class="modal-title ltse" id="myModalLabel" style="font-size: 24px!important">Finalizar compra</h4>
                    </div>
                    <div>
                        <button class="btn btn-danger" onclick="$('#modalTicket').modal('hide')">Cerrar</button>
                    </div>
                </div>
                <div class="modal-body">

                    <div class="row" style="text-align: center;">
                        <div style="text-align: center;">
                            <h5 style="color: #4169e1">Total a pagar</h5>
                            <h1 id="ticket_total" style="color: #4169e1; font-weight: bold;"></h1>
                            <input type="hidden" id="ticket_total_tmp" disabled>
                        </div>

                        <div style="text-align: center;">
                            <h6 style="color: #71c55b">Comisión</h6>
                            <div class="d-flex justify-content-center align-items-center">
                                <h3 id="ticket_comision" style="color: #71c55b; font-weight: bold;">$0.00</h3>

                                <div class='form-check form-check-success mx-4'>
                                    <label class='form-check-label fs-6' data-title="Al marcar la casilla se contabilizará la comisión, de lo contrario se ajustará a $0.00">
                                        <input type='checkbox' class='form-check-input chk-comision' value='1'>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <br>

                    <div class="row">
                        <div class="col-12 d-flex flex-wrap justify-content-center gap-3">
                            <?php
                            while ($pagos = $rpagos->fetch_assoc()) {

                                switch ($pagos['nombre']) {
                                    case 'Efectivo':
                                        $id = 'efectivo';
                                        $icon = 'bx-coin';
                                        $btn = '';
                                        break;
                                    case 'Tarjeta de crédito':
                                        $id = 'credito';
                                        $icon = 'bx-credit-card-alt';
                                        $btn = '';
                                        break;
                                    case 'Tarjeta de debito':
                                        $id = 'debito';
                                        $icon = 'bx-credit-card';
                                        $btn = '';
                                        break;
                                    case 'Cheque':
                                        $id = 'cheque';
                                        $icon = 'bx-edit';
                                        $btn = '<input type="text" class="form-control" id="cheque_referencia" placeholder="Referencia">';
                                        break;
                                    case 'Transferencia':
                                        $id = 'transferencia';
                                        $icon = 'bx-transfer-alt';
                                        $btn = '<input type="text" class="form-control" id="transferencia_referencia" placeholder="Referencia">';
                                        break;
                                }
                                echo <<<HTML
                                    <div class='col-sm-3 col-lg-2 d-flex align-items-center flex-column'>
                                        <i class='bx $icon' style='font-size: 28px;'></i>
                                        <h6>$pagos[nombre]</h6>
                                        <input type='number' class='form-control pago_input' id='$id' value='0.00' min='0'>
                                        <input type='hidden' class='form-control' id='comision_$id' value='$pagos[comision]' min='0'>$btn
                                    </div>
                                HTML;
                            }
                            ?>
                        </div>
                    </div>

                    <br><br>

                    <div class="row">
                        <div class="form-group d-flex justify-content-center">
                            <div class="col-12 col-lg-10">
                                <label for="observaciones"><i class='bx bx-note mx-2' style="font-size: 20px;"></i> Observaciones</label>
                                <textarea class="form-control" id="observaciones" cols="30" rows="10" style="height: 100px;" placeholder="Observaciones (ej.series)"></textarea>
                            </div>
                        </div>
                    </div>

                    <br><br>

                    <div class="row d-flex flex-wrap justify-content-center">
                        <div class="col-12 col-lg-10 d-flex flex-wrap">
                            <div class="col-sm-12 col-lg-4">
                                <div class="d-flex align-items-center">
                                    <i class='bx bx-user-plus mx-2' style="font-size: 28px;"></i>
                                    <h4 id="ticket_cliente" style="font-weight: bold;"></h4>
                                </div>

                                <button type="button" id="asignar_credito" class="btn btn-social-icon-text btn-google"><i class='bx bx-data'></i>Asignar crédito</button>
                            </div>

                            <div class="col-sm-12 col-lg-4">
                                <div class="d-flex align-items-center flex-column nota-credito-content">
                                </div>
                            </div>

                            <div class="col-sm-12 col-lg-4 d-flex justify-content-end align-items-end flex-column">
                                <h5 style="color: #4169e1">Cambio</h5>
                                <h3 id="ticket_cambio" style="font-weight: bold;">
                                </h3>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button id="guardar" type="button" class="btn btn-primary-dast mx-2"><i class="fa fa-save mx-2"></i>Registrar venta</button>
                </div>
            </div>
        </div>
    </div>


    <div class="modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-bs-backdrop="static" id="modalCreditos">
        <div class="modal-dialog modal-lg w-100 w-lg-50">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-between align-items-center" style="background-color: #ff4040;">
                    <div class="d-flex justify-content-start align-items-center">
                        <i class='bx bx-user-plus mx-2 fs-1'></i>
                        <h4 class="modal-title ltse" id="myModalLabel" style="font-size: 24px!important">Asignar crédito</h4>
                    </div>
                </div>
                <div class="modal-body">

                    <div class="row" style="text-align: center;">
                        <div style="text-align: center;">
                            <h3 id="credito_cliente" style="font-weight: bold"></h3>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6 col-lg-4">
                            <h5 style="font-weight: bold;">Días de crédito</h5>
                            <h5 id="credito_dias" class="badge-primary-integra">0</h5>
                        </div>
                        <div class="col-sm-6 col-lg-4">
                            <h5 style="font-weight: bold;">Límite de crédito</h5>
                            <h5 id="credito_limite" class="badge-warning-integra">0</h5>
                        </div>
                        <div class="col-sm-6 col-lg-4">
                            <h5 style="font-weight: bold;">Crédito disponible</h5>
                            <h5 id="credito_disponible" class="badge-success-integra">0</h5>
                        </div>
                    </div>

                    <div class="row my-1">
                        <h3 id="total_venta_credito" class="text-center" style="font-weight: bold; color: #2563EB"></h3>
                    </div>

                    <div class="row">
                        <div class="col-sm-12 col-lg-6">
                            <div class="form-group">
                                <label for="credito_tipo_pago">Forma de pago</label>
                                <select class="form-control" id="credito_tipo_pago">
                                    <option value="0" selected>Seleccione</option>
                                    <option value="10">Sin especificar</option>
                                    <?php
                                    $qtipospagos = "SELECT * FROM ct_pagos WHERE estado = 1";
                                    if (!$rtipospagos = $mysqli->query($qtipospagos)) {
                                        echo "Lo sentimos, esta aplicación está experimentando problemas.";
                                        exit;
                                    }
                                    while ($tipospagos = $rtipospagos->fetch_assoc()) {
                                        echo "<option value='$tipospagos[pk_pago]'>$tipospagos[nombre]</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-sm-12 col-lg-6">
                            <div class="form-group">
                                <label for="credito_total">$ Anticipo</label>
                                <input type="number" id="credito_total" class="form-control" value="0.00" disabled>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12 col-lg-6">
                            <div class="form-group">
                                <label for="credito_fecha"><i class='bx bx-calendar mx-2'></i> Fecha de vencimiento</label>
                                <input type="text" id="credito_fecha" class="form-control datepicker" placeholder="0000-00-00" disabled>
                            </div>
                        </div>

                        <div class="col-sm-12 col-lg-6">
                            <div class="form-group">
                                <label for="credito_a_saldar">$ Total a crédito</label>
                                <input type="number" id="credito_a_saldar" class="form-control" value="0.00" disabled>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="d-none" id="credito_advertencia">
                            <p class="badge-danger-integra"><i class='bx bx-info-circle mx-2'></i> El monto de la venta <span style="font-weight: bold;">supera el crédito disponible</span> del cliente.</p>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button" id="credito_cerrar" class="btn btn-danger mx-2"></i>Cerrar</button>
                    <button type="button" id="guardar_credito" class="btn btn-primary-dast mx-2"><i class="fa fa-save mx-2"></i>Registrar venta</button>

                </div>
            </div>
        </div>
    </div>


    <div class="modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-bs-backdrop="static" id="modalSaldoInicial">
        <div class="modal-dialog modal-lg" style="width: 40%;">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-between align-items-center" style="background-color: #5468ff;">
                    <div class="d-flex justify-content-start align-items-center">
                        <i class='bx bx-coin-stack mx-2 fs-1'></i>
                        <h4 class="modal-title ltse" id="myModalLabel" style="font-size: 24px!important">Saldo inicial en caja</h4>
                    </div>
                </div>
                <div class="modal-body">

                    <div class="row">
                        <div class="col-sm-12 col-lg-6">
                            <div class="form-group">
                                <label for="saldo_inicial">Saldo inicial</label>
                                <input type="number" id="saldo_inicial" class="form-control" placeholder="Cantidad inicial">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="observaciones_saldo">Observaciones</label>
                                <textarea class="form-control" id="observaciones_saldo" cols="30" rows="10" placeholder="Comentarios u observaciones" style="height: 100px;"></textarea>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-danger" onclick="$(location).attr('href','verVentas.php')">Cerrar</button>
                    <button type="button" id="guardar_saldo" class="btn btn-primary-dast mx-2"><i class="fa fa-save mx-2"></i>Guardar</button>
                </div>
            </div>
        </div>
    </div>


    <div class="modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-bs-backdrop="static" id="modalDescuento">
        <div class="modal-dialog modal-lg" style="width: 40%;">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-between align-items-center" style="background-color: #5468ff;">
                    <div class="d-flex justify-content-start align-items-center">
                        <i class='bx bx-lock-open-alt mx-2 fs-1'></i>
                        <h4 class="modal-title ltse" id="myModalLabel" style="font-size: 24px!important">Desbloquear descuento</h4>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12 col-xl-6">
                            <div class="form-group">
                                <label for="usuario_descuento">Usuario administrador</label>
                                <input type="text" id="usuario_descuento" class="form-control" placeholder="Usuario de acceso" autocomplete="off">
                            </div>
                        </div>

                        <div class="col-sm-12 col-xl-6">
                            <div class="form-group">
                                <label for="pass_descuento">Contraseña</label>
                                <input type="password" id="pass_descuento" class="form-control" placeholder="Contraseña del usuario" autocomplete="off">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="$('#modalDescuento').modal('hide')" class="btn btn-danger mx-2"></i>Cerrar</button>
                    <button type="button" id="desbloquer_descuento" class="btn btn-primary-dast mx-2"><i class="fa fa-unlock-alt mx-2"></i>Desbloquear</button>
                </div>
            </div>
        </div>
    </div>


    <div class="modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static" id="modalImagenesProducto">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header text-center" style="background-color: #BFDBFE; color:#000;">
                    <h5 class="text-left elproductoimg"></h5>
                </div>
                <div class="modal-body">
                    <div id="carouselImgProducto" class="carousel slide">
                        <div class="carousel-inner carouselImgProductoContent">
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#carouselImgProducto" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#carouselImgProducto" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                    </>
                </div>
                <div class="modal-footer d-flex justify-content-end">
                    <button type="button" onclick="$('#modalImagenesProducto').modal('hide')" class="btn btn-danger mx-2"></i>Cerrar</button>
                </div>
            </div>
        </div>
    </div>


    <div class="modal" id="exito" tabindex="-1" role="dialog" aria-hidden="true" data-bs-keyboard="false" data-bs-backdrop="static">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header text-center" style="background-color: #BFDBFE; color:#000;">
                    <h2 class="text-center exitot">Venta <span id="nentrada"></span> </h2>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12 col-md-6 text-center">
                            <button id="nueva" class="btn btn-success pdfb shadow"><i class="fa fa-plus fa-5x" aria-hidden="true"></i><br><br>NUEVA VENTA</a>
                        </div>
                        <div class="col-sm-12 col-md-6 text-center">
                            <a target="_blank" id="pdf" class="btn btn-danger pdfb"><i class="fa fa-file-pdf-o fa-5x" aria-hidden="true"></i><br><br>TICKET PDF</a>
                        </div>
                    </div>
                </div>
                <div class="modal-footer text-center">
                    <div class="col-sm-12">
                        <button id="historial" type="button" class="btn btn-sm btn-info">Historial de ventas</button>
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
    <script src="assets/lib/data-table/buttons.bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <script>
        //INPUTS DE FILTRADO POR COLUMNA
        $('#dtEmpresa tfoot th').each(function() {
            var title = $(this).text().trim();
            if (title != '') {
                $(this).html('<input type="text" class="form-control" placeholder="Buscar ' + title + '" />');
            }
        });

        $('#dtEmpresa').DataTable({
            initComplete: function() {
                // Aplicar la búsqueda
                this.api()
                    .columns()
                    .every(function() {
                        var that = this;

                        $('input', this.footer()).on('keyup change clear', function() {
                            if (that.search() !== this.value) {
                                that.search(this.value).draw();
                            }
                        });
                    });
            },

            responsive: true,
            ordering: true,
            pageLength: 10,
            dom: '<"dtEmpresa_header"lfp><t><rip>',
            //lfptrip
            language: {
                "lengthMenu": "Mostrando _MENU_ registros por pagina",
                "search": "Buscar:",
                "zeroRecords": "No hay registros",
                "info": "Mostrando pagina _PAGE_ de _PAGES_",
                "infoEmpty": "Sin registros disponibles",
                "infoFiltered": "(filtrando de _MAX_ registros totales)",
                "paginate": {
                    "previous": "Anterior",
                    "next": "Siguiente"
                }
            }

        });
    </script>

    <script src="assets/loading/loadingoverlay.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="custom/puntoVenta.js"></script>

</body>

</html>
