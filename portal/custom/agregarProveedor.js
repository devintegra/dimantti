var $ = jQuery;


$(document).ready(function () {
    $("#telefono").mask("(999) 999-9999");

});



function validar() {

    var retorno = true;
    var regex = /[\w-\.]{2,}@([\w-]{2,}\.)*([\w-]{2,}\.)[\w-]{2,4}/;

    if ($('#nombre').val().length < 2) {
        retorno = false;
        $('#nombre').css('background-color', '#ffdddd');
    }

    if ($('#telefono').val().length < 10) {
        retorno = false;
        $('#telefono').css('background-color', '#ffdddd');
    }

    if ($("#correo").val().length > 0) {
        if (!regex.test($('#correo').val().trim())) {
            retorno = false;
            $('#correo').css('background-color', '#ffdddd');
        }
    }

    if ($("#credito").val() == 1) {
        if ($("#dias_credito").val().length < 1) {
            retorno = false;
            $('#dias_credito').css('background-color', '#ffdddd');
        }
    }

    return retorno;

}


$('#guardar').click(function () {

    if (validar()) {

        $.LoadingOverlay("show", {
            color: "rgba(255, 255, 255, 0)"
        });

        var parametros = {

            "rfc": $("#rfc").val(),
            "nombre": $("#nombre").val(),
            "direccion": $("#direccion").val(),
            "telefono": $("#telefono").val(),
            "correo": $("#correo").val(),
            "contacto": $("#contacto").val(),
            "credito": $("#credito").val(),
            "dias_credito": $("#dias_credito").val()


        };
        $.ajax({
            data: parametros,

            url: 'servicios/agregarProveedor.php',

            type: 'POST',

            contentType: "application/x-www-form-urlencoded;charset=utf-8",

            beforeSend: function () {

            },

            success: function (response) {
                $.LoadingOverlay("hide");

                if (response.codigo == 200) {
                    swal("Guardado exitoso", "El registro se guardó correctamente", "success").then(function () {
                        $(location).attr('href', "verProveedores.php");
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


$('#credito').on('change', function () {

    if (parseInt(this.value) == 1) {
        $("#dias_credito").removeAttr("disabled");
    }

    else {
        $("#dias_credito").attr("disabled", "disabled");
        $("#dias_credito").val(0);
    }
});
