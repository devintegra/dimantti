var $ = jQuery;


$(document).ready(function () {

    getExistencias();

});


//OBTENER EXISTENCIAS
//#region
function getExistencias() {

    $.LoadingOverlay("show", {
        color: "rgba(255, 255, 255, 0)"
    });

    var parametros = {
        "nivel": $("#nivel").val(),
        "tipo_inventario": $("#tipo_inventario").val()
    };

    $.ajax({

        data: parametros,

        url: 'servicios/getExistencias.php',

        type: 'GET',

        beforeSend: function () {

        },

        success: function (response) {

            $('#dtEmpresa').DataTable().destroy();
            $("#dtEmpresa tbody").empty();

            $.LoadingOverlay("hide");

            $.each(response.objList, function (i, element) {

                var badge = "";
                if (parseInt(element.cantidad) < parseInt(element.inventariomin)) {
                    badge = "<p class='badge-danger-integra'>Necesario surtir</p>";
                } else if (parseInt(element.cantidad) > parseInt(element.inventariomax)) {
                    badge = "<p class='badge-success-integra'>Inventario superior</p>";
                } else {
                    badge = "<p class='badge-primary-integra'>Inventario estable</p>";
                }

                //IMAGEN
                //#region
                var file = `servicios/productos/${element.imagen}`;
                if (file) {
                    var fondo = `<img style='border-radius: 7px; width:50px; height:50px; object-fit:cover;' loading='lazy' src='servicios/productos/${element.imagen}'>`;
                } else {
                    var fondo = "<img style='border-radius: 7px; width:50px; height:50px; object-fit:cover;' loading='lazy' src='images/picture.png'>";
                }
                //#endregion

                var tr = `
                    <tr class='odd gradeX'>
                        <td>${fondo}</td>
                        <td style='white-space: normal'>${element.sucursal} / ${element.almacen}</td>
                        <td>${element.codigobarras}</td>
                        <td style='white-space: normal'>${element.descripcion_producto}</td>
                        <td style='white-space: normal'>${element.serie}</td>
                        <td>${element.cantidad}</td>
                        <td>${badge}</td>
                    </tr>
                `;

                $("#dtEmpresa tbody").append(tr);

            });

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
                    order: [
                        [3, 'desc']
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


$(document).on('change', "#tipo_inventario", function () {

    getExistencias();

});
//#endregion




//EXTRAS
//#region
function ver(elid) {


    $("#registros").html("");


    $.LoadingOverlay("show", {
        color: "rgba(255, 255, 255, 0)"

    });

    var parametros = {
        "pk_producto": elid
    };
    $.ajax({

        data: parametros,

        url: 'servicios/verSeries.php',

        type: 'post',

        beforeSend: function () {



        },

        success: function (response) {
            $.LoadingOverlay("hide");

            $("#registros").html(response);

            $('#modalRegistros').modal('show');
        },

        error: function (arg1, arg2, arg3) {
            console.log(arg3);
        }

    });
}


$('#cerrar').click(function () {
    $('#modalRegistros').modal('hide');

});
//#endregion
