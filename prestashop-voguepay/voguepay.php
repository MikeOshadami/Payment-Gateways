<?php

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('_PS_VERSION_'))
    exit;

class VoguePay extends PaymentModule {

    const LEFT_COLUMN = 0;
    const RIGHT_COLUMN = 1;
    const FOOTER = 2;
    const DISABLE = -1;

    public function __construct() {
        $this->name = 'voguepay';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->currencies = true;
        $this->currencies_mode = 'radio';
        parent::__construct();
        $this->author = 'Oshadami Mike';
        $this->page = basename(__FILE__, '.php');
        $this->displayName = $this->l('VoguePay');
        $this->description = $this->l('Accept payments by credit card  both local and international buyers, quickly and securely with VoguePay.');
        $this->confirmUninstall = $this->l('Are you sure you want to delete your details ?');
    }

    public function install() {
 //       unlink(dirname(__FILE__) . '/../../cache/class_index.php');
        if (!parent::install()
                OR ! $this->registerHook('paymentOptions')
                OR ! $this->registerHook('paymentReturn')
                OR ! Configuration::updateValue('VOGUEPAY_MERCHANT_ID', '')) {
            return false;
        }


        return true;
    }

    public function uninstall() {
    //    unlink(dirname(__FILE__) . '/../../cache/class_index.php');
        return ( parent::uninstall()
                AND Configuration::deleteByName('VOGUEPAY_MERCHANT_ID')
                );
    }

    public function getContent() {
        global $cookie;
        $errors = array();
        $html = '<div style="width:550px">
            <p style="text-align:center;"><a href="http://voguepay.com/" target="_blank"><img src="' . __PS_BASE_URI__ . 'modules/voguepay/voguepay-logo.jpg" alt="VoguePay" boreder="0" /></a></p><br />';

        /* Update configuration variables */
        if (Tools::isSubmit('submitVoguepay')) {

            if (( $merchant_id = Tools::getValue('voguepay_merchant_id') ) AND preg_match('/[a-zA-Z0-9]/', $merchant_id)) {
                Configuration::updateValue('VOGUEPAY_MERCHANT_ID', $merchant_id);
            } else {
                $errors[] = '<div class="warning warn"><h3>' . $this->l('Merchant ID seems to be wrong') . '</h3></div>';
            }



            if (!sizeof($errors)) {
                //Tools::redirectAdmin( $currentIndex.'&configure=payfast&token='.Tools::getValue( 'token' ) .'&conf=4' );
            }


            foreach (array('displayLeftColumn', 'displayRightColumn', 'displayFooter') as $hookName)
                if ($this->isRegisteredInHook($hookName))
                    $this->unregisterHook($hookName);
            if (Tools::getValue('logo_position') == self::LEFT_COLUMN)
                $this->registerHook('displayLeftColumn');
            else if (Tools::getValue('logo_position') == self::RIGHT_COLUMN)
                $this->registerHook('displayRightColumn');
            else if (Tools::getValue('logo_position') == self::FOOTER)
                $this->registerHook('displayFooter');
            if (method_exists('Tools', 'clearSmartyCache')) {
                Tools::clearSmartyCache();
            }
        }
        /* Display errors */
        if (sizeof($errors)) {
            $html .= '<ul style="color: red; font-weight: bold; margin-bottom: 30px; width: 506px; background: #FFDFDF; border: 1px dashed #BBB; padding: 10px;">';
            foreach ($errors AS $error)
                $html .= '<li>' . $error . '</li>';
            $html .= '</ul>';
        }

        $blockPositionList = array(
            self::DISABLE => $this->l('Disable'),
            self::LEFT_COLUMN => $this->l('Left Column'),
            self::RIGHT_COLUMN => $this->l('Right Column'),
            self::FOOTER => $this->l('Footer'));

        if ($this->isRegisteredInHook('displayLeftColumn')) {
            $currentLogoBlockPosition = self::LEFT_COLUMN;
        } elseif ($this->isRegisteredInHook('displayRightColumn')) {
            $currentLogoBlockPosition = self::RIGHT_COLUMN;
        } elseif ($this->isRegisteredInHook('displayFooter')) {
            $currentLogoBlockPosition = self::FOOTER;
        } else {
            $currentLogoBlockPosition = -1;
        }
        /* Display settings form */
        $html .= '
        <form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
          <fieldset>
          <legend><img src="' . __PS_BASE_URI__ . 'modules/voguepay/logo.gif" />' . $this->l('Settings') . '</legend>
            <p>' . $this->l('You can find your Merchant ID in your VoguePay account > My Account > Integration.') . '</p>
            <label>
              ' . $this->l('Merchant ID') . '
            </label>
            <div class="margin-form">
              <input type="text" name="voguepay_merchant_id" value="' . Tools::getValue('voguepay_merchant_id', Configuration::get('VOGUEPAY_MERCHANT_ID')) . '" />
            </div>
            <div style="float:right;"><input type="submit" name="submitVoguepay" class="button" value="' . $this->l('   Save   ') . '" /></div><div class="clear"></div>
          </fieldset>
        </form>
        <br /><br />
        <fieldset>
          <legend><img src="../img/admin/warning.gif" />' . $this->l('Information') . '</legend>
          <p>- ' . $this->l('In order to use your VoguePay module, you must insert your VoguePay Merchant ID above.') . '</p>
       </fieldset>
        </div>';

        return $html;
    }

    private function _displayLogoBlock($position) {
        return '<div style="text-align:center;"><a href="http://voguepay.com/" target="_blank" title="Secure Payments With VoguePay"><img src="' . __PS_BASE_URI__ . 'modules/voguepay/secure_logo.png" width="150" /></a></div>';
    }

    public function hookDisplayRightColumn($params) {
        return $this->_displayLogoBlock(self::RIGHT_COLUMN);
    }

    public function hookDisplayLeftColumn($params) {
        return $this->_displayLogoBlock(self::LEFT_COLUMN);
    }

    public function hookDisplayFooter($params) {
        $html = '<section id="voguepay_footer_link" class="footer-block col-xs-12 col-sm-2">        
        <div style="text-align:center;"><a href="http://voguepay.com/" target="_blank" title="Secure Payments With VoguePay"><img src="' . __PS_BASE_URI__ . 'modules/voguepay/secure_logo.png"  /></a></div>  
        </section>';
        return $html;
    }

    //new method
    public function hookPaymentOptions($params) {
        if (!$this->active) {
            return;
        }
        $payment_options = [
            $this->getCardPaymentOption()
        ];

        return $payment_options;
    }

    public function getCardPaymentOption() {
        global $cookie, $cart;

        // Buyer details
        $customer = new Customer((int) ($cart->id_customer));

        $toCurrency = new Currency(Currency::getIdByIsoCode('ZAR'));
        $fromCurrency = new Currency((int) $cookie->id_currency);

        $total = $cart->getOrderTotal();

        $pfAmount = Tools::convertPriceFull($total, $fromCurrency, $toCurrency);

        $data = array();

        $currency = $this->getCurrency((int) $cart->id_currency);
        if ($cart->id_currency != $currency->id) {
            $cart->id_currency = (int) $currency->id;
            $cookie->id_currency = (int) $cart->id_currency;
            $cart->update();
        }

        $data['info']['merchant_id'] = Configuration::get('VOGUEPAY_MERCHANT_ID');
        $data['voguepay_url'] = 'https://voguepay.com/pay/';

        // Create URLs
        $data['info']['return_url'] = $this->context->link->getPageLink('order-confirmation', null, null, 'key=' . $cart->secure_key . '&id_cart=' . (int) ($cart->id) . '&id_module=' . (int) ($this->id));
        $data['info']['cancel_url'] = Tools::getHttpHost(true) . __PS_BASE_URI__;
        $data['info']['notify_url'] = Tools::getHttpHost(true) . __PS_BASE_URI__ . 'modules/voguepay/validation.php';
		$responseurl = $this->context->link->getModuleLink('voguepay', 'response', array(), true);
     //   $data['info']['notify_url'] ="http://google.com";
        $data['info']['name_first'] = $customer->firstname;
        $data['info']['name_last'] = $customer->lastname;
        $data['info']['email_address'] = $customer->email;
        $data['info']['m_payment_id'] = $cart->id;
        $data['info']['amount'] = number_format(sprintf("%01.2f", $pfAmount), 2, '.', '');
        $data['info']['item_name'] = Configuration::get('PS_SHOP_NAME') . ' purchase, Cart Item ID #' . $cart->id;
        $data['info']['custom_int1'] = $cart->id;
        $data['info']['custom_str1'] = $cart->secure_key;
        $uniqueRef = uniqid();
	    $voguePayOrderId = $uniqueRef.'_'.$cart->id.'_'.$cart->secure_key;
        $data['info']['merchant_ref'] = $voguePayOrderId;
        //create the payment option object
        $externalOption = new PaymentOption();
        $externalOption->setCallToActionText($this->l(''))
                ->setAction($data['voguepay_url']) //link to voguepay
                ->setInputs([ //voguepay values
                    'v_merchant_id' => [
                        'name' => 'v_merchant_id',
                        'type' => 'hidden',
                        'value' => $data['info']['merchant_id'],
                    ],
                    'success_url' => [
                        'name' => 'success_url',
                        'type' => 'hidden',
                        'value' => $responseurl,
                    ],
                    'fail_url' => [
                        'name' => 'fail_url',
                        'type' => 'hidden',
                        'value' => $data['info']['cancel_url'],
                    ],
                    'notify_url2222' => [
                        'name' => 'notify_url222',
                        'type' => 'hidden',
                        'value' => $data['info']['notify_url'],
                    ],
                    'name_first' => [
                        'name' => 'name_first',
                        'type' => 'hidden',
                        'value' => $data['info']['name_first'],
                    ],
                    'name_last' => [
                        'name' => 'name_last',
                        'type' => 'hidden',
                        'value' => $data['info']['name_last'],
                    ],
                    'email_address' => [
                        'name' => 'email_address',
                        'type' => 'hidden',
                        'value' => $data['info']['email_address'],
                    ],
                    'merchant_ref' => [
                        'name' => 'merchant_ref',
                        'type' => 'hidden',
                        'value' => $data['info']['merchant_ref'],
                    ],
                    'total' => [
                        'name' => 'total',
                        'type' => 'hidden',
                        'value' => $data['info']['amount'],
                    ],
                    'memo' => [
                        'name' => 'memo',
                        'type' => 'hidden',
                        'value' => $data['info']['item_name'],
                    ],
                    'custom_int1' => [
                        'name' => 'custom_int1',
                        'type' => 'hidden',
                        'value' => $data['info']['custom_int1'],
                    ],
                    'custom_str1' => [
                        'name' => 'custom_str1',
                        'type' => 'hidden',
                        'value' => $data['info']['custom_str1'],
                    ],
                ])
                ->setAdditionalInformation($this->context->smarty->fetch('module:voguepay/payment_info.tpl'))
                ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/voguepay-logo.jpg'));

        return $externalOption;
    }

    public function hookPaymentReturn($params) {
        if (!$this->active) {
            return;
        }
        $test = __FILE__;
        $state = $params['order']->getCurrentState();
            $this->smarty->assign(array(
                'shop_name' => $this->context->shop->name,
                'total' => Tools::displayPrice(
                    $params['order']->getOrdersTotalPaid(),
                    new Currency($params['order']->id_currency),
                    false
                ),
                'status' => 'ok',
                'contact_url' => $this->context->link->getPageLink('contact', true)
            ));

      //  return $this->display($test, 'voguepay_success.tpl');
         return $this->fetch('module:voguepay/voguepay_success.tpl');
    }

}
