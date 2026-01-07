/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
var $ = jQuery;
var id = 0;

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

        var inicio = $("#inicio").val();
        var fin = $("#fin").val();
        var sucursal = $("#sucursal").val();
        var cliente = $("#cliente").val() ? $("#cliente").val() : 0;
        var usuario = $("#usuario").val();
        var pago = $("#pago").val();

        window.open('servicios/reporteVentas.php?inicio=' + inicio + "&fin=" + fin + "&sucursal=" + sucursal + "&cliente=" + cliente + "&vendedor=" + usuario + "&pago=" + pago, '_blank');

    }

});


$('#pdf').click(function () {

    if (validar()) {

        var inicio = $("#inicio").val();
        var fin = $("#fin").val();
        var sucursal = $("#sucursal").val();
        var cliente = $("#cliente").val() ? $("#cliente").val() : 0;
        var usuario = $("#usuario").val();
        var pago = $("#pago").val();

        window.open('reporteVentasPDF.php?inicio=' + inicio + "&fin=" + fin + "&sucursal=" + sucursal + "&cliente=" + cliente + "&vendedor=" + usuario + "&pago=" + pago, '_blank');

    }

});


$("#buscar").click(function () {

    var parametros = {

        "inicio": $("#inicio").val(),
        "fin": $("#fin").val(),
        "sucursal": $("#sucursal").val(),
        "cliente": $("#cliente").val() ? $("#cliente").val() : 0,
        "usuario": $("#usuario").val(),
        "pago": $("#pago").val(),

    };

    $.ajax({
        data: parametros,

        url: 'servicios/getReporteVentas.php',

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
