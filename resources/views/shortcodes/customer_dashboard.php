<?php
defined('ABSPATH') || exit;

use SmartPay\Models\Product;
use SmartPay\Modules\Frontend\Utilities\Downloader;

$smartpay_customer = $smartpay_view_data['customer'] ?? null;

if (!$smartpay_customer) {
    return;
}

$smartpay_active_payments = $smartpay_customer->payments()->where('status', 'completed')->get();
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
                        <h3 class="mt-4 mb-2"><?php echo esc_html($smartpay_customer->full_name ?? ''); ?></h3>
                        <p class="my-0" class="text-muted"><?php echo esc_html($smartpay_customer->email ?? ''); ?></p>
                    </div>
                </div>

                <div class="mt-5">
                    <nav class="nav justify-content-center nav-pills mb-4" role="tablist">
                        <a class="nav-link mx-2 px-4 active" data-toggle="pill" href="#payments" role="tab"><?php esc_html_e('Payments', 'smartpay'); ?></a>

                        <a class="nav-link mx-2 px-4" data-toggle="pill" href="#downloads" role="tab"><?php esc_html_e('Downloads', 'smartpay'); ?></a>

                        <a class="nav-link mx-2 px-4" data-toggle="pill" href="#profile" role="tab"><?php esc_html_e('Profile', 'smartpay'); ?></a>

                        <?php do_action('smartpay_customer_dashboard_tab_link'); ?>
                    </nav>
                    <div class="d-flex justify-content-center">
                        <div class="col-11">
                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="payments" role="tabpanel">
                                    <?php if (!count($smartpay_customer->payments)) : ?>
                                        <div class="card">
                                            <div class="card-body py-5">
                                                <p class="text-info  m-0 text-center"><?php esc_html_e('You don\'t have any payment yet.', 'smartpay'); ?></p>
                                            </div>
                                        </div>
                                    <?php else : ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th scope="col"><?php esc_html_e('Order ID', 'smartpay'); ?></th>
                                                        <th scope="col"><?php esc_html_e('Date', 'smartpay'); ?></th>
                                                        <th scope="col"><?php esc_html_e('Status', 'smartpay'); ?></th>
                                                        <th scope="col"><?php esc_html_e('Amount', 'smartpay'); ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody>

                                                    <?php foreach ($smartpay_customer->payments as $smartpay_index => $smartpay_payment) : ?>
                                                        <tr>
	                                                        <?php
	                                                        $smartpay_payment_detail_url = add_query_arg('smartpay-payment', $smartpay_payment->uuid, smartpay_get_payment_success_page_uri());
	                                                        ?>
                                                            <th scope="row">
                                                                <a href="<?php echo esc_url($smartpay_payment_detail_url); ?>" target="_blank">
                                                                    <?php echo '#' . esc_html($smartpay_payment->id); ?>
                                                                </a>
                                                            </th>
                                                            <td>
                                                                <!-- show completed date else order created date-->
                                                                <?php
                                                                    $smartpay_date = $smartpay_payment->completed_at ?? $smartpay_payment->created_at;
                                                                ?>
                                                                <?php echo esc_html(mysql2date('F j, Y', $smartpay_date)) ; ?>
                                                            </td>
                                                            <td class="<?php echo esc_attr('completed' == $smartpay_payment->status ? 'text-success' : 'text-danger'); ?>">
                                                                <?php echo esc_html($smartpay_payment->status); ?></td>
                                                            <td class="text-muted">
                                                                <strong class="<?php echo esc_attr('completed' == $smartpay_payment->status ? 'text-success' : 'text-danger'); ?>">
                                                                    <?php echo esc_html(smartpay_amount_format($smartpay_payment->amount, $smartpay_payment->currency)); ?>
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
                                    <?php if (!count($smartpay_active_payments)) : ?>
                                        <div class="card">
                                            <div class="card-body py-5">
                                                <p class="text-info  m-0 text-center"><?php esc_html_e('You don\'t have downloadable item yet!', 'smartpay'); ?></p>
                                            </div>
                                        </div>
                                    <?php else : ?>
                                        <?php foreach ($smartpay_active_payments as $smartpay_index => $smartpay_payment) : ?>

                                            <!-- // FIXME: Add accessor -->
                                            <?php if ('Product Purchase' !== $smartpay_payment->type) continue; ?>

                                            <?php $smartpay_product_id = $smartpay_payment->data['product_id'] ?? 0; ?>
                                            <?php $smartpay_product = Product::with(['parent'])->find($smartpay_product_id); ?>

                                            <?php if (!$smartpay_product_id || !$smartpay_product) : ?>
                                                <p>Product Not available</p>
                                            <?php else : ?>
                                                <div class="border mb-3 product">
                                                    <div class="p-3 product--header">
                                                        <div class="d-flex align-items-center" data-toggle="collapse" data-target="#collapse-payment-<?php echo esc_attr($smartpay_index); ?>">
                                                            <?php $smartpay_covers = $smartpay_product->isParent() ? $smartpay_product->covers : $smartpay_product->parent->covers; ?>
                                                            <?php if (count($smartpay_covers)) : ?>
                                                                <div class="product--image mr-3">
                                                                    <img src="<?php echo esc_url($smartpay_covers[0]['icon']); ?>" class="border" alt="">
                                                                </div>
                                                            <?php endif; ?>
                                                            <div class="flex-grow-1">
                                                                <h5 class="my-0"><?php echo esc_html($smartpay_product->formatted_title); ?></h5>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="p-3 bg-light collapse show" id="collapse-payment-<?php echo esc_attr($smartpay_index); ?>">
                                                        <?php $smartpay_download_files = $smartpay_product->files; ?>
                                                        <?php if ($smartpay_download_files) : ?>
                                                            <p><?php esc_html_e('Files', 'smartpay'); ?></p>
                                                            <ul class="list-group">
                                                                <?php foreach ($smartpay_download_files as $smartpay_file) : ?>
                                                                    <li class="list-group-item p-2">
                                                                        <div class="d-flex align-items-center">
                                                                            <img src="<?php echo esc_url($smartpay_file['icon']); ?>" class="download-item-icon" alt="">
                                                                            <div class="d-flex flex-grow-1 flex-column ml-3">
                                                                                <p class="m-0"><?php echo esc_html($smartpay_file['name'] ?? ''); ?></p>
                                                                                <div class="d-flex flex-row justify-content-between text-muted m-0">
                                                                                    <small><?php echo esc_html( sprintf( /* translators: %s: file size */ __( 'Size: %s', 'smartpay' ), $smartpay_file['size'] ?? '' ) ); ?></small>
                                                                                </div>
                                                                            </div>
                                                                            <a href="<?php echo esc_url(smartpay()->make(Downloader::class)->getDownloadUrl($smartpay_file['id'], $smartpay_payment->id, $smartpay_product->id)); ?>" class="btn btn-sm btn-primary btn--download"><?php esc_html_e('Download', 'smartpay'); ?></a>
                                                                        </div>
                                                                    </li>
                                                                <?php endforeach; ?>
                                                            </ul>
                                                        <?php else : ?>
                                                            <p class="mb-0 text-center"><?php esc_html_e('This item has no download file.', 'smartpay'); ?></p>
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
                                        <input type="hidden" name="customer_id" value="<?php echo esc_attr($smartpay_customer->id); ?>">
                                        <div class="form-row mb-2">
                                            <div class="form-group col">
                                                <label for="first_name"><?php esc_html_e('First Name', 'smartpay'); ?></label>
                                                <input type="text" name="first_name" id="first_name" class="form-control" value="<?php echo esc_attr($smartpay_customer->first_name ?? '') ?>" placeholder="<?php esc_attr_e('First Name', 'smartpay'); ?>">
                                            </div>
                                            <div class="form-group col">
                                                <label for="last_name"><?php esc_html_e('Last Name', 'smartpay'); ?></label>
                                                <input type="text" name="last_name" id="last_name" class="form-control" value="<?php echo esc_attr($smartpay_customer->last_name ?? '') ?>" placeholder="<?php esc_attr_e('Last Name', 'smartpay'); ?>">
                                            </div>
                                        </div>

                                        <div class="form-group mb-4">
                                            <label for="email"><?php esc_html_e('Email', 'smartpay'); ?></label>
                                            <input type="email" name="email" id="email" class="form-control" value="<?php echo esc_attr($smartpay_customer->email ?? '') ?>" placeholder=" <?php esc_attr_e('Email', 'smartpay'); ?>">
                                        </div>
                                        <?php $smartpay_user_info = get_userdata($smartpay_customer->id); ?>
                                        <div class="form-group mb-4">
                                            <label><?php esc_html_e('Username', 'smartpay'); ?></label>
                                            <input type="text" class="form-control" placeholder="<?php esc_attr_e('Username', 'smartpay'); ?>" value="<?php echo esc_attr($smartpay_user_info->user_login ?? '') ?>" disabled>
                                        </div>

                                        <div class="form-row mb-2">
                                            <div class="form-group col-6">
                                                <label><?php esc_html_e('Password', 'smartpay'); ?></label>
                                                <input type="password" name="password" id="password" class="form-control" placeholder="<?php esc_attr_e('Password', 'smartpay'); ?>">
                                                <small class="form-text text-muted"><?php esc_html_e('If you don\'t want to change password, then ignore this fields.', 'smartpay'); ?></small>
                                            </div>
                                            <div class="form-group col-6">
                                                <label><?php esc_html_e('Confirm Password', 'smartpay'); ?></label>
                                                <input type="password" name="password_confirm" id="password_confirm" class="form-control" placeholder="<?php esc_attr_e('Confirm Password', 'smartpay'); ?>">
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-center mt-4">
                                            <button type="submit" class="btn btn-primary px-5"><?php esc_html_e('Update', 'smartpay'); ?></button>
                                        </div>
                                    </form>
                                </div>

                                <?php do_action('smartpay_customer_dashboard_tab_content', $smartpay_customer, $smartpay_customer->payments); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
