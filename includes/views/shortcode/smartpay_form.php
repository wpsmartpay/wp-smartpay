<?php
?>

<form action="<?php echo home_url('smartpay_checkout'); ?>" method="POST">
    <input type="hidden" name="form_id" value="<?php echo $form_id ?>">

    <label for="first_name">First Name</label>
    <input type="text" name="first_name" id="first_name" placeholder="First Name">
    <br>

    <label for="last_name">Last Name</label>
    <input type="text" name="last_name" id="last_name" placeholder="Last Name">
    <br>

    <label for="email">Email</label>
    <input type="email" name="email" id="email" placeholder="Email">
    <br>

    <label for="amount">Amount</label>
    <input type="text" name="amount" id="amount" value="<?php echo $_amount ?>">
    <br>

    <button type="submit">Pay Now</button>
</form>