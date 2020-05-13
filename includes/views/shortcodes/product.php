<?php
//var_dump($product) 
?>

<div class="smartpay">
    <h3><?php echo $product->name; ?></h3>
    <p><?php echo $product->sale_price; ?></p>
    <p><?php echo $product->description; ?></p>
    <form action="">
        <button type="button" class="smartpay-product-buy">Buy now</button>
    </form>
</div>