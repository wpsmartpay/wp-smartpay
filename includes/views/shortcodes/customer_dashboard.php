<style>
    .profile img {
        height: 90px;
        width: 90px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    }

    .profile h3 {
        font-size: 20px;
    }

    .product--header {
        cursor: pointer;
    }

    table td,
    table th {
        border: none;
    }
</style>

<div class="smartpay">
    <div class="card border-light mb-3">
        <div class="card-body py-5">
            <div class="d-flex justify-content-center flex-column">

                <div class="profile d-flex justify-content-center flex-column">
                    <div class="mx-auto">
                        <img class="rounded-circle" src="http://smartpay.test/wp-content/uploads/2020/06/profile.jpg" alt="">
                    </div>
                    <div class="text-center">
                        <h3 class="mt-4 mb-2"><?php echo ($customer->first_name ?? '') . ' ' . ($customer->last_name ?? ''); ?></h3>
                        <p class="my-0" class=""><?php echo $customer->email ?? ''; ?></p>
                    </div>
                </div>

                <div class="mt-5">
                    <nav class="nav justify-content-center nav-pills mb-4" role="tablist">
                        <a class="nav-link mx-2 px-4 active" data-toggle="pill" href="#payments" role="tab"><?php _e('Payments', 'smartpay'); ?></a>

                        <a class="nav-link mx-2 px-4" data-toggle="pill" href="#downloads" role="tab"><?php _e('Downloads', 'smartpay'); ?></a>

                        <a class="nav-link mx-2 px-4" data-toggle="pill" href="#profile" role="tab"><?php _e('Profile', 'smartpay'); ?></a>
                    </nav>
                    <div class="d-flex justify-content-center">
                        <div class="col-11">
                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="payments" role="tabpanel">
                                    <?php if (!is_array($customer->all_payments()) || !count($customer->all_payments())) : ?>
                                        <div class="card">
                                            <div class="card-body py-5">
                                                <p class="text-info  m-0 text-center"><?php _e('You don\'t have any payment yet.', 'smartpay'); ?></p>
                                            </div>
                                        </div>
                                    <?php else : ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th scope="col"><?php _e('Order ID', 'smartpay'); ?></th>
                                                        <th scope="col"><?php _e('Date', 'smartpay'); ?></th>
                                                        <th scope="col"><?php _e('Item', 'smartpay'); ?>(s)</th>
                                                        <th scope="col"><?php _e('Status', 'smartpay'); ?></th>
                                                        <th scope="col"><?php _e('Amount', 'smartpay'); ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody>

                                                    <?php foreach ($customer->all_payments() as $index => $payment) : ?>
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
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif ?>
                                </div>

                                <div class="tab-pane fade" id="downloads" role="tabpanel">
                                    <?php

                                    use SmartPay\Products\SmartPay_Product;

                                    $product = new SmartPay_Product(11);
                                    ?>
                                    <?php foreach (range(1, 2) as $i) : ?>

                                        <div class="border mb-3 product">
                                            <div class="p-3  product--header">
                                                <div class="row" data-toggle="collapse" data-target="#collapse<?php echo $i; ?>">
                                                    <div class="col-sm-2 product--image">
                                                        <img src="<?php echo $product->image; ?>" class="border" alt="">
                                                    </div>
                                                    <div class="col-sm-7">
                                                        <h5 class="mt-0"><?php echo $product->title; ?></h5>
                                                        <p><strong>Variation:</strong> Pro</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="p-3 bg-light collapse show" id="collapse<?php echo $i; ?>">
                                                <p>Files</p>
                                                <ul class="list-group">
                                                    <?php foreach (range(1, 5) as $i) : ?>
                                                        <li class="list-group-item p-2">
                                                            <div class="d-flex align-items-center flex-wrap">
                                                                <div>
                                                                    <img src="<?php echo $product->image; ?>" style="height: 40px;" alt="">
                                                                </div>
                                                                <div class="ml-3">
                                                                    <p class="m-0"><?php echo $product->title; ?></p>
                                                                    <p class="text-muted m-0"><small>Size: 10kb</small></p>
                                                                </div>
                                                                <div class="ml-auto">
                                                                    <button class="btn btn-sm btn-primary mr-1"><?php _e('Download', 'smartpay'); ?></button>
                                                                </div>
                                                            </div>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="tab-pane fade" id="profile" role="tabpanel">
                                    <form class="my-5">
                                        <div class="form-row mb-2">
                                            <div class="form-group col">
                                                <label for="first_name"><?php _e('First Name', 'smartpay'); ?></label>
                                                <input type="text" name="first_name" id="first_name" class="form-control" value="<?php echo $customer->first_name ?? '' ?>" placeholder="<?php _e('First Name', 'smartpay'); ?>">
                                            </div>
                                            <div class="form-group col">
                                                <label for="last_name"><?php _e('Last Name', 'smartpay'); ?></label>
                                                <input type="text" name="last_name" id="last_name" class="form-control" value="<?php echo $customer->last_name ?? '' ?>" placeholder="<?php _e('Last Name', 'smartpay'); ?>">
                                            </div>
                                        </div>

                                        <div class="form-group mb-4">
                                            <label for="email"><?php _e('Email', 'smartpay'); ?></label>
                                            <input type="email" name="email" id="email" class="form-control" value="<?php echo $customer->email ?? '' ?>" placeholder=" <?php _e('Email', 'smartpay'); ?>">
                                        </div>

                                        <div class="form-group mb-4">
                                            <label><?php _e('Username', 'smartpay'); ?></label>
                                            <input type="text" class="form-control" placeholder="<?php _e('Username', 'smartpay'); ?>" value="<?php echo $customer->wp_user->user_login ?? '' ?>" disabled>
                                        </div>

                                        <div class="form-row mb-2">
                                            <div class="form-group col-6">
                                                <label><?php _e('Password', 'smartpay'); ?></label>
                                                <input type="password" class="form-control" placeholder="<?php _e('Password', 'smartpay'); ?>">
                                                <small class="form-text text-muted"><?php _e('If you don\'t want to change password, then ignore this fields.', 'smartpay'); ?></small>
                                            </div>
                                            <div class="form-group col-6">
                                                <label><?php _e('Confirm Password', 'smartpay'); ?></label>
                                                <input type="password" class="form-control" placeholder="<?php _e('Confirm Password', 'smartpay'); ?>">
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-center mt-4">
                                            <button type="submit" class="btn btn-primary px-5"><?php _e('Update', 'smartpay'); ?></button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>