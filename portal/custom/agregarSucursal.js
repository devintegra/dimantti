var latitud = 21.1218994;
var longitud = -101.7360523;
var link = 'https://www.google.com/maps/?q=21.1218994,-101.7360523';
var mu;
var $ = jQuery;
var zipcode = 37000;


$(document).ready(function () {
    $("#telefono").mask("(999) 999-9999");
    autocompletar();
});



//MAPA
//#region
function autocompletar() {

    // alert("hola");

    var mapOptions = {
        center: new google.maps.LatLng(21.1218994, -101.7360523),
        zoom: 16,
        zoomControl: false,
        mapTypeControl: false,
        streetViewControl: false


    };

    map = new google.maps.Map(document.getElementById("mapa"), mapOptions);

    //alert(map.getCenter());

    var muOptions = {
        position: new google.maps.LatLng(21.1218994, -101.7360523),
        map: map,
        title: "Mi ubicacion",
        draggable: true
    };

    mu = new google.maps.Marker(muOptions);
    var geocoder = new google.maps.Geocoder();

    google.maps.event.addListener(mu, 'dragend', function () {
        drag = 1;

        geocoder.geocode({ 'latLng': mu.getPosition() }, function (results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                //alert("hola");
                if (results[0]) {

                    var address = results[0].formatted_address;
                    $('#direccion').val(address);
                    //zipcode=results[0].address_components[6].short_name;

                }
            }
            else {
                alert('Geocode was not successful for the following reason: ' + status);
            }

        });

    });



    autocomplete = new google.maps.places.Autocomplete(
      /** @type {!HTMLInputElement} */(document.getElementById('direccion')),
        { types: ['geocode'] });

    autocomplete.addListener('place_changed', poscionarMarcador);


}


function poscionarMarcador() {
    var address = $('#direccion').val();
    var geocoder = new google.maps.Geocoder();
    geocoder.geocode({ 'address': address }, geocodeResult);
}


function geocodeResult(results, status) {
    // Verificamos el estatus
    if (status == 'OK') {
        //zipcode=results[0].address_components[6].short_name;
        drag = 1;
        mu.setMap(null);

        map.setCenter(results[0].geometry.location);


        var muOptions = {
            position: results[0].geometry.location,
            map: map,
            title: "Mi ubicacion",
            draggable: true
        };

        mu = new google.maps.Marker(muOptions);

        var geocoder = new google.maps.Geocoder();

        google.maps.event.addListener(mu, 'dragend', function () {
            drag = 1;

            geocoder.geocode({ 'latLng': mu.getPosition() }, function (results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    //alert("hola");
                    if (results[0]) {

                        var address = results[0].formatted_address;
                        $('#direccion').val(address);
                        //zipcode=results[0].address_components[6].short_name;




                    }
                }
                else {
                    alert('Geocode was not successful for the following reason: ' + status);
                }

            });

        });
        //

        // Si hay resultados encontrados, centramos y repintamos el mapa
        // esto para eliminar cualquier pin antes puesto

        // Dibujamos un marcador con la ubicación del primer resultado obtenido

    } else {
        // En caso de no haber resultados o que haya ocurrido un error
        // lanzamos un mensaje con el error
        alert("Geocoding no tuvo éxito debido a: " + status);
    }
}
//#endregion




//MOTIVOS DE RETIRO
//#region
$("#buscar").click(function () {
    $("#modalMotivos").modal('show');
});


$(document).on('click', '.fp', function () {

    var id = this.id;
    if ($('#entradas').find('tr#' + id).length == 0) {

        var nombre = $(this).closest('tr').find('td').eq('0').text().trim();

        var contenido = "<tr id='" + id + "' class='view'><td><i id='" + id + "' class='bx bx-trash eliminar' style='padding: 3px; background-color: red; color:white; cursor:pointer;'></i></td><td style='white-space: normal' class='view-content'>" + nombre + "</td></tr>";
        $("#entradas tbody").append(contenido);

        $('.eliminar').click(function () {
            var idpp = this.id;
            $("#entradas tbody tr").each(function () {
                if (idpp == this.id) {
                    $(this).remove();
                }
            });
        });

        $('#modalMotivos').modal('hide');

    } else {

        swal("Advertencia", "El motivo de gasto ya existe en esta sucursal, favor de ingresar uno diferente", "info");

    }

});


function getMotivos() {
    var data = [];
    $("#entradas tbody tr").each(function (index) {

        var idp, clave, nombre;

        var idp = parseInt($(this).attr('id'));

        $(this).children("td").each(function (index2) {

            switch (index2) {

                case 1:
                    nombre = $(this).text().trim();
                    break;

            }

        });

        var myObj = { "fk_retiro": idp, "nombre": nombre };

        data.push(myObj);

    });

    return data;
}
//#endregion




//ALMACENES
//#region
function validarAlmacenes() {
    var retorno = true;

    if ($('#almacensc').val().length < 1) {
        retorno = false;
        $('#almacensc').css('background-color', '#ffdddd');
    } else {
        $('#almacensc').css('background-color', '#fff');
    }

    return retorno;

}


$('#agregar').click(function () {

    if (validarAlmacenes()) {

        var nombre = $("#almacensc").val();
        var descripcion = $("#descripcion_almacen").val();

        var contenido = "<tr><td><i class='bx bx-trash eliminarAlmacen' style='padding: 3px; background-color: red; color:white; cursor:pointer;'></i></td><td style='white-space: normal'>" + nombre + "</td><td style='white-space: normal'>" + descripcion + "</td></tr>";
        $("#entradasAlmacen tbody").append(contenido);

        $('.eliminarAlmacen').click(function () {

            $(this).closest('tr').remove();

        });

        $("#almacensc").val("");
        $("#descripcion_almacen").val("");

    }

});


function getAlmacenes() {
    var data = [];

    var nombre, descripcion;

    $("#entradasAlmacen tbody tr").each(function (index) {

        $(this).children("td").each(function (index2) {

            switch (index2) {

                case 1:
                    nombre = $(this).text().trim();
                    break;

                case 2:
                    descripcion = $(this).text().trim();
                    break;

            }

        });

        var myObj = { "nombre": nombre, "descripcion": descripcion };

        data.push(myObj);

    });

    return data;
}
//#endregion




//GUARDAR
//#region
function validar() {
    var retorno = true;
    var regex = /[\w-\.]{2,}@([\w-]{2,}\.)*([\w-]{2,}\.)[\w-]{2,4}/;

    if ($('#nombre').val().length < 2) {
        retorno = false;
        $('#nombre').css('background-color', '#ffdddd');
    }

    if ($("#correo").val().length > 0) {

        if (!regex.test($('#correo').val().trim())) {
            retorno = false;
            $('#correo').css('background-color', '#ffdddd');
        }
    }


    if ($('#direccion').val().length < 4) {
        retorno = false;
        $('#direccion').css('background-color', '#ffdddd');
    }


    if ($('#clave').val().length != 3) {
        retorno = false;
        $('#clave').css('background-color', '#ffdddd');
    }


    return retorno;

}


$('#guardar').click(function () {

    if (validar()) {

        $.LoadingOverlay("show", {
            color: "rgba(255, 255, 255, 0)"
        });

        latitud = mu.getPosition().lat();
        longitud = mu.getPosition().lng();


        var parametros = {
            "nombre": $("#nombre").val(),
            "direccion": $("#direccion").val(),
            "latitud": latitud,
            "longitud": longitud,
            "telefono": $("#telefono").val(),
            "correo": $("#correo").val(),
            "clave": $("#clave").val(),
            "motivos": getMotivos(),
            "almacenes": getAlmacenes()
        };

        $.ajax({
            data: JSON.stringify(parametros),

            url: 'servicios/agregarSucursal.php',

            type: 'post',

            dataType: 'json',

            contentType: "application/json; charset=utf-8",

            beforeSend: function () {

            },

            success: function (response) {

                $.LoadingOverlay("hide");

                if (response.codigo == 200) {

                    swal("Guardado exitoso", "El registro se guardó correctamente", "success").then(function () {
                        $(location).attr('href', "verSucursales.php");
                    });

                } else {

                    swal("Error", response.descripcion, "error").then(function () {
                        location.reload();
                    });

                }

            },
            error: function (arg1, arg2, arg3) {
                console.log(arg3);
            }
        });

    }

});


$(function () {
    $("#formuploadajax").on("submit", function (e) {
        e.preventDefault();

        if (validar()) {

            $.LoadingOverlay("show", {
                color: "rgba(255, 255, 255, 0)"
            });

            latitud = mu.getPosition().lat();
            longitud = mu.getPosition().lng();


            var f = $(this);
            var formData = new FormData(document.getElementById("formuploadajax"));
            formData.append("nombre", $("#nombre").val());
            formData.append("direccion", $('#direccion').val());
            formData.append("latitud", latitud);
            formData.append("longitud", longitud);
            formData.append("telefono", $('#telefono').val());
            formData.append("correo", $('#correo').val());
            formData.append("clave", $('#clave').val());

            $.ajax({
                url: "servicios/agregarSucursal.php",
                type: "post",
                dataType: "html",
                data: formData,
                cache: false,
                contentType: false,
                processData: false,

                beforeSend: function () {

                },

                success: function (response) {

                    response = JSON.parse(response);

                    $.LoadingOverlay("hide");

                    if (response.codigo == 200) {

                        swal("Guardado exitoso", "El registro se guardó correctamente", "success").then(function () {
                            $(location).attr('href', "verSucursales.php");
                        });

                    }
                    else {

                        swal("Error", response.descripcion, "error").then(function () {
                            location.reload();
                        });

                    }

                },

                error: function (arg1, arg2, arg3) {
                    alert(arg3);
                }

            });


        }



    });
});
//#endregion



//EXTRAS
//#region
$('#salir').click(function () {
    $(location).attr("href", "index.php");
});
//#endregion
