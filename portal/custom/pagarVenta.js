var $ = jQuery;
var factura = 0;


$(document).ready(function () {
    $("#monto").numeric(".");
    factura = $("#fk_venta").val();
});


function validar() {
    var retorno = true;

    if ($('#fk_pago').val() == 0) {
        retorno = false;
        $('#fk_pago').css('background-color', '#ffdddd');
    }

    if ($('#monto').val().length < 1) {
        retorno = false;
        $('#monto').css('background-color', '#ffdddd');
    }

    if ($('#fk_sucursal').val() == 0) {
        retorno = false;
        $('#fk_sucursal').css('background-color', '#ffdddd');
    }

    return retorno;
}


$('#agregar').click(function () {

    if (validar()) {

        var parametros = {
            "monto": $("#monto").val(),
            "fk_venta": $("#fk_venta").val(),
            "fk_usuario": $("#fk_usuario").val(),
            "fk_pago": $("#fk_pago").val(),
            "fk_sucursal": $("#fk_sucursal").val()
        };

        $.ajax({
            data: parametros,

            url: 'servicios/agregarAbonoVenta.php',

            type: 'POST',

            contentType: "application/x-www-form-urlencoded;charset=utf-8",

            beforeSend: function () {

            },

            success: function (response) {

                if (response.codigo == 200) {
                    swal("Guardado exitoso", "El registro se guardó correctamente", "success").then(function () {
                        $(location).attr('href', "verVentas.php");
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



//EXTRAS
//#region
$("#fk_pago").change(function () {
    getComision();
});


$(document).on('blur', '#monto', function () {
    getComision();
});


function getComision() {
    var comision = $("#fk_pago").find(':selected').attr('data-comision');
    var monto = parseFloat($("#monto").val());
    if (comision > 0 && monto > 0) {
        var comision_final = (monto * comision) / 100;
        var comisionHTML = `<p class='badge-primary-integra my-2 comision'>Comisión: $${comision_final.toFixed(2)}</p>`;
        $(".comision-content").html(comisionHTML);
    } else {
        $(".comision").remove();
    }
}


$('#nueva').click(function () {
    $(location).attr("href", "pagarVenta.php?id=" + factura);
});


$('#inicio').click(function () {
    $(location).attr("href", "index.php");
});


$('#imprimir').click(function () {
    window.open("abonosVentaPDF.php?id=" + factura, '_blank');
});
//#endregion
