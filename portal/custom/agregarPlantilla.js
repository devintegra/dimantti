var $ = jQuery;
let pk_plantilla = $('#pk_plantilla').val();


$(document).ready(function () {

    $('.select2').select2({
        tags: false,
    });

});


//AGREGAR PRODUCTOS
//#region
$("#fk_insumo").on('select2:select', function (e) {

    let selectedOptionText = $(this).find('option:selected');
    let fk_insumo = selectedOptionText.val();
    let nombre = selectedOptionText.text().trim();
    let presentacion = selectedOptionText.attr('data-presentacion');

    if (!verificarInsumo(fk_insumo)) {

        var trHTML = `
            <tr data-id="${fk_insumo}">
                <td><i class='bx bx-x fs-3 eliminar-insumo' style='padding: 3px; color: red; cursor: pointer;'></i></td>
                <td>${nombre}</td>
                <td><input type="number" class="form-control input-cantidad" min="1" value="1" autocomplete="off" style="width: 100px;"></td>
                <td>${presentacion}</td>
            </tr>
        `;

        $('#dtInsumos tbody').append(trHTML);

    } else {
        swal('Mensaje', 'Este producto ya fue agregado anteriormente', 'info');
    }

});


function verificarInsumo(fk_insumo) {

    let retorno = false;

    $('#dtInsumos tbody tr').each(function () {

        let id = parseInt($(this).attr('data-id'));

        if (id == fk_insumo) {
            retorno = true;
            return
        }

    });

    return retorno;

}


$(document).on('click', '.eliminar-insumo', function () {

    let tr = $(this).closest('tr');
    let fk_producto = tr.attr('data-id');

    var parametros = {
        "pk_plantilla": pk_plantilla,
        "fk_producto": fk_producto
    };

    $.ajax({

        data: parametros,

        url: 'servicios/eliminarPlantillaProducto.php',

        type: 'POST',

        beforeSend: function () {

        },

        success: function (response) {

            if (response.codigo == 200) {

                tr.remove();

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



//GUARDAR
//#region
function getInsumos() {

    var data = [];

    $("#dtInsumos tbody tr").each(function (index) {

        var id, nombre, cantidad;

        id = parseInt($(this).attr('data-id'));

        $(this).children("td").each(function (index2) {

            switch (index2) {

                case 1:
                    nombre = $(this).text().trim();
                    break;

                case 2:
                    cantidad = parseFloat($(this).find('input').val());
                    break;
            }

        });

        var myObj = { "id": id, "nombre": nombre, "cantidad": cantidad };

        data.push(myObj);

    });

    return data;

}


function validarGuardado() {

    var retorno = true;

    if ($('#nombre').val().length == 0) {
        retorno = false;
        $('#nombre').css('background-color', '#ffdddd');
    }

    if (getInsumos().length == 0) {
        retorno = false;
        swal('Mensaje', 'No se ha agregado ningún producto a la plantilla', 'info');
    }

    $('#dtInsumos tbody tr').each(function () {

        let inputCantidad = $(this).find('td:eq(2) input');

        if (inputCantidad.val().length == 0 || inputCantidad.val() <= 0) {
            inputCantidad.css('background-color', '#ffdddd');
            retorno = false;
        }

    });

    return retorno;
}


$('#guardar').click(function () {

    if (validarGuardado()) {

        var parametros = {
            "pk_plantilla": pk_plantilla,
            "nombre": $('#nombre').val(),
            "insumos": getInsumos()
        };

        $.ajax({

            data: JSON.stringify(parametros),

            url: 'servicios/agregarPlantilla.php',

            type: 'post',

            dataType: 'json',

            contentType: "application/json; charset=utf-8",

            beforeSend: function () {

            },

            success: function (response) {

                if (response.codigo == 200) {

                    swal("Éxito", "El registro se guardó correctamente", "success").then(function () {
                        $(location).attr('href', 'verPlantillas.php');
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

});
//#endregion



//ELIMINAR
//#region
$("#eliminar").click(function () {
    Swal.fire({
        title: 'Eliminar plantilla',
        text: "¿Estás seguro que deseas eliminar la plantilla?",
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
        "pk_plantilla": pk_plantilla
    };

    $.ajax({
        data: parametros,

        url: 'servicios/eliminarPlantilla.php',

        type: 'POST',

        beforeSend: function () {

        },

        success: function (response) {

            if (response.codigo == 200) {

                swal("Eliminacion exitosa", "El registro se eliminó correctamente", "success").then(function () {
                    $(location).attr("href", "verPlantillas.php");
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
