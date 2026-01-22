var $ = jQuery;


$(document).ready(function () {

    $('.datepicker').datepicker({
        dateFormat: 'yy-mm-dd'
    });

});


//GUARDAR
//#region
function validarGuardado() {
    var retorno = true;

    if ($('#plantilla').val() == 0) {
        retorno = false;
        $('#plantilla').css('background-color', '#ffdddd');
    }

    if ($('#ruta').val() == 0) {
        retorno = false;
        $('#ruta').css('background-color', '#ffdddd');
    }

    if ($('#usuario').val() == 0) {
        retorno = false;
        $('#usuario').css('background-color', '#ffdddd');
    }

    if ($('#fecha').val().length < 10) {
        retorno = false;
        $('#fecha').css('background-color', '#ffdddd');
    }

    return retorno;
}


$('#guardar').click(function () {

    if (validarGuardado()) {

        let fk_sucursal = $("#ruta option:selected").attr('data-fk-sucursal');
        let fk_almacen = $("#ruta option:selected").attr('data-fk-almacen');

        var parametros = {
            "fk_sucursal": fk_sucursal,
            "fk_almacen": fk_almacen,
            "pk_plantilla": $("#plantilla").val(),
            "fk_ruta": $("#ruta").val(),
            "pk_usuario": $("#usuario").val(),
            "fecha": $("#fecha").val()
        };

        $.ajax({

            data: parametros,

            url: 'servicios/agregarAlmacen.php',

            type: 'POST',

            contentType: "application/x-www-form-urlencoded;charset=utf-8",

            beforeSend: function () {

            },

            success: function (response) {

                if (response.codigo == 200) {

                    swal("Exito", "Se iniciado el almacen correctamente", "success").then(function () {
                        location.reload();
                    });
                }

                else {
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

});
//#endregion
