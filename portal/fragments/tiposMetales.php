<?php

$mysqli->next_result();
if (!$rsp_get_metales = $mysqli->query("CALL sp_get_metales()")) {
    echo "Lo sentimos, esta aplicación está experimentando problemas.";
    exit;
}

?>

<style>
    #cardHeader {
        background-image: linear-gradient(120deg, #84fab0 0%, #8fd3f4 100%);
        padding: 18px 24px;
        border-radius: 15px 15px 0 0;
        color: #000000;
    }

    #cardHeader i {
        color: #000000;
    }

    #btnAdd {
        width: 40px;
        height: 40px;
        border-radius: 50px;
        background-color: #4DA768;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    #btnAdd i {
        color: #FFFFFF;
    }

    #guardarMetales {
        padding: 12px 18px;
        border-radius: 35px;
        color: #fff;
        background: #7ad17a;
        border: none;
    }
</style>

<div class="row mt-4">
    <div class="card card-rounded" style="padding: 0;">
        <div class="card-body" style="padding: 0;">
            <div id="cardHeader" class="d-flex justify-content-between align-items-center">
                <h3 class="fs-5 fw-bold mb-0 d-flex align-items-center gap-2"> <i class="bx bxs-diamond fs-4"></i> Precio actual de cada metal</h3>
                <a href="agregarMetal.php" id="btnAdd"><i class="bx bx-plus fs-2"></i></a>
            </div>

            <div>
                <div class="table-responsive overflow-hidden">
                    <table id='dtMetales' class='table table-striped text-center'>
                        <tbody>
                            <?php
                            while ($row = $rsp_get_metales->fetch_assoc()) {
                                echo <<<HTML
                                    <tr data-id="$row[pk_metal]">
                                        <td>$row[nombre]</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <input type="number" class="form-control input-precio" value="$row[precio]" placeholder="0.00">
                                                <span>por gr</span>
                                            </div>
                                        </td>
                                    </tr>
                                HTML;
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <div class="w-100 d-flex justify-content-center align-items-center">
                    <button id="guardarMetales" type="button" class="btn btn-success btn-lg mx-2">Actualizar precios</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function getMetales() {

        let data = [];

        $('#dtMetales tbody tr').each(function() {

            let id = $(this).attr('data-id');
            let precio = $(this).find('td:eq(1) input').val();

            if (precio && precio > 0) {
                data.push({
                    "id": id,
                    "precio": precio
                })
            }

        });

        return data;

    }

    $(document).on('click', '#guardarMetales', function() {

        var parametros = {
            "metales": getMetales()
        };

        $.ajax({

            data: JSON.stringify(parametros),

            url: 'servicios/editarMetalesPrecio.php',

            type: 'post',

            dataType: 'json',

            contentType: "application/json; charset=utf-8",

            beforeSend: function() {

            },

            success: function(response) {

                if (response.codigo == 200) {

                    swal("Éxito", "Los precios se actualizaron correctamente", "success").then(function() {
                        location.reload();
                    });

                } else {

                    swal("Error", response.descripcion, "error").then(function() {
                        location.reload();
                    });

                }
            },

            error: function(arg1, arg2, arg3) {
                console.log(arg3);
            }
        });

    });
</script>
