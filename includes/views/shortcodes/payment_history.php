<div class="smartpay">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">Order</th>
                    <th scope="col">Date</th>
                    <th scope="col">Status</th>
                    <th scope="col">Total</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>

                <?php foreach ($payments as $index => $payment) : ?>

                <tr>
                    <th scope="row"><?php echo '#' . $payment->ID; ?></th>
                    <td><?php echo mysql2date('F j, Y', $payment->date); ?></td>
                    <td class="text-danger"><?php echo $payment->status_nicename; ?></td>
                    <td class="text-muted">
                        <strong class="text-danger">
                            <?php echo smartpay_amount_format($payment->amount, $payment->currency); ?>
                        </strong>
                    </td>
                    <td>
                        <a class="btn btn-primary" href="#">Pay</a>
                        <a class="btn btn-secondary" href="#">View</a>
                        <a class="btn btn-link text-danger" href="#">Cancel</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <!-- <tr>
                    <th scope="row">#164968</th>
                    <td>May 7, 2020</td>
                    <td class="text-danger">Pending</td>
                    <td class="text-muted"><strong class="text-danger">$50</strong> for 1 item</td>
                    <td>
                        <a class="btn btn-primary" href="#">Pay</a>
                        <a class="btn btn-secondary" href="#">View</a>
                        <a class="btn btn-link text-danger" href="#">Cancel</a>
                    </td>
                </tr>
                <tr>
                    <th scope="row">#164968</th>
                    <td>May 7, 2020</td>
                    <td class="text-success">Completed</td>
                    <td class="text-muted"><strong class="text-success">$200</strong> for 2 items</td>
                    <td>
                        <a class="btn btn-secondary" href="#">View</a>
                        <a class="btn btn-link text-danger" href="#">Cancel</a>
                    </td>
                </tr> -->
            </tbody>
        </table>
    </div>
</div>