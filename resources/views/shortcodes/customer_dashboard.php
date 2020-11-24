<?php
global $wp;


$activePayments = $customer->payments()->where('status', 'completed')->get();
$update_profile_action = home_url(add_query_arg(array(), $wp->request));
?>

<div class="smartpay">
    <div class="customer-dashboard card border-light mb-3">
        <div class="card-body py-5">
            <div class="d-flex justify-content-center flex-column">
                <div class="profile d-flex justify-content-center flex-column">
                    <div class="mx-auto">
                        <img class="rounded-circle" src="<?php echo esc_url(get_avatar_url(get_current_user_id())); ?>" alt="Profile image">
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
                                    <?php if (!count($customer->payments)) : ?>
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
                                                    <th scope="col"><?php _e('Status', 'smartpay'); ?></th>
                                                    <th scope="col"><?php _e('Amount', 'smartpay'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                                <?php foreach ($customer->payments as $index => $payment) : ?>
                                                <tr>
                                                    <th scope="row"><?php echo '#' . $payment->id; ?></th>
                                                    <td><?php echo mysql2date('F j, Y', $payment->completed_at); ?></td>
                                                    <td class="<?php echo 'completed' == $payment->status ? 'text-success' : 'text-danger'; ?>">
                                                        <?php echo $payment->status; ?></td>
                                                    <td class="text-muted">
                                                        <strong class="<?php echo 'completed' == $payment->status ? 'text-success' : 'text-danger'; ?>">
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
                                    <?php if (!is_array($activePayments) || !count($activePayments)) : ?>
                                    <div class="card">
                                        <div class="card-body py-5">
                                            <p class="text-info  m-0 text-center"><?php _e('You don\'t have downloadable item yet!', 'smartpay'); ?></p>
                                        </div>
                                    </div>
                                    <?php else : ?>
                                    <!-- // TODO: Check if payment exist -->
                                    <?php foreach ($activePayments as $index => $payment) : ?>
                                    <?php
                                            if ('product_purchase' !== $payment->payment_type) {
                                                continue;
                                            } ?>
                                    <?php
                                            $product_id = $payment->payment_data['product_id'] ?? 0;
                                            $variation_id = $payment->payment_data['variation_id'] ?? 0;
                                            $product = new SmartPay_Product($product_id);
                                            $variation = new Product_Variation($variation_id);

                                            if ($variation_id && $variation) {
                                                // TODO: Check if variation exist and have permission
                                                $download_files = $variation->get_downloadable_files();
                                            } else {
                                                $download_files = $product->get_downloadable_files();
                                            } ?>

                                    <div class="border mb-3 product">
                                        <div class="p-3 product--header">
                                            <div class="d-flex align-items-center" data-toggle="collapse" data-target="#collapse-payment-<?php echo $index; ?>">
                                                <?php if ($product->image) : ?>
                                                <div class="product--image mr-3">
                                                    <img src="<?php echo $product->image; ?>" class="border" alt="">
                                                </div>
                                                <?php endif; ?>
                                                <div class="flex-grow-1">
                                                    <h5 class="my-0"><?php echo $product->title; ?></h5>
                                                    <?php if ($variation_id && $variation) : ?>
                                                    <p><?php _e(sprintf('<strong>Variation: </strong>', 'smartpay') . $variation->name); ?></p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="p-3 bg-light collapse show" id="collapse-payment-<?php echo $index; ?>">
                                            <?php if ($download_files) : ?>
                                            <p><?php _e('Files', 'smartpay'); ?></p>
                                            <ul class="list-group">
                                                <?php foreach ($download_files as $file_index => $file) : ?>
                                                <li class="list-group-item p-2">
                                                    <div class="d-flex align-items-center flex-wrap">
                                                        <img src="<?php echo $file['icon']; ?>" class="download-item-icon" alt="">
                                                        <div class="d-flex flex-grow-1 flex-column ml-3">
                                                            <p class="m-0"><?php echo $file['filename'] ?? ''; ?></p>
                                                            <div class="d-flex flex-row justify-content-between text-muted m-0">
                                                                <small><?php _e(sprintf('Size: ', 'smartpay') . $file['size'] ?? ''); ?></small>
                                                            </div>
                                                        </div>
                                                        <a href="<?php echo $download->get_file_download_url($file_index, $payment->ID, $product_id, $variation_id); ?>" class="btn btn-sm btn-primary btn--download"><?php _e('Download', 'smartpay'); ?></a>
                                                    </div>
                                                </li>
                                                <?php endforeach; ?>
                                            </ul>
                                            <?php else : ?>
                                            <p class="mb-0 text-center"><?php _e('This item has no download file.', 'smartpay'); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>

                                <div class="tab-pane fade" id="profile" role="tabpanel">
                                    <!-- <div class="alert alert-danger text-center">
                                        You should check in on some of those fields below.
                                    </div> -->
                                    <form class="my-5" action="<?php echo $update_profile_action; ?>" method="POST">
                                        <?php wp_nonce_field('smartpay_process_profile_update', 'smartpay_process_profile_update'); ?>

                                        <div class="form-row mb-2">
                                            <div class="form-group col">
                                                <label for="first_name"><?php _e('First Name', 'smartpay'); ?></label>
                                                <input type="text" name="first_name" id="first_name" class="form-control" value="<?php echo esc_attr($customer->first_name ?? '') ?>" placeholder="<?php _e('First Name', 'smartpay'); ?>">
                                            </div>
                                            <div class="form-group col">
                                                <label for="last_name"><?php _e('Last Name', 'smartpay'); ?></label>
                                                <input type="text" name="last_name" id="last_name" class="form-control" value="<?php echo esc_attr($customer->last_name ?? '') ?>" placeholder="<?php _e('Last Name', 'smartpay'); ?>">
                                            </div>
                                        </div>

                                        <div class="form-group mb-4">
                                            <label for="email"><?php _e('Email', 'smartpay'); ?></label>
                                            <input type="email" name="email" id="email" class="form-control" value="<?php echo esc_attr($customer->email ?? '') ?>" placeholder=" <?php _e('Email', 'smartpay'); ?>">
                                        </div>

                                        <div class="form-group mb-4">
                                            <label><?php _e('Username', 'smartpay'); ?></label>
                                            <input type="text" class="form-control" placeholder="<?php _e('Username', 'smartpay'); ?>" value="<?php echo $customer->wp_user->user_login ?? '' ?>" disabled>
                                        </div>

                                        <div class="form-row mb-2">
                                            <div class="form-group col-6">
                                                <label><?php _e('Password', 'smartpay'); ?></label>
                                                <input type="password" name="password" id="password" class="form-control" placeholder="<?php _e('Password', 'smartpay'); ?>">
                                                <small class="form-text text-muted"><?php _e('If you don\'t want to change password, then ignore this fields.', 'smartpay'); ?></small>
                                            </div>
                                            <div class="form-group col-6">
                                                <label><?php _e('Confirm Password', 'smartpay'); ?></label>
                                                <input type="password" name="password_confirm" id="password_confirm" class="form-control" placeholder="<?php _e('Confirm Password', 'smartpay'); ?>">
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