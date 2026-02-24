var $ = jQuery;


$(document).ready(function () {

});



//GUARDAR
//#region
function validar() {

    var retorno = true;

    if ($('#descripcion').val().length < 1) {
        retorno = false;
        $('#descripcion').css('background-color', '#ffdddd');
    }

    return retorno;

}


$("#guardar").click(function () {

    if (validar()) {

        $.LoadingOverlay("show", {
            color: "rgba(255, 255, 255, 0)"
        })

        var formData = new FormData(document.getElementById("formuploadajax"));
        formData.append("pk_clausula", $("#pk_clausula").val());
        formData.append("descripcion", $("#descripcion").val());
        formData.append("estado", 1);

        $.ajax({
            url: "servicios/editarClausula.php",
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
                        $(location).attr("href", "verClausulas.php");
                    });

                } else {

                    swal("Error", response.descripcion, "error").then(function () {
                        $(location).attr("href", "verClausulas.php");
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
        title: 'Eliminar cláusula',
        text: "¿Estás seguro que deseas eliminar la cláusula?",
        icon: 'warning',
        showCancelButton: true,
        iconColor: '#D8A31A',
        confirmButtonColor: '#D8A31A',
        cancelButtonColor: '#000',
        confirmButtonText: 'Si, desactivar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            eliminar();
        }
    })
});


function eliminar() {

    var parametros = {
        "pk_clausula": $("#pk_clausula").val()
    };

    $.ajax({
        data: parametros,

        url: 'servicios/eliminarClausula.php',

        type: 'POST',

        beforeSend: function () {

        },

        success: function (response) {

            if (response.codigo == 200) {

                swal("Eliminacion exitosa", "El registro se eliminó correctamente", "success").then(function () {
                    $(location).attr("href", "verClausulas.php");
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
