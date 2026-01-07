var $ = jQuery;
var checados = "";
var total = 0;
var subtotalpr = 0;
var pdf = "";
var nentrada = 0;
var id;
var pk_ventas = [];
var nivel_usuario = $("#nivel").val();


$(document).ready(function () {

    $('.datepicker').datepicker({
        dateFormat: 'yy-mm-dd'
    });

    $('.select2').select2({
        tags: false
    });

});



function getTotal() {

    total = 0;
    subtotalpr = 0;

    $("#entradas tbody tr").each(function () {

        var cantidad = parseInt($(this).find('td:eq(2)').text().trim());
        var unitario = parseFloat($(this).find('td:eq(3)').text().trim().slice(1).replace(/,/g, ""));
        var subtotal = parseFloat($(this).find('td:eq(5)').text().trim().slice(1).replace(/,/g, ""));

        subtotalpr = subtotalpr + unitario;
        total = total + subtotal;

    });

    subtotalpr = subtotalpr.toFixed(2);
    $("#totalf").text("$" + total.toFixed(2));
}



//GUARDAR
//#region
function getProductos() {
    var data = [];
    $("#entradas tbody tr").each(function (index) {

        var idp, descripcion, unitario, iva, total;

        var idp = parseInt($(this).attr('data-id'));
        var fk_venta = parseInt($(this).attr('data-venta'));

        !pk_ventas.includes(fk_venta) ? pk_ventas.push(fk_venta) : undefined;

        $(this).children("td").each(function (index2) {

            switch (index2) {

                case 1:
                    descripcion = $(this).text().trim();
                    break;

                case 2:
                    cantidad = parseInt($(this).text().trim());
                    break;

                case 3:
                    unitario = parseFloat($(this).text().slice(1).replace(/,/g, ""));
                    break;

                case 4:
                    iva = parseFloat($(this).text().slice(1).replace(/,/g, ""));
                    break;

                case 5:
                    total = parseFloat($(this).text().slice(1).replace(/,/g, ""));
                    break;

            }

        });

        var myObj = { "fk_producto": idp, "descripcion": descripcion, "cantidad": cantidad, "costo": unitario, "iva": iva, "total": total };

        data.push(myObj);

    });

    return data;
}


function validarGuardado() {
    var retorno = true;

    if ($('#fk_cliente').val().length < 1) {
        retorno = false;
        $('#cliente').css('background-color', '#ffdddd');
    }

    if ($('#forma_pago').val() == 0) {
        retorno = false;
        $('#forma_pago').css('background-color', '#ffdddd');
    }

    if ($('#metodo_pago').val() == 0) {
        retorno = false;
        $('#metodo_pago').css('background-color', '#ffdddd');
    }

    if ($('#fk_cfdi').val() == 0) {
        retorno = false;
        $('#fk_cfdi').css('background-color', '#ffdddd');
    }

    if ($("#entradas tbody tr").length < 1) {
        retorno = false;
        swal("Factura vacía", "La venta no cuenta con ningún producto a facturar", "info");
    }

    return retorno;
}


//OBTENER LOS DATOS DE LA FACTURA
$('#guardar').click(function () {

    if (validarGuardado()) {

        $.LoadingOverlay("show");

        getTotal();

        var subtotal_factura = parseFloat(total / 1.16).toFixed(2);

        var parametros = {
            "productos": getProductos(),
            "pk_ventas": pk_ventas,
            "fk_usuario": $("#usuario").val(),
            "fk_cliente": $("#fk_cliente").val(),
            "fk_empresa": 1,
            "forma_pago": $("#forma_pago").val(),
            "metodo_pago": $("#metodo_pago").val(),
            "fk_cfdi": $("#fk_cfdi").val(),
            "subtotal": subtotalpr,
            "total": total,
        };

        $.ajax({

            data: JSON.stringify(parametros),

            url: 'servicios/getFactura.php',

            type: 'post',

            dataType: 'json',

            contentType: "application/json; charset=utf-8",

            beforeSend: function () {

            },

            success: function (response) {

                $.LoadingOverlay("hide");

                if (response.codigo == 200) {

                    body = JSON.stringify(response.objList);

                    getFactura(body);

                    // generarToken().then(function (response) {
                    //     console.log(response)
                    // }).catch(function (error) {
                    //     console.error("Error al generar el token:", error);
                    //     swal("Error", "No se pudo generar el token.", "error").then(function () {
                    //         location.reload();
                    //     });
                    // });

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



// MANDAMOS A LLAMAR LA API
async function getFactura(body) {
    const options = {
        method: 'POST',
        headers: {
            accept: 'application/json',
            'content-type': 'application/*+json',
            authorization: `Bearer ${TOKEN_FACTUROPORTI_DEV}`
        },
        body: body
    };

    try {
        $.LoadingOverlay("show")
        const response = await fetch(`${API_FACTUROPORTI_DEV}/servicios/timbrar/json`, options);
        const json = await response.json();
        $.LoadingOverlay("hide")
        if (json.estatus.codigo == '000') {
            await swal("Factura generada", "La factura se generó con éxito", "success");
            const pdf = json.cfdiTimbrado.respuesta.pdf;
            const xml = json.cfdiTimbrado.respuesta.cfdixml;
            const uid = json.cfdiTimbrado.respuesta.uuid;
            await agregarFactura(uid, pdf, xml);
        } else {
            swal("Error", `${json.estatus.descripcion}. ${json.estatus.informacionTecnica}`, "error").then(function () {
                location.reload();
            });
        }
    }
    catch (e) {
        console.log(e);
    }
    pk_venta = 0;
}


// GUARDARMOS LA FACTURA
async function agregarFactura(uid, pdf, xml) {
    $.LoadingOverlay("show");
    getProductos();
    var parametros = {
        pk_venta: pk_ventas,
        fk_cliente: $("#fk_cliente").val(),
        forma_pago: $("#forma_pago").val(),
        metodo_pago: $("#metodo_pago").val(),
        productos: getProductos(),
        uid: uid,
        pdf: pdf,
        xml: xml
    };
    const response = await $.ajax({
        url: 'servicios/agregarFactura.php',
        dataType: 'json', // added data type
        type: 'POST',
        contentType: "application/json; charset=utf-8",
        data: JSON.stringify(parametros)
    });
    $.LoadingOverlay("hide")
    if (response.codigo == 200) {
        swal("Factura agregada", "La factura se agregó con éxito", "success").then(function () {
            if (nivel_usuario != 10) {
                $(location).attr('href', `verFacturasHistorial.php`);
            } else {
                $(location).attr('href', `index_cliente.php`);
            }
        });
    } else if (response.codigo == 201) {
        swal("Error", response.descripcion, "error").then(function () { });
    }
}


//GENERAR TOKEN
async function generarToken() {

    const options = { method: 'GET', headers: { accept: 'application/json' } };

    const usuario = encodeURIComponent(USER_FACTUROPORTI_DEV);

    const pass = encodeURIComponent(PASS_FACTUROPORTI_DEV);

    try {

        const response = await fetch(`${API_FACTUROPORTI_DEV}/token/crear?Usuario=${usuario}&Password=${pass}`, options);

        const json = await response.json()

        return json.token;

    } catch (e) {

        console.log(e);

    }

}


//ELIMINAR TOKEN
async function eliminarToken() {

    const usuario = encodeURIComponent(USER_FACTUROPORTI_DEV);

    const pass = encodeURIComponent(PASS_FACTUROPORTI_DEV);

    const options = {
        method: 'DELETE',
        headers: {
            accept: 'application/json',
            'content-type': 'application/*+json'
        },
        body: JSON.stringify({
            "Usuario": usuario,
            "Password": pass
        })
    };

    try {
        $.LoadingOverlay("show")
        const response = await fetch(`${API_FACTUROPORTI_DEV}/token/borrar`, options);
        const json = await response.json();
        console.log(json);
        $.LoadingOverlay("hide")
        if (json.codigo == '000') {
            await swal("Token eliminado", "El token se eliminó correctamente", "success");
        } else {
            swal("Error", json.mensaje, "error").then(function () {
                location.reload();
            });
        }
    }
    catch (e) {
        console.log(e);
    }

}
//#endregion



//VER FACTURAS
//#region
function verFactura(e) {

    //const pk = e.target.dataset.id;

    const factura = e.target.dataset.factura;

    download(`Factura de venta`, factura, 'pdf');

}


function verFacturaXML(e) {

    //const pk = e.target.dataset.id;

    const factura = e.target.dataset.factura;

    download(`Factura de venta`, factura, 'xml');

}


function verAcuseXML(e) {

    //const pk = e.target.dataset.id;

    const factura = e.target.dataset.factura;

    download(`Acuse de `, factura, 'xml');

}


function download(filename, base64, extencion) {

    var element = document.createElement('a');

    element.setAttribute('href', 'data:application/' + extencion + ';base64,' + base64);

    element.setAttribute('download', filename + '.' + extencion);

    element.style.display = 'none';

    document.body.appendChild(element);

    element.click();

    document.body.removeChild(element);

}
//#endregion



//CANCELAR FACTURA
//#region
$(document).on('click', '.cancelar_factura', function () {

    var uuid = $(this).attr('data-uuid');
    //var pk_venta = $(this).attr('data-id');

    $("#modalCancelacionFactura #uuid_cancelacion").val(uuid);
    //$("#modalCancelacionFactura #fk_venta_cancelacion").val(pk_venta);

    $("#modalCancelacionFactura").modal("show");

});


function validarCancelacion() {

    var retorno = true;

    if ($('#modalCancelacionFactura #motivo_cancelacion').val() == 0) {
        retorno = false;
        $('#modalCancelacionFactura #motivo_cancelacion').css('background-color', '#ffdddd');
    }

    if ($('#modalCancelacionFactura #motivo_cancelacion').val() == '01') {
        if ($('#modalCancelacionFactura #folio_fiscal').val().length < 1) {
            retorno = false;
            $('#modalCancelacionFactura #folio_fiscal').css('background-color', '#ffdddd');
        }
    }

    return retorno;

}


$(document).on('click', '#modalCancelacionFactura #guardarCancelacion', function () {

    if (validarCancelacion()) {

        Swal.fire({
            title: 'Cancelar factura',
            text: "¿Estás seguro que deseas cancelar esta factura?",
            icon: 'warning',
            showCancelButton: true,
            iconColor: '#D8A31A',
            confirmButtonColor: '#D8A31A',
            cancelButtonColor: '#000',
            confirmButtonText: 'Si, borrar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                cancelarFactura();
            }
        })

    }

});


function cancelarFactura() {

    var parametros = {
        //"pk_venta": $("#modalCancelacionFactura #fk_venta_cancelacion").val(),
        "uuid": $("#modalCancelacionFactura #uuid_cancelacion").val(),
        "fk_motivo": $("#modalCancelacionFactura #motivo_cancelacion").val(),
        "folio_fiscal": $("#modalCancelacionFactura #folio_fiscal").val()
    };

    $.ajax({

        data: parametros,

        url: 'servicios/cancelarFactura.php',

        type: 'post',

        beforeSend: function () {

        },

        success: function (response) {

            $("#modalCancelacionFactura").modal('hide');
            $.LoadingOverlay("hide");

            if (response.codigo == 200) {

                swal('Éxito', 'La factura se canceló correctamente', 'success').then(function () {
                    $(location).attr('href', 'verFacturasHistorial.php');
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


$(document).on('change', '#modalCancelacionFactura #motivo_cancelacion', function () {

    var option = $(this).val();

    if (option == '01') {
        $("#modalCancelacionFactura .folio_fiscal_content").removeClass('d-none');
    } else {
        $("#modalCancelacionFactura .folio_fiscal_content").addClass('d-none');
    }

});
//#endregion



//OBTENER VENTAS
//#region
$(document).on('click', '#buscar', function () {
    getVentas();
});


function validarBusqueda() {
    var retorno = true;

    if ($('#inicio').val() != '') {
        if ($('#fin').val().length < 8) {
            retorno = false;
            $('#fin').css('background-color', '#ffdddd');
        }
    }

    if ($('#fin').val() != '') {
        if ($('#inicio').val().length < 8) {
            retorno = false;
            $('#fin').css('background-color', '#ffdddd');
        }
    }

    return retorno;
}


function getVentas() {

    if (validarBusqueda()) {

        $.LoadingOverlay("show", {
            color: "rgba(255, 255, 255, 0)"
        });

        var parametros = {
            "inicio": $("#inicio").val(),
            "fin": $("#fin").val(),
            "fk_sucursal": $("#fk_sucursalv").val(),
            "fk_vendedor": $("#fk_vendedor").val(),
            "fk_pago": $("#fk_pagov").val(),
            "fk_cliente": $("#fk_clientev").val()
        };

        $.ajax({

            data: parametros,

            url: 'servicios/getVentasFilterFactura.php',

            type: 'GET',

            beforeSend: function () {

            },

            success: function (response) {

                $('#dtEmpresa').DataTable().destroy();
                $("#dtEmpresa tbody").empty();

                $.LoadingOverlay("hide");

                $.each(response.objList, function (i, element) {

                    var tr = `
                        <tr class='odd gradeX'>
                            <td>
                                <input type='checkbox' class='chk-venta' style='width: 20px; height: 20px;'>
                            </td>
                            <td>${element.pk_venta}</td>
                            <td style='white-space: normal'>${element.fecha} ${element.hora}</td>
                            <td style='white-space: normal'>${element.sucursal}</td>
                            <td style='white-space: normal'>${element.cliente}</td>
                            <td>${element.metodos_pago}</td>
                            <td>${element.fk_usuario}</td>
                            <td><p class='badge-success-integra'>$${element.total}</p></td>
                        </tr>
                    `;

                    $("#dtEmpresa tbody").append(tr);

                });

                if (!$.fn.DataTable.isDataTable('#dtEmpresa')) {
                    $('#dtEmpresa').DataTable({
                        responsive: true,
                        ordering: true,
                        pageLength: 10,
                        order: [
                            [1, 'desc']
                        ],
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

}
//#endregion



//DETALLE DE LA VENTA
//#region
$(document).on('change', '.chk-venta', function () {

    var fk_venta = parseInt($(this).closest('tr').find('td:eq(1)').text().trim());

    if ($(this).is(':checked')) {

        getVentaDetalle(fk_venta);
    }
    else {

        $('#entradas tbody tr').each(function () {

            if (parseInt($(this).attr('data-venta')) == fk_venta) {
                $(this.remove());
                getTotal();
            }

        });

    }

});


function getVentaDetalle(fk_venta) {

    if (validarBusqueda()) {

        $.LoadingOverlay("show", {
            color: "rgba(255, 255, 255, 0)"
        });

        var parametros = {
            "pk_venta": fk_venta
        };

        $.ajax({

            data: parametros,

            url: 'servicios/getVentaDetalle.php',

            type: 'GET',

            beforeSend: function () {

            },

            success: function (response) {

                $.LoadingOverlay("hide");

                $.each(response.objList, function (i, element) {

                    var total = element.total;

                    var unitario = element.total / element.cantidad;
                    var costo_total = parseFloat((unitario * element.cantidad) / 1.16).toFixed(2);
                    var iva_total = parseFloat((total - costo_total).toFixed(2));

                    //IMAGEN
                    //#region
                    if (element.imagen) {
                        var fondo = `<img style='border-radius: 7px; width:70px; height:70px; object-fit:cover;' loading='lazy' src='servicios/productos/${element.imagen}'>`;
                    } else {
                        var fondo = "<img style='border-radius: 7px; width:70px; height:70px; object-fit:cover;' loading='lazy' src='images/picture.png'>";
                    }
                    //#endregion

                    var tr = `
                        <tr class='odd gradeX' data-id='${element.fk_producto}' data-venta='${element.fk_venta}'>
                            <td>${fondo}</td>
                            <td style='white-space: normal;'>${element.descripcion}</td>
                            <td>${element.cantidad}</td>
                            <td>$${costo_total}</td>
                            <td>$${iva_total}</td>
                            <td>$${total}</td>
                        </tr >
                    `;

                    $("#entradas tbody").append(tr);

                });

                getTotal();

            },

            error: function (arg1, arg2, arg3) {
                console.log(arg3);
            }

        });

    }

}
//#endregion



//ENVIAR CORREO
//#region
$(document).on('click', '.enviar_factura', function () {

    var uuid = $(this).attr('data-uuid');
    var correo = $(this).attr('data-correo');

    $("#modalCorreo #uuid_correo").val(uuid);
    $("#modalCorreo #correo_cliente").val(correo);

    $("#modalCorreo").modal('show');

});


function validarCorreo() {

    var retorno = true;
    var regex = /[\w-\.]{2,}@([\w-]{2,}\.)*([\w-]{2,}\.)[\w-]{2,4}/;

    if ($('#modalCorreo #correo_cliente').val().length < 1) {
        retorno = false;
        $('#modalCorreo #correo_cliente').css('background-color', '#ffdddd');
    }

    if (!regex.test($('#modalCorreo #correo_cliente').val().trim())) {
        retorno = false;
        $('#modalCorreo #correo_cliente').css('background-color', '#ffdddd');
    }

    return retorno;

}


$(document).on('click', '#modalCorreo #enviarCorreo', function () {

    if (validarCorreo()) {

        Swal.fire({
            title: 'Enviar por correo',
            text: "¿Estás seguro que deseas enviar esta factura por correo?",
            icon: 'warning',
            showCancelButton: true,
            iconColor: '#D8A31A',
            confirmButtonColor: '#D8A31A',
            cancelButtonColor: '#000',
            confirmButtonText: 'Si, enviar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                enviarFactura();
            }
        })

    }

});


function enviarFactura() {

    var parametros = {
        "uuid": $("#modalCorreo #uuid_correo").val(),
        "correo": $("#modalCorreo #correo_cliente").val(),
    };

    $.ajax({

        data: parametros,

        url: 'servicios/enviarFactura.php',

        type: 'post',

        beforeSend: function () {

        },

        success: function (response) {

            $("#modalCorreo").modal('hide');
            $.LoadingOverlay("hide");

            if (response.codigo == 200) {

                swal('Éxito', 'La factura se envió correctamente', 'success').then(function () {
                    $(location).attr('href', 'verFacturasHistorial.php');
                });

            } else {

                swal("Error", 'Hubo un error al enviar la factura, verifque o intente de nuevo', "error").then(function () {
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
