<?php
header("Access-Control-Allow-Origin: *");
include("servicios/conexioni.php");
require('servicios/tcpdf/tcpdf.php');
require('servicios/phpqrcode/qrlib.php');
mysqli_set_charset($mysqli, 'utf8');

if (isset($_GET['id']) && is_string($_GET['id'])) {
    $id = $_GET['id'];
}


//SUCURSAL
#region
$qsucursal = "SELECT * FROM ct_sucursales WHERE pk_sucursal = (SELECT fk_sucursal FROM tr_ventas WHERE pk_venta = $id)";

if (!$rsucursal = $mysqli->query($qsucursal)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. Error al obtener los datos de la sucursal";
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



//ENCABEZADO DE LA VENTA
#region
$qentrada = "SELECT trv.*,
    ctu.nombre as usuario
FROM tr_ventas trv
LEFT JOIN ct_usuarios ctu ON ctu.pk_usuario = trv.fk_usuario
WHERE pk_venta=$id";

if (!$rentrada = $mysqli->query($qentrada)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. Error al obtener los datos de la venta";
    exit;
}
$entrada = $rentrada->fetch_assoc();
$entrada_fecha = $entrada["fecha"];
$entrada_cliente = (int)$entrada["fk_cliente"];
$entrada_usuario = $entrada["usuario"];
$entrada_hora = $entrada["hora"];
$entrada_tipo = $entrada["tipo"];
$entrada_pago = $entrada["fk_pago"];
$entrada_pdf_impreso = $entrada["pdf_impreso"];
$entrada_folio = $entrada["folio"];
$entrada_estatus = $entrada["estatus"];
$entrada_observaciones = $entrada["observaciones"];
$entrada_observaciones_format = str_replace("\n", ", ", $entrada_observaciones);
$entrada_subtotal = $entrada["subtotal"];
$entrada_descuento = $entrada["descuento"];
$entrada_comision = $entrada["comision"];
$entrada_saldo = $entrada["saldo"];
$entrada_total = $entrada["total"];
$entrada_anticipo = $entrada["anticipo"];
$entrada_cambio = ($entrada["total"] - $entrada["anticipo"]);
$entrada_cambio >= 0 ? $entrada_cambio = 0 : $entrada_cambio = abs($entrada_cambio);
#endregion



//MÉTODO DE PAGO
#region
$qpago = "SELECT CONCAT(
    CASE WHEN efectivo > 0 THEN 'Efectivo.' ELSE '' END,
    CASE WHEN credito > 0 THEN 'Crédito. ' ELSE '' END,
    CASE WHEN debito > 0 THEN 'Debito. ' ELSE '' END,
    CASE WHEN cheque > 0 THEN 'Cheque. ' ELSE '' END,
    CASE WHEN transferencia > 0 THEN 'Tran. ' ELSE '' END
  ) AS campos_cumplen
  FROM tr_ventas
  WHERE pk_venta = $id";

if (!$rpago = $mysqli->query($qpago)) {
    echo "<br>Lo sentimos, esta aplicación está experimentando problemas. Error al obtener los métodos de pago";
    exit;
}

$pago = $rpago->fetch_assoc();
$npago = $pago["campos_cumplen"];
#endregion



//FIRMA
#region
if ($entrada["firmav"] != null && $entrada["firmav"] != "" && $entrada["firmav"] != " ") {
    $entrada_firma = $entrada["firmav"];
} else {
    $entrada_firma = $entrada["firma"];
}
#endregion



//DATOS DEL CLIENTE
#region
$qproveedor = "SELECT * FROM ct_clientes WHERE pk_cliente = $entrada_cliente";

if (!$rproveedor = $mysqli->query($qproveedor)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. Error al obtener los datos del cliente";
    exit;
}
$proveedor = $rproveedor->fetch_assoc();
$cliente_nombre = $proveedor["nombre"];
$cliente_telefono = $proveedor["telefono"];
$cliente_correo = $proveedor["correo"];
#endregion



//USUARIO
#region
$qusuario = "SELECT ct_usuarios.nombre,
    ct_sucursales.nombre as sucursal
    FROM ct_usuarios, ct_sucursales
    WHERE ct_usuarios.pk_usuario = '$entrada_usuario'
    AND ct_usuarios.fk_sucursal = ct_sucursales.pk_sucursal";

if (!$rusuario = $mysqli->query($qusuario)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. Error al obtener los datos del usuario";
    exit;
}
$usuario = $rusuario->fetch_assoc();
$usuario_nombre = $usuario["nombre"];
$usuario_sucursal = $usuario["sucursal"];
#endregion



//ALTO DE LA HOJA
#region
$qdetalle = "SELECT COUNT(DISTINCT fk_producto) AS registros FROM tr_ventas_detalle WHERE fk_venta = $id AND unitario > 0 AND estado = 1";

if (!$rdetalle = $mysqli->query($qdetalle)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas6.";
    exit;
}

$detalle = $rdetalle->fetch_assoc();
$detalle_registros = (int)$detalle["registros"];

$pageHeight = 200 + $detalle_registros * 16;


if ($entrada_estatus == 2) {
    $qdev = "SELECT COUNT(DISTINCT fk_producto) AS registros FROM tr_devoluciones_detalle WHERE fk_venta = $id AND estado = 1";

    if (!$rdev = $mysqli->query($qdev)) {
        echo "Lo sentimos, esta aplicación está experimentando problemas6.";
        exit;
    }

    $dev = $rdev->fetch_assoc();
    $dev_registros = (int)$dev["registros"];

    $pageHeight += ($dev_registros * 16);
}

if ($entrada_estatus == 3) {
    $pageHeight += ($detalle_registros * 16);
}

$pageHeight = $pageHeight < 260 ? 260 : $pageHeight; //min-heigth = 260
#endregion



//PRODUCTOS
#region
$qentradasd = "SELECT SUM(cantidad) as cantidad, descripcion, unitario, serie FROM tr_ventas_detalle WHERE fk_venta = $id AND unitario > 0 GROUP BY fk_producto, serie, descripcion;";

if (!$rentradasd = $mysqli->query($qentradasd)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas. Errro al obtener los productos";
    exit;
}
#endregion




//CREAR PDF
#region
$pageLayout = array(80, $pageHeight);
$pdf = new TCPDF('P', PDF_UNIT, $pageLayout, true, 'UTF-8', false);
//$pdf = new TCPDF('P', PDF_UNIT, $pageLayout, false, 'ISO-8859-1', false);

$pdf->SetCreator("Posmovil");
$pdf->SetAuthor('Posmovil');
$pdf->SetTitle("VENTA #" . $id);

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(5, 5, 5);

$pdf->SetAutoPageBreak(TRUE, 5);

$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
    require_once(dirname(__FILE__) . '/lang/eng.php');
    $pdf->setLanguageArray($l);
}

$fontname = TCPDF_FONTS::addTTFfont('servicios/tcpdf/fonts/quicksand/Quicksand-Regular.ttf', 'TrueTypeUnicode', '');
$pdf->SetFont('dejavusans', '', 10);

$pdf->AddPage();
$currentPage = $pdf->getAliasNumPage();
$totalPages = $pdf->getAliasNbPages();
#endregion




//DIRECCION
$parrafo_direccion = <<<HTML
    <div>
        <p style="font-size: 10px; text-align: center;">
            $sucursal_nombre. $sucursal_direccion.
            <span style="font-weight: bold;"> Teléfono: </span> $sucursal_telefono.
            <span style="font-weight: bold;"> Correo: </span> $sucursal_correo
        </p>
    </div>
HTML;


//FOLIO/CLIENTE/FECHA
$parrafo_info = <<<HTML
    <table border="0" cellpadding="2" style="text-align: center; font-size: 10px;">
        <tr>
            <td colspan="2" style="font-weight: bold;">$entrada_folio</td>
        </tr>
        <tr>
            <td colspan="2">CLIENTE: $cliente_nombre</td>
        </tr>
        <tr>
            <td>FECHA: $entrada_fecha</td>
            <td>HORA: $entrada_hora</td>
        </tr>
    </table>
HTML;




//LEYENDA DE IMPRESIÓN
#region
$leyenda_ticket = "";
if ($entrada_tipo != 2) {
    if ($entrada_estatus == 1) {
        if ($entrada_pdf_impreso != 0) {
            $leyenda_ticket = "REIMPRESION";
        } else {
            if (!$mysqli->query("UPDATE tr_ventas SET pdf_impreso = 1 WHERE pk_venta = $id")) {
                $error = 1;
            }
        }
    }

    if ($entrada_estatus == 2) {
        $leyenda_ticket = "DEVOLUCION DE VENTA";
    }

    if ($entrada_estatus == 3) {
        $leyenda_ticket = "CANCELACION DE VENTA";
    }
}
#endregion





//QR
#region
$url = "https://posmovil.integracontrol.online/portal/ventaPDF.php?id=$id&ph=";
QRcode::png($url, "qrcode.png");
#endregion





//PRODUCTOS
#region
$cantidad_productos = 0;
$tr_producto = "";

while ($row = $rentradasd->fetch_assoc()) {

    $unitario = number_format($row['unitario'], 2);
    $total_producto = number_format($row['cantidad'] * $row['unitario'], 2);

    $tr_producto .= <<<HTML
        <tr>
            <td style="width: 10%">$row[cantidad]</td>
            <td style="width: 54%">$row[descripcion]</td>
            <td style="width: 18%; text-align: right;">$$unitario</td>
            <td style="width: 18%; text-align: right;">$$total_producto</td>
        </tr>
    HTML;

    $cantidad_productos += $row['cantidad'];
}


$subtotal = number_format($entrada_subtotal, 2);
$descuento = number_format($entrada_descuento, 2);
$comision = number_format($entrada_comision, 2);
$total_venta = number_format($entrada_total + $entrada_comision, 2);
$anticipo = number_format($entrada_anticipo, 2);
$cambio = number_format($entrada_cambio, 2);
$saldo = number_format($entrada_saldo, 2);


$parrafo_productos = <<<HTML
    <table border="0" cellpadding="3" style="text-align: center; font-size: 8px;">
        <thead>
            <tr style="font-weight: bold;">
                <th style="width: 10%">#</th>
                <th style="width: 54%">PRODUCTO</th>
                <th style="width: 18%">UNIT.</th>
                <th style="width: 18%">IMP.</th>
            </tr>
        </thead>
        <tbody>
            $tr_producto
        </tbody>
        <tfoot>
            <tr>
                <td>$cantidad_productos</td>
                <td colspan="3"></td>
            </tr>
            <tr style="font-size: 10px; text-align: left;">
                <td colspan="4">Método de pago: $npago</td>
            </tr>
            <tr style="font-size: 10px; text-align: left;">
                <td colspan="4">Observaciones: $entrada_observaciones_format</td>
            </tr>
            <tr>
                <td colspan="4"></td>
            </tr>
            <tr style="text-align: right;">
                <td colspan="3" style="font-weight: bold;">SUBTOTAL:</td>
                <td>$$subtotal</td>
            </tr>
            <tr style="text-align: right;">
                <td colspan="3" style="font-weight: bold;">DESCUENTO:</td>
                <td>$$descuento</td>
            </tr>
            <tr style="text-align: right;">
                <td colspan="3" style="font-weight: bold;">COMISION:</td>
                <td>$$comision</td>
            </tr>
            <tr style="text-align: right;">
                <td colspan="3" style="font-weight: bold;">TOTAL:</td>
                <td>$$total_venta</td>
            </tr>
            <tr style="text-align: right;">
                <td colspan="3" style="font-weight: bold;">EL CLIENTE ENTREGA:</td>
                <td>$$anticipo</td>
            </tr>
            <tr style="text-align: right;">
                <td colspan="3" style="font-weight: bold;">CAMBIO:</td>
                <td>$$cambio</td>
            </tr>
            <tr style="text-align: right;">
                <td colspan="3" style="font-weight: bold;">SALDO:</td>
                <td>$$saldo</td>
            </tr>
            <tr>
                <td colspan="4"></td>
            </tr>
            <tr>
                <td colspan="4"></td>
            </tr>
            <tr style="font-size: 10px; text-align: center;">
                <td colspan="4">Le ha atendido $entrada_usuario</td>
            </tr>
            <tr style="font-size: 10px; text-align: center;">
                <td colspan="4">Gracias por su compra!</td>
            </tr>
            <tr>
                <td colspan="4"></td>
            </tr>
            <tr>
                <td colspan="4" style="font-size: 24px;">$leyenda_ticket</td>
            </tr>
            <tr>
                <td colspan="4"></td>
            </tr>
            <tr>
                <td colspan="4">CLAUSULAS DE GARANTIA</td>
            </tr>
            <tr>
                <td colspan="4">
                    <img src="qrcode.png" style="width: 100px; height: auto;">
                </td>
            </tr>
        </tfoot>
    </table>
HTML;
#endregion




//IMPRIMIR CONTENIDO
#region
$pdf->Image('@' . file_get_contents('servicios/logo.png'), 30, 5, 0, 20);
$pdf->writeHTMLCell(76, '', 2, 18, $parrafo_direccion);
$pdf->writeHTMLCell(76, '', 2, 40, $parrafo_info);
$pdf->Line(2, 58, 78, 58);
$pdf->writeHTMLCell(76, '', 2, 60, $parrafo_productos);
#endregion




//BARCODE
#region
$style = array(
    'border' => 0,
    'vpadding' => 'auto',
    'hpadding' => 'auto',
    'fgcolor' => array(0, 0, 0),
    'bgcolor' => false, // transparente
    'text' => true,
    'font' => 'helvetica',
    'fontsize' => 8,
    'stretchtext' => 4,
);

// Generar el código de barras
$pdf->SetY($pdf->getPageHeight() - 25);
$pdf->write1DBarcode($entrada_folio, 'C39', '12', "", '60', 18, 0.4, $style, 'N');
#endregion




//DETALLES FINALES
#region
// reset pointer to the last page
$pdf->lastPage();

// Obtener el ancho y alto de la página, teniendo en cuenta los márgenes
$anchoPagina = $pdf->getPageWidth() - 8 - 8;
$altoPagina = $pdf->getPageHeight() - 8 - 8;

//Close and output PDF document
$fileName = "VENTA_#" . $id . ".pdf";
$pdf->Output($fileName, 'I');
#endregion
