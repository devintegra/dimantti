var $ = jQuery;


$(document).ready(function () {

    if ($("#sucursal").val() == 0) {
        $('#modalSucursales').modal('show');
    } else {
        getMotivos($("#sucursal").val());
    }

});



//SUCURSALES
//#region
$('.suc').click(function () {

    $('#sucursal').val(this.id);
    $('#lasucursal').text($(this).text().trim());
    getMotivos(this.id);
    $('#modalSucursales').modal('hide');

});
//#endregion



//MOTIVOS
//#region
function getMotivos(fk_sucursal) {

    var parametros = {

        "fk_sucursal": fk_sucursal

    };
    $.ajax({
        data: parametros,

        url: 'servicios/getMotivosSucursal.php',

        type: 'GET',

        beforeSend: function () {

        },

        success: function (response) {

            $("#tipo").html(response);

        },
        error: function (arg1, arg2, arg3) {
            console.log(arg3);
        }
    });

}


$("#tipo").change(function () {

    //idt = 6-2-50
    //representa = fk_retiro-¿es variable?-cantidad$

    var idt = $("#tipo").val();
    var idtt = idt.split("-");

    if (idtt[1] == 2) {
        $("#cantidad").val(idtt[2]);
        $("#cantidad").attr("disabled", "disabled");
    } else {
        $("#cantidad").val("");
        $("#cantidad").removeAttr("disabled");
    }

});
//#endregion




//GUARDAR
//#region
function validar() {
    var retorno = true;

    limpiar();


    if ($('#tipo').val() == 0) {
        retorno = false;
        $('#tipo').css("background-color", " #ffdddd");
    }


    if ($('#cantidad').val().length < 1) {
        retorno = false;
        $('#cantidad').css("background-color", " #ffdddd");
    }

    if ($('#tipo_pago').val() == 0) {
        retorno = false;
        $('#tipo_pago').css("background-color", " #ffdddd");
    }


    return retorno;

}


$('#guardar').click(function () {

    if (validar()) {

        $("#guardar").attr("disabled", "disabled");

        $.LoadingOverlay("show", {
            color: "rgba(255, 255, 255, 0)"

        });

        var idt = $("#tipo").val();
        var idtt = idt.split("-");

        var parametros = {

            "fk_sucursal": $("#sucursal").val(),
            "fk_usuario": $("#fk_usuario").val(),
            "fk_tipo": idtt[0],
            "monto": $("#cantidad").val().replace(/,/g, ""),
            "descripcion": $("#descripcion").val(),
            "fk_pago": $("#tipo_pago").val()

        };
        $.ajax({
            data: parametros,

            url: 'servicios/agregarRetiro.php',

            type: 'POST',

            contentType: "application/x-www-form-urlencoded;charset=utf-8",

            beforeSend: function () {

            },

            success: function (response) {
                $.LoadingOverlay("hide");

                if (response.codigo == 200) {

                    nentrada = response.objList.pk_retiro;

                    $("#nentrada").text(nentrada);
                    $("#pdf").attr("href", "retiroPDF.php?id=" + nentrada);
                    $('#exito').modal('show');

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





//EXTRAS
//#region
function limpiar() {
    $('#cantidad').css("background-color", " #ffffff");
    $('#tipo').css("background-color", " #ffffff");
}

$('#nueva').click(function () {
    $(location).attr("href", "agregarRetiro.php");
});

$('#ver_registros').click(function () {
    $(location).attr("href", "verRetiros.php");
});

$('#inicio').click(function () {
    $(location).attr("href", "index.php");
});

$("#cantidad").on("input", function () {
    $(this).val(currencyMX($(this).val()));
});
//#endregion
