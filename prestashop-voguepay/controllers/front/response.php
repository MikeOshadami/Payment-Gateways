<?php
 /**
 * Plugin Name: Remita Prestashop Payment Gateway
 * Plugin URI:  https://www.remita.net
 * Description: Remita Prestashop Payment gateway allows you to accept payment on your Prestashop store via Visa Cards, Mastercards, Verve Cards, eTranzact, PocketMoni, Paga, Internet Banking, Bank Branch and Remita Account Transfer.
 * Author:      Oshadami Mike
  * Version:     2.0
 */
require_once(dirname(__FILE__).'/../../../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../../../init.php');
class VoguepayResponseModuleFrontController extends ModuleFrontController
{
	public $ssl = true;
	public function initContent()
	{	    
		  $transId = $_POST['transaction_id'];
//var_dump($transId);
$transaction = array();
$t = array();
$order_id = '';
//if (isset($_POST['transaction_id'])) {
$json = file_get_contents('https://voguepay.com/?v_transaction_id=' . $transId . '&demo=true&type=json');
$transaction = json_decode($json, true);
$email = $transaction['email'];
$total = $transaction['total'];
$date = $transaction['date'];
$voguePayOrderId = $transaction['merchant_ref'];
$order_details = explode('_', $voguePayOrderId);
$uniqueRef = $order_details[0];
$cartId = $order_details[1];
$secure_key = $order_details[2];
$status = $transaction['status'];
$transaction_id = $transaction['transaction_id'];
//$fromCurrency = new Currency(Currency::getIdByIsoCode('ZAR'));
//$toCurrency = new Currency((int) $cart->id_currency);
//$total = Tools::convertPriceFull($total, $fromCurrency, $toCurrency);
/**
$context = Context::getContext();
$cart = $context->cart;
$customer = new Customer((int)$cart->id_customer);
if (trim(strtolower($status)) == 'approved') {
  $voguepay->validateOrder((int) $cartId, (int) _PS_OS_PAYMENT_, (float) $total, $voguepay->displayName, NULL, array('transaction_id' => $transaction_id), NULL, false, $secure_key);
} elseif (trim(strtolower($status)) == 'pending') {
  $voguepay->validateOrder((int) $cartId, (int) _PS_OS_ERROR_, (float) $total, $voguepay->displayName, NULL, array('transaction_id' => $transaction_id), NULL, false, $secure_key);
} else {
  $voguepay->validateOrder((int) $cartId, (int) _PS_OS_ERROR_, (float) $total, $voguepay->displayName, NULL, array('transaction_id' => $transaction_id), NULL, false, $secure_key);
}
 // $voguepay->setTemplate('confirmation.tpl');
 // Tools::redirect('index.php?controller=order-confirmation&id_cart='.$cart->id.'&id_module='.$voguepay->id.'&id_order='.$voguepay->currentOrder.'&key='.$customer->secure_key);
//$url = $this->context->link->getPageLink( 'order-confirmation', null, null, 'key='.$cart->secure_key.'&id_cart='.(int)($cart->id).'&id_module='.(int)($this->id));
 // Tools::redirect($url);
  //  Tools::redirect('index.php?controller=order-confirmation&id_cart='.(int)$cart->id.'&id_module='.(int)$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key);
   */       
 return $this->fetch('module:voguepay/voguepay_success.tpl');
			$this->display_column_left = false;
			parent::initContent();
			$this->context->smarty->assign(array(
				'ref_number' => $order_id,
				'message' => $message,
				'response_code' => $response_code,
				'response_reason' => $response_reason,
				'rrr' => $rrr,
				'this_path' => $this->module->getPathUri(),
				'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/'
			));
			$this->setTemplate('response.tpl');
		   	}
				
}
}
		

