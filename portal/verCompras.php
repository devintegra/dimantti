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
    $pk_sucursal = $_SESSION["pk_sucursal"];
    $tipo = "Chofer";
    $menu = "fragments/menub.php";
}


if ($nivel != 1) {
    header('Location: ../index.php');
}

//FILTROS
#region
$flsucursal = "";
if ($nivel != 1) {
    $flsucursal = " AND tr_compras.fk_sucursal = $pk_sucursal";
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
                            <h4 class="card-title">Compras</h4>
                            <a href="agregarCompra.php"><button type="button" class="btn btn-social-icon-text btn-add"><i class='bx bx-plus'></i>Nueva compra</button></a>
                        </div>

                        <div class="table-responsive overflow-hidden">
                            <table id='dtEmpresa' class='table table-striped'>
                                <thead>
                                    <tr>
                                        <?php echo ($nivel == 1) ? "<th>Aprobación</th>" : "" ?>
                                        <th>#Compra</th>
                                        <th>Estatus</th>
                                        <th>Productos</th>
                                        <th>Proveedor</th>
                                        <th>Fecha</th>
                                        <th>Saldo</th>
                                        <th>Factura</th>
                                        <th>Observaciones</th>
                                        <th>Acciones</th>

                                    </tr>
                                </thead>
                                <tfoot style='display: table-header-group'>
                                    <tr>
                                        <?php echo ($nivel == 1) ? "<th></th>" : "" ?>
                                        <th>#Compra</th>
                                        <th>Estatus</th>
                                        <th>Productos</th>
                                        <th>Proveedor</th>
                                        <th>Fecha</th>
                                        <th>Saldo</th>
                                        <th>Factura</th>
                                        <th>Observaciones</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                                <tbody>
                                    <?php

                                    $eusuario = "SELECT ct_proveedores.nombre as proveedor,
                                        tr_compras.pk_compra as pk_compra,
                                        tr_compras.archivo as archivo,
                                        tr_compras.factura,
                                        tr_compras.saldo,
                                        tr_compras.fecha as fecha,
                                        tr_compras.hora,
                                        tr_compras.aprobado,
                                        tr_compras.estatus as estatus,
                                        tr_compras.observaciones as observaciones_compras,
                                        IFNULL(GROUP_CONCAT(tr_entradas.observaciones SEPARATOR ' | '), '') as observaciones
                                    FROM tr_compras
                                    INNER JOIN ct_proveedores ON tr_compras.fk_proveedor = ct_proveedores.pk_proveedor
                                    LEFT JOIN tr_entradas ON tr_entradas.fk_compra = tr_compras.pk_compra
                                    WHERE tr_compras.estado = 1$flsucursal
                                    GROUP BY tr_compras.pk_compra
                                    ORDER BY tr_compras.fecha DESC;";

                                    if (!$resultado = $mysqli->query($eusuario)) {
                                        echo "Lo sentimos, esta aplicación está experimentando problemas.";
                                        exit;
                                    }

                                    while ($row = $resultado->fetch_assoc()) {

                                        //VER ARCHIVOS
                                        #region
                                        $botones = "<a target='_blank' href='compraPDF.php?id=$row[pk_compra]' class='btn-reabrir-dast' title='Ticket PDF'><i class='fa fa-file-pdf-o'></i></a>";

                                        if ($nivel == 1) {
                                            $botones = $botones . "<button type='button' id='$row[pk_compra]' class='btn-iniciar-dast ver' title='Ver detalles'>
                                                <i class='fa fa-eye'></i>
                                            </button>";
                                        }

                                        if ($row["archivo"] != null &&  $row["archivo"] != "" &&  $row["archivo"] != "null") {
                                            $botones = $botones . "<a href='servicios/compras/$row[archivo]' target='_blank' class='btn-iniciar-dast' title='Archivo'><i class='fa fa fa-file'></i></a>";
                                        }
                                        #endregion


                                        //ESTATUS DE COMPRA
                                        #region
                                        $nestatus = "";
                                        switch ((int)$row['estatus']) {
                                            case 1:
                                                $nestatus = "<p class='badge-danger-integra'>Sin surtir</p>";
                                                if ($row['aprobado'] == 1) {
                                                    $botones = $botones . "<button type='button' id='$row[pk_compra]' class='btn-entregar-dast surtir' title='Surtir compra'>
                                                        <i class='fa fa-plus'></i>
                                                    </button>";
                                                }
                                                break;
                                            case 2:
                                                $nestatus = "<p class='badge-warning-integra'>Surtido en proceso</p>";
                                                if ($row['aprobado'] == 1) {
                                                    $botones = $botones . "<button type='button' id='$row[pk_compra]' class='btn-entregar-dast surtir' title='Surtir compra'>
                                                        <i class='fa fa-plus'></i>
                                                    </button>";
                                                }
                                                break;
                                            case 3:
                                                $nestatus = "<p class='badge-success-integra'>Completo</p>";
                                                break;
                                        }
                                        #endregion


                                        //SALDO
                                        #region
                                        $saldo = number_format($row['saldo'], 2);
                                        $sestilo = "badge-danger-integra";
                                        if ($saldo > 0) {
                                            $botones = $botones . "<a href='pagarCompra.php?id=$row[pk_compra]'><button type='button' class='btn-editar-dast p-1' title='Saldar cuenta'>
                                                    <i class='bx bx-coin-stack'></i>
                                                </button></a>";
                                        } else {
                                            $sestilo = "badge-success-integra";
                                            $botones = $botones . "<a target='_blank' href='abonosPDF.php?id=$row[pk_compra]' class='btn-reasignar-dast' title='Historial de abonos'><i class='fa fa-file-pdf-o'></i></a>";
                                        }
                                        #endregion


                                        //APROBACION
                                        #region
                                        $aprobacion_chk = "";
                                        if ($nivel == 1) {
                                            $aprobacion_chk = "<td></td>";
                                            if ($row["estatus"] == 1) {
                                                if ($row['aprobado'] == 1) {
                                                    $aprobacion_chk = "<td><input type='checkbox' class='aprobar-chk' name='aprobar-chk' checked value='$row[pk_compra]' style='width: 20px; height: 20px;'></td>";
                                                } else {
                                                    $aprobacion_chk = "<td><input type='checkbox' class='aprobar-chk' name='aprobar-chk' value='$row[pk_compra]' style='width: 20px; height: 20px;'></td>";
                                                }
                                            }
                                        }
                                        #endregion


                                        //PRODUCTOS
                                        #region
                                        $qdetalle = "SELECT
                                            c.pk_compra,
                                            p.codigobarras,
                                            cd.fk_producto_nombre as producto,
                                            cd.cantidad
                                        FROM tr_compras c
                                        JOIN tr_compras_detalle cd ON c.pk_compra = cd.fk_compra
                                        JOIN ct_productos p ON cd.fk_producto = p.pk_producto
                                        LEFT JOIN tr_entradas e ON c.pk_compra = e.fk_compra
                                        LEFT JOIN tr_entradas_detalle ed ON e.pk_entrada = ed.fk_entrada AND cd.fk_producto = ed.fk_producto
                                        WHERE c.pk_compra = $row[pk_compra]
                                        GROUP BY cd.fk_producto;";

                                        if (!$rdetalle = $mysqli->query($qdetalle)) {
                                            echo "Lo sentimos, esta aplicación está experimentando problemas.";
                                            exit;
                                        }

                                        $productos_detalle = "";
                                        while ($detalle = $rdetalle->fetch_assoc()) {
                                            $productos_detalle .= "<li>" . $detalle['codigobarras'] . " | " . $detalle['producto'] . "</li>";
                                        }
                                        #endregion


                                        echo <<<HTML
                                            <tr class="odd gradeX">
                                                $aprobacion_chk
                                                <td>$row[pk_compra]</td>
                                                <td>$nestatus</td>
                                                <td><ul>$productos_detalle</ul></td>
                                                <td style='white-space: normal;'>$row[proveedor]</td>
                                                <td>
                                                    <i class='bx bx-calendar fs-5'></i>
                                                    $row[fecha] $row[hora]
                                                </td>
                                                <td>
                                                    <p class='$sestilo'>$$saldo</p>
                                                </td>
                                                <td>
                                                    $row[factura]
                                                </td>
                                                <td style='white-space: normal;'>$row[observaciones_compras]</td>
                                                <td style='white-space: normal'>
                                                    <div class='d-flex'>
                                                        $botones
                                                    </div>
                                                </td>
                                            </tr>
                                        HTML;
                                    }
                                    ?>
                                </tbody>
                            </table>
                            <div class='row'>&nbsp;</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <div class="modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static" id="modalInfo">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header justify-content-end">
                    <button class="btn btn-danger" id="cerrarinfo">Cerrar</button>
                </div>

                <div class="modal-body" id="tablaInfo">
                    <table id="dtInfo" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Cantidad</th>
                                <th>Producto</th>
                                <th>Unitario</th>
                                <th>Total</th>
                                <th>Estatus</th>
                                <th>Faltantes</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>

                <div class="modal-footer">
                    <div class="row d-flex justify-content-end">
                        <button id="actualizarPrecios" type="button" class="btn btn-primary-dast mx-2"><i class="fa fa-save mx-2"></i>Guardar precios</button>
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
    <script src="assets/lib/data-table/jszip.min.js"></script>
    <script src="assets/lib/data-table/pdfmake.min.js"></script>
    <script src="assets/lib/data-table/vfs_fonts.js"></script>
    <script src="assets/lib/data-table/buttons.html5.min.js"></script>
    <script src="assets/lib/data-table/buttons.print.min.js"></script>
    <script src="assets/lib/data-table/buttons.colVis.min.js"></script>
    <script src="assets/lib/data-table/datatables-init.js"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="custom/numberFormats.js"></script>
    <script src="custom/verCompras.js"></script>

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
            "order": [
                [1, "desc"]
            ],
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
