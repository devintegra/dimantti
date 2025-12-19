var $ = jQuery;


$(document).ready(function () {
    localStorage.removeItem('usuario');
    localStorage.removeItem('pass');
});


function valida() {

    var error = 0;

    if ($('#usuario').val().length < 4) {
        error = 1;
        $('#usuario').attr("style", "background-color:#ffdddd");
    }

    if ($('#pass').val().length < 4) {
        error = 1;
        $('#pass').attr("style", "background-color:#ffdddd");
    }

    return error;
}


$('#login').click(function () {

    if (valida() === 0) {
        validacion();
    }

});


function validacion() {
    var pass = btoa($("#pass").val());

    var parametros = {
        "usuario": $('#usuario').val(),
        "pass": pass
    };

    $.ajax({
        data: parametros,

        url: 'portal/servicios/login.php',

        type: 'POST',

        beforeSend: function () {

        },

        success: function (response) {

            if (response.codigo === 201 || response.objList.existe === 0) {
                swal("Error al ingresar", "Datos incorrectos, vuelve a intentarlo", "error").then(function () {
                    location.reload();
                });
            }

            if (response.objList.existe === 1) {

                $(location).attr("href", "portal/index.php");

            }
        },
        error: function (arg1, arg2, arg3) {
            console.log(arg3);
        }

    });
}


$("#pass").on('keyup', function (e) {
    if (e.key === 'Enter' || e.keyCode === 13) {
        if (valida() === 0) {
            validacion();
        }
    }
});
