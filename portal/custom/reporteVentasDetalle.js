var $ = jQuery;
var id = 0;
var nivel_usuario = parseInt($("#nivel_usuario").val());

$(document).ready(function () {

    $('.datepicker').datepicker({
        dateFormat: 'yy-mm-dd'
    });

    $('.select2').select2({
        tags: false,
        placeholder: "Seleccione un vendedor"
    });

});




function validar() {
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


$('#generar').click(function () {


    if (validar()) {

        var selectedValues = $("#clave").val();
        var formattedValues = "";
        if (selectedValues) {
            formattedValues = selectedValues.length > 1 ? selectedValues.join(',') : selectedValues[0];
        }

        var inicio = $("#inicio").val();
        var fin = $("#fin").val();
        var sucursal = $("#sucursal").val();
        var cliente = $("#cliente").val() ? $("#cliente").val() : 0;
        var usuario = $("#usuario").val();
        var pago = $("#pago").val();
        var categoria = $("#categoria").val();
        var agrupar = $("#agrupar").val();
        var clave = formattedValues;
        var tipo = $("#tipo_venta").val();

        window.open('servicios/reporteVentasDetalle.php?inicio=' + inicio + "&fin=" + fin + "&sucursal=" + sucursal + "&cliente=" + cliente + "&vendedor=" + usuario + "&pago=" + pago + "&categoria=" + categoria + "&agrupar=" + agrupar + "&producto=" + clave + "&tipo=" + tipo + "&nivel=" + nivel_usuario, '_blank');

    }

});


$('#pdf').click(function () {

    if (validar()) {

        var selectedValues = $("#clave").val();
        var formattedValues = "";
        if (selectedValues) {
            formattedValues = selectedValues.length > 1 ? selectedValues.join(',') : selectedValues[0];
        }

        var inicio = $("#inicio").val();
        var fin = $("#fin").val();
        var sucursal = $("#sucursal").val();
        var cliente = $("#cliente").val() ? $("#cliente").val() : 0;
        var usuario = $("#usuario").val();
        var pago = $("#pago").val();
        var categoria = $("#categoria").val();
        var agrupar = $("#agrupar").val();
        var clave = formattedValues;
        var tipo = $("#tipo_venta").val();

        window.open('reporteVentasDetallePDF.php?inicio=' + inicio + "&fin=" + fin + "&sucursal=" + sucursal + "&cliente=" + cliente + "&vendedor=" + usuario + "&pago=" + pago + "&categoria=" + categoria + "&agrupar=" + agrupar + "&producto=" + clave + "&tipo=" + tipo + "&nivel=" + nivel_usuario, '_blank');

    }

});


$("#buscar").click(function () {

    var selectedValues = $("#clave").val();
    var formattedValues = "";
    if (selectedValues) {
        formattedValues = selectedValues.length > 1 ? selectedValues.join(',') : selectedValues[0];
    }

    var parametros = {

        "inicio": $("#inicio").val(),
        "fin": $("#fin").val(),
        "sucursal": $("#sucursal").val(),
        "cliente": $("#cliente").val() ? $("#cliente").val() : 0,
        "usuario": $("#usuario").val(),
        "pago": $("#pago").val(),
        "categoria": $("#categoria").val(),
        "agrupar": $("#agrupar").val(),
        "clave": formattedValues,
        "tipo": $("#tipo_venta").val(),
        "nivel": nivel_usuario

    };

    $.ajax({
        data: parametros,

        url: 'servicios/getReporteVentasDetalle.php',

        type: 'get',

        beforeSend: function () {

        },

        success: function (response) {

            $("#tabla").html(response);

            $('#dtEmpresa').DataTable({
                responsive: true,
                ordering: true,
                pageLength: 10,
                order: [[0, 'desc']],
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

        },

        error: function (arg1, arg2, arg3) {
            console.log(arg3);
        }

    });
})
