var $ = jQuery;
const prevBtns = document.querySelectorAll(".btn-prev");
const nextBtns = document.querySelectorAll(".btn-next");
const progress = document.getElementById("progress");
const formSteps = document.querySelectorAll(".form-step");
const progressSteps = document.querySelectorAll(".progress-step");
let formStepsNum = 0;
var nentrada = 0;
var ph = "";


$(document).ready(function () {

    $("#telefono").mask("(999) 999-9999");

    if ($("#sucursal").val() == 0) {
        $('#modalEmpresa').modal('show');
    }

});



//SUCURSAL
//#region
$(document).on('click', '.empresa', function () {

    $("#" + this.id).addClass("tdsel");
    $('#modalEmpresa').modal('hide');
    var le = $("#s-" + this.id).text();

    $("#lasucursal").text(le);
    $("#sucursal").val(this.id);

    $("#tablaEmpresa").html("");

});
//#endregion




//CLIENTES
//#region
$('#buscarc').click(function () {
    $('#modalClientes').modal('show');
});


$('.cliente').click(function () {

    var elid = this.id;

    elid = elid.split("-");
    elid = elid[1];

    $("#cliente").val(elid);


    $(this).children("td").each(function (index2) {

        switch (index2) {

            case 0:
                $("#clientenombre").val($(this).text().trim());
                break;

            case 1:
                $("#telefono").val($(this).text().trim());
                break;

            case 2:
                $("#correo").val($(this).text().trim());
                break;

        }

    });

    $('#modalClientes').modal('hide');

});


$('#cerrarm').click(function () {
    $('#modalClientes').modal('hide');
});
//#endregion




//GUARDAR
//#region
function validarGuardado() {

    var retorno = true;

    if ($('#clientenombre').val().length < 1) {
        retorno = false;
        $('#clientenombre').css('background-color', '#ffdddd');
    }

    if ($('#telefono').val().length < 10) {
        retorno = false;
        $('#telefono').css('background-color', '#ffdddd');
    }

    if ($("#tipo_pago").val() > 0) {
        if ($('#montopago').val() < 0) {
            retorno = false;
            $('#montopago').css('background-color', '#ffdddd');
        }
    }

    if ($("#tipo_pago").val() == 0) {
        $('#montopago').val("0");
    }

    if ($('#fk_categoria').val() == 0) {
        retorno = false;
        $('#fk_categoria').css('background-color', '#ffdddd');
    }

    if ($('#observaciones').val().length == 0) {
        retorno = false;
        $('#observaciones').css('background-color', '#ffdddd');
    }

    return retorno;

}


$('#guardar').click(function () {

    if (validarGuardado()) {

        $("#guardar").attr("disabled", "disabled");

        $.LoadingOverlay("show", {
            color: "rgba(255, 255, 255, 0)"
        });

        var parametros = {
            "fk_sucursal": $("#sucursal").val(),
            "fk_usuario": $("#usuario").val(),
            "fk_cliente": $("#cliente").val(),
            "cliente_nombre": $("#clientenombre").val(),
            "cliente_telefono": $("#telefono").val(),
            "cliente_correo": $("#correo").val(),
            "monto_pago": $('#montopago').val().replace(/,/g, ""),
            "asignacion": "joyero",
            "fk_pago": $("#tipo_pago").val(),
            "ns": $('#ns').val(),
            "fk_categoria": $('#fk_categoria').val(),
            "marca": $('#marca').val(),
            "modelo": $('#modelo').val(),
            "valor_estimado": $('#valor_estimado').val().replace(/,/g, ""),
            "observaciones": $('#observaciones').val(),
        };

        $.ajax({

            data: JSON.stringify(parametros),

            url: 'servicios/agregarOrden.php',

            type: 'post',

            dataType: 'json',

            contentType: "application/json; charset=utf-8",

            beforeSend: function () {

            },

            success: function (response) {


                if (response.codigo == 200) {

                    pk_orden = response.objList.nentrada;
                    ph = response.objList.ph;

                    guardarImagenes(pk_orden, ph);

                }

                else {
                    swal("Error", response.descripcion, "error").then(function () {
                        location.reload();
                    });
                }

            },

            error: function (arg1, arg2, arg3) {
                console.log(arg3);
            }
        });
    }

});


function guardarImagenes(pk_orden, ph) {

    var formData = new FormData(document.getElementById("formuploadajax"));
    formData.append("pk_orden", pk_orden);
    formData.append("usuario", $("#usuario").val());

    $.ajax({
        url: "servicios/agregarImagenesOrden.php",
        type: "post",
        dataType: "html",
        data: formData,
        cache: false,
        contentType: false,
        processData: false,

        beforeSend: function () {

        },

        success: function (response) {

            $.LoadingOverlay("hide");

            response = JSON.parse(response);

            if (response.codigo == 200) {

                $(location).attr("href", "firmaMovil.php?id=" + pk_orden + "&ph=" + ph);

            }
            else {

                swal("Error", "Hubo un error, porfavor vuelva a intentarlo", "error").then(function () {
                    location.reload();
                });

            }

        },

        error: function (arg1, arg2, arg3) {
            console.log(arg3);
        }

    });
}
//#endregion




//EXTRAS
//#region
$('#ver_ordenes').click(function () {
    $(location).attr("href", "verOrdenes.php");
});


$('#nueva').click(function () {
    $(location).attr("href", "agregarOrden.php");
});


$('#inicio').click(function () {
    $(location).attr("href", "index.php");
});


$('#fotos').click(function () {
    $(location).attr("href", "fotos.php?id=" + ph);
});


$('#tipo_pago').change(function () {

    var valor = this.value;

    if (valor > 0) {
        $("#montopago").removeAttr("disabled");
    }
    else {
        $("#montopago").attr("disabled", "disabled");
    }

});


$(document).on("input", "#montopago, #valor_estimado", function () {
    $(this).val(currencyMX($(this).val()));
});
//#endregion




//PREVISUALIZAR IMAGENES
//#region
document.getElementById("imagen_uno").onchange = function (e) {
    let reader = new FileReader();

    reader.onload = function () {
        $("#label_img_uno").css('background-image', 'url(' + reader.result + ')');
    };
    reader.readAsDataURL(e.target.files[0]);
}

//Imagen 2
document.getElementById("imagen_dos").onchange = function (e) {
    let reader = new FileReader();

    reader.onload = function () {
        $("#label_img_dos").css('background-image', 'url(' + reader.result + ')');
    };
    reader.readAsDataURL(e.target.files[0]);
}

//Imagen 3
document.getElementById("imagen_tres").onchange = function (e) {
    let reader = new FileReader();

    reader.onload = function () {
        $("#label_img_tres").css('background-image', 'url(' + reader.result + ')');
    };
    reader.readAsDataURL(e.target.files[0]);
}

//Imagen 4
document.getElementById("imagen_cuatro").onchange = function (e) {
    let reader = new FileReader();

    reader.onload = function () {
        $("#label_img_cuatro").css('background-image', 'url(' + reader.result + ')');
    };
    reader.readAsDataURL(e.target.files[0]);
}
//#endregion




//WIZARD
//#region
function validarPaso1() {
    var retorno = true;

    if ($('#clientenombre').val().length < 1) {
        retorno = false;
        $('#clientenombre').css('background-color', '#ffdddd');
    } else {
        $('#clientenombre').css('background-color', '#ffffff');
    }

    if ($('#telefono').val().length < 1) {
        retorno = false;
        $('#telefono').css('background-color', '#ffdddd');
    } else {
        $('#telefono').css('background-color', '#ffffff');
    }

    if ($("#montopago").val() < 0) {
        retorno = false;
        $("#montopago").css('background-color', '#ffdddd');
    } else {
        $('#montopago').css('background-color', '#ffffff');
    }

    if (retorno == true) {
        formStepsNum++;
        updateFormSteps();
        updateProgressbar();
    }

    return retorno;
}


function validarPaso2() {
    var retorno = true;

    if ($('#ns').val().length < 1) {
        retorno = false;
        $('#ns').css('background-color', '#ffdddd');
    } else {
        $('#ns').css('background-color', '#ffffff');
    }

    if ($('#fk_categoria').val() == 0) {
        retorno = false;
        $('#fk_categoria').css('background-color', '#ffdddd');
    } else {
        $('#fk_categoria').css('background-color', '#ffffff');
    }

    if ($('#marca').val().length < 1) {
        retorno = false;
        $('#marca').css('background-color', '#ffdddd');
    } else {
        $('#marca').css('background-color', '#ffffff');
    }

    if ($("#modelo").val().length < 1) {
        retorno = false;
        $("#modelo").css('background-color', '#ffdddd');
    } else {
        $('#modelo').css('background-color', '#ffffff');
    }

    if ($("#observaciones").val().length == 0) {
        retorno = false;
        $("#observaciones").css('background-color', '#ffdddd');
    } else {
        $('#observaciones').css('background-color', '#ffffff');
    }

    if (retorno == true) {
        formStepsNum++;
        updateFormSteps();
        updateProgressbar();
    }

    return retorno;
}


prevBtns.forEach((btn) => {
    btn.addEventListener("click", () => {
        formStepsNum--;
        updateFormSteps();
        updateProgressbar();
    });
});


function updateFormSteps() {
    formSteps.forEach((formStep) => {
        formStep.classList.contains("form-step-active") &&
            formStep.classList.remove("form-step-active");
    });

    formSteps[formStepsNum].classList.add("form-step-active");
}


function updateProgressbar() {
    progressSteps.forEach((progressStep, idx) => {
        if (idx < formStepsNum + 1) {
            progressStep.classList.add("progress-step-active");
        } else {
            progressStep.classList.remove("progress-step-active");
        }
    });

    const progressActive = document.querySelectorAll(".progress-step-active");

    progress.style.width =
        ((progressActive.length - 1) / (progressSteps.length - 1)) * 100 + "%";
}
//#endregion
