var $ = jQuery;
var pk_registro = 0;


function validar() {
    var retorno = true;

    if ($('#descripcion').val().length < 1) {
        retorno = false;
        $('#descripcion').css("background-color", " #ffdddd");;
    }

    if ($('#tipo').val() == 0) {
        retorno = false;
        $('#tipo').css("background-color", " #ffdddd");;
    }

    if ($('#publico').val() == 3) {
        retorno = false;
        $('#publico').css("background-color", " #ffdddd");;
    }

    if ($('#precio').val().length < 1) {
        retorno = false;
        $('#precio').css("background-color", " #ffdddd");;
    }

    if ($('#costo').val().length < 1) {
        retorno = false;
        $('#costo').css("background-color", " #ffdddd");;
    }

    return retorno;
}


$('#guardar').click(function () {

    if (validar()) {

        $.LoadingOverlay("show", {
            color: "rgba(255, 255, 255, 0)"
        });

        var parametros = {
            "pk_orden": $("#pk_orden").val(),
            "tipo": $("#tipo").val(),
            "publico": $("#publico").val(),
            "precio": $("#precio").val(),
            "costo": $("#costo").val(),
            "descripcion": $("#descripcion").val(),
            "fk_usuario": $("#fk_usuario").val(),
        };

        $.ajax({

            data: parametros,

            url: 'servicios/agregarRegistro.php',

            type: 'post',

            beforeSend: function () {

            },

            success: function (response) {

                $.LoadingOverlay("hide");
                var myObj = response.objList;

                if (response.codigo == 200) {

                    pk_registro = myObj.pk_registro;

                    if ($("#archivo").val().length > 2) {
                        upImagen(pk_registro);
                    }

                    swal("Guardado exitoso", "El registro se guardó correctamente", "success").then(function () {
                        $(location).attr('href', "verOrden.php?id=" + $("#pk_orden").val());
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


function upImagen(pk_registro) {

    var f = $(this);
    var formData = new FormData(document.getElementById("formuploadajax"));
    formData.append("archivo", $('#archivo').val());
    formData.append("pk_registro", pk_registro);

    $.ajax({
        url: "servicios/imagenRegistro.php",
        type: "post",
        dataType: "html",
        data: formData,
        cache: false,
        contentType: false,
        processData: false,

        beforeSend: function () {

        },

        success: function (response) {

        },

        error: function (arg1, arg2, arg3) {
            alert(arg3);
        }

    });

}
