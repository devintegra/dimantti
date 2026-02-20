var $ = jQuery;
var ph;
var id;
var scanner;
var total_pagar;
var tipo_pago = 0;


$(document).ready(function () {
    getCurrentURL()
})


function getCurrentURL() {
    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);
    id = parseInt(urlParams.get('id'))
}




//GUARDAR
//#region
function validarGuardado() {

    var retorno = true;
    var recibe = 0;

    $("#modalTicket .pago_input").each(function () {
        var valor = parseFloat($(this).val());
        if (!isNaN(valor)) {
            recibe += valor;
        }
    });

    if (recibe < parseFloat($("#modalTicket #ticket_total").text().trim().slice(1))) {
        retorno = false;
        swal("Mensaje", "Es necesario ingresar la cantidad total en alguno(s) de los campos de tipo de pago para continuar", "info");
    }

    return retorno;

}


$('#guardar').click(function () {
    $("#modalTicket").modal("show");
    total_pagar = $("#total").val() - $("#anticipo").val() - $("#descuento").val();
    var elid = $("#eldi").val();
    var lacomision = parseFloat($("#comision").val()) / 100;
    lacomision = total_pagar * lacomision;
    lacomision = lacomision.toFixed(2);

    var pagado = 0;
    $(".pago_input").each(function () {
        var valor = parseFloat($(this).val());
        if (!isNaN(valor)) {
            pagado += valor;
        }
    });

    $("#ticket_comision").text("$" + lacomision);
    $("#ticket_cambio").text("$" + (pagado - total_pagar));
    $("#ticket_total").text("$" + total_pagar);
    $("#modalTicket").modal("show");
});


function guardar() {

    if (validarGuardado()) {

        $("#guardar_orden").attr('disabled', 'disabled');

        if ($(".chk-comision").is(":checked")) {
            total_pagar = total_pagar + $("#ticket_comision").text().trim().slice(1);
        }

        var efectivo = parseFloat($("#efectivo").val());
        if (parseFloat($("#efectivo").val()) > 0 && parseFloat($('#ticket_cambio').text().trim().slice(1)) > 0) {
            efectivo = parseFloat($("#efectivo").val()) - parseFloat($('#ticket_cambio').text().trim().slice(1));
        }

        var parametros = {
            "pk_orden": $("#pk_orden").val(),
            "fk_usuario": $("#fk_usuario").val(),
            "total": $("#total").val(),
            "total_pagar": total_pagar,
            "comision": parseFloat($("#ticket_comision").text().trim().slice(1)),
            "descuento": $("#descuento").val(),
            "efectivo": efectivo,
            "credito": $("#credito").val(),
            "debito": $("#debito").val(),
            "cheque": $("#cheque").val(),
            "transferencia": $("#transferencia").val(),
            "cheque_referencia": $("#cheque_referencia").val(),
            "transferencia_referencia": $("#transferencia_referencia").val(),
            "tipo_pago": tipo_pago
        };

        $.ajax({

            data: parametros,

            url: 'servicios/agregarOrdenVenta.php',

            type: 'post',

            beforeSend: function () {

            },

            success: function (response) {

                var myObj = response.objList;

                if (response.codigo == 200) {

                    ph = myObj.telefono;
                    var pk_venta = myObj.orden;

                    swal("Exito", "La orden se cerró correctamente", "success").then(function () {
                        $(location).attr("href", "firmaMovilV.php?id=" + pk_venta + "&ph=" + ph);
                    });

                }
                else {
                    swal("Error", "Hubo un error, porfavor vuelva a intentarlo", "error").then(function () {
                        location.reload();
                    });

                }

            },

            error: function (arg1, arg2, arg3) {
                console.log(arg3);
            }

        });

    }


}


$(document).on("input", ".pago_input", function () {
    var cambio_total = 0;
    var valora = 0;
    var paso = 1;
    var comision_total = 0;

    //Actualizar el cambio
    $(".pago_input").each(function () {
        var valor = parseFloat($(this).val());
        if (valor > valora) {
            tipo_pago = paso;
        }
        valora = valor;
        var comision = (parseFloat($(this).next("input").val()) / 100) * valor;

        if (!isNaN(valor)) {
            cambio_total += valor;
            comision_total += comision;
        }
        paso++;
    });

    var ticket_total = parseFloat($("#ticket_total").text().trim().slice(1));

    cambio_total = cambio_total - ticket_total;
    $("#ticket_cambio").text("$" + cambio_total.toFixed(2));


    if ($(".chk-comision").is(":checked")) {
        $("#ticket_comision").text("$" + comision_total.toFixed(2));
    }

    if (cambio_total >= 0) {
        $("#ticket_cambio").css('color', '#4ea93b');
    } else {
        $("#ticket_cambio").css('color', '#ff4040');
    }

});


$('#guardar_orden').click(function () {
    guardar();
});
//#endregion
