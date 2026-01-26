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
    $tipo = "Chofer";
    $menu = "fragments/menub.php";
}


if ($nivel != 1) {
    header('Location: ../index.php');
}



if (isset($_GET['id']) && is_string($_GET['id'])) {
    $pk_cliente = (int)$_GET['id'];
}



//DATOS
#region
$eusuario = "SELECT ctc.*,
        ctr.clave as regimen
    FROM ct_clientes ctc
    LEFT JOIN ct_regimenes_fiscales ctr ON ctr.pk_regimen_fiscal = ctc.fk_regimen_fiscal
    WHERE ctc.pk_cliente = $pk_cliente";

if (!$resultado = $mysqli->query($eusuario)) {
    echo "<br>Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

$row = $resultado->fetch_assoc();
$fk_ruta = $row["fk_ruta"];
$clave = $row["clave"];
$nombre = $row["nombre"];
$telefono = $row["telefono"];
$correo = $row["correo"];
$dias_credito = $row["dias_credito"];
$limite_credito = $row["limite_credito"];
$credito = $row["credito"];
$abonos = $row["abonos"];
$fk_categoria = $row["fk_categoria_cliente"];
$cp = $row["cp"];
$rfc = $row["rfc"];
$fk_regimen_fiscal = $row["fk_regimen_fiscal"];
$lunes = $row["lunes"];
$martes = $row["martes"];
$miercoles = $row["miercoles"];
$jueves = $row["jueves"];
$viernes = $row["viernes"];
$sabado = $row["sabado"];
$domingo = $row["domingo"];
$clave_regimen = $row["regimen"];
$direccion = $row["direccion"];
$latitud = $row["latitud"];
$longitud = $row["longitud"];

//Evitar modificar el nombre del Cliente General
$disabled = "";
if ($nombre == "Cliente general") {
    $disabled = "disabled";
}
#endregion


//REGIMENES
#region
$qregimen = "SELECT * FROM ct_regimenes_fiscales where estado=1";

if (!$rregimen = $mysqli->query($qregimen)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.2";
    exit;
}
#endregion


//LISTAS DE PRECIOS
#region
$arrayPrecios = array(
    array('id' => 1, 'nombre' => 'Precio N°1'),
    array('id' => 2, 'nombre' => 'Precio N°2'),
    array('id' => 3, 'nombre' => 'Precio N°3'),
    array('id' => 4, 'nombre' => 'Precio N°4')
);
#endregion


//RUTAS
#region
$qrutas = "SELECT * FROM ct_rutas where estado=1";

if (!$rrutas = $mysqli->query($qrutas)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}
#endregion


//DIAS
#region
$arrayDias = array(
    array('id' => 'lunes', 'nombre' => 'Lunes', 'valor' => $lunes),
    array('id' => 'martes', 'nombre' => 'Martes', 'valor' => $martes),
    array('id' => 'miercoles', 'nombre' => 'Miércoles', 'valor' => $miercoles),
    array('id' => 'jueves', 'nombre' => 'Jueves', 'valor' => $jueves),
    array('id' => 'viernes', 'nombre' => 'Viernes', 'valor' => $viernes),
    array('id' => 'sabado', 'nombre' => 'Sábado', 'valor' => $sabado),
    array('id' => 'domingo', 'nombre' => 'Domingo', 'valor' => $domingo),
);
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

    <link rel="shortcut icon" href="images/user.jpg" />

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
                            <h4 class="card-title">Editar cliente</h4>
                            <i class='bx bx-group' style="font-size:32px"></i>
                        </div><br>
                        <form class="forms-sample" enctype="multipart/form-data" id="formuploadajax">

                            <h4 class="card-title col-lg-4 fs-4 d-flex justify-content-start align-items-center gap-2" style="color: #2563EB; font-weight: bold;"><i class='bx bx-user-circle fs-4'></i>Datos generales</h4>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="nombre">Razón social</label>
                                        <?php
                                        echo "<input type='text' id='nombre' name='nombre' placeholder='Razón social' class='form-control' value='$nombre' $disabled autocomplete='off'>
                                        <input type='hidden' id='pk_cliente' class='form-control' value='$pk_cliente' autocomplete='off'>";
                                        ?>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="telefono">Teléfono</label>
                                        <?php
                                        echo "<input type='text' id='telefono' name='telefono' placeholder='Teléfono' class='form-control' value='$telefono'>";
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="correo">Correo</label>
                                        <?php
                                        echo "<input type='email' id='correo' name='correo' placeholder='Correo' class='form-control' value='$correo'>";
                                        ?>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="cp">Código postal</label>
                                        <?php
                                        echo "<input type='text' id='cp' name='cp' placeholder='Código postal' class='form-control' value='$cp'>";
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="rfc">RFC</label>
                                        <?php
                                        echo "<input type='text' id='rfc' name='rfc' placeholder='RFC' class='form-control' value='$rfc'>";
                                        ?>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="regimen_fiscal">Regímen fiscal</label>
                                        <select class='form-control' id='regimen_fiscal'>
                                            <option value='0'>SELECCIONE</option>
                                            <?php
                                            while ($regimen = $rregimen->fetch_assoc()) {
                                                if ($regimen['pk_regimen_fiscal'] == $fk_regimen_fiscal) {
                                                    echo "<option value='$regimen[pk_regimen_fiscal]' selected>$regimen[clave]-$regimen[descripcion]</option>";
                                                } else {
                                                    echo "<option value='$regimen[pk_regimen_fiscal]'>$regimen[clave]-$regimen[descripcion]</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="fk_ruta">Ruta</label>
                                        <select class="form-control" id="fk_ruta">
                                            <option value="0">Seleccione</option>
                                            <?php
                                            while ($rowr = $rrutas->fetch_assoc()) {
                                                if ($rowr['pk_ruta'] == $fk_ruta) {
                                                    echo "<option value='$rowr[pk_ruta]' selected>$rowr[nombre]</option>";
                                                } else {
                                                    echo "<option value='$rowr[pk_ruta]'>$rowr[nombre]</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="dia">Día</label>
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <?php
                                            foreach ($arrayDias as $rowd) {
                                                $checked = $rowd['valor'] == 1 ? "checked" : "";
                                                echo <<<HTML
                                                    <input type="checkbox" id="$rowd[id]" class="chkDia" style="width: 20px; height: 20px;" $checked>
                                                    <label class="mb-0">$rowd[nombre]</label>
                                                HTML;
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="direccion">Dirección</label>
                                        <?php
                                        echo "<input type='text' id='direccion' name='direccion' placeholder='Dirección' class='form-control' value='$direccion'>";
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12 form-group" style="height: 200px;">
                                    <?php
                                    echo <<<HTML
                                        <input type='hidden' id='latitud' name='text-input' value='$latitud'>
                                        <input type='hidden' id='longitud' name='text-input' value='$longitud'>
                                    HTML;
                                    ?>
                                    <div id="mapa" style="height: 100%;"></div>
                                </div>
                            </div>



                            <br>
                            <div class="line-success-integra"></div><br>

                            <h4 class="card-title col-lg-4 fs-4 d-flex justify-content-start align-items-center gap-2" style="color: #2CA880; font-weight: bold;"><i class='bx bx-data fs-4'></i>Crédito</h4>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="abonos">¿Se aceptan abonos?</label>
                                        <select class="form-control" id="abonos">
                                            <option value="0">Seleccione</option>
                                            <?php
                                            if ($abonos == 1) {

                                                echo "<option value='1' selected>Si</option>
                                                    <option value='2'>No</option>";
                                            } else {

                                                echo "<option value='1'>Si</option>
                                                    <option value='2' selected>No</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="dias_credito">Días de crédito</label>
                                        <?php
                                        echo "<input type='number' id='dias_credito' name='dias_credito' placeholder='Días de crédito' class='form-control' value='$dias_credito'>";
                                        ?>
                                    </div>
                                </div>

                            </div>

                            <div class="row">

                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="limite_credito">Límite de crédito</label>
                                        <?php
                                        echo "<input type='text' id='limite_credito' name='limite_credito' placeholder='Límite de crédito' class='form-control' value='$limite_credito'>";
                                        ?>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="credito">Crédito disponible</label>
                                        <?php
                                        echo "<input type='text' id='credito' name='credito' placeholder='Crédito disponible' class='form-control' value='$credito'>";
                                        ?>
                                    </div>
                                </div>

                            </div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="categoria">Lista de precios asignada</label>
                                        <select class='form-control dato' id='categoria'>
                                            <?php
                                            foreach ($arrayPrecios as $rowp) {
                                                if ($rowp['id'] == $fk_categoria) {
                                                    echo "<option value='$rowp[id]' selected>$rowp[nombre]</option>";
                                                } else {
                                                    echo "<option value='$rowp[id]'>$rowp[nombre]</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>



                            <br><br>

                            <div class="row d-flex justify-content-center">
                                <?php
                                if ($nivel == 1) {
                                    echo "
                                        <div class='row col-lg-4 m-2'>
                                            <button id='eliminar' type='button' class='btn btn-danger mx-2 d-flex justify-content-center align-items-center'><i class='bx bx-x-circle mx-2' style='font-size: 20px;'></i>Eliminar</button>
                                        </div>
                                        ";
                                }
                                ?>
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
    <script src="custom/jquery.confirm.js"></script>
    <script src="custom/jquery.maskedinput.js"></script>
    <script src="assets/loading/loadingoverlay.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script type="text/javascript" src="https://maps.google.com/maps/api/js?key=AIzaSyAyzxxx9sOTmkGCHGgrK_Xy86eQxB-AxuI&libraries=places"></script>
    <script src="custom/numberFormats.js"></script>
    <script src="custom/editarCliente.js?v=<?= time(); ?>"></script>
</body>

</html>
