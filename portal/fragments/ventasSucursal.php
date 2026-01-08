<?php

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

?>

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

<script></script>
