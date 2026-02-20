<?php

$qordenes = "SELECT ct_sucursales.iniciales as iniciales,
  tr_ordenes_detalle.nombre as nombre,
  tr_ordenes.pk_orden as id,
  tr_ordenes.espera as espera,
  tr_ordenes.folio as folio,
  tr_ordenes.reabierta as reabierta,
  tr_ordenes.fk_venta as fk_venta,
  tr_ordenes.fecha as fecha,
  tr_ordenes.estatus as estatus,
  tr_ordenes.fk_tecnico as tecnico,
  ct_clientes.telefono as telefono,
  ct_clientes.nombre as cliente
  FROM tr_ordenes, ct_clientes, ct_sucursales, tr_ordenes_detalle
  WHERE tr_ordenes.fk_cliente = ct_clientes.pk_cliente
  AND tr_ordenes.fk_sucursal = ct_sucursales.pk_sucursal
  AND tr_ordenes.estado = 1
  AND tr_ordenes.estatus = 4
  AND tr_ordenes.pk_orden = tr_ordenes_detalle.fk_orden
  ORDER BY tr_ordenes.pk_orden DESC";

if (!$rordenes = $mysqli->query($qordenes)) {
    echo "<br>Lo sentimos, esta aplicación está experimentando problemas.2";
    exit;
}

?>


<div class="col-12 col-lg-12 grid-margin stretch-card">
    <div class="card card-rounded" style="border-top: 4px solid #f6d365;">
        <div class="card-body">
            <div>
                <h6 class="card-title">Entregas pendientes</h6>
                <div class="table-responsive overflow-hidden">
                    <table id='dtOrdenes' class="table table-striped">
                        <thead>
                            <tr>
                                <th>Folio</th>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Descripción</th>
                                <th>Técnico</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            while ($rowordenes = $rordenes->fetch_assoc()) {
                                echo <<<HTML
                                    <tr class="odd gradeX">
                                        <td>$rowordenes[folio]</td>
                                        <td>$rowordenes[fecha]</td>
                                        <td>$rowordenes[cliente]</td>
                                        <td style='white-space: normal;'>$rowordenes[nombre]</td>
                                        <td>$rowordenes[tecnico]</td>
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
