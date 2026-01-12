var $ = jQuery;
let pk_compra = $('#fk_compra').val();


$(document).ready(function () {

});




//GUARDAR
//#region
function validar() {

    var retorno = true;
    let pago = $('#monto_abono').val();

    if (parseFloat($("#monto").val()) > parseFloat($('#saldo').val())) {
        retorno = false;
        swal('Mensaje', 'El pago no puede ser mayor al saldo pendiente', 'info');
        $('#monto').val(pago);
    }

    if ($("#fk_pago").val() == 0) {
        retorno = false;
        $("#fk_pago").css('background-color', '#ffdddd');
    }

    if ($("#fk_sucursal").val() == 0) {
        retorno = false;
        $("#fk_sucursal").css('background-color', '#ffdddd');
    }

    return retorno;

}


$('#guardar').click(function () {

    if (validar()) {

        $.LoadingOverlay("show", {
            color: "rgba(255, 255, 255, 0)"
        })

        var parametros = {
            "pk_prestamo": $("#pk_prestamo").val(),
            "monto": $('#monto').val(),
            "fk_pago": $('#fk_pago').val(),
            "fk_usuario": $("#fk_usuario").val(),
            "fk_sucursal": $("#fk_sucursal").val()
        };

        $.ajax({
            data: parametros,

            url: 'servicios/agregarPrestamoPago.php',

            type: 'POST',

            contentType: "application/x-www-form-urlencoded;charset=utf-8",

            beforeSend: function () {

            },

            success: function (response) {

                $.LoadingOverlay("hide");

                if (response.codigo == 200) {

                    swal("Guardado exitoso", "El registro se guardó correctamente", "success").then(function () {
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

    }
});
//#endregion
