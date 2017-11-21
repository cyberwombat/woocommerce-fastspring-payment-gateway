<?php
if (!defined('ABSPATH')) {
  exit;
}

/**
 * WC_Gateway_FastSpring class.
 *
 * @extends WC_Payment_Gateway
 */
class WC_Gateway_FastSpring extends WC_Payment_Gateway {

  /**
   * Constructor
   */
  public function __construct() {

    $this->id = 'fastspring';
    $this->method_title = __('FastSpring', 'woocommerce-gateway-fastspring');
    $this->method_description = __('The FastSpring payment plugin provides hosted checkout payment processing from FastSpring via a popup. ');
    $this->has_fields = true;
    $this->supports = array(

    );

    // Load the form fields.
    $this->init_form_fields();

    // Load the settings.
    $this->init_settings();

    // Get setting values.
    $this->title = $this->option('title');
    $this->description = $this->option('description');

    if ($this->option('enabled')) {
      $this->order_button_text = __('Continue to payment', 'woocommerce-gateway-fastspring');
    }

    if ($this->option('testmode')) {
      $this->description .= "\n" . sprintf(__('TEST MODE ENABLED. In test mode, you can use the card numbers provided in the test panel of the FastSpring dashboard. Please check the documentation "<a target="_blank" href="%s">Testing Orders</a>" for more information.', 'woocommerce-gateway-fastspring'), 'http://docs.fastspring.com/activity-events-orders-and-subscriptions/test-orders');

      $this->description = trim($this->description);
    }

    // Hooks.
    add_action('wc_ajax_wc_fastspring_order_complete', array($this, 'ajax_order_complete'));
    add_action('wp_enqueue_scripts', array($this, 'payment_scripts'));
    add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
    add_action('woocommerce_receipt_' . $this->id, array($this, 'payment_page'));
    add_action('woocommerce_api_wc_gateway_fastspring_commerce', array($this, 'return_handler'));
    //add_filter( 'woocommerce_payment_complete_order_status', array($this, 'filter_woocommerce_payment_complete_order_status'), 10, 1 );

  }

  /**
   * Mark complete after payment
   */
   public function filter_woocommerce_payment_complete_order_status( $order_id ) {
      return 'completed';
  }
 
  /**
   * Check if this gateway is enabled
   */
  public function is_available() {

    if (!$this->option('enabled')) {
      return false;
    }

    if (!$this->option('access_key') || !$this->option('private_key') || !$this->option('storefront_path')) {
      return false;
    }

    return true;

  }

  /**
   * Initialise gateway settings form fields
   */
  public function init_form_fields() {
    $this->form_fields = include 'settings-fastspring.php';
  }

 
  /**
   * payment_scripts function.
   *
   * Outputs scripts used for fastspring payment
   */
  public function payment_scripts() {

    $load_scripts = false;

    if (is_checkout()) {
      $load_scripts = true;
    }
    if ($this->is_available()) {
      $load_scripts = true;
    }

    if (false === $load_scripts) {
      return;
    }

    $suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

    if ($this->option('enabled')) {

      wp_enqueue_script('fastspring', WC_FASTSPRING_SCRIPT, '', false, true);

      wp_enqueue_script('woocommerce_fastspring', plugins_url('assets/js/fastspring-checkout' . $suffix . '.js', WC_FASTSPRING_MAIN_FILE), array('jquery', 'fastspring'), WC_FASTSPRING_VERSION, true);

    }

    $fastspring_params = array(
      'ajax_url' => WC_AJAX::get_endpoint('%%endpoint%%'),
      'nonce' => array(
        'receipt' => wp_create_nonce('wc-fastspring-receipt'),
      ),
    );

    wp_localize_script('woocommerce_fastspring', 'woocommerce_fastspring_params', apply_filters('woocommerce_fastspring_params', $fastspring_params));
  }

  /**
   * Process the payment.
   *
   * @param int $order_id
   *
   * @return array|void
   */
  public function process_payment($order_id) {
    $order = wc_get_order($order_id);

    return array(
      'result' => 'success',
      'redirect' => $order->get_checkout_payment_url(true),
    );

  }

  /**
   * Options
   *
   * @param string $option option name
   * @return mixed option value
   */
  public function option($option) {
    return WC_FastSpring::get_option($option);
  }

  /**
   * Logs
   *
   * @param string $message
   */
  public function log($message) {
    WC_FastSpring::log($message);
  }

  /**
   * Calculates discountd item price based on overall discount share
   *
   * @return float
   */
  public function get_discount_item_amount($amount) {

    $total = WC()->cart->cart_contents_total;
    $discount = WC()->cart->discount_cart;

    return $amount > 0 ? $amount - $discount / ($total / $amount) : $amount;
  }

  /**
   * Builds cart payload
   *
   * @return object
   */
  public function get_cart_items() {

    $items = array();

    foreach (WC()->cart->cart_contents as $cart_item_key => $values) {
      $amount = $values['line_subtotal'] / $values['quantity'];

      $product = $values['data'];



      $item = array(
        'product' => $product->get_slug(),
        'quantity' => $values['quantity'],
        'pricing' => [
          'quantityBehavior' => 'lock',
          'price' => [
            'USD' => $this->get_discount_item_amount($amount),
          ],
        ],
        // customer-visible product display name or title
        'display' => [
          'en' => $values['quantity'] . ' ' . $product->get_name(),
        ],
        'description' => [
          'summary' => [
            'en' => $product->get_short_description(),
          ],
          'full' => [
            'en' => $product->get_short_description(),
          ],
        ],
        'image' => $this->get_image($product->get_image_id()),
        'removable' => false, // Boolean - controls whether or not product can be removed from the cart by the customer
        'sku' => $product->get_sku(), // String - optional product SKU ID (e.g. to match your internal SKU or product ID)

      );

      $items[] = $item;
    }

    return $items;

  }

  /**
   * Gets a product image URL
   *
   * @return string
   */
  public function get_image($id, $size = 'shop_thumbnail', $attr = array(), $placeholder = true) {

    $data = wp_get_attachment_image_src($id, $size);

    if ($data) {
      $image = $data[0];
    } elseif ($placeholder) {
      $image = wc_placeholder_img($size);
    } else {
      $image = '';
    }
    return str_replace(array('https://', 'http://'), '//', $image);
  }

  /**
   * Get cart info
   *
   * @return object
   */
  public function get_cart_customer_details() {


    $order_id = absint(WC()->session->get('order_awaiting_payment'));

    // Set another session var that wont get erased - we need that for receipt
    // We sometimes get a race condition
    WC()->session->set( 'current_order', $order_id);

    $order = wc_get_order($order_id);

    return [

      'email' => 'user_' . rand(10000, 99999) . '@software4recording.com', //$order->get_billing_email(),
      'firstName' => $order->get_billing_first_name(),
      'lastName' => $order->get_billing_last_name(),
      'company' => $order->get_billing_company(),
      'addressLine1' => $order->get_billing_address_1(),
      'addressLine2' => $order->get_billing_address_2(),
      'region' => $order->get_billing_state(),
      'city' => $order->get_billing_city(),
      'postalCode' => $order->get_billing_postcode(),
      'country' => $order->get_billing_country(),
      'phoneNumber' => $order->get_billing_phone(),
    ];
  }

  /**
   * Builds JSON payload
   *
   * @return object
   */
  public function get_json_payload() {
    return array(
      'contact' => $this->get_cart_customer_details(),
      'items' => $this->get_cart_items(),
    );
  }

  /**
   * Builds encrypted JSON payload
   *
   * @return object
   */
  public function get_secure_json_payload() {

    $aes_key = $this->aes_key_generate();
    $payload = $this->encrypt_payload($aes_key, json_encode($this->get_json_payload()));
    $key = $this->encrypt_key($aes_key);

    return [
      'payload' => $payload,
      'key' => $key,

    ];
  }

  /**
   * Encrypts payload
   *
   * @param object
   * @return string
   */
  public function encrypt_payload($aes_key, $string) {
    $cipher = openssl_encrypt($string, "AES-128-ECB", $aes_key, OPENSSL_RAW_DATA);
    return base64_encode($cipher);

  }

  /**
   * Create secure key
   *
   * @param object
   * @return string
   */
  public function encrypt_key($aes_key) {
    $private_key = openssl_pkey_get_private($this->option('private_key'));
    openssl_private_encrypt($aes_key, $aes_key_encrypted, $private_key);
    return base64_encode($aes_key_encrypted);
  }

  /**
   * Generate AES key
   *
   * @return string
   */
  public function aes_key_generate() {
    return openssl_random_pseudo_bytes(16);
  }

  /**
   * Payment form on checkout page.
   */
  public function payment_fields() {
    $description = $this->get_description();

    if ($description) {
      echo wpautop(wptexturize(trim($description)));
    }

  }

  /**
   * Payment page (actually the receipt)
   *
   * @param  int $order_id
   */
  public function payment_page($order_id) {
    $order = wc_get_order($order_id);

    echo '<p>' . sprintf(__('Thank you for your order, please click the button below to pay using %s.', 'woocommerce'), $this->option('title')) . '</p>';

    $json = $this->get_secure_json_payload();

    echo '<script>
        jQuery( function () {
          var fscSession = ' . json_encode($json) . '
          fastspring.builder.secure(fscSession.payload, fscSession.key)
        });
        </script>';

    echo '<button class="button alt" data-fsc-action="Checkout">' . __('Enter payment info', 'woocommerce') . '</button> <a class="button cancel" href="' . esc_url($order->get_cancel_order_url()) . '">' . __('Cancel order &amp; restore cart', 'woocommerce') . '</a>';
  }


}
