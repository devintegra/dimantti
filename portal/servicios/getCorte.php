<?php
header("Access-Control-Allow-Origin: *");
include("conexioni.php");


if (isset($_GET['fk_sucursal']) && is_numeric($_GET['fk_sucursal'])) {
    $fk_sucursal = (int)$_GET['fk_sucursal'];
}

if (isset($_GET['fk_usuario']) && is_string($_GET['fk_usuario'])) {
    $fk_usuario = $_GET['fk_usuario'];
}

if (isset($_GET['tipo']) && is_numeric($_GET['tipo'])) {
    $tipo = (int)$_GET['tipo'];
}

if (isset($_GET['comision']) && is_numeric($_GET['comision'])) {
    $comision = (int)$_GET['comision'];
}

if (isset($_GET['nivel']) && is_numeric($_GET['nivel'])) {
    $nivel = (int)$_GET['nivel'];
}

$tipo == 1 ? $tipo_corte = 'Ventas' : $tipo_corte = 'Reparación';


$flsucursal_saldo_inicial = "";


//SUCURSAL
#region
if ($fk_sucursal != 0) {
    $qsucursal = "SELECT * FROM ct_sucursales WHERE pk_sucursal = $fk_sucursal";

    if (!$rsucursal = $mysqli->query($qsucursal)) {
        echo "Lo sentimos, esta aplicación está experimentando problemas. 1";
        exit;
    }

    $empresa = $rsucursal->fetch_assoc();
    $empresa_nombre = $empresa["nombre"];

    $flsucursal_saldo_inicial = " AND fk_sucursal = $fk_sucursal";
} else {
    $empresa_nombre = 'Todas las sucursales';
}
#endregion




//ENCABEZADO
#region
$parrafo_uno = <<<HTML
        <div style='display: flex; justify-content: center; align-items: center; flex-direction: column;'>
            <img src='servicios/logo.png' style='width: 50%; margin-bottom: 5px;'>
            <div style='display: flex; justify-content: center; align-items: center; gap: 10px; margin-bottom: 12px;'>
                <h4><i class='bx bx-user-circle' ></i> $fk_usuario</h4>
                <h4><i class='bx bx-cart-alt' ></i> $tipo_corte</h4>
            </div>
        </div>
    HTML;
#endregion


$total_entrada = 0;
$total_comision = 0;
$total_salida = 0;


//EFECTIVO
#region
$parrafo_efectivo_titulo = <<<HTML
        <div>
            <div style='background-color: #000; color: #fff; text-align: center; padding: 5px; margin-bottom: 12px; display: flex; justify-content: center; align-items: center; gap: 8px;'>
                <h4 style='margin-bottom: 0px;'> <i class='bx bx-coin'></i> EFECTIVO</h4>
            </div>

    HTML;

//SALDO INCIAL
$qsaldos = "SELECT 1 as tipo, '>> SALDO INICIAL' as titulo, IFNULL(SUM(saldo),0) as monto
	FROM tr_saldos_iniciales
    WHERE fk_corte = 0 AND fk_usuario = '$fk_usuario' AND estado = 1$flsucursal_saldo_inicial";

$mysqli->next_result();
if (!$rsaldos = $mysqli->query($qsaldos)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. 2";
    exit;
}

$parrafo_efectivo = '';
while ($rowsaldos = $rsaldos->fetch_assoc()) {
    $monto = number_format($rowsaldos['monto'], 2);
    $textColor = '#2a952a';
    $total_entrada += $rowsaldos['monto'];
    $parrafo_efectivo .= "
        <div style='display: flex; justify-content: space-between; align-items: center;'>
            <h4>$rowsaldos[titulo]</h4>
            <h4 style='font-weight: bold; color:$textColor;'>$$monto</h4>
        </div>
    ";
}

//DEMÁS ABONOS
$mysqli->next_result();
if (!$result = $mysqli->query("CALL get_corte_totales(0, 1, '$fk_usuario', $tipo, $fk_sucursal, $nivel)")) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. 3" . $mysqli->error;
    exit;
}

while ($row = $result->fetch_assoc()) {
    $monto = number_format($row['monto'], 2);
    if ($row['tipo'] == 1) {
        $textColor = '#2a952a';
        $total_entrada += $row['monto'];
    } else {
        $textColor = '#cf3737';
        $total_salida += $row['monto'];
    }
    $total_comision += $row['comision'];
    $parrafo_efectivo .= "
        <div style='display: flex; justify-content: space-between; align-items: center;'>
            <h4>$row[titulo]</h4>
            <h4 style='font-weight: bold; color:$textColor;'>$$monto</h4>
        </div>
    ";
}

$parrafo_efectivo_cierre = <<<HTML
        </div>
    HTML;
#endregion



//TRANSFERENCIA
#region
$parrafo_transferencia_titulo = <<<HTML
        <div>
            <div style='background-color: #000; color: #fff; text-align: center; padding: 5px; margin-bottom: 12px; display: flex; justify-content: center; align-items: center; gap: 8px;'>
                <h4 style='margin-bottom: 0px;'> <i class='bx bx-transfer-alt'></i> TRANSFERENCIA</h4>
            </div>

    HTML;

$mysqli->next_result();
if (!$result = $mysqli->query("CALL get_corte_totales(0, 2, '$fk_usuario', $tipo, $fk_sucursal, $nivel)")) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. 4";
    exit;
}

$parrafo_transferencia = '';
while ($row = $result->fetch_assoc()) {
    $monto = number_format($row['monto'], 2);
    if ($row['tipo'] == 1) {
        $textColor = '#2a952a';
        $total_entrada += $row['monto'];
    } else {
        $textColor = '#cf3737';
        $total_salida += $row['monto'];
    }
    $total_comision += $row['comision'];
    $parrafo_transferencia .= "
        <div style='display: flex; justify-content: space-between; align-items: center;'>
            <h4>$row[titulo]</h4>
            <h4 style='font-weight: bold; color:$textColor;'>$$monto</h4>
        </div>
    ";
}

$parrafo_transferencia_cierre = <<<HTML
        </div>
    HTML;
#endregion



//DEBITO
#region
$parrafo_debito_titulo = <<<HTML
        <div>
            <div style='background-color: #000; color: #fff; text-align: center; padding: 5px; margin-bottom: 12px; display: flex; justify-content: center; align-items: center; gap: 8px;'>
                <h4 style='margin-bottom: 0px;'> <i class='bx bx-credit-card'></i> TAR.DEBITO</h4>
            </div>

    HTML;

$mysqli->next_result();
if (!$result = $mysqli->query("CALL get_corte_totales(0, 3, '$fk_usuario', $tipo, $fk_sucursal, $nivel)")) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. 5";
    exit;
}

$parrafo_debito = '';
while ($row = $result->fetch_assoc()) {
    $monto = number_format($row['monto'], 2);
    if ($row['tipo'] == 1) {
        $textColor = '#2a952a';
        $total_entrada += $row['monto'];
    } else {
        $textColor = '#cf3737';
        $total_salida += $row['monto'];
    }
    $total_comision += $row['comision'];
    $parrafo_debito .= "
        <div style='display: flex; justify-content: space-between; align-items: center;'>
            <h4>$row[titulo]</h4>
            <h4 style='font-weight: bold; color:$textColor;'>$$monto</h4>
        </div>
    ";
}

$parrafo_debito_cierre = <<<HTML
        </div>
    HTML;
#endregion



//CREDITO
#region
$parrafo_credito_titulo = <<<HTML
        <div>
            <div style='background-color: #000; color: #fff; text-align: center; padding: 5px; margin-bottom: 12px; display: flex; justify-content: center; align-items: center; gap: 8px;'>
                <h4 style='margin-bottom: 0px;'> <i class='bx bx-credit-card-alt'></i> TAR.CREDITO</h4>
            </div>

    HTML;

$mysqli->next_result();
if (!$result = $mysqli->query("CALL get_corte_totales(0, 5, '$fk_usuario', $tipo, $fk_sucursal, $nivel)")) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. 6";
    exit;
}

$parrafo_credito = '';
while ($row = $result->fetch_assoc()) {
    $monto = number_format($row['monto'], 2);
    if ($row['tipo'] == 1) {
        $textColor = '#2a952a';
        $total_entrada += $row['monto'];
    } else {
        $textColor = '#cf3737';
        $total_salida += $row['monto'];
    }
    $total_comision += $row['comision'];
    $parrafo_credito .= "
        <div style='display: flex; justify-content: space-between; align-items: center;'>
            <h4>$row[titulo]</h4>
            <h4 style='font-weight: bold; color:$textColor;'>$$monto</h4>
        </div>
    ";
}

$parrafo_credito_cierre = <<<HTML
        </div>
    HTML;
#endregion



//CHEQUE
#region
$parrafo_cheque_titulo = <<<HTML
        <div>
            <div style='background-color: #000; color: #fff; text-align: center; padding: 5px; margin-bottom: 12px; display: flex; justify-content: center; align-items: center; gap: 8px;'>
                <h4 style='margin-bottom: 0px;'> <i class='bx bx-edit'></i> CHEQUE</h4>
            </div>

    HTML;

$mysqli->next_result();
if (!$result = $mysqli->query("CALL get_corte_totales(0, 4, '$fk_usuario', $tipo, $fk_sucursal, $nivel)")) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. 7";
    exit;
}

$parrafo_cheque = '';
while ($row = $result->fetch_assoc()) {
    $monto = number_format($row['monto'], 2);
    if ($row['tipo'] == 1) {
        $textColor = '#2a952a';
        $total_entrada += $row['monto'];
    } else {
        $textColor = '#cf3737';
        $total_salida += $row['monto'];
    }
    $total_comision += $row['comision'];
    $parrafo_cheque .= "
        <div style='display: flex; justify-content: space-between; align-items: center;'>
            <h4>$row[titulo]</h4>
            <h4 style='font-weight: bold; color:$textColor;'>$$monto</h4>
        </div>
    ";
}

$parrafo_cheque_cierre = <<<HTML
        </div>
    HTML;
#endregion



//COMISION
#region
if ($comision == 0) {
    $parrafo_comision = '';
} else {
    $total_comision = number_format($total_comision, 2);
    $parrafo_comision = <<<HTML
            <div>
                <div style='background-color: #000; color: #fff; text-align: center; padding: 5px; margin-bottom: 12px; display: flex; justify-content: center; align-items: center; gap: 8px;'>
                    <h4 style='margin-bottom: 0px;'> <i class='bx bx-money'></i> COMISION</h4>
                </div>
                <div style='display: flex; justify-content: space-between; align-items: center;'>
                    <h4></h4>
                    <h4 style='font-weight: bold; color:#000;'>$$total_comision</h4>
                </div>
            </div>
        HTML;
}
#endregion


if ($comision == 0) {
    $total_final = number_format($total_entrada - $total_salida, 2);
} else {
    $total_final = number_format($total_entrada + $total_comision - $total_salida, 2);
}


//TOTALES
#region
$total_entrada = number_format($total_entrada, 2);
$comision == 0 ? $total_comision = '' : $total_comision = '$' . number_format($total_comision, 2);
$total_salida = number_format($total_salida, 2);

$parrafo_totales = <<<HTML
        <div style='display: flex; justify-content: end; align-items: end; flex-direction: column; margin-top: 20px;'>
            <div style='width: 100%; height: 2px; background-color: #000; margin-bottom: 2px;'></div>
            <div style='width: 100%; height: 2px; background-color: #000; margin-bottom: 6px;'></div>
            <h4 style='font-weight: bold; color:#2a952a;' id='total_entrada'>$$total_entrada</h4>
            <h4 style='font-weight: bold; color:#000;' id='total_comision'>$total_comision</h4>
            <h4 style='font-weight: bold; color:#cf3737;' id='total_salida'>-$$total_salida</h4>
            <div style='width: 100%; display: flex; justify-content: space-between; align-items: center;'>
                <h3 style='font-weight: bold;'>TOTAL:</h3>
                <h3 style='font-weight: bold; color:#2563eb;'>$$total_final</h3>
            </div>
        </div>
    HTML;
#endregion



//ACCIONES
#region
$parrafo_acciones = <<<HTML
        <div style='display: flex; justify-content: center; align-items: center; gap: 10px; margin-top: 20px;'>
            <button id="generar" type="button" class="btn btn-primary-dast mx-2"><i class="fa fa-save mx-2"></i>Generar Corte</button>
        </div>
    HTML;
#endregion



echo $parrafo_uno
    . $parrafo_efectivo_titulo
    . $parrafo_efectivo
    . $parrafo_efectivo_cierre
    . $parrafo_transferencia_titulo
    . $parrafo_transferencia
    . $parrafo_transferencia_cierre
    . $parrafo_debito_titulo
    . $parrafo_debito
    . $parrafo_debito_cierre
    . $parrafo_credito_titulo
    . $parrafo_credito
    . $parrafo_credito_cierre
    . $parrafo_cheque_titulo
    . $parrafo_cheque
    . $parrafo_cheque_cierre
    . $parrafo_comision
    . $parrafo_totales
    . $parrafo_acciones;
