<?php
$base_price = $product->get_base_price() ?? '';
$sale_price = $product->get_sale_price() ?? '';
?>

<div class="smartpay smartpay-pricing">
    <div class="form-row">
        <div class="col-6">
            <div class="form-group">
                <label for="base_price" class="text-muted my-2 d-block"><strong>Base price</strong></label>
                <input type="text" class="form-control" id="base_price" name="base_price" placeholder="2.0"
                    value="<?php echo $base_price; ?>">
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                <label for="sale_price" class="text-muted my-2 d-block"><strong>Sales price</strong></label>
                <input type="text" class="form-control" id="sale_price" name="sale_price" placeholder="1.0"
                    value="<?php echo $sale_price; ?>">
            </div>
        </div>
    </div>

    <?php include 'variations.php'; ?>
</div>