var $ = jQuery;
var id = 0;
var subcategorias = [];
var categorias = [];
var marcas = [];

$(document).ready(function () {

});



$("#agrupar").change(function () {

    if (this.value == 1) {
        $("#filterCategorias").addClass('d-none');
    } else if (this.value == 2) {
        $("#filterCategorias").removeClass('d-none');
    } else if (this.value == 3) {
        $("#filterCategorias").addClass('d-none');
    } else {
        $("#filterCategorias").addClass('d-none');
    }
});


$(document).on("change", ".form-check-input", function () {

    var clase = $(this).attr('class').split(" ")[1];

    if (this.checked) {
        if (this.value == 0) {
            $('.' + clase).prop("checked", true);
        }
    } else {
        if (this.value == 0) {
            $('.' + clase).prop("checked", false);
        }
    }

})


function getSeleccionados() {

    categorias = [];

    $(document).find('.chk-categorias:checked').each(function () {
        categorias.push($(this).val());
    });

}


function validar() {

    var retorno = true;

    if ($('#agrupar').val() == 0) {
        retorno = false;
        $('#agrupar').css('background-color', '#ffdddd');
    }

    return retorno;

}


$('#generar').click(function () {

    if (validar()) {

        getSeleccionados();

        if ($("#agrupar").val() == 2) {
            var filtros = categorias.join(',');
        }

        var agrupar = $("#agrupar").val();

        window.open('servicios/reportePrecios.php?agrupar=' + agrupar + "&filtros=" + filtros, '_blank');

    }

});
