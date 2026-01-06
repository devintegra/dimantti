var $ = jQuery;
var productosAgregados = [];
var id = 0;


$(document).ready(function () {

    var fk_sucursal;
    $("#sucursal").val().length != 0 ? fk_sucursal = $("#sucursal").val() : fk_sucursal = 0;

    getSucursales(fk_sucursal);
    $('#modalEmpresa').modal('show');

    if ($("#sucursal").val() != 0 && $("#existe_corte").val() == 0) {
        $('#modalSaldoInicial').modal('show');
    }


    $('.select2').select2({
        tags: false,
    });


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


    $('.datepicker').datepicker({
        dateFormat: 'yy-mm-dd'
    });


    setTimeout(function () {
        $("#clave").focus();
        if (!esDispositivoMovil()) {
            $(".navbar-toggler").click();
        }
        $("ul.nav").css('display', 'none');
    }, 100);


    $("#entradas tbody tr").each(function () {
        var id = (this.id).split("*-*");
        var fk_producto = parseInt(id[0]);
        var cantidad = parseInt($(this).find("td:eq(5) input[type='number']").val());

        productosAgregados.push({ fk_producto: fk_producto, cantidad: cantidad });
    });


    getDatosCliente($("#cliente").val());


    getTotal();

});


$(document).on('click', '.navbar-toggler', function () {
    $("ul.nav").toggle();
});


function esDispositivoMovil() {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
}


function getTotal() {
    total = 0;
    subtotal = 0;
    iva = 0;
    $("#entradas tbody tr").each(function (index) {

        $(this).children("td").each(function (index2) {

            switch (index2) {
                case 6:
                    subtotal = subtotal + parseFloat($(this).text().trim().slice(1));
                    break;
            }

        });

        total = subtotal;
        iva = 0;


    });

    total = total.toFixed(2);
    $("#subtotal").text("$" + total);
    $("#total").text("$" + total);
}


//SUCURSALES
//#region
function getSucursales(fk_sucursal) {

    var parametros = {
        "fk_sucursal": fk_sucursal
    }

    $.ajax({

        data: parametros,

        url: 'servicios/getSucursalAlmacenes.php',

        type: 'GET',

        beforeSend: function () {
        },

        success: function (response) {

            if (response.codigo == 200) {

                $('#dtEmpresa').DataTable().destroy();
                $("#dtEmpresa tbody").empty();

                response.objList.forEach(element => {

                    let trHTML = `
                        <tr class="odd gradeX" data-sucursal="${element.fk_sucursal}" data-almacen="${element.pk_sucursal_almacen}">
                            <td class='empresa'>${element.sucursal} - ${element.nombre}</td>
                        </tr>
                    `;

                    $('#dtEmpresa tbody').append(trHTML);

                });

                if (!$.fn.DataTable.isDataTable('#dtEmpresa')) {
                    $('#dtEmpresa').DataTable({
                        responsive: true,
                        language: {
                            "lengthMenu": "Mostrando _MENU_ registros por pagina",
                            "search": "Buscar:",
                            "zeroRecords": "No hay registros",
                            "info": "Mostrando pagina _PAGE_ de _PAGES_",
                            "infoEmpty": "Sin registros disponibles",
                            "infoFiltered": "(filtrando de _MAX_ registros totales)",
                            "paginate": {
                                "previous": "Anterior",
                                "next": "Siguiente"
                            }
                        },
                        fnDrawCallback: function (oSettings) {
                            $('.empresa').click(function () {

                                var pk_sucursal = $(this).closest('tr').attr('data-sucursal');
                                var pk_almacen = $(this).closest('tr').attr('data-almacen');
                                var le = $(this).text();

                                $("#lasucursal").text(le);
                                $("#sucursal").val(pk_sucursal);
                                $("#fk_almacen").val(pk_almacen);

                                $('#modalEmpresa').modal('hide');
                                $("#tablaEmpresa").html("");

                                if ($("#existe_corte").val() == 0) {
                                    $('#modalSaldoInicial').modal('show');
                                }

                            });

                        }
                    });
                }

            }

            $("#modalEmpresa .form-control-sm").val("");
            $("#modalEmpresa .form-control-sm").attr("autocomplete", "off");

        },
        error: function (arg1, arg2, arg3) {
            console.log(arg3);
        }

    });
}
//#endregion





//SALDO INICIAL
//#region
function validarSaldo() {
    var retorno = true;

    if ($('#modalSaldoInicial #saldo_inicial').val().length < 1) {
        retorno = false;
        $('#modalSaldoInicial #saldo_inicial').css('background-color', '#ffdddd');
    }

    return retorno;
}


$(document).on('click', '#modalSaldoInicial #guardar_saldo', function () {

    if (validarSaldo()) {

        var parametros = {
            "fk_sucursal": $("#sucursal").val(),
            "fk_usuario": $("#fk_usuario").val(),
            "saldo": $("#modalSaldoInicial #saldo_inicial").val(),
            "observaciones": $('#modalSaldoInicial #observaciones_saldo').val(),
        };

        $.ajax({

            data: parametros,

            url: 'servicios/agregarSaldoInicial.php',

            type: 'post',

            beforeSend: function () {

            },

            success: function (response) {

                $.LoadingOverlay("hide");

                if (response.codigo == 200) {

                    swal("Éxito", "El saldo se guardó correctamente", "success").then(function () {
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

    }

});
//#endregion





//AGREGAR PRODUCTOS
//#region
$('#clave').on('select2:select', function (e) {

    var selectedOption = e.params.data.id;

    var productoExistente = productosAgregados.find(producto => producto.fk_producto === parseInt(selectedOption));

    if (productoExistente) {
        productoExistente.cantidad++;
    } else {
        productosAgregados.push({ fk_producto: parseInt(selectedOption), cantidad: 1, precio: 0 });
    }

    actualizarTablaProductos($("#cliente").val(), productosAgregados);


    $('#clave').val([]).trigger('change');

});


$(document).on("click", ".eliminar", function () {

    var id = parseInt($(this).closest('tr').attr('id').split("*-*")[0]);

    if (productosAgregados.splice(productosAgregados.findIndex(producto => producto.fk_producto === id), 1)) {

        $(this).closest("tr").remove();

        getTotal();

        $('#clave').val([]).trigger('change');
        $('#clave').focus();

    }

});


$(document).on("input", ".cantidad", function () {

    var cantidad = parseInt($(this).val());
    var precio = parseFloat($(this).closest("tr").find("td:eq(4) input").val().trim());
    var total = precio * cantidad;

    $(this).closest("tr").find("td:eq(6)").text("$" + total);

    var pk = $(this).closest("tr").attr("id");
    pk = pk.split("*-*");
    pk = pk[0];
    var productoExistente = productosAgregados.find(producto => producto.fk_producto === parseInt(pk));
    productoExistente.cantidad = cantidad;

    setTimeout(function () {
        actualizarTablaProductos($("#cliente").val(), productosAgregados);
    }, 100)

    getTotal();

});


$(document).on("input", ".precio", function () {

    var precio = $(this).val();
    var cantidad = $(this).closest("tr").find("td:eq(5) input[type='number']").val();
    var total = precio * cantidad;

    $(this).closest("tr").find("td:eq(6)").text("$" + total);

    getTotal();

});


$(document).on("change", "#cliente", function () {

    var fk_cliente = $(this).val();

    actualizarTablaProductos(fk_cliente, productosAgregados);

    getDatosCliente(fk_cliente);

})


function actualizarTablaProductos(fk_cliente, productosAgregados) {

    var parametros = {
        "fk_cliente": fk_cliente,
        "productos": productosAgregados,
    };

    $.ajax({
        data: JSON.stringify(parametros),

        url: 'servicios/addProductosTablaVenta.php',

        type: 'post',

        dataType: 'json',

        contentType: "application/json; charset=utf-8",

        beforeSend: function () {

        },

        success: function (response) {

            if (response.codigo == 200) {

                $('#entradas').DataTable().destroy();
                $("#entradas tbody").empty();

                response.objList.forEach(element => {

                    let trHTML = `
                        <tr class='odd gradeX fp' id='${element.pk_producto}*-*${element.codigobarras}'>
                            <td>
                                <i class='fa fa-trash eliminar' style='padding: 3px; background-color: red; color:white'></i>
                            </td>
                            <td>
                                <img style='border-radius: 7px; width:70px; height:70px; object-fit:cover; cursor: pointer;' class='btnImgProducto' data-id='${element.pk_producto}' title='Ver imágenes' loading='lazy' src='${element.imagen}'>
                            </td>
                            <td style='white-space:normal'>${element.codigobarras} - ${element.nombre}</td>
                            <td>$${element.precio}</td>
                            <td>
                                <input type='number' class='form-control precio' style='width: 140px;' min='1' value='${element.precio}' disabled>
                            </td>
                            <td>
                                <input type='number' class='form-control cantidad' style='width: 140px;' min='1' value='${element.cantidad}'>
                            </td>
                            <td>
                                $${element.total}
                            </td>
                        </tr>
                    `;

                    $("#entradas tbody").append(trHTML);

                });

                getTotal();

                updateCambioCheckIn();

            } else {

                swal('Mensaje', 'Error al añadir el producto, verifique e intente de nuevo', 'info');

            }

            $("#clave").focus();
        },

        error: function (arg1, arg2, arg3) {
            console.log(arg3);
        }

    });

}


function getDatosCliente(fk_cliente) {

    var parametros = {
        "fk_cliente": fk_cliente
    };

    $.ajax({
        data: parametros,

        url: 'servicios/getCliente.php',

        type: 'post',

        beforeSend: function () {

        },

        success: function (response) {

            var myObj = response.objList;

            $("#cliente_dias").val(myObj.dias_credito);
            $("#cliente_limite").val(myObj.limite);
            $("#cliente_credito").val(myObj.credito);

        },

        error: function (arg1, arg2, arg3) {
            console.log(arg3);
        }

    });

}


$(document).on('mouseenter', '.select2-results__option', function (e) {

    var $option = $(this);
    var productoId = $(this).find('span').attr('data-id');

    if (productoId) {
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
    }

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

                    if (!selectedOption) {
                        swal('Mensaje', 'Producto no encontrado', 'info');
                        $select.val(null).trigger('change');
                        $select.select2('close');
                        return
                    }

                    var productoExistente = productosAgregados.find(producto => producto.fk_producto === parseInt(selectedOption));

                    if (productoExistente) {
                        productoExistente.cantidad++;
                    } else {
                        productosAgregados.push({ fk_producto: parseInt(selectedOption), cantidad: 1, precio: 0 });
                    }

                    actualizarTablaProductos($("#cliente").val(), productosAgregados);

                    $select.val(null).trigger('change');
                    $select.select2('close');
                } else {
                    console.log('El producto aún no ha sido cargado.');
                }
            }, 1000);
        }
    });

});


function updateCambioCheckIn() {

    var recibe = parseFloat($("#recibe").val());
    var total = $("#total").text().trim().slice(1);
    var cambio = 0;

    if (recibe > 0) {

        cambio = recibe - total;
        $("#cambio").text("$" + cambio);

        if (cambio >= 0) {
            $("#cambio").css("color", "#4ea93b");
        } else {
            $("#cambio").css("color", "#ff4040");
        }
    }

}
//#endregion





//CHECK-IN
//#region
$("#descuento").on("input", function () {

    var descuento = $(this).val();
    var subtotal = parseFloat($("#subtotal").text().trim().slice(1));

    var total = (subtotal - descuento).toFixed(2);
    $("#total").text("$" + total);

});


$("#recibe").on("input", function () {

    var recibe = $(this).val();
    var total = parseFloat($("#total").text().trim().slice(1));

    var total = (recibe - total).toFixed(2);
    $("#cambio").text("$" + total);

    var cambio = parseFloat($("#cambio").text().trim().slice(1));

    if (cambio >= 0) {

        $("#cambio").css("color", "#4ea93b");

    } else {

        $("#cambio").css("color", "#ff4040");

    }


});


$(".btn-billete").click(function () {

    var id = $(this).children('img').attr('id');
    var idp = id.split("-");

    var denominacion = parseInt(idp[1]);

    !$("#recibe").val() ? $("#recibe").val(0) : "";

    var subtotal = parseFloat($("#recibe").val()) + denominacion;
    $("#recibe").val(subtotal);

    var recibe = $("#recibe").val();
    var total = parseFloat($("#total").text().trim().slice(1));

    var total = (parseFloat(recibe) - total).toFixed(2);
    $("#cambio").text("$" + total);

    var cambio = parseFloat($("#cambio").text().trim().slice(1));

    if (cambio >= 0) {

        $("#cambio").css("color", "#4ea93b");

    } else {

        $("#cambio").css("color", "#ff4040");

    }

})
//#endregion





//TICKET
//#region
function getComisionCambio() {
    var pagado = 0;
    var comision_total = 0;
    var cambio_total = 0;
    var ticket_total_tmp = parseFloat($("#ticket_total_tmp").val());
    var nota = parseFloat($("#modalTicket #nota_credito").find('option:selected').text().trim().slice(1));
    !nota ? nota = 0 : nota = nota;


    //TOTAL INGRESADO
    $(".pago_input").each(function () {
        var valor = parseFloat($(this).val());
        var comision = (parseFloat($(this).next("input").val()) / 100) * valor;

        if (!isNaN(valor)) {
            pagado += valor;
            comision_total += comision;
        }
    });


    //COMISION
    comision_total = Math.round(comision_total);
    if ($(".chk-comision").is(":checked")) {
        var total_comision = Math.round(ticket_total_tmp + comision_total);
    } else {
        comision_total = 0;
        var total_comision = Math.round(ticket_total_tmp + comision_total);
    }
    $("#ticket_total").text("$" + total_comision);
    $("#ticket_comision").text("$" + comision_total.toFixed(2));


    //CAMBIO
    //var ticket_total = parseFloat($("#ticket_total").text().trim().slice(1));
    var ticket_total = parseFloat($("#ticket_total_tmp").val());
    cambio_total = (pagado + nota) - ticket_total;
    $("#ticket_cambio").text("$" + cambio_total.toFixed(2));
    if (cambio_total >= 0) {
        $("#ticket_cambio").css('color', '#4ea93b');
    } else {
        $("#ticket_cambio").css('color', '#ff4040');
    }
}


$(document).on("input", ".pago_input", function () {

    getComisionCambio();

    var input = $(this);
    var metodo = this.id;
    var monto = parseFloat($(this).val());
    var cambio = parseFloat($('#ticket_cambio').text().trim().slice(1));

    if (metodo != 'efectivo' && cambio > 0) {
        swal('Mensaje', 'El monto recibido debe ser exacto', 'info');
        input.val(0);
        getComisionCambio();
    }

});
//#endregion





//ASIGNAR CRÉDITO
//#region
$("#asignar_credito").click(function () {

    $(".pago_input").each(function () {
        $(this).val(0);
    });
    $('#ticket_comision').text('$0.00');
    $("#ticket_total").text($("#total").text().trim());
    $("#ticket_cambio").text("-" + $("#total").text().trim());

    var total_venta = ($("#total").text().trim().slice(1));
    var cambio_venta = Math.abs(parseFloat($("#ticket_cambio").text().trim().slice(1)));
    var total_cambio = total_venta - cambio_venta;
    $("#credito_cliente").text($("#cliente option:selected").text().trim());
    $("#credito_total").val(0);
    $("#total_venta_credito").text("$" + total_venta);
    $("#credito_a_saldar").val(0);

    var cliente_dias = parseInt($("#cliente_dias").val());
    var cliente_limite = $("#cliente_limite").val();
    var cliente_credito = $("#cliente_credito").val();
    $("#credito_dias").text(cliente_dias);
    $("#credito_limite").text(cliente_limite);
    $("#credito_disponible").text(cliente_credito);

    var fechaLimite = new Date();
    fechaLimite.setDate(fechaLimite.getDate() + cliente_dias);
    var year = fechaLimite.getFullYear();
    var month = String(fechaLimite.getMonth() + 1).padStart(2, '0'); // Agrega 1 al mes ya que los meses en JavaScript van de 0 a 11
    var day = String(fechaLimite.getDate()).padStart(2, '0');
    var fechaFormateada = `${year}-${month}-${day}`;

    if (parseFloat(cliente_credito) >= parseFloat(total_venta)) {
        $("#credito_tipo_pago").removeAttr('disabled');
        $("#guardar_credito").removeAttr('disabled');
        $("#credito_advertencia").addClass('d-none');
        if ($("#credito_tipo_pago").val() != 0) {
            $("#credito_total").removeAttr('disabled');
        }

    } else {
        $("#credito_tipo_pago").attr('disabled', 'disabled');
        $("#credito_total").attr('disabled', 'disabled');
        $("#guardar_credito").attr('disabled', 'disabled');
        let currentDate = new Date().toJSON().slice(0, 10);
        $("#credito_fecha").val(currentDate);
        $("#credito_advertencia").removeClass('d-none');
    }


    $("#credito_fecha").val(fechaFormateada);
    $("#modalTicket").addClass("d-none");
    $("#modalCreditos").modal("show");

});


function creditoSaldar(anticipo) {
    var total_venta = parseFloat($("#total").text().trim().slice(1));
    var credito = total_venta - anticipo;
    $("#modalCreditos #credito_a_saldar").val(credito.toFixed(2));
}


$(document).on('input', '#modalCreditos #credito_total', function () {

    var anticipo = parseFloat($(this).val());
    creditoSaldar(anticipo);

});


$(document).on('change', '#modalCreditos #credito_tipo_pago', function () {
    var option = $(this).val();

    if (option == 10) {
        $("#modalCreditos #credito_total").val(0);
        $("#modalCreditos #credito_total").attr('disabled', 'disabled');
        creditoSaldar(0);
    } else if (option == 0) {
        $("#modalCreditos #credito_total").val(0);
        $("#modalCreditos #credito_total").attr('disabled', 'disabled');
    } else {
        $("#modalCreditos #credito_total").removeAttr('disabled');
    }
})


$("#credito_cerrar").click(function () {

    $("#modalTicket").removeClass("d-none");
    $("#modalCreditos").modal("hide");

});
//#endregion





//GUARDAR
//#region
function getProductos() {

    var data = [];

    $("#entradas tbody tr").each(function (index) {

        var id, precio, cantidad, total;

        var idp = (this.id).split("*-*");
        var id = idp[1];

        $(this).children("td").each(function (index2) {

            switch (index2) {

                case 4:
                    precio = parseFloat($(this).find("input[type='number']").val());
                    break;

                case 5:
                    cantidad = parseInt($(this).find("input[type='number']").val());
                    break;

                case 6:
                    total = parseFloat($(this).text().trim().slice(1));
                    break;

            }

        });

        var myObj = { "codigobarras": id, "cantidad": cantidad, "unitario": precio, "total": total };

        data.push(myObj);

    });

    return data;
}


function validarRegistro() {
    var retorno = true;
    var total = parseFloat($('#total').text().trim().slice(1));
    var cambio = parseFloat($('#cambio').text().trim().slice(1));
    var recibe = parseFloat($('#recibe').val());
    var ticket_cambio = parseFloat($('#ticket_cambio').text().trim().slice(1));

    if (total == 0) {
        retorno = false;
        swal("Mensaje", "Nota vacía. Agregue los productos correctamente", "info");
    }

    $("#entradas tbody tr").each(function () {
        var producto = $(this).find('td:eq(2)').text().trim();
        var cantidad = parseInt($(this).find('td:eq(5) input[type="number"]').val());
        if (cantidad < 1) {
            retorno = false;
            swal("Mensaje", `El producto ${producto} tiene cantidad de 0, favor de poner una cantidad mayor a 0 para continuar`, "info");
        }
    });

    // if (recibe.length < 1 || recibe == 0) {
    //     retorno = false;
    //     swal("Mensaje", "Es necesario indicar la cantidad recibida", "info");
    // }

    // if (cambio < 0 || ticket_cambio < 0) {
    //     retorno = false;
    //     swal("Mensaje", "La venta no puede ser vendida por debajo del total indicado", "info");
    // }

    return retorno;
}


function validarGuardado() {
    var retorno = true;
    var total = parseFloat($('#total').text().trim().slice(1));
    var cambio = parseFloat($('#cambio').text().trim().slice(1));
    var ticket_cambio = parseFloat($('#ticket_cambio').text().trim().slice(1));
    var recibe = 0;
    var tipo_pago = '';

    if (total == 0) {
        retorno = false;
        swal("Mensaje", "Nota vacía. Agregue los productos correctamente", "info");
    }

    $(".pago_input").each(function () {
        var valor = parseFloat($(this).val());
        if (!isNaN(valor)) {
            recibe += valor;
            if (valor > 0) {
                tipo_pago += this.id;
            }
        }
    });

    var nota = parseFloat($("#modalTicket #nota_credito").find('option:selected').text().trim().slice(1));
    !nota ? nota = 0 : nota = nota;

    if (tipo_pago == 'credito' || tipo_pago == 'debito' || tipo_pago == 'transferencia' || tipo_pago == 'cheque') {
        if ((recibe + nota) != total) {
            retorno = false;
            swal("Mensaje", `Este tipo de pago no acepta cambio, es necesario ingresar la cantidad total para continuar ($${total.toFixed(2)})`, "info");
        }
    }

    if ((recibe + nota) < total) {
        retorno = false;
        swal("Mensaje", "Es necesario ingresar la cantidad total en alguno(s) de los campos de tipo de pago para continuar", "info");
    }

    return retorno;
}


$('#registrar').click(function () {

    if (validarRegistro()) {

        $("#ticket_total").text("$" + parseFloat($("#total").text().trim().slice(1)));
        $("#ticket_total_tmp").val(parseFloat($("#total").text().trim().slice(1)));
        var total = parseFloat($("#total").text().trim().slice(1));
        var recibe = parseFloat($("#recibe").val());
        var cambio = recibe - total;

        $("#ticket_cambio").text("$" + cambio.toFixed(2));

        if (cambio >= 0) {
            $("#ticket_cambio").css("color", "#4ea93b");
        } else {
            $("#ticket_cambio").css("color", "#ff4040");
        }

        $("#ticket_cliente").text($("#cliente option:selected").text().trim());
        $("#efectivo").val($("#recibe").val());

        getNotasCredito();
        $("#modalTicket").modal("show");

    }

});


$("#guardar_credito").click(function () {

    var retorno = true;
    var dias_credito = parseInt($("#modalCreditos #credito_dias").text().trim());
    var fecha_seleccionada = $("#modalCreditos #credito_fecha").val();

    //FECHA DE HOY
    //#region
    var fechaActual = new Date();
    var año = fechaActual.getFullYear();
    var mes = (fechaActual.getMonth() + 1).toString().padStart(2, '0');
    var día = fechaActual.getDate().toString().padStart(2, '0');
    var fecha_hoy = `${año}-${mes}-${día}`;

    const fecha1 = new Date(fecha_seleccionada);
    const fecha2 = new Date(fecha_hoy);
    const diferenciaMilisegundos = fecha1 - fecha2;
    const diferenciaDias = diferenciaMilisegundos / (1000 * 60 * 60 * 24);
    //#endregion

    if ($("#modalCreditos #credito_tipo_pago").val() == 0) {
        retorno = false;
        $('#modalCreditos #credito_tipo_pago').css('background-color', '#ffdddd');
    }

    if ($("#modalCreditos #credito_total").val().length < 1) {
        retorno = false;
        $('#modalCreditos #credito_total').css('background-color', '#ffdddd');
    }

    if ($("#modalCreditos #credito_fecha").val().length < 1) {
        retorno = false;
        $('#modalCreditos #credito_fecha').css('background-color', '#ffdddd');
    }

    if (diferenciaDias > dias_credito) {
        retorno = false;
        swal('Mensaje', 'La fecha seleccionada es mayor a los días de crédito disponibles, seleccione una fecha menor o igual para continuar', 'info');
    }

    if ($("#modalCreditos #credito_total").val().length < 1 || $("#modalCreditos #credito_total").val() < 0) {
        retorno = false;
        $('#modalCreditos #credito_total').css('background-color', '#ffdddd');
    }

    if (retorno == true) {

        Swal.fire({
            title: 'Registrar venta',
            text: "¿Estás seguro que deseas registra la venta? Al registrar favor de capturar las series necesarias",
            icon: 'warning',
            showCancelButton: true,
            iconColor: '#368FCD',
            confirmButtonColor: '#368FCD',
            cancelButtonColor: '#000',
            confirmButtonText: 'Si, continuar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $("#modalTicket #guardar_credito").attr("disabled", "disabled");
                guardar();
            }
        })
    }
});


$("#guardar").click(function () {

    if (validarGuardado()) {

        Swal.fire({
            title: 'Registrar venta',
            text: "¿Estás seguro que deseas registra la venta?",
            icon: 'warning',
            showCancelButton: true,
            iconColor: '#368FCD',
            confirmButtonColor: '#368FCD',
            cancelButtonColor: '#000',
            confirmButtonText: 'Si, continuar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                guardar();
            }
        })

    }
});


function guardar() {

    $("#modalTicket #guardar").attr("disabled", "disabled");

    $.LoadingOverlay("show", {
        color: "rgba(255, 255, 255, 0)"
    });


    //Monto recibido
    var monto = 0;
    $(".pago_input").each(function () {
        var valor = parseFloat($(this).val());

        if (!isNaN(valor)) {
            monto += valor;
        }
    });


    var efectivo = parseFloat($("#efectivo").val());
    if (parseFloat($("#efectivo").val()) > 0 && parseFloat($('#ticket_cambio').text().trim().slice(1)) > 0) {
        efectivo = parseFloat($("#efectivo").val()) - parseFloat($('#ticket_cambio').text().trim().slice(1));
    }


    var parametros = {
        "pk_venta": 0,
        "fk_sucursal": $("#sucursal").val(),
        "fk_almacen": $("#fk_almacen").val(),
        "fk_usuario": $("#fk_usuario").val(),
        "fk_cliente": $("#cliente").val(),
        "fk_cotizacion": $("#fk_cotizacion").val(),
        "fk_prestamo": 0,

        "productos": getProductos(),

        "efectivo": efectivo,
        "credito": $("#credito").val(),
        "debito": $("#debito").val(),
        "cheque": $("#cheque").val(),
        "transferencia": $("#transferencia").val(),
        "cheque_referencia": $("#cheque_referencia").val(),
        "transferencia_referencia": $("#transferencia_referencia").val(),

        "credito_cliente": $('#credito_total').val(),
        "credito_fecha": $('#credito_fecha').val(),
        "credito_tipo_pago": $('#credito_tipo_pago').val(),
        "credito_saldo": $('#credito_a_saldar').val(),
        "fk_devolucion": $('#nota_credito').val(),

        "comision": $('#ticket_comision').text().trim().slice(1),
        "descuento": $('#descuento').val(),
        "monto": monto,
        "subtotal": $("#subtotal").text().trim().slice(1),
        "total": $("#total").text().trim().slice(1),

        "observaciones": $('#observaciones').val(),
    };

    $.ajax({

        data: JSON.stringify(parametros),

        url: 'servicios/agregarVenta.php',

        type: 'post',

        dataType: 'json',

        contentType: "application/json; charset=utf-8",

        beforeSend: function () {

        },

        success: function (response) {

            $.LoadingOverlay("hide");

            if (response.codigo == 200) {

                nentrada = response.objList.pk_venta;

                $("#nentrada").text(nentrada);
                $("#pdf").attr("href", "ventaPDF.php?id=" + nentrada + "&ph=" + $("#telefono").val());
                $('#modalTicket').modal('hide');
                $('#modalCreditos').modal('hide');
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
//#endregion





//MODAL DESCUENTO
//#region
$(".btnModalDescuento").click(function () {
    $("#modalDescuento").modal("show");
});


function validarDescuento() {
    var retorno = true;

    if ($("#modalDescuento #usuario_descuento").val().length < 1) {
        retorno = false;
        $('#modalDescuento #usuario_descuento').css('background-color', '#ffdddd');
    }

    if ($("#modalDescuento #pass_descuento").val().length < 1) {
        retorno = false;
        $('#modalDescuento #pass_descuento').css('background-color', '#ffdddd');
    }

    return retorno;
}


$("#modalDescuento #desbloquer_descuento").click(function () {

    if (validarDescuento()) {

        var pass = btoa($("#modalDescuento #pass_descuento").val());

        var parametros = {
            "usuario": $("#modalDescuento #usuario_descuento").val(),
            "pass": pass
        };

        $.ajax({
            data: parametros,

            url: 'servicios/getUsuario.php',

            type: 'POST',

            beforeSend: function () {

            },

            success: function (response) {

                if (response.codigo == 200) {

                    $("#entradas tbody tr").each(function () {
                        $(this).find('td:eq(4) input').removeAttr('disabled');
                    });

                    swal('Exito', 'Se desbloqueo el campo de descuento para cada producto', 'success').then(function () {
                        $("#modalDescuento").modal("hide");
                        $("#descuento").focus();
                    });
                }
                else {
                    $("#descuento").attr('disabled', 'disabled');
                    swal("Error", response.descripcion, "error");
                }

            },
            error: function (arg1, arg2, arg3) {
                console.log(arg3);
            }
        });

    }

})
//#endregion





//NOTA DE CREDITO
//#region
function getNotasCredito() {

    var parametros = {
        "fk_cliente": $("#cliente").val(),
        "total": parseFloat($("#modalTicket #ticket_total").text().trim().slice(1))
    }

    $.ajax({

        data: parametros,

        url: 'servicios/getNotasCredito.php',

        type: 'get',

        beforeSend: function () {
        },

        success: function (response) {

            $("#modalTicket .nota-credito-content").html(response);

        },
        error: function (arg1, arg2, arg3) {
            console.log(arg3);
        }

    });
}


$(document).on('change', '#modalTicket #nota_credito', function () {

    var monto = parseFloat($(this).find('option:selected').text().trim().slice(1));
    !monto ? monto = 0 : monto = monto;

    var ticket_total = parseFloat($("#modalTicket #ticket_total").text().trim().slice(1));
    var pagoInput = getTotalInputs();

    var cambio_total = (monto + pagoInput) - ticket_total;
    $("#ticket_cambio").text("$" + cambio_total.toFixed(2));

    if (cambio_total >= 0) {
        $("#ticket_cambio").css('color', '#4ea93b');
    } else {
        $("#ticket_cambio").css('color', '#ff4040');
    }

    if (monto > 0) {
        $("#modalTicket #asignar_credito").attr('disabled', 'disabled');
    } else {
        $("#modalTicket #asignar_credito").removeAttr('disabled');
    }

});


function getTotalInputs() {
    var monto = 0;
    $("#modalTicket .pago_input").each(function () {
        var valor = parseFloat($(this).val());

        if (!isNaN(valor)) {
            monto += valor;
        }
    });
    return monto;
}
//#endregion





//IMAGENES DEL PRODUCTO
//#region
$(document).on('click', '.btnImgProducto', function () {

    var pk_producto = parseInt($(this).attr('data-id'));
    var title = $(this).closest('tr').find('td:eq(2)').text().trim();

    var parametros = {
        "pk_producto": pk_producto,
    }

    $.ajax({

        data: parametros,

        url: 'servicios/getImagenesProducto.php',

        type: 'get',

        beforeSend: function () {
        },

        success: function (response) {

            $("#modalImagenesProducto .carouselImgProductoContent").empty();

            $.each(response, function (i, element) {
                var imgHTML = '';
                var active = '';
                if (i == 0) {
                    active = 'active';
                }
                imgHTML = `
                    <div class="carousel-item ${active}">
                        <img src="servicios/productos/${element}" class="d-block w-100">
                    </div>
                `;
                $("#modalImagenesProducto .carouselImgProductoContent").append(imgHTML);
            })

            $("#modalImagenesProducto .elproductoimg").text(title);

            $("#modalImagenesProducto").modal('show');

        },
        error: function (arg1, arg2, arg3) {
            console.log(arg3);
        }

    });
})
//#endregion





//EXTRAS
//#region
$('#nueva').click(function () {
    $(location).attr("href", "puntoVenta.php");
});


$('#historial').click(function () {
    $(location).attr("href", "verVentas.php");
});


$(document).on('click', '#pdf', function (e) {
    e.preventDefault();

    var pdfUrl = $(this).attr('href');
    var pdfWindow = window.open(pdfUrl, '_blank');

    pdfWindow.onload = function () {
        pdfWindow.print();
    };
})


$(".chk-comision").change(function () {
    getComisionCambio();
});
//#endregion
