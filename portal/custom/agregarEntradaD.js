var $ = jQuery;
var checados = "";
var total = 0;
var subtotal = 0;
var iva = 0;
var pdf = "";
var nentrada = 0;


$(document).ready(function () {

    $('#cantidad').numeric("");
    $('#precio').numeric(".");

    setTimeout(function () {
        $("#clave").focus();
    }, 100);

    $('.select2').select2({
        tags: false,
        placeholder: "Escanea el código de barras"
    });

});


function getTotal() {

    total = 0;

    $("#entradas tbody tr").each(function (index) {

        $(this).children("td").each(function (index2) {
            switch (index2) {
                case 5:
                    total = total + parseFloat($(this).text());
                    break;
            }

        });

    });

    $("#total").text("$" + total);

}




//PRODUCTOS
//#region
$('#clave').on('select2:select', function (e) {

    getProducto();

    $('#clave').val([]).trigger('change');

});


function getProducto() {

    $.LoadingOverlay("show", {
        color: "rgba(255, 255, 255, 0)"
    });

    var retorno = 0;
    var error = 0;

    if ($('#clave').val().length < 1) {
        error = 1;
        $('#clave').css('background-color', '#ffdddd');
    }

    if (error == 0) {

        var parametros = {
            "clave": $("#clave").val().toString().replace(',', '')
        };

        $.ajax({

            data: parametros,

            url: 'servicios/getProductoByClave.php',

            type: 'GET',

            beforeSend: function () {
            },

            success: function (response) {

                $.LoadingOverlay("hide");

                if (response.codigo == 200) {

                    $("#nombred").val(response.objList.nombre);
                    $("#costod").val(response.objList.costo);
                    $("#imagen").val(response.objList.imagen);
                    $("#claveo").val(response.objList.pk_producto);
                    $("#barcode").val(response.objList.codigobarras);
                    $("#cantidad").focus();

                } else {

                    swal("Mensaje", "El producto no pudo ser encontrado. Verifica que este dado de alta en el sistema o el código sea correcto", "info")
                        .then(function () {
                            $("#clave").focus();
                            limpiar();
                        });

                }

            },

            error: function (arg1, arg2, arg3) {
                console.log(arg3);
            }

        });
    }

    return retorno;
}


$(document).off("keypress", "#cantidad").on("keypress", "#cantidad", function () {

    var keycode = (event.keyCode ? event.keyCode : event.which);
    if (keycode == '13') {
        if (validarAgregar()) {
            agregarProducto();
        }
    }

})


function validarAgregar() {

    var retorno = true;

    if ($("#clave").val([]).length < 1) {
        retorno = false;
        $('.select2-selection').css('background-color', '#ffdddd');
    }

    if ($("#nombred").val().length < 1) {
        retorno = false;
        $('#nombred').css('background-color', '#ffdddd');
    }

    if ($("#cantidad").val().length < 1) {
        retorno = false;
        $('#cantidad').css('background-color', '#ffdddd');
    }

    return retorno;

}


function limpiar() {
    $('#clave').val([]).trigger('change');
    $("#claveo").val("");
    $("#nombred").val("");
    $("#costod").val("");
    $("#cantidad").val(1);
    $("#imagen").val("");
    $("#barcode").val("");
    $("#seried").val("");
    $('#nombred').css('background-color', '#fff');
    $('#seried').css('background-color', '#fff');
    $('#cantidad').css('background-color', '#fff');
}


function agregarProducto() {

    if (validarAgregar()) {

        var nombre = $("#nombred").val();
        var clave = $("#barcode").val();
        var imagen = $("#imagen").val();
        var cantidad = $("#cantidad").val();
        var serie = $("#seried").val();
        var unitario = $("#costod").val();
        var descripcion = clave + "|" + nombre;
        var idp = $("#claveo").val() + "*-*" + serie;
        var idpa = idp.split("*-*");
        var total = (parseFloat(unitario.replace(/,/g, "")) * cantidad).toFixed(2);


        //Verificar si el producto con la serie ingresada ya existe fue agregado anteriormente
        var encontrado = false;
        $("#entradas tbody tr").each(function () {
            //var seriex = $(this).closest('tr').find('td:eq(4)').text();
            var seriex = "";

            if (serie != '') {
                if (serie == seriex) {
                    encontrado = true;
                }
            }

        });


        if (encontrado == false) { //El producto aún no se ha agregado

            var existeSinSerie = false;
            $("#entradas tbody tr").each(function () { //Verificar si el producto sin serie ya existe para aumentar su cantidad en +1
                if ($(this).attr("id") === idp) {
                    existeSinSerie = true;
                    var cantidadAnterior = parseFloat($(this).find('td:eq(3) input[type=number]').val()) + 1;
                    var precioAnterior = parseFloat($(this).find('td:eq(4) input[type=text]').val());
                    var totalAnterior = cantidadAnterior * precioAnterior;
                    $(this).find('td:eq(3) input[type=number]').val(cantidadAnterior);
                    $(this).find('td:eq(5)').text(totalAnterior);
                    return false;
                }
            });

            if (!existeSinSerie) { //Si el producto sin serie no existe se crea completamente la fila
                var inputCantidad = "<input type='number' class='form-control cantidad' style='width: 140px;' value='" + cantidad + "'>";
                if (serie.length > 0) {
                    inputCantidad = "<input type='number' class='form-control cantidad' style='width: 140px;' value='" + cantidad + "' disabled>";
                }
                var contenido = `
                    <tr id='${idp}'>
                        <td><i id='${idp}' class='fa fa-trash eliminar' style='padding: 3px; background-color: red; color:white'></i></td>
                        <td>${imagen}</td>
                        <td style='white-space: normal;'>${descripcion}</td>
                        <td>${inputCantidad}</td>
                        <td>
                            <input type='text' class='form-control precio' style='width: 140px;' value='${unitario}'>
                            <input type='hidden' class='form-control precio_og' value='${unitario}'>
                        </td>
                        <td>${total}</td>
                    </tr>
                `;

                $("#entradas tbody").append(contenido);
            }

            getTotal();

            $('.eliminar').click(function () {
                var idpp = this.id;
                $("#entradas tbody tr").each(function () {
                    if (idpp == this.id) {
                        $(this).remove();
                        getTotal();
                        $("#clave").focus();
                    }
                });
            });

        } else {
            swal("Mensaje", "Este producto y serie ya fueron agregados anteriormente", "info").then(function () {
                setTimeout(function () {
                    $('#clave').focus();
                }, 100);
            });
        }

        limpiar();
        $("#seried").val("");

        setTimeout(function () {
            $('#clave').focus();
        }, 100);

    }
}


$(document).on("input", ".cantidad", function () {

    var input = $(this);

    if (parseInt($(this).val()) <= 0) {
        swal('Mensaje', 'Ingrese una cantidad correcta', 'info');
        input.val(1);
    }

    var cantidad = parseInt($(this).val());
    var precio = $(this).closest("tr").find("td:eq(4) input[type='text']").val().replace(/,/g, "");
    var total = parseFloat(precio * cantidad).toFixed(2);

    $(this).closest("tr").find("td:eq(5)").text(total);

    getTotal();

});


$(document).on("input", ".precio", function () {

    var input = $(this);
    var precio_og = parseFloat($(this).next('input').val());

    if (parseFloat($(this).val().replace(/,/g, "")) <= 0) {
        swal('Mensaje', 'Ingrese un precio correcto', 'info');
        input.val(precio_og);
    }

    var precio = $(this).val().replace(/,/g, "");
    var cantidad = $(this).closest("tr").find("td:eq(3) input[type='number']").val();
    var total = parseFloat(precio * cantidad).toFixed(2);

    $(this).closest("tr").find("td:eq(5)").text(total);

    getTotal();

});
//#endregion




//GUARDAR
//#region
function getProductos() {

    var data = [];

    $("#entradas tbody tr").each(function (index) {

        var id, clave, cantidad, serie = "", unitario, total;

        var idp = this.id;
        var idpp = idp.split("*");
        id = parseInt(idpp[0]);

        $(this).children("td").each(function (index2) {

            switch (index2) {

                case 2:
                    clave = $(this).text().trim().split("|")[0];
                    break;

                case 3:
                    cantidad = parseInt($(this).find("input[type='number']").val());
                    break;

                case 4:
                    unitario = parseFloat($(this).find("input[type='text']").val().replace(/,/g, ""));
                    break;

                case 5:
                    total = parseFloat($(this).text());
                    break;

            }

        });

        var myObj = { "fk_producto": id, "clave": clave, "cantidad": cantidad, "serie": serie, "unitario": unitario, "total": total };

        data.push(myObj);

    });

    return data;

}


function validarGuardado() {

    var retorno = true;

    if ($('#sucursal').val() == 0) {
        retorno = false;
        $('#sucursal').css('background-color', '#ffdddd');
    }

    if (getProductos().length < 1) {
        retorno = false;
        swal("Error", "Orden vacía", "error");
    }

    return retorno;
}


$('#guardar').click(function () {

    if (validarGuardado()) {

        $("#guardar").attr("disabled", "disabled");

        $.LoadingOverlay("show", {
            color: "rgba(255, 255, 255, 0)"
        });

        var parametros = {
            "fk_usuario": $("#usuario").val(),
            "total": total,
            "productos": getProductos(),
            "fk_almacen": $("#sucursal").val(),
            "observaciones": $("#observaciones").val()
        };

        $.ajax({

            data: JSON.stringify(parametros),

            url: 'servicios/agregarEntradaD.php',

            type: 'post',

            dataType: 'json',

            contentType: "application/json; charset=utf-8",

            beforeSend: function () {

            },

            success: function (response) {

                $.LoadingOverlay("hide");

                if (response.codigo == 200) {

                    nentrada = response.objList.pk_entrada;

                    $("#nentrada").text($("#contrato").val());
                    $("#pdf").attr("href", "entradaPDF.php?id=" + nentrada);
                    $('#exito').modal('show');

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
//#endregion




//EXTRAS
//#region
$('#nueva').click(function () {
    $(location).attr("href", "agregarEntradaD.php");
});


$('#ver_registros').click(function () {
    $(location).attr("href", "verEntradasD.php");
});


$('#inicio').click(function () {
    $(location).attr("href", "index.php");
});


$("#capturar_series").on('change', function () {

    if ($(this).is(':checked')) {
        $("#seried").removeAttr('disabled');
        $("#cantidad").val('1');
        $("#cantidad").attr('disabled', 'disabled');
    } else {
        $("#seried").attr('disabled', 'disabled');
        $("#cantidad").removeAttr('disabled');
    }

    setTimeout(function () {
        $('#clave').focus();
    }, 100);
})


$('#cerrarm').click(function () {
    $('#modalProductos').modal('hide');
});


$('#salir').click(function () {
    $(location).attr("href", "index.html");
});


$(document).on("input", ".precio, #costo_producto, .precio_pr", function () {
    $(this).val(currencyMX($(this).val()));
});
//#endregion
