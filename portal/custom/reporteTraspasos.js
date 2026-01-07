var $ = jQuery;
var id = 0;

$(document).ready(function () {

    $('.datepicker').datepicker({
        dateFormat: 'yy-mm-dd'
    });

    $('.select2').select2({
        tags: false,
        placeholder: "Escanea el código de barras"
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
        var origen = $("#origen").val();
        var destino = $("#destino").val();
        var clave = $("#clave").val() ? $("#clave").val() : "";

        window.open('servicios/reporteTraspasos.php?inicio=' + inicio + "&fin=" + fin + "&origen=" + origen + "&destino=" + destino + "&producto=" + clave, '_blank');

    }

});


$('#pdf').click(function () {


    if (validar()) {

        var inicio = $("#inicio").val();
        var fin = $("#fin").val();
        var origen = $("#origen").val();
        var destino = $("#destino").val();
        var clave = $("#clave").val() ? $("#clave").val() : "";

        window.open('reporteTraspasosPDF.php?inicio=' + inicio + "&fin=" + fin + "&origen=" + origen + "&destino=" + destino + "&producto=" + clave, '_blank');

    }

});
