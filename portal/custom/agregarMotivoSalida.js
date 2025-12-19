
var $ = jQuery;


function validar() {
    var retorno = true;


    if ($('#nombre').val().length < 1) {
        retorno = false;
        $('#nombre').css('background-color', '#ffdddd');
    }


    return retorno;

}


$('#guardar').click(function () {

    if (validar()) {

        var parametros = {

            "nombre": $("#nombre").val(),

        };
        $.ajax({
            data: parametros,

            url: 'servicios/agregarMotivoSalida.php',

            type: 'POST',

            beforeSend: function () {

            },

            success: function (response) {

                if (response.codigo == 200) {

                    swal("Guardado exitoso", "El registro se guardó correctamente", "success").then(function () {
                        $(location).attr('href', "verMotivosSalida.php");
                    });

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
