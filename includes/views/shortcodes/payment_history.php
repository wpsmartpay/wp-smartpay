<div class="smartpay">
    <?php if (!is_array($payments) || !count($payments)) : ?>
        <div class="card">
            <div class="card-body py-5">
                <p class="text-info  m-0 text-center">You don't have any payment yet.</p>
            </div>
        </div>
    <?php else : ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th scope="col">Order</th>
                        <th scope="col">Date</th>
                        <th scope="col">Item(s)</th>
                        <th scope="col">Status</th>
                        <th scope="col">Total</th>
                        <!-- <th scope="col">Actions</th> -->
                    </tr>
                </thead>
                <tbody>

                    <?php foreach ($payments as $index => $payment) : ?>
                        <tr>
                            <th scope="row"><?php echo '#' . $payment->ID; ?></th>
                            <td><?php echo mysql2date('F j, Y', $payment->date); ?></td>
                            <td><?php echo 'Items'; ?></td>
                            <td class="<?php echo 'publish' == $payment->status ? 'text-success' : 'text-danger'; ?>">
                                <?php echo $payment->status_nicename; ?></td>
                            <td class="text-muted">
                                <strong class="<?php echo 'publish' == $payment->status ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo smartpay_amount_format($payment->amount, $payment->currency); ?>
                                </strong>
                            </td>
                            <!-- <td>
                        <a class="btn btn-primary" href="#">Pay</a>
                        <a class="btn btn-secondary" href="#">View</a>
                        <a class="btn btn-link text-danger" href="#">Cancel</a>
                    </td> -->
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif ?>
</div>