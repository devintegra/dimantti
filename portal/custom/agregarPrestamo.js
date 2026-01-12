var $ = jQuery;

$(document).ready(function () {

    $('.select2').select2({
        tags: false,
    });

    $('#monto, #cantidad_pagos').on('input', function () {
        var monto = parseFloat($('#monto').val());
        var cantidad_pagos = parseInt($('#cantidad_pagos').val());

        if (isNaN(monto) || isNaN(cantidad_pagos) || cantidad_pagos <= 0) {
            $('#pago_visible').val("$0.00");
            return;
        }

        var pago = monto / cantidad_pagos;
        $('#monto_abono').val(pago.toFixed(2));
        $('#pago_visible').val("$" + pago.toFixed(2));
    });

});



//GUARDAR
//#region
function validar() {

    var retorno = true;

    if ($('#nombre_empleado').val() == 0) {
        retorno = false;
        $('#nombre_empleado').css('background-color', '#ffdddd');
    }

    if ($('#monto').val() < 1) {
        retorno = false;
        $('#monto').css('background-color', '#ffdddd');
    }

    if ($('#frecuencia').val() == 0) {
        retorno = false;
        $('#frecuencia').css('background-color', '#ffdddd');
    }

    if ($('#cantidad_pagos').val() < 1) {
        retorno = false;
        $('#cantidad_pagos').css('background-color', '#ffdddd');
    }

    if ($('#pago').val().length == 0) {
        retorno = false;
        $('#pago').css('background-color', '#ffdddd');
    }

    return retorno;

}

$('#guardar').click(function () {

    if (validar()) {

        var parametros = {
            "nombre_empleado": $("#nombre_empleado").val(),
            "monto": $("#monto").val(),
            "frecuencia": $("#frecuencia").val(),
            "cantidad_pagos": $("#cantidad_pagos").val(),
            "monto_abono": $("#monto_abono").val(),
            "pago": $("#pago").val(),
            "observaciones": $("#observaciones").val(),
            "sucursal": $("#sucursal").val(),
            "usuario": $("#usuario").val(),
        };

        $.ajax({
            data: parametros,

            url: 'servicios/agregarPrestamo.php',

            type: 'POST',

            beforeSend: function () {

            },

            success: function (response) {

                if (response.codigo == 200) {

                    swal("Guardado exitoso", "El registro se guardó correctamente", "success").then(function () {
                        $(location).attr('href', "verPrestamos.php");
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
