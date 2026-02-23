
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

    if ($('#categoria').val() == 0) {
        retorno = false;
        $('#categoria').css('background-color', '#ffdddd');
    }

    return retorno;

}


$("#guardar").click(function () {

    if (validar()) {

        $.LoadingOverlay("show", {
            color: "rgba(255, 255, 255, 0)"
        })

        var formData = new FormData(document.getElementById("formuploadajax"));
        formData.append("pk_subcategoria", $("#pk_subcategoria").val());
        formData.append("fk_categoria", $("#categoria").val());
        formData.append("nombre", $("#nombre").val());
        formData.append("estado", $("#estado").val());

        $.ajax({
            url: "servicios/editarSubcategoria.php",
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
                        $(location).attr("href", "verSubcategorias.php");
                    });

                } else {

                    swal("Error", response.descripcion, "error").then(function () {
                        $(location).attr("href", "verSubcategorias.php");
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
        title: 'Eliminar subcategoría',
        text: "¿Estás seguro que deseas eliminar la subcategoría?",
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
        "pk_subcategoria": $("#pk_subcategoria").val()
    };

    $.ajax({
        data: parametros,

        url: 'servicios/eliminarSubcategoria.php',

        type: 'POST',

        beforeSend: function () {

        },

        success: function (response) {

            if (response.codigo == 200) {

                swal("Eliminacion exitosa", "El registro se eliminó correctamente", "success").then(function () {
                    $(location).attr("href", "verSubcategorias.php");
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
