<?php
include("portal/servicios/conexioni.php");

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];
}

if (isset($_GET['ph']) && is_string($_GET['ph'])) {
    $telefono = $_GET['ph'];
}

$telefonoOutFormat = preg_replace('/\D/', '', $telefono);


//DATOS GENERALES
#region
$mysqli->next_result();
if (!$rentrada = $mysqli->query("CALL sp_get_orden($id)")) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. 1" . $mysqli->error;
    exit;
}

$entrada = $rentrada->fetch_assoc();
$entrada_fecha = $entrada["fecha"];
$folio = $entrada["folio"];
$entrada_estatus = $entrada["estatus"];
$entrada_fk_cliente = $entrada["fk_cliente"];
$entrada_fk_sucursal = $entrada["fk_sucursal"];
$cliente_nombre = $entrada["cliente"];
#endregion


//REGISTROS
#region
$mysqli->next_result();
if (!$rregistros = $mysqli->query("CALL sp_get_orden_registros_publicos($id)")) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. 3";
    exit;
}
#endregion


//DATOS DE LA SUCURSAL
#region
$mysqli->next_result();
if (!$rsucursal = $mysqli->query("CALL sp_get_sucursal($entrada_fk_sucursal)")) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. 4";
    exit;
}

$sucursal = $rsucursal->fetch_assoc();
$sucursal_inicial = $sucursal["iniciales"];
$sucursal_nombre = $sucursal["nombre"];
$sucursal_direccion = $sucursal["direccion"];
$sucursal_telefono = $sucursal["telefono"];
#endregion


//ESTATUS
#region
$elestatus = "";

if ($entrada_estatus <= 3) {
    $icon_estatus = "<i class='bx bx-time-five fs-3 mx-2'></i>";
    $elestatus = "En Curso";
    $badge = 'badge-seguimiento-warning';
}

if ($entrada_estatus == 4) {
    $icon_estatus = "<i class='bx bx-check fs-3 mx-2'></i>";
    $elestatus = "Terminada";
    $badge = 'badge-seguimiento-success';
}

if ($entrada_estatus == 5) {
    $icon_estatus = "<i class='bx bxs-truck fs-3 mx-2'></i>";
    $elestatus = "Entregada";
    $badge = 'badge-seguimiento-info';
}
#endregion


//DATOS DEL EQUIPO
#region
$mysqli->next_result();
if (!$requipos = $mysqli->query("CALL sp_get_orden_detalle($id)")) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. 5";
    exit;
}

$equipo = $requipos->fetch_assoc();
$equipo_nombre = $equipo["nombre"];
$equipo_falla = $equipo["categoria"];
$equipo_ns = $equipo["ns"];
$equipo_marca = $equipo["marca"];
$equipo_modelo = $equipo["modelo"];
#endregion


//ORDEN REGISTROS/PIEZAS
#region
$mysqli->next_result();
if (!$rimagenes = $mysqli->query("CALL sp_get_orden_archivos($id)")) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. 6";
    exit;
}
#endregion

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="seguimiento.css">
    <title>Órden #<?php echo $folio ?> - Dimantti</title>
    <link rel="shortcut icon" href="portal/images/user-sbg.png" />
</head>

<body>

    <header>
        <img src="portal/servicios/logotipo.png" alt="Dimantti Logo">
        <h1><?php echo $sucursal_nombre . " | <i class='bx bx-map-pin'></i> " . $sucursal_direccion . " | <i class='bx bx-phone'></i> " . $sucursal_telefono; ?></h1>
    </header>


    <section id="content-main" class="d-flex flex-wrap">
        <div id="content-info" class="col-sm-12 col-lg-6">
            <h1 class="card-title fw-bold">#<?php echo $folio ?></h1>
            <h4 class="d-flex justify-content-start align-items-center"> <i class='bx bx-user-circle fs-4'></i> <?php echo $cliente_nombre ?></h4>
            <h4 class="d-flex justify-content-start align-items-center"> <i class='bx bx-shopping-bag fs-4'></i> <?php echo $equipo_nombre ?></h4>
            <h5> Categoría: <?php echo $equipo_falla ?></h5>
            <h5> Marca: <?php echo $equipo_marca ?></h5>
            <h5> Modelo: <?php echo $equipo_modelo ?></h5>
            <br>
            <?php echo "<h3 class='badge-seguimiento $badge'> $icon_estatus $elestatus</h3>"; ?>
        </div>

        <div id="content-image" class="col-sm-12 col-lg-6 py-4">
            <?php if ($rimagenes->num_rows > 0) : ?>
                <div id="carouselExample" class="carousel slide">
                    <div class="carousel-inner">
                        <?php
                        $i = 0;
                        while ($imagenes = $rimagenes->fetch_assoc()) {
                            if ($i == 0) {
                                echo "
                                    <div class='carousel-item active'>
                                        <img src='https://dimantti.integracontrol.online/portal/servicios/pruebas/$imagenes[archivo]' class='d-block'>
                                    </div>
                                ";
                            } else {
                                echo "
                                    <div class='carousel-item'>
                                        <img src='https://dimantti.integracontrol.online/portal/servicios/pruebas/$imagenes[archivo]' class='d-block'>
                                    </div>
                                ";
                            }
                            $i++;
                        }
                        ?>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselExample" data-bs-slide="prev">
                        <span class="carousel-prev-icon" aria-hidden="true"><i class='bx bx-chevron-left'></i></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carouselExample" data-bs-slide="next">
                        <span class="carousel-next-icon" aria-hidden="true"><i class='bx bx-chevron-right'></i></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </section>


    <section id="content-details">
        <div class="w-100">

            <h2 class="fw-bold d-flex justify-content-start align-items-center gap-2"><i class="bx bxs-truck"></i> Seguimiento</h2>
            <br>

            <table class="table table-striped text-center">
                <thead class="table-info">
                    <tr>
                        <th>EVIDENCIA</th>
                        <th>FECHA</th>
                        <th>DESCRIPCION</th>
                        <th>PRECIO</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($registros = $rregistros->fetch_assoc()) {

                        $precio = '';
                        if ($registros['precio'] > 0) {
                            $precio = '$' . number_format($registros['precio'], 2);
                        }

                        if ($registros['archivo']) {
                            $file = "<img src='$registros[archivo]' class='item-image'>";
                        } else {
                            $file = "";
                        }

                        echo "
                            <tr>
                                <td>$file</td>
                                <td>$registros[fecha] $registros[hora]</td>
                                <td style='white-space: normal;'>$registros[comentarios]</td>
                                <td>$precio</td>
                            </tr>
                        ";
                    }
                    ?>
                </tbody>
            </table>
        </div>

    </section>


    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>

</body>

</html>
