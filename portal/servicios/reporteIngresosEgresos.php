<?php

    header("Access-Control-Allow-Origin: *");
    require_once "Classes/PHPExcel.php";
    require "Classes/PHPExcel/Writer/Excel2007.php";
    include("conexioni.php");
    mysqli_set_charset( $mysqli, 'utf8' );

 

if (isset($_GET['inicio']) && is_string($_GET['inicio'])) {
    $inicio = $_GET['inicio'];
}
 

if (isset($_GET['fin']) && is_string($_GET['fin'])) {
    $fin = $_GET['fin'];
}

if (isset($_GET['sucursal']) && is_numeric($_GET['sucursal'])) {
    $fk_sucursal = (int)$_GET['sucursal'];
}

if (isset($_GET['tipo']) && is_numeric($_GET['tipo'])) {
    $tipo = (int)$_GET['tipo'];
}


$filtro="";
$filtro_retiro="";

if ($fk_sucursal!=0)
{
    $filtro=" and tr_abonos.fk_sucursal=$fk_sucursal";
    $filtro_retiro=" and tr_retiros.fk_sucursal=$fk_sucursal";
}


 $qventaso="select ct_sucursales.nombre as sucursal, tr_abonos.monto as cantidad, tr_abonos.fecha as fecha, tr_abonos.fk_pago as pago, ct_clientes.nombre as cliente, tr_ordenes.folio as folio from ct_sucursales, tr_abonos, tr_ordenes, ct_clientes where tr_abonos.tipo=1 and tr_abonos.fk_sucursal=ct_sucursales.pk_sucursal and tr_abonos.fk_factura=tr_ordenes.pk_orden and tr_ordenes.fk_cliente=ct_clientes.pk_cliente and tr_abonos.monto>0$filtro and tr_abonos.fecha between '$inicio' and '$fin'";
 
 $qventasd="select ct_sucursales.nombre as sucursal, tr_abonos.monto as cantidad, tr_abonos.fecha as fecha, tr_abonos.fk_pago as pago, ct_clientes.nombre as cliente, tr_ventas.folio as folio from ct_sucursales, tr_abonos, tr_ventas, ct_clientes where tr_abonos.tipo=2 and tr_abonos.fk_sucursal=ct_sucursales.pk_sucursal and tr_abonos.fk_factura=tr_ventas.pk_venta and tr_ventas.fk_cliente=ct_clientes.pk_cliente and tr_ventas.tipo=1 and tr_abonos.monto>0$filtro and tr_abonos.fecha between '$inicio' and '$fin'";

 $qretiros = "select ct_sucursales.nombre as sucursal, ct_retiros.nombre as motivo, tr_retiros.descripcion as descripcion, tr_retiros.fecha as fecha, tr_retiros.monto as cantidad, tr_retiros.fk_pago as pago from ct_sucursales, ct_retiros, tr_retiros where tr_retiros.fk_sucursal=ct_sucursales.pk_sucursal and tr_retiros.fk_retiro=ct_retiros.pk_retiro$filtro_retiro and tr_retiros.fecha between '$inicio' and '$fin'";

 if (!$rventaso = $mysqli->query($qventaso)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}


if (!$rventasd = $mysqli->query($qventasd)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

if (!$rretiros = $mysqli->query($qretiros)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}


$paso = 2;
// Crea un nuevo objeto PHPExcel
$objPHPExcel = new PHPExcel();

// Establecer propiedades
if($tipo == 1){
    $objPHPExcel->getProperties()
        ->setCreator("Integra Connective")
        ->setLastModifiedBy("Integra Connective")
        ->setTitle("Reporte Dast Ingresos")
        ->setSubject("Ingresos")
        ->setDescription("Ingresos")
        ->setKeywords("Ingresos")
        ->setCategory("Ingresos");
}

if($tipo == 2){
    $objPHPExcel->getProperties()
        ->setCreator("Integra Connective")
        ->setLastModifiedBy("Integra Connective")
        ->setTitle("Reporte Dast Egresos")
        ->setSubject("Egresos")
        ->setDescription("Egresos")
        ->setKeywords("Egresos")
        ->setCategory("Egresos");
}

if($tipo == 3){
    $objPHPExcel->getProperties()
        ->setCreator("Integra Connective")
        ->setLastModifiedBy("Integra Connective")
        ->setTitle("Reporte Dast Balance")
        ->setSubject("Balance")
        ->setDescription("Balance")
        ->setKeywords("Balance")
        ->setCategory("Balance");
}


// Agregar Informacion
$objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A1', 'Tipo')
        ->setCellValue('B1', 'Sucursal')
        ->setCellValue('C1', 'Folio / Motivo')
        ->setCellValue('D1', 'Fecha')
        ->setCellValue('E1', 'Cliente')
        ->setCellValue('F1', 'Pago')
        ->setCellValue('G1', 'Total');


$objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('368FCD');

$objPHPExcel->getActiveSheet()->getStyle('G1:G100')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);


$total_efectivo=0.00;
$total_transferencia=0.00;
$total_tarjeta=0.00;
$total_cheque=0.00;

$total_efectivo_retiros=0.00;
$total_transferencia_retiros=0.00;
$total_tarjeta_retiros=0.00;
$total_cheque_retiros=0.00;

$total_final_abonos = 0.00;
$total_final_retiros = 0.00;
$total_final = 0.00;


//ORDEN
if($tipo == 1 || $tipo==3){

    while ($abonoso = $rventaso->fetch_assoc()) {
        
        $npago="";
    
        if ($abonoso["pago"]==1)
        {
            $total_efectivo=$total_efectivo+$abonoso["cantidad"];
            $npago="Efectivo";
        }

        if ($abonoso["pago"]==2)
        {
            $total_transferencia=$total_transferencia+$abonoso["cantidad"];
            $npago="Transferencia";
        }


        if ($abonoso["pago"]==3)
        {
            $total_tarjeta=$total_tarjeta+$abonoso["cantidad"];
            $npago="Tarjeta";
        }


        if ($abonoso["pago"]==4)
        {
            $total_cheque=$total_cheque+$abonoso["cantidad"];
            $npago="Cheque";
        }


        $cantidadf=number_format($abonoso["cantidad"],2);


        $objPHPExcel->getActiveSheet()->setCellValue('A' . $paso, 'Orden');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $paso, $abonoso["sucursal"]);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $paso, $abonoso["folio"]);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $paso, $abonoso["fecha"] );
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $paso, $abonoso["cliente"] );
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $paso, $npago );
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $paso, "$".$cantidadf );
        

        $paso++;

    }

}


//VENTAS MOSTRADOR
if($tipo==1 || $tipo ==3){

    while ($abonosd = $rventasd->fetch_assoc()) {
        
        $npago="";
    
        if ($abonosd["pago"]==1)
        {
            $total_efectivo=$total_efectivo+$abonosd["cantidad"];
            $npago="Efectivo";
        }

        if ($abonosd["pago"]==2)
        {
            $total_transferencia=$total_transferencia+$abonosd["cantidad"];
            $npago="Transferencia";
        }


        if ($abonosd["pago"]==3)
        {
            $total_tarjeta=$total_tarjeta+$abonosd["cantidad"];
            $npago="Tarjeta";
        }


        if ($abonosd["pago"]==4)
        {
            $total_cheque=$total_cheque+$abonosd["cantidad"];
            $npago="Cheque";
        }


        $cantidadf=number_format($abonosd["cantidad"],2);


        $objPHPExcel->getActiveSheet()->setCellValue('A' . $paso, 'Venta mostrador');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $paso, $abonosd["sucursal"]);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $paso, $abonosd["folio"]);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $paso, $abonosd["fecha"] );
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $paso, $abonosd["cliente"] );
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $paso, $npago );
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $paso, "$".$cantidadf );
        

        $paso++;


    }

}


//RETIROS
if($tipo == 2 || $tipo == 3){

    while ($retiros = $rretiros->fetch_assoc()) {
        
        $npago="";
    
        if ($retiros["pago"]==1)
        {
            $total_efectivo_retiros=$total_efectivo_retiros+$retiros["cantidad"];
            $npago="Efectivo";
        }

        if ($retiros["pago"]==2)
        {
            $total_transferencia_retiros=$total_transferencia_retiros+$retiros["cantidad"];
            $npago="Transferencia";
        }


        if ($retiros["pago"]==3)
        {
            $total_tarjeta_retiros=$total_tarjeta_retiros+$retiros["cantidad"];
            $npago="Tarjeta";
        }


        if ($retiros["pago"]==4)
        {
            $total_cheque_retiros=$total_cheque_retiros+$retiros["cantidad"];
            $npago="Cheque";
        }


        $cantidadf=number_format($retiros["cantidad"],2);

        $objPHPExcel->getActiveSheet()->setCellValue('A' . $paso, 'Retiro');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $paso, $retiros["sucursal"]);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $paso, $retiros["motivo"].'('.$retiros["descripcion"].')');
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $paso, $retiros["fecha"] );
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $paso, 'N/A' );
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $paso, $npago );
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $paso, "$".$cantidadf );

        $objPHPExcel->getActiveSheet()->getStyle('A'.$paso.':G'.$paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('ffdddd');

        $paso++;

    }

}


//ORDEN/VENTA MOSTRADOR --> TOTALES
if($tipo == 1 || $tipo == 3){

    $objPHPExcel->getActiveSheet()->getStyle('F'.$paso.':G'.$paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('fff7b6');
    $objPHPExcel->getActiveSheet()->setCellValue('F' . $paso, "Efectivo (Ingreso)");
    $objPHPExcel->getActiveSheet()->setCellValue('G' . $paso, "$".number_format($total_efectivo,2));

    $paso++;

    $objPHPExcel->getActiveSheet()->getStyle('F'.$paso.':G'.$paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('fef58d');
    $objPHPExcel->getActiveSheet()->setCellValue('F' . $paso, "Transferencia (Ingreso)");
    $objPHPExcel->getActiveSheet()->setCellValue('G' . $paso, "$".number_format($total_transferencia,2));

    $paso++;

    $objPHPExcel->getActiveSheet()->getStyle('F'.$paso.':G'.$paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('fff7b6');
    $objPHPExcel->getActiveSheet()->setCellValue('F' . $paso, "Tarjeta (Ingreso)");
    $objPHPExcel->getActiveSheet()->setCellValue('G' . $paso, "$".number_format($total_tarjeta,2));

    $paso++;

    $objPHPExcel->getActiveSheet()->getStyle('F'.$paso.':G'.$paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('fef58d');
    $objPHPExcel->getActiveSheet()->setCellValue('F' . $paso, "Cheque (Ingreso)");
    $objPHPExcel->getActiveSheet()->setCellValue('G' . $paso, "$".number_format($total_cheque,2));

    $paso++;

}


//RETIRO --> TOTALES
if($tipo == 2 || $tipo == 3){

    $objPHPExcel->getActiveSheet()->getStyle('F'.$paso.':G'.$paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('9acef8');
    $objPHPExcel->getActiveSheet()->setCellValue('F' . $paso, "Efectivo (Egreso)");
    $objPHPExcel->getActiveSheet()->setCellValue('G' . $paso, "$".number_format($total_efectivo_retiros,2));

    $paso++;

    $objPHPExcel->getActiveSheet()->getStyle('F'.$paso.':G'.$paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('6eb8f5');
    $objPHPExcel->getActiveSheet()->setCellValue('F' . $paso, "Transferencia (Egreso)");
    $objPHPExcel->getActiveSheet()->setCellValue('G' . $paso, "$".number_format($total_transferencia_retiros,2));

    $paso++;

    $objPHPExcel->getActiveSheet()->getStyle('F'.$paso.':G'.$paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('9acef8');
    $objPHPExcel->getActiveSheet()->setCellValue('F' . $paso, "Tarjeta (Egreso)");
    $objPHPExcel->getActiveSheet()->setCellValue('G' . $paso, "$".number_format($total_tarjeta_retiros,2));

    $paso++;

    $objPHPExcel->getActiveSheet()->getStyle('F'.$paso.':G'.$paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('6eb8f5');
    $objPHPExcel->getActiveSheet()->setCellValue('F' . $paso, "Cheque (Egreso)");
    $objPHPExcel->getActiveSheet()->setCellValue('G' . $paso, "$".number_format($total_cheque_retiros,2));

    $paso++;

}


$total_final_abonos = $total_efectivo + $total_transferencia + $total_tarjeta + $total_cheque;

$total_final_retiros = $total_efectivo_retiros + $total_transferencia_retiros + $total_tarjeta_retiros + $total_cheque_retiros;

$total_final = $total_final_abonos - $total_final_retiros;


//TOTALES
if($tipo == 1){
    $objPHPExcel->getActiveSheet()->getStyle('F'.$paso.':G'.$paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('A8F991');
    $objPHPExcel->getActiveSheet()->setCellValue('F' . $paso, "TOTAL");
    $objPHPExcel->getActiveSheet()->setCellValue('G' . $paso, "$".number_format($total_final_abonos,2));
}

if($tipo == 2){
    $objPHPExcel->getActiveSheet()->getStyle('F'.$paso.':G'.$paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('A8F991');
    $objPHPExcel->getActiveSheet()->setCellValue('F' . $paso, "TOTAL");
    $objPHPExcel->getActiveSheet()->setCellValue('G' . $paso, "$".number_format($total_final_retiros,2));
}

if($tipo == 3){
    $objPHPExcel->getActiveSheet()->getStyle('F'.$paso.':G'.$paso)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('A8F991');
    $objPHPExcel->getActiveSheet()->setCellValue('F' . $paso, "TOTAL");
    $objPHPExcel->getActiveSheet()->setCellValue('G' . $paso, "$".number_format($total_final,2));
}


$objPHPExcel->getActiveSheet()->getColumnDimension('A')
        ->setAutoSize(true);

$objPHPExcel->getActiveSheet()->getColumnDimension('B')
        ->setAutoSize(true);

$objPHPExcel->getActiveSheet()->getColumnDimension('C')
        ->setAutoSize(true);

$objPHPExcel->getActiveSheet()->getColumnDimension('D')
        ->setAutoSize(true);

$objPHPExcel->getActiveSheet()->getColumnDimension('E')
        ->setAutoSize(true);

$objPHPExcel->getActiveSheet()->getColumnDimension('F')
        ->setAutoSize(true);

$objPHPExcel->getActiveSheet()->getColumnDimension('G')
        ->setAutoSize(true);





$objPHPExcel->getActiveSheet()->setTitle('Reporte cortes');


$objPHPExcel->setActiveSheetIndex(0);

// Se modifican los encabezados del HTTP para indicar que se envia un archivo de Excel.
// Redirect output to a client’s web browser (Excel5)

$ahora = new DateTime();
$id_file = $ahora->getTimestamp() . ".xls";



header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment;filename="' . $id_file . '"');
header('Cache-Control: max-age=0');



$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');

