
var $ = jQuery;


$(document).ready(function () {

});



//GUARDAR
//#region
function validar() {

    var retorno = true;

    if ($('#clave').val().length < 1) {
        retorno = false;
        $('#clave').css('background-color', '#ffdddd');
    }

    if ($('#nombre').val().length < 1) {
        retorno = false;
        $('#nombre').css('background-color', '#ffdddd');
    }

    return retorno;

}


$("#guardar").click(function () {

    if (validar()) {

        $.LoadingOverlay("show", {
            color: "rgba(255, 255, 255, 0)"
        })

        var formData = new FormData(document.getElementById("formuploadajax"));
        formData.append("pk_ruta", $("#pk_ruta").val());
        formData.append("clave", $("#clave").val());
        formData.append("nombre", $("#nombre").val());

        $.ajax({
            url: "servicios/editarRuta.php",
            type: "post",
            dataType: "html",
            data: formData,
            cache: false,
            contentType: false,
            processData: false,

            beforeSend: function () {

            },

            success: function (response) {

                $.LoadingOverlay("hide");

                response = JSON.parse(response);

                if (response.codigo == 200) {

                    swal("Enviado", "El registro se guardó correctamente", "success").then(function () {
                        $(location).attr("href", "verRutas.php");
                    });

                } else {

                    swal("Error", response.descripcion, "error").then(function () {
                        $(location).attr("href", "verRutas.php");
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
        title: 'Eliminar ruta',
        text: "¿Estás seguro que deseas eliminar la ruta?",
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


function eliminar() {

    var parametros = {
        "pk_ruta": $("#pk_ruta").val()
    };

    $.ajax({
        data: parametros,

        url: 'servicios/eliminarRuta.php',

        type: 'POST',

        beforeSend: function () {

        },

        success: function (response) {

            if (response.codigo == 200) {

                swal("Eliminacion exitosa", "El registro se eliminó correctamente", "success").then(function () {
                    $(location).attr("href", "verRutas.php");
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
