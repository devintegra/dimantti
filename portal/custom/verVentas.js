var $ = jQuery;
var id = 0;

$(document).ready(function () {

    $('.datepicker').datepicker({
        dateFormat: 'yy-mm-dd'
    });

    getVentasDeferred();

});



//OBTENER VENTAS
//#region
function getVentasDeferred() {

    var parametros = {
        "fk_sucursal": $("#sucursal").val(),
        "nivel": $("#nivel").val()
    };

    $('#dtEmpresa').DataTable().destroy();
    $("#dtEmpresa tbody").empty();

    if (!$.fn.DataTable.isDataTable('#dtEmpresa')) {

        $('#dtEmpresa').DataTable({
            "processing": true,
            "serverSide": true,
            responsive: true,
            ordering: true,
            order: [
                [0, 'desc']
            ],
            "ajax": {
                "url": "servicios/getVentas.php",
                "type": "GET",
                "data": function (d) {
                    $.extend(d, parametros);

                    $('#dtEmpresa tfoot th input').each(function (index) {
                        d['columns'][index]['search']['value'] = $(this).val();
                    });
                }
            },
            columns: [
                { data: '#' },
                { data: 'folio' },
                { data: 'fecha' },
                { data: 'sucursal' },
                { data: 'cliente' },
                { data: 'vendedor' },
                { data: 'origen' },
                { data: 'estatus' },
                { data: 'acciones' },
                { data: 'observaciones' },
                { data: 'total' }
            ],
            rowId: function (rowData) {
                return rowData.pk_venta;  // O usa otro ID único de tu dataset
            },
            columnDefs: [{
                "defaultContent": "",
                "targets": "_all"
            }],
            "rowCallback": function (row, data) {
                $(row).addClass('producto-detalle');
            },
            "initComplete": function () {

                $('#dtEmpresa tfoot th').each(function () {
                    var title = $(this).text().trim();
                    if (title !== '') {
                        $(this).html('<input type="text" class="form-control" placeholder="Buscar ' + title + '" />');
                    }
                });

                var api = this.api();

                api.columns().every(function () {
                    var that = this;

                    $('input', this.footer()).on('keyup change clear', function () {
                        if (that.search() !== this.value) {
                            that.search(this.value).draw();
                        }
                    });
                });
            },
            drawCallback: function (settings) {
                var api = this.api();

                var totalVentasVisible = parseFloat(settings.json.totalVentasVisible).toFixed(2);
                var totalVentas = parseFloat(settings.json.totalVentas).toFixed(2);

                $('#total_ventas').text(`$${totalVentasVisible} MXN`);
            },
            language: {
                "lengthMenu": "Mostrando _MENU_ registros por pagina",
                "search": "Buscar:",
                "processing": "Procesando...",
                "zeroRecords": "No hay registros",
                "info": "Mostrando pagina _PAGE_ de _PAGES_",
                "infoEmpty": "Sin registros disponibles",
                "infoFiltered": "(filtrando de _MAX_ registros totales)",
                "paginate": {
                    "previous": "Anterior",
                    "next": "Siguiente"
                }
            }
        });

    }
}
//#endregion




//DEVOLVER
//#region
function getTotalDetalles() {
    var productos = 0;
    var total = 0;

    $("#modalDevolucion #dtDetalles tbody tr").each(function (index) {

        $(this).children("td").each(function (index2) {

            switch (index2) {
                case 3:
                    productos = productos + parseInt($(this).text().trim());
                    break;
                case 5:
                    total = total + parseFloat($(this).text().trim().slice(1).replace(/,/g, ""));
                    break;
            }

        });
    });

    total = total.toFixed(2);
    $("#modalDevolucion #dtDetalles #detalles_productos").text(productos);
    $("#modalDevolucion #dtDetalles #detalles_total").text("$" + total);
}


function getTotalDevueltos() {
    var productos = 0;
    var total = 0;

    $("#modalDevolucion #dtDevueltos tbody tr").each(function (index) {

        $(this).children("td").each(function (index2) {

            switch (index2) {
                case 3:
                    productos = productos + parseInt($(this).text().trim());
                    break;
                case 5:
                    total = total + parseFloat($(this).text().trim().slice(1).replace(/,/g, ""));
                    break;
            }

        });
    });

    total = total.toFixed(2);
    $("#modalDevolucion #dtDevueltos #devueltos_productos").text(productos);
    $("#modalDevolucion #dtDevueltos #devueltos_total").text("$" + total);
}


function claveFocus() {
    $("#modalDevolucion #clave").val("");
    $("#modalDevolucion #serie").val("");

    setTimeout(function () {
        $('#modalDevolucion #clave').focus();
    }, 100);
}


function getVentaDetalle(fk_venta, tipo) {

    var parametros = {
        "pk_venta": fk_venta,
    };

    $.ajax({
        url: 'servicios/getVentaDetalleDevolucion.php',

        type: 'get',

        data: parametros,

        beforeSend: function () {

        },

        success: function (response) {

            if (tipo == 2) {
                $("#tablaPrestamos").html(response);
            } else {
                $("#tablaDetalles").html(response);
            }

        },

        error: function (arg1, arg2, arg3) {
            console.log(arg3);
        }

    });

}


$(document).on('click', '.devolver', function () {

    $.LoadingOverlay("show", {
        color: "rgba(255, 255, 255, 0)"
    });

    var fk_venta = $(this).attr('data-id');
    var fk_sucursal = $(this).attr('data-sucursal');
    var fk_almacen = $(this).attr('data-almacen');
    var anticipo = parseFloat($(this).closest('tr').find('td:eq(10) input[type="hidden"]').val()).toFixed(2);

    $("#modalDevolucion #fk_venta").val(fk_venta);
    $("#modalDevolucion #fk_sucursal_devolucion").val(fk_sucursal);
    $("#modalDevolucion #fk_almacen_devolucion").val(fk_almacen);
    $("#modalDevolucion #anticipo_devolucion").text(anticipo);

    getVentaDetalle(fk_venta, 2);

    $("#modalDevolucion").modal("show");

    setTimeout(function () {
        $("#modalDevolucion #dtDetalles tbody tr").each(function () {
            $(this).find('td:eq(3) input[type="number"]').attr('disabled', 'disabled');
        })
    }, 100);

    claveFocus();

    $.LoadingOverlay("hide");

});


$(document).off("keypress", "#modalDevolucion #clave").on("keypress", "#modalDevolucion #clave", function () {

    var serie = "";
    var clave = $("#modalDevolucion #clave").val();
    var data_seleccion = clave + "*-*" + serie; //producto seleccionado

    var keycode = (event.keyCode ? event.keyCode : event.which);
    if (keycode == '13') {

        var existe = false;
        var data_id = "";
        var data_ventad = "";

        //Verificar si la clave ingresada pertenece a algún producto de esta venta
        $("#modalDevolucion #dtDetalles tbody tr").each(function () {
            data_id = $(this).attr('data-id');

            if (data_seleccion == data_id) {
                data_ventad = $(this).attr('data-ventad');
                existe = true;
            }
        });

        if (existe == true) {
            var row = $("#modalDevolucion #dtDetalles tbody tr[data-id='" + data_seleccion + "']").html();
            var inputPrecioValue = $("#modalDevolucion #dtDetalles tbody tr[data-id='" + data_seleccion + "']").find('td:eq(4) input[type=number]').val();
            var existeDevueltos = false;
            var idDevueltos = 0;

            //Verificar si el producto ya había sido devuelto
            $("#modalDevolucion #dtDevueltos tbody tr").each(function () {
                idDevueltos = $(this).attr('data-id');
                if (idDevueltos == data_seleccion) {
                    existeDevueltos = true;
                }
            });

            if (existeDevueltos == true) { //El producto ya fue agregado anteriormente
                var cantidadDetalle = parseFloat($("#modalDevolucion #dtDetalles tbody tr[data-id='" + data_seleccion + "']").find('td:eq(3)').text().trim());
                var cantidadDevolver = parseFloat($("#modalDevolucion #dtDevueltos tbody tr[data-id='" + data_seleccion + "']").find('td:eq(3)').text().trim());

                if ((cantidadDevolver + 1) > cantidadDetalle) {
                    swal("Mensaje", "El producto sobrepasa la cantidad vendida (" + cantidadDetalle + ")", "info").then(function () {
                        claveFocus();
                    });
                } else {
                    var precioDevolver = parseFloat($("#modalDevolucion #dtDevueltos tbody tr[data-id='" + data_seleccion + "']").find('td:eq(4) input[type=number]').val());
                    var totalDevolver = (cantidadDevolver + 1) * precioDevolver;
                    $("#modalDevolucion #dtDevueltos tbody tr[data-id='" + data_seleccion + "']").find('td:eq(3)').text(cantidadDevolver + 1);
                    $("#modalDevolucion #dtDevueltos tbody tr[data-id='" + data_seleccion + "']").find('td:eq(5)').text("$" + totalDevolver.toFixed(2));
                    getTotalDevueltos();
                }


            } else {
                $("#modalDevolucion #dtDevueltos tbody").append("<tr data-id=" + data_seleccion + " data-ventad=" + data_ventad + ">" + row + "</tr>");
                $("#modalDevolucion #dtDevueltos tbody tr[data-id='" + data_seleccion + "']").find('td:eq(3)').text(1);
                $("#modalDevolucion #dtDevueltos tbody tr[data-id='" + data_seleccion + "']").find('td:eq(4) input[type=number]').val(inputPrecioValue);
                $("#modalDevolucion #dtDevueltos tbody tr[data-id='" + data_seleccion + "']").find('td:eq(5)').text("$" + inputPrecioValue);
                getTotalDevueltos();
            }

        } else {
            swal("Mensaje", "Este producto no forma parte de esta venta", "info").then(function () {
                claveFocus();
            });
            claveFocus();
        }

        claveFocus();

    }
});


$(document).on('input', '#modalDevolucion #dtDevueltos .input-precio', function () {

    var precio = parseFloat($(this).val());
    var cantidad = parseFloat($(this).closest('tr').find('td:eq(3)').text().trim());
    var total = precio * cantidad;

    $(this).closest('tr').find('td:eq(5)').text("$" + (total.toFixed(2)));

    getTotalDevueltos();

});


$(document).on('input', '#modalDevolucion #dtDetalles .input-precio', function () {

    var precio = parseFloat($(this).val());
    var cantidad = parseFloat($(this).closest('tr').find('td:eq(3)').text().trim());
    var total = precio * cantidad;

    $(this).closest('tr').find('td:eq(5)').text("$" + (total.toFixed(2)));

    getTotalDetalles();

});


$('#modalDevolucion input[type=radio][name=tipo_devolucion]').change(function () {
    if (this.value == 1) {
        $("#modalDevolucion #tipo_pago").removeAttr('disabled');
    }
    else if (this.value == 2) {
        $("#modalDevolucion #tipo_pago").attr('disabled', 'disabled');
    }
});


function getProductosDevolucion() {

    var data = [];

    $("#modalDevolucion #dtDevueltos tbody tr").each(function (index) {

        var id, row, serie, cantidad, unitario, total;

        row = $(this);
        id = $(this).attr('data-id').split("*-*")[0];
        fk_venta_detalle = $(this).attr('data-ventad');

        var cantidadTotal = parseInt($(this).find('td:eq(3)').text().trim());

        for (var i = 0; i < cantidadTotal; i++) {
            id = row.attr('data-id').split("*-*")[0];
            serie = row.find('td:eq(2)').text().trim();
            unitario = parseFloat(row.find("td:eq(4) input[type='number']").val());
            cantidad = 1;
            total = unitario;

            var myObj = { "codigobarras": id, "serie": serie, "cantidad": cantidad, "unitario": unitario, "total": total };

            data.push(myObj);

        }

    });

    return data;
}


function validarDevolucion() {
    var retorno = true;

    if ($("#modalDevolucion #dtDevueltos tbody tr").length < 1) {
        retorno = false;
        swal("Mensaje", "No se ha seleccionado ningún producto a devolver", "info");
    }

    if ($("#modalDevolucion input[name='tipo_devolucion']:checked").val() == 1) {
        if ($("#modalDevolucion #tipo_pago").val() == 0) {
            retorno = false;
            $('#modalDevolucion #tipo_pago').css('background-color', '#ffdddd');
        } else {
            $('#modalDevolucion #tipo_pago').css('background-color', '#fff');
        }
    }

    if (parseFloat($("#modalDevolucion #anticipo_devolucion").text().trim()) <= 0) {
        swal('Mensaje', 'Debido a que el cliente dejo un anticipo de $0.00, la devolución procederá solo con el ajuste del saldo de venta y crédito disponible del cliente');
    }

    return retorno;
}


$(document).on('click', '#guardarDevolucion', function () {

    if (validarDevolucion()) {

        Swal.fire({
            title: 'Devolver productos',
            text: "¿Estás seguro que deseas devolver estos productos?",
            icon: 'warning',
            showCancelButton: true,
            iconColor: '#368FCD',
            confirmButtonColor: '#368FCD',
            cancelButtonColor: '#000',
            confirmButtonText: 'Si, devolver',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                guardarDevolucion();
            }
        })
    }
});


function guardarDevolucion() {

    $("#guardarDevolucion").attr("disabled", "disabled");

    $.LoadingOverlay("show", {
        color: "rgba(255, 255, 255, 0)"
    });


    var parametros = {
        "pk_venta": $("#modalDevolucion #fk_venta").val(),
        "fk_usuario": $("#fk_usuario").val(),
        "fk_pago": parseInt($("#modalDevolucion #tipo_pago").val()),
        "tipo": parseInt($("#modalDevolucion input[name='tipo_devolucion']:checked").val()),
        "observaciones": $("#modalDevolucion #observaciones").val(),
        "total": parseFloat($("#modalDevolucion #devueltos_total").text().trim().slice(1)),
        "productos": getProductosDevolucion(),
    };

    $.ajax({

        data: JSON.stringify(parametros),

        url: 'servicios/editarVentaDevolver.php',

        type: 'post',

        dataType: 'json',

        contentType: "application/json; charset=utf-8",

        beforeSend: function () {

        },

        success: function (response) {

            $.LoadingOverlay("hide");

            if (response.codigo == 200) {
                swal("Devolución exitosa", "El registro se guardó correctamente", "success").then(function () {
                    $(location).attr('href', "verVentas.php");
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





//CANCELAR VENTA
//#region
$('input[type=radio][name=tipo_devolucion]').change(function () {
    if (this.value == 1) {
        $("#tipo_pago").removeAttr('disabled');
    }
    else if (this.value == 2) {
        $("#tipo_pago").attr('disabled', 'disabled');
    }
});


function validarCancelacion() {

    var retorno = true;

    if ($("input[name='tipo_devolucion']:checked").val() == 1) {

        if ($("#tipo_pago").val() == 0) {
            retorno = false;
            $('#tipo_pago').css('background-color', '#ffdddd');
        } else {
            $('#tipo_pago').css('background-color', '#fff');
        }

    }


    if (parseFloat($("#anticipo_txt").text().trim().slice(1)) <= 0) {
        swal('Mensaje', 'Debido a que el cliente dejo un anticipo de $0.00, la cancelación procederá solo con el ajuste del saldo de venta y crédito disponible del cliente');
    }

    return retorno;

}


$(document).on("click", ".cancelar", function () {

    var fk_venta = $(this).closest('tr').attr('id');
    var cliente = $(this).closest('tr').find('td:eq(3)').text().trim();
    var fecha = $(this).closest('tr').find('td:eq(1)').text().trim();
    var total = ($(this).closest('tr').find('td:eq(10) p').text().trim().slice(1));
    var anticipo = parseFloat($(this).closest('tr').find('td:eq(10) input[type="hidden"]').val().replace(/,/g, "")).toFixed(2);

    $("#cancelarModal #fk_venta").val(fk_venta);
    $("#cliente_txt").text(cliente);
    $("#fecha_txt").text(fecha);
    $("#anticipo_txt").text("$" + anticipo);
    $("#total_txt").text("$" + total);

    $("#cancelarModal").modal("show");

});


$("#cancelarModal #guardar_cancelacion").click(function () {

    if (validarCancelacion()) {

        Swal.fire({
            title: 'Cancelar venta',
            text: "¿Estás seguro que deseas cancelar la venta?",
            icon: 'warning',
            showCancelButton: true,
            iconColor: '#368FCD',
            confirmButtonColor: '#368FCD',
            cancelButtonColor: '#000',
            confirmButtonText: 'Si, cancelar',
            cancelButtonText: 'Cancelar'

        }).then((result) => {

            if (result.isConfirmed) {

                var parametros = {
                    "pk_venta": $("#cancelarModal #fk_venta").val(),
                    "fk_usuario_cancela": $("#fk_usuario").val(),
                    "tipo": $("#cancelarModal input[name='tipo_devolucion']:checked").val(),
                    "fk_pago": $("#cancelarModal #tipo_pago").val()
                };

                $.ajax({
                    data: parametros,

                    url: 'servicios/editarVentaCancelar.php',

                    type: 'POST',

                    beforeSend: function () {

                    },

                    success: function (response) {

                        if (response.codigo == 200) {
                            swal("Cancelación exitosa", "El registro se canceló correctamente", "success").then(function () {
                                $(location).attr('href', "verVentas.php");
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
        })

    }

});
//#endregion





//EXTRAS
//#region
$('#limpiarfiltros').click(function () {
    location.reload();
});


$(document).on('click', '.btnSaldarVenta', function () {
    var fk_venta = $(this).attr('data-id');
    $(location).attr("href", "pagarVenta.php?id=" + fk_venta);
});


$(document).on('click', '.facturarVenta', function () {
    var fk_venta = $(this).attr('data-id');
    $(location).attr("href", "facturarVenta.php?id=" + fk_venta + "&tipo=1");
});
//#endregion
