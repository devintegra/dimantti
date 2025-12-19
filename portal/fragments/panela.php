<?php


$qproductos = "select count(*) as total from ct_productos where estado=1";

if (!$rproductos = $mysqli->query($qproductos)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}
$productos = $rproductos->fetch_assoc();
$nproductos = $productos["total"];



?>

<style>
    .content-body {
        flex-wrap: wrap;
    }

    .content-card {
        margin: 1.5rem;
    }

    .card-1>div {
        background-image: radial-gradient(circle 248px at center, #16d9e3 0%, #30c7ec 47%, #46aef7 100%);
    }

    .card-2>div {
        background-image: linear-gradient(109deg, #7742B2 0%, #F180FF 72%, #FD8BD9 100%);
    }

    .card-3>div {
        background-image: linear-gradient(103deg, #ff5858 0%, #f09819 100%);
    }

    .content-text {
        height: 150px;
    }

    .footer {
        background-color: transparent;
        border-radius: 0 0 20px 20px;
    }

    .footer>a,
    .footer>div>a {
        text-decoration: none;
        color: #f4f4f4;
        font-size: 1rem;
    }

    h4 {
        font-size: 2.5rem;
    }

    p {
        font-size: 1.2rem;
    }

    i {
        font-size: 6rem;
        color: #f4f4f4;
    }

    @media screen and (min-width: 320px) and (max-width: 610px) {
        .content-body {
            flex-direction: column;
        }

        .content-card {
            margin: 1.5rem 0;
        }
    }
</style>

<div class="col-12 grid-margin stretch-card">
    <div class="card card-rounded">
        <div class="card-body d-flex justify-content-center content-body">

            <div class="col-sm-6 col-lg-3 content-card card-1 overflow-hidden">
                <div class="card text-white bg-flat-color-1">
                    <div class="content-text card-body pb-0 d-flex justify-content-between">
                        <div class="d-flex flex-wrap justify-content-center align-items-center">
                            <div>
                                <h4 class="mb-0 text-start">
                                    Punto de venta
                                </h4>
                                <!-- <p class="text-light">Administrar</p> -->
                            </div>

                        </div>
                        <div class="d-flex flex-wrap justify-content-center align-items-center">
                            <i class='bx bx-cart-alt'></i>
                        </div>
                    </div>

                    <div class="footer px-0 d-flex justify-content-center align-items-end text-center pb-3" style="height:40px;" height="70">
                        <div class="col-lg-6"><a href="puntoVenta.php">Nueva venta</a></div>
                        <div class="col-lg-6"><a href="verVentas.php">Historial</a></div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3 content-card card-2 overflow-hidden">
                <div class="card text-white bg-flat-color-2">
                    <div class="content-text card-body pb-0 d-flex justify-content-between">
                        <div class="d-flex flex-wrap justify-content-center align-items-center">
                            <div>
                                <h4 class="mb-0 text-start">
                                    Corte de caja
                                </h4>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap justify-content-center align-items-center">
                            <i class='bx bx-money-withdraw'></i>
                        </div>
                    </div>

                    <div class="footer px-0 d-flex justify-content-center align-items-end text-center pb-3" style="height:40px;" height="70">
                        <a href="agregarCorte.php">Realizar corte</a>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3 content-card card-3 overflow-hidden">
                <div class="card text-white bg-flat-color-3">
                    <div class="content-text card-body pb-0 d-flex justify-content-between">
                        <div class="d-flex flex-wrap justify-content-center align-items-center">
                            <div>
                                <h4 class="mb-0">
                                    <?php
                                    echo "<span class=\"count\">$nproductos</span>";
                                    ?>
                                </h4>
                                <p class="text-light">Productos</p>
                            </div>

                        </div>
                        <div class="d-flex flex-wrap justify-content-center align-items-center">
                            <i class='bx bxs-devices'></i>
                        </div>
                    </div>

                    <div class="footer px-0 d-flex justify-content-center align-items-end text-center pb-3" style="height:40px;" height="70">
                        <a href="verProductos.php">Ver productos</a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
