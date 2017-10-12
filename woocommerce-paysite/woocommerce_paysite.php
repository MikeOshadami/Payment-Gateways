<?php

/**
 * Plugin Name: Paysite WooCommerce Payment Gateway
 * Plugin URI:  https://pay-site.com
 * Description: Paysite Woocommerce Payment gateway allows you to accept payment on your Woocommerce store.
  * Version:     1.0
 */
 
register_activation_hook( __FILE__ ,'jal_install');
	function jal_install() {
		global $jal_db_version;
	   $jal_db_version = '1.0';
		global $wpdb;
		global $jal_db_version;

		$table_name = $wpdb->prefix . 'paysitepaymentgatewaytranx';
		
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			paysiteorderid varchar(255) NOT NULL,
			storeorderid varchar(255) NOT NULL,
			UNIQUE KEY id (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		add_option( 'jal_db_version', $jal_db_version );
	}
add_filter('plugins_loaded', 'wc_paysite_init' );
function wc_paysite_init() {
	if (!class_exists('WC_Payment_Gateway')) {
		return;
	}
	
	class WC_Paysite extends WC_Payment_Gateway {	
			
		public function __construct() { 
			global $woocommerce;
			
			$this->id		= 'paysite';
			$this->icon 	= apply_filters('woocommerce_paysite_icon', plugins_url( 'images/paysite-payment-options.png' , __FILE__ ) );
			$this->method_title     = __( 'Paysite', 'woocommerce' );
			$this->notify_url   = WC()->api_request_url('WC_Paysite');
			$default_payment_type_options = array(
											'Verve' => "Verve Card",  
											'Visa' => "Visa",  
											'MasterCard' => "MasterCard",  
											'GhLink' => "GhLink",
											'CUP' => "CUP",
											'Airtel' =>"Airtel"
										//Add more static Payment option here...
											);
												
			$this->payment_type_options = apply_filters( 'woocommerce_paysite_card_types', $default_payment_type_options );
			// Load the form fields.
			$this->init_form_fields();
			
			// Load the settings.
			$this->init_settings();
			
			// Define user set variables
			$this->title 		= $this->settings['title'];
			$this->description 	= $this->settings['description'];
			$this->mert_id 	= $this->settings['merchantid'];
			$this->mert_secretkey 	= $this->settings['merchantsecretkey'];
			$this->public_key 	= $this->settings['publickey'];  
			$this->paysite_paymentoptions = $this->settings['paysite_paymentoptions'];			
			$this->thanks_message	= $this->settings['thanks_message'];	
			$this->error_message	= $this->settings['error_message'];	
			$this->feedback_message	= '';
			//$this->paymentTypes = $this->getEnabledPaymentTypes($cardtypes);
			add_action('woocommerce_receipt_remita', array(&$this, 'receipt_page'));
			add_action('woocommerce_update_options_payment_gateways_' . $this->id, array(&$this, 'process_admin_options'));
			add_action( 'woocommerce_checkout_update_order_meta', array(&$this,'my_ccustom_checkout_field_update_order_meta' ));	
			add_action('woocommerce_thankyou_' . $this->id, array(&$this, 'thankyou_page'));
			add_action( 'check_ipn_response', array( $this, 'check_ipn_response') );
			// Payment listener/API hook
			add_action( 'woocommerce_api_wc_remita', array( &$this, 'process_ipn' )  );
			//Filters
			add_filter('woocommerce_currencies', array($this, 'add_ngn_currency'));
			add_filter('woocommerce_currency_symbol', array($this, 'add_ngn_currency_symbol'), 10, 2);
		 //   register_activation_hook( __FILE__, array( &$this, 'jal_install' ) );
			 
				
		}
	
		function my_ccustom_checkout_field_update_order_meta( $order_id ) {
							if ( ! empty( $_POST['paymenttype'] ) ) {
								update_post_meta( $order_id, 'paymentType', sanitize_text_field( $_POST['paymenttype'] ) );
							}
						}
	    
		function add_ngn_currency($currencies) {
		     $currencies['NGN'] = __( 'Nigerian Naira (NGN)', 'woocommerce' );
		     return $currencies;
		}
		
		function add_ngn_currency_symbol($currency_symbol, $currency) {
			switch( $currency ) {
				case 'NGN':
					$currency_symbol = '₦';
					break;
			}
			
			return $currency_symbol;
		}    
	    
		function is_valid_for_use() {
			$return = true;
			
			if (!in_array(get_option('woocommerce_currency'), array('NGN'))) {
			    $return = false;
			}
		
			return $return;
		}
	    
			function admin_options() {
			echo '<h3>' . __('Paysite Payment Gateway', 'woocommerce') . '</h3>';
			echo '<p>' . __('<br><img src="' . plugins_url( 'images/paysite.png', __FILE__ ) . '" >', 'woocommerce') . '</p>';
			echo '<table class="form-table">';
				
			if ( $this->is_valid_for_use() ) {
				$this->generate_settings_html();
			} else {
				echo '<div class="inline error"><p><strong>' . __( 'Gateway Disabled', 'woocommerce' ) . '</strong>: ' . __( 'Paysite does not support your store currency.', 'woocommerce' ) . '</p></div>';
			}
				
			echo '</table>';
				
		}
	    
	      
	       function init_form_fields() {
		 	   	  
		   	   $this->form_fields = array(
				'title' => array(
								'title' => __( 'Title', 'woocommerce' ), 
								'type' => 'text', 
								'default' => __( 'Paysite Payment Gateway', 'woocommerce' ),
								'disabled' =>  true
							),
				'description' => array(
								'title' => __( 'Description', 'woocommerce' ), 
								'type' => 'textarea', 
								'disabled' =>  true,
								'default' => __("Pay Via Paysite: Accepts Visa, Mastercard and Verve cards;", 'woocommerce')
							),
				'enabled' => array(
								'title' => __( 'Enable/Disable', 'woocommerce' ), 
								'type' => 'checkbox', 
								'label' => __( 'Enable', 'woocommerce' ), 
								'default' => 'yes'
							), 
				'merchantid' => array(
								'title' => __( 'Merchant ID', 'woocommerce' ), 
								'type' => 'text' 
																
							),
				'merchantsecretkey' => array(
								'title' => __( 'Merchant Secret Key', 'woocommerce' ), 
								'type' => 'text',
															
							),
				'publickey' => array(
								'title' => __( 'Public Key', 'woocommerce' ), 
								'type' => 'text' 
																
							),
					
						'paysite_paymentoptions' => array(
							'title' => __( 'Payment Options', 'woocommerce' ),
							'type' 			=> 'multiselect',
							'default' => 'Visa',
							'desc_tip'      => true,
							'description' => __( 'Select which Payment Channel to accept.', 'woothemes' ),
							'placeholder'	=> '',
							'options' => $this->payment_type_options,
						),
						
				);
	    
		}
	    
		function payment_fields() {
		 	// Description of payment method from settings
          		if ( $this->description ) { ?>
            		<p><?php echo $this->description; ?></p>
			<?php } ?>
				<fieldset>
				 <li class="payment_method_cod">
						Payment Type
						<label for="payment_method_cod">
							<select name="paymenttype" id="paymenttype" class="woocommerce-select">
								<option>-- Select Payment Type --</option>
								<?php foreach( $this->paysite_paymentoptions as $key => $value ) { ?>
									<option value="<?php echo $value ?>"><?php _e( $value, 'woocommerce' ); ?></option>
								<?php } ?>
							 </select>		
						 </label>		
					</li>								 
				</fieldset>
			            			
		<?php  } 
			
			
		function get_paysite_args( $order ) {
			global $woocommerce;
			global $jal_db_version;
			$jal_db_version = '1.0';
			global $wpdb;
			global $jal_db_version;
			$table_name = $wpdb->prefix . 'paysitepaymentgatewaytranx';
			$order_id = $order->id;
			$uniqueRef = uniqid();
			$paysiteorderid = $uniqueRef.'_'.$order->id;
			$wpdb->insert( 
				$table_name, 
				array( 
					'time' => current_time( 'mysql' ), 
					'paysiteorderid' => $paysiteorderid, 
					'storeorderid' => $order_id, 
				) 
			);
			$redirect_url = $this -> notify_url;
			$order_total = round(((number_format($this->get_order_total($order) + $woocommerce->cart->get_total_discount(), 2, '.', ''))),0);
			$amountInKobo = $order_total * 100;
			$hash_string = $this ->mert_id . $amountInKobo . $redirect_url . $this ->mert_secretkey .$paysiteorderid . $this ->public_key;
		    $hash = hash('sha512', $hash_string);
			$cardType	= get_post_meta( $order->id, 'paymentType', true );
			$pys_product_name="eCommerceStore - Payment";
			$pys_product_id="eCommerceStore";
			$paysite_args = array(
				'pys_merchant_id' => $this -> mert_id,
				'pys_product_name' => $pys_product_name,
				'pys_product_id' => $pys_product_id,
				'pys_amount' => $amountInKobo,
				'pys_hash' => $hash,
				'pys_transaction_id' => $paysiteorderid,
				'pys_callback_url' => $redirect_url,
				'pys_customer_name' => trim($order->billing_first_name . ' ' . $order->billing_last_name),
				'pys_email' => trim($order->billing_email),
				'pys_mobile_no' => trim($order->billing_phone),
				'pys_card_type' => 'VERVE',
				'pys_payment_method' => 'CARD',
				'pys_lang' => 'en-English',
				'pys_version' => '1',
				'pys_ccy' => 'NGN',
			);
			
			if (isset($order->user_id)) {
				$paysite_args['pys_customer_id'] = $order->user_id;
			}
			
			$paysite_args = apply_filters('woocommerce_paysite_args', $paysite_args);
			
			return $paysite_args;
		}
	
		function generate_remita_form( $order_id ) {
			global $woocommerce;
			
			$order = new WC_Order( $order_id );
			$paysite_args = $this->get_paysite_args( $order );
			$paysite_args_array = array();
			$gateway_url = 'https://pay-site.com/merchantgateway/servicepayLIVE';		
		foreach ($paysite_args as $key => $value) {
				$paysite_args_array[] = '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '" />';
			}
			//
				wc_enqueue_js('
					jQuery("body").block({
							message: "<img src=\"'.esc_url( $woocommerce->plugin_url() ).'/assets/images/ajax-loader.gif\" alt=\"Redirecting...\" style=\"float:left; margin-right: 10px;\" />'.__('Thank you for your order. We are now redirecting you to Paysite.', 'woothemes').'",
							overlayCSS:
							{
								background: "#fff",
								opacity: 0.6
							},
							css: {
						        padding:        20,
						        textAlign:      "center",
						        color:          "#555",
						        border:         "3px solid #aaa",
						        backgroundColor:"#fff",
						        cursor:         "wait",
						        lineHeight:		"32px"
						    }
						});
					jQuery("#submit_paysite_payment_form").click();
				');
				
	
			return '<form action="'.esc_url( $gateway_url ).'" method="post" id="paysite_payment_form">
					' . implode('', $paysite_args_array) . '
						<input type="submit" class="button alt" id="submit_paysite_payment_form" value="'.__('Submit', 'woothemes').'" /> <a class="button cancel" href="'.esc_url( $order->get_cancel_order_url() ).'">'.__('Cancel order &amp; restore cart', 'woothemes').'</a>
					</form>';
	
		}
		
		public function process_ipn(){
			@ob_clean();
    	if ( isset( $_GET['txnId'] )) {
    		do_action( 'check_ipn_response',$_GET );
    	}
    }
    
		public function updatePaymentStatus($order,$response_code,$response_reason,$paymentRef)	{
			switch($response_code)
					{
				case "00":                    
					 if($order->status == 'processing'){
						$order->add_order_note('Payment Via Paysite<br />Payment Reference: '.$paymentRef);
						//Add customer order note
						$order->add_order_note('Payment Received.<br />Your order is currently being processed.<br />We will be shipping your order to you soon.<br />Payment Reference: '.$paymentRef, 1);
						// Reduce stock levels
						$order->reduce_order_stock();
						// Empty cart
						WC()->cart->empty_cart();
						$message = 'Thank you for shopping with us.<br />Your transaction was successful, payment was received.<br />Your order is currently being processed.<br />Payment Reference: '.$paymentRef;
						$message_type = 'success';
					}
					else{
						if( $order->has_downloadable_item() ){
							//Update order status
							$order->update_status( 'completed', 'Payment received, your order is now complete.' );
							$order->add_order_note('Payment Received.<br />Your order is now complete.<br />Payment Reference: '.$paymentRef);
							$message = 'Thank you for shopping with us.<br />Your transaction was successful, payment was received.<br />Your order is now complete.<br />Payment Reference: '.$paymentRef;
							$message_type = 'success';
						}
						else{
							//Update order status
							$order->update_status( 'processing', 'Payment received, your order is currently being processed.' );
							$order->add_order_note('Payment Received.<br />Your order is currently being processed.<br />We will be shipping your order to you soon.<br />Payment Reference: '.$paymentRef);
							$message = 'Thank you for shopping with us.<br />Your transaction was successful, payment was received.<br />Your order is currently being processed.<br />Payment Reference: '.$paymentRef;
							$message_type = 'success';
						}
						// Reduce stock levels
						$order->reduce_order_stock();
						// Empty cart
						WC()->cart->empty_cart();
						}
					break;
					default:
						//process a failed transaction
						$message = 	'Thank you for shopping with us. <br />However, the transaction wasn\'t successful, payment wasn\'t received.<br />Reason: '. $response_reason.'<br />Payment Reference: '.$paymentRef;
						$message_type = 'error';
						$order->add_order_note( $message );
						//Update the order status
						$order->update_status('failed', '');
						break;
				}	
				return array($message, $message_type);
				}

  
		function check_ipn_response($posted) {
		@ob_clean();
		global $woocommerce;
		if( isset($posted['txnId'] ) ){
		$orderId = $posted['txnId'];
		$order_details 	= explode('_', $orderId);
		$paysiteorderid 	= $order_details[0];
		$order_id 		= $order_details[1];
		$response = $this->paysite_transaction_details($orderId);
		$response_code = $response['pys_rsp_code'];
		$paymentRef = $response['pys_payment_ref'];
		$response_reason = $response['pys_rsp_message'];
		$order = new WC_Order( (int) $order_id );
		$callUpdate = $this->updatePaymentStatus($order,$response_code,$response_reason,$paymentRef);
		$message = $callUpdate[0];
		$message_type = $callUpdate[1]; 		
			}
		else {
			$message = 	'Thank you for shopping with us. <br />However, the transaction wasn\'t successful, payment wasn\'t received.';
			$message_type = 'error';
			
			}
		wc_add_notice( $message, $message_type );
		$redirect_url = $this->get_return_url( $order );
        wp_redirect( $redirect_url );
        exit;		
		}
/**
	 	* Query a transaction details
	 	**/
			
    function paysite_transaction_details($txnRef) {
        $mert =  $this -> mert_id;
        $secretKey = $this -> mert_secretkey;
        $publicKey = $this -> public_key;
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
	
		function thankyou_page() {
			echo wpautop($this->feedback_message);
		}
	
		
		function process_payment( $order_id ) {
			$order = new WC_Order( $order_id );
			$this->paymenttype 	= get_post_meta( $order_id, 'paymenttype', true );
				return array(
				'result' => 'success',
				'redirect' => add_query_arg('order', $order->id, add_query_arg('key', $order->order_key, get_permalink(woocommerce_get_page_id('pay'))))
			);
		}
		
		function receipt_page( $order ) {
			echo '<p>'.__('Thank you for your order, please click the button below to pay with Paysite.', 'woocommerce').'</p>';
			
			echo $this->generate_remita_form( $order );
		}
	

		
		 	}
		function woocommerce_add_paysite_gateway( $methods ) {
		$methods[] = 'wc_paysite'; 
		return $methods;
	}
	
	add_filter('woocommerce_payment_gateways', 'woocommerce_add_paysite_gateway' );	
}

?>