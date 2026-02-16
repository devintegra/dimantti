<?php

$mysqli = new mysqli('162.241.61.149', 'rober248_dimantti', 'Dimantti2026!', 'rober248_dimantti');

if ($mysqli->connect_errno) {

    echo "Lo sentimos, este sitio web está experimentando problemas.";

    exit;
}
