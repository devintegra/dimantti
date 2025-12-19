<?php

$mysqli = new mysqli('162.241.61.149', 'rober248_posmovil', 'Posmovil2025!', 'rober248_posmovil');

if ($mysqli->connect_errno) {

    echo "Lo sentimos, este sitio web está experimentando problemas.";

    exit;
}
