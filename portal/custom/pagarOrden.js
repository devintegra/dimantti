var $ = jQuery;


function validar() {
    var retorno = true;

    if ($('#monto').val().length < 1) {
        retorno = false;
        $('#monto').css('background-color', '#ffdddd');
    }

    if (parseFloat($('#monto').val()) > parseFloat($("#saldo").val())) {
        retorno = false;
        swal('Mensaje', 'El monto sobrepasa el saldo actual, ingrese una cantidad menor o igual al saldo para continuar', 'info');
    }

    if ($('#tipo_pago').val() < 1) {
        retorno = false;
        $('#tipo_pago').css('background-color', '#ffdddd');
    }

    return retorno;
}


$('#guardar').click(function () {

    if (validar()) {

        var parametros = {
            "pk_orden": $("#pk_orden").val(),
            "monto": $("#monto").val(),
            "fk_usuario": $("#fk_usuario").val(),
            "tipo_pago": $("#tipo_pago").val()
        };
        $.ajax({

            data: parametros,

            url: 'servicios/pagarOrden.php',

            type: 'post',

            beforeSend: function () {
            },

            success: function (response) {

                var myObj = response;
                if (myObj.codigo == 200) {

                    swal("Guardado exitoso", "El registro se guardó correctamente", "success").then(function () {
                        $(location).attr('href', "verOrdenesPago.php");
                    });

                } else {
                    swal("Error", myObj.descripcion, "error").then(function () {
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


$('#salir').click(function () {
    $(location).attr("href", "verOrdenesPago.php");
});
