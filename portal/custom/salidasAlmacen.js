var $ = jQuery;
var total = 0;
var subtotal = 0;
var descuento = 0;
var iva = 0;
var pdf = "";
var nentrada = 0;


$(document).ready(function () {

    if ($("#fk_sucursal").val() == 0) {
        $("#modalSucursales").modal("show");
    }

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
                case 6:
                    total = total + parseFloat($(this).text());
                    break;
            }

        });

    });

    $("#total").text("$" + total.toFixed(2));

}



//SUCURSALES
//#region
$(".fc").click(function () {

    var fk_sucursal = $(this).closest('tr').find('td:eq(0)').text().trim();
    var sucursal = $(this).closest('tr').find('td:eq(1)').text().trim();
    var almacen = $(this).closest('tr').find('td:eq(2)').text().trim();

    $("#fk_sucursal").val(fk_sucursal);
    $("#lasucursal").text(sucursal + ". " + almacen);

    $("#modalSucursales").modal("hide");

    $("#clave").focus();

});
//#endregion




//PRODUCTOS
//#region
$('#clave').on('select2:select', function (event) {
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
            "clave": $("#clave").val().toString()
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


                    if (response.objList.existencias > 0) {
                        $("#nombred").val(response.objList.nombre);
                        $("#costod").val(response.objList.costo);
                        $("#imagen").val(response.objList.imagen);
                        $("#claveo").val(response.objList.pk_producto);
                        $("#barcode").val(response.objList.codigobarras);
                        $("#existencias").val(response.objList.existencias);
                        agregarProducto();
                    } else {
                        swal('Mensaje', 'No hay existencias en almacén', 'info');
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


function agregarProducto() {

    var idp = ($("#claveo").val() + "*-*" + $("#serie").val());

    var idpa = idp.split("*-*");

    var claveo = $("#claveo").val();
    var clave = $("#barcode").val();
    var nombre = $("#nombred").val();
    var descripcion = clave + "|" + nombre;
    var serie = $("#serie").val();
    var sucursal = $("#fk_sucursal").val();
    var precio = $("#costod").val();
    var cantidad = $("#existencias").val();
    var imagen = $("#imagen").val();

    if ($("#entradas tbody tr[id='" + idp + "']").length > 0) {

        var cantidadActual = parseFloat($("#entradas tbody tr[id='" + idp + "']").find('td:eq(4) input[type="number"]').val().trim());
        var cantidadNueva = cantidadActual + 1;

        if (cantidadNueva > cantidad) {

            swal("Mensaje", "La cantidad sobrepasa las existencias en almacén.", "info");

        } else {

            var total = parseFloat(parseInt(cantidadNueva) * precio.replace(/,/g, "")).toFixed(2);
            $("#entradas tbody tr[id='" + idp + "']").find('td:eq(4) input[type="number"]').val(cantidadNueva);
            $("#entradas tbody tr[id='" + idp + "']").find('td:eq(6)').text(total);

        }

    } else {

        var total = parseFloat(1 * precio.replace(/,/g, "")).toFixed(2);

        var contenido = `
            <tr id='${idp}'>
                <td><i id='${idp}' class='bx bx-trash eliminar' style='padding: 3px; background-color: red; color:white'></i></td>
                <td>${imagen}</td>
                <td style='white-space:normal'>${descripcion}</td>
                <td>${sucursal}</td>
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
            var precio = input.closest("tr").find("td:eq(5) input[type='text']").val().replace(/,/g, "");
            var total = parseFloat(precio * cantidad).toFixed(2);
            input.closest("tr").find("td:eq(7)").text(total);
            getTotal();
        });
    }

    var cantidad = $(this).val();
    var precio = $(this).closest("tr").find("td:eq(5) input[type='text']").val().replace(/,/g, "");
    var total = parseFloat(precio * cantidad).toFixed(2);

    $(this).closest("tr").find("td:eq(6)").text(total);

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
    var cantidad = $(this).closest("tr").find("td:eq(4) input[type='number']").val();
    var total = parseFloat(precio * cantidad).toFixed(2);

    $(this).closest("tr").find("td:eq(6)").text(total);

    getTotal();

});


function limpiar() {
    $('#clave').val([]).trigger('change');
    $("#nombred").val("");
    $("#claveo").val("");
    $("#serie").val("");
    $("#cantidad").val("");
    $("#cantidadtmp").val("");
    $("#costod").val("");
    $("#existencias").val("");
    $('#nombred').css('background-color', '#fff');
}
//#endregion




//GUARDAR
//#region
function getProductos() {

    var data = [];

    $("#entradas tbody tr").each(function (index) {

        var idp, clave, fk_sucursal, serie = "", cantidad, unitario, total;

        var idp_valor = $(this).attr('id');
        var idpa = idp_valor.split("*-*");
        idp = parseInt(idpa[0]);

        $(this).children("td").each(function (index2) {

            switch (index2) {

                case 2:
                    clave = $(this).text().trim().split("|")[0];
                    break;

                case 3:
                    var ids = $(this).text().trim();
                    var idss = ids.split("-");
                    fk_sucursal = parseInt(idss[0]);
                    break;

                case 4:
                    cantidad = parseFloat($(this).find("input[type='number']").val());
                    break;

                case 5:
                    unitario = parseFloat($(this).find("input[type='text']").val().replace(/,/g, ""));
                    break;

                case 6:
                    total = parseFloat($(this).text());
                    break;

            }

        });

        var myObj = { "fk_producto": idp, "clave": clave, "fk_sucursal_almacen": fk_sucursal, "serie": serie, "cantidad": cantidad, "unitario": unitario, "total": total };

        data.push(myObj);

    });

    return data;

}


function validarGuardado() {

    var retorno = true;

    if ($('#motivo_salida').val() == 0) {
        retorno = false;
        $('#motivo_salida').css('background-color', '#ffdddd');
    }


    if (getProductos().length < 1) {
        retorno = false;
        swal("Error", "Orden vacía. Ningún producto seleccionado", "error");
    }

    return retorno;

}


$('#salida').click(function () {

    if (validarGuardado()) {

        $("#salida").attr("disabled", "disabled");

        var parametros = {
            "fk_usuario": $("#usuario").val(),
            "fk_almacen": $("#fk_sucursal").val(),
            "total": total,
            "productos": getProductos(),
            "motivo_salida": $("#motivo_salida").val(),
            "observaciones": $("#observaciones").val()
        };

        $.ajax({

            data: JSON.stringify(parametros),

            url: 'servicios/salidasAlmacen.php',

            type: 'post',

            dataType: 'json',

            contentType: "application/json; charset=utf-8",

            beforeSend: function () {

            },

            success: function (response) {

                if (response.codigo == 200) {

                    nentrada = response.objList.pk_salida;

                    $("#nentrada").text($("#contrato").val());
                    $("#pdf").attr("href", "salidasAlmacenPDF.php?id=" + nentrada);
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
    $(location).attr("href", "salidasAlmacen.php");
});

$('#ver_registros').click(function () {
    $(location).attr("href", "verSalidasAlmacen.php");
});

$('#inicio').click(function () {
    $(location).attr("href", "index.php");
});

$(document).on("input", ".precio", function () {
    $(this).val(currencyMX($(this).val()));
});
//#endregion
