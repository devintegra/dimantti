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


    if ($('#inicio').val().length < 8) {
        retorno = false;
        $('#inicio').css('background-color', '#ffdddd');
    }


    if ($('#fin').val().length < 8) {
        retorno = false;
        $('#fin').css('background-color', '#ffdddd');
    }

    if ($('#tipo').val() == 0) {
        retorno = false;
        $('#tipo').css('background-color', '#ffdddd');
    }
    return retorno;



}




$('#generar').click(function () {


    if (validar()) {

        window.open('servicios/reporteIngresosEgresos.php?inicio=' + $("#inicio").val() + "&fin=" + $("#fin").val() + "&sucursal=" + $("#sucursal").val() + "&tipo=" + $("#tipo").val(), '_blank');

    }



});




