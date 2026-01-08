<?php

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

?>

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

<script src=""></script>
