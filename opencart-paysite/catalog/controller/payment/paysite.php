<?php

/**
 * Plugin Name: PaySite OpenCart Payment Gateway
 * Plugin URI:  https://www.pay-site.com
 * Description: PaySite OpenCart Payment gateway allows you to accept payment on your OpenCart store.
 * Author:      Oshadami Mike
 * Version:     1.0
 */
class ControllerPaymentPaysite extends Controller {

    public function index() {
        $this->language->load('payment/paysite');
        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $order_id = $this->session->data['order_id'];
        if ($order_info) {
            $this->data['paysite_mercid'] = trim($this->config->get('paysite_mercid'));
            $this->data['paysite_secretkey'] = trim($this->config->get('paysite_secretkey'));
            $this->data['paysite_publickey'] = trim($this->config->get('paysite_publickey'));
            $selectedPayment = $this->config->get('paysite_paymentoptions');
            $this->data['orderid'] = $this->session->data['order_id'];
            //	$this->data['orderid'] = date('His') . $this->session->data['order_id'];
            $this->data['returnurl'] = $this->url->link('payment/paysite/callback', '', 'SSL');
            $this->data['total'] = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
            $this->data['totalAmount'] = html_entity_decode($this->data['total']);
            $this->data['payerName'] = $order_info['payment_firstname'] . ' ' . $order_info['payment_lastname'];
            $this->data['payerEmail'] = $order_info['email'];
            $this->data['payerPhone'] = html_entity_decode($order_info['telephone'], ENT_QUOTES, 'UTF-8');
            $this->data['customerId'] = $order_info['customer_id'];
            $this->data['productId'] = "eCommerceStore";
            $this->data['productName'] = "eCommerceStore - Payment";
            $uniqueRef = uniqid();
            $this->data['paysiteOrderId'] = $uniqueRef . '_' . $this->data['orderid'];
            $this->data['button_confirm'] = $this->language->get('button_confirm');
            $this->data['totalAmountinKobo'] = $this->data['total'] * 100;
            $paymentOptions = array(
                'Verve' => "Verve Card",
                'Visa' => "Visa",
                'Mastercard' => "MasterCard",
                'GhLink' => "GhLink",
                'CUP' => "CUP",
                'Airtel' => "Airtel"
                    //Add more static Payment option here...  
            );

            function getEnabledPaymentTypes($paymentOptions, $selected) {

                foreach ($paymentOptions as $code => $name) {
                    if (!in_array($code, $selected)) {
                        unset($paymentOptions[$code]);
                    }
                }

                return $paymentOptions;
            }

            $this->data['paymentOptions'] = getEnabledPaymentTypes($paymentOptions, $selectedPayment);
            $this->data['gateway_url'] = 'https://pay-site.com/merchantgateway/servicepayLIVE';
            $hash_string = $this->data['paysite_mercid'] . $this->data['totalAmountinKobo'] . $this->data['returnurl'] . $this->data['paysite_secretkey'] . $this->data['paysiteOrderId'] . $this->data['paysite_publickey'];
            $this->data['hash'] = hash('sha512', $hash_string);
            //pys_hash = SHA512 (pys_merchant_id + pys_amount + pys_callback_url + merchant_secret_key + pys_transaction_id + merchant_public_key)   	
        }

        //1 - Pending Status
        $message = 'Payment Status : Pending';
        //	$this->model_checkout_order->addOrderHistory($order_id, 1, $message, false);
        $this->model_checkout_order->confirm($order_id, 1, $message, false);
        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/paysite.tpl')) {
            $this->template = $this->config->get('config_template') . '/template/payment/paysite.tpl';
        } else {
            $this->template = 'default/template/payment/paysite.tpl';
        }

        $this->render();
    }

    function paysite_transaction_details($txnRef) {
        //hash  = SHA512 (txnRef + merchantId/No +secret Key  + Public Key)
        $mert = trim($this->config->get('paysite_mercid'));
        $secretKey = trim($this->config->get('paysite_secretkey'));
        $publicKey = trim($this->config->get('paysite_publickey'));
        $hash_string = $txnRef . $mert . $secretKey .$publicKey;
        $hash = hash('sha512', $hash_string);
        $query_url = 'https://pay-site.com/merchantgateway/gettransactionstatusLIVE?';
        $url = $query_url . 'merchantNo=' . $mert . '&txnRef=' . $txnRef . '&hash=' . $hash;
	//	var_dump($url);
        //  Initiate curl
        $ch = curl_init();
        // Disable SSL verification
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // Will return the response, if false it print the response
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Set the url
        curl_setopt($ch, CURLOPT_URL, $url);
        // Execute
        $result = curl_exec($ch);
        // Closing
        curl_close($ch);
        $response = json_decode($result, true);
	//	var_dump($response);
        return $response;
    }

    function updatePaymentStatus($order_id, $response_code, $response_reason, $paymentRef, $authCode) {
        switch ($response_code) {
            case "00":
                $message = 'Payment Status : - Successful - Paysite Payment Reference: ' . $paymentRef . ', Authorization Code:' . $authCode;
                //	$this->model_checkout_order->addOrderHistory($order_id, trim($this->config->get('paysite_processed_status_id')), $message , false);	
                $this->model_checkout_order->update($order_id, $this->config->get('paysite_processed_status_id'), $message, true);
                break;
            default:
                //process a failed transaction
                $message = 'Payment Status : - Not Successful - Reason: ' . $response_reason . ' - Paysite Payment Reference: ' . $paymentRef;
                //1 - Pending Status
                //	$this->model_checkout_order->addOrderHistory($order_id, 1, $message, false);	
                $this->model_checkout_order->update($order_id, 1, $message, true);
                break;
        }
    }

    public function callback() {
        //echo "Return URL";	
        $this->data['pys_rsp_code'] = "";
        $this->data['pys_rsp_message'] = "";
        if (isset($_GET['txnId'])) {
            $txnref = $_GET['txnId'];
            $response = $this->paysite_transaction_details($txnref);
            //var_dump($response);
            $order_details = explode('_', $txnref);
            $order_id = $order_details[1];
            $this->data['order_id'] = $order_id;
            $this->load->model('checkout/order');
            $order_info = $this->model_checkout_order->getOrder($order_id);
            $this->data['pys_rsp_code'] = $response['pys_rsp_code'];
            $this->data['pys_rsp_message'] = $response['pys_rsp_message'];
            $this->data['pys_payment_ref'] = $response['pys_payment_ref'];
            $this->data['pys_auth_code'] = $response['pys_auth_code'];
            $this->updatePaymentStatus($order_id, $this->data['pys_rsp_code'], $this->data['pys_rsp_message'], $this->data['pys_payment_ref'], $this->data['pys_auth_code']);
            if (isset($this->session->data['order_id'])) {
                $this->cart->clear();
                unset($this->session->data['shipping_method']);
                unset($this->session->data['shipping_methods']);
                unset($this->session->data['payment_method']);
                unset($this->session->data['payment_methods']);
                unset($this->session->data['guest']);
                unset($this->session->data['comment']);
                unset($this->session->data['order_id']);
                unset($this->session->data['coupon']);
                unset($this->session->data['reward']);
                unset($this->session->data['voucher']);
                unset($this->session->data['vouchers']);
            }
        }
        $this->language->load('checkout/success');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'href' => $this->url->link('common/home'),
            'text' => $this->language->get('text_home'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'href' => $this->url->link('checkout/cart'),
            'text' => $this->language->get('text_basket'),
            'separator' => $this->language->get('text_separator')
        );

        $this->data['breadcrumbs'][] = array(
            'href' => $this->url->link('checkout/checkout', '', 'SSL'),
            'text' => $this->language->get('text_checkout'),
            'separator' => $this->language->get('text_separator')
        );

        $this->data['breadcrumbs'][] = array(
            'href' => $this->url->link('checkout/success'),
            'text' => $this->language->get('text_success'),
            'separator' => $this->language->get('text_separator')
        );

        $this->data['heading_title'] = $this->language->get('heading_title');

        $this->data['button_continue'] = $this->language->get('button_continue');
        $this->data['fail_continue'] = $this->url->link('checkout/checkout', '', 'SSL');
        $this->data['continue'] = $this->url->link('account/order/info', 'order_id=' . $this->data['order_id'], 'SSL');
        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/paysite_success.tpl')) {
            $this->template = $this->config->get('config_template') . '/template/payment/paysite_success.tpl';
        } else {
            $this->template = 'default/template/payment/paysite_success.tpl';
        }
        $this->children = array(
            'common/column_left',
            'common/column_right',
            'common/content_top',
            'common/content_bottom',
            'common/footer',
            'common/header'
        );

        $this->response->setOutput($this->render());
    }

}

?>