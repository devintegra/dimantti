var $ = jQuery;


$(document).ready(function () {

});



function validar() {

    var retorno = true;

    if ($('#nombre').val().length == 0) {
        retorno = false;
        $('#nombre').css('background-color', '#ffdddd');
    }

    if ($('#costo').length == 0) {
        retorno = false;
        $('#costo').css('background-color', '#ffdddd');
    }

    if ($('#precio').length == 0 || parseFloat($('#precio').val()) < parseFloat($('#costo').val())) {
        retorno = false;
        $('#precio').css('background-color', '#ffdddd');
    }

    return retorno;

}


$("#guardar").click(function () {

    if (validar()) {

        $.LoadingOverlay("show", {
            color: "rgba(255, 255, 255, 0)"
        })

        var formData = new FormData(document.getElementById("formuploadajax"));

        $.ajax({
            url: "servicios/agregarMetal.php",
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
                        $(location).attr("href", "verMetales.php");
                    });

                } else {

                    swal("Error", response.descripcion, "error");

                }

            },

            error: function (arg1, arg2, arg3) {
                console.log(arg3);
            }

        });

    }

});
