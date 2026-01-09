var $ = jQuery;

function validar() {

    var retorno = true;
    var regex = /[\w-\.]{2,}@([\w-]{2,}\.)*([\w-]{2,}\.)[\w-]{2,4}/;

    if ($('#tipo').val() == 0) {
        retorno = false;
        $('#tipo').css('background-color', '#ffdddd');
    }

    if ($('#nombre').val().length < 2) {
        retorno = false;
        $('#nombre').css('background-color', '#ffdddd');
    }

    if (!regex.test($('#correo').val().trim())) {
        retorno = false;
        $('#correo').css('background-color', '#ffdddd');
    }

    if ($('#usuario').val().length < 2) {
        retorno = false;
        $('#usuario').css('background-color', '#ffdddd');
    }

    if ($('#pass').val().length < 4 || $('#pass').val() !== $('#passc').val()) {
        retorno = false;
        $('#pass').css('background-color', '#ffdddd');
        $('#passc').css('background-color', '#ffdddd');
    }

    if ($('#tipo').val() == 2) {
        if ($('#sueldo').val().length == 0 || $('#sueldo').val() == 0) {
            retorno = false;
            $('#sueldo').css('background-color', '#ffdddd');
        }

        if ($('#comision').val().length == 0) {
            retorno = false;
            $('#comision').css('background-color', '#ffdddd');
        }
    }

    return retorno;

}


function imgSeleccionada(seleccion) {
    $('#img-seleccion').val(seleccion);
}


$('#ver_password').click(function () {
    var tipo = document.getElementById("pass");
    if (tipo.type == "password") {
        tipo.type = "text";
        $('#ver_password').css('color', '#368FCD');
        $('#ver_password').attr("title", "Ocultar contraseña");
    } else {
        tipo.type = "password";
        $('#ver_password').css('color', '#918D8D');
        $('#ver_password').attr("title", "Mostrar contraseña");
    }
});


$('#ver_passwordc').click(function () {
    var tipo = document.getElementById("passc");
    if (tipo.type == "password") {
        tipo.type = "text";
        $('#ver_passwordc').css('color', '#368FCD');
        $('#ver_passwordc').attr("title", "Ocultar contraseña");
    } else {
        tipo.type = "password";
        $('#ver_passwordc').css('color', '#918D8D');
        $('#ver_passwordc').attr("title", "Mostrar contraseña");
    }
});


$('#guardar').click(function () {

    if (validar()) {

        $.LoadingOverlay("show", {
            color: "rgba(255, 255, 255, 0)"
        });

        var pass = btoa($("#pass").val());

        var parametros = {
            "pk_usuario": $("#usuario").val(),
            "nombre": $("#nombre").val(),
            "correo": $("#correo").val(),
            "pass": pass,
            "nivel": $("#tipo").val(),
            "fk_sucursal": $("#sucursal").val(),
            "sueldo": $("#sueldo").val(),
            "comision": $("#comision").val(),
            "avatar": "logo.png"
        };

        $.ajax({
            data: parametros,

            url: 'servicios/agregarUsuario.php',

            type: 'POST',

            beforeSend: function () {

            },

            success: function (response) {

                $.LoadingOverlay("hide");

                if (response.codigo == 200) {

                    swal("Guardado exitoso", "El registro se guardó correctamente", "success").then(function () {
                        $(location).attr('href', "verUsuarios.php");
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


$('#salir').click(function () {
    $(location).attr("href", "index.php");
});


$("#tipo").change(function () {

    let tipo = $("#tipo").val();

    if (tipo == 1 || tipo == 0) {
        $("#sucursal").attr("disabled", "disabled");
    } else {
        $("#sucursal").removeAttr("disabled");
    }

    if (tipo == 2) {
        $("#contentNomina").removeClass("d-none");
    } else {
        $("#contentNomina").addClass("d-none");
    }

});
