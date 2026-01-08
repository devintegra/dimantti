<?php

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

?>

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

<script></script>
