var $ = jQuery;
var latitud = 21.1218994;
var longitud = -101.7360523;
var link = 'https://www.google.com/maps/?q=21.1218994,-101.7360523';
var mu;
var zipcode = 37000;


$(document).ready(function () {
    $("#telefono").mask("(999) 999-9999");
    creditoInputDisabled($("#abonos").val());
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



//GUARDAR
//#region
function validar() {
    var retorno = true;
    var regex = /[\w-\.]{2,}@([\w-]{2,}\.)*([\w-]{2,}\.)[\w-]{2,4}/;


    if ($('#nombre').val().length < 2) {
        retorno = false;
        $('#nombre').css('background-color', '#ffdddd');
    }

    if ($("#correo").val() != '') {
        if (!regex.test($('#correo').val().trim())) {
            retorno = false;
            $('#correo').css('background-color', '#ffdddd');
        }
    }

    if ($('#abonos').val() == 0) {
        retorno = false;
        $('#abonos').css('background-color', '#ffdddd');
    }

    if ($('#abonos').val() == 1) {
        if ($('#dias_credito').val().length < 1) {
            retorno = false;
            $('#dias_credito').css('background-color', '#ffdddd');
        }
        if ($('#limite_credito').val().length < 1) {
            retorno = false;
            $('#limite_credito').css('background-color', '#ffdddd');
        }
        if ($('#credito').val().length < 1) {
            retorno = false;
            $('#credito').css('background-color', '#ffdddd');
        }
    }

    if (parseFloat($("#credito").val()) > parseFloat($("#limite_credito").val())) {
        retorno = false;
        swal('Mensaje', 'El crédito disponible no puede ser mayor al límite del crédito', 'info');
    }

    if ($("#pass").val().length > 0) {
        if ($('#pass').val().length < 5) {
            retorno = false;
            $('#pass').css('background-color', '#ffdddd');
        }
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
        var pass = btoa($("#pass").val());

        var parametros = {
            "nombre": $("#nombre").val(),
            "telefono": $("#telefono").val(),
            "correo": $("#correo").val(),
            "dias_credito": $("#dias_credito").val(),
            "limite_credito": $("#limite_credito").val().replace(/,/g, ""),
            "credito": $("#credito").val().replace(/,/g, ""),
            "abonos": $("#abonos").val(),
            "fk_categoria": $("#categoria").val(),
            "cp": $("#cp").val(),
            "rfc": $("#rfc").val(),
            "tipo": 1,
            "fk_regimen_fiscal": $("#regimen_fiscal").val(),
            "usuario": $("#usuario").val(),
            "pass": pass,
            "direccion": $("#direccion").val(),
            "latitud": latitud,
            "longitud": longitud
        };

        $.ajax({
            data: parametros,

            url: 'servicios/agregarCliente.php',

            type: 'POST',

            contentType: "application/x-www-form-urlencoded;charset=utf-8",

            beforeSend: function () {

            },

            success: function (response) {

                $.LoadingOverlay("hide");

                if (response.codigo == 200) {

                    swal("Guardado exitoso", "El registro se guardó correctamente", "success").then(function () {
                        $(location).attr('href', "verClientes.php");
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
//#endregion



//EXTRAS
//#region
$(document).on('change', '#abonos', function () {

    var option = $(this).val();
    creditoInputDisabled(option);

});


function creditoInputDisabled(option) {
    if (option == 1) {
        $("#dias_credito").removeAttr('disabled');
        $("#limite_credito").removeAttr('disabled');
        $("#credito").removeAttr('disabled');
    } else if (option == 2 || option == 0) {
        $("#dias_credito").attr('disabled', 'disabled');
        $("#limite_credito").attr('disabled', 'disabled');
        $("#credito").attr('disabled', 'disabled');
    }
}


$("#credito, #limite_credito").on("input", function () {
    $(this).val(currencyMX($(this).val()));
});
//#endregion
