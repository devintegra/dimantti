<?php
include("servicios/conexioni.php");
require('servicios/entradaPDF.php');
require('servicios/phpqrcode/qrlib.php');
mysqli_set_charset($mysqli, 'utf8');


if (isset($_GET['id']) && is_string($_GET['id'])) {
    $id = $_GET['id'];
}

if (isset($_GET['ph']) && is_string($_GET['ph'])) {
    $telefono = $_GET['ph'];
}


//SUCURSAL
#region
$mysqli->next_result();
if (!$rsucursal = $mysqli->query("CALL sp_get_sucursal($id)")) {
    echo "Lo sentimos, esta aplicación está experimentando problemas1.";
    exit;
}
$sucursal = $rsucursal->fetch_assoc();
$sucursal_nombre = $sucursal["nombre"];
$sucursal_id = $sucursal["pk_sucursal"];
$sucursal_direccion = $sucursal["direccion"];
$sucursal_telefono = $sucursal["telefono"];
$sucursal_correo = $sucursal["correo"];
$sucursal_inicial = $sucursal["iniciales"];
#endregion



//ORDEN
#region
$mysqli->next_result();
if (!$rentrada = $mysqli->query("CALL sp_get_orden($id)")) {
    echo "Lo sentimos, esta aplicación está experimentando problemas2.";
    exit;
}

$entrada = $rentrada->fetch_assoc();
$entrada_fecha = $entrada["fecha"];
$entrada_cliente = $entrada["fk_cliente"];
$cliente_nombre = $entrada["cliente"];
$cliente_telefono = $entrada["telefono"];
$cliente_correo = $entrada["correo"];
$entrada_usuario = $entrada["fk_usuario"];
$usuario_nombre = $entrada["usuario"];
$entrada_hora = $entrada["hora"];
$entrada_observaciones = $entrada["observaciones"];
$entrada_firma = $entrada["firma"];
$entrada_anticipo = $entrada["anticipo"];
$entrada_pago_inicial = $entrada["pago"];
$entrada_folio = $entrada["folio"];
$fk_orden_prev = $entrada["fk_orden_prev"];
$direccion = $entrada["direccion"];
$latitud = $entrada["latitud"];
$longitud = $entrada["longitud"];
$folio = $entrada["folio"];

if ($entrada_firma != null && $entrada_firma != "") {
    $firma = "servicios/firmas/" . $entrada_firma;
}
#endregion




//ABONO
#region
$qanticipo = "SELECT MIN(pk_ordenes_registros) as pk_ordenes_registros, comentarios as comentarios from rt_ordenes_registros where fk_orden=$id and pdf=1";

$mysqli->next_result();
if (!$ranticipo = $mysqli->query($qanticipo)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas6.";
    exit;
}

$anticipo_valor = $ranticipo->fetch_assoc();
$comentarios = $anticipo_valor["comentarios"];

if ($comentarios != '') {

    $separador = "Abono: $"; // Usar una cadena
    $separada = explode($separador, $comentarios);
    if (isset($separada[1])) {
        $anticipo_pagado = $separada[1];
    } else {
        $anticipo_pagado = "";
    }
} else {

    $anticipo_pagado = 0.00;

    if ($entrada_anticipo != null && $entrada_anticipo != 0) {

        $anticipo_pagado = $entrada_anticipo;
    }
}
#endregion




//EQUIPO
#region
$mysqli->next_result();
if (!$rentradasd = $mysqli->query("CALL sp_get_orden_detalle($id)")) {
    echo "Lo sentimos, esta aplicación está experimentando problemas7.";
    exit;
}
#endregion




//CREAR PDF
#region
$pdf = new PDF_Invoice('P', 'mm', 'A5');
$pdf->AddPage();
$pdf->SetAutoPageBreak(false);
$pdf->SetTitle("ORDEN $folio");
#endregion




//ENCABEZADO
#region
$pdf->addSociete(
    $sucursal_nombre,
    "$sucursal_direccion \n" .
        "Telefono: $sucursal_telefono. " .
        "Correo: $sucursal_correo"
);
$pdf->Image("servicios/logotipo.png", 5, 5, 0, 25);
$pdf->fact_dev("", $entrada_folio);

$pdf->tipo_pago("Anticipo: $" . $anticipo_pagado); //$anticipo
$pdf->contacto("fecha", $entrada_fecha);

$pdf->addCliente($cliente_nombre);
$pdf->addClienteTel($cliente_telefono);
$pdf->addClienteCorreo($cliente_correo);
#endregion




//TABLA
#region
$cols = array(
    "NS"    => 25,
    "Accesorio"  => 30,
    "Defecto"      => 30,
    "Observaciones"      => 54
);
$pdf->addCols($cols);

$cols = array(
    "NS"    => "C",
    "Accesorio"  => "C",
    "Defecto"      => "C",
    "Observaciones"      => "C"
);

$pdf->addLineFormat($cols);
$pdf->addLineFormat($cols);

$y    = 65;


while ($entradasd = $rentradasd->fetch_assoc()) {

    $line = array(
        "NS"    => $entradasd["ns"],
        "Accesorio"  => $entradasd["nombre"],
        "Defecto"      => $entradasd["checklist"],
        "Observaciones" => "Valor estimado: $" . $entradasd["estimado"]
    );
    $size = $pdf->addLine($y, $line);
    $y   += $size + 2;
}
#endregion




//REGISTROS
#region
$qservicios = "SELECT * FROM rt_ordenes_registros WHERE fk_orden = $id AND pdf = 1 AND precio > 0";

$mysqli->next_result();
if (!$rservicios = $mysqli->query($qservicios)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas8.";
    exit;
}

while ($servicio = $rservicios->fetch_assoc()) {

    $line = array(
        "NS"    => "-",
        "Accesorio"  => $servicio["comentarios"],
        "Defecto"      => "-",
        "Observaciones" => "Costo: $" . $servicio["precio"]
    );
    $size = $pdf->addLine($y, $line);
    $y   += $size + 2;
}
#endregion




//QR
#region
$url = "https://dimantti.integracontrol.online/portal/ordenPDF.php?id=$id&ph=$telefono";
QRcode::png($url, "qrcode.png");
$pdf->addImageQRI('qrcode.png', 172);
#endregion




//FIRMA
#region
if (is_file($firma)) {
    $pdf->addFirmanew($firma, 162);
}

$pdf->setXY(39, 192);
$pdf->Cell(40, 1, "____________________________________", 0, 0, "L");
$pdf->SetFont("Arial", "B", 8);
$pdf->setXY(52, 194);
$pdf->Cell(40, 3, "Firma del cliente", 0, 0, "L");
#endregion




$pdf->Output();
