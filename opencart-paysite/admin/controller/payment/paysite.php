<?php
/**
 * Plugin Name: PaySite OpenCart Payment Gateway
 * Plugin URI:  https://www.pay-site.com
 * Description: PaySite OpenCart Payment gateway allows you to accept payment on your OpenCart store.
 * Author:      Oshadami Mike
 * Version:     1.0
 */
class ControllerPaymentPaysite extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('payment/paysite');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('paysite', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['heading_title'] = $this->language->get('heading_title');
		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_all_zones'] = $this->language->get('text_all_zones');
		$this->data['text_yes'] = $this->language->get('text_yes');
		$this->data['text_no'] = $this->language->get('text_no');
		$this->data['text_test'] = $this->language->get('text_test');
		$this->data['text_live'] = $this->language->get('text_live');
		$this->data['text_edit'] = $this->language->get('text_edit');
		$this->data['entry_mercid'] = $this->language->get('entry_mercid');
		$this->data['entry_token'] = $this->language->get('entry_token');
		$this->data['entry_secretkey'] = $this->language->get('entry_secretkey');
		$this->data['entry_publickey'] = $this->language->get('entry_publickey');
		$this->data['entry_debug'] = $this->language->get('entry_debug');	
		$this->data['entry_test'] = $this->language->get('entry_test');
		$this->data['entry_paymentoptions'] = $this->language->get('entry_paymentoptions');
		$this->data['entry_pending_status'] = $this->language->get('entry_pending_status');
		$this->data['entry_processed_status'] = $this->language->get('entry_processed_status');
		$this->data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');
		$paymentOptions = array(  
									  
									'Verve' => "Verve Card",  
									'Visa' => "Visa",  
									'Mastercard' => "MasterCard",  
									'GhLink' => "GhLink",
									'CUP' => "CUP",
									'Airtel' =>"Airtel"
									 //Add more static Payment option here...  
								);
								
		$this->data['paymentOptions'] = $paymentOptions; 
 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

 		if (isset($this->error['paysite_mercid'])) {
			$this->data['error_mercid'] = $this->error['paysite_mercid'];
		} else {
			$this->data['error_mercid'] = '';
		}
		if (isset($this->error['paysite_secretkey'])) {
			$this->data['error_secretkey'] = $this->error['paysite_secretkey'];
		} else {
			$this->data['error_secretkey'] = '';
		}
		if (isset($this->error['paysite_publickey'])) {
			$this->data['error_publickey'] = $this->error['paysite_publickey'];
		} else {
			$this->data['error_publickey'] = '';
		}
 		
		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),      		
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_payment'),
			'href'      => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('payment/paysite', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);

		$this->data['action'] = $this->url->link('payment/paysite', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');
		
		if (isset($this->request->post['paysite_mercid'])) {
			$this->data['paysite_mercid'] = $this->request->post['paysite_mercid'];
		} else {
			$this->data['paysite_mercid'] = $this->config->get('paysite_mercid');
		}	
		if (isset($this->request->post['paysite_secretkey'])) {
			$this->data['paysite_secretkey'] = $this->request->post['paysite_secretkey'];
		} else {
			$this->data['paysite_secretkey'] = $this->config->get('paysite_secretkey');
		}	
		if (isset($this->request->post['paysite_publickey'])) {
			$this->data['paysite_publickey'] = $this->request->post['paysite_publickey'];
		} else {
			$this->data['paysite_publickey'] = $this->config->get('paysite_publickey');
		}	
	  if (isset($this->request->post['paysite_paymentoptions'])) {
			$prefix = '';
			$payment_types = $this->request->post['paysite_paymentoptions'];
				foreach ($payment_types as $payment_type)
				{
					$paymentList .= $prefix . $payment_type;
					$prefix = ',';
				}
			$this->data['paysite_paymentoptions'] = $paymentList;
		} else {
			$this->data['paysite_paymentoptions'] = $this->config->get('paysite_paymentoptions');
		}
	
		if (isset($this->request->post['paysite_debug'])) {
			$this->data['paysite_debug'] = $this->request->post['paysite_debug'];
		} else {
			$this->data['paysite_debug'] = $this->config->get('paysite_debug');
		}
								
		if (isset($this->request->post['paysite_pending_status_id'])) {
			$this->data['paysite_pending_status_id'] = $this->request->post['paysite_pending_status_id'];
		} else {
			$this->data['paysite_pending_status_id'] = $this->config->get('paysite_pending_status_id');
		}
									
		if (isset($this->request->post['paysite_processed_status_id'])) {
			$this->data['paysite_processed_status_id'] = $this->request->post['paysite_processed_status_id'];
		} else {
			$this->data['paysite_processed_status_id'] = $this->config->get('paysite_processed_status_id');
		}

		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['paysite_geo_zone_id'])) {
			$this->data['paysite_geo_zone_id'] = $this->request->post['paysite_geo_zone_id'];
		} else {
			$this->data['paysite_geo_zone_id'] = $this->config->get('paysite_geo_zone_id');
		}

		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['paysite_status'])) {
			$this->data['paysite_status'] = $this->request->post['paysite_status'];
		} else {
			$this->data['paysite_status'] = $this->config->get('paysite_status');
		}
		
		if (isset($this->request->post['paysite_sort_order'])) {
			$this->data['paysite_sort_order'] = $this->request->post['paysite_sort_order'];
		} else {
			$this->data['paysite_sort_order'] = $this->config->get('paysite_sort_order');
		}
			$this->template = 'payment/paysite.tpl';
					$this->children = array(
						'common/header',
						'common/footer'
					);
			$this->response->setOutput($this->render());
	}
		
	private function validate() {
		if (!$this->user->hasPermission('modify', 'payment/paysite')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['paysite_mercid']) {
			$this->error['paysite_mercid'] = $this->language->get('error_mercid');
		}
		if (!$this->request->post['paysite_secretkey']) {
			$this->error['paysite_secretkey'] = $this->language->get('error_secretkey');
		}
		if (!$this->request->post['paysite_publickey']) {
			$this->error['paysite_publickey'] = $this->language->get('error_publickey');
		}
	
		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}
}
?>