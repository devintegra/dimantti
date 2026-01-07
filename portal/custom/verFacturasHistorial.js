var $ = jQuery;


$(document).ready(function () {

    getFacturas();

});


//OBTENER FACTURAS
function getFacturas() {

    var parametros = {
        "fk_sucursal": $("#sucursal").val(),
        "fk_usuario": $("#fk_usuario").val(),
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
                "url": "servicios/getFacturasHistorial.php",
                "type": "GET",
                "data": function (d) {
                    $.extend(d, parametros);

                    $('#dtEmpresa tfoot th input').each(function (index) {
                        d['columns'][index]['search']['value'] = $(this).val();
                    });
                }
            },
            columns: [
                { data: 'fecha' },
                { data: 'venta' },
                { data: 'cliente' },
                { data: 'forma_pago' },
                { data: 'metodo_pago' },
                { data: 'estatus' },
                { data: 'acciones' }
            ],
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
