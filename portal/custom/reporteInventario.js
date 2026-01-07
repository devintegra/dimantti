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

    // if ($('#inicio').val() != '') {
    //     if ($('#fin').val().length < 8) {
    //         retorno = false;
    //         $('#fin').css('background-color', '#ffdddd');
    //     }
    // }

    // if ($('#fin').val() != '') {
    //     if ($('#inicio').val().length < 8) {
    //         retorno = false;
    //         $('#fin').css('background-color', '#ffdddd');
    //     }
    // }

    return retorno;

}


$('#generar').click(function () {


    if (validar()) {

        // var inicio = $("#inicio").val();
        // var fin = $("#fin").val();
        var sucursal = $("#sucursal").val();
        var categoria = $("#categoria").val();
        var clave = $("#clave").val() ? $("#clave").val() : "";

        window.open('servicios/reporteInventario.php?sucursal=' + sucursal + "&categoria=" + categoria + "&producto=" + clave, '_blank');

    }

});


$('#pdf').click(function () {

    if (validar()) {

        // var inicio = $("#inicio").val();
        // var fin = $("#fin").val();
        var sucursal = $("#sucursal").val();
        var categoria = $("#categoria").val();
        var marca = $("#marca").val();
        var clave = $("#clave").val() ? $("#clave").val() : "";

        window.open('reporteInventarioPDF.php?sucursal=' + sucursal + "&categoria=" + categoria + "&producto=" + clave, '_blank');

    }

});
