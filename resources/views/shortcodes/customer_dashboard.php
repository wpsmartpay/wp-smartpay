<?php

use SmartPay\Models\Product;
use SmartPay\Modules\Frontend\Utilities\Downloader;

$activePayments = $customer->payments()->where('status', 'completed')->get();
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
                        <h3 class="mt-4 mb-2"><?php echo ($customer->full_name ?? ''); ?></h3>
                        <p class="my-0" class="text-muted"><?php echo $customer->email ?? ''; ?></p>
                    </div>
                </div>

                <div class="mt-5">
                    <nav class="nav justify-content-center nav-pills mb-4" role="tablist">
                        <a class="nav-link mx-2 px-4 active" data-toggle="pill" href="#payments" role="tab"><?php _e('Payments', 'smartpay'); ?></a>

                        <a class="nav-link mx-2 px-4" data-toggle="pill" href="#downloads" role="tab"><?php _e('Downloads', 'smartpay'); ?></a>

                        <a class="nav-link mx-2 px-4" data-toggle="pill" href="#profile" role="tab"><?php _e('Profile', 'smartpay'); ?></a>

                        <?php do_action('smartpay_customer_dashboard_tab_link'); ?>
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
                                                        <th scope="col"><?php _e('Action', 'smartpay'); ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody>

                                                    <?php foreach ($customer->payments as $index => $payment) : ?>
                                                        <tr>
	                                                        <?php
	                                                        $payment_detail_url = add_query_arg('smartpay-payment', $payment->uuid, smartpay_get_payment_success_page_uri());
	                                                        ?>
                                                            <th scope="row">
                                                                <a href="<?php echo $payment_detail_url; ?>" target="_blank">
                                                                    <?php echo '#' . $payment->id; ?>
                                                                </a>
                                                            </th>
                                                            <td>
                                                                <!-- show completed date else order created date-->
                                                                <?php
                                                                    $date = $payment->completed_at ?? $payment->created_at;
                                                                ?>
                                                                <?php echo mysql2date('F j, Y', $date) ; ?>
                                                            </td>
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
                                    <?php if (!count($activePayments)) : ?>
                                        <div class="card">
                                            <div class="card-body py-5">
                                                <p class="text-info  m-0 text-center"><?php _e('You don\'t have downloadable item yet!', 'smartpay'); ?></p>
                                            </div>
                                        </div>
                                    <?php else : ?>
                                        <?php foreach ($activePayments as $index => $payment) : ?>

                                            <!-- // FIXME: Add accessor -->
                                            <?php if ('Product Purchase' !== $payment->type) continue; ?>

                                            <?php $productId = $payment->data['product_id'] ?? 0; ?>
                                            <?php $product = Product::with(['parent'])->find($productId); ?>

                                            <?php if (!$productId || !$product) : ?>
                                                <p>Product Not available</p>
                                            <?php else : ?>
                                                <div class="border mb-3 product">
                                                    <div class="p-3 product--header">
                                                        <div class="d-flex align-items-center" data-toggle="collapse" data-target="#collapse-payment-<?php echo $index; ?>">
                                                            <?php $covers = $product->isParent() ? $product->covers : $product->parent->covers; ?>
                                                            <?php if (count($covers)) : ?>
                                                                <div class="product--image mr-3">
                                                                    <img src="<?php echo $covers[0]['icon']; ?>" class="border" alt="">
                                                                </div>
                                                            <?php endif; ?>
                                                            <div class="flex-grow-1">
                                                                <h5 class="my-0"><?php echo $product->formatted_title; ?></h5>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="p-3 bg-light collapse show" id="collapse-payment-<?php echo $index; ?>">
                                                        <?php $downloadFiles = $product->files; ?>
                                                        <?php if ($downloadFiles) : ?>
                                                            <p><?php _e('Files', 'smartpay'); ?></p>
                                                            <ul class="list-group">
                                                                <?php foreach ($downloadFiles as $file) : ?>
                                                                    <li class="list-group-item p-2">
                                                                        <div class="d-flex align-items-center">
                                                                            <img src="<?php echo $file['icon']; ?>" class="download-item-icon" alt="">
                                                                            <div class="d-flex flex-grow-1 flex-column ml-3">
                                                                                <p class="m-0"><?php echo $file['name'] ?? ''; ?></p>
                                                                                <div class="d-flex flex-row justify-content-between text-muted m-0">
                                                                                    <small><?php _e(sprintf('Size: ', 'smartpay') . $file['size'] ?? ''); ?></small>
                                                                                </div>
                                                                            </div>
                                                                            <a href="<?php echo smartpay()->make(Downloader::class)->getDownloadUrl($file['id'], $payment->id, $product->id); ?>" class="btn btn-sm btn-primary btn--download"><?php _e('Download', 'smartpay'); ?></a>
                                                                        </div>
                                                                    </li>
                                                                <?php endforeach; ?>
                                                            </ul>
                                                        <?php else : ?>
                                                            <p class="mb-0 text-center"><?php _e('This item has no download file.', 'smartpay'); ?></p>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>

                                <div class="tab-pane fade" id="profile" role="tabpanel">
                                    <!-- <div class="alert alert-danger text-center">
                                        You should check in on some of those fields below.
                                    </div> -->
                                    <form class="my-5" action="#" method="POST">
                                        <div id="form-response"></div>
                                        <?php wp_nonce_field('smartpay_process_profile_update', 'smartpay_process_profile_update'); ?>
                                        <input type="hidden" name="customer_id" value="<?php echo esc_attr($customer->id); ?>">
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
                                        <?php $userinfo = get_userdata($customer->id); ?>
                                        <div class="form-group mb-4">
                                            <label><?php _e('Username', 'smartpay'); ?></label>
                                            <input type="text" class="form-control" placeholder="<?php _e('Username', 'smartpay'); ?>" value="<?php echo $userinfo->user_login ?? '' ?>" disabled>
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

                                <?php do_action('smartpay_customer_dashboard_tab_content', $customer, $customer->payments); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>