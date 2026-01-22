var $ = jQuery;
var id = 0;

$(document).ready(function () {

    getVentasDeferred();

});



//OBTENER VENTAS
//#region
function getVentasDeferred() {

    var parametros = {
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
                "url": "servicios/getVentasMovil.php",
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
                { data: 'cliente' },
                { data: 'ruta' },
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
