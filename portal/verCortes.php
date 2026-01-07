<?php
header('Cache-control: private');
date_default_timezone_set('America/Mexico_City');
include("servicios/conexioni.php");
@session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
}

$usuario = $_SESSION["usuario"];
$nivel = $_SESSION["nivel"];
$fk_sucursal = 0;


if ($nivel == 1) {
    $tipo = "Administrador";
    $menu = "fragments/menua.php";
}

if ($nivel == 2) {
    $fk_sucursal = $_SESSION["pk_sucursal"];
    $tipo = "Chofer";
    $menu = "fragments/menub.php";
}


if ($nivel != 1) {
    header('Location: ../index.php');
}

mysqli_set_charset($mysqli, 'utf8');



$filtro = "";

if ($fk_sucursal != 0) {
    $filtro = " AND tr_ventas.fk_sucursal=$fk_sucursal";
}


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
                            <h4 class="card-title">Cortes de caja</h4>
                            <?php
                            if ($nivel == 1 || $nivel == 4) {
                                echo "<a href='agregarCorte.php'><button type='button' class='btn btn-social-icon-text btn-add'><i class='bx bx-plus'></i>Nuevo corte de caja</button></a>";
                            }
                            ?>
                        </div>

                        <div class="table-responsive overflow-hidden" id="tabla">
                            <table id='dtEmpresa' class='table table-striped'>
                                <thead>
                                    <tr>
                                        <th>#Corte</th>
                                        <th>Fecha / Hora</th>
                                        <th>Tipo</th>
                                        <th>Sucursal</th>
                                        <th>Usuario</th>
                                        <th>Total</th>
                                        <th>Ver</th>
                                        <th>Estatus</th>
                                    </tr>
                                </thead>
                                <tfoot style='display: table-header-group'>
                                    <tr>
                                        <th>#Corte</th>
                                        <th>Fecha / Hora</th>
                                        <th>Tipo</th>
                                        <th>Sucursal</th>
                                        <th>Usuario</th>
                                        <th>Total</th>
                                        <th></th>
                                        <th>Estatus</th>
                                    </tr>
                                </tfoot>
                                <tbody>
                                    <?php

                                    $nivel == 1 ? $flusuario = "" : $flusuario = " AND tr_cortes.fk_usuario = '$usuario'";

                                    $qcortes = "SELECT * FROM tr_cortes WHERE estatus != 0 AND estado = 1$flusuario";

                                    if (!$rcortes = $mysqli->query($qcortes)) {
                                        echo "Lo sentimos, esta aplicación está experimentando problemas.";
                                        exit;
                                    }

                                    $total_peso = 0.00;

                                    while ($row = $rcortes->fetch_assoc()) {

                                        //SUCURSAL
                                        #region
                                        if ($row['fk_sucursal'] == 0) {
                                            $sucursal = "Todas";
                                        } else {
                                            $qsucursal = "SELECT * FROM ct_sucursales where pk_sucursal = $row[fk_sucursal]";

                                            if (!$rsucursal = $mysqli->query($qsucursal)) {
                                                echo "<br>Lo sentimos, esta aplicación está experimentando problemas.";
                                                exit;
                                            }

                                            $sucursales = $rsucursal->fetch_assoc();
                                            $sucursal = $sucursales["nombre"];
                                        }
                                        #endregion


                                        //Estatus
                                        #region
                                        $botons = "";
                                        if ($row['estatus'] == 1) {
                                            $estatus = "Pendiente de aprobación";
                                            $color = "badge-warning-integra";
                                            if ($nivel == 1) {
                                                $botons = "<button type='button' class='btn-reabrir-dast' onclick='aprobacion($row[pk_corte], 0)' title='No aprobar'><i class='fa fa-close'></i></button>
                                            <button type='button' class='btn-entregar-dast' onclick='aprobacion($row[pk_corte], 2)' title='Aprobar'><i class='fa fa-check'></i></button>";
                                            }
                                        } else if ($row['estatus'] == 2) {
                                            $estatus = "Aprobado";
                                            $color = "badge-success-integra";
                                        } else {
                                            $estatus = "No aprobado";
                                            $color = "badge-danger-integra";
                                        }
                                        #endregion


                                        //ORIGEN
                                        #region
                                        if ($row['origen'] == 1) {
                                            $tipo = "<p class='badge-primary-integra'>Venta</p>";
                                        } else {
                                            $tipo = "<p class='badge-warning-integra'>Reparación</p>";
                                        }
                                        #endregion


                                        //TOTAL
                                        #region
                                        $totald = number_format($row["total"], 2);

                                        if ($totald > 0) {
                                            $estilo = "badge-success-integra";
                                        } else {
                                            $estilo = "badge-danger-integra";
                                        }
                                        #endregion

                                        echo <<<HTML
                                            <tr class='odd gradeX'>
                                                <td>$row[pk_corte]</td>
                                                <td><i class='bx bx-calendar fs-5'></i>$row[fecha] / $row[hora]</td>
                                                <td style='white-space: normal'>$tipo</td>
                                                <td style='white-space: normal'>$sucursal</td>
                                                <td style='white-space: normal'>$row[fk_usuario]</td>
                                                <td>
                                                    <p class='$estilo'>$$totald</p>
                                                </td>
                                                <td><a target='_blank' href='cortePDF.php?id=$row[pk_corte]'><i class='fa fa-file-pdf-o vpdf fa-lg btn-pdf'></i></a></td>
                                                <td>
                                                    <p class='$color'>$estatus</p>
                                                    $botons
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

    <script src="assets/vendor/jquery-2.1.4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js"></script>
    <script src="assets/plugins.js"></script>
    <script src="assets/main.js"></script>
    <script src="assets/lib/data-table/datatables.min.js"></script>
    <script src="assets/lib/data-table/dataTables.bootstrap.min.js"></script>
    <script src="assets/lib/data-table/dataTables.buttons.min.js"></script>
    <script src="assets/lib/data-table/buttons.bootstrap.min.js"></script>
    <script src="assets/lib/data-table/pdfmake.min.js"></script>
    <script src="assets/lib/data-table/vfs_fonts.js"></script>
    <script src="assets/lib/data-table/buttons.html5.min.js"></script>
    <script src="assets/lib/data-table/buttons.print.min.js"></script>
    <script src="assets/lib/data-table/buttons.colVis.min.js"></script>
    <script src="assets/lib/data-table/datatables-init.js"></script>

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
            order: [
                [0, 'desc']
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
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

    <script>
        function aprobacion(fk_corte, tipo) {

            var parametros = {
                "fk_corte": fk_corte,
                "tipo": tipo
            }

            $.ajax({
                url: 'servicios/editarCorteAprobacion.php',

                type: 'post',

                data: parametros,

                beforeSend: function() {

                },

                success: function(response) {

                    if (response.codigo == 200) {

                        swal("Exito", "El registro se actualizó correctamente", "success").then(function() {
                            $(location).attr('href', "verCortes.php");
                        });

                    } else {

                        swal("Error", response.descripcion, "error").then(function() {
                            location.reload();
                        });

                    }

                },

                error: function(arg1, arg2, arg3) {
                    console.log(arg3);
                }

            });

        }
    </script>

</body>

</html>
