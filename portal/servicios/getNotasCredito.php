<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");
mysqli_set_charset($mysqli, 'utf8');

if (isset($_GET['fk_cliente']) && is_numeric($_GET['fk_cliente'])) {
    $fk_cliente = (int)$_GET['fk_cliente'];
}

if (isset($_GET['total']) && is_numeric($_GET['total'])) {
    $total = (float)$_GET['total'];
}


$qcredito = "SELECT * FROM tr_devoluciones WHERE fk_cliente = $fk_cliente AND saldo > 0 AND saldo <= $total AND estado=1";


if (!$rcredito = $mysqli->query($qcredito)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

if ($rcredito->num_rows > 0) {

    echo <<<HTML
        <label class='form-label'>Notas de crédito</label>
        <select class='form-control' id='nota_credito'>
            <option value='0'>Seleccione</option>
    HTML;

    while ($credito = $rcredito->fetch_assoc()) {
        echo <<<HTML
            <option value='$credito[pk_devolucion]'>$$credito[saldo]</option>
        HTML;
    }

    echo <<<HTML
        </select>
        <p class='badge-success-integra my-2'>El cliente tiene notas de crédito disponibles</p>
    HTML;
}
