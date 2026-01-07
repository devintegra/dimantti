
var $ = jQuery;
var id = 0;

$(document).ready(function () {

    $('.datepicker').datepicker({
        dateFormat: 'yy-mm-dd'
    });

});


//BUSCAR
//#region
function validar() {
    var retorno = true;

    if ($('#inicio').val().length < 10) {
        retorno = false;
        $('#inicio').css('background-color', '#ffdddd');
    }

    if ($('#fin').val().length < 10) {
        retorno = false;
        $('#fin').css('background-color', '#ffdddd');
    }

    return retorno;

}


$('#buscar').click(function () {

    var comision;
    $("#comision").is(":checked") ? comision = 1 : comision = 0;

    var parametros = {
        "nivel": $("#nivel").val(),
        "fk_sucursal": $("#sucursal").val(),
        "fk_usuario": $("#fk_usuario").val(),
        "tipo": $("#tipo").val(),
        "comision": comision
    }

    $.ajax({
        url: 'servicios/getCorte.php',

        type: 'get',

        data: parametros,

        beforeSend: function () {
        },

        success: function (response) {

            $("#modalCorte .corte-content").html(response);

            $("#modalCorte").modal('show');

        },

        error: function (arg1, arg2, arg3) {
            console.log(arg3);
        }

    });


});
//#endregion




//EXPORTAR
//#region
$('#excel').click(function () {

    window.open('servicios/reporteCortes.php?inicio=' + "&sucursal=" + $("#sucursal").val() + "&fk_usuario=" + $("#fk_usuario").val(), '_blank');

});
//#endregion




//GENERAR
//#region
$(document).on('click', "#modalCorte #generar", function () {

    let total_entrada = parseFloat($("#modalCorte #total_entrada").text().trim().slice(1).replace(/,/g, ''));
    let total_salida = parseFloat($("#modalCorte #total_salida").text().trim().slice(2).replace(/,/g, ''));

    if (total_entrada != 0 || total_salida != 0) {

        Swal.fire({
            title: 'Generar corte',
            text: "¿Estás seguro que deseas generar el corte de caja?",
            icon: 'warning',
            showCancelButton: true,
            iconColor: '#368FCD',
            confirmButtonColor: '#368FCD',
            cancelButtonColor: '#000',
            confirmButtonText: 'Si, generar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                generar();
            }
        })
    } else {

        swal("Mensaje", "No hay cortes pendientes por el momento", "info");

    }
});


function generar() {

    $("#modalCorte #generar").attr("disabled", "disabled");

    var comision;
    $("#comision").is(":checked") ? comision = 1 : comision = 0;

    var parametros = {
        "nivel": $("#nivel").val(),
        "fk_usuario": $("#fk_usuario").val(),
        "fk_sucursal": $("#sucursal").val(),
        "tipo": $("#tipo").val(),
        "comision": comision
    }

    $.ajax({
        url: 'servicios/agregarCorte.php',

        type: 'post',

        data: parametros,

        beforeSend: function () {

        },

        success: function (response) {

            if (response.codigo == 200) {

                swal("Exito", "El registro se guardó correctamente", "success").then(function () {
                    $(location).attr('href', "verCortes.php");
                });

            } else {

                swal("Error", "Hubo un problema, vuelva a intentarlo", "error").then(function () {
                    location.reload();
                });

            }

        },

        error: function (arg1, arg2, arg3) {
            console.log(arg3);
        }

    });

}
//#endregion




//EXTRAS
//#region
$('#limpiarfiltros').click(function () {
    location.reload();
});
//#endregion
