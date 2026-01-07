var $ = jQuery;
var checados = "";
var total = 0;
var pdf = "";
var nentrada = 0;
var faltante = 0;
var tipo = 0;

$(document).ready(function () {

    $('.datepicker').datepicker({
        dateFormat: 'yy-mm-dd'
    });

    $('.select2').select2({
        tags: false,
        placeholder: "Escanea el código de barras"
    });

    setTimeout(function () {
        $("#clave").focus();
    }, 100);

    $('#cantidad').numeric("");
    $('#precio').numeric(".");

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

    $("#total").text("$" + total.toFixed(2));
}



//PRODUCTOS
//#region
$('#clave').on('select2:select', function (e) {

    var parametros = {
        "fk_compra": $('#compra').val(),
        "pk_producto": $('#clave').val(),
    };

    $.ajax({
        data: parametros,

        url: 'servicios/getCompraProducto.php',

        type: 'post',

        beforeSend: function () {

        },

        success: function (response) {

            var myObj = response.objList;

            if (myObj.existe == 1) {

                $("#nombred").val(myObj.nombre);
                $("#costod").val(myObj.precio);
                $("#imagen").val(myObj.imagen);
                $("#claveo").val(myObj.pk_producto);
                $("#barcode").val(myObj.codigobarras);
                $("#cantidad").val(myObj.faltante);

                agregarProducto();

            } else {

                swal("Mensaje", "El producto ingresado no existe o no forma parte de esta compra, intente de nuevo", "info").then(function () {
                    $("#clave").focus();
                });

            }

        },

        error: function (arg1, arg2, arg3) {
            console.log(arg3);
        }

    });

});


function validarAgregar() {

    var retorno = true;

    if ($("#nombred").val().length < 1) {
        retorno = false;
        $('#nombred').css('background-color', '#ffdddd');
    }

    return retorno;

}


function agregarProducto() {

    if (validarAgregar()) {

        var nombre = $("#nombred").val();
        var clave = $("#barcode").val();
        var imagen = $("#imagen").val();
        var cantidad = 1;
        var serie = $("#seried").val();
        var solicitadas = parseFloat($("#cantidad").val()).toFixed(2);
        var unitario = $("#costod").val();
        var unitario_format = parseFloat(unitario).toFixed(2);
        var descripcion = clave + "|" + nombre;
        var idp = $("#claveo").val() + "*-*" + serie;
        var idpa = idp.split("*-*");
        var total = parseFloat(unitario) * 1;
        var total_format = parseFloat(total).toFixed(2);

        //Asegurarnos de que la cantidad escaneada no sea mayor a las unidades a surtir
        var existenciaMayor = false;
        $("#entradas tbody tr").each(function () {

            if ((this.id).split("*-*")[0] == idpa[0]) {
                let cantidadF = parseFloat($("#cantidad").val()).toFixed(2);
                if (parseFloat($(this).find('td:eq(3) input').val()) + 1 > $("#cantidad").val()) {
                    swal("Mensaje", "La cantidad escaneada sobrepasa las unidades a surtir (" + cantidadF + ")", "info").then(function () {
                        setTimeout(function () {
                            $('#clave').focus();
                        }, 100);
                    });
                    existenciaMayor = true;
                }
            }

        });

        var cantidadProducto = 0;
        $("#entradas tbody tr").each(function () {
            if ((this.id).split("*-*")[0] == idpa[0]) {
                cantidadProducto += parseInt($(this).find('td:eq(3) input').val());
            }
        });

        if (parseInt(cantidadProducto) + 1 > $("#cantidad").val()) {
            let cantidadF = parseFloat($("#cantidad").val()).toFixed(2);
            swal("Mensaje", "La cantidad escaneada sobrepasa las unidades a surtir (" + cantidadF + ")", "info").then(function () {
                setTimeout(function () {
                    $('#clave').focus();
                }, 100);
            });
            existenciaMayor = true;
        }


        if (!existenciaMayor) {
            //Verificar si el producto con la serie ingresada ya existe fue agregado anteriormente
            var encontrado = false;
            $("#entradas tbody tr").each(function () {
                //var seriex = $(this).closest('tr').find('td:eq(4)').text();
                var seriex = "";

                if (serie != '') {
                    if (serie == seriex) {
                        encontrado = true;
                    }
                }

            });


            if (encontrado == false) { //El producto aún no se ha agregado

                var existeSinSerie = false;
                $("#entradas tbody tr").each(function () { //Verificar si el producto sin serie ya existe para aumentar su cantidad en +1
                    if ($(this).attr("id") === idp) {
                        existeSinSerie = true;
                        var cantidadAnterior = parseFloat($(this).find('td:eq(3) input[type=number]').val()) + 1;
                        var precioAnterior = parseFloat($(this).find('td:eq(4) input[type=number]').val());
                        var totalAnterior = cantidadAnterior * precioAnterior;
                        $(this).find('td:eq(3) input[type=number]').val(cantidadAnterior);
                        $(this).find('td:eq(5)').text(totalAnterior);
                        return false;
                    }
                });

                if (!existeSinSerie) { //Si el producto sin serie no existe se crea completamente la fila
                    var inputCantidad = "<input type='number' class='form-control cantidad' style='width: 140px;' value='1'>";
                    if (serie.length > 0) {
                        inputCantidad = "<input type='number' class='form-control cantidad' style='width: 140px;' value='1' disabled>";
                    }
                    var contenido = `
                        <tr id='${idp}'>
                            <td><i id='${idp}' class='fa fa-trash eliminar' style='padding: 3px; background-color: red; color:white'></i></td>
                            <td>${imagen}</td>
                            <td style='white-space: normal;'>${descripcion}</td>
                            <td>${inputCantidad}<p class='badge-primary-integra mt-1'>${solicitadas} por surtir</p></td>
                            <td><input type='number' class='form-control precio' style='width: 140px;' disabled value='${unitario_format}'></td>
                            <td>${total_format}</td>
                        </tr>`;
                    $("#entradas tbody").append(contenido);
                }

                getTotal();

                $('.eliminar').click(function () {
                    var idpp = this.id;
                    $("#entradas tbody tr").each(function () {
                        if (idpp == this.id) {
                            $(this).remove();
                            getTotal();

                        }
                    });
                });

            } else {
                swal("Mensaje", "Este producto y serie ya fueron agregados anteriormente", "info").then(function () {
                    setTimeout(function () {
                        $('#clave').focus();
                    }, 100);
                });
            }
        }

        limpiar();

        $("#seried").val("");

        setTimeout(function () {
            $('#clave').focus();
        }, 100);

    }
}


function limpiar() {
    $('#clave').val([]).trigger('change');
    $("#claveo").val("");
    $("#nombred").val("");
    $("#costod").val("");
    $("#cantidad").val("");
    $("#imagen").val("");
    $("#barcode").val("");
    $("#seried").val("");
    $('#seried').css('background-color', '#fff');
    $('#nombred').css('background-color', '#fff');
}


$(document).on('input', '.cantidad', function () {

    var idpa = $(this).closest('tr').attr('id').split("*-*")[0];
    var input = $(this);
    var cantidad_ingresada = $(this).val();
    var cantidad_solicitada = parseInt($(this).next('p').text().trim().split(" ")[0]);

    var cantidadProducto = 0;
    $("#entradas tbody tr").each(function () {
        if ((this.id).split("*-*")[0] == idpa) {
            cantidadProducto += parseInt($(this).find('td:eq(3) input').val());
        }
    });

    if (parseInt(cantidadProducto) > cantidad_solicitada || parseInt(cantidadProducto) == 0) {
        swal('Mensaje', 'La cantidad ingresada debe ser mayor a 0 o menor igual a la cantidad faltante, favor de ingresar una cantidad correcta', 'info').then(function () {
            input.val(1);
            var nuevaCantidad = parseInt(input.val());
            var unitario = parseFloat(input.closest('tr').find('td:eq(4) input').val());
            var total = nuevaCantidad * unitario;
            input.closest('tr').find('td:eq(6)').text(total.toFixed(2));
        });
    }

    var nuevaCantidad = parseInt($(this).val());
    var unitario = parseFloat($(this).closest('tr').find('td:eq(4) input').val());
    var total = nuevaCantidad * unitario;
    $(this).closest('tr').find('td:eq(5)').text(total.toFixed(2));

    getTotal();

})
//#endregion




//GUARDAR
//#region
function getProductos() {

    var data = [];

    $("#entradas tbody tr").each(function (index) {

        var id, cantidad, unitario, serie = "";

        var idp = this.id;
        var idpp = idp.split("*-*");
        id = idpp[0];

        $(this).children("td").each(function (index2) {

            switch (index2) {

                case 3:
                    cantidad = parseFloat($(this).find("input[type='number']").val());
                    break;
                case 4:
                    unitario = parseFloat($(this).find("input[type='number']").val());
                    break;
                case 5:
                    total = parseFloat($(this).text());
                    break;

            }

        });

        var myObj = { "fk_producto": id, "cantidad": cantidad, "serie": serie, "unitario": unitario, "total": total };

        data.push(myObj);

    });

    return data;
}


function validarGuardado() {
    var retorno = true;

    if (total < 1) {
        retorno = false;
        swal("Mensaje", "Orden vacía", "info");
    }

    return retorno;
}


$('#guardar').click(function () {

    if (validarGuardado()) {

        $("#guardar").attr("disabled", "disabled");

        var parametros = {
            "fk_usuario": $("#usuario").val(),
            "pk_contrato": $("#compra").val(),
            "total": total,
            "productos": getProductos(),
            "observaciones": $("#observaciones").val()
        };

        $.ajax({

            data: JSON.stringify(parametros),

            url: 'servicios/surtirCompra.php',

            type: 'post',

            dataType: 'json',

            contentType: "application/json; charset=utf-8",

            beforeSend: function () {

            },

            success: function (response) {

                if (response.codigo == 200) {
                    nentrada = response.objList.pk_entrada;
                    $("#nentrada").text(nentrada);
                    $("#pdf").attr("href", "entradaPDF.php?id=" + nentrada);
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
    $(location).attr("href", "verCompras.php");
});


$('#inicio').click(function () {
    $(location).attr("href", "index.php");
});


$(document).on("input", ".cantidad", function () {

    var cantidad = parseInt($(this).val());
    var precio = parseFloat($(this).closest("tr").find("td:eq(4) input[type='number']").val());

    var total = cantidad * precio;

    $(this).closest("tr").find("td:eq(7)").text(total);

})


$(document).on("input", ".precio", function () {

    var precio = parseFloat($(this).val());
    var cantidad = parseInt($(this).closest("tr").find("td:eq(3) input[type='number']").val());

    var total = cantidad * precio;

    $(this).closest("tr").find("td:eq(7)").text(total);

})
//#endregion
