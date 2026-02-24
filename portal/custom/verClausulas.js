var $ = jQuery;
var clausulasOrden = [];


$(document).ready(function (e) {

    $("#dtEmpresa").on("click", ".subirOrden", function (e) {
        e.preventDefault();
        var fila = $(this).closest("tr");
        fila.insertBefore(fila.prev());
        actualizarOrden();
    });

    $("#dtEmpresa").on("click", ".bajarOrden", function (e) {
        e.preventDefault();
        var fila = $(this).closest("tr");
        fila.insertAfter(fila.next());
        actualizarOrden();
    });

    function actualizarOrden() {
        $("#dtEmpresa tbody tr").each(function (index) {
            $(this).find("td:first p").text(index + 1); // Actualizar el número de orden
        });
    }
});


function getClausulas() {

    var data = [];

    $("#dtEmpresa tbody tr").each(function () {

        var pk_clausula = parseInt($(this).attr('data-id'));
        var orden = parseInt($(this).find('td').eq(0).find('p').text());

        var myObj = { 'pk_clausula': pk_clausula, 'orden': orden };

        data.push(myObj);

    });

    return data;

}



$(document).on('click', '#guardarOrden', function () {

    var parametros = {
        "clausulas": getClausulas(),
    };

    $.ajax({

        data: JSON.stringify(parametros),

        url: 'servicios/editarClausulaOrden.php',

        type: 'post',

        dataType: 'json',

        contentType: "application/json; charset=utf-8",

        beforeSend: function () {

        },

        success: function (response) {
            location.reload();
        },

        error: function (arg1, arg2, arg3) {
            console.log(arg3);
        }
    });

})
