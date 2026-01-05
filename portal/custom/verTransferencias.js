var $ = jQuery;
var nivel_usuario = $("#nivel_usuario").val();


$(document).ready(function () {

    getTransferencias();

});



//OBTENER TRANSFERENCIAS
//#region
function validar() {

    var retorno = true;

    if ($('#inicio').val().length < 10) {
        retorno = false;
        $('#inicio').css('background-color', '#ffdddd');
    }

    if ($('#fin').val().length < 10) {
        retorno = false;
        $('#fin').css('background-color', '#ffdddd');
    }

    return retorno;

}


$('#buscar').click(function () {
    if (validar()) {
        getTransferencias();
    }
});


function getTransferencias() {

    let fk_almacen = $("#sucursal").val();

    var parametros = {
        "inicio": $("#inicio").val(),
        "fin": $("#fin").val(),
        "fk_almacen": fk_almacen,
        "nivel": nivel_usuario
    };

    $.ajax({
        url: 'servicios/getTransferencias.php',

        type: 'GET',

        data: parametros,

        beforeSend: function () {

        },

        success: function (response) {

            if (response.codigo == 200) {

                $('#dtEmpresa').DataTable().destroy();
                $("#dtEmpresa tbody").empty();

                let total = 0.00;

                response.objList.forEach(element => {

                    let botones = `<a target='_blank' class='btn-reabrir-dast' href='transferenciaPDF.php?id=${element.pk_transferencia}'><i class='fa fa-file-pdf-o'></i></a>`;

                    //BOTONES
                    //#region
                    if (element.estatus == 1 && element.fk_sucursal_destino == fk_almacen) { //Aún quedan productos pendientes
                        botones += `<button type='button' class='btn-entregar-dast btnModalTransferencia' data-accion='2' data-id='${element.pk_transferencia}' title='Recibir productos'>
                                <i class='fa fa-chevron-down mx-2'></i>
                            </button>`;

                        botones += `<button type='button' class='btn-editar-dast btnModalTransferencia' data-accion='3' data-id='${element.pk_transferencia}' title='Devolver productos'>
                                <i class='fa fa-repeat mx-2'></i>
                            </button>`;
                    }

                    let whoSend = '';
                    if (element.fk_sucursal_destino == fk_almacen) {
                        whoSend = "<p class='badge-warning-integra'>Generada externamente</p>";
                    } else {
                        whoSend = "<p class='badge-primary-integra'>Generada localmente</p>";
                    }

                    botones += `<button type='button' class='btn-iniciar-dast btnModalHistorial' data-id='${element.pk_transferencia}' title='Historial de transferencia'>
                            <i class='fa fa-history mx-2'></i>
                        </button>`;
                    //#endregion

                    let totalf = parseFloat(element.total).toFixed(2);
                    total += parseFloat(element.total);

                    let trHTML = `
                        <tr class="odd gradeX">
                            <td style='white-space: normal;'>
                                <i class='bx bx-chevrons-left fs-5'></i>${element.origen} - ${element.almacen_origen}
                            </td>
                            <td style='white-space: normal;'>
                                <i class='bx bx-chevrons-right fs-5'></i>${element.destino} - ${element.almacen_destino}
                            </td>
                            <td>
                                <i class='bx bx-calendar fs-5'></i>${element.fecha} ${element.hora}
                            </td>
                            <td>
                                <i class='bx bx-user fs-5'></i>${element.fk_usuario}
                            </td>
                            <td style='white-space: normal;'>
                                ${element.observaciones}
                            </td>
                            <td>
                                ${whoSend} ${botones}
                            </td>
                            <td style='background-color: #fff7b6'>
                                $${totalf}
                            </td>
                        </tr>
                    `;

                    $("#dtEmpresa tbody").append(trHTML);

                });

                total = parseFloat(total).toFixed(2);

                $("#dtEmpresa tfoot #total").text(`$${total}`);

            }


            if (!$.fn.DataTable.isDataTable('#dtEmpresa')) {
                $('#dtEmpresa tfoot th').each(function () {
                    var title = $(this).text().trim();
                    if (title != '') {
                        $(this).html('<input type="text" class="form-control" placeholder="Buscar ' + title + '" />');
                    }
                });

                $('#dtEmpresa').DataTable({
                    initComplete: function () {
                        // Aplicar la búsqueda
                        this.api()
                            .columns()
                            .every(function () {
                                var that = this;

                                $('input', this.footer()).on('keyup change clear', function () {
                                    if (that.search() !== this.value) {
                                        that.search(this.value).draw();
                                    }
                                });
                            });
                    },

                    responsive: true,
                    ordering: true,
                    pageLength: 10,
                    "order": [
                        [2, "desc"]
                    ],
                    dom: '<"dtEmpresa_header"lf><t><rip>',
                    //lfptrip
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
                    }

                });
            }

        },

        error: function (arg1, arg2, arg3) {
            console.log(arg3);
        }

    });
}
//#endregion




//CORTE
//#region
$('#imprimir').click(function () {

    window.open("cortetPDF.php?inicio=" + $("#inicio").val() + "&fin=" + $("#fin").val() + "&fk_almacen=" + $("#sucursal").val(), '_blank');

});
//#endregion




//RECIBIR/DEVOLVER
//#region
function getTotalDetalles() {
    var productos = 0;
    var total = 0;

    $("#modalTransferencia #dtDetalles tbody tr").each(function (index) {

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
    $("#modalTransferencia #dtDetalles #detalles_productos").text(productos);
    $("#modalTransferencia #dtDetalles #detalles_total").text("$" + total);
}


function getTotalDevueltos() {
    var productos = 0;
    var total = 0;

    $("#modalTransferencia #dtDevueltos tbody tr").each(function (index) {

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
    $("#modalTransferencia #dtDevueltos #devueltos_productos").text(productos);
    $("#modalTransferencia #dtDevueltos #devueltos_total").text("$" + total);
}


function claveFocus() {
    $("#modalTransferencia #clave").val("");
    $("#modalTransferencia #serie").val("");

    setTimeout(function () {
        $('#modalTransferencia #clave').focus();
    }, 100);
}


function getTransferenciaDetalle(fk_transferencia, tipo) {

    var parametros = {

        "pk_transferencia": fk_transferencia,
    };

    $.ajax({
        url: 'servicios/getTransferenciaDetalle.php',

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


$(document).on('click', '.btnModalTransferencia', function () {

    $("#modalTransferencia #tablaDevueltos tbody").empty();
    $("#modalTransferencia #tablaDevueltos tfoot").empty();
    var footer = "<tr style='background-color: #A8F991;'><td></td><td></td><td></td><td style='font-weight:bold;' id='devueltos_productos'>0</td><td></td><td style='font-weight:bold;' id='devueltos_total'>$0.00</td></tr > ";
    $("#modalTransferencia #tablaDevueltos tfoot").append(footer);

    $.LoadingOverlay("show", {
        color: "rgba(255, 255, 255, 0)"
    });

    var accion = $(this).attr('data-accion');
    var fk_transferencia = $(this).attr('data-id');
    var fk_sucursal_origen = $(this).attr('data-fk-sucursal-origen');
    var fk_almacen_origen = $(this).attr('data-fk-almacen-origen');
    var fk_sucursal_destino = $(this).attr('data-fk-sucursal-destino');
    var fk_almacen_destino = $(this).attr('data-fk-almacen-destino');

    $("#modalTransferencia #accion").val(accion);
    $("#modalTransferencia #fk_transferencia").val(fk_transferencia);
    $("#modalTransferencia #fk_sucursal_origen").val(fk_sucursal_origen);
    $("#modalTransferencia #fk_almacen_origen").val(fk_almacen_origen);
    $("#modalTransferencia #fk_sucursal_destino").val(fk_sucursal_destino);
    $("#modalTransferencia #fk_almacen_destino").val(fk_almacen_destino);

    if (accion == 2) {
        $("#modalTransferencia #guardarDevolucion").text("Recibir productos");
    } else {
        $("#modalTransferencia #guardarDevolucion").text("Devolver productos");
    }

    getTransferenciaDetalle(fk_transferencia, 2);

    $("#modalTransferencia").modal("show");

    setTimeout(function () {
        $("#modalTransferencia #dtDetalles tbody tr").each(function () {
            $(this).find('td:eq(3) input[type="number"]').attr('disabled', 'disabled');
        })
    }, 100);

    claveFocus();

    $.LoadingOverlay("hide");

});


$(document).off("keypress", "#modalTransferencia #clave").on("keypress", "#modalTransferencia #clave", function () {
    var keycode = (event.keyCode ? event.keyCode : event.which);
    if (keycode == '13') {
        setTimeout(function () {
            $('#modalTransferencia #serie').focus();
        }, 100);
    }
})


$(document).off("keypress", "#modalTransferencia #serie").on("keypress", "#modalTransferencia #serie", function (event) {

    var serie = $(this).val();
    var clave = $("#modalTransferencia #clave").val();
    var data_seleccion = clave + "*-*" + serie; //producto seleccionado

    var keycode = (event.keyCode ? event.keyCode : event.which);
    if (keycode == '13') {

        var existe = false;
        var data_id = "";
        var data_transferenciad = "";

        //Verificar si la clave ingresada pertenece a algún producto de esta venta
        $("#modalTransferencia #dtDetalles tbody tr").each(function () {
            data_id = $(this).attr('data-id');

            if (data_seleccion == data_id) {
                data_transferenciad = $(this).attr('data-transferenciad');
                existe = true;
            }
        });

        if (existe == true) {
            var row = $("#modalTransferencia #dtDetalles tbody tr[data-id='" + data_seleccion + "']").html();
            var inputPrecioValue = $("#modalTransferencia #dtDetalles tbody tr[data-id='" + data_seleccion + "']").find('td:eq(4) input[type=number]').val();
            var existeDevueltos = false;
            var idDevueltos = 0;

            //Verificar si el producto ya había sido devuelto
            $("#modalTransferencia #dtDevueltos tbody tr").each(function () {
                idDevueltos = $(this).attr('data-id');
                if (idDevueltos == data_seleccion) {
                    existeDevueltos = true;
                }
            });

            if (existeDevueltos == true) { //El producto ya fue agregado anteriormente
                var cantidadDetalle = parseFloat($("#modalTransferencia #dtDetalles tbody tr[data-id='" + data_seleccion + "']").find('td:eq(3)').text().trim());
                var cantidadDevolver = parseFloat($("#modalTransferencia #dtDevueltos tbody tr[data-id='" + data_seleccion + "']").find('td:eq(3)').text().trim());

                if ((cantidadDevolver + 1) > cantidadDetalle) {
                    swal("Mensaje", "El producto sobrepasa la cantidad prestada (" + cantidadDetalle + ")", "info").then(function () {
                        claveFocus();
                    });
                } else {
                    var precioDevolver = parseFloat($("#modalTransferencia #dtDevueltos tbody tr[data-id='" + data_seleccion + "']").find('td:eq(4) input[type=number]').val());
                    var totalDevolver = (cantidadDevolver + 1) * precioDevolver;
                    $("#modalTransferencia #dtDevueltos tbody tr[data-id='" + data_seleccion + "']").find('td:eq(3)').text(cantidadDevolver + 1);
                    $("#modalTransferencia #dtDevueltos tbody tr[data-id='" + data_seleccion + "']").find('td:eq(5)').text("$" + totalDevolver.toFixed(2));
                    getTotalDevueltos();
                }


            } else {
                $("#modalTransferencia #dtDevueltos tbody").append("<tr data-id=" + data_seleccion + " data-transferenciad=" + data_transferenciad + ">" + row + "</tr>");
                $("#modalTransferencia #dtDevueltos tbody tr[data-id='" + data_seleccion + "']").find('td:eq(3)').text(1);
                $("#modalTransferencia #dtDevueltos tbody tr[data-id='" + data_seleccion + "']").find('td:eq(4) input[type=number]').val(inputPrecioValue);
                getTotalDevueltos();
            }

        } else {
            swal("Mensaje", "Este producto no forma parte de esta transferencia", "info").then(function () {
                claveFocus();
            });
            claveFocus();
        }

        claveFocus();

    }
});


$(document).on('input', '#modalTransferencia #dtDevueltos .input-precio', function () {

    var precio = parseFloat($(this).val());
    var cantidad = parseFloat($(this).closest('tr').find('td:eq(3)').text().trim());
    var total = precio * cantidad;

    $(this).closest('tr').find('td:eq(5)').text("$" + (total.toFixed(2)));

    getTotalDevueltos();

});


$(document).on('input', '#modalTransferencia #dtDetalles .input-precio', function () {

    var precio = parseFloat($(this).val());
    var cantidad = parseFloat($(this).closest('tr').find('td:eq(3)').text().trim());
    var total = precio * cantidad;

    $(this).closest('tr').find('td:eq(5)').text("$" + (total.toFixed(2)));

    getTotalDetalles();

})


function getProductosDevolucion() {
    var data = [];
    $("#modalTransferencia #dtDevueltos tbody tr").each(function (index) {

        var id, fk_transferencia_detalle, serie, cantidad, unitario, total;

        id = $(this).attr('data-id').split("*-*")[0];
        fk_transferencia_detalle = $(this).attr('data-transferenciad');

        $(this).children("td").each(function (index2) {

            switch (index2) {

                case 2:
                    serie = ($(this).text().trim());
                    break;
                case 3:
                    cantidad = parseInt($(this).text().trim());
                    break;
                case 4:
                    unitario = parseFloat($(this).find("input[type='number']").val());
                    break;
                case 5:
                    total = parseFloat($(this).text().trim().slice(1).replace(/,/g, ""));
                    break;

            }

        });

        var myObj = { "codigobarras": id, "fk_transferencia_detalle": fk_transferencia_detalle, "serie": serie, "cantidad": cantidad, "unitario": unitario, "total": total };

        data.push(myObj);

    });

    return data;
}


function validarDevolucion() {
    var retorno = true;

    if ($("#modalTransferencia #dtDevueltos tbody tr").length < 1) {
        retorno = false;
        swal("Mensaje", "No se ha seleccionado ningún producto a devolver", "info");
    }

    return retorno;
}


$(document).on('click', '#guardarDevolucion', function () {

    var accion = $("#modalTransferencia #accion").val();
    var myTitle, myText;
    if (accion == 2) {
        myTitle = "Recibir productos";
        myText = "¿Estás seguro que deseas recibir estos productos?";
    } else {
        myTitle = "Devolver productos";
        myText = "¿Estás seguro que deseas devolver estos productos?";
    }

    if (validarDevolucion()) {

        Swal.fire({
            title: myTitle,
            text: myText,
            icon: 'warning',
            showCancelButton: true,
            iconColor: '#368FCD',
            confirmButtonColor: '#368FCD',
            cancelButtonColor: '#000',
            confirmButtonText: 'Si, continuar',
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
    var accion = $("#modalTransferencia #accion").val();

    $.LoadingOverlay("show", {
        color: "rgba(255, 255, 255, 0)"
    });

    var parametros = {

        "fk_transferencia": $("#modalTransferencia #fk_transferencia").val(),
        "observaciones": $("#modalTransferencia #observaciones").val(),
        "fk_usuario": $("#fk_usuario").val(),
        "tipo": accion,
        "productos": getProductosDevolucion(),
        "total": parseFloat($("#modalTransferencia #devueltos_total").text().trim().slice(1)),

    };

    $.ajax({
        data: JSON.stringify(parametros),

        url: 'servicios/accionesTransferencia.php',

        type: 'post',

        dataType: 'json',

        contentType: "application/json; charset=utf-8",

        beforeSend: function () {

        },

        success: function (response) {

            $.LoadingOverlay("hide");

            if (response.codigo == 200) {
                swal("Éxito", "El registro se guardó correctamente", "success").then(function () {
                    location.reload();
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
//#endregion




//HISTORIAL
//#region
$(document).on('click', '.btnModalHistorial', function () {
    $.LoadingOverlay("show", {
        color: "rgba(255, 255, 255, 0)"
    });

    var fk_transferencia = $(this).attr('data-id');

    getTransferenciaHistorial(fk_transferencia);

    $("#modalHistorial").modal("show");

    $.LoadingOverlay("hide");

});


function getTransferenciaHistorial(fk_transferencia) {

    var parametros = {
        "pk_transferencia": fk_transferencia,
    };

    $.ajax({
        url: 'servicios/getTransferenciaHistorial.php',

        type: 'get',

        data: parametros,

        beforeSend: function () {

        },

        success: function (response) {

            $("#tablaHistorial").html(response);

        },

        error: function (arg1, arg2, arg3) {
            console.log(arg3);
        }

    });

}
//#endregion
