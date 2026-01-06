var $ = jQuery;
var id;


$(document).ready(function () {

    $.ajax({

        url: 'servicios/agregarInventarioGenerarId.php',

        type: 'POST',

        contentType: "application/x-www-form-urlencoded;charset=utf-8",

        beforeSend: function () {

        },

        success: function (response) {

            id = response.objList.pk_inventario;

            if (response.objList.existe == 0) {

                $("#modalSucursales").modal("show");

            } else {

                getInventario(id);
                $("#lafecha").text("Última modificación el " + response.objList.fecha);

            }

        },

        error: function (arg1, arg2, arg3) {
            console.log(arg3);
        }
    });

    $('.select2').select2({
        tags: false,
        placeholder: "Ingrese el código de barras del producto",
    });

});



//INVENTARIO
//#region
function getInventario(id) {

    $.LoadingOverlay("show", {
        color: "rgba(255, 255, 255, 0)"
    });

    var parametros = {
        "fk_inventario": id
    };

    $.ajax({
        data: parametros,

        url: 'servicios/getInventario.php',

        type: 'get',

        beforeSend: function () {
        },

        success: function (response) {

            $('#dtProductos').DataTable().destroy();
            $("#dtProductos tbody").empty();

            if (response.codigo == 200) {

                response.objList.forEach(element => {

                    //COLOR
                    //#region
                    let color = "";
                    if (element.inventario_existencia_real === null || (element.inventario_existencia_real).trim().length === 0) {
                        color = '';
                    } else if (element.inventario_existencia_real < 0) {
                        color = '#FF9EA2';
                    } else if (element.inventario_existencia_real > 0) {
                        color = '#FFFFCC';
                    } else {
                        color = '#D0FDD7';
                    }
                    //#endregion

                    let trHTML = `
                        <tr class='odd gradeX' id='${element.pk_producto}'>
                            <td>
                                <img style='border-radius: 7px; width:50px; height:50px; object-fit:cover;' loading='lazy' src='${element.imagen}'>
                            </td>
                            <td style='white-space: normal'>${element.clave}</td>
                            <td style='white-space: normal'>${element.nombre}</td>
                            <td>${element.cantidad}</td>
                            <td>
                                <div class='d-flex'>
                                    <button class='btn btn-sm btn-primary-dast disminuir'>-</button>
                                    <input type='text' class='form-control input-cantidad' value='${element.inventario_escaneadas}' autocomplete='off' style='width: 40%'>
                                    <button class='btn btn-sm btn-primary-dast aumentar'>+</button>
                                    <button class='btn btn-sm btn-success-dast ajustar'>Ajustar</button>
                                </div>
                            </td>
                            <td style='background-color:${color}'>
                                ${element.inventario_existencia_real}
                            </td>
                        </tr>
                    `;

                    $("#dtProductos tbody").append(trHTML);

                });

            }

            $('#codigo_barras').focus();

            $.LoadingOverlay("hide");

        },
        error: function (arg1, arg2, arg3) {
            console.log(arg3);
        }

    });

}
//#endregion




//SUCURSALES
//#region
$(".fc").click(function () {

    var ids = this.id;
    var idss = ids.split("*-*");
    var fk_sucursal = idss[1];

    $("#fk_sucursal").val(fk_sucursal);

    var sucursal = $(this).closest('tr').find('td:eq(0)').text().trim();
    $("#lafecha").text(sucursal);

    obtenerAlmacenes();

    $("#dtEmpresa tbody tr").each(function (index) {

        $(this).css('background-color', 'transparent');

        if (this.id == ids) {
            $(this).css('background-color', '#D0FDD7');
        }

    })

});
//#endregion




//ALMACENES
//#region
function obtenerAlmacenes() {

    var parametros = {
        "fk_sucursal": $("#fk_sucursal").val()
    };

    $.ajax({
        data: parametros,

        url: 'servicios/getSucursalAlmacenes.php',

        type: 'get',

        beforeSend: function () {
        },

        success: function (response) {

            if (response.codigo == 200) {

                response.objList.forEach(element => {

                    let contentHTML = `
                        <div class='form-check form-check-warning mx-4'>
                            <label class='form-check-label fs-6'>
                            <input type='checkbox' class='form-check-input chk-almacenes' value='${element.pk_sucursal_almacen}'>
                            ${element.nombre}
                            <i class='input-helper'></i>
                            </label>
                        </div>
                    `;

                    $("#almacenes").append(contentHTML);

                });

            }

        },
        error: function (arg1, arg2, arg3) {
            console.log(arg3);
        }

    });

}
//#endregion




//FILTRAR
//#region
var almacenes = [];
var categorias = [];
var productos = [];

function getSeleccionados() {

    $(document).find('.chk-almacenes:checked').each(function () {
        almacenes.push($(this).val());
    });

    $(document).find('.chk-categorias:checked').each(function () {
        categorias.push($(this).val());
    });

    $(document).find('.chk-productos:checked').each(function () {
        productos.push($(this).val());
    });

}


function validarFiltrar() {

    var retorno = true;

    if ($('#fk_sucursal').val() == 0) {
        retorno = false;
        swal("Mensaje", "Es necesario elegir una sucursal para avanzar", "info");
    }

    return retorno;

}


$("#filtrar").click(function () {

    if (validarFiltrar()) {

        $.LoadingOverlay("show", {
            color: "rgba(255, 255, 255, 0)"
        });

        getSeleccionados();

        $("#arrAlmacenes").val(almacenes.join(','));
        $("#arrCategorias").val(categorias.join(','));
        $("#arrProductos").val(productos.join(','));

        var parametros = {
            "fk_sucursal": $("#fk_sucursal").val(),
            "almacenes": almacenes,
            "categorias": categorias,
            "productos": productos
        };

        $.ajax({
            data: parametros,

            url: 'servicios/getProductosInventario.php',

            type: 'get',

            beforeSend: function () {
            },

            success: function (response) {

                $.LoadingOverlay("hide");

                if (response.codigo == 200) {

                    response.objList.forEach(element => {

                        let trHTML = `
                            <tr class='odd gradeX' id='${element.pk_producto}'>
                                <td>
                                    <img style='border-radius: 7px; width:50px; height:50px; object-fit:cover;' loading='lazy' src='${element.imagen}'>
                                </td>
                                <td style='white-space: normal'>${element.codigobarras}</td>
                                <td style='white-space: normal'>${element.descripcion}</td>
                                <td>${element.cantidad}</td>
                                <td>
                                    <div class='d-flex'>
                                        <button class='btn btn-sm btn-primary-dast disminuir'>-</button>
                                        <input type='text' class='form-control input-cantidad' autocomplete='off' style='width: 40%'>
                                        <button class='btn btn-sm btn-primary-dast aumentar'>+</button>
                                        <button class='btn btn-sm btn-success-dast ajustar'>Ajustar</button>
                                    </div>
                                </td>
                                <td>
                                </td>
                            </tr>
                        `;

                        $('#dtProductos tbody').append(trHTML);

                    });

                }

                setTimeout(function () {
                    $("#codigo_barras").focus();
                }, 100);

                $("#modalSucursales").modal("hide");

            },
            error: function (arg1, arg2, arg3) {
                console.log(arg3);
            }

        });

    }


});
//#endregion





//MANEJAR INVENTARIO
//#region
$('#codigo_barras').on("select2:select", function (event) {

    var codigobarras = event.params.data.id;

    var parametros = {
        "clave": codigobarras
    };

    $.ajax({
        data: parametros,

        url: 'servicios/getProductoByClave.php',

        type: 'GET',

        beforeSend: function () {
        },

        success: function (response) {

            if (response.codigo == 200) {

                var id = response.objList.pk_producto;

                if ($("#dtProductos tbody tr#" + id).length > 0) {

                    actualizarInventario(id);

                    var audio = new Audio("../portal/images/scanner-check.mp3");
                    audio.play();

                    guardadoAutomatico();

                    $("#codigo_barras").val("");
                    $("#codigo_barras").focus();

                } else {
                    var audio = new Audio("../portal/images/scanner-error.mp3");
                    audio.play();
                }

            } else {

                swal('Error', response.descripcion, 'error');

            }

        },
        error: function (arg1, arg2, arg3) {
            console.log(arg3);
        }

    });

    $('#codigo_barras').val([]).trigger('change');

});


function actualizarInventario(fk_producto) {

    var row = "#dtProductos tbody tr#" + fk_producto;

    var existencia = parseInt($(row).find("td:eq(3)").text());
    var escaneadas = $(row).find("td:eq(4)").find('input').val() || 0;
    var existencia_real;

    //Escaneadas
    //escaneadas.length == 0 ? escaneadas = 1 : escaneadas++;
    escaneadas++;

    $(row).find("td:eq(4)").find('input').val(escaneadas);

    //Existencia real
    existencia_real = existencia - parseInt(escaneadas); //escaneadas - existencia

    $(row).find("td:eq(5)").text(existencia_real);

    actualizarColor(row, existencia_real);

}


$(document).on('input', '.input-cantidad', function () {

    var valorInput = $(this).val();
    valorInput = valorInput.replace(/[^\d]/g, '');
    $(this).val(valorInput);

    if (valorInput != '') {
        var existencia = parseInt($(this).closest('tr').find("td:eq(3)").text());
        var existencia_real = existencia - parseInt(valorInput); //valorInput - existencia
        $(this).closest('tr').find("td:eq(5)").text(existencia_real);

    } else {
        $(this).closest('tr').find("td:eq(5)").text('');
    }

    actualizarColor($(this).closest('tr'), existencia_real);

    guardadoAutomatico();

});


$(document).on('click', '.disminuir', function () {

    var row = $(this).closest('tr');
    var cantidad = parseInt($(this).closest('tr').find('td:eq(4)').find('input').val()) || 0;
    var existencia = parseInt($(row).find("td:eq(3)").text());
    var existencia_real;

    cantidad--;

    $(this).closest('tr').find('td:eq(4)').find('input').val(cantidad);

    existencia_real = existencia - cantidad; //cantidad - existencia
    $(row).find("td:eq(5)").text(existencia_real);

    actualizarColor(row, existencia_real);

    guardadoAutomatico();

})


$(document).on('click', '.aumentar', function () {

    var row = $(this).closest('tr');
    var cantidad = parseInt($(this).closest('tr').find('td:eq(4)').find('input').val()) || 0;
    var existencia = parseInt($(row).find("td:eq(3)").text());
    var existencia_real;

    cantidad++;

    $(this).closest('tr').find('td:eq(4)').find('input').val(cantidad);

    existencia_real = existencia - cantidad; //cantidad - existencia
    $(row).find("td:eq(5)").text(existencia_real);

    actualizarColor(row, existencia_real);

    guardadoAutomatico();

})


$(document).on('click', '.ajustar', function () {

    var row = $(this).closest('tr');
    var existencias = Math.abs(parseInt($(this).closest('tr').find('td:eq(3)').text()));

    $(this).closest('tr').find('td:eq(4)').find('input').val(existencias);

    $(row).find("td:eq(5)").text(0);

    actualizarColor(row, 0);

    guardadoAutomatico();

})


function actualizarColor(row, existencia_real) {

    //Color
    if (existencia_real > 0) {
        $(row).find("td:eq(5)").css('background-color', '#FFFFCC');
    } else if (existencia_real < 0) {
        $(row).find("td:eq(5)").css('background-color', '#FF9EA2');
    } else if (existencia_real == 0) {
        $(row).find("td:eq(5)").css('background-color', '#D0FDD7');
    } else {
        $(row).find("td:eq(5)").css('background-color', 'transparent');
    }

}


$("#ajuste_global").click(function () {

    Swal.fire({
        title: 'Ajustar inventario',
        text: "¿Estás seguro que deseas ajustar todo el inventario?",
        icon: 'warning',
        showCancelButton: true,
        iconColor: '#368FCD',
        confirmButtonColor: '#368FCD',
        cancelButtonColor: '#000',
        confirmButtonText: 'Si, ajustar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {

            $("#dtProductos tbody tr").each(function (index) {

                var row = $(this);
                var existencias = Math.abs(parseInt($(this).find('td:eq(3)').text()));

                $(this).find('td:eq(4)').find('input').val(existencias);
                $(this).find('td:eq(5)').text(0);

                actualizarColor(row, 0);

            });

            guardadoAutomatico();

        }
    })

})
//#endregion




//GUARDAR
//#region
function getProductos() {

    var data = [];

    $("#dtProductos tbody tr").each(function (index) {

        var id, clave, existencias, escaneadas, existencia_real;

        var id = this.id;

        $(this).children("td").each(function (index2) {

            switch (index2) {

                case 1:
                    clave = $(this).text().trim();
                    break;

                case 3:
                    existencias = parseInt($(this).text());
                    break;

                case 4:
                    escaneadas = $(this).find('input').val();
                    break;

                case 5:
                    existencia_real = $(this).text();
                    break;

            }

        });

        var myObj = { "fk_producto": id, "clave": clave, "existencias": existencias, "escaneadas": escaneadas, "existencia_real": existencia_real };

        data.push(myObj);

    });

    return data;
}


function guardadoAutomatico() {

    var parametros = {
        "pk_inventario": id,
        "fk_sucursal": $("#fk_sucursal").val(),
        "almacenes": $("#arrAlmacenes").val(),
        "categorias": $("#arrCategorias").val(),
        "productosfl": $("#arrProductos").val(),
        "fk_usuario": $("#fk_usuario").val(),
        "productos": getProductos()
    };

    $.ajax({

        data: JSON.stringify(parametros),

        url: 'servicios/agregarInventario.php',

        type: 'post',

        dataType: 'json',

        contentType: "application/json; charset=utf-8",

        beforeSend: function () {

        },

        success: function (response) {

        },

        error: function (arg1, arg2, arg3) {
            console.log(arg3);
        }
    });

}
//#endregion




//ACCIONES
//#region
$("#finalizar").click(function () {

    $.LoadingOverlay("show", {
        color: "rgba(255, 255, 255, 0)"
    });

    var parametros = {
        "fk_inventario": id,
        "tipo": 2,
    };

    $.ajax({

        data: parametros,

        url: 'servicios/editarInventarioEstatus.php',

        type: 'POST',

        beforeSend: function () {

        },

        success: function (response) {

            $.LoadingOverlay("hide");

            nentrada = response.objList.pk_inventario;

            if (response.codigo == 200) {

                $("#nentrada").text(nentrada);
                $("#pdf").attr("href", "inventarioPDF.php?id=" + nentrada);
                $("#excel").attr("href", "servicios/reporteRegistroInventario.php?id=" + nentrada);
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

})


$("#cancelar").click(function () {

    $.LoadingOverlay("show", {
        color: "rgba(255, 255, 255, 0)"
    });

    var parametros = {
        "fk_inventario": id,
        "tipo": 3,
    };

    $.ajax({

        data: parametros,

        url: 'servicios/editarInventarioEstatus.php',

        type: 'POST',

        beforeSend: function () {

        },

        success: function (response) {

            $.LoadingOverlay("hide");

            nentrada = response.objList.pk_inventario;

            if (response.codigo == 200) {

                swal("Éxito", "El inventario fue cancelado exitosamente", "success").then(function () {
                    location.reload();
                });

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

})
//#endregion





//EXTRAS
//#region
$('#nueva').click(function () {
    $(location).attr("href", "verInventario.php");
});


$('#inicio').click(function () {
    $(location).attr("href", "index.php");
});
//#endregion
