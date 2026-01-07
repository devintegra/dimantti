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

    var inicio = $("#inicio").val();
    var fin = $("#fin").val();
    var usuario = $("#usuario").val();

    window.open('servicios/reporteVentasProductos.php?inicio=' + inicio + "&fin=" + fin + "&usuario=" + usuario, '_blank');

});
