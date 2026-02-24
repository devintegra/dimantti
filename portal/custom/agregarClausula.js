var $ = jQuery;


$(document).ready(function () {


});


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
        formData.append("descripcion", $("#descripcion").val());

        $.ajax({
            url: "servicios/agregarClausula.php",
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

                    swal("Guardado", "El registro se guardó correctamente", "success").then(function () {
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
