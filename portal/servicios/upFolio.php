<?php

header("Access-Control-Allow-Origin: *");
include("conexioni.php");
mysqli_set_charset( $mysqli, 'utf8' );
date_default_timezone_set('America/Mexico_City');
$norden=0;
$error=0;
 

   

$qorden="select * from tr_ordenes";

if (!$rorden = $mysqli->query($qorden)) {
    $codigo=201;
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
} 






while ($orden = $rorden->fetch_assoc())
            {
    $pk_orden=$orden["pk_orden"];
            
    $qsucursal="select * from ct_sucursales where pk_sucursal = (select fk_sucursal from tr_ordenes where pk_orden=$pk_orden)";

if (!$rsucursal = $mysqli->query($qsucursal)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas1.";
    exit;
}   
   $sucursal = $rsucursal->fetch_assoc();
   
   $sucursal_inicial=$sucursal["iniciales"];

   $qentrada="select * from tr_ordenes where pk_orden=$pk_orden";

if (!$rentrada = $mysqli->query($qentrada)) {
    echo "Lo sentimos, esta aplicación está experimentando problemas2.";
    exit;
}   
   $entrada = $rentrada->fetch_assoc();
   $entrada_fecha=$entrada["fecha"];   

$fecha_folio=  str_replace("-", "",$entrada_fecha);
$folio=$sucursal_inicial.$fecha_folio.$pk_orden;      


if (!$mysqli->query("update tr_ordenes set folio='$folio' where pk_orden=$pk_orden"))
      {
        echo "Lo sentimos, esta aplicación está experimentando problemas3.";
    exit;
      }


    
            }



      
      
      
    
    

       
      
      
            