<?php

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

?>

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

<script></script>
