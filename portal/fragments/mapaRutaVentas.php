<?php

?>


<div id="mapa" style="width: 100%; height: 100%">
</div>


<script type="text/javascript" src="https://maps.google.com/maps/api/js?key=AIzaSyAyzxxx9sOTmkGCHGgrK_Xy86eQxB-AxuI&libraries=places"></script>
<script>
    var mu, latitud, longitud;
    var $ = jQuery;

    var map;
    var posi;
    var marker;
    var numDeltas = 100;
    var delaya = 10; //milliseconds
    var i = 0;
    var deltaLat;
    var deltaLng;
    var estatus;
    var delay = 15000;
    var paso = 0;
    var pk_salida = 0;


    $(document).ready(function() {

        var mapOptions = {
            zoom: 12,
            zoomControl: false,
            fullscreenControl: false,
            mapTypeControl: false,
            scaleControl: false,
            streetViewControl: false,
            rotateControl: false
        };

        map = new google.maps.Map(document.getElementById('mapa'), mapOptions);
        initialize();

    });


    function initialize() {

        $.ajax({

            url: 'servicios/getUbicaciones.php',

            type: 'GET',

            beforeSend: function() {

            },

            success: function(response) {

                for (let value of Object.values(response.objList)) {

                    console.log(value.latitud + "," + value.longitud);
                    var eltotal = "$" + value.total;

                    var pos = new google.maps.LatLng(value.latitud, value.longitud);

                    marker = new google.maps.Marker({
                        position: pos,
                        map: map,
                        title: value.total,
                        label: {
                            text: eltotal
                        }
                    });

                    marker.addListener('click', function() {
                        infowindow.open(map, marker);
                    });

                }

                map.setCenter(pos);

            },
            error: function(arg1, arg2, arg3) {
                console.log(arg3);
            }
        });

    }


    function obtenerUbicacion() {

        var parametros = {
            "usuario": $("#usuario").val()
        };

        $.ajax({
            data: parametros,

            url: 'servicios/getUbicacion.php',

            type: 'GET',

            beforeSend: function() {

            },

            success: function(response) {

                map.panTo(new google.maps.LatLng(response.objList.latitud, response.objList.longitud));
                transition(response.objList.latitud, response.objList.longitud);

            },
            error: function(arg1, arg2, arg3) {
                console.log(arg3);
            }
        });
    }


    function transition(latitud, longitud) {
        i = 0;
        var coords = new google.maps.LatLng(latitud, longitud);
        deltaLat = (coords.lat() - posi[0]) / numDeltas;
        deltaLng = (coords.lng() - posi[1]) / numDeltas;
        moveMarker();
    }


    function moveMarker() {
        posi[0] += deltaLat;
        posi[1] += deltaLng;
        var latlng = new google.maps.LatLng(posi[0], posi[1]);
        marker.setPosition(latlng);
        if (i != numDeltas) {
            i++;
            setTimeout(moveMarker, delaya);
        }
    }
</script>
