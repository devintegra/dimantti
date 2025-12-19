
var $ = jQuery;
var id;
var id_valor;
var id_split;
var id_seleccionado;


$(document).ready(function () {


});



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
        formData.append("nombre", $("#nombre").val());

        $.ajax({
            url: "servicios/agregarCategoria.php",
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
