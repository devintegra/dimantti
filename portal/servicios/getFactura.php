<?php
//CONFIG
#region
header("Access-Control-Allow-Origin: *");
include("conexioni.php");

mysqli_set_charset($mysqli, 'utf8');

//Make sure that it is a POST request.
if (strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0) {
    throw new Exception('Request method must be POST!');
}

//Make sure that the content type of the POST request has been set to application/json
$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
if (strcasecmp($contentType, 'application/json; charset=utf-8') != 0) {
    throw new Exception('Content type must be: application/json');
}

//Receive the RAW post data.
$content = trim(file_get_contents("php://input"));

//Attempt to decode the incoming RAW post data from JSON.
$decoded = json_decode($content, true);

//If json_decode failed, the JSON is invalid.
if (!is_array($decoded)) {
    throw new Exception('Received content contained invalid JSON!');
}
#endregion


$codigo = 200;
$descripcion = "";

$pk_ventas = $decoded['pk_ventas'];
$pk_venta = $pk_ventas[0];
$fk_usuario = $decoded['fk_usuario'];
$fk_cliente = (int)$decoded['fk_cliente'];
$fk_empresa = (int)$decoded['fk_empresa'];
$metodo_pago = $decoded['metodo_pago'];
$fk_cfdi = $decoded['fk_cfdi'];
$forma_pago = $decoded['forma_pago'];
$subtotal = (float)$decoded['subtotal'];
$total = (float)$decoded['total'];


$mes_actual = date('m');
$anio_acutal = date('Y');



//OBTENER LOS DATOS FISCALES DEL CLIENTE
if ($codigo == 200) {

    $qcliente = "SELECT ct_clientes.*,
        ct_regimenes_fiscales.clave
        FROM ct_clientes, ct_regimenes_fiscales
        WHERE ct_clientes.pk_cliente = $fk_cliente
        AND ct_regimenes_fiscales.pk_regimen_fiscal = ct_clientes.fk_regimen_fiscal";

    if (!$rcliente = $mysqli->query($qcliente)) {
        $codigo = 201;
        $descripcion .= "Error al obtener los datos del cliente\n";
    }

    if ($rcliente->num_rows == 0) {
        $codigo = 201;
        $descripcion .= "No se han registrado los datos fiscales del cliente\n";
    }

    $cliente = $rcliente->fetch_assoc();
}



//OBTENER LOS DATOS FISCALES DE LA EMPRESA
if ($codigo == 200) {

    $qempresa = "SELECT ct_empresas.*,
        ct_regimenes_fiscales.clave
        FROM ct_empresas, ct_regimenes_fiscales
        WHERE ct_empresas.pk_empresa = $fk_empresa
        AND ct_regimenes_fiscales.pk_regimen_fiscal = ct_empresas.fk_regimen_fiscal";

    if (!$rempresa = $mysqli->query($qempresa)) {
        $codigo = 201;
        $descripcion .= "Error al obtener los datos fiscales\n";
    }

    // comprobamos que exista al menos un registro de datos fiscales y los obtenemos
    if ($rempresa->num_rows == 0) {
        $codigo = 201;
        $descripcion .= "No se han registrado los datos fiscales de la empresa\n";
    }

    $datos = $rempresa->fetch_assoc();

    $password = base64_decode($datos['pass']);
}



//VERIFICAR LA EXISTENCIA DEL CER Y KEY
if ($codigo == 200) {

    if (empty($datos['cer'])) {

        $codigo = 201;
        $descripcion .= "No se han subido el archivo del certificado (.cer)\n";
    } else {
        $csd = $datos['cer'];
    }

    if (empty($datos['fkey'])) {

        $codigo = 201;
        $descripcion .= "No se han subido el archivo de la llave (.key)\n";
    } else {
        $key = $datos['fkey'];
    }
}



//GENERAR EL JSON
if ($codigo == 200) {

    $logo_contents = file_get_contents('logo.png');
    $logo = base64_encode($logo_contents);

    $fecha = date("Y-m-d\TH:i:s");



    $generales = array(

        "Version" => "4.0",

        "CSD" => $csd,

        "LlavePrivada" => $key,

        "CSDPassword" => $password,

        "GeneraPDF" => true,

        "Logotipo" => "$logo",

        "CFDI" => "Factura",

        "OpcionDecimales" => "1",

        "NumeroDecimales" => "2",

        "TipoCFDI" => "Ingreso",

        "EnviaEmail" =>  true,

        "ReceptorEmail" => "$datos[correo]",

        "ReceptorEmailCC" => "$cliente[correo]",

        "ReceptorEmailCCO" => "",

        "EmailMensaje" => "Envío y generación de factura desde Dimantti. Integra Connective"

    );


    //Empresa
    $emisor = array(

        "RFC" => "$datos[rfc]",

        "NombreRazonSocial" => "$datos[nombre]",

        "RegimenFiscal" => "$datos[clave]",

        "Direccion" => array(

            array(

                "Calle" => "",

                "NumeroExterior" => "",

                "NumeroInterior" => "",

                "Colonia" => "",

                "Localidad" => "",

                "Municipio" => "",

                "Estado" => "",

                "Pais" => "",

                "CodigoPostal" => "$datos[cp]",

            )

        )

    );


    //Cliente
    $receptor = array(

        "RFC" => "$cliente[rfc]",

        "NombreRazonSocial" => "$cliente[nombre]",

        "UsoCFDI" => "$fk_cfdi",

        "RegimenFiscal" => "$cliente[clave]",

        "Direccion" => array(

            "Calle" => "",

            "NumeroExterior" => "",

            "NumeroInterior" => "",

            "Colonia" => "",

            "Localidad" => "",

            "Municipio" => "",

            "Estado" => "",

            "Pais" => "",

            "CodigoPostal" => "$cliente[cp]",

        )

    );


    $factura_global = array(

        "Periodicidad" => "02", // Mensual

        "Meses" => "$mes_actual",

        "Año" => $anio_acutal // Año en el que se emite la factura

    );


    $encabezado = array(

        "CFDIsRelacionados" => "",

        "TipoRelacion" => "04",

        "Emisor" => $emisor,

        "Receptor" => $receptor,

        "Fecha" => "$fecha",

        "Serie" => "A",

        "Folio" => "$pk_venta",

        "MetodoPago" => "$metodo_pago",

        "FormaPago" => "$forma_pago",

        "Moneda" => "MXN",

        "LugarExpedicion" => "$datos[cp]",

        "SubTotal" => "$subtotal",

        "Total" => "$total"

    );


    if ($cliente['nombre'] == "PUBLICO EN GENERAL" || $cliente['nombre'] == "PÚBLICO EN GENERAL") {
        $encabezado['InformacionFacturaGlobal'] = $factura_global;
    }


    $conceptos = array();


    foreach ($decoded['productos'] as $key => $value) {

        //Datos adicionales
        #region
        if ((int)$value['fk_producto'] > 0) {

            $qdetalle = "SELECT ct_productos.*, ct_unidades_sat.nombre as nombre_unidad
                FROM ct_productos
                LEFT JOIN ct_unidades_sat ON ct_unidades_sat.pk_unidad_sat = ct_productos.clave_unidad_sat
                WHERE ct_productos.pk_producto = $value[fk_producto];";

            if (!$rdetalle = $mysqli->query($qdetalle)) {
                echo "<br>Lo sentimos, esta aplicación está experimentando problemas.1";
                exit;
            }

            $rowd = $rdetalle->fetch_assoc();
            $facturacion = ('Facturación del producto ' . $rowd['codigobarras'] . '. ' . $rowd["nombre"]);
            $clave_sat = $rowd["clave_producto_sat"];
            $rowd["clave_unidad_sat"] ? $clave_unidad_sat = $rowd['clave_unidad_sat'] : $clave_unidad_sat = 'H87';
            $clave_impuesto = '02';
            $unidad = $rowd['nombre_unidad'] ? $rowd['nombre_unidad'] : 'Pieza';
        } else {
            $facturacion = $value['descripcion'];
            $clave_sat = "43211600";
            $clave_unidad_sat = "H87";
            $clave_impuesto = "02";
            $unidad = 'Pieza';
        }
        #endregion



        $subtotal = doubleval($value['total']); //con iva

        //$subtotal_siniva = doubleval($value['costo'] * $value['cantidad']);
        $subtotal_siniva = doubleval($value['costo']);

        $cantidad = doubleval($value['cantidad']);

        //$iva = doubleval($value['iva']);
        //$iva_out_format = $subtotal_siniva * 0.16;
        $iva = number_format($value['iva'], 2, '.', '');

        //$precio_unitario = doubleval($value['costo']);
        $precio_unitario_out_format = (($value['costo'] + $value['iva']) / $value['cantidad']) / 1.16;
        $precio_unitario = number_format($precio_unitario_out_format, 2, ".", "");

        //$subtotal_siniva = number_format($subtotal_siniva, 2, '.', '');

        $clave_impuesto == "02" ? $importe = $subtotal_siniva : $importe = $subtotal;




        if ($clave_impuesto == "02") {

            $impuestos = array(

                array(

                    "TipoImpuesto" => "1",

                    "Impuesto" => "2",

                    "Factor" => "1",

                    "Base" => "$subtotal_siniva", //Mismo que importe

                    "Tasa" => "0.160000",

                    "ImpuestoImporte" => "$iva" //El iva del importe

                )

            );
        } else {
            $impuestos = array();
        }


        $concepto = array(

            "Cantidad" => "$cantidad",

            "CodigoUnidad" => "$clave_unidad_sat",

            "Unidad" => $unidad,

            "CodigoProducto" => "$clave_sat",

            "Producto" => "$facturacion",

            "PrecioUnitario" => "$precio_unitario",

            "Importe" => "$importe", //Multiplicacion de precio_unitario * cantidad

            "ObjetoDeImpuesto" => "$clave_impuesto",

            "Impuestos" => $impuestos

        );

        array_push($conceptos, $concepto);
    }


    $objList = array(

        "DatosGenerales" => $generales,

        "Encabezado" => $encabezado,

        "Conceptos" => $conceptos,

    );

    $objList_json = json_encode(
        array(

            "DatosGenerales" => "",

            "Encabezado" => $encabezado,

            "Conceptos" => $conceptos,

        )
    );

    if (!$mysqli->query("INSERT INTO tr_peticiones(descripcion) VALUES('$objList_json')")) {
        $codigo = 201;
        $descripcion .= "Error al guardar la peticion\n";
    }
}




$mysqli->close();
$general = array("codigo" => $codigo, "descripcion" => $descripcion, "objList" => $objList);
$myJSON = json_encode($general);
header('Content-type: application/json');
echo $myJSON;
