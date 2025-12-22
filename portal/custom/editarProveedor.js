var $ = jQuery;


$(document).ready(function () {
    $("#telefono").mask("(999) 999-9999");

    if (parseInt($("#credito").val()) == 1) {
        $("#dias_credito").removeAttr("disabled");
    } else {
        $("#dias_credito").attr("disabled", "disabled");
        $("#dias_credito").val(0);
    }

    $("#eliminar").click(function () {
        Swal.fire({
            title: 'Eliminar proveedor',
            text: "¿Estás seguro que deseas eliminar al proveedor?",
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

        var parametros = {
            "pk_cliente": $("#id").val(),
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

            url: 'servicios/editarProveedor.php',

            type: 'POST',

            contentType: "application/x-www-form-urlencoded;charset=utf-8",

            beforeSend: function () {

            },

            success: function (response) {

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


function eliminar() {

    var parametros = {
        "pk_cliente": $("#id").val()
    };

    $.ajax({
        data: parametros,

        url: 'servicios/eliminarProveedor.php',

        type: 'POST',

        beforeSend: function () {

        },

        success: function (response) {

            if (response.codigo == 200) {

                swal("Eliminacion exitosa", "El registro se eliminó correctamente", "success").then(function () {
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


$('#credito').on('change', function () {

    if (parseInt(this.value) == 1) {
        $("#dias_credito").removeAttr("disabled");
    }

    else {
        $("#dias_credito").attr("disabled", "disabled");
        $("#dias_credito").val(0);
    }
});
