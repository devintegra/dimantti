var $ = jQuery;


$(document).ready(function () {

    if ($("#tipo").val() != 1) {
        $("#sucursal").removeAttr("disabled");
    }

    if ($("#tipo").val() == 2) {
        $("#contentNomina").removeClass("d-none");
    }

});



//GUARDAR
//#region
function validar() {

    var retorno = true;
    var regex = /[\w-\.]{2,}@([\w-]{2,}\.)*([\w-]{2,}\.)[\w-]{2,4}/;


    if ($('#nombre').val().length < 2) {
        retorno = false;
        $('#nombre').css('background-color', '#ffdddd');
    }

    if ($('#correo').val().length > 0) {
        if (!regex.test($('#correo').val().trim())) {
            retorno = false;
            $('#correo').css('background-color', '#ffdddd');
        }
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


$('#guardar').click(function () {

    if (validar()) {

        $.LoadingOverlay("show", {
            color: "rgba(255, 255, 255, 0)"
        });

        var pass = btoa($("#pass").val());

        var parametros = {
            "pk_usuario": $("#id").val(),
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

            url: 'servicios/editarUsuario.php',

            type: 'POST',

            beforeSend: function () {

            },

            success: function (response) {

                $.LoadingOverlay("hide");

                if (response.codigo == 200) {

                    swal("Guardado exitoso", "El registro se guardó correctamente", "success").then(function () {
                        $(location).attr('href', "verUsuarios.php");
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
//#endregion



//ELIMINAR
//#region
$("#eliminar").click(function () {
    Swal.fire({
        title: 'Eliminar usuario',
        text: "¿Estás seguro que deseas eliminar al usuario?",
        icon: 'warning',
        showCancelButton: true,
        iconColor: '#368FCD',
        confirmButtonColor: '#368FCD',
        cancelButtonColor: '#000',
        confirmButtonText: 'Si, borrar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            eliminar();
        }
    })
});


function eliminar() {

    $.LoadingOverlay("show", {
        color: "rgba(255, 255, 255, 0)"
    });

    var parametros = {
        "pk_usuario": $("#usuario").val()
    };

    $.ajax({
        data: parametros,

        url: 'servicios/eliminarUsuario.php',

        type: 'POST',

        beforeSend: function () {

        },

        success: function (response) {

            $.LoadingOverlay("hide");

            if (response.codigo == 200) {

                swal("Borrado exitoso", "El registro se eliminó correctamente", "success").then(function () {
                    $(location).attr('href', "verUsuarios.php");
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
//#endregion



//EXTRAS
//#region
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


$('#salir').click(function () {
    $(location).attr("href", "index.php");
});


$("#tipo").change(function () {

    let tipo = $("#tipo").val();

    if (tipo != 1) {
        $("#sucursal").removeAttr("disabled");
    } else {
        $("#sucursal").attr("disabled", "disabled");
    }

    if (tipo == 2) {
        $("#contentNomina").removeClass("d-none");
    } else {
        $("#contentNomina").addClass("d-none");
    }

});
//#endregion
