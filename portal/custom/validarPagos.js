var $ = jQuery;
var id = 0;

$(document).ready(function () {
});



//APROBAR PAGO
//#region
$(document).on('click', '.validar', function () {

    var pk_abono = $(this).attr('data-id');

    Swal.fire({
        title: 'Validar pago',
        text: "¿Estás seguro que deseas validar el pago?",
        icon: 'warning',
        showCancelButton: true,
        iconColor: '#368FCD',
        confirmButtonColor: '#368FCD',
        cancelButtonColor: '#000',
        confirmButtonText: 'Si, validar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {

        if (result.isConfirmed) {

            var parametros = {
                "pk_abono": pk_abono,
                "fk_usuario_valida": $("#fk_usuario").val()
            };

            $.ajax({
                data: parametros,

                url: 'servicios/validarPagos.php',

                type: 'POST',

                beforeSend: function () {

                },

                success: function (response) {

                    if (response.codigo == 200) {
                        swal("Validación exitosa", "El registro se guardó correctamente", "success").then(function () {
                            $(location).attr('href', "validarPagos.php");
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

});
//#endregion



//DETALLES DE LA VENTA
//#region
$(document).on('click', '.venta', function () {

    var pk_venta = $(this).attr('data-id');
    getDetalleVenta(pk_venta);
    $("#modalVenta").modal('show');

});


function getDetalleVenta(pk_venta) {

    var parametros = {
        "pk_venta": pk_venta,
    };

    $.ajax({
        data: parametros,

        url: 'servicios/getVentaDetalle.php',

        type: 'GET',

        beforeSend: function () {

        },

        success: function (response) {

            if (response.codigo == 200) {

                $('#modalVenta #tablaVenta tbody').empty();
                let subtotal = 0.00;
                let cantidad_productos = 0;
                let descuento = 0.00;

                response.objList.forEach(element => {

                    let costo = parseFloat(element.unitario).toFixed(2);
                    let total = parseFloat(element.total).toFixed(2);

                    descuento += parseFloat(element.descuento);
                    cantidad_productos += parseFloat(element.cantidad);
                    subtotal += parseFloat(element.total);

                    let trHTML = `
                        <tr class='odd gradeX'>
                            <td>${element.cantidad}</td>
                            <td style='white-space: normal'>${element.codigobarras}</td>
                            <td style='white-space: normal'>${element.descripcion}</td>
                            <td>$${costo}</td>
                            <td>$${total}</td>
                        </tr>
                    `;

                    $('#modalVenta #tablaVenta tbody').append(trHTML);

                });

                let total = parseFloat(subtotal - descuento).toFixed(2);
                subtotal = subtotal.toFixed(2);
                descuento = descuento.toFixed(2);

                $('#modalVenta #tablaVenta tfoot #cantidadProductos').text(cantidad_productos);
                $('#modalVenta #tablaVenta tfoot #subtotal').text(subtotal);
                $('#modalVenta #tablaVenta tfoot #descuento').text(descuento);
                $('#modalVenta #tablaVenta tfoot #total').text(total);

            }

        },
        error: function (arg1, arg2, arg3) {
            console.log(arg3);
        }
    });
}


$(document).on('click', '#cerrarVenta', function () {
    $("#modalVenta").modal('hide');
})

//#endregion




//SUBIR COMPROBANTE
//#region
$(document).on('click', '.comprobante', function () {
    $("#modalComprobante #pk_abono_comprobante").val($(this).attr('data-id'));
    $("#modalComprobante").modal('show');
});


function validarComprobante() {
    var retorno = true;

    if ($('#archivo').val().length < 5) {
        retorno = false;
        $('#archivo').css('background-color', '#ffdddd');
    }

    return retorno;

}


$(document).on('click', '#subirComprobante', function () {

    if (validarComprobante()) {

        $.LoadingOverlay("show", {
            color: "rgba(255, 255, 255, 0)"
        })

        var formData = new FormData(document.getElementById("formuploadajax"));
        formData.append("pk_abono", $("#modalComprobante #pk_abono_comprobante").val());

        $.ajax({
            url: "servicios/agregarArchivoAbono.php",
            type: "post",
            dataType: "html",
            data: formData,
            cache: false,
            contentType: false,
            processData: false,

            beforeSend: function () {

            },

            success: function (response) {

                $.LoadingOverlay("hide");

                var response = JSON.parse(response);

                if (response.codigo == 200) {

                    swal("Enviado", "El registro se guardó correctamente", "success").then(function () {
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
