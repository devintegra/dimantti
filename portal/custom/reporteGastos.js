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
        var gasto = $("#gasto").val();
        var usuario = $("#usuario").val();

        window.open('servicios/reporteGastos.php?inicio=' + inicio + "&fin=" + fin + "&sucursal=" + sucursal + "&gasto=" + gasto + "&usuario=" + usuario, '_blank');

    }

});


$('#pdf').click(function () {


    if (validar()) {

        var inicio = $("#inicio").val();
        var fin = $("#fin").val();
        var sucursal = $("#sucursal").val();
        var gasto = $("#gasto").val();
        var usuario = $("#usuario").val();

        window.open('reporteGastosPDF.php?inicio=' + inicio + "&fin=" + fin + "&sucursal=" + sucursal + "&gasto=" + gasto + "&usuario=" + usuario, '_blank');

    }

});
