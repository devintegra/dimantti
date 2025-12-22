<?php
include("servicios/conexioni.php");
require('servicios/entradaoPDF.php');
mysqli_set_charset($mysqli, 'utf8');


if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
}


$qproducto = "SELECT * FROM ct_productos where pk_producto = $id";

if (!$rproducto = $mysqli->query($qproducto)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas1.";
    exit;
}
$producto = $rproducto->fetch_assoc();
$producto_nombre = $producto["nombre"];
$producto_fecha = $producto["fecha"];
$producto_codigo = $producto["codigobarras"];
$producto_clave = $producto["clave"];



$pdf = new PDF_Invoice('P', 'mm', array(80, 100));
$pdf->AddPage();
$pdf->SetAutoPageBreak(false);


//$pdf->addImage("servicios/logo.png", 120, 50);
//$pdf->fact_dev("", $id);
$pdf->SetFont('Arial', 'B', 10);    //Letra Arial, negrita (Bold), tam. 20
$textypos = 24;
$pdf->setY(2);
$pdf->setX(5);
$pdf->Cell(70, $textypos, utf8_decode($producto_clave), 0, 0, 'L');
$textypos += 10;
$pdf->setX(5);
$pdf->Cell(70, $textypos, utf8_decode($producto_nombre), 0, 0, 'L');
$textypos += 60;



$pdf->Code128(5, 25, $producto_codigo, 70, 20);
$pdf->setX(5);
$pdf->Cell(70, $textypos, utf8_decode($producto_codigo), 0, 0, 'C');



$ny = $ny + 9;

$pdf->Output();
