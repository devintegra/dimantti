<?php

$ventasSemana = "fragments/ventasSemana.php";
$ventasCompras = "fragments/ventasCompras.php";
$ventasSucursal = "fragments/ventasSucursal.php";
$topClientes = "fragments/topClientes.php";
$topProductos = "fragments/topProductos.php";

?>

<!--ESTADISTICAS-->
<div class="col-lg-8 col-12 d-flex flex-column" id="ventas_dia">
    <div class="row">
        <?php
        include $ventasSemana;
        include $ventasCompras;
        include $ventasSucursal;
        include $topClientes;
        include $topProductos;
        ?>
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
