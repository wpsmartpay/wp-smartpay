<?php
$base_price = $download->get_base_price() ?? 0;
$sale_price = $download->get_sale_price() ?? 0;
?>

<div class="smartpay smartpay_pricing" id="smartpay_pricing_section">
    <div class="form-row">
        <div class="col-6">
            <div class="form-group">
                <label for="base_price" class="text-muted my-2 d-block"><strong>Base price</strong></label>
                <input type="text" class="form-control" id="base_price" name="base_price" placeholder="0.00" value="<?php echo $base_price; ?>">
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                <label for="sale_price" class="text-muted my-2 d-block"><strong>Sales price</strong></label>
                <input type="text" class="form-control" id="sale_price" name="sale_price" placeholder="0.00" value="<?php echo $sale_price; ?>">
            </div>
        </div>
    </div>

    <?php include 'variants.php'; ?>
</div>