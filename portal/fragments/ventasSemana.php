<?php

?>

<div class="col-12 col-lg-4 col-lg-12 grid-margin stretch-card">
    <div class="card card-rounded" style="border-top: 4px solid #f69100;">
        <div class="card-body">
            <div class="d-sm-flex justify-content-between">
                <h4 class="card-title">Ventas de la semana</h4>
                <div id="performance-line-legend"></div>
            </div>
            <div class="chartjs-wrapper mt-5">
                <canvas id="ventasSemana"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
    var $ = jQuery;
    var pk_ventas = 0;

    $(document).ready(function() {

        if (!!document.getElementById('ventasSemana') == true) {
            var grafica = document.getElementById('ventasSemana').getContext('2d');
            var grafica2 = document.getElementById('ventasSemana').getContext('2d');
            var datosSemanaActual = [];
            var datosSemanaPasada = [];
            var array = [];
            var diasSemana = [];

            var saleGradientBg = grafica.createLinearGradient(5, 0, 5, 100);
            saleGradientBg.addColorStop(0, 'rgba(0, 0, 0, 0.18)');
            saleGradientBg.addColorStop(1, 'rgba(0, 0, 0, 0.02)');
            var saleGradientBg2 = grafica2.createLinearGradient(100, 0, 50, 150);
            saleGradientBg2.addColorStop(0, 'rgba(181,139,41,0.19)');
            saleGradientBg2.addColorStop(1, 'rgb(181,139,41,0.03)');

            $.ajax({
                type: 'GET',
                url: 'https://posmovil.integracontrol.online/portal/servicios/getGrafica.php',
                dataType: 'json',

                beforeSend: function() {

                },

                success: function(data) {
                    $.each(data.objList, function(i, element) {
                        array.push(element)
                    });

                    const sortedArray = array.sort((a, b) => moment(a.fecha, "YYYY-MM-DD").unix() - moment(b.fecha, "YYYY-MM-DD").unix()); //Ordenar arreglo por fecha

                    //Agregar todos los totales a sus respectivos arreglos (semanaActual, semanaPasada)
                    array.forEach(element => {
                        if (element.tipo == 1) {
                            datosSemanaActual.push(element.total);
                            diasSemana.push(element.dia); //Arreglo de etiquetas
                        } else if (element.tipo == 2) {
                            datosSemanaPasada.push(element.total);
                        }
                    })

                    var ventasData = {
                        labels: diasSemana,
                        datasets: [{
                            label: 'Esta semana',
                            data: datosSemanaActual,
                            backgroundColor: saleGradientBg,
                            borderColor: ['#000'],
                            borderWidth: 1.5,
                            fill: true,
                            pointBorderWidth: 1,
                            pointRadius: [4, 4, 4, 4, 4, 4, 4],
                            pointHoverRadius: [2, 2, 2, 2, 2, 2, 2],
                            pointBackgroundColor: ['#000', '#000', '#000', '#000', '#000', '#000', '#000'],
                            pointBorderColor: ['#fff', '#fff', '#fff', '#fff', '#fff', '#fff', '#fff'],
                        }, {
                            label: 'Última semana',
                            data: datosSemanaPasada,
                            backgroundColor: saleGradientBg2,
                            borderColor: ['#b58b29'],
                            borderWidth: 1.5,
                            fill: true, // 3: no fill
                            pointBorderWidth: 1,
                            pointRadius: [4, 4, 4, 4, 4, 4, 4],
                            pointHoverRadius: [2, 2, 2, 2, 2, 2, 2],
                            pointBackgroundColor: ['#b58b29', '#b58b29', '#b58b29', '#b58b29', '#b58b29', '#b58b29', '#b58b29'],
                            pointBorderColor: ['#fff', '#fff', '#fff', '#fff', '#fff', '#fff', '#fff'],
                        }]

                    };

                    var chartOptions = {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            yAxes: [{
                                gridLines: {
                                    display: true,
                                    drawBorder: false,
                                    color: "#F0F0F0",
                                    zeroLineColor: '#F0F0F0',
                                },
                                ticks: {
                                    beginAtZero: false,
                                    autoSkip: true,
                                    maxTicksLimit: 8,
                                    fontSize: 10,
                                    color: "#6B778C"
                                }
                            }],
                            xAxes: [{
                                gridLines: {
                                    display: false,
                                    drawBorder: false,
                                },
                                ticks: {
                                    beginAtZero: false,
                                    autoSkip: true,
                                    maxTicksLimit: 7,
                                    fontSize: 10,
                                    color: "#6B778C"
                                }
                            }],
                        },
                        legend: false,
                        legendCallback: function(chart) {
                            var text = [];
                            text.push('<div class="chartjs-legend"><ul>');
                            for (var i = 0; i < chart.data.datasets.length; i++) {
                                text.push('<li>');
                                text.push('<span style="background-color:' + chart.data.datasets[i].borderColor + '">' + '</span>');
                                text.push(chart.data.datasets[i].label);
                                text.push('</li>');
                            }
                            text.push('</ul></div>');
                            return text.join("");
                        },

                        elements: {
                            line: {
                                tension: 0.4,
                            }
                        },
                        tooltips: {
                            backgroundColor: 'rgba(0, 0, 0, .2)',
                        }
                    };

                    var myChart = new Chart(grafica, {
                        type: 'line',
                        data: ventasData,
                        options: chartOptions
                    });

                    document.getElementById('performance-line-legend').innerHTML = myChart.generateLegend();

                },

                error: function() {

                }
            });
        }

    });
</script>
