/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
var $ = jQuery;
var id = 0;




function validar() {
    var retorno = true;

    if ($('#cliente').val().length < 8) {
        retorno = false;
        $('#cliente').css('background-color', '#ffdddd');
    }

    return retorno;

}




$('#generar').click(function () {

    window.open('servicios/reporteListaPrecios.php?fk_cliente=' + $("#fk_cliente").val(), '_blank');

});



//CLIENTES
$('#buscar').click(function () {

    $("#modalClientes").modal("show");

});


$(document).on('click', '.fp', function () {

    $("#fk_cliente").val(this.id);

    $("#" + this.id).children("td").each(function (index2) {

        switch (index2) {

            case 0:
                $("#cliente").val($(this).text().trim());
                break;
        }

    });

    $('#modalClientes').modal('hide');

});
