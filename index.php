<?php

@session_start();
@session_destroy();

?>


<!doctype html>

<html class="no-js" lang="">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Dimantti - Consola de administración</title>
    <meta name="description" content="Consola de administraciòn Dast">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href='https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="portal/css/login.css">

    <link rel="shortcut icon" href="portal/images/user-sbg.png" />

</head>

<body>

    <div class="container">
        <div class="content-main">
            <div class="content-logo">
                <img src="portal/images/logotipo.png" alt="Dimantti Logotipo">
                <h1 class="titulo">MANAGEMENT</h1>
            </div>
            <div class="content-form">
                <div class="forma">
                    <div class="content-input">
                        <i class='bx bx-user-circle'></i>
                        <input id="usuario" type="text" placeholder="usuario">
                    </div>
                    <div class="content-input">
                        <i class='bx bx-key'></i>
                        <input id="pass" type="password" placeholder="contraseña">
                    </div>

                    <button id="login" type="button">INICIO DE SESIÓN</button>

                </div>

            </div>
        </div>
    </div>

    <script src="portal/assets/vendor/jquery-2.1.4.min.js"></script>
    <script src="portal/assets/popper.min.js"></script>
    <script src="portal/assets/plugins.js"></script>
    <script src="portal/assets/main.js"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="portal/custom/login.js"></script>

</body>

</html>
