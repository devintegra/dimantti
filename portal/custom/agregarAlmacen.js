var $ = jQuery;


$(document).ready(function () {

    $('.datepicker').datepicker({
        dateFormat: 'yy-mm-dd'
    });

    if ($('#fk_sucursal').val() != 0) {
        getAlmacenes();
    }

});


//SUCURSAL
//#region
function getAlmacenes() {

    var parametros = {
        "fk_sucursal": $("#fk_sucursal").val()
    };

    $.ajax({

        data: parametros,

        url: 'servicios/getSucursalAlmacenes.php',

        type: 'GET',

        contentType: "application/x-www-form-urlencoded;charset=utf-8",

        beforeSend: function () {

        },

        success: function (response) {

            if (response.codigo == 200) {

                $('#fk_almacen').empty();

                let optionsHTML = "<option value='0'>Seleccione</option>";
                response.objList.forEach(element => {

                    optionsHTML += `<option value='${element.pk_sucursal_almacen}'>${element.nombre}</option>`;

                });

                $('#fk_almacen').append(optionsHTML);

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


$(document).on('change', '#fk_sucursal', function () {
    getAlmacenes();
});
//#endregion



//GUARDAR
//#region
function validarGuardado() {
    var retorno = true;

    if ($('#fk_sucursal').val() == 0) {
        retorno = false;
        $('#fk_sucursal').css('background-color', '#ffdddd');
    }

    if ($('#fk_almacen').val() == 0) {
        retorno = false;
        $('#fk_almacen').css('background-color', '#ffdddd');
    }

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

        var parametros = {
            "fk_sucursal": $("#fk_sucursal").val(),
            "fk_almacen": $("#fk_almacen").val(),
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
