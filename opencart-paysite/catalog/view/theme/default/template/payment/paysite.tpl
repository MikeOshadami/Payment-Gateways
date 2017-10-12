 <form name="PaysiteForm" method="post" action="<?php echo $gateway_url; ?>">
<input id="pys_merchant_id" name="pys_merchant_id" value="<?php echo $paysite_mercid; ?>" type="hidden"/>
<input id="pys_product_name" name="pys_product_name" value="<?php echo $productName; ?>" type="hidden"/>
<input id="pys_amount" name="pys_amount" value="<?php echo $totalAmountinKobo; ?>" type="hidden"/>
<input id="pys_callback_url" name="pys_callback_url" value="<?php echo $returnurl; ?>" type="hidden"/>
<input id="pys_hash" name="pys_hash" value="<?php echo $hash; ?>" type="hidden"/>
<input id="pys_product_id" name="pys_product_id" value="<?php echo $productId; ?>" type="hidden"/>
<input id="pys_customer_id" name="pys_customer_id" value="<?php echo $customerId; ?>" type="hidden"/>
<input id="pys_customer_name" name="pys_customer_name" value="<?php echo $payerName; ?>" type="hidden"/>
<input id="pys_email" name="pys_email" value="<?php echo $payerEmail; ?>" type="hidden"/>
<input id="pys_mobile_no" name="pys_mobile_no" value="<?php echo $payerPhone; ?>" type="hidden"/>
<input id="pys_transaction_id" name="pys_transaction_id" value="<?php echo $paysiteOrderId; ?>" type="hidden"/>
<input id="pys_payment_method" name="pys_payment_method" value="CARD" type="hidden"/>
<input id="pys_payment_method" name="pys_card_type" value="Verve" type="hidden"/>
<input id="pys_lang" name="pys_lang" value="en-English" type="hidden"/>
<input id="pys_version" name="pys_version" value="1" type="hidden"/>
<input id="pys_ccy" name="pys_ccy" value="NGN" type="hidden"/>
<div class="buttons">
		<div class="right">
		<input type="submit" value="<?php echo $button_confirm; ?>" class="button" />
		</div>
		</div>
</form>
