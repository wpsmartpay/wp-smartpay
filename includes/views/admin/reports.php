<?php

$total_days = cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y'));

$report = array_fill(1, $total_days, ['product_purchase' => 0, 'form_payment' => 0]);

$report_data = SmartPay()->admin->report->get_report_data();

foreach ($report_data as $index => $data) {
    if (!$data->completed_date) continue;

    $date = date('j', strtotime($data->completed_date));
    $report[$date][$data->payment_type] += $data->amount ?? 0;
}

$product_purchases = array_column($report, 'product_purchase');
$form_payments = array_column($report, 'form_payment');
?>

<div class="wrap">
    <h1><?php _e('Reports', 'smartpay'); ?></h1>
    <div class="smartpay">
        <div class="card">
            <div id="revenueReport" class="p-3 mb-4"></div>

            <div class="border-top text-center mt-5">
                <div class="row no-gutters">
                    <div class="col-sm-4">
                        <div class="stats stats-highlight py-5">
                            <div class="label text-uppercase">
                                <?php _e('Total Earning', 'smartpay') ?>
                            </div>
                            <div class="metrics text-info">
                                <?php echo array_sum($product_purchases) + array_sum($form_payments); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4 border-left">
                        <div class="stats py-3">
                            <div class="label text-uppercase"><?php _e('Total Product Purchase', 'smartpay') ?></div>
                            <div class="metrics"><?php echo array_sum($product_purchases); ?></div>
                        </div>
                        <div class="stats py-3 border-top">
                            <div class="label text-uppercase"><?php _e('Avg. Product Purchase', 'smartpay') ?></div>
                            <div class="metrics"><?php echo (0 < array_sum($product_purchases)) ? number_format(array_sum($product_purchases) / count($form_payments)) : 0; ?></div>
                        </div>
                    </div>
                    <div class="col-sm-4 border-left">
                        <div class="stats py-3">
                            <div class="label text-uppercase"><?php _e('Total Form Payment', 'smartpay') ?></div>
                            <div class="metrics"><?php echo array_sum($form_payments); ?></div>
                        </div>
                        <div class="stats py-3 border-top">
                            <div class="label text-uppercase"><?php _e('Avg. Form Payment', 'smartpay') ?></div>
                            <div class="metrics"><?php echo (0 < array_sum($form_payments)) ? number_format(array_sum($form_payments) / count($form_payments)) : 0; ?></div>
                        </div>
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
            name: "<?php echo __('Product Purchase', 'smartpay'); ?>",
            data: <?php echo json_encode(array_column($report, 'product_purchase')) ?>
        }, {
            name: "<?php echo __('Form Payment', 'smartpay'); ?>",
            data: <?php echo json_encode(array_column($report, 'form_payment')) ?>
        }],
        chart: {
            type: 'bar',
            height: 350
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '50%'
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
            categories: <?php echo json_encode(array_keys($report)) ?>,
        },
        yaxis: {
            title: {
                text: 'Revenue'
            }
        },
        fill: {
            opacity: 1
        },
        tooltip: {
            y: {
                formatter: function(val) {
                    return `${val}`
                }
            }
        }
    };

    var chart = new ApexCharts(document.querySelector("#revenueReport"), options);
    chart.render();
</script>
</div>