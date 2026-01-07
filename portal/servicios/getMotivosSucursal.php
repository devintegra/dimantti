<?php
header("Access-Control-Allow-Origin: *");


include("conexioni.php");
mysqli_set_charset($mysqli, 'utf8');

if (isset($_GET['fk_sucursal']) && is_numeric($_GET['fk_sucursal'])) {
    $fk_sucursal = (int)$_GET['fk_sucursal'];
}


$qmotivos = "select rt_sucursales_motivos.*,
    ct_retiros.*
    from rt_sucursales_motivos, ct_retiros
    where rt_sucursales_motivos.fk_sucursal = $fk_sucursal
    and rt_sucursales_motivos.estado = 1
    and ct_retiros.pk_retiro = rt_sucursales_motivos.fk_retiro";


echo "<option value='0'>Seleccione</option>";

if (!$rmotivos = $mysqli->query($qmotivos)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

while ($motivos = $rmotivos->fetch_assoc()) {

    echo "<option value='$motivos[fk_retiro]-$motivos[variable]-$motivos[cantidad]'>$motivos[nombre]</option>";

}
