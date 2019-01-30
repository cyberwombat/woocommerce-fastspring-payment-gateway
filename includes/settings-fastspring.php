<?php
if (!defined('ABSPATH')) {
    exit;
}

return apply_filters(
    'woocommerce-gateway-fastspring_settings',

  array(
    'enabled' => array(
      'title' => __('Enable/Disable', 'woocommerce-gateway-fastspring'),
      'label' => __('Enable FastSpring payment gateway', 'woocommerce-gateway-fastspring'),
      'type' => 'checkbox',
      'description' => '',
      'default' => 'no',
    ),
    'title' => array(
      'title' => __('Title', 'woocommerce-gateway-fastspring'),
      'type' => 'text',
      'description' => __('The title which the user sees during checkout. Keep it brief to avoid potential layout issues with icons.', 'woocommerce-gateway-fastspring'),
      'default' => __('Pay with FastSpring', 'woocommerce-gateway-fastspring'),
      'desc_tip' => false,
    ),
    'description' => array(
      'title' => __('Description', 'woocommerce-gateway-fastspring'),
      'type' => 'text',
      'description' => __('The lengthier description which the user sees once selecting this payment gateway.', 'woocommerce-gateway-fastspring'),
      'default' => __('Pay with credit card, PayPal, Amazon Pay and more.', 'woocommerce-gateway-fastspring'),
      'desc_tip' => false,
    ),
    'icons' => array(
      'title' => __('Payment Icons', 'woocommerce-gateway-fastspring'),
      'type' => 'multiselect',
      'description' => __('Select the payment method icons to show.', 'woocommerce-gateway-fastspring'),
      'default' => array( 'paypal', 'visa', 'mastercard', 'amex' ),
      'desc_tip' => false,
      'class'       => 'wc-enhanced-select',
      'options' => array(
        'paypal' => 'paypal',
        'visa' => 'visa',
        'mastercard' => 'mastercard',
        'amex' => 'amex',
        'discover' => 'discover',
        'jcb' => 'jcb',
        'diners' => 'diners',
        'ideal' => 'ideal',
        'unionpay' => 'unionpay',
        'sofort' => 'sofort',
        'giropay' => 'giropay',
      )
    ),
    'testmode' => array(
      'title' => __('Test mode', 'woocommerce-gateway-fastspring'),
      'label' => __('Enable Test Mode', 'woocommerce-gateway-fastspring'),
      'type' => 'checkbox',
      'description' => __('Places the payment gateway in test mode. In this mode, you can use the card numbers provided in the test panel of the FastSpring dashboard. Please check the documentation "<a target="_blank" href="http://docs.fastspring.com/activity-events-orders-and-subscriptions/test-orders">Testing Orders</a>" for more information.', 'woocommerce-gateway-fastspring'),
      'default' =>  'no',
      'desc_tip' => false,
    ),
    'logging' => array(
      'title' => __('Logging', 'woocommerce-gateway-fastspring'),
      'label' => __('Log debug messages', 'woocommerce-gateway-fastspring'),
      'type' => 'checkbox',
      'description' => __('Save debug messages to the WooCommerce System Status log.', 'woocommerce-gateway-fastspring'),
      'default' =>  'no',
    ),
    'storefront_path' => array(
      'title' => __('Storefront', 'woocommerce-gateway-fastspring'),
      'type' => 'text',
      'description' => __('The path of your live FastSpring storefront (Ex: mystore.onfastspring.com/mystore). This plugin handles either hosted or popup storefronts.', 'woocommerce-gateway-fastspring)'),
      'desc_tip' => false,
    ),
    'billing_address' => array(
      'title' => __('Billing Address Fields', 'woocommerce-gateway-fastspring'),
      'label' => __('Remove billing address fields from checkout.', 'woocommerce-gateway-fastspring'),
      'type' => 'checkbox',
      'description' => __('If no other payment gateway requires these fields they can be removed as FastSpring does not use them.'),
      'default' => 'yes',
    ),
    'api_details' => array(
      'title' => __('Access Credentials', 'woocommerce-gateway-fastspring'),
      'type' => 'title',
      'description' => __('Your Access Key and Private Key are used to encrypt the order infomation being sent to FastSpring.  Refer to the <a target="_blank" href="http://docs.fastspring.com/integrating-with-fastspring/store-builder-library/passing-sensitive-data-with-secure-requests">documentation</a> under the <i>Generating "securePayload" and "secureKey"</i> section for instructions on creating an SSL certificate and private/public keys. Once generated, enter the private key below and the public key in the FastSping dashboard under <i>Integrations > Store Builder Library</i> where you can also obtain the Access Key to enter below.', 'woocommerce-gateway-fastspring'),
    ),
    'access_key' => array(
      'title' => __('Access Key', 'woocommerce-gateway-fastspring'),
      'type' => 'text',
      'description' => __('Your FastSpring access key.', 'woocommerce-gateway-fastspring'),
      'desc_tip' => false,
    ),
    'private_key' => array(
      'title' => __('Private Key', 'woocommerce-gateway-fastspring'),
      'type' => 'textarea',
      'description' => __('RSA private certificate.', 'woocommerce-gateway-fastspring'),
      'desc_tip' => false,
    ),
    'order_verification' => array(
      'title' => __('Order Verification', 'woocommerce-gateway-fastspring'),
      'type' => 'title',
      'description' => __('In order to allow FastSpring to mark orders as completed within WooCommerce you can either use a Webhook or the FastSpring API. if you are using a hosted storefront you must use the webhook method. If, instead, you are using a popup storefront you may use either a webhook or an API call (or both). <h4>Webhook Method Instructions</h4>In order to use the webhook method, generate a secret below and enter it along with your webhook URL (<code>' . site_url('?wc-api=wc_gateway_fastspring', 'https') . '</code>) in the FastSpring dashboard under <i>Integrations > Webhooks</i> in the HMAC SHA256 Secret and URL fields respectively.<h4>API Method Instructions</h4>To use the API verification method enter your API username and password below. These can be generated from the FastSpring dashboard under <i>Integrations > API Credentials</i>.', 'woocommerce-gateway-fastspring'),
    ),
    'webhook_secret' => array(
      'title' => __('Webhook Secret', 'woocommerce-gateway-fastspring'),
      'type' => 'text',
      'description' => __('A webhook secret is a random sequence of characters used to authenticate webhook calls.<br>The defaults were automatically generated for you as a convenience.', 'woocommerce-gateway-fastspring'),
      'desc_tip' => false,
      'default' => substr(str_shuffle(MD5(microtime())), 0, 30),
    ),
    'api_username' => array(
      'title' => __('API Username', 'woocommerce-gateway-fastspring'),
      'type' => 'text',
      'description' => __('Your FastSpring API username.', 'woocommerce-gateway-fastspring'),
      'desc_tip' => false,
    ),
    'api_password' => array(
      'title' => __('API Password', 'woocommerce-gateway-fastspring'),
      'type' => 'password',
      'description' => __('Your FastSpring API password.', 'woocommerce-gateway-fastspring'),
      'desc_tip' => false,
    ),

  )
);
