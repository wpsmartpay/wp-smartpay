<?php ?>

<div class="wrap">
    <h1><?php _e('Reports', 'smartpay'); ?></h1>
    <div class="smartpay">
        <div class="card">
            <div id="chart" class="p-3 mb-4"></div>

            <div class="border-top text-center mt-5">
                <div class="row no-gutters">
                    <div class="col-sm-3">
                        <div class="stats stats-highlight py-5">
                            <div class="label text-uppercase">Total Conversations</div>
                            <div class="metrics text-info">145</div>
                            <div class="previous text-muted">-1%</div>
                            <div class="info" data-toggle="tooltip" title="" data-original-title="Conversation touched (created, replied to, status changed, assigned), excluding spam and deleted">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3 border-left">
                        <div class="stats py-3">
                            <div class="label text-uppercase">New Conversations</div>
                            <div class="metrics">125</div>
                            <div class="previous text-muted">-1%</div>
                            <div class="info" data-toggle="tooltip" title="" data-original-title="Total amount of incoming conversations">
                            </div>
                        </div>
                        <div class="stats py-3 border-top">
                            <div class="label text-uppercase">Avg. Conversations Per Day</div>
                            <div class="metrics">22</div>
                            <div class="previous text-muted">-1%</div>
                            <div class="info" data-toggle="tooltip" title="" data-original-title="Average number of incoming conversations per day">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3 border-left">
                        <div class="stats py-3">
                            <div class="label text-uppercase">Resolutions</div>
                            <div class="metrics">110</div>
                            <div class="previous text-muted">-1%</div>
                            <div class="info" data-toggle="tooltip" title="" data-original-title="Total amount of conversations marked as closed.">
                            </div>
                        </div>
                        <div class="stats py-3 border-top">
                            <div class="label text-uppercase">Avg. Resolutions Per Day</div>
                            <div class="metrics">5</div>
                            <div class="previous text-muted">-1%</div>
                            <div class="info" data-toggle="tooltip" title="" data-original-title="Average number of conversations marked as closed per day.">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3 border-left">
                        <div class="stats py-3">
                            <div class="label text-uppercase">Customer Helped</div>
                            <div class="metrics">95</div>
                            <div class="previous text-muted">-1%</div>
                            <div class="info" data-toggle="tooltip" title="" data-original-title="Total amount of customers that sent in a conversations(one customer may have multiple conversations) excluding spam and deleted.">
                            </div>
                        </div>
                        <div class="stats py-3 border-top">
                            <div class="label text-uppercase">Avg. Customer Per Day</div>
                            <div class="metrics">5</div>
                            <div class="previous text-muted">-1%</div>
                            <div class="info" data-toggle="tooltip" title="" data-original-title="Average number of customers that sent in a conversations(one customer may have multiple conversations) excluding spam and deleted.">
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <script>
        var options = {
            series: [{
                name: 'Product Purchase',
                data: [14, 4, 4, 4, 24, 4, 34, 4, 4]
            }, {
                name: 'Form Payment',
                data: [4, 4, 4, 4, 4, 4, 4, 4, 4]
            }],
            chart: {
                type: 'bar',
                height: 350
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '20%'
                },
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent']
            },
            xaxis: {
                categories: ['1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1'],
            },
            yaxis: {
                title: {
                    text: '$ (revenue)'
                }
            },
            fill: {
                opacity: 1
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return `$${val}`
                    }
                }
            }
        };

        var chart = new ApexCharts(document.querySelector("#chart"), options);
        chart.render();
    </script>
</div>