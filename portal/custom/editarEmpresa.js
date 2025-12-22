var $ = jQuery;


$(document).ready(function () {

    $("#telefono").mask("(999) 999-9999");

    $("#eliminar").click(function () {
        Swal.fire({
            title: 'Eliminar empresa',
            text: "¿Estás seguro que deseas eliminar la empresa?",
            icon: 'warning',
            showCancelButton: true,
            iconColor: '#D8A31A',
            confirmButtonColor: '#D8A31A',
            cancelButtonColor: '#000',
            confirmButtonText: 'Si, borrar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                eliminar();
            }
        })
    });
});



//VER CONTRASEÑA
//#region
$('#ver_password').click(function () {
    var tipo = document.getElementById("pass");
    if (tipo.type == "password") {
        tipo.type = "text";
        $('#ver_password').css('color', '#b58b29');
        $('#ver_password').attr("data-title", "Ocultar contraseña");
    } else {
        tipo.type = "password";
        $('#ver_password').css('color', '#918D8D');
        $('#ver_password').attr("data-title", "Mostrar contraseña");
    }
});
//#endregion



//GUARDAR
//#region
function validar() {
    var retorno = true;
    var regex = /[\w-\.]{2,}@([\w-]{2,}\.)*([\w-]{2,}\.)[\w-]{2,4}/;

    if ($('#nombre').val().length < 2) {
        retorno = false;
        $('#nombre').css('background-color', '#ffdddd');
    }

    if ($('#correo').val() != '') {

        if (!regex.test($('#correo').val().trim())) {
            retorno = false;
            $('#correo').css('background-color', '#ffdddd');
        }

    }


    return retorno;

}


$('#guardar').click(function () {

    if (validar()) {

        var pass = btoa($("#pass").val());

        var parametros = {

            "pk_empresa": $("#pk_empresa").val(),
            "nombre": $("#nombre").val(),
            "direccion": $("#direccion").val(),
            "telefono": $("#telefono").val(),
            "cp": $("#cp").val(),
            "rfc": $("#rfc").val(),
            "regimen_fiscal": $("#regimen_fiscal").val(),
            "password": pass,
            "correo": $("#correo").val(),
            "responsable": $("#responsable").val()

        };
        $.ajax({
            data: parametros,

            url: 'servicios/editarEmpresa.php',

            type: 'POST',

            contentType: "application/x-www-form-urlencoded;charset=utf-8",

            beforeSend: function () {

            },

            success: function (response) {

                if (response.codigo == 200) {

                    if ($("#cer").val().length > 4 || $("#key").val().length > 4) {

                        guardarDocumentos(response.objList.pk_empresa);

                    } else {

                        swal("Guardado exitoso", "El registro se guardó correctamente", "success").then(function () {
                            $(location).attr('href', "verEmpresas.php");
                        });

                    }

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


function guardarDocumentos(pk_empresa) {

    var formData = new FormData(document.getElementById("formuploadajax"));
    formData.append("pk_empresa", pk_empresa);

    $.ajax({
        url: "servicios/agregarDocumentosEmpresa.php",
        type: "post",
        dataType: "html",
        data: formData,
        cache: false,
        contentType: false,
        processData: false,

        beforeSend: function () {

        },

        success: function (response) {

            response = JSON.parse(response);

            $.LoadingOverlay("hide");

            if (response.codigo == 200) {

                swal("Enviado", "La empresa se guardó correctamente", "success").then(function () {
                    $(location).attr("href", "verEmpresas.php");
                });

            } else {

                swal("Error", response.descripcion, "error").then(function () {
                    $(location).attr("href", "verEmpresas.php");
                });

            }

        },

        error: function (arg1, arg2, arg3) {
            console.log(arg3);
        }

    });
}


function eliminar() {
    var parametros = {


        "pk_empresa": $("#pk_empresa").val()

    };
    $.ajax({
        data: parametros,

        url: 'servicios/eliminarEmpresa.php',

        type: 'POST',

        beforeSend: function () {

        },

        success: function (response) {

            if (response.codigo == 200) {

                swal("Eliminacion exitosa", "El registro se eliminó correctamente", "success").then(function () {
                    $(location).attr('href', "verEmpresas.php");
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
