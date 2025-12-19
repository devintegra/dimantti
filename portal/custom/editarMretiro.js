
var $ = jQuery;


$(document).ready(function () {

    if ($("#recurrente").val() == 2) {
        $("#dia_pago").attr('disabled', 'disabled');
    }

    if ($("#variable").val() == 1) {
        $("#cantidad").attr('disabled', 'disabled');
    }


    $("#eliminar").click(function () {
        Swal.fire({
            title: 'Eliminar motivo de retiro',
            text: "¿Estás seguro que deseas eliminar el motivo?",
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


function eliminar() {


    $.LoadingOverlay("show", {
        color: "rgba(255, 255, 255, 0)"

    });
    var parametros = {


        "pk_retiro": $("#pk_retiro").val()

    };
    $.ajax({
        data: parametros,

        url: 'servicios/eliminarMretiro.php',

        type: 'POST',

        beforeSend: function () {

        },

        success: function (response) {

            $.LoadingOverlay("hide");

            if (response.codigo == 200) {
                swal("Eliminación exitosa", "El registro se eliminó correctamente", "success").then(function () {
                    $(location).attr('href', "verMretiros.php");
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



//GUARDAR
$("#recurrente").change(function () {

    if ($("#recurrente").val() == 1) {
        $("#dia_pago").removeAttr('disabled');
    } else {
        $("#dia_pago").attr('disabled', 'disabled');
    }

});


$("#variable").change(function () {

    if ($("#variable").val() == 2) {
        $("#cantidad").removeAttr('disabled');
    } else {
        $("#cantidad").attr('disabled', 'disabled');
    }

});



function validar() {
    var retorno = true;

    limpiar();


    if ($('#nombre').val().length < 1) {
        retorno = false;
        $('#nombre').css("background-color", " #ffdddd");
    }

    if ($('#recurrente').val() == 0) {
        retorno = false;
        $('#recurrente').css("background-color", " #ffdddd");
    }

    if ($('#recurrente').val() == 1) {

        if ($('#dia_pago').val().length < 1 || $('#dia_pago').val() > 31) {
            retorno = false;
            swal("Mensaje", "El día esta fuera de los límites del mes, ingrese uno correcto", "info");
            $('#dia_pago').css("background-color", " #ffdddd");
        }

    }

    if ($('#variable').val() == 0) {
        retorno = false;
        $('#variable').css("background-color", " #ffdddd");
    }

    if ($('#variable').val() == 2) {

        if ($('#cantidad').val().length < 1) {
            retorno = false;
            $('#cantidad').css("background-color", " #ffdddd");
        }

    }


    return retorno;

}


$('#guardar').click(function () {

    if (validar()) {

        $.LoadingOverlay("show", {
            color: "rgba(255, 255, 255, 0)"

        });

        var parametros = {

            "nombre": $("#nombre").val(),
            "pk_retiro": $("#pk_retiro").val(),
            "recurrente": $("#recurrente").val(),
            "variable": $("#variable").val(),
            "dia_pago": $("#dia_pago").val(),
            "cantidad": $("#cantidad").val().replace(/,/g, "")

        };
        $.ajax({
            data: parametros,

            url: 'servicios/editarMretiro.php',

            type: 'POST',

            contentType: "application/x-www-form-urlencoded;charset=utf-8",

            beforeSend: function () {

            },

            success: function (response) {

                $.LoadingOverlay("hide");

                if (response.codigo == 200) {
                    swal("Guardado exitoso", "El registro se guardó correctamente", "success").then(function () {
                        $(location).attr('href', "verMretiros.php");
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



function limpiar() {

    $('#nombre').css("background-color", " #ffffff");
    $('#recurrente').css("background-color", " #ffffff");
    $('#variable').css("background-color", " #ffffff");
    $('#dia_pago').css("background-color", " #ffffff");
    $('#cantidad').css("background-color", " #ffffff");

}


$("#cantidad").on("input", function () {
    $(this).val(currencyMX($(this).val()));
});
