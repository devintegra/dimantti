var $ = jQuery;
var checados = "";
var total = 0;
var pdf = "";
var nentrada = 0;
var productosAgregados = {};


$(document).ready(function () {

    $("#modalSucursales").modal("show");

    $('.select2').select2({
        tags: false,
        placeholder: 'SELECCIONE'
    });

    $('#cantidad').numeric("");
    $('#precio').numeric(".");

    $('.datepicker').datepicker({
        dateFormat: 'yy-mm-dd'
    });

}
);


function getTotal() {

    total = 0;

    $("#entradas tbody tr").each(function (index) {

        $(this).children("td").each(function (index2) {
            switch (index2) {
                case 5:
                    total = total + parseFloat($(this).text().replace(/,/g, ""));
                    break;
            }

        });

    });

    $("#total").text("$" + total.toFixed(2));
    $("#monto").val(total.toFixed(2));
}



//SUCURSALES
//#region
$(document).on("click", ".fc", function () {

    var fk_sucursal = $(this).closest('tr').find('td:eq(0)').text().trim();
    var sucursal = $(this).closest('tr').find('td:eq(1)').text().trim();
    var almacen = $(this).closest('tr').find('td:eq(2)').text().trim();

    $("#fk_sucursal").val(fk_sucursal);
    $("#lasucursal").text(sucursal + "(" + almacen + ")");

    $("#modalSucursales").modal("hide");

    $("#clave").focus();
});
//#endregion




//PRODUCTOS
//#region
$('#clave').on('select2:select', function (e) {

    // var selectedOption = e.params.data.id;

    // if (productosAgregados[selectedOption]) {
    //     productosAgregados[selectedOption].cantidad++;
    // } else {
    //     productosAgregados[selectedOption] = { fk_producto: parseInt(selectedOption), cantidad: 1, precio: 0 };
    // }

    // actualizarTablaProductos(productosAgregados);

    // $('#clave').val([]).trigger('change');

    // $("#clave").focus();

    let selectedOptionText = $(this).find('option:selected');
    let fk_producto = selectedOptionText.val();
    let nombre = selectedOptionText.text().trim();
    let precio = selectedOptionText.attr('data-precio');

    if (!verificarProducto(fk_producto)) {

        var trHTML = `
            <tr class='odd gradeX fp' data-id='${fk_producto}'>
                <td>
                    <i class='fa fa-trash eliminar' style='padding: 3px; background-color: red; color:white'></i>
                </td>
                <td style='white-space:normal'>${nombre}</td>
                <td>$${precio}</td>
                <td>
                    <input type='text' class='form-control precio' value='${precio}'>
                </td>
                <td>
                    <input type='number' class='form-control cantidad' value='1'>
                </td>
                <td>
                    ${precio}
                </td>
            </tr>
        `;

        $('#entradas tbody').append(trHTML);

        getTotal();
        $('#clave').val([]).trigger('change');
        $("#clave").focus();

    } else {
        swal('Mensaje', 'Este producto ya fue agregado anteriormente', 'info');
    }

});


function verificarProducto(fk_producto) {

    let retorno = false;

    $('#entradas tbody tr').each(function () {

        let id = parseInt($(this).attr('data-id'));

        if (id == fk_producto) {
            retorno = true;
            return
        }

    });

    return retorno;

}


$(document).on("click", ".eliminar", function () {

    // var id = parseInt($(this).closest('tr').attr('id').split("*-*")[0]);

    // for (var clave in productosAgregados) {
    //     if (productosAgregados.hasOwnProperty(clave) && parseInt(clave) === id) {
    //         delete productosAgregados[clave];
    //         break;
    //     }
    // }

    $(this).closest('tr').remove();
    getTotal();
    $("#clave").focus();
});


$(document).on("input", ".cantidad", function () {

    var tr = $(this).closest("tr");
    var input = $(this);

    if (parseFloat(input.val()) <= 0) {
        swal('Mensaje', 'Ingrese una cantidad correcta', 'info');
        input.val(1);
    }

    var cantidad = parseFloat(input.val());
    var precio = parseFloat(tr.find("td:eq(3) input[type='text']").val().replace(/,/g, ""));
    var total = cantidad * precio;

    tr.find("td:eq(5)").text(total.toFixed(2));

    getTotal();

});


$(document).on("input", ".precio", function () {

    var tr = $(this).closest("tr");
    var input = $(this);
    var precio_og = parseFloat($(this).next('input').val());

    if (parseFloat(input.val()) <= 0) {
        swal('Mensaje', 'Ingrese un precio correcto', 'info');
        input.val(precio_og);
    }

    var precio = input.val().replace(/,/g, "");
    var cantidad = parseFloat(tr.find("td:eq(4) input[type='number']").val());
    var total = cantidad * precio;

    tr.find("td:eq(5)").text(total.toFixed(2));

    getTotal();

});
//#endregion




//GUARDAR
//#region
function getProductos() {

    var data = [];

    $("#entradas tbody tr").each(function (index) {

        var id, cantidad, unitario, total;

        id = $(this).attr('data-id');

        $(this).children("td").each(function (index2) {

            switch (index2) {

                case 3:
                    unitario = parseFloat($(this).find("input[type='text']").val().replace(/,/g, ""));
                    break;
                case 4:
                    cantidad = parseFloat($(this).find("input[type='number']").val());
                    break;
                case 5:
                    total = parseFloat($(this).text().replace(/,/g, ""));
                    break;

            }

        });

        var myObj = { "fk_producto": id, "cantidad": cantidad, "unitario": unitario, "total": total };

        data.push(myObj);

    });

    return data;

}


$('#guardar').click(function () {
    var retorno = true;

    if ($('#proveedor').val() < 1) {
        retorno = false;
        $('#proveedor').css('background-color', '#ffdddd');
    }

    if ($("#entradas tbody tr").length == 0) {
        retorno = false;
        swal("Error", "Orden vacía", "error");
    }

    if ($("#fk_pago").val() == 0) {
        retorno = false;
        $('#fk_pago').css('background-color', '#ffdddd');
    }

    if ($("#monto").val().length < 1) {
        retorno = false;
        $('#monto').css('background-color', '#ffdddd');
    }

    if (retorno == true) {
        var dias_credito = $("#proveedor").val().split("*-*")[1];
        var rango_fecha = parseInt($("#fecha_fin").val().split("-")[2]) - parseInt($("#fecha_inicio").val().split("-")[2]);

        if (rango_fecha > dias_credito) {
            Swal.fire({
                title: 'El rango de fechas excede los días de crédito del proveedor',
                html:
                    '<b class="badge-primary-integra">Días de crédito: ' + dias_credito + '</b><br>',
                text: "¿Deseas continuar?",
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
            });
        } else {
            guardar();
        }
    }

    return retorno;
});


function guardar() {

    $("#guardar").attr("disabled", "disabled");

    var parametros = {
        "fk_sucursal": $("#fk_sucursal").val(),
        "fk_usuario": $("#usuario").val(),
        "fk_proveedor": $("#proveedor").val().split("*-*")[0],
        "inicio": $("#fecha_inicio").val(),
        "fin": $("#fecha_fin").val(),
        "tipo": $("#tipo").val(),
        "factura": $("#factura").val(),
        "fk_pago": $("#fk_pago").val(),
        "monto": $("#monto").val().replace(/,/g, ""),
        "productos": getProductos(),
        "total": total,
        "observaciones": $("#observaciones").val().replace(/['"]/g, ''),
    };

    $.ajax({

        data: JSON.stringify(parametros),

        url: 'servicios/agregarCompra.php',

        type: 'post',

        dataType: 'json',

        contentType: "application/json; charset=utf-8",

        beforeSend: function () {

        },

        success: function (response) {

            if (response.codigo == 200) {

                nentrada = response.objList.pk_compra;

                if ($("#archivo").val().length > 4) {
                    upArchivo(nentrada);
                } else {
                    $("#nentrada").text($("#contrato").val());
                    $("#pdf").attr("href", "compraPDF.php?id=" + nentrada);
                    $('#exito').modal('show');
                }

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

};


function upArchivo(pk_contrato) {

    var f = $(this);
    var formData = new FormData(document.getElementById("formuploadajax"));
    formData.append("pk_contrato", pk_contrato);

    $.ajax({
        url: "servicios/agregarCompraArchivo.php",
        type: "post",
        dataType: "html",
        data: formData,
        cache: false,
        contentType: false,
        processData: false,

        beforeSend: function () {

        },

        success: function (response) {

            response = (JSON.parse(response));

            if (response.codigo == 200) {

                $("#nentrada").text($("#contrato").val());
                $("#pdf").attr("href", "compraPDF.php?id=" + pk_contrato);
                $('#exito').modal('show');

            }

        },

        error: function (arg1, arg2, arg3) {
            alert(arg3);
        }

    });

}
//#endregion




//EXTRAS
//#region
$('#nueva').click(function () {
    $(location).attr("href", "agregarCompra.php");
});


$('#ver_registros').click(function () {
    $(location).attr("href", "verCompras.php");
});


$('#inicio').click(function () {
    $(location).attr("href", "index.php");
});


$('#pago').on('change', function () {

    if (parseInt(this.value) > 1) {
        $("#monto_pago").removeAttr("disabled");
    } else {
        $("#monto_pago").attr("disabled", "disabled")
    }

});


$('#credito').on('change', function () {

    if (parseInt(this.value) == 1) {
        $("#dias_credito").removeAttr("disabled");
    } else {
        $("#dias_credito").attr("disabled", "disabled")
    }

});


$('#proveedor').on('change', function () {

    var dias_credito = parseInt($(this).val().split("*-*")[1]);

    if (dias_credito > 0) {
        $("#fecha_inicio").removeAttr("disabled");
        $("#fecha_fin").removeAttr("disabled");
        $("#monto").removeAttr("disabled");
    } else {
        $("#fecha_inicio").attr("disabled", "disabled");
        $("#fecha_fin").attr("disabled", "disabled");
        $("#monto").attr("disabled", "disabled");
    }

});


$(document).on("input", "#monto, .precio, #costo_producto, .precio_pr", function () {
    $(this).val(currencyMX($(this).val()));
});
//#endregion
