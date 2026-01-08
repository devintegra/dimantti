<?php

$mapaRutaVentas = "fragments/mapaRutaVentas.php";

//CONSULTAS
#region

//Ventas móviles y punto de venta
#region
$qventastotal = "SELECT COUNT(CASE WHEN tipo = 1 THEN 1 END) AS total_mostrador,
  COUNT(CASE WHEN tipo = 2 THEN 1 END) AS total_web
  FROM tr_ventas
  WHERE fecha BETWEEN DATE_SUB(CURDATE(), INTERVAL 1 MONTH) AND CURDATE();";

if (!$rventastotal = $mysqli->query($qventastotal)) {
    echo "<br>Lo sentimos, esta aplicación está experimentando problemas.1";
    exit;
}
$ventastotal = $rventastotal->fetch_assoc();
$ventas_mostrador = $ventastotal["total_mostrador"];
$ventas_web = $ventastotal["total_web"];
#endregion


//Compras vs ventas
#region
$qcomprasventas = "SELECT
  tr_ventas.total_ventas AS total_ventas,
  tr_compras.total_compras AS total_compras
  FROM
      (SELECT COUNT(*) AS total_ventas FROM tr_ventas
      WHERE fecha BETWEEN DATE_SUB(CURDATE(), INTERVAL 1 MONTH) AND CURDATE()) AS tr_ventas
  JOIN
      (SELECT COUNT(*) AS total_compras FROM tr_compras
      WHERE fecha BETWEEN DATE_SUB(CURDATE(), INTERVAL 1 MONTH) AND CURDATE()) AS tr_compras;";

if (!$rcomprasventas = $mysqli->query($qcomprasventas)) {
    echo "<br>Lo sentimos, esta aplicación está experimentando problemas.2";
    exit;
}
$comprasventas = $rcomprasventas->fetch_assoc();
$total_compras = $comprasventas["total_compras"];
$total_ventas = $comprasventas["total_ventas"];
#endregion


//Ventas por sucursal
#region
$qventassucursal = "SELECT
    s.pk_sucursal,
    s.nombre,
    IFNULL(COUNT(v.pk_venta), 0) AS total_ventas
    FROM ct_sucursales s
    LEFT JOIN tr_ventas v ON s.pk_sucursal = v.fk_sucursal
      AND v.fecha BETWEEN DATE_SUB(CURDATE(), INTERVAL 1 MONTH) AND CURDATE()
    WHERE s.estado = 1
    GROUP BY s.pk_sucursal, s.nombre;";

if (!$rventassucursal = $mysqli->query($qventassucursal)) {
    echo "<br>Lo sentimos, esta aplicación está experimentando problemas.1";
    exit;
}
#endregion


//Top 10 clientes
#region
$qtopclientes = "SELECT
    c.pk_cliente,
    c.nombre,
    COUNT(*) AS total_compras
    FROM tr_ventas v
    JOIN ct_clientes c ON v.fk_cliente = c.pk_cliente
    WHERE v.fecha BETWEEN DATE_SUB(CURDATE(), INTERVAL 1 MONTH) AND CURDATE()
    GROUP BY c.pk_cliente, c.nombre
    ORDER BY total_compras DESC
    LIMIT 10;";

if (!$rtopclientes = $mysqli->query($qtopclientes)) {
    echo "<br>Lo sentimos, esta aplicación está experimentando problemas.2";
    exit;
}
#endregion


//Top 10 productos
#region
$qtopproductos = "SELECT
    p.pk_producto,
    p.nombre,
    SUM(vd.cantidad) AS total_vendido
    FROM ct_productos p
    JOIN tr_ventas_detalle vd ON p.pk_producto = vd.fk_producto AND vd.devuelto = 0
    JOIN tr_ventas v ON vd.fk_venta = v.pk_venta
    WHERE v.fecha BETWEEN DATE_SUB(CURDATE(), INTERVAL 1 MONTH) AND CURDATE()
    AND v.estatus != 3
    GROUP BY p.pk_producto, p.nombre
    ORDER BY total_vendido DESC
    LIMIT 10;";

if (!$rtopproductos = $mysqli->query($qtopproductos)) {
    echo "<br>Lo sentimos, esta aplicación está experimentando problemas.2";
    exit;
}
#endregion

#endregion


?>

<!--GRÁFICA-->
<div class="col-lg-8 col-12 d-flex flex-column" id="ventas_dia">

    <div class="row">
        <div class="col-12 col-lg-4 col-lg-12 grid-margin stretch-card">
            <div class="card card-rounded" style="border-top: 4px solid #ff4040;">
                <div class="card-body">
                    <div class="d-sm-flex justify-content-between">
                        <h4 class="card-title">Entregas del día</h4>
                    </div>
                    <div class="d-flex justify-content-center align-items-center" style="height: 500px">
                        <?php include $mapaRutaVentas; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-lg-4 col-lg-12 grid-margin stretch-card">
            <div class="card card-rounded" style="border-top: 4px solid #f69100;">
                <div class="card-body">
                    <div class="d-sm-flex justify-content-between">
                        <h4 class="card-title">Ventas de la semana</h4>
                        <div id="performance-line-legend"></div>
                    </div>
                    <div class="chartjs-wrapper mt-5">
                        <canvas id="ventasSemana"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-lg-6 grid-margin stretch-card">
            <div class="card card-rounded" style="border-top: 4px solid #5468ff;">
                <div class="card-body">
                    <div>
                        <h6 class="card-title">Ventas móvil y punto de venta <i class='bx bxs-info-circle fs-5 mx-2' style="color: #000" title='Los resultados corresponden a los últimos 30 días'></i></h6>
                    </div>
                    <div class="d-flex justify-content-center gap-0">
                        <div class="col-6 text-center" style="border-right: 2px solid #eee;">
                            <span style="font-size: 50px; font-weight: 600; color: #f6d365;"> <?php echo $ventas_mostrador ?> </span>
                            <p>Punto de venta</p>
                        </div>
                        <div class="col-6 text-center" style="border-left: 2px solid #eee;">
                            <span style="font-size: 50px; font-weight: 600; color: #a6c1ee;"> <?php echo $ventas_web ?> </span>
                            <p>Móvil</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6 grid-margin stretch-card">
            <div class="card card-rounded" style="border-top: 4px solid #FFD94D;">
                <div class="card-body">
                    <div>
                        <h6 class="card-title">Compras vs Ventas <i class='bx bxs-info-circle fs-5 mx-2' style="color: #000" title='Los resultados corresponden a los últimos 30 días'></i></h6>
                        <div class="d-flex justify-content-center gap-0">
                            <div class="col-6 text-center" style="border-right: 2px solid #eee;">
                                <span style="font-size: 50px; font-weight: 600; color: #f6d365;"> <?php echo $total_compras ?> </span>
                                <p>Compras</p>
                            </div>
                            <div class="col-6 text-center" style="border-left: 2px solid #eee;">
                                <span style="font-size: 50px; font-weight: 600; color: #a6c1ee;"> <?php echo $total_ventas ?> </span>
                                <p>Ventas</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-lg-12 grid-margin stretch-card">
            <div class="card card-rounded" style="border-top: 4px solid #16A34A;">
                <div class="card-body">
                    <div>
                        <h6 class="card-title">Ventas por sucursal <i class='bx bxs-info-circle fs-5 mx-2' style="color: #000" title='Los resultados corresponden a los últimos 30 días'></i></h6>
                    </div>
                    <div class="table-responsive overflow-hidden">
                        <table id='dtSucursales' class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#ID</th>
                                    <th>Sucursal</th>
                                    <th>Ventas totales</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (!$rventassucursal = $mysqli->query($qventassucursal)) {
                                    echo "Lo sentimos, esta aplicación está experimentando problemas.";
                                    exit;
                                }
                                while ($rowventassucursal = $rventassucursal->fetch_assoc()) {
                                    echo <<<HTML
                                        <tr class="odd gradeX">
                                            <td>$rowventassucursal[pk_sucursal]</td>
                                            <td>$rowventassucursal[nombre]</td>
                                            <td>$rowventassucursal[total_ventas]</td>
                                        </tr>
                                    HTML;
                                }
                                ?>
                            </tbody>
                        </table>
                        <div class="row\">&nbsp;</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-lg-12 grid-margin stretch-card">
            <div class="card card-rounded" style="border-top: 4px solid #d08bf0;">
                <div class="card-body">
                    <div>
                        <h6 class="card-title">Top 10 clientes <i class='bx bxs-info-circle fs-5 mx-2' style="color: #000" title='Los resultados corresponden a los últimos 30 días'></i></h6>
                    </div>
                    <div class="table-responsive overflow-hidden">
                        <table id='dtClientes' class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#ID</th>
                                    <th>Cliente</th>
                                    <th>Total de compras</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                echo "";
                                if (!$rtopclientes = $mysqli->query($qtopclientes)) {
                                    echo "Lo sentimos, esta aplicación está experimentando problemas.";
                                    exit;
                                }
                                while ($rowtopclientes = $rtopclientes->fetch_assoc()) {
                                    echo <<<HTML
                                        <tr class="odd gradeX">
                                            <td>$rowtopclientes[pk_cliente]</td>
                                            <td>$rowtopclientes[nombre]</td>
                                            <td>$rowtopclientes[total_compras]</td>
                                        </tr>
                                    HTML;
                                }
                                ?>
                            </tbody>
                        </table>
                        <div class="row">&nbsp;</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-lg-12 grid-margin stretch-card">
            <div class="card card-rounded" style="border-top: 4px solid #16A34A;">
                <div class="card-body">
                    <div>
                        <h6 class="card-title">Top 10 productos más vendidos <i class='bx bxs-info-circle fs-5 mx-2' style="color: #000" title='Los resultados corresponden a los últimos 30 días'></i></h6>
                    </div>
                    <div class="table-responsive overflow-hidden">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#ID</th>
                                    <th>Producto</th>
                                    <th>Total vendido</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (!$rtopproductos = $mysqli->query($qtopproductos)) {
                                    echo "Lo sentimos, esta aplicación está experimentando problemas.";
                                    exit;
                                }
                                while ($rowtopproductos = $rtopproductos->fetch_assoc()) {
                                    echo <<<HTML
                                        <tr class="odd gradeX">
                                            <td>$rowtopproductos[pk_producto]</td>
                                            <td>$rowtopproductos[nombre]</td>
                                            <td>$rowtopproductos[total_vendido]</td>
                                        </tr>
                                    HTML;
                                }
                                ?>
                            </tbody>
                        </table>
                        <div class="row">&nbsp;</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!--ALERTAS-->
<div class="col-lg-4 col-12 d-flex flex-column" id="ventas_dia">
    <div class="row">
        <div class="card card-rounded" style="padding: 0;">
            <div class="card-body" style="padding: 0;">
                <div style="background-color: #ccc; padding: 18px 24px; border-radius: 15px 15px 0 0;">
                    <h3 class="fs-2 fw-bold"><i class="bx bxs-error-alt fs-2 mx-2" style="color: #000;"></i>Alertas</h3>
                    <p>Procesos que requieren atención</p>
                </div>

                <div>
                    <a href="verVentas.php" class="d-flex justify-content-between card-alert"><span>Créditos a vencer</span>
                        <p class="badge-primary-integra">En desarrollo</p>
                    </a>
                    <a href="verCompras.php" class="d-flex justify-content-between card-alert"><span>Créditos a pagar</span>
                        <p class="badge-success-integra">En desarrollo</p>
                    </a>
                    <a href="verExistencias.php" class="d-flex justify-content-between card-alert"><span>Productos en mínimo</span>
                        <p class="badge-danger-integra">En desarrollo</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
