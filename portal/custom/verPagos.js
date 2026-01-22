var $ = jQuery;

$(document).on('change', '.chkRuta', function () {

    let pk_pago = $(this).closest('tr').attr('data-id');
    let estatus = $(this).is(':checked') ? 1 : 0;

    var parametros = {
        "pk_pago": pk_pago,
        "estatus": estatus
    };

    $.ajax({

        data: parametros,

        url: 'servicios/editarPagoEstatusRuta.php',

        type: 'POST',

        beforeSend: function () {
        },

        success: function (response) {

            if (response.codigo != 200) {

                swal('Error', response.descripcion, 'error').then(function () {
                    location.reload();
                });

            }

        },
        error: function (arg1, arg2, arg3) {
            console.log(arg3);
        }

    });

});
