var $ = jQuery;

var draw = 0;

$(document).ready(function () {

    $('.datepicker').datepicker({
        dateFormat: 'yy-mm-dd'
    });

    getOrdenes();

});


//FILTRAR
//#region
function validar() {

    var retorno = true;

    if ($('#inicio').val().length > 0) {

        if ($('#inicio').val().length < 10) {
            retorno = false;
            $('#inicio').css('background-color', '#ffdddd');
        }

        if ($('#fin').val().length < 10) {
            retorno = false;
            $('#fin').css('background-color', '#ffdddd');
        }

    }

    if ($('#inicio').val().length < 1 && $("#sucursal").val() < 1 && $("#tipo").val() < 1) {
        retorno = false;
        alert("No hay ningun criterio de busqueda");
    }

    return retorno;

}


$('#buscar').click(function () {
    if (validar()) {
        getOrdenes();
    }
});


function getOrdenes() {

    var parametros = {
        "tipo": $("#tipo").val(),
        "pk_sucursal": $("#sucursal").val(),
        "fk_usuario": $("#fk_usuario").val(),
        "nivel": $("#nivel").val(),
        "inicio": $("#inicio").val(),
        "fin": $("#fin").val(),
        "fecha": $("#lafecha").val()
    };

    $.ajax({

        data: parametros,

        url: 'servicios/getOrdenes.php',

        type: 'GET',

        beforeSend: function () {

        },

        success: function (response) {

            if (response.codigo == 200) {

                $('#dtEmpresa tbody').empty();
                let registros = response.objList;

                registros.forEach(element => {

                    //BOTONES
                    //#region
                    let reabierta = "";
                    let tecnico = "";
                    let link_venta = "";
                    let estatus = "<p class='badge-default-integra'>Sin asignar</p>";
                    let reabrir = "";
                    let ventaPDF = "";
                    let ventaPDFlink = "";
                    let fecha_entrega = "";
                    let ordenPDF = `https://dimantti.integracontrol.online/portal/ordenPDF.php?id=${element.id}%26ph=${element.telefono}`;
                    let seguimiento = `https://dimantti.integracontrol.online/seguimiento.php?id=${element.id}%26ph=${element.telefono}`;

                    if (parseInt(element.reabierta) > 0) {
                        reabierta = "(Reabierta) ";
                    }

                    switch (parseInt(element.estatus)) {
                        case 2:
                            estatus = "<p class='badge-primary-integra'>Asignada</p>";
                            tecnico = `${element.tecnico}<br> <button type='button' class='btn-iniciar-dast btn-iniciar'><i class='bx bx-chevrons-right fs-4' title='Iniciar'></i></button>`;
                            break;
                        case 3:
                            tecnico = (parseInt(element.espera) != 1) ? `${element.tecnico}<br>` : "";
                            estatus = (parseInt(element.espera) == 0) ? `<p class='badge-warning-integra'> ${reabierta} En curso</p>` : "<p class='badge-purple-integra'>En espera</p>";
                            break;
                        case 4:
                            tecnico = `${element.tecnico}<br> <button type='button' id='${element.id}' class='btn-entregar-dast entregar'><i class='bx bx-send fs-4' title='Entregar'></i></button>`;
                            estatus = "<p class='badge-orange-integra'>Terminada</p>";
                            reabrir = `<button type='button' id='${element.id}' class='btn-reabrir-dast reabrir'><i class='bx bx-history fs-4' title='Reabrir'></i></button>`;
                            break;
                        case 5:
                            estatus = "<p class='badge-success-integra'>Entregada</p>";
                            tecnico = "";
                            link_venta = `<a target='_blank' title='Hoja de venta' href='ventaPDF.php?id=${element.fk_venta}&ph=${element.telefono}'><i class='fa fa-file-pdf-o vpdfb fa-lg btn-pdf'></i></a>`;
                            reabrir = `<button type='button' id='${element.id}' class='btn-reabrir-dast reabrir'><i class='bx bx-history fs-4' title='Reabrir'></i></button>`;
                            ventaPDFlink = `https://dimantti.integracontrol.online/portal/ventaPDF.php?id=${element.fk_venta}%26ph=${element.telefono}`;
                            ventaPDF = `<a target='_blank' href='https://wa.me/+52${element.telefonoOutFormat}?text=Confirmamos%20de%20entregado%20su%20articulo%20con%20Orden%20de%20servicio%20No.${element.folio},%20puede%20descargar%20su%20recibo%20de%20pago%20directamente%20del%20siguiente%20enlace:${ventaPDFlink}%20%0AGracias%20por%20permitirnos%20ayudarles%20en%20sus%20necesidades%20tecnol%C3%B3gicas,%20%C2%A1fue%20un%20placer%20anterderle!.%0A*Este%20es%20un%20mensaje%20automatizado'><i class='bx bxl-whatsapp btn-whatsapp'></i></a>`;
                            fecha_entrega = element.fecha_entrega;
                            break;
                    }
                    //#endregion

                    let trHTML = `
                        <tr class='odd gradeX' data-id='${element.id}' data-estimado='${element.estimado}'>
                            <td><a style='text-decoration:none' href='verOrden.php?id=${element.id}'>${element.folio}</a></td>
                            <td><a style='text-decoration:none' href='verOrden.php?id=${element.id}'>${element.nombre}</a></td>
                            <td><a style='text-decoration:none' href='verOrden.php?id=${element.id}'>${element.telefono}</a></td>
                            <td><a style='text-decoration:none' href='verOrden.php?id=${element.id}'>${element.fecha}</a></td>
                            <td><a style='text-decoration:none' href='verOrden.php?id=${element.id}'>${element.nombre}</a></td>
                            <td>${tecnico} ${reabrir}</td>
                            <td>
                                ${estatus}
                                ${fecha_entrega}
                            </td>
                            <td>
                                <div>
                                    <a style='text-decoration:none' title='Hoja de orden' target='_blank' href='ordenPDF.php?id=${element.id}&ph=${element.telefono}'><i class='fa fa-file-pdf-o vpdf fa-lg btn-pdf'></i></a>
                                    ${link_venta}
                                </div>
                            </td>
                            <td>
                                <div>
                                    <a target='_blank' title='Enviar hoja de orden' href='https://wa.me/+52${element.telefonoOutFormat}?text=Apreciable%20cliente,%20confirmamos%20de%20recibido%20su%20articulo%20con%20Orden%20de%20servicio%20No.${element.folio},%20puede%20descargar%20su%20hoja%20directamente%20del%20siguiente%20enlace:%20${ordenPDF}%0AA%20trav%C3%A9s%20de%20este%20medio%20le%20enviaremos%20notificaciones%20respecto%20al%20avance%20de%20su%20servicio.%0AAdicionalmente%20le%20informamos%20que%20puede%20consultar%20el%20estatus%20de%20su%20servicio%20directamente%20en%20este%20enlace:%20${seguimiento}%0ASi%20no%20puede%20descargar%20su%20orden,%20guarde%20este%20número%20en%20sus%20contactos%20e%20intente%20nuevamente'><i class='bx bxl-whatsapp btn-whatsapp'></i></a>
                                    ${ventaPDF}
                                </div>
                            </td>
                        </tr>
                    `;

                    $('#dtEmpresa tbody').append(trHTML);

                });

                if (!$.fn.DataTable.isDataTable('#dtEmpresa')) {
                    $('#dtEmpresa').DataTable({
                        responsive: true,
                        ordering: true,
                        order: [3, 'desc'],
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

            } else {

                swal('Error', response.descripcion, 'error');

            }

        },

        error: function (arg1, arg2, arg3) {
            console.log(arg3);
        }

    });

}
//#endregion




//INICIAR
//#region
$(document).on('click', '.btn-iniciar', function () {

    $.LoadingOverlay("show", {
        color: "rgba(255, 255, 255, 0)"
    });

    let pk_orden = $(this).closest('tr').attr('data-id');
    let estimado = $(this).closest('tr').attr('data-estimado');

    var parametros = {
        "pk_orden": pk_orden,
        "tipo": 3,
        "publico": 0,
        "costo": 0,
        "precio": 0,
        "descripcion": "Inicio de orden",
        "fk_usuario": $("#fk_usuario").val(),
        "estimado": estimado
    };

    $.ajax({

        data: parametros,

        url: 'servicios/agregarRegistro.php',

        type: 'post',

        beforeSend: function () {

        },

        success: function (response) {
            $('#estimado').modal('hide');
            $.LoadingOverlay("hide");

            var myObj = response;

            if (myObj.codigo == 200) {

                swal("Guardado exitoso", "El registro se guardó correctamente", "success").then(function () {
                    $(location).attr('href', "verOrden.php?id=" + pk_orden);
                });

            }
            else {
                swal("Error", myObj.descripcion, "error").then(function () {
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




//REABRIR
//#region
$(document).on('click', '.reabrir', function () {

    var elid = this.id;

    $.LoadingOverlay("show", {
        color: "rgba(255, 255, 255, 0)"
    });

    var parametros = {
        "pk_orden": elid,
        "fk_usuario": $("#fk_usuario").val()
    };

    $.ajax({

        data: parametros,

        url: 'servicios/reabrir.php',

        type: 'post',

        beforeSend: function () {

        },

        success: function (response) {

            $.LoadingOverlay("hide");

            var myObj = response;

            if (myObj.codigo == 200) {

                swal("Guardado exitoso", "El registro se guardó correctamente", "success").then(function () {
                    $(location).attr('href', "verOrden.php?id=" + elid);
                });

            }
            else {
                swal("Error", myObj.descripcion, "error").then(function () {
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




//ENTREGAR
//#region
$(document).on('click', '.entregar', function () {
    var elid = this.id;
    $(location).attr('href', "entrega.php?id=" + elid);
});
//#endregion




//EXTRAS
//#region
$('#iniciob').click(function () {
    $(location).attr("href", "index.php");
});


$('.btn-add').click(function () {
    $(location).attr("href", "agregarOrden.php");
});
//#endregion
