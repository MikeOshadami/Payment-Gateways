<?php echo $header; ?><?php echo $column_left; ?><?php echo $column_right; ?>
<div style="text-align: center;">
    <?php if($pys_rsp_code == '00') { ?>
    <h2>Transaction Successful</h2>
    <p><b>Your Payment Has Been Received</b></p>
    <p>You can view your Purchase History from your "Account Page"</p>
    <p><b>Paysite Payment Reference: </b><?php echo $pys_payment_ref; ?><p>
    <div class="buttons">
        <div class="right"><a href="<?php echo $continue; ?>" class="button"><?php echo $button_continue; ?></a></div>
    </div>
    <?php } else{ ?>
    <h2>Your Transaction was not Successful</h2>
    <p>Payment for this order was not received.</p>
    <?php if ($pys_payment_ref !=null){ ?>
    <p>Your Paysite Payment Reference is <span><b><?php echo $pys_payment_ref; ?></b></span><br />
        <?php } ?> 
        <?php if ($pys_rsp_message !=null) { ?>
    <p><b>Reason: </b><?php echo $pys_rsp_message; ?><p>
        <?php } ?> 
    <div class="buttons">
        <div class="right"><a class="button" href="<?php echo $fail_continue; ?>"><?php echo $button_continue; ?></a></div>
    </div>

</div>
<?php }?>
<?php echo $content_bottom; ?></div>
<?php echo $footer; ?>