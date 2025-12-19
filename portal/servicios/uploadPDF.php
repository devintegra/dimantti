<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

header("Access-Control-Allow-Origin: *");
include("conexioni.php");
mysqli_set_charset( $mysqli, 'utf8' );
$error=0;


 

 
 $archivo=$_FILES['archivo']['name'];
 
if (isset($_POST['pk_propiedad']) && is_numeric($_POST['pk_propiedad'])) {
    $pk_propiedad = (int)$_POST['pk_propiedad'];
}


$narchivo=$pk_propiedad.".pdf";

 
      
      
  if ($error==0)
  {
      $target_dir = "contratos/";
$target_file = $target_dir . $narchivo;
$uploadOk = 1;
//$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

if (move_uploaded_file($_FILES["archivo"]["tmp_name"], $target_file)) {
    if (!$mysqli->query("update ct_propiedades set archivo_contrato='$narchivo' where pk_propiedad=$pk_propiedad"))
      {
         $error=1; 
      }

       
    } else {
       $error=1;
    }

  }

$myObj->error = $error;
$myObj->mensajea = "se ha agregado el archivo correctamente";

$myJSON = json_encode($myObj);
echo $myJSON;
       