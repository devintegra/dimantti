
var $ = jQuery;


$(document).ready(function () {

});



//GUARDAR
//#region
function validar() {

    var retorno = true;

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
        formData.append("pk_categoria", $("#pk_categoria").val());
        formData.append("nombre", $("#nombre").val());

        $.ajax({
            url: "servicios/editarCategoria.php",
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
                        $(location).attr("href", "verCategorias.php");
                    });

                } else {

                    swal("Error", response.descripcion, "error").then(function () {
                        $(location).attr("href", "verCategorias.php");
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
        title: 'Eliminar categoría',
        text: "¿Estás seguro que deseas eliminar la categoría?",
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
        "pk_categoria": $("#pk_categoria").val()
    };

    $.ajax({
        data: parametros,

        url: 'servicios/eliminarCategoria.php',

        type: 'POST',

        beforeSend: function () {

        },

        success: function (response) {

            if (response.codigo == 200) {

                swal("Eliminacion exitosa", "El registro se eliminó correctamente", "success").then(function () {
                    $(location).attr("href", "verCategorias.php");
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
