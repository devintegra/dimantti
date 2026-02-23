var $ = jQuery;


$(document).ready(function () {

});




//GUARDAR
//#region
function getParametos() {

    var data = [];

    $(".inputParametro").each(function (index) {

        let id = $(this).attr('data-id');
        let valor = $(this).val();

        if (valor.length > 0) {

            var myObj = { "pk_parametro": id, "valor": valor };

            data.push(myObj);

        }

    });

    return data;

}


$('#guardar').click(function () {

    $.LoadingOverlay("show", {
        color: "rgba(255, 255, 255, 0)"
    });

    var parametros = {
        "parametros": getParametos()
    };

    $.ajax({

        data: JSON.stringify(parametros),

        url: 'servicios/editarParametros.php',

        type: 'post',

        dataType: 'json',

        contentType: "application/json; charset=utf-8",

        beforeSend: function () {

        },

        success: function (response) {

            $.LoadingOverlay("hide");

            if (response.codigo == 200) {

                swal("Éxito", "Los parámetros se actualizaron correctamente", "success").then(function () {
                    location.reload();
                });

            } else {

                swal("Error", response.descripcion, "error").then(function () {
                    location.reload();
                });

            }
        },

        error: function (arg1, arg2, arg3) {
            console.log(arg3);
        }
    });

});
//#endregion
