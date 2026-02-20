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
$pk_sucursal = $_SESSION["pk_sucursal"];

if ($nivel == 1) {
    $tipo = "Administrador";
    $menu = "fragments/menua.php";
    $panel = "fragments/panela.php";
}

if ($nivel == 2) {
    $tipo = "Vendedor";
    $menu = "fragments/menub.php";
}

if ($nivel == 3) {
    $tipo = "Tecnico";
    $menu = "fragments/menuc.php";
}

if ($nivel != 1 && $nivel != 3) {
    header('Location: ../index.php');
}


if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $fk_orden = (int)$_GET['id'];
}


//DATOS DE LA ORDEN
#region
if (!$rorden = $mysqli->query("CALL sp_get_orden_by_sucursal($nivel, $fk_orden, $pk_sucursal)")) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.1";
    exit;
}
$orden = $rorden->fetch_assoc();

$estatus = $orden["estatus"];
$folio = $orden["folio"];
$espera = $orden["espera"];
$fk_cliente = $orden["fk_cliente"];
$fk_venta = $orden["fk_venta"];
$cliente_nombre = $orden["cliente"];
$cliente_telefono = $orden["telefono"];
$cliente_correo = $orden["correo"];
#endregion



//DETALLE
#region
$mysqli->next_result();
if (!$rordend = $mysqli->query("CALL sp_get_orden_detalle($fk_orden)")) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. 3";
    exit;
}

$ordend = $rordend->fetch_assoc();
$equipo = $ordend["nombre"];
$estimado = $ordend["estimado"];
#endregion



//ESTATUS
#region
if ($estatus == 2) {
    $nestatus = "Asignada";
}

if ($estatus == 3) {

    if ($espera == 0) {
        $nestatus = $reabierta . "En curso";
    }

    if ($espera == 1) {
        $nestatus = "En espera";
    }
}

if ($estatus == 4) {
    $nestatus = $reabierta . "Terminada";
}

if ($estatus == 5) {
    $nestatus = $reabierta . "Entregada";
}
#endregion



//PDF NOTAS
#region
$mysqli->next_result();
if (!$rpdf = $mysqli->query("SELECT MAX(pdf) as pdf from rt_ordenes_registros where fk_orden=$fk_orden and entrega=0")) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.4";
    exit;
}

$pdf = $rpdf->fetch_assoc();
$pdf_valor = $pdf["pdf"];
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
                            <h4 class="card-title">Datos orden</h4>
                            <i class='bx bx-note' style="font-size:32px"></i>
                            <?php
                            echo <<<HTML
                                <input type='hidden' id='pk_orden' value='$fk_orden'/>
                                <input type='hidden' id='nivel' value='$nivel'/>
                                <input type='hidden' id='fk_usuario' value='$usuario'/>
                                HTML
                            ?>
                        </div>
                        <form class="forms-sample">

                            <div class="filter-box">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="folio">Folio</label>
                                            <?php
                                            echo "<input type='text' id='folio' name='folio' placeholder='Folio' class='form-control' value='$folio' disabled>
                                        <input type='hidden' id='fk_orden' class='form-control' value='$fk_orden' disabled>";
                                            ?>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="nombre">Nombre</label>
                                            <?php
                                            echo "<input type='text' id='nombre' name='nombre' placeholder='Nombre' class='form-control' value='$cliente_nombre' disabled>";
                                            ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="telefono">Teléfono</label>
                                            <?php
                                            echo "<input type='text' id='telefono' name='telefono' placeholder='Telefono' class='form-control' value='$cliente_telefono' disabled>";
                                            ?>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="correo">Correo</label>
                                            <?php
                                            echo "<input type='text' id='correo' name='correo' placeholder='Correo' class='form-control' value='$cliente_correo' disabled>";
                                            ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="estatus">Estatus</label>
                                            <?php
                                            echo "<input type='text' id='estatus' name='estatus' placeholder='estatus' class='form-control' value='$nestatus' disabled>
                                        <input type='hidden' id='pdf' name='pdf' class='form-control' value='$pdf_valor' disabled>";
                                            ?>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group d-flex">
                                            <div class="col-lg-6">
                                                <label for="estimado">$ Valor estimado</label>
                                                <?php
                                                $cliente_telefono = preg_replace('/\s+/', '', $cliente_telefono);
                                                echo <<<HTML
                                                <input type='text' id='estimado' name='estimado' placeholder='$0.00' class='form-control' value='$estimado' disabled>
                                            HTML;
                                                ?>
                                            </div>

                                            <div class="col-lg-6 d-flex justify-content-center align-items-center">
                                                <label for="text-input" class=" form-control-label"><?php echo "<a target='_blank' href='ordenPDF.php?id=$fk_orden&ph=$cliente_telefono'><i class='fa fa-file-pdf-o vpdf fa-lg btn-pdf' style='padding:15px'></i></a>"; ?></label>
                                                <?php
                                                $mysqli->next_result();
                                                if (!$rservicios = $mysqli->query("SELECT * FROM rt_ordenes_registros WHERE fk_orden = $fk_orden AND pdf > 1 GROUP BY pdf")) {
                                                    echo "Lo sentimos, esta aplicación está experimentando problemas.5";
                                                    exit;
                                                }

                                                while ($rowservicio = $rservicios->fetch_assoc()) {
                                                    echo <<<HTML
                                                    <label for='text-input' class='form-control-label mx-2'>
                                                        <a target='_blank' href='ordenServicioPDF.php?id=$fk_orden&ph=$cliente_telefono&servicio=$rowservicio[pdf]'>
                                                            <i class='fa fa-file-pdf-o vpdf fa-lg btn-pdf' style='padding:15px'></i>
                                                        </a>
                                                    </label>
                                                HTML;
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <hr>


                            <!--TABLA DE ORDENES-->
                            <h4 class="card-title">Registros de la orden</h4>

                            <div class="table-responsive overflow-auto scroll-style" id="tabla">
                                <table id='dtEmpresa' class='table table-striped'>
                                    <thead>
                                        <tr>
                                            <th>Publico</th>
                                            <th>Realizo</th>
                                            <th>Fecha</th>
                                            <th>Comentarios</th>
                                            <th>Precio</th>
                                            <th>Costo</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php

                                        $mysqli->next_result();
                                        if (!$resultado = $mysqli->query("CALL sp_get_orden_registros($fk_orden)")) {
                                            echo "Lo sentimos, esta aplicación está experimentando problemas.6";
                                            exit;
                                        }

                                        while ($row = $resultado->fetch_assoc()) {

                                            $cliente_telefono = preg_replace('/\s+/', '', $cliente_telefono);
                                            $estilo = "";


                                            //ARCHIVO
                                            #region
                                            $archivo = "";
                                            if ($row["archivo"] != null && $row["archivo"] != "") {
                                                $archivo = "<a target='_blank' href='https://dimantti.integracontrol.online/portal/servicios/pruebas/$row[archivo]'><i class='bx bx-file fs-5 btn-img'></i></a>";
                                            }
                                            #endregion


                                            //PUBLICO
                                            #region
                                            $publico = "no";

                                            if ($row["publico"] == 1) {
                                                $publico = "si";
                                                $estilo = " style='background-color:#ffdddd'";
                                            }

                                            if ($row["publico"] == 1 && $row["tipo"] != 3 && $row["tipo"] != 4 && $row["tipo"] != 5 && $row["tipo"] != 6 && $row["tipo"] != 7) {
                                                $publico = "si";
                                                $estilo = " style='background-color:#ffdddd'";
                                                $archivo = $archivo . "<a target='_blank' href='https://wa.me/+52$cliente_telefono?text=$row[comentarios]'><i class='bx bxl-whatsapp btn-whatsapp'></i></a>";
                                            }

                                            $botonp = "<button type='button' id='$row[pk_ordenes_registros]' class='btn-entregar-dast cambiar' title='Cambiar'><i class='bx bx-history fs-4'></i></button>";
                                            #endregion


                                            //ACCIONES, WHATSAPP
                                            #region
                                            /*TIPOS de acuerdo a tabla de mensajes para whatsapp
                                                1-Registro de orden
                                                4-Equipo listo para entrega
                                                5-Se cierra orden y se cobra
                                                6-Encuesta de satisfacción
                                                7-Calificación en Google Maps
                                            */

                                            $ordenPDF = "https://dimantti.integracontrol.online/portal/ordenPDF.php?id=$fk_orden%26ph=$cliente_telefono";
                                            $seguimiento = "https://dimantti.integracontrol.online/seguimiento.php?id=$fk_orden%26ph=$cliente_telefono";
                                            $ventaPDFlink = "https://dimantti.integracontrol.online/portal/ventaPDF.php?id=$fk_venta%26ph=$cliente_telefono";
                                            $paseo_moral = "https://g.page/r/CU93hVxFyt2DEBM/review";
                                            $leon_moderno = "https://g.page/r/CXaCGg6rd0x3EBM/review";
                                            $encuesta = "https://forms.gle/316qsex45s3x3ziM7";

                                            if ($row["tipo"] == 3) {
                                                $archivo = $archivo . "<a target='_blank' href='https://wa.me/+52$cliente_telefono?text=Apreciable%20cliente,%20confirmamos%20de%20recibido%20su%20equipo%20con%20Orden%20de%20servicio%20No.$folio,%20puede%20descargar%20su%20hoja%20directamente%20del%20siguiente%20enlace:%20$ordenPDF%0AA%20trav%C3%A9s%20de%20este%20medio%20le%20enviaremos%20notificaciones%20respecto%20al%20avance%20de%20su%20servicio.%0AAdicionalmente%20le%20informamos%20que%20puede%20consultar%20el%20estatus%20de%20su%20servicio%20directamente%20en%20este%20enlace:%20$seguimiento%0A*Este%20es%20un%20mensaje%20automatizado,%20si%20tiene%20alguna%20duda%20o%20requiere%20atenci%C3%B3n%20personalizada,%20con%20gusto%20le%20atender%C3%A1%20uno%20de%20nuestros%20ejecutivos%20dando%20clic%20en%20el%20wsp%204778266777%0ASi%20no%20puede%20descargar%20su%20orden,%20guarde%20este%20número%20en%20sus%20contactos%20e%20intente%20nuevamente'><i class='bx bxl-whatsapp btn-whatsapp'></i></a>";
                                            }

                                            if ($row["tipo"] == 4) {
                                                $archivo = $archivo . "<a target='_blank' href='https://wa.me/+52$cliente_telefono?text=Apreciable%20cliente,%20le%20informamos%20que%20ha%20sido%20concluido%20su%20servicio%20y%20su%20equipo%20se%20encuentra%20listo%20para%20su%20entrega.%0A*Este%20es%20un%20mensaje%20automatizado,%20si%20tiene%20alguna%20duda%20o%20requiere%20atenci%C3%B3n%20personalizada,%20con%20gusto%20le%20atender%C3%A1%20uno%20de%20nuestros%20ejecutivos%20dando%20clic%20en%20el%20wsp%204778266777'><i class='bx bxl-whatsapp btn-whatsapp'></i></a>";
                                            }

                                            if ($row["tipo"] == 5) {
                                                $archivo = $archivo . "<a target='_blank' href='https://wa.me/+52$cliente_telefono?text=Confirmamos%20de%20entregado%20su%20equipo%20con%20Orden%20de%20servicio%20No.$folio,%20puede%20descargar%20su%20recibo%20de%20pago%20directamente%20del%20siguiente%20enlace:$ventaPDFlink%20%0AGracias%20por%20permitirnos%20ayudarles%20en%20sus%20necesidades%20tecnol%C3%B3gicas,%20%C2%A1fue%20un%20placer%20anterderle!.%0A*Este%20es%20un%20mensaje%20automatizado,%20si%20tiene%20alguna%20duda%20o%20requiere%20atenci%C3%B3n%20personalizada,%20con%20gusto%20le%20atender%C3%A1%20uno%20de%20nuestros%20ejecutivos%20dando%20clic%20en%20el%20wsp%204778266777'><i class='bx bxl-whatsapp btn-whatsapp'></i></a>";
                                            }

                                            if ($row["tipo"] == 6) {
                                                $archivo = $archivo . "<a target='_blank' href='https://wa.me/+52$cliente_telefono?text=Para%Tectron%20Tecnolog%C3%ADas%20del%20Baj%C3%ADo%20tu%20opini%C3%B3n%20es%20important%C3%ADsima%20%C2%BFser%C3%ADas%20tan%20amable%20de%20apoyarnos%20a%20responder%20la%20siguiente%20encuesta%20de%20satisafacci%C3%B3n?%20Son%20solamente%206%20preguntas%20que%20nos%20ayudar%C3%ADan%20mucho%20a%20mejorar!%20$encuesta'><i class='bx bxl-whatsapp btn-whatsapp'></i></a>";
                                            }

                                            if ($row["tipo"] == 7) {
                                                $archivo = $archivo . "<a target='_blank' href='https://wa.me/+52$cliente_telefono?text=De%20parte%20de%20todo%20el%20equipo%20de%Tectron%20Tecnolog%C3%ADas%20del%20Baj%C3%ADo,%20agradecemos%20la%20oportunidad%20de%20ayudar%20en%20la%20soluci%C3%B3n%20de%20tus%20problemas%20t%C3%A9cnicos.%20Si%20te%20gust%C3%B3%20nuestro%20servicio%20%C2%BFnos%20apoyar%C3%ADas%20con%20tu%20calificaci%C3%B3n%20en%20Google%20Maps?%20Con%20un%20minuto%20de%20tu%20tiempo%20nos%20ayudar%C3%ADas%20much%C3%ADsimo!%20%0A%0APara%20sucursal%20paseo%20del%20moral:$paseo_moral%20%0A%0APara%20sucursal%20Le%C3%B3n%20Moderno:$leon_moderno%20'><i class='bx bxl-whatsapp btn-whatsapp'></i></a>";
                                            }
                                            #endregion


                                            echo <<<HTML
                                                <tr class='odd gradeX'$estilo>
                                                    <td>$publico $botonp</td>
                                                    <td>$row[fk_autor]</td>
                                                    <td>$row[fecha]</td>
                                                    <td style='white-space: normal'>$row[comentarios]</td>
                                                    <td style='white-space: normal'>$row[precio]</td>
                                                    <td style='white-space: normal'>$row[costo]</td>
                                                    <td><div class='d-flex'>$archivo</div></td>
                                                </tr>
                                            HTML;
                                        }
                                        ?>
                                    </tbody>
                                </table>
                                <div class='row'>&nbsp;</div>
                            </div>

                            <?php

                            if ($estatus == 3 && $espera == 0) {
                                echo <<<HTML
                                    <div class='card-footer'>
                                        <button type='button' id='agregar' class='btn btn-reasignar-txt'>
                                            <i class='fa fa-plus mx-2'></i> Agregar
                                        </button>
                                    </div>
                                HTML;
                            }

                            if ($estatus == 3 && $espera == 1) {
                                echo <<<HTML
                                    <div class='card-footer'>
                                        <button type='button' id='reactivar' class='btn btn-reasignar-txt'>
                                            <i class='bx bx-power-off mx-2'></i> Reactivar
                                        </button>
                                    </div>
                                HTML;
                            }

                            if ($estatus == 4) {
                                echo <<<HTML
                                    <div class='card-footer'>
                                        <button type='button' id='entregar' class='btn btn-reasignar-txt'>
                                            <i class='fa fa-send mx-2'></i> Entregar
                                        </button>
                                    </div>
                                HTML;
                            }

                            ?>
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
    <script src="assets/lib/data-table/jszip.min.js"></script>
    <script src="assets/lib/data-table/pdfmake.min.js"></script>
    <script src="assets/lib/data-table/vfs_fonts.js"></script>
    <script src="assets/lib/data-table/buttons.html5.min.js"></script>
    <script src="assets/lib/data-table/buttons.print.min.js"></script>
    <script src="assets/lib/data-table/buttons.colVis.min.js"></script>
    <script src="assets/lib/data-table/datatables-init.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="assets/loading/loadingoverlay.js"></script>
    <script src="custom/verOrden.js?v=<?= time(); ?>"></script>

    <script>
        $('#dtEmpresa').DataTable({
            responsive: true,
            ordering: true,
            order: [
                [2, 'asc']
            ],
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
