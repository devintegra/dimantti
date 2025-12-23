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


//MAPA
//#region
function initialize() {

    $.ajax({

        url: 'servicios/obtenerUbicaciones.php',

        type: 'GET',

        beforeSend: function () {

        },

        success: function (response) {

            for (let value of Object.values(response.objList)) {

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

                marker.addListener('click', function () {
                    infowindow.open(map, marker);
                });

            }

            map.setCenter(pos);

        },
        error: function (arg1, arg2, arg3) {
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

        url: 'servicios/obtenerUbicacion.php',

        type: 'GET',

        beforeSend: function () {

        },

        success: function (response) {

            map.panTo(new google.maps.LatLng(response.objList.latitud, response.objList.longitud));
            transition(response.objList.latitud, response.objList.longitud);

        },
        error: function (arg1, arg2, arg3) {
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
//#endregion



//REACTIVAR ALMACEN
//#region
function confirmar(id) {
    Swal.fire({
        title: 'Reactivar almacen',
        text: "¿Estás seguro que deseas reactivar el almacen?",
        icon: 'warning',
        showCancelButton: true,
        iconColor: '#daa745',
        confirmButtonColor: '#daa745',
        cancelButtonColor: '#000',
        confirmButtonText: 'Si',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            reactivarAlmacen(id);
        }
    })
}


function reactivarAlmacen(id) {

    var parametros = {
        "pk_almacen": id
    };

    $.ajax({
        data: parametros,

        url: 'servicios/editarAlmacenReactivar.php',

        type: 'POST',

        beforeSend: function () {

        },

        success: function (response) {

            if (response.codigo == 200) {
                swal("Exito", "Se ha reactivado el almacen", "success").then(function () {
                    location.reload();
                });
            } else {
                swal("Error", "Hubo un problema, vuelva a intentarlo", "error").then(function () {
                    location.reload();
                });
            }

        },
        error: function (arg1, arg2, arg3) {
            console.log(arg3);
        }
    });

}
//#endregion
