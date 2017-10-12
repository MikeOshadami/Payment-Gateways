{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}
<form action="{$gatewayURL}" method="post">
</p>
		<p>
			<input id="merchantId" name="merchantId" value="{$merchantId}" type="hidden"/>
			<input id="serviceTypeId" name="serviceTypeId" value="{$serviceTypeId}" type="hidden"/>
			<input id="amt" name="amt" value="{$amt}" type="hidden"/>
			<input id="responseurl" name="responseurl" value="{$responseurl}" type="hidden"/>
			<input id="hash" name="hash" value="{$hash}" type="hidden"/>
			<input id="payerName" name="payerName" value="{$payerName}" type="hidden"/>
			<input id="payerEmail" name="payerEmail" value="{$payerEmail}" type="hidden"/>			
			<input id="orderId" name="orderId" value="{$orderId}" type="hidden"/>
		</p>	
	<div data-example-id="bordered-table" class="bs-example">
		<table class="table table-bordered">
			<tr>
				<td>	
					<b>Payment Type</b>
				</td>
				<td>
					<select name="paymenttype" class="form-control">
						<option value=""> -- Select --</option>
						{foreach from=$paymentTypes item=label key=key}
						  <option value="{$key}">{$label}
						  </option>
						{/foreach}
					</select>
				</td>
			</tr>   
		</table>
    </div>
	<p class="cart_navigation">		
		<input type="submit" name="submit" value="{l s='Place Order' mod='remita'}" class="exclusive_small" />
	</p>
</form>
