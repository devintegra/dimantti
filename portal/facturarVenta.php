<?php
header('Cache-control: private');
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

if ($nivel == 10) {
    $tipo = "Cliente";
    $menu = "fragments/menux.php";
    $fk_sucursal = $_SESSION["pk_sucursal"];
}

if ($nivel != 1 && $nivel != 10) {
    header('Location: ../index.php');
}

mysqli_set_charset($mysqli, 'utf8');



if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $pk_venta = (int)$_GET['id'];
}

if (isset($_GET['tipo']) && is_numeric($_GET['tipo'])) {
    $tipo = (int)$_GET['tipo']; //1.VENTA INDIVIDUAL, 2.VARIAS VENTAS
}



//DATOS DE LA VENTA
#region
if ($tipo == 1) {
    $qventa = "SELECT tr_ventas.*,
        ct_clientes.nombre as cliente
        FROM tr_ventas, ct_clientes
        WHERE tr_ventas.pk_venta = $pk_venta
        AND ct_clientes.pk_cliente = tr_ventas.fk_cliente";

    if (!$rventa = $mysqli->query($qventa)) {
        echo "<br>Lo sentimos, esta aplicación está experimentando problemas. Error al obtener los datos de la venta";
        exit;
    }

    $venta = $rventa->fetch_assoc();
    $folio = $venta["folio"];
    $fk_cliente = $venta["fk_cliente"];
    $cliente_nombre = $venta["cliente"];
    $total_venta = number_format($venta["total"], 2);
} else {
    $total_venta = 0;
}
#endregion



//DETALLE
#region
if ($tipo == 1) {
    $qdetalle = "SELECT tr_ventas_detalle.*,
        CASE
            WHEN tr_ventas_detalle.fk_producto > 0 THEN ct_productos.nombre
            ELSE tr_ventas_detalle.descripcion
        END AS producto,
        SUM(cantidad) as cantidad_total,
        (SELECT imagen FROM rt_imagenes_productos WHERE fk_producto = tr_ventas_detalle.fk_producto AND estado = 1 LIMIT 1) as imagen
        FROM tr_ventas_detalle
        LEFT JOIN ct_productos ON ct_productos.pk_producto = tr_ventas_detalle.fk_producto
        WHERE tr_ventas_detalle.fk_venta = $pk_venta
        AND tr_ventas_detalle.estado = 1
        GROUP BY tr_ventas_detalle.fk_producto";

    if (!$rdetalle = $mysqli->query($qdetalle)) {
        echo "<br>Lo sentimos, esta aplicación está experimentando problemas. Error al obtener los productos de la venta";
        exit;
    }
}
#endregion



//DATOS DEL CLIENTE
#region
if ($tipo == 1) {
    $qcliente = "SELECT ct_clientes.*,
        ct_regimenes_fiscales.descripcion as regimen
        FROM ct_clientes, ct_regimenes_fiscales
        WHERE ct_clientes.pk_cliente = $fk_cliente
        AND ct_regimenes_fiscales.pk_regimen_fiscal = ct_clientes.fk_regimen_fiscal;";

    if (!$rcliente = $mysqli->query($qcliente)) {
        echo "<br>Lo sentimos, esta aplicación está experimentando problemas. Error al obtener los datos del cliente";
        exit;
    }

    $cliente = $rcliente->fetch_assoc();
    $cliente_correo = $cliente["correo"];
    $cliente_rfc = $cliente["rfc"];
    $cliente_cp = $cliente["cp"];
    $cliente_fk_regimen = $cliente["fk_regimen_fiscal"];
    $cliente_regimen = $cliente["regimen"];
}
#endregion




//DATOS DEL USUARIO
#region
$qusuario = "SELECT * FROM ct_clientes WHERE usuario = '$usuario'";

if (!$rusuario = $mysqli->query($qusuario)) {
    echo "<br>Lo sentimos, esta aplicación está experimentando problemas. Error al obtener los datos del cliente";
    exit;
}

$user = $rusuario->fetch_assoc();
$pk_cliente_sesion = $user["pk_cliente"];
#endregion



//SABER SI ES UNA VENTA DE ESE CLIENTE
#region
if ($nivel == 10) {

    if ($tipo == 2) {
        header('Location: index_cliente.php');
    }

    if ($fk_cliente != $pk_cliente_sesion) {
        header('Location: index_cliente.php');
    }
}
#endregion




//FORMAS DE PAGO
#region
$qformaspago = "SELECT * FROM ct_formas_pago_sat where estado=1";

if (!$rformaspago = $mysqli->query($qformaspago)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. Error al obtener las formas de pago SAT";
    exit;
}
#endregion



//MÉTODOS DE PAGO
#region
$qmetodospago = "SELECT * FROM ct_metodos_pago_sat where estado=1";

if (!$rmetodospago = $mysqli->query($qmetodospago)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. Error al obtener los métodos de pago SAT";
    exit;
}
#endregion



//CATALOGO CFDI
#region
$qcfdi = "SELECT * FROM ct_cfdi_sat where estado=1";

if (!$rcfdi = $mysqli->query($qcfdi)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. Error al obtener el catalogo de CFDI.";
    exit;
}
#endregion



//MÉTODO DE PAGO
#region
$qpago = "SELECT CONCAT(
        CASE WHEN tr_ventas.efectivo > 0 THEN 'Efectivo.' ELSE '' END,
        CASE WHEN tr_ventas.credito > 0 THEN 'Tarjeta Crédito. ' ELSE '' END,
        CASE WHEN tr_ventas.debito > 0 THEN 'Tarjeta de Debito. ' ELSE '' END,
        CASE WHEN tr_ventas.cheque > 0 THEN 'Cheque. ' ELSE '' END,
        CASE WHEN tr_ventas.transferencia > 0 THEN 'Transferencia. ' ELSE '' END,
        CASE WHEN tr_ventas.efectivo = 0 AND
                      tr_ventas.credito = 0 AND
                      tr_ventas.debito = 0 AND
                      tr_ventas.cheque = 0 AND
                      tr_ventas.transferencia = 0
                 THEN 'Venta a crédito' ELSE '' END
    ) AS campos_cumplen
  FROM tr_ventas
  WHERE pk_venta = $pk_venta";

if (!$rpago = $mysqli->query($qpago)) {
    echo "<br>Lo sentimos, esta aplicación está experimentando problemas. Error al obtener los metodos de pago utilizados.";
    exit;
}

$npago = $rpago->fetch_assoc();
$metodos_pago = $npago["campos_cumplen"];
#endregion



//VENDEDORES
#region
$fk_sucursal != 0 ? $flsucursal = " AND fk_sucursal = $fk_sucursal" : $flsucursal = "";
$nivel == 3 || $nivel == 4 ? $flusuario = " AND pk_usuario = '$usuario'" : $flusuario = "";

$qvendedores = "SELECT * FROM ct_usuarios where estado = 1$flsucursal $flusuario";

if (!$rvendedores = $mysqli->query($qvendedores)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. Error al obtener a los vendedores.";
    exit;
}
#endregion



//CATALOGO CFDI
#region
$qclientes = "SELECT * FROM ct_clientes where estado = 1";

if (!$rclientes = $mysqli->query($qclientes)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. Error al obtener los CFDI.";
    exit;
}
#endregion



//SUCURSALES
#region
$qsucursales = "SELECT * FROM ct_sucursales where estado=1";

if (!$rsucursales = $mysqli->query($qsucursales)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. Error al obtener las sucursales.";
    exit;
}
#endregion



//FORMAS DE PAGO
#region
$qctpagos = "SELECT * FROM ct_pagos where estado=1";

if (!$rctpagos = $mysqli->query($qctpagos)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. Error al obtener los métodos de pago.";
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
                            <div class="d-flex gap-2">
                                <?php if ($tipo == 1) : ?>
                                    <h4 class="card-title">Facturar venta</h4>
                                    <p class="badge-warning-integra"><i class='bx bx-file fs-5'></i><?php echo $folio ?></p>
                                    <p class="badge-primary-integra"><i class='bx bx-user fs-5'></i><?php echo $cliente_nombre ?></p>
                                <?php elseif ($tipo == 2) : ?>
                                    <h4 class="card-title">Facturar varios tickets</h4>
                                <?php endif; ?>
                            </div>
                            <div>
                                <i class='bx bx-note' style="font-size:32px"></i>
                            </div>
                        </div>

                        <form class="forms-sample" method="post" enctype="multipart/form-data" id="formuploadajax">

                            <?php
                            echo "<input type='hidden' id='pk_venta' class='form-control' value='$pk_venta' disabled>";
                            echo "<input type='hidden' id='usuario' class='form-control' value='$usuario' disabled>";
                            echo "<input type='hidden' id='tipo' class='form-control' value='$tipo' disabled>";
                            echo "<input type='hidden' id='nivel' class='form-control' value='$nivel' disabled>";
                            ?>

                            <br><br>

                            <h4 style="color: #2E7BB3; font-weight: bold;"><i class='bx bx-user fs-5'></i>Datos del cliente</h4>


                            <?php if ($tipo == 1) : ?>
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="cliente">Nombre</label>
                                            <?php
                                            echo "<input type='text' id='cliente' class='form-control' placeholder='Nombre del cliente' value='$cliente_nombre' disabled>";
                                            echo "<input type='hidden' id='fk_cliente' class='form-control' value='$fk_cliente' disabled>";
                                            ?>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="correo">Correo electrónico</label>
                                            <?php echo "<input type='text' id='correo' class='form-control' placeholder='Correo electrónico' value='$cliente_correo' disabled>"; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="cp">Código Postal</label>
                                            <?php echo "<input type='text' id='cp' class='form-control' placeholder='Código Postal' value='$cliente_cp' disabled>"; ?>
                                        </div>
                                    </div>

                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="rfc">RFC</label>
                                            <?php echo "<input type='text' id='rfc' class='form-control' placeholder='RFC' value='$cliente_rfc' disabled>"; ?>
                                        </div>
                                    </div>

                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="regimen">Regímen físcal</label>
                                            <?php echo "<input type='text' id='regimen' class='form-control' placeholder='Regímen físcal' value='$cliente_regimen' disabled>"; ?>
                                        </div>
                                    </div>
                                </div>

                            <?php elseif ($tipo == 2) : ?>
                                <div class="row">
                                    <div class="col-sm-12 col-lg-6">
                                        <div class="form-group">
                                            <label for="fk_cliente" class="d-flex align-items-center gap-2" style="margin-bottom: 16px;"> Seleccione el cliente</label>
                                            <select class="form-control select2" id="fk_cliente">
                                                <option value="0">Seleccione</option>
                                                <?php
                                                while ($clientev = $rclientes->fetch_assoc()) {
                                                    echo "<option value='$clientev[pk_cliente]'>$clientev[nombre]</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>


                            <div class="row d-none">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="archivo">Archivo</label>
                                        <input type="file" id="archivo" name="archivo" class="form-control dato">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="monto_pago">Monto de pago</label>
                                        <input type="number" id="monto_pago" name="monto_pago" class="form-control dato" disabled>
                                    </div>
                                </div>
                            </div>

                            <br>
                            <div class="line-default-integra"></div>
                            <br>


                            <h4 style="color: #16A34A; font-weight: bold;"><i class='bx bx-coin fs-5'></i>Pago</h4>

                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="forma_pago">Formas de pago SAT</label>
                                        <select id='forma_pago' class="form-control dato">
                                            <option value="0">Seleccione</option>
                                            <?php
                                            while ($formaspago = $rformaspago->fetch_assoc()) {
                                                echo "<option value='$formaspago[pk_forma]'>$formaspago[nombre]</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <?php
                                    if ($tipo == 1) {
                                        echo "<p class='badge-primary-integra'>Esta venta se realizó en $metodos_pago</p>";
                                    }
                                    ?>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="metodo_pago">Métodos de pago SAT</label>
                                        <select id='metodo_pago' class="form-control dato">
                                            <option value="0">Seleccione</option>
                                            <?php
                                            while ($metodospago = $rmetodospago->fetch_assoc()) {
                                                echo "<option value='$metodospago[pk_metodo]'>$metodospago[nombre]</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="fk_cfdi">CFDI</label>
                                        <select id='fk_cfdi' class="form-control dato">
                                            <option value="0">Seleccione</option>
                                            <?php
                                            while ($cfdi = $rcfdi->fetch_assoc()) {
                                                echo "<option value='$cfdi[pk_cfdi_sat]'>$cfdi[pk_cfdi_sat]. $cfdi[nombre]</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>


                            <br>
                            <div class="line-default-integra"></div>
                            <br>



                            <!--LISTADO DE VENTAS-->
                            <?php if ($tipo == 2) : ?>

                                <h4 style="color: #9032bb; font-weight: bold;"><i class='bx bx-file fs-5'></i>Seleccionar ventas</h4>

                                <div class="filter-box">
                                    <div class="row">
                                        <div class="col-sm-12 col-lg-6">
                                            <div class="form-group">
                                                <label for="text-input" class=" form-control-label"><i class='bx bx-calendar fs-5'></i>Fecha inicio</label>
                                                <input type="text" id="inicio" name="inicio" placeholder="Inicio" class="form-control datepicker" autocomplete="off">
                                            </div>
                                        </div>

                                        <div class="col-sm-12 col-lg-6">
                                            <div class="form-group">
                                                <label for="text-input" class=" form-control-label"><i class='bx bx-calendar fs-5'></i>Fecha fin</label>
                                                <input type="text" id="fin" name="fin" placeholder="Fin" class="form-control datepicker" autocomplete="off">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-sm-12 col-lg-6">
                                            <div class="form-group">
                                                <label for="fk_vendedor" class="d-flex align-items-center gap-2"> <i class="bx bx-user-circle fs-5"></i> Vendedor</label>
                                                <select class="form-control" id="fk_vendedor">
                                                    <option value="0">Seleccione</option>
                                                    <?php
                                                    while ($vendedor = $rvendedores->fetch_assoc()) {
                                                        echo "<option value='$vendedor[pk_usuario]'>$vendedor[nombre]</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <?php
                                        if ($fk_sucursal == 0) {
                                            echo "<div class='col-sm-12 col-lg-6'>
                                                <div class='form-group'>
                                                    <label for='fk_sucursalv' class='d-flex align-items-center gap-2'> <i class='bx bx-store-alt fs-5'></i> Sucursal</label>
                                                    <select class='form-control' id='fk_sucursalv'>
                                                        <option value='0'>Seleccione</option>";
                                            while ($sucursalv = $rsucursales->fetch_assoc()) {
                                                echo "<option value='$sucursalv[pk_sucursal]'>$sucursalv[nombre]</option>";
                                            }
                                            echo "</select>
                                                </div>
                                            </div>";
                                        } else {
                                            echo "<input type='hidden' id='fk_sucursalv' value='$fk_sucursal'>";
                                        }
                                        ?>
                                    </div>

                                    <div class="row">
                                        <div class="col-sm-12 col-lg-6">
                                            <div class="form-group">
                                                <label for="fk_pagov" class="d-flex align-items-center gap-2"> <i class="bx bx-coin fs-5"></i> Forma de pago</label>
                                                <select class="form-control" id="fk_pagov">
                                                    <option value="0">Seleccione</option>
                                                    <?php
                                                    while ($pagov = $rctpagos->fetch_assoc()) {
                                                        echo "<option value='$pagov[pk_pago]'>$pagov[nombre]</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-lg-6">
                                            <div class="form-group">
                                                <label for="fk_clientev" class="d-flex align-items-center gap-2"> <i class="bx bx-user fs-5" style="margin-bottom: 16px;"></i> Cliente</label>
                                                <select class="form-control select2" id="fk_clientev">
                                                    <option value="0">Seleccione</option>
                                                    <?php
                                                    $rclientes->data_seek(0);
                                                    while ($clientev = $rclientes->fetch_assoc()) {
                                                        echo "<option value='$clientev[pk_cliente]'>$clientev[nombre]</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-sm-12 col-lg-6"></div>
                                        <div class="col-sm-12 col-lg-6 d-flex justify-content-end">
                                            <button id="buscar" type="button" class="btn btn-info-dast mx-2"><i class="bx bx-search mx-2"></i>Consultar</button>
                                        </div>
                                    </div>

                                    <br>

                                    <div class="table-responsive overflow-auto scroll-style">
                                        <table id="dtEmpresa" class="table table-striped">
                                            <thead>
                                                <tr class="table-light">
                                                    <th colspan="8" class="text-center">VENTAS SIN FACTURAR</th>
                                                </tr>
                                                <tr>
                                                    <th></th>
                                                    <th>ID</th>
                                                    <th>Fecha</th>
                                                    <th>Sucursal</th>
                                                    <th>Cliente</th>
                                                    <th>Forma de pago</th>
                                                    <th>Vendedor</th>
                                                    <th>Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div>

                                </div>

                                <br>
                                <div class="line-default-integra"></div>
                                <br>
                            <?php endif; ?>



                            <!--TABLA-->
                            <div class="table-responsive overflow-auto scroll-style filter-box">
                                <table id="entradas" class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>Producto</th>
                                            <th>Cantidad</th>
                                            <th>Costo</th>
                                            <th>IVA(16%)</th>
                                            <th>Total</th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if ($tipo == 1) {
                                            while ($rowd = $rdetalle->fetch_assoc()) {

                                                //IMAGEN
                                                #region
                                                $imagen = $rowd["imagen"];

                                                $file = "servicios/productos/$imagen";

                                                if (is_file($file)) {
                                                    $fondo = "<img style='border-radius: 7px; width:70px; height:70px; object-fit:cover;' loading='lazy' src='servicios/productos/$imagen'>";
                                                } else {
                                                    $fondo = "<img style='border-radius: 7px; width:70px; height:70px; object-fit:cover;' loading='lazy' src='images/picture.png'>";
                                                }
                                                #endregion

                                                $total = $rowd['cantidad_total'] * $rowd['unitario'];
                                                $costo = number_format(($rowd['cantidad_total'] * $rowd['unitario']) / 1.16, 2, '.', '');
                                                $iva = number_format($total - $costo, 2, '.', '');

                                                echo "
                                                    <tr class='odd gradeX' data-id='$rowd[fk_producto]' data-venta='$rowd[fk_venta]'>
                                                        <td>$fondo</td>
                                                        <td style='white-space: normal;'>$rowd[producto]</td>
                                                        <td>$rowd[cantidad_total]</td>
                                                        <td>$$costo</td>
                                                        <td>$$iva</td>
                                                        <td>$$total</td>
                                                    </tr>
                                                ";
                                            }
                                        }
                                        ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td style='font-weight: bold;'>TOTAL:</td>
                                            <td style='font-weight: bold;' id="totalf">$<?php echo $total_venta; ?></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <br><br>

                            <button id="guardar" type="button" class="btn btn-primary-dast mx-2"><i class="bx bx-send mx-2"></i>Facturar</button>

                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>


    <div class="modal" id="exito" tabindex="-1" role="dialog" aria-hidden="true" data-bs-keyboard="false" data-bs-backdrop="static">
        <div class="modal-dialog modal-md">

            <div class="modal-content">
                <div class="modal-header text-center">
                    <h2 class="text-center exitot">Orden de compra <span id="nentrada"></span> </h2>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <a target="_blank" id="pdf" class="btn btn-danger pdfb"><i class="fa fa-file-pdf-o fa-5x" aria-hidden="true"></i><br><br>DESCARGAR COMPROBANTE</a>
                        </div>
                    </div>
                </div>
                <div class="modal-footer text-center">
                    <div class="col-sm-12">
                        <button id="nueva" type="button" class="btn btn-sm btn-info">Generar Nueva Orden</button>
                        <button id="inicio" type="button" class="btn btn-sm btn-success">Inicio</button>
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
    <script src="custom/jquery.numeric.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="custom/config.js"></script>
    <script src="custom/facturarVenta.js"></script>

</body>

</html>
