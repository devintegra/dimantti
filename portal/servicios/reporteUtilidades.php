<?php
require_once "Classes/PHPExcel.php";
require "Classes/PHPExcel/Writer/Excel2007.php";
include("conexioni.php");
mysqli_set_charset($mysqli, 'utf8');

if (isset($_GET['inicio']) && is_string($_GET['inicio'])) {
    $inicio = $_GET['inicio'];
}

if (isset($_GET['fin']) && is_string($_GET['fin'])) {
    $fin = $_GET['fin'];
}

if (isset($_GET['sucursal']) && is_numeric($_GET['sucursal'])) {
    $sucursal = (int)$_GET['sucursal'];
}

if (isset($_GET['cliente']) && is_numeric($_GET['cliente'])) {
    $cliente = (int)$_GET['cliente'];
}

if (isset($_GET['vendedor']) && is_string($_GET['vendedor'])) {
    $fk_usuario = $_GET['vendedor'];
}

if (isset($_GET['pago']) && is_numeric($_GET['pago'])) {
    $pago = (int)$_GET['pago'];
}

if (isset($_GET['tipo']) && is_numeric($_GET['tipo'])) {
    $tipo = (int)$_GET['tipo'];
}


//FILTROS
#region
$flfechas = "";
$flsucursal = "";
$flcliente = "";
$flusuario = "";
$flpago = "";
$rpfechas = "";
$rpsucursal = "";
$rpcliente = "";
$rpusuario = "";
$rppago = "";

if ($inicio != "" && $fin != "") {
    $flfechas = " AND tr_ventas.fecha BETWEEN '$inicio' AND '$fin'";
    $rpfechas = " AND tr_ordenes.fecha BETWEEN '$inicio' AND '$fin'";
}

if ($sucursal != 0) {
    $flsucursal = " AND tr_ventas.fk_sucursal = $sucursal";
    $rpsucursal = " AND tr_ordenes.fk_sucursal = $sucursal";
}

if ($cliente != 0) {
    $flcliente = " AND tr_ventas.fk_cliente = $cliente";
    $rpcliente = " AND tr_ordenes.fk_cliente = $cliente";
}

if ($fk_usuario != '0') {
    $flusuario = " AND tr_ventas.fk_usuario = '$fk_usuario'";
    $rpusuario = " AND tr_ordenes.fk_usuario = '$fk_usuario'";
}

if ($pago != 0) {

    switch ($pago) {
        case 1:
            $metodo = "Efectivo. ";
            $flpago = " AND tr_ventas.efectivo > 0";
            break;
        case 2:
            $metodo = "Trans. ";
            $flpago = " AND tr_ventas.transferencia > 0";
            break;
        case 3:
            $metodo = "Debito. ";
            $flpago = " AND tr_ventas.debito > 0";
            break;
        case 4:
            $metodo = "Cheque. ";
            $flpago = " AND tr_ventas.cheque > 0";
            break;
        case 5:
            $metodo = "Credito. ";
            $flpago = " AND tr_ventas.credito > 0";
            break;
    }
}
#endregion



$paso = 4;
// Crea un nuevo objeto PHPExcel
$objPHPExcel = new PHPExcel();

// Establecer propiedades
$objPHPExcel->getProperties()
    ->setCreator("Integra Connective")
    ->setLastModifiedBy("Integra Connective")
    ->setTitle("Reporte Ventas")
    ->setSubject("Ventas")
    ->setDescription("Ventas")
    ->setKeywords("Ventas")
    ->setCategory("Ventas");


// Agregar Informacion
$objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue('A3', 'Fecha')
    ->setCellValue('B3', 'ID Venta')
    ->setCellValue('C3', 'Tipo')
    ->setCellValue('D3', 'Sucursal')
    ->setCellValue('E3', 'Cliente')
    ->setCellValue('F3', 'Forma de pago')
    ->setCellValue('G3', 'Vendedor')
    ->setCellValue('H3', 'Total')
    ->setCellValue('I3', 'Utilidad')
    ->setCellValue('J3', 'Estatus');



$styleArrayTitle = array(
    'font'  => array(
        'bold'  => true,
        'color' => array('rgb' => 'ff7a21'),
        'size'  => 12,
        'name'  => 'Calibri'
    )
);


$styleArrayHeaders = array(
    'font'  => array(
        'bold'  => true,
        'color' => array('rgb' => 'ffffff'),
        'size'  => 12,
        'name'  => 'Calibri'
    )
);


$styleArrayHeadersTable = array(
    'font'  => array(
        'bold'  => true,
        'color' => array('rgb' => 'FFFFFF'),
        'size'  => 12,
        'name'  => 'Calibri'
    )
);

$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArrayTitle);
$objPHPExcel->getActiveSheet()->getStyle('A2:A3')->applyFromArray($styleArrayHeaders);


$objPHPExcel->getActiveSheet()->setCellValue('A1', "POSMOVIL. Integra Connective");


$objPHPExcel->getActiveSheet()->getStyle('A3:J3')->applyFromArray($styleArrayHeaders);
$objPHPExcel->getActiveSheet()->getStyle('A3:J3')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('000000');


$total_final = 0.00;
$total_utilidad = 0.00;


//VENTAS
if ($tipo == 0 || $tipo == 1) {

    $qventas = "SELECT tr_ventas.pk_venta,
        tr_ventas.fecha,
        tr_ventas.hora,
        ct_sucursales.nombre as sucursal,
        ct_clientes.nombre as cliente,
        tr_ventas.fk_usuario,
        tr_ventas.total,
        tr_ventas.estatus
        FROM tr_ventas, ct_sucursales, ct_clientes
        WHERE tr_ventas.tipo IN(1,2,3,4)
        AND tr_ventas.fecha BETWEEN '$inicio' AND '$fin'
        AND ct_sucursales.pk_sucursal = tr_ventas.fk_sucursal
        AND ct_clientes.pk_cliente = tr_ventas.fk_cliente$flsucursal $flcliente $flusuario $flpago";

    if (!$rventas = $mysqli->query($qventas)) {
        echo "Lo sentimos, esta aplicación está experimentando problemas.1";
        exit;
    }

    while ($row = $rventas->fetch_assoc()) {

        $total_venta = 0;
        $total_costo_productos = 0;

        //SABER QUE TIPO DE DEVOLUCION/CANCELACION FUE
        #region
        if ($row['estatus'] == 2 || $row['estatus'] == 3) {
            $qtipo = "SELECT * FROM tr_devoluciones WHERE fk_venta = $row[pk_venta] AND estado = 1";

            if (!$rtipo = $mysqli->query($qtipo)) {
                echo "<br>Lo sentimos, esta aplicación está experimentando problemas.2";
                exit;
            }

            $tipod = $rtipo->fetch_assoc();
            $tipo_devolucion = $tipod["tipo"]; //1 -> Con dinero,  2 -> Sin dinero
        }
        #endregion


        //MÉTODO DE PAGO
        #region
        $qpago = "SELECT CONCAT(
            CASE WHEN efectivo > 0 THEN 'Efectivo.' ELSE '' END,
            CASE WHEN credito > 0 THEN 'Tarjeta Crédito. ' ELSE '' END,
            CASE WHEN debito > 0 THEN 'Tarjeta de Debito. ' ELSE '' END,
            CASE WHEN cheque > 0 THEN 'Cheque. ' ELSE '' END,
            CASE WHEN transferencia > 0 THEN 'Transferencia. ' ELSE '' END,
            CASE WHEN efectivo = 0 AND
                        credito = 0 AND
                        debito = 0 AND
                        cheque = 0 AND
                        transferencia = 0
                    THEN 'Venta a crédito' ELSE '' END
        ) AS campos_cumplen
        FROM tr_ventas
        WHERE pk_venta = $row[pk_venta]";

        if (!$rpago = $mysqli->query($qpago)) {
            echo "<br>Lo sentimos, esta aplicación está experimentando problemas.";
            exit;
        }

        $rowpago = $rpago->fetch_assoc();
        $npago = $rowpago["campos_cumplen"];
        #endregion


        //DEVOLUCION
        if ($row['estatus'] == 2) {
            $estatus = "Devuelta";
            $nestatus = "badge-warning-integra";
            $objPHPExcel->getActiveSheet()->getStyle('J' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFD94D');

            if ($tipo_devolucion == 1) {
                $qproductos = "SELECT * FROM tr_ventas_detalle WHERE fk_venta = $row[pk_venta] AND devuelto = 0 AND estado = 1";
            }

            if ($tipo_devolucion == 2) {
                $qproductos = "SELECT * FROM tr_ventas_detalle WHERE fk_venta = $row[pk_venta] AND estado = 1";
            }
        }


        //CANCELACION
        if ($row['estatus'] == 3) {
            $estatus = "Cancelada";
            $nestatus = "badge-danger-integra";
            $objPHPExcel->getActiveSheet()->getStyle('J' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('DC2626');

            if ($tipo_devolucion == 1) {
                $total_costo_productos = $row['total'];
            }

            if ($tipo_devolucion == 2) {
                $qproductos = "SELECT * FROM tr_ventas_detalle WHERE fk_venta = $row[pk_venta] AND estado = 1";
            }
        }


        //VENTA NORMAL
        if ($row['estatus'] == 1) {
            $estatus = "Registrada";
            $nestatus = "badge-primary-integra";
            $objPHPExcel->getActiveSheet()->getStyle('J' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('2563EB');
            $qproductos = "SELECT * FROM tr_ventas_detalle WHERE fk_venta = $row[pk_venta] AND estado = 1";
        }


        //EJECUTAR CONSULTA
        if ($qproductos) {
            if (!$rproductos = $mysqli->query($qproductos)) {
                echo "<br>Lo sentimos, esta aplicación está experimentando problemas.3" . $mysqli->error;
                exit;
            }

            while ($rowproducto = $rproductos->fetch_assoc()) {

                $total_venta += $rowproducto['total'];

                $qcostos = "SELECT * FROM ct_productos WHERE pk_producto = $rowproducto[fk_producto] AND estado = 1";

                if (!$rcostos = $mysqli->query($qcostos)) {
                    echo "<br>Lo sentimos, esta aplicación está experimentando problemas.4";
                    exit;
                }

                $costos = $rcostos->fetch_assoc();
                $total_costo_productos += ($costos["costo"] * $rowproducto['cantidad']);
            }
        }


        $utilidad = $total_venta - $total_costo_productos;


        $objPHPExcel->getActiveSheet()->setCellValue('A' . $paso, $row["fecha"] . " " . $row["hora"]);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $paso, $row["pk_venta"]);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $paso, 'VENTA');
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $paso, $row["sucursal"]);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $paso, $row["cliente"]);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $paso, $npago);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $paso, $row["fk_usuario"]);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . $paso, "$" . number_format($total_venta, 2));
        $objPHPExcel->getActiveSheet()->setCellValue('I' . $paso, "$" . number_format($utilidad, 2));
        $objPHPExcel->getActiveSheet()->setCellValue('J' . $paso, $estatus);

        $total_final += $total_venta;
        $total_utilidad += $utilidad;

        $paso++;
    }
}



$objPHPExcel->getActiveSheet()->getStyle('H1:I' . $paso)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

$objPHPExcel->getActiveSheet()->getStyle('H' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('BFDBFE');
$objPHPExcel->getActiveSheet()->getStyle('I' . $paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('BBF7B0');

$objPHPExcel->getActiveSheet()->setCellValue('H' . $paso, "$" . number_format($total_final, 2));
$objPHPExcel->getActiveSheet()->setCellValue('I' . $paso, "$" . number_format($total_utilidad, 2));



$objPHPExcel->getActiveSheet()->setTitle('Reporte utilidad');


$objPHPExcel->setActiveSheetIndex(0);


foreach (range('A', 'J') as $columnID) {
    $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
        ->setAutoSize(true);
}

$objPHPExcel->getActiveSheet()->getStyle('A1:F' . $paso)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);


// Se modifican los encabezados del HTTP para indicar que se envia un archivo de Excel.
// Redirect output to a client’s web browser (Excel5)

$ahora = new DateTime();
$id_file = "reporte_utilidades_" . $ahora->getTimestamp() . ".xls";





header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment;filename="' . $id_file . '"');
header('Cache-Control: max-age=0');



$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
