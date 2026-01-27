var $ = jQuery;
var latitud;
var longitud;
var link = 'https://www.google.com/maps/?q=21.1218994,-101.7360523';
var mu;


$(document).ready(function () {

    $("#telefono").mask("(999) 999-9999");
    $("#limite_credito").val(currencyMX($("#limite_credito").val()));
    $("#credito").val(currencyMX($("#credito").val()));

    latitud = $("#latitud").val();
    longitud = $("#longitud").val();

    creditoInputDisabled($("#abonos").val());

    autocompletar();

});



//MAPA
//#region
function autocompletar() {

    // alert("hola");

    var mapOptions = {
        center: new google.maps.LatLng(latitud, longitud),
        zoom: 16,
        zoomControl: false,
        mapTypeControl: false,
        streetViewControl: false


    };

    map = new google.maps.Map(document.getElementById("mapa"), mapOptions);

    //alert(map.getCenter());

    var muOptions = {
        position: new google.maps.LatLng(latitud, longitud),
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
                    latitud = mu.getPosition().lat();
                    longitud = mu.getPosition().lng();

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
    latitud = mu.getPosition().lat();
    longitud = mu.getPosition().lng();
}


function geocodeResult(results, status) {
    // Verificamos el estatus
    if (status == 'OK') {

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
        latitud = mu.getPosition().lat();
        longitud = mu.getPosition().lng();
        var geocoder = new google.maps.Geocoder();

        google.maps.event.addListener(mu, 'dragend', function () {
            drag = 1;

            geocoder.geocode({ 'latLng': mu.getPosition() }, function (results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    //alert("hola");
                    if (results[0]) {

                        var address = results[0].formatted_address;
                        $('#direccion').val(address);

                        latitud = mu.getPosition().lat();
                        longitud = mu.getPosition().lng();




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

    if ($('#fk_ruta').val() == 0) {
        retorno = false;
        $('#fk_ruta').css('background-color', '#ffdddd');
    }

    if ($('.chkDia:checked').length == 0) {
        retorno = false;
        swal('Mensaje', 'Selecciona por lo menos un día', 'info');
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

    if (parseFloat($("#credito").val().replace(/,/g, "")) > parseFloat($("#limite_credito").val().replace(/,/g, ""))) {
        retorno = false;
        swal('Mensaje', 'El crédito disponible no puede ser mayor al límite del crédito', 'info');
    }

    return retorno;

}


$('#guardar').click(function () {

    if (validar()) {

        let lunes = $('#lunes').is(':checked') ? 1 : 0;
        let martes = $('#martes').is(':checked') ? 1 : 0;
        let miercoles = $('#miercoles').is(':checked') ? 1 : 0;
        let jueves = $('#jueves').is(':checked') ? 1 : 0;
        let viernes = $('#viernes').is(':checked') ? 1 : 0;
        let sabado = $('#sabado').is(':checked') ? 1 : 0;
        let domingo = $('#domingo').is(':checked') ? 1 : 0;

        var parametros = {
            "pk_cliente": $("#pk_cliente").val(),
            "nombre": $("#nombre").val(),
            "telefono": $("#telefono").val(),
            "correo": $("#correo").val(),
            "cp": $("#cp").val(),
            "rfc": $("#rfc").val(),
            "fk_regimen_fiscal": $("#regimen_fiscal").val(),
            "fk_ruta": $("#fk_ruta").val(),
            "lunes": lunes,
            "martes": martes,
            "miercoles": miercoles,
            "jueves": jueves,
            "viernes": viernes,
            "sabado": sabado,
            "domingo": domingo,
            "direccion": $("#direccion").val(),
            "latitud": latitud,
            "longitud": longitud,
            "dias_credito": $("#dias_credito").val(),
            "limite_credito": $("#limite_credito").val().replace(/,/g, ""),
            "credito": $("#credito").val().replace(/,/g, ""),
            "abonos": $("#abonos").val(),
            "fk_categoria": $("#categoria").val()
        };

        $.ajax({
            data: parametros,

            url: 'servicios/editarCliente.php',

            type: 'POST',

            contentType: "application/x-www-form-urlencoded;charset=utf-8",

            beforeSend: function () {

            },

            success: function (response) {

                if (response.codigo == 200) {

                    swal("Guardado exitoso", "El registro se guardó correctamente", "success").then(function () {
                        $(location).attr('href', "verClientes.php");
                    });

                }
                else {
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



//ELIMINAR
//#region
$("#eliminar").click(function () {
    Swal.fire({
        title: 'Eliminar cliente',
        text: "¿Estás seguro que deseas eliminar al cliente?",
        icon: 'warning',
        showCancelButton: true,
        iconColor: '#368FCD',
        confirmButtonColor: '#368FCD',
        cancelButtonColor: '#000',
        confirmButtonText: 'Si, borrar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            eliminar();
        }
    })
});


function eliminar() {

    var parametros = {
        "pk_cliente": $("#pk_cliente").val()
    };

    $.ajax({
        data: parametros,

        url: 'servicios/eliminarCliente.php',

        type: 'POST',

        beforeSend: function () {

        },

        success: function (response) {

            if (response.codigo == 200) {

                swal("Eliminacion exitosa", "El registro se eliminó correctamente", "success").then(function () {
                    $(location).attr('href', "verClientes.php");
                });

            }
            else {
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
//#endregion



//EXTRAS
//#region
$('#ver_password').click(function () {
    var tipo = document.getElementById("pass");
    if (tipo.type == "password") {
        tipo.type = "text";
        $('#ver_password').css('color', '#368FCD');
        $('#ver_password').attr("title", "Ocultar contraseña");
    } else {
        tipo.type = "password";
        $('#ver_password').css('color', '#918D8D');
        $('#ver_password').attr("title", "Mostrar contraseña");
    }
});


function creditoInputDisabled(creditoOption) {
    var option = creditoOption;
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


$(document).on('change', '#abonos', function () {
    creditoInputDisabled($(this).val());
});


$("#credito, #limite_credito").on("input", function () {
    $(this).val(currencyMX($(this).val()));
});
//#endregion
