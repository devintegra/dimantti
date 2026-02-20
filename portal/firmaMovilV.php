<?php

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
}

if (isset($_GET['ph']) && is_string($_GET['ph'])) {
    $ph = $_GET['ph'];
}

?>

<!DOCTYPE html>

<html>

<head>
    <title>Dimantti - Consola de administración</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <link rel="stylesheet" href="custom/customform.css">
    <link rel="stylesheet" href="assets/css/font-awesome.min.css">

</head>

<body style="max-width: 100%; overflow: hidden">

    <canvas id="canvas" style="border: 1px ridge #000; width: 100%; height: 50%"></canvas>
    <div id="go" style="display:none">[ CLICK/TAP TO DRAW ]</div>
    <form method="post" accept-charset="utf-8" name="form1">
        <input name="hidden_data" id='hidden_data' type="hidden" />
        <?php
        echo "<input type='hidden' id='pk_guia' name='pk_guia' value='$id'>"
        ?>
    </form>
    <ul style="font-size: 1em">
        <li>Confirmo que recibí mi accesorio</li>

    </ul>
    <button id="guardar" class="btn btn-danger" onclick="limpiar()" style="font-weight: 100!important; font-size: 16px; width: 28%">Limpiar</button>
    <button id="guardar" class="btn btn-success" onclick="guardar()" style="font-weight: 100!important; font-size: 16px; width: 70%">Guardar</button>

    <div class="modal" id="exito" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
        <div class="modal-dialog modal-md">

            <div class="modal-content">
                <div class="modal-header text-center">
                    <h2 class="text-center exitot">Venta <span id="nentrada"></span> </h2>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <?php echo "  <a target='_blank' id='pdf' href='ventaPDF.php?id=$id&ph=$ph' class='btn btn-danger pdfb'><i class='fa fa-file-pdf-o fa-5x' aria-hidden='true'></i><br><br>DESCARGAR ORDEN</a>"; ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer text-center">
                    <div class="col-sm-12">
                        <button onclick="nueva()" id="nueva" type="button" class="btn btn-sm btn-info">Generar Nueva Orden</button>
                        <button onclick="inicio()" id="inicio" type="button" class="btn btn-sm btn-success">Inicio</button>
                    </div>
                </div>
            </div>
        </div>
    </div>







    <script src="assets/vendor/jquery-2.1.4.min.js"></script>
    <script src="assets/vendor/bootstrap.min.js"></script>
    <script>
        function $(el) {
            return document.getElementById(el.replace(/#/, ''));
        };
        var canvas = $('#canvas');
        canvas.width = document.body.clientWidth;
        canvas.height = 330;
        var context = canvas.getContext('2d');
        var start = function(coors) {
            context.beginPath();
            context.moveTo(coors.x, coors.y);
            this.isDrawing = true;
        };
        var move = function(coors) {
            if (this.isDrawing) {
                context.strokeStyle = "#000";
                context.lineJoin = "round";
                context.lineWidth = 3;
                context.lineTo(coors.x, coors.y);
                context.stroke();
            }
        };
        var stop = function(coors) {
            if (this.isDrawing) {
                this.touchmove(coors);
                this.isDrawing = false;
            }
        };
        var drawer = {
            isDrawing: false,
            mousedown: start,
            mousemove: move,
            mouseup: stop,
            touchstart: start,
            touchmove: move,
            touchend: stop
        };
        var draw = function(e) {
            var coors = {
                x: e.clientX || e.targetTouches[0].pageX,
                y: e.clientY || e.targetTouches[0].pageY
            };
            drawer[e.type](coors);
        }
        canvas.addEventListener('mousedown', draw, false);
        canvas.addEventListener('mousemove', draw, false);
        canvas.addEventListener('mouseup', draw, false);
        canvas.addEventListener('touchstart', draw, false);
        canvas.addEventListener('touchmove', draw, false);
        canvas.addEventListener('touchend', draw, false);

        var go = function(e) {
            this.parentNode.removeChild(this);
            draw(e);
        };

        $('#go').addEventListener('mousedown', go, false);
        $('#go').addEventListener('touchstart', go, false);

        // prevent elastic scrolling
        document.body.addEventListener('touchmove', function(e) {
            e.preventDefault();
        }, false);
        // end body:touchmove
        window.onresize = function(e) {
            canvas.width = document.body.clientWidth;
            canvas.height = document.body.clientHeight;
        };


        function guardar() {
            var dataURL = canvas.toDataURL("image/png");
            document.getElementById('hidden_data').value = dataURL;
            var fd = new FormData(document.forms["form1"]);

            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'servicios/upload_datav.php', true);

            xhr.upload.onprogress = function(e) {
                if (e.lengthComputable) {
                    var percentComplete = (e.loaded / e.total) * 100;
                    console.log(percentComplete + '% uploaded');
                    jQuery('#exito').modal('show');
                }
            };

            xhr.onload = function() {

            };
            xhr.send(fd);

        }

        function limpiar() {

            location.reload();
        }


        function inicio() {
            jQuery(location).attr("href", "index.php");
        }


        function nueva() {
            jQuery(location).attr("href", "verOrdenes.php");
        }


        function mod() {
            alert("Hola");
            jQuery('#exito').modal('show');
        }

        function fotos() {
            var pk_guia = jQuery("#pk_guia").val();
            jQuery(location).attr("href", "fotos.php?id=" + pk_guia);
        }
    </script>

</body>

</html>
