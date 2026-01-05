
var $ = jQuery;


$('.ver').click(function () {

    var elid = this.id;

    var parametros = {
        "fk_compra": elid
    };

    $.ajax({

        data: parametros,

        url: 'servicios/getCompraDetalle.php',

        type: 'get',

        beforeSend: function () {
        },

        success: function (response) {

            if (response.codigo == 200) {

                $('#modalInfo #dtInfo').DataTable().destroy();
                $("#modalInfo #dtInfo tbody").empty();

                response.objList.forEach(element => {

                    //ESTATUS DE COMPRA
                    let badge_estatus = "<p class='badge-danger-integra'>Sin surtir</p>";
                    let unitarioFormat = parseFloat(element.unitario).toFixed(2);
                    let input_cantidad = `<input type='text' class='form-control insumo-precio' value='${unitarioFormat}'>`;
                    let actualizar_precio_chk = "<div class='d-flex gap-2'><input type='checkbox' class='actualizar-precio-chk' name='actualizar-precio-chk' style='width: 20px; height: 20px;'><p>¿Actualizar precio en ficha técnica?</p></div>";

                    if (element.faltante > 0 && element.faltante < element.cantidad) {
                        badge_estatus = "<p class='badge-warning-integra'>Surtido en proceso</p>";
                        input_cantidad = `<input type='text' class='form-control insumo-precio' value='${unitarioFormat}' disabled>`;
                        actualizar_precio_chk = "";
                    }

                    if (element.faltante == 0) {
                        badge_estatus = "<p class='badge-success-integra'>Completo</p>";
                        input_cantidad = `<input type='text' class='form-control insumo-precio' value='${unitarioFormat}' disabled>`;
                        actualizar_precio_chk = "";
                    }

                    let trHTML = `
                        <tr class="odd gradeX" data-id='${element.pk_compra_detalle}'>
                            <td>${element.cantidad}</td>
                            <td>${element.codigobarras} | ${element.nombre}</td>
                            <td>
                                ${input_cantidad}
                                <input type='hidden' class='form-control precio_og' value='${element.unitario}'>
                                ${actualizar_precio_chk}
                            </td>
                            <td>${element.total}</td>
                            <td>${badge_estatus}</td>
                            <td>${element.faltante}</td>
                        </tr>
                    `;

                    $("#modalInfo #dtInfo tbody").append(trHTML);

                });

                if (!$.fn.DataTable.isDataTable('#modalInfo #dtInfo')) {
                    $('#modalInfo #dtInfo').DataTable({
                        responsive: true,
                        ordering: true,
                        pageLength: 10,
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

                $('#modalInfo').modal('show');

            }

        },
        error: function (arg1, arg2, arg3) {
            console.log(arg3);
        }

    });


});


//MODIFICAR PRECIO UNITARIO DEL ISNUMOS DESDE MODAL VER
//#region
$(document).on('input', '.insumo-precio', function () {

    var input = $(this);
    var precio_og = parseFloat($(this).next('input.precio_og').val());

    if (parseFloat($(this).val().replace(/,/g, "")) <= 0) {
        swal('Mensaje', 'Ingrese un precio correcto', 'info');
        input.val(precio_og);
    }

    var precio = $(this).val().replace(/,/g, "");
    var cantidad = $(this).closest('tr').find('td:eq(0)').text().trim();
    var total = precio * cantidad;

    $(this).closest('tr').find('td:eq(3)').text(total);

});


function getProductos() {

    var data = [];

    $("#modalInfo #dtInfo tbody tr").each(function (index) {

        var fk_compra_detalle, unitario, actualizar_precio, total;

        fk_compra_detalle = $(this).attr('data-id');

        $(this).children("td").each(function (index2) {

            switch (index2) {

                case 2:
                    unitario = parseFloat($(this).find("input[type='text']").val().replace(/,/g, ""));
                    actualizar_precio = ($(this).find("input[type='checkbox']").is(":checked")) ? 1 : 0;
                    break;
                case 3:
                    total = parseFloat($(this).text());
                    break;

            }

        });

        var myObj = { "fk_compra_detalle": fk_compra_detalle, "unitario": unitario, "actualizar_precio": actualizar_precio, "total": total };

        data.push(myObj);

    });

    return data;

}


$(document).on('click', '#modalInfo #actualizarPrecios', function () {

    var parametros = {
        "productos": getProductos(),
    };

    $.ajax({

        data: JSON.stringify(parametros),

        url: 'servicios/editarCompraPrecios.php',

        type: 'post',

        dataType: 'json',

        contentType: "application/json; charset=utf-8",

        beforeSend: function () {

        },

        success: function (response) {

            if (response.codigo == 200) {

                swal("Éxito", "Los registros se guardaron correctamente", "success").then(function () {
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

});
//#endregion



//EXTRAS
//#region
$('.surtir').click(function () {
    var elid = this.id;
    $(location).attr('href', "surtirCompra.php?id=" + elid);

});


$('#cerrarinfo').click(function () {

    $('#modalInfo').modal('hide');
});


$('.aprobar-chk').click(function () {

    var fk_compra = this.value;

    var parametros = {
        "fk_compra": fk_compra
    };

    $.ajax({

        data: parametros,

        url: 'servicios/editarCompraAprobar.php',

        type: 'post',

        beforeSend: function () {
        },

        success: function (response) {

            if (response.codigo == 200) {

                swal("Modificación exitosa", "El registro se guardó correctamente", "success").then(function () {
                    $(location).attr('href', "verCompras.php");
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


});


$(document).on("input", "#modalInfo .insumo-precio", function () {
    $(this).val(currencyMX($(this).val()));
});
//#endregion
