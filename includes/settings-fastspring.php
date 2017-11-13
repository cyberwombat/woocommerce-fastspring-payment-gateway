<?php
if (!defined('ABSPATH')) {
  exit;
}

return apply_filters('woocommerce-gateway-fastspring_settings',

  array(
    'enabled' => array(
      'title' => __('Enable/Disable', 'woocommerce-gateway-fastspring'),
      'label' => __('Enable FastSpring payment gateway.', 'woocommerce-gateway-fastspring'),
      'type' => 'checkbox',
      'description' => '',
      'default' => 'no',
    ),
    'title' => array(
      'title' => __('Title', 'woocommerce-gateway-fastspring'),
      'type' => 'text',
      'description' => __('This controls the title which the user sees during checkout.', 'woocommerce-gateway-fastspring'),
      'default' => __('Credit Card (FastSpring)', 'woocommerce-gateway-fastspring'),
      'desc_tip' => false,
    ),
    'description' => array(
      'title' => __('Description', 'woocommerce-gateway-fastspring'),
      'type' => 'text',
      'description' => __('This controls the description which the user sees during checkout.', 'woocommerce-gateway-fastspring'),
      'default' => __('Pay with your credit card via FastSpring.', 'woocommerce-gateway-fastspring'),
      'desc_tip' => false,
    ),
    'testmode' => array(
      'title' => __('Test mode', 'woocommerce-gateway-fastspring'),
      'label' => __('Enable Test Mode', 'woocommerce-gateway-fastspring'),
      'type' => 'checkbox',
      'description' => __('Place the payment gateway in test mode.', 'woocommerce-gateway-fastspring'),
      'default' => 'no',
      'desc_tip' => false,
    ),
    'logging' => array(
      'title' => __('Logging', 'woocommerce-gateway-fastspring'),
      'label' => __('Log debug messages', 'woocommerce-gateway-fastspring'),
      'type' => 'checkbox',
      'description' => __('Save debug messages to the WooCommerce System Status log.', 'woocommerce-gateway-fastspring'),
      'default' => 'no',
    ),
  
    'storefront_path' => array(
      'title' => __('Storefront Path', 'woocommerce-gateway-fastspring'),
      'type' => 'text',
      'description' => __('The path of your live FastSpring popup storefront (Ex: mystore.onfastspring.com/mypopup.', 'woocommerce-gateway-fastspring).'),
      'desc_tip' => false,
    ),
    'billing_address' => array(
      'title' => __('Billing Address Fields', 'woocommerce-gateway-fastspring'),
      'label' => __('Remove billing address fields from checkout', 'woocommerce-gateway-fastspring'),
      'type' => 'checkbox',
      'description' => __('If no other payment gateway requires these fields they can be removed as FastSpring does not use them.'),
      'default' => 'yes',
    ),
    'webhook_details' => array(
      'title' => __('Webhooks', 'woocommerce-gateway-fastspring'),
      'type' => 'title',
      'description' => __('Setup your webhook URL (<i>' . site_url('?wc-api=wc_gateway_fastspring', 'https') . '</i>) and SHA256 secret in the FastSpring dashboard under Integrations > Webhooks.'),
    ),
    'webhook_secret' => array(
      'title' => __('Webhook Secret', 'woocommerce-gateway-fastspring'),
      'type' => 'text',
      'description' => __('Optional but recommended webhook secret.', 'woocommerce-gateway-fastspring'),
      'desc_tip' => false,
    ),
    'api_details' => array(
      'title' => __('Access Credentials', 'woocommerce-gateway-fastspring'),
      'type' => 'title',
      'description' => sprintf(__('Obtain your access key and upload your public certificate in FastSpring dashboard under Integrations > Store Builder Library.  Refer to the <a target="_blank" href="%s">documentation</a> to learn how to generate your private/public keys.', 'woocommerce-gateway-fastspring'), 'http://docs.fastspring.com/integrating-with-fastspring/store-builder-library/passing-sensitive-data-with-secure-requests'),
    ),
    'access_key' => array(
      'title' => __('Access Key', 'woocommerce-gateway-fastspring'),
      'type' => 'text',
      'description' => __('Your FastSpring access key.', 'woocommerce-gateway-fastspring'),
      'desc_tip' => false,
    ),
    'private_key' => array(
      'title' => __('RSA Private Key', 'woocommerce-gateway-fastspring'),
      'type' => 'textarea',
      'description' => __('SSL private certificate.', 'woocommerce-gateway-fastspring'),
      'desc_tip' => false,
    ),

  )
);
