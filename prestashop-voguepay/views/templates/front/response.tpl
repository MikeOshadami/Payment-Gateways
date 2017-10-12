<div style="text-align: center;">
	{if $response_code == '00' || $response_code == '01'}
	<h2>Transaction Successful</h2>
	<p><b>Your Payment Has Been Received</b></p>
	<p>You can view your Purchase History from your "Account Page"</p>
	<p><b>Remita Retrieval Reference: </b>{$rrr}<p>
	{else if $response_code == '021'}
	<h2>RRR Generated Successfully</h2>
	<p><b>Remita Retrieval Reference: </b>{$rrr}</p>
	{else}
	<h2>Your Transaction was not Successful</h2>
	<p>Payment for this order was not received.</p>
	{if $rrr !=null}
	<p>Your Remita Retrieval Reference is <span><b>{$rrr}</b></span><br />
	{/if}
	<p><b>Reason: </b>{$response_reason}<p>	
	{/if}
</div>
