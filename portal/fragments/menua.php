<?php

header('Cache-control: private');
include("servicios/conexioni.php");
mysqli_set_charset($mysqli, 'utf8');
@session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
}


//DATOS DEL USUARIO
#region
$usuario = $_SESSION['usuario'];
$nivel_usuario = $_SESSION['nivel'];

$qusuarios = "SELECT * FROM ct_usuarios where pk_usuario='$usuario' and estado=1";
if (!$rusuarios = $mysqli->query($qusuarios)) {
    echo "<br>Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

$usuarios = $rusuarios->fetch_assoc();
$nombre_usuario = $usuarios["nombre"];
$avatar = $usuarios["imagen"];
#endregion

?>


<!--ICONOS-->
<link href='https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css' rel='stylesheet'>
<style>
    /*Estilos del icono de cada pestaña*/
    .left-panel-icons {
        font-size: 28px;
        margin: 0 5px 0 0;
    }

    /*Estilos del botón de cerrar*/
    .nav-link-close {
        display: -webkit-flex;
        display: flex;
        -webkit-align-items: center;
        align-items: center;
        white-space: nowrap;
        padding: 10px 35px 10px 35px;
        color: #484848;
        border-radius: 0px 20px 20px 0px;
        -webkit-transition-duration: 0.45s;
        -moz-transition-duration: 0.45s;
        -o-transition-duration: 0.45s;
        transition-duration: 0.45s;
        transition-property: color;
        -webkit-transition-property: color;
        font-weight: 400;
        cursor: pointer;
    }

    .nav-link-close:hover {
        background-color: #000;
        color: #fff;
    }

    .nav-link-close:hover>a {
        color: #fff;
    }

    .nav-link-close>a {
        font-size: 12px;
        text-decoration: none;
        display: flex;
        justify-content: center;
        align-items: center;
    }
</style>



<!-- BARRA SUPERIOR -->
<nav class="navbar default-layout col-lg-12 col-12 p-0 fixed-top d-flex align-items-top flex-row flex-wrap" style="min-height: 150px;">
    <!--BOTON PARA DESPLEGAR MENÚ-->
    <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-start">
        <div class="me-3">
            <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-bs-toggle="minimize">
                <span class="icon-menu"></span>
            </button>
        </div>
        <div>
            <a class="navbar-brand brand-logo" href="index.php">
                <h3 class="welcome-text">Posmovil</h3>
            </a>
            <a class="navbar-brand brand-logo-mini" href="index.php">
                <img src="../portal/images/user.jpg" alt="logo" />
            </a>
        </div>
    </div>
    <div class="navbar-menu-wrapper d-flex align-items-top" style="border-bottom: 4px solid #ff7a21;">

        <ul class="navbar-nav">
            <li class="nav-item font-weight-semibold d-none d-lg-block ms-0">
                <div class="welcome-sub-text d-flex gap-3 justify-content-start align-items-center">
                    <h1 class="welcome-text fs-1"><span class="text-black fw-bold">Hola, <?php echo $nombre_usuario ?></span></h1>
                    <p class="badge-success-integra fs-6">Administrador</p>
                </div>
                <h3 class="welcome-sub-text"> ¡Bienvenido!</h3>
                <?php echo "<input type='hidden' id='nivel_usuario' value='$nivel_usuario' class='form-control'>"; ?>
            </li>
        </ul>
        <ul class="navbar-nav ms-auto">
            <!-- <div class="btn-notification">
                <div class="badge-btn-notification">
                    <p></p>
                </div>
                <i class='bx bx-bell btn-notifications-dropdown'></i>
                <div id="dropdown-notifications">
                </div>
            </div> -->

            <img src="../portal/images/user-sbg.png" alt="avatar" style="height: 110px; width: auto; margin-top:-23px">

            <?php
            echo "<input type='hidden' value='$usuario' id='nombre_usuario'>";
            echo "<input type='hidden' value='$nivel' id='nivel_usuario'>";
            ?>
        </ul>

        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-bs-toggle="offcanvas">
            <span class="mdi mdi-menu"></span>
        </button>
    </div>
</nav>


<!-- <script src="assets/vendor/jquery-2.1.4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js"></script>
<script src="custom/getNotificaciones.js"></script> -->


<!-- CONTENIDO CENTRAL -->
<div class="container-fluid page-body-wrapper" style="background-color: #F4F5F7">
    <div id="right-sidebar" class="settings-panel">
        <i class="settings-close ti-close"></i>
        <ul class="nav nav-tabs border-top" id="setting-panel" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="todo-tab" data-bs-toggle="tab" href="#todo-section" role="tab" aria-controls="todo-section" aria-expanded="true">TO DO LIST</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="chats-tab" data-bs-toggle="tab" href="#chats-section" role="tab" aria-controls="chats-section">CHATS</a>
            </li>
        </ul>
        <div class="tab-content" id="setting-content">
            <div class="tab-pane fade show active scroll-wrapper" id="todo-section" role="tabpanel" aria-labelledby="todo-section">
                <div class="add-items d-flex px-3 mb-0">
                    <form class="form w-100">
                        <div class="form-group d-flex">
                            <input type="text" class="form-control todo-list-input" placeholder="Add To-do">
                            <button type="submit" class="add btn btn-dark todo-list-add-btn" id="add-task">Add</button>
                        </div>
                    </form>
                </div>
                <div class="list-wrapper px-3">
                    <ul class="d-flex flex-column-reverse todo-list">
                        <li>
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input class="checkbox" type="checkbox">
                                    Team review meeting at 3.00 PM
                                </label>
                            </div>
                            <i class="remove ti-close"></i>
                        </li>
                        <li>
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input class="checkbox" type="checkbox">
                                    Prepare for presentation
                                </label>
                            </div>
                            <i class="remove ti-close"></i>
                        </li>
                        <li>
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input class="checkbox" type="checkbox">
                                    Resolve all the low priority tickets due today
                                </label>
                            </div>
                            <i class="remove ti-close"></i>
                        </li>
                        <li class="Completado">
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input class="checkbox" type="checkbox" checked>
                                    Schedule meeting for next week
                                </label>
                            </div>
                            <i class="remove ti-close"></i>
                        </li>
                        <li class="Completado">
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input class="checkbox" type="checkbox" checked>
                                    Project review
                                </label>
                            </div>
                            <i class="remove ti-close"></i>
                        </li>
                    </ul>
                </div>
                <h4 class="px-3 text-muted mt-5 fw-light mb-0">Events</h4>
                <div class="events pt-4 px-3">
                    <div class="wrapper d-flex mb-2">
                        <i class="ti-control-record text-dark me-2"></i>
                        <span>Feb 11 2018</span>
                    </div>
                    <p class="mb-0 font-weight-thin text-gray">Creating component page build a js</p>
                    <p class="text-gray mb-0">The total number of sessions</p>
                </div>
                <div class="events pt-4 px-3">
                    <div class="wrapper d-flex mb-2">
                        <i class="ti-control-record text-dark me-2"></i>
                        <span>Feb 7 2018</span>
                    </div>
                    <p class="mb-0 font-weight-thin text-gray">Meeting with Alisa</p>
                    <p class="text-gray mb-0 ">Call Sarah Graves</p>
                </div>
            </div>
            <!-- To do section tab ends -->
            <div class="tab-pane fade" id="chats-section" role="tabpanel" aria-labelledby="chats-section">
                <div class="d-flex align-items-center justify-content-between border-bottom">
                    <p class="settings-heading border-top-0 mb-3 pl-3 pt-0 border-bottom-0 pb-0">Friends</p>
                    <small class="settings-heading border-top-0 mb-3 pt-0 border-bottom-0 pb-0 pr-3 fw-normal">See All</small>
                </div>
                <ul class="chat-list">
                    <li class="list active">
                        <div class="profile"><img src="" alt="image"><span class="online"></span></div>
                        <div class="info">
                            <p>Thomas Douglas</p>
                            <p>Available</p>
                        </div>
                        <small class="text-muted my-auto">19 min</small>
                    </li>
                    <li class="list">
                        <div class="profile"><img src="" alt="image"><span class="offline"></span></div>
                        <div class="info">
                            <div class="wrapper d-flex">
                                <p>Catherine</p>
                            </div>
                            <p>Away</p>
                        </div>
                        <div class="badge badge-success badge-pill my-auto mx-2">4</div>
                        <small class="text-muted my-auto">23 min</small>
                    </li>
                    <li class="list">
                        <div class="profile"><img src="" alt="image"><span class="online"></span></div>
                        <div class="info">
                            <p>Daniel Russell</p>
                            <p>Available</p>
                        </div>
                        <small class="text-muted my-auto">14 min</small>
                    </li>
                    <li class="list">
                        <div class="profile"><img src="" alt="image"><span class="offline"></span></div>
                        <div class="info">
                            <p>James Richardson</p>
                            <p>Away</p>
                        </div>
                        <small class="text-muted my-auto">2 min</small>
                    </li>
                    <li class="list">
                        <div class="profile"><img src="" alt="image"><span class="online"></span></div>
                        <div class="info">
                            <p>Madeline Kennedy</p>
                            <p>Available</p>
                        </div>
                        <small class="text-muted my-auto">5 min</small>
                    </li>
                    <li class="list">
                        <div class="profile"><img src="" alt="image"><span class="online"></span></div>
                        <div class="info">
                            <p>Sarah Graves</p>
                            <p>Available</p>
                        </div>
                        <small class="text-muted my-auto">47 min</small>
                    </li>
                </ul>
            </div>
            <!-- chat tab ends -->
        </div>
    </div>

    <!--  MENU LATERAL-->
    <nav class="sidebar sidebar-offcanvas" id="sidebar" style="margin-top: 50px;">
        <ul class="nav">
            <!--ADMINISTRADOR-->
            <li class="nav-item">
                <a class="nav-link" href="../portal/index.php">
                    <i class='bx bx-home-smile left-panel-icons'></i>
                    <span class="menu-title">Dashboard</span>
                </a>
            </li>
            <li class="nav-item nav-category">MENU PRINCIPAL</li>
            <!--KNOWLEDGE-->
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#configuracion" aria-expanded="false" aria-controls="configuracion">
                    <i class='bx bx-brain left-panel-icons'></i>
                    <span class="menu-title">Configuración</span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="configuracion">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"><a class="nav-link" href="../portal/verCategorias.php">Categorías</a></li>
                    </ul>
                </div>
                <div class="collapse" id="configuracion">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"><a class="nav-link" href="../portal/verPagos.php">Tipos de pago</a></li>
                    </ul>
                </div>
                <div class="collapse" id="configuracion">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"><a class="nav-link" href="../portal/verMotivosSalida.php">Motivos de salida</a></li>
                    </ul>
                </div>
                <div class="collapse" id="configuracion">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"><a class="nav-link" href="../portal/verMretiros.php">Motivos de retiro</a></li>
                    </ul>
                </div>
            </li>

            <!--EMPRESAS-->
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#empresas" aria-expanded="false" aria-controls="charts">
                    <i class='bx bx-buildings left-panel-icons'></i>
                    <span class="menu-title">Empresas</span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="empresas">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"> <a class="nav-link" href="../portal/verEmpresas.php">Mi Empresa</a></li>
                    </ul>
                </div>
            </li>

            <!--USUARIOS-->
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#usuarios" aria-expanded="false" aria-controls="charts">
                    <i class='bx bx-user left-panel-icons'></i>
                    <span class="menu-title">Usuarios</span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="usuarios">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"> <a class="nav-link" href="../portal/verUsuarios.php">ABC Usuarios</a></li>
                    </ul>
                </div>
            </li>

            <!--SUCURSALES-->
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#sucursales" aria-expanded="false" aria-controls="charts">
                    <i class='bx bx-store left-panel-icons'></i>
                    <span class="menu-title">Sucursales</span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="sucursales">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"> <a class="nav-link" href="../portal/verSucursales.php">ABC Sucursales</a></li>
                    </ul>
                </div>
            </li>

            <!--CLIENTES-->
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#clientes" aria-expanded="false" aria-controls="charts">
                    <i class='bx bx-map left-panel-icons'></i>
                    <span class="menu-title">Clientes y Rutas</span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="clientes">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"> <a class="nav-link" href="../portal/verClientes.php">ABC Clientes</a></li>
                    </ul>
                </div>
                <div class="collapse" id="clientes">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"> <a class="nav-link" href="../portal/verRutas.php">ABC Rutas</a></li>
                    </ul>
                </div>
            </li>

            <!--PROVEEDORES-->
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#proveedores" aria-expanded="false" aria-controls="charts">
                    <i class='bx bx-group left-panel-icons'></i>
                    <span class="menu-title">Proveedores</span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="proveedores">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"> <a class="nav-link" href="../portal/verProveedores.php">ABC Proveedores</a></li>
                    </ul>
                </div>
            </li>

            <!--PRODUCTOS-->
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#productos" aria-expanded="false" aria-controls="charts">
                    <i class='bx bx-shopping-bag left-panel-icons'></i>
                    <span class="menu-title">Productos</span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="productos">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"> <a class="nav-link" href="../portal/verProductos.php">ABC Productos</a></li>
                    </ul>
                </div>
                <div class="collapse" id="productos">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"> <a class="nav-link" href="../portal/verPresentaciones.php">ABC Presentaciones</a></li>
                    </ul>
                </div>
            </li>

            <!--ALMACEN-->
            <!-- <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#almacen" aria-expanded="false" aria-controls="ventas">
                    <i class='bx bx-package left-panel-icons'></i>
                    <span class="menu-title">Almacén</span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="almacen">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"> <a class="nav-link" href="../portal/verExistencias.php">Existencias</a></li>
                    </ul>
                </div>
                <div class="collapse" id="almacen">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"> <a class="nav-link" href="../portal/verInventario.php">Registro de inventario</a></li>
                    </ul>
                </div>
                <div class="collapse" id="almacen">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"> <a class="nav-link" href="../portal/verMovimientos.php">Bitácora de movimientos</a></li>
                    </ul>
                </div>
                <div class="collapse" id="almacen">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"> <a class="nav-link" href="../portal/verCompras.php">Historial de Compras</a></li>
                    </ul>
                </div>
                <div class="collapse" id="almacen">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"> <a class="nav-link" href="../portal/verEntradas.php">Entradas desde compras</a></li>
                    </ul>
                </div>
                <div class="collapse" id="almacen">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"> <a class="nav-link" href="../portal/verEntradasD.php">Entradas directas</a></li>
                    </ul>
                </div>
                <div class="collapse" id="almacen">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"> <a class="nav-link" href="../portal/verSalidasAlmacen.php">Salidas directas</a></li>
                    </ul>
                </div>
                <div class="collapse" id="almacen">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"> <a class="nav-link" href="../portal/verTransferencias.php">Transferencias</a></li>
                    </ul>
                </div>
                <div class="collapse" id="almacen">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"> <a class="nav-link" href="../portal/verPrestamos.php">Prestamos</a></li>
                    </ul>
                </div>
            </li> -->

            <!--PUNTO DE VENTA-->
            <!-- <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#ventas" aria-expanded="false" aria-controls="reportes">
                    <i class='bx bx-cart-alt left-panel-icons'></i>
                    <span class="menu-title">Punto de venta</span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="ventas">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"> <a class="nav-link" href="../portal/puntoVenta.php">Punto de venta</a></li>
                    </ul>
                </div>
                <div class="collapse" id="ventas">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"> <a class="nav-link" href="../portal/verVentas.php">Historial de ventas</a></li>
                    </ul>
                </div>
                <div class="collapse" id="ventas">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"> <a class="nav-link" href="../portal/verVentasWeb.php">Historial de ventas web</a></li>
                    </ul>
                </div>
                <div class="collapse" id="ventas">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"> <a class="nav-link" href="../portal/verCotizaciones.php">Historial de cotizaciones</a></li>
                    </ul>
                </div>
                <div class="collapse" id="ventas">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"> <a class="nav-link" href="../portal/verDevoluciones.php">Historial de devoluciones</a></li>
                    </ul>
                </div>
                <div class="collapse" id="ventas">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"> <a class="nav-link" href="../portal/verFacturasHistorial.php">Historial de facturas</a></li>
                    </ul>
                </div>
                <div class="collapse" id="ventas">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"> <a class="nav-link" href="../portal/validarPagos.php">Validador de pagos</a></li>
                    </ul>
                </div>
                <div class="collapse" id="ventas">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"> <a class="nav-link" href="../portal/verCortes.php">Cortes de caja</a></li>
                    </ul>
                </div>
                <div class="collapse" id="ventas">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"> <a class="nav-link" href="../portal/verRetiros.php">Retiros de caja</a></li>
                    </ul>
                </div>
            </li> -->

            <!--REPORTES-->
            <!-- <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#reportes" aria-expanded="false" aria-controls="sitios">
                    <i class='bx bxs-report left-panel-icons'></i>
                    <span class="menu-title">Reportes</span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="reportes">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"> <a class="nav-link" href="../portal/reporteInventario.php">Reporte de inventario</a></li>
                    </ul>
                </div>
                <div class="collapse" id="reportes">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"> <a class="nav-link" href="../portal/reporteVentas.php">Reporte de ventas</a></li>
                    </ul>
                </div>
                <div class="collapse" id="reportes">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"> <a class="nav-link" href="../portal/reporteVentasDetalle.php">Reporte de ventas detalle</a></li>
                    </ul>
                </div>
                <div class="collapse" id="reportes">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"> <a class="nav-link" href="../portal/reporteUtilidades.php">Reporte de utilidades</a></li>
                    </ul>
                </div>
                <div class="collapse" id="reportes">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"> <a class="nav-link" href="../portal/reporteCompras.php">Reporte de compras</a></li>
                    </ul>
                </div>
                <div class="collapse" id="reportes">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"> <a class="nav-link" href="../portal/reporteMovimientos.php">Reporte de movimientos</a></li>
                    </ul>
                </div>
                <div class="collapse" id="reportes">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"> <a class="nav-link" href="../portal/reporteGastos.php">Reporte de gastos</a></li>
                    </ul>
                </div>
                <div class="collapse" id="reportes">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"> <a class="nav-link" href="../portal/reporteTraspasos.php">Reporte de traspasos</a></li>
                    </ul>
                </div>
                <div class="collapse" id="reportes">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"> <a class="nav-link" href="../portal/reportePrecios.php">Reporte de lista de precios</a></li>
                    </ul>
                </div>
                <div class="collapse" id="reportes">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"> <a class="nav-link" href="../portal/reporteListaPrecios.php">Reporte de precios a clientes</a></li>
                    </ul>
                </div>
            </li> -->

            <!--CERRAR SESION-->
            <li class="nav-item">
                <a class="nav-link" href="../index.php">
                    <i class='bx bx-log-out left-panel-icons'></i>
                    <span class="menu-title">Cerrar sesion</span>
                </a>
            </li>
        </ul>
    </nav>


    <script src="vendors/js/vendor.bundle.base.js"></script>
    <script src="vendors/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <script src="js/off-canvas.js"></script>
    <script src="js/hoverable-collapse.js"></script>
    <script src="js/template.js"></script>
    <script src="js/settings.js"></script>
    <script src="js/todolist.js"></script>
