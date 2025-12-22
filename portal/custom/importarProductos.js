var $ = jQuery;


$(document).ready(function () {

});


//IMPORTAR
//#region
function validarImportacion() {
    var retorno = true;

    if ($('#archivo_productos').val().length < 5) {
        retorno = false;
        $('#archivo_productos').css("background-color", " #ffdddd");
    }

    return retorno;
}


$(document).on("click", "#btnImportar", function (e) {

    e.preventDefault();

    if (validarImportacion()) {


        $.LoadingOverlay("show", {
            color: "rgba(255, 255, 255, 0)"

        });

        var formData = new FormData(document.getElementById("formuploadajax"));

        $.ajax({
            url: "servicios/importarProductos.php",
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

                var myObj = JSON.parse(response);

                if (myObj.codigo == 200) {

                    swal("Importación exitosa", "Los registros se importaron correctamente", "success").then(function () {
                        $(location).attr('href', 'verProductos.php');
                    });

                } else {

                    swal("Error", myObj.descripcion, "error").then(function () {
                        location.reload();
                    });

                }

            },

            error: function (arg1, arg2, arg3) {
                alert(arg3);
            }

        });

    }
})
//#endregion
