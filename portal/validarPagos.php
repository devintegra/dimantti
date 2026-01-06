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
    $pk_sucursal = $_SESSION["pk_sucursal"];
    $tipo = "Chofer";
    $menu = "fragments/menub.php";
}


if ($nivel != 1) {
    header('Location: ../index.php');
}

mysqli_set_charset($mysqli, 'utf8');



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


    <div class="main-panel">

        <div class="row justify-content-center">
            <div class="col-lg-10 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title">Validador de pagos</h4>
                            <i class="bx bx-money-withdraw" style="font-size: 32px;"></i>
                        </div>

                        <div class="table-responsive overflow-hidden" id="tabla">
                            <table id='dtEmpresa' class='table table-striped'>
                                <thead>
                                    <th>Tipo</th>
                                    <th>Monto</th>
                                    <th>Referencia</th>
                                    <th>Fecha</th>
                                    <th>#Venta</th>
                                    <th>Usuario</th>
                                    <th>Sucursal</th>
                                    <th>Cliente</th>
                                    <th>Forma de pago</th>
                                    <th>Estatus</th>
                                    <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tfoot style='display: table-header-group'>
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Monto</th>
                                        <th>Referencia</th>
                                        <th>Fecha</th>
                                        <th>#Venta</th>
                                        <th>Usuario</th>
                                        <th>Sucursal</th>
                                        <th>Cliente</th>
                                        <th>Forma de pago</th>
                                        <th>Estatus</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                                <tbody>
                                    <?php

                                    echo "<input type='hidden' id='fk_usuario' class='form-control' value='$usuario'>";

                                    $qabonos = "SELECT tr_abonos.*,
                                        tr_ventas.folio,
                                        ct_clientes.nombre as cliente,
                                        ct_sucursales.nombre as sucursal,
                                        tr_ventas.fk_usuario,
                                        tr_ventas.cheque_referencia,
                                        tr_ventas.transferencia_referencia,
                                        ct_pagos.nombre as pago
                                    FROM tr_abonos, tr_ventas, ct_clientes, ct_sucursales, ct_pagos
                                    WHERE tr_abonos.fk_pago IN (2,3,4,5)
                                    AND tr_abonos.monto > 0
                                    AND tr_ventas.pk_venta = tr_abonos.fk_factura
                                    AND ct_clientes.pk_cliente = tr_ventas.fk_cliente
                                    AND ct_sucursales.pk_sucursal = tr_ventas.fk_sucursal
                                    AND ct_pagos.pk_pago = tr_abonos.fk_pago
                                    AND tr_abonos.estado = 1";

                                    if (!$rabonos = $mysqli->query($qabonos)) {
                                        echo "Lo sentimos, esta aplicación está experimentando problemas.";
                                        exit;
                                    }


                                    while ($row = $rabonos->fetch_assoc()) {

                                        //TIPOS
                                        #region
                                        $tipo = "Venta";
                                        $estiloTipo = "badge-primary-integra";
                                        if ($row['origen'] == 2) {
                                            $tipo = "Orden de servicio";
                                            $estiloTipo = "badge-success-integra";
                                        }
                                        #endregion


                                        //ESTATUS
                                        #region
                                        $estatus = "No validado";
                                        $estiloEstatus = "badge-danger-integra";
                                        $btn_validar = "<button data-id='$row[pk_abono]' class='validar' title='Validar' style='border:none; background:transparent;'><i class='fa fa-check vpdf fa-lg btn-excel'></i></button>";
                                        if ($row['aprobado'] == 1) {
                                            $estatus = "Validado";
                                            $estiloEstatus = "badge-success-integra";
                                            $btn_validar = "";
                                        }
                                        #endregion


                                        //ARCHIVO
                                        #region
                                        if ($row['archivo']) {
                                            $comprobante = "<a target='_blank' href='servicios/abonos/$row[archivo]' data-title='Ver comprobante'><i class='fa fa-file-o vpdf fa-lg btn-pdf'></i></a>";
                                        } else {
                                            $comprobante = "<button data-id='$row[pk_abono]' class='comprobante' title='Subir comprobante' style='border:none; background:transparent;'><i class='fa fa-upload vpdf fa-lg btn-pdf'></i></button>";
                                        }
                                        #endregion

                                        $monto = number_format($row['monto'], 2);


                                        echo <<<HTML
                                            <tr id='$row[pk_abono]'>
                                                <td><p class='$estiloTipo'>$tipo</p></td>

                                                <td><p class='badge-warning-integra'>$$monto</p></td>

                                                <td>$row[transferencia_referencia] $row[cheque_referencia]</td>

                                                <td><i class='bx bx-calendar fs-5'></i>$row[fecha] $row[hora]</td>

                                                <td>$row[fk_factura]</td>

                                                <td>$row[fk_usuario]</td>

                                                <td style='white-space:normal;'>$row[sucursal]</td>

                                                <td style='white-space:normal;'><i class='bx bx-user-circle fs-5'></i>$row[cliente]</td>

                                                <td>$row[pago]</td>

                                                <td><p class='$estiloEstatus'>$estatus</p></td>

                                                <td>
                                                    $btn_validar
                                                    <button data-id='$row[fk_factura]' class='venta' title='Ver venta' style='border:none; background:transparent;'><i class='fa fa-eye vpdf fa-lg btn-img'></i></button>
                                                    $comprobante
                                                </td>
                                            </tr>
                                        HTML;
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>


    <div class="modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static" id="modalVenta">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header justify-content-between" style="background-color: #2563EB; color:#fff;">
                    <h2 class="text-center exitot">Detalle Venta</h2>
                    <button class="btn btn-danger" id="cerrarVenta">Cerrar</button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="table-responsive overflow-hidden" id="tablaVenta">
                            <table class='table table-striped'>
                                <thead>
                                    <tr>
                                        <th>Cantidad</th>
                                        <th>Clave</th>
                                        <th>Producto</th>
                                        <th>Costo</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot>
                                    <tr style='background-color: #d0fdd7; font-weight: bold'>
                                        <td>Total de productos: <span id="cantidadProductos">0.00</span></td>
                                        <td colspan="2"></td>
                                        <td>SUBTOTAL: </td>
                                        <td>$<span id="subtotal">0.00</span></td>
                                    </tr>
                                    <tr style='background-color: #d0fdd7; font-weight: bold'>
                                        <td colspan="3"></td>
                                        <td>DESCUENTO: </td>
                                        <td>$<span id="descuento">0.00</span></td>
                                    </tr>
                                    <tr style='background-color: #BBF7B0; font-weight: bold'>
                                        <td colspan="3"></td>
                                        <td>TOTAL: </td>
                                        <td>$<span id="total">0.00</span></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>


    <div class="modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static" id="modalComprobante">
        <div class="modal-dialog modal-md">
            <div class="modal-content">

                <div class="modal-header justify-content-between" style="background-color: #FFD94D; color:#000;">
                    <h2 class="text-center">Agregar comprobante</h2>
                    <button class="btn btn-danger" onclick="$('#modalComprobante').modal('hide')">Cerrar</button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <form class="forms-sample" enctype="multipart/form-data" id="formuploadajax">

                            <div class="row">
                                <div class="col-12">
                                    <label for="archivo">Seleccione un archivo</label>
                                    <input type="file" class="form-control" id="archivo" name="archivo">
                                    <input type="hidden" id="pk_abono_comprobante" class="form-control">
                                </div>
                            </div>
                            <br>
                            <button id="subirComprobante" type="button" class="btn btn-primary-dast mx-2"><i class="fa fa-save mx-2"></i>Subir comprobante</button>

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
    <script src="assets/lib/data-table/buttons.bootstrap.min.js"></script>
    <script src="assets/lib/data-table/datatables-init.js"></script>
    <script src="assets/loading/loadingoverlay.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="custom/validarPagos.js"></script>

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
            order: [
                [3, 'desc']
            ],
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

</body>

</html>
