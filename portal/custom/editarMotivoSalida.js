var $ = jQuery;


$(document).ready(function () {
    $("#eliminar").click(function () {
        Swal.fire({
            title: 'Eliminar motivo de salida',
            text: "¿Estás seguro que deseas eliminar el motivo de salida?",
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
});


function validar() {
    var retorno = true;


    if ($('#nombre').val().length < 1) {
        retorno = false;
        $('#nombre').css('background-color', '#ffdddd');
    }


    return retorno;

}


$('#guardar').click(function () {

    if (validar()) {

        var parametros = {

            "pk_motivo_salida": $("#pk_motivo_salida").val(),
            "nombre": $("#nombre").val(),

        };
        $.ajax({
            data: parametros,

            url: 'servicios/editarMotivoSalida.php',

            type: 'POST',

            beforeSend: function () {

            },

            success: function (response) {

                if (response.codigo == 200) {

                    swal("Guardado exitoso", "El registro se guardó correctamente", "success").then(function () {
                        $(location).attr('href', "verMotivosSalida.php");
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


function eliminar() {
    var parametros = {


        "pk_motivo_salida": $("#pk_motivo_salida").val()

    };
    $.ajax({
        data: parametros,

        url: 'servicios/eliminarMotivoSalida.php',

        type: 'POST',

        beforeSend: function () {

        },

        success: function (response) {

            if (response.codigo == 200) {

                swal("Eliminacion exitosa", "El registro se eliminó correctamente", "success").then(function () {
                    $(location).attr("href", "verMotivosSalida.php");
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
