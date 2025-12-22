var $ = jQuery;
var myFormData;
let arrImages = [];


$(document).ready(function () {

    initDropzone();

});


function initDropzone() {

    let myDropzoneEdit = new Dropzone('.dropzone', {
        url: '/portal/',
        maxFilesize: 2,
        maxFiles: 4,
        acceptedFiles: 'image/jpeg, image/png',
        addRemoveLinks: true,
        dictRemoveFile: 'Quitar'
    });

    myDropzoneEdit.on('addedfile', file => {
        arrImages.push(file);
        if (arrImages.length > 0) {
            $(".dz-default").addClass('d-none');
        }
    });

    myDropzoneEdit.on('removedfile', file => {
        let i = arrImages.indexOf(file);
        arrImages.splice(i, 1);
        if (arrImages.length <= 0) {
            $(".dz-default").removeClass('d-none');
        }
    });

}



//GUARDAR
//#region
function validar() {

    var retorno = true;

    if ($('#nombre').val().length < 1) {
        retorno = false;
        $('#nombre').css('background-color', '#ffdddd');
    }

    if ($('#fk_presentacion').val() == 0) {
        retorno = false;
        $('#fk_presentacion').css('background-color', '#ffdddd');
    }

    if ($('#fk_categoria').val() == 0) {
        retorno = false;
        $('#fk_categoria').css('background-color', '#ffdddd');
    }

    if ($('#costo').val().length < 1) {
        retorno = false;
        $('#costo').css('background-color', '#ffdddd');
    }

    if ($('#precio1').val().length < 1) {
        retorno = false;
        $('#precio1').css('background-color', '#ffdddd');
    }

    if ($('#inventario').val() == 2) {
        if ($('#inventariomin').val().length == 0) {
            retorno = false;
            $('#inventariomin').css('background-color', '#ffdddd');
        }

        if ($('#inventariomax').val().length == 0) {
            retorno = false;
            $('#inventariomax').css('background-color', '#ffdddd');
        }
    }

    return retorno;

}


$(document).on("click", "#guardar", function () {

    if (validar()) {

        $.LoadingOverlay("show", {
            color: "rgba(255, 255, 255, 0)"
        });

        var parametros = {
            "nombre": $("#nombre").val(),
            "codigo_barras": $("#codigo_barras").val(),
            "fk_presentacion": $("#fk_presentacion").val() ?? 0,
            "fk_categoria": $("#fk_categoria").val() ?? 0,
            "descripcion": $("#descripcion").val(),
            "costo": $("#costo").val().replace(/,/g, "") ?? 0,
            "precio": $("#precio1").val().replace(/,/g, "") ?? 0,
            "precio2": $("#precio2").val().replace(/,/g, "") ?? 0,
            "precio3": $("#precio3").val().replace(/,/g, "") ?? 0,
            "precio4": $("#precio4").val().replace(/,/g, "") ?? 0,
            "utilidad": $("#utilidad_1").val() ?? 0,
            "utilidad2": $("#utilidad_2").val() ?? 0,
            "utilidad3": $("#utilidad_3").val() ?? 0,
            "utilidad4": $("#utilidad_4").val() ?? 0,
            "inventario": $("#inventario").val() ?? 1,
            "inventariomin": $("#inventariomin").val() ?? 0,
            "inventariomax": $("#inventariomax").val() ?? 0,
            "clave_producto_sat": $("#clave_producto_sat").val(),
            "clave_unidad_sat": $("#clave_unidad_sat").val() ?? 0
        };

        $.ajax({
            data: parametros,

            url: 'servicios/agregarProducto.php',

            type: 'POST',

            contentType: "application/x-www-form-urlencoded;charset=utf-8",

            beforeSend: function () {

            },

            success: function (response) {

                if (response.codigo == 200) {

                    var pk_producto = response.objList.pk_producto;

                    if (arrImages.length > 0) {

                        guardarImagenes(pk_producto);

                    } else {

                        $.LoadingOverlay("hide");

                        swal("Éxito", "El registro se guardó correctamente", "success").then(function () {
                            location.reload();
                        });

                    }

                } else {

                    $.LoadingOverlay("hide");

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


function guardarImagenes(pk_producto) {

    var f = $(this);
    var formData = new FormData();
    formData.append("pk_producto", pk_producto);

    for (var i = 0; i < arrImages.length; i++) {
        var imageFile = arrImages[i];
        formData.append('file[]', imageFile);
    }

    $.ajax({
        url: "servicios/agregarProductoImagenes.php",
        type: "post",
        dataType: "html",
        data: formData,
        cache: false,
        contentType: false,
        processData: false,

        beforeSend: function () {

        },

        success: function (response) {

            response = JSON.parse(response);

            $.LoadingOverlay("hide");

            if (response.codigo == 200) {

                swal("Éxito", "El registro se guardó correctamente", "success").then(function () {
                    $(location).attr('href', 'verProductos.php')
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
}
//#endregion




//EXTRAS
//#region
$(document).on("input", ".utilidad", function () {

    var id = (this.id).split("_")[1];
    var valor = parseFloat($(this).val());
    var costo = parseFloat($("#costo").val().replace(/,/g, ""));
    var utilidad = costo * (valor / 100);
    var precio = (costo + utilidad).toFixed(2);
    $("#precio" + id + "").val(currencyMX(precio));

});


$(document).on("blur", "#costo", function () {

    var id, utilidad, anterior_precio, nuevo_precio, costo = parseFloat($(this).val().replace(/,/g, ""));

    $(".utilidad").each(function () {
        if ($(this).val()) {
            id = (this.id).split("_")[1];
            utilidad = parseFloat($(this).val());
            anterior_precio = $(`#precio${id}`).val().replace(/,/g, "");
            nuevo_precio = parseFloat(costo + (costo * (utilidad / 100)));
            $(`#precio${id}`).val(nuevo_precio);
        }
    });
});


$(document).on("input", "#costo, .precio", function () {
    $(this).val(currencyMX($(this).val()));
});


$(document).on("change", "#inventario", function () {

    if ($(this).val() != 2) {
        $('#inventariomin').val(0);
        $('#inventariomin').prop('disabled', true);
        $('#inventariomax').val(0);
        $('#inventariomax').prop('disabled', true);
    } else {
        $('#inventariomin').prop('disabled', false);
        $('#inventariomax').prop('disabled', false);
    }

});
//#endregion
