var $ = jQuery;
var checados = "";
var total = 0;
var pdf = "";
var nentrada = 0;
var productosData = {};


$(document).ready(function () {

    $('#modalEmpresa').modal('show');

    $('.select2').select2({
        tags: false,
        placeholder: "Ingrese el código de barras del producto",
    });

    $('#cantidad').numeric("");

    $('#clave').select2({
        ajax: {
            url: 'servicios/getProductosSelectSearch.php',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    busqueda: params.term
                };
            },
            processResults: function (data) {
                data.forEach(producto => {
                    productosData[producto.id] = producto;
                });
                return {
                    results: data //Array de objetos { id: valor, text: texto }
                };
            },
            cache: true
        },
        placeholder: "Buscar productos...",
        minimumInputLength: 5,
        allowClear: true,
        escapeMarkup: function (markup) { return markup; },
        templateResult: function (data) {
            return $(`<span data-id='${data.id}'>${data.text}</span>`)
        }
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

    $("#total").val(total);
}



//SUCURSALES
//#region
$('.fs').click(function () {

    var ids = this.id;
    var idss = ids.split("*-*");

    $("#sucursal").val(idss[1]);

    $(this).children("td").each(function (index2) {
        switch (index2) {

            case 0:
                $("#lasucursal").text($(this).text().trim());
                break;

        }

    });

    $('#modalEmpresa').modal('hide');

    $("#tablaEmpresa").html("");

    //ELIMINAR LA SUCURSAL SELECCIONADA DEL SELECT DE ALMACENES
    $("#proveedor option").each(function () {
        var value = $(this).val();
        var option = $(this);

        if (value == idss[1]) {
            option.remove();
        }
    });

    setTimeout(function () {
        $("#clave").focus();
    }, 100);

});
//#endregion



//PRODUCTOS
//#region
$('#clave').on("select2:select", function (event) {

    var id = event.params.data.id;
    var text = event.params.data.text;
    var codigobarras = text.split('|')[0].trim();

    getProducto(0, codigobarras);

    $('#clave').val([]).trigger('change');

});


function getProducto(tipo, codigobarras) {

    var retorno = 0;
    var error = 0;

    if (!codigobarras || codigobarras.length < 1) {
        error = 1;
        $('#clave').css('background-color', '#ffdddd');
    } else {
        $('#clave').css('background-color', '#fff');
    }

    if (error == 0) {

        $.LoadingOverlay("show", {
            color: "rgba(255, 255, 255, 0)"
        });

        var parametros = {
            "clave": codigobarras
        };

        $.ajax({

            data: parametros,

            url: 'servicios/getProductoByClave.php',

            type: 'get',

            beforeSend: function () {
            },

            success: function (response) {
                $.LoadingOverlay("hide");

                if (response.codigo == 200) {

                    if (tipo == 0) {

                        $("#nombre").val(response.objList.nombre);
                        $("#precio").val(response.objList.costo);
                        $("#imagen").val(response.objList.imagen);
                        $("#codigobarras").val(response.objList.codigobarras);
                        $("#existencias").val(response.objList.existencias);
                        $("#claveo").val($("#codigobarras").val());
                        agregarProducto();

                    }

                } else {

                    swal("Mensaje", "El producto no pudo ser encontrado. Verifica que este dado de alta en el sistema o el código sea correcto", "info")
                        .then(function () {
                            $("#clave").focus();
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


function validarAgregar() {

    var retorno = true;

    if ($("#clave").val([]).length < 1) {
        retorno = false;
        $('.select2-selection').css('background-color', '#ffdddd');
    }

    if ($("#nombre").val().length < 1) {
        retorno = false;
        $('#nombre').css('background-color', '#ffdddd');
    }

    return retorno;

}


function agregarProducto() {

    var idp = ($("#claveo").val() + "*-*" + $("#serie").val());

    var idpa = idp.split("*-*");

    var claveo = $("#claveo").val();
    var clave = $("#codigobarras").val();
    var nombre = $("#nombre").val();
    var descripcion = clave + " | " + nombre;
    var serie = $("#serie").val();
    var sucursal = $("#fk_sucursal").val();
    var precio = $("#precio").val();
    var imagen = $("#imagen").val();
    var cantidad = $("#existencias").val();

    if ($("#entradas tbody tr[id='" + idp + "']").length > 0) {

        var cantidadActual = parseFloat($("#entradas tbody tr[id='" + idp + "']").find('td:eq(3) input').val().trim());
        var cantidadNueva = parseFloat(cantidadActual + 1);

        if (cantidadNueva > cantidad) {

            swal("Mensaje", "La cantidad sobrepasa las existencias en almacén. Existencias: " + cantidad, "info");

        } else {

            var total = parseFloat(parseInt(cantidadNueva) * precio.replace(/,/g, "")).toFixed(2);
            $("#entradas tbody tr[id='" + idp + "']").find('td:eq(3) input').val(cantidadNueva);
            $("#entradas tbody tr[id='" + idp + "']").find('td:eq(5)').text(total);

        }

    } else {

        var total = parseFloat(1 * precio.replace(/,/g, "")).toFixed(2);

        var contenido = `
            <tr id='${idp}'>
                <td><i id='${idp}' class='bx bx-trash eliminar' style='padding: 3px; background-color: red; color:white'></i></td>
                <td>${imagen}</td>
                <td style='white-space:normal'>${descripcion}</td>
                <td>
                    <input type='number' class='form-control cantidad' value='1'>
                    <input type='hidden' class='form-control cantidad_almacen' value='${cantidad}'>
                    <p class='badge-primary-integra mt-1'>${cantidad} en almacén</p>
                </td>
                <td>
                    <input type='text' class='form-control precio' value='${precio}'>
                    <input type='hidden' class='form-control precio_og' value='${precio}'>
                </td>
                <td>${total}</td>
            </tr>
        `;

        $("#entradas tbody").append(contenido);

    }

    getTotal();

    limpiar();

    $("#clave").focus();

}


$(document).on('click', '.eliminar', function () {
    $(this).closest('tr').remove();
    getTotal();
});


$(document).on("input", ".cantidad", function () {

    var input = $(this);
    var cantidad_ingresada = $(this).val();
    var cantidad_almacen = $(this).next("input.cantidad_almacen").val();

    if (parseInt(cantidad_ingresada) > cantidad_almacen || parseInt(cantidad_ingresada) <= 0) {
        swal('Mensaje', 'La cantidad ingresada es incorrecta, favor de ingresar una cantidad correcta', 'info').then(function () {
            input.val(cantidad_almacen);
            var cantidad = input.val();
            var precio = input.closest("tr").find("td:eq(4) input[type='text']").val().replace(/,/g, "");
            var total = parseFloat(precio * cantidad).toFixed(2);
            input.closest("tr").find("td:eq(5)").text(total);
            getTotal();
        });
    }

    var cantidad = $(this).val();
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


function limpiar() {
    $("#clave").val("");
    $("#nombre").val("");
    $("#existencias").val("");
    $("#cantidad").val(1);
    $("#serie").val("");
    $("#precio").val(0);
    $("#imagen").val("");
    $('#nombre').css('background-color', '#fff');
}


$(document).on('mouseenter', '.select2-results__option', function (e) {

    var $option = $(this);
    var productoId = $(this).find('span').attr('data-id');

    var parametros = {
        "pk_producto": productoId
    }

    $.ajax({
        url: 'servicios/getProductoExistencias.php',

        method: 'GET',

        data: parametros,

        success: function (response) {

            var existencias = response.objList.cantidades;

            $option.attr('data-title', existencias);

        },
        error: function () {
            console.error('Error al consumir el servicio PHP.');
        }
    });

});


$('#clave').on('select2:open', function () {

    $(document).on('keyup keydown', 'input.select2-search__field', function (e) {
        if (e.keyCode === 13) {

            //e.preventDefault();

            let $select = $("#clave");

            setTimeout(function () {

                var items = $('.select2-results__options li');

                if (items.length > 0) {
                    var selectedOption = $('.select2-results__option--highlighted span').attr('data-id');
                    var text = $('.select2-results__option--highlighted span').text();

                    if (!selectedOption) {
                        swal('Mensaje', 'Producto no encontrado', 'info');
                        $select.val(null).trigger('change');
                        $select.select2('close');
                        return
                    }

                    var codigobarras = text.split('|')[0].trim();

                    getProducto(0, codigobarras);

                    $select.val(null).trigger('change');
                    $select.select2('close');
                } else {
                    console.log('El producto aún no ha sido cargado.');
                }
            }, 1000);
        }
    });

});
//#endregion



//GUARDAR
//#region
function getProductos() {

    var data = [];

    $("#entradas tbody tr").each(function (index) {

        var id, nombre, serie = "", cantidad, unitario, total;

        var idp = this.id;
        var idpp = idp.split("*");
        id = idpp[0];

        $(this).children("td").each(function (index2) {

            switch (index2) {

                case 2:
                    nombre = $(this).text().trim();
                    break;

                case 3:
                    cantidad = parseInt($(this).find("input[type='number']").val());
                    break;

                case 4:
                    unitario = parseFloat($(this).find("input[type='text']").val().replace(/,/g, ""));
                    break;

                case 5:
                    total = parseFloat($(this).text().trim());
                    break;

            }

        });

        var myObj = { "codigobarras": id, "cantidad": cantidad, "nombre": nombre, "serie": serie, "unitario": unitario, "total": total };

        data.push(myObj);

    });

    return data;

}


function validarGuardado() {

    var retorno = true;

    if ($('#proveedor').val() == 0) {
        retorno = false;
        $('#proveedor').css('background-color', '#ffdddd');
    }

    if ($('#entradas tbody tr').length < 1) {
        retorno = false;
        swal("Error", "La orden se encuentra vacía", "error");
    }

    return retorno;

}


$('#guardar').click(function () {

    if (validarGuardado()) {

        $("#guardar").attr("disabled", "disabled");

        var parametros = {
            "fk_almacen": $("#sucursal").val(),
            "fk_usuario": $("#usuario").val(),
            "fk_almacen_destino": $("#proveedor").val(),
            "observaciones": $("#observaciones").val(),
            "total": $("#total").val(),
            "productos": getProductos()
        };

        $.ajax({

            data: JSON.stringify(parametros),

            url: 'servicios/agregarTransferencia.php',

            type: 'post',

            dataType: 'json',

            contentType: "application/json; charset=utf-8",

            beforeSend: function () {

            },

            success: function (response) {

                if (response.codigo == 200) {

                    nentrada = response.objList.nentrada;

                    $("#nentrada").text(nentrada);
                    $("#pdf").attr("href", "transferenciaPDF.php?id=" + nentrada);
                    $('#exito').modal('show');

                } else {

                    swal("Error", "Hubo un problema, vuelva a intentarlo", "error").then(function () {
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
    $(location).attr("href", "agregarTransferencia.php");
});

$('#ver_registros').click(function () {
    $(location).attr("href", "verTransferencias.php");
});

$('#inicio').click(function () {
    $(location).attr("href", "index.php");
});
//#endregion
