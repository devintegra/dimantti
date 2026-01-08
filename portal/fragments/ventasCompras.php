<?php

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

?>

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

<script></script>
