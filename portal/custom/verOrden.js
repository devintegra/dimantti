var $ = jQuery;

$(document).on('ready', function () {

});


//AGREGAR REGISTRO
//#region
$('#agregar').click(function () {
    var nivel = $("#nivel").val();
    if (nivel == 1 || nivel == 3 || nivel == 4) {
        $(location).attr("href", "agregarRegistro.php?id=" + $("#pk_orden").val());
    }
});
//#endregion



//PUBLICO
//#region
$('.cambiar').click(function () {

    var elid = this.id;

    var parametros = {
        "pk_registro": elid
    };

    $.ajax({

        data: parametros,

        url: 'servicios/editarOrdenRegistroPrivacidad.php',

        type: 'post',

        beforeSend: function () {

        },

        success: function (response) {

            if (response.codigo == 200) {

                swal("Cambio exitoso exitoso", "El estatus se actualizó correctamente", "success").then(function () {
                    location.reload();
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

});
//#endregion



//REACTIVAR
//#region
$('#reactivar').click(function () {

    $.LoadingOverlay("show", {
        color: "rgba(255, 255, 255, 0)"
    });

    var descripcion = "Se reactiva la orden";

    var parametros = {
        "pk_orden": $("#pk_orden").val(),
        "tipo": 6,
        "publico": 0,
        "precio": 0,
        "costo": 0,
        "descripcion": descripcion,
        "fk_usuario": $("#fk_usuario").val(),
    };

    $.ajax({

        data: parametros,

        url: 'servicios/agregarRegistro.php',

        type: 'post',

        beforeSend: function () {

        },

        success: function (response) {

            $.LoadingOverlay("hide");

            if (response.codigo == 200) {

                swal("Guardado exitoso", "La orden se reactivo correctamente", "success").then(function () {
                    $(location).attr('href', "verOrden.php?id=" + $("#pk_orden").val());
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




});
//#endregion



//ENTREGAR
//#region
$('#entregar').click(function () {

    $(location).attr("href", "entrega.php?id=" + $("#pk_orden").val() + "&servicio=" + $("#pdf").val());

});
//#endregion
