<?php
header('Cache-control: private');
include("servicios/conexioni.php");
mysqli_set_charset($mysqli, 'utf8');
@session_start();



if (isset($_GET['id']) && is_string($_GET['id'])) {
    $clave = (int)$_GET['id'];
}

if (!isset($_GET['id']) || strlen($clave) == 0 || !$clave) {
    header('Location: index.php');
}



//PRODUCTO
#region
$mysqli->next_result();
if (!$rsp_get_producto = $mysqli->query("CALL sp_get_producto_by_clave('$clave')")) {
    echo "<br>Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

$row = $rsp_get_producto->fetch_assoc();
$pk_producto = $row["pk_producto"];
$nombre = $row["nombre"];
$metal = $row["metal"];
$categoria = $row["categoria"];
$descripcion = $row["descripcion"];
$costo = $row["costo"];
$tipo_precio = $row["tipo_precio"];
$precio = number_format($row["precio"], 2);
$utilidad = $row["utilidad"];
$gramaje = $row["gramaje"];
$precio_metal = number_format($row["precio_metal"], 2);
$inventario = $row["inventario"];
$inventariomin = $row["inventariomin"];
$inventariomax = $row["inventariomax"];
$clave_producto_sat = $row["clave_producto_sat"];
$clave_unidad_sat = $row["clave_unidad_sat"];

$tipo_precio_venta = ((int)$tipo_precio == 1) ? "Precio fijo" : "Varia respecto al valor actual del metal";
$gramaje_valor = ((int)$tipo_precio == 1) ? "NA" : "$gramaje gr";
$precio_venta = ((int)$tipo_precio == 1) ? $precio : number_format($row['precio_metal'] * $gramaje, 2);
#endregion



//IMAGENES
#region
$mysqli->next_result();
if (!$rsp_get_imagenes = $mysqli->query("CALL sp_get_producto_imagenes($pk_producto)")) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}
#endregion



//DATOS DE LA EMPRESA
#region
$mysqli->next_result();
if (!$rsp_get_empresa = $mysqli->query("CALL sp_get_empresa(1)")) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

$rowe = $rsp_get_empresa->fetch_assoc();
$sucursal_nombre = $rowe["nombre"];
$sucursal_direccion = $rowe["direccion"];
$sucursal_telefono = $rowe["telefono"];
#endregion



?>

<!doctype html>

<html class="no-js" lang="">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Dimantti - Consola de administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <link rel="shortcut icon" href="images/user-sbg.png" />

    <style>
        * {
            padding: 0;
            margin: 0;
            box-sizing: border-box;
        }

        @import url("https://fonts.googleapis.com/css2?family=Abril+Fatface&display=swap");
        @import url("https://fonts.googleapis.com/css2?family=Montserrat&display=swap");

        body {
            font-family: "Montserrat", sans-serif;
            /* font-family: "Abril Fatface", cursive; */
            color: #444444;
            background-color: #f7f7f7;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        header {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            padding: 20px;
            gap: 10px;
        }

        header>img,
        footer img {
            width: 10%;
        }

        header>h1 {
            font-size: 20px;
            text-align: center;
        }

        #content-main {
            width: 75%;
            background-color: #fff;
            border-radius: 20px;
            margin-bottom: 50px;
            box-shadow: rgba(149, 157, 165, 0.2) 0px 8px 24px;
        }

        #content-info {
            padding: 80px;
            text-align: left;
        }

        #content-image {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .carousel-item>img {
            height: 400px;
            width: auto;
            border-radius: 20px;
        }

        .carousel-prev-icon,
        .carousel-next-icon {
            display: inline-block;
            font-size: 50px;
            color: #000;
        }

        .badge-seguimiento {
            width: fit-content;
            height: auto;
            padding: 20px 35px;
            border-radius: 35px;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .badge-seguimiento-info {
            background-color: #83c7ff;
        }

        .badge-seguimiento-success {
            background-color: #82fd69;
        }

        .badge-seguimiento-warning {
            background-color: #f8f545;
        }

        #content-details {
            width: 75%;
        }

        @media (max-width: 1024px) {

            #content-main,
            #content-details {
                width: 90%;
            }

            header>img,
            footer img {
                width: 30%;
            }

            header>h1 {
                font-size: 16px;
            }

            .carousel-item>img {
                height: 200px;
            }

            #content-info {
                padding: 20px;

                h1 {
                    font-size: 16px;
                }

                h4,
                h3 {
                    font-size: 14px;
                }
            }

            .badge-seguimiento {
                padding: 13px 28px;
            }
        }
    </style>

</head>

<body>

    <header>
        <img src="images/logotipo.png" alt="Dimantti Logo">
        <h1><?php echo $sucursal_nombre . " | <i class='bx bx-map-pin'></i> " . $sucursal_direccion . " | <i class='bx bx-phone'></i> " . $sucursal_telefono; ?></h1>
    </header>


    <section id="content-main" class="d-flex flex-column justify-content-center align-items-center flex-wrap">

        <div id="content-image" class="w-100 py-4">
            <?php if ($rsp_get_imagenes->num_rows > 0) : ?>
                <div id="carouselExample" class="carousel slide">
                    <div class="carousel-inner">
                        <?php
                        $i = 0;
                        while ($rowi = $rsp_get_imagenes->fetch_assoc()) {
                            if ($i == 0) {
                                echo "
                                    <div class='carousel-item active'>
                                        <img src='https://dimantti.integracontrol.online/portal/servicios/productos/$rowi[imagen]' class='d-block'>
                                    </div>
                                ";
                            } else {
                                echo "
                                    <div class='carousel-item'>
                                        <img src='https://dimantti.integracontrol.online/portal/servicios/productos/$rowi[imagen]' class='d-block'>
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


        <div id="content-info" class="w-100 text-center d-flex flex-column justify-content-center align-items-center">
            <?php
            echo <<<HTML
                <h4>$clave</h4>
                <h1 class="card-title fw-bold">$nombre</h1>
                <h4>$descripcion</h4>
                <h4> <span class="fw-bold">Categoría:</span> $categoria</h4>
                <h4> <span class="fw-bold">Metal:</span> $metal</h4>
                <h4> <span class="fw-bold">Gramaje:</span> $gramaje_valor</h4>
                <h4> <span class="fw-bold">Tipo de precio:</span> $tipo_precio_venta</h4>
                <h4>$metal actualmente en: $$precio_metal x gr</h4>
                <h3 class='badge-seguimiento badge-seguimiento-info'>Precio de venta: $$precio_venta</h3>
                    <br>
            HTML;
            ?>
        </div>

    </section>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>


</body>

</html>
