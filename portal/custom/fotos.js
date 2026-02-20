var $ = jQuery;
var pk_registro = 0;



function validar() {

    var retorno = true;

    if ($('#archivo').val().length < 5) {
        retorno = false;
        $('#archivo').css("background-color", " #ffdddd");;
    }

    return retorno;

}


$('#guardar').click(function () {

    if (validar()) {
        var pk_orden = $("#pk_orden").val();
        $.LoadingOverlay("show", {
            color: "rgba(255, 255, 255, 0)"

        });
        upImagen(pk_orden);
    }

});


function upImagen(pk_orden) {

    var f = $(this);
    var formData = new FormData(document.getElementById("formuploadajax"));
    formData.append("archivo", $('#archivo').val());
    formData.append("pk_orden", pk_orden);
    formData.append("pk_usuario", $("#usuario").val());

    $.ajax({
        url: "servicios/imagenOrden.php",
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
            location.reload();

        },

        error: function (arg1, arg2, arg3) {
            alert(arg3);
        }

    });

}


$('#salir').click(function () {
    $(location).attr("href", "verOrdenes.php");
});
