<?php
include("portal/servicios/conexioni.php");


//DATOS DE LA SUCURSAL
#region
$mysqli->next_result();
if (!$rsucursal = $mysqli->query("CALL sp_get_empresa(1)")) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. 4";
    exit;
}

$sucursal = $rsucursal->fetch_assoc();
$sucursal_nombre = $sucursal["nombre"];
$sucursal_direccion = $sucursal["direccion"];
$sucursal_telefono = $sucursal["telefono"];
#endregion



//CLUAUSLAS
#region
$mysqli->next_result();
if (!$rclausulas = $mysqli->query("CALL sp_get_clausulas()")) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
}

$clausulas = $rclausulas->fetch_all(MYSQLI_ASSOC);
#endregion

?>

<!DOCTYPE html>
<html lang="zxx">

<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="seguimiento.css">
    <title>Dimantti | Cláusulas de garantía</title>
    <link rel="shortcut icon" href="portal/images/user-sbg.png" />
</head>

<body>

    <header>
        <img src="portal/servicios/logotipo.png" alt="Dimantti Logo">
        <h1><?php echo $sucursal_nombre . " | <i class='bx bx-map-pin'></i> " . $sucursal_direccion . " | <i class='bx bx-phone'></i> " . $sucursal_telefono; ?></h1>
    </header>


    <section id="content-main">
        <h2 class="text-center mt-4">Cláusulas de garantía</h2>
        <br>
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="faq-accordin">
                        <div class="accordion" id="accordionExample">
                            <ol>
                                <?php
                                foreach ($clausulas as $clausula) {
                                    $descripcion = $clausula['descripcion'];
                                    $orden = $clausula['orden'];
                                    echo <<<HTML
                                        <li>
                                            $descripcion
                                        </li><br>
                                    HTML;
                                }
                                ?>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>

</body>

</html>
