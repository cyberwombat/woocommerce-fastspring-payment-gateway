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
  }

  /**
   * Validate access key settings field
   *
   * @params $value
   */
  public function validate_access_key_field($key, $value) {
    if (!empty($value)) {
      return $value;
    }
    WC_Admin_Settings::add_error(esc_html__('A FastSpring access key is required.', 'woocommerce-gateway-fastspring'));

  }

  /**
   * Validate private key settings field
   *
   * @params $value
   */
  public function validate_private_key_field($key, $value) {

    if (strpos($value, '-----BEGIN RSA PRIVATE KEY-----') !== false) {
      return $value;
    }
    WC_Admin_Settings::add_error(esc_html__('The RSA private key field is invalid.', 'woocommerce-gateway-fastspring'));

  }

  /**
   * Validate title settings field
   *
   * @params $value
   */
  public function validate_title_field($key, $value) {
    if (empty($value)) {
      WC_Admin_Settings::add_error(esc_html__('Enter a valid title.', 'woocommerce-gateway-fastspring'));
    }
    return $value;
  }

  /**
   * Validate hosted storefront path settings field
   *
   * @params $value
   */
  public function validate_hosted_storefront_path_field($key, $value) {
    if (!isset($_POST['woocommerce_fastspring_popup']) && empty($value)) {
      WC_Admin_Settings::add_error(esc_html__('Enter a valid hosted storefront path.', 'woocommerce-gateway-fastspring'));
    } else if (!empty($value)) {
      return preg_replace('#^https?://#', '', rtrim($value, '/'));
    }
  }

  /**
   * Validate popup storefront path settings field
   *
   * @params $value
   */
  public function validate_storefront_path_field($key, $value) {

    if (isset($_POST['woocommerce_fastspring_popup']) && empty($value)) {
      WC_Admin_Settings::add_error(esc_html__('Enter a valid popup storefront path.', 'woocommerce-gateway-fastspring'));
    } else if (!empty($value)) {
      return preg_replace('#^https?://#', '', rtrim($value, '/'));
    }
  }

  /**
   * Check if this gateway is enabled
   */
  public function is_available() {

    if (!$this->option('enabled')) {
      return false;
    }

    if ($this->option('access_key') && $this->option('private_key') && ($this->option('popup') && $this->option('storefront_path')) || (!$this->option('popup') && $this->option('hosted_storefront_path'))) {
      return true;
    }
    return false;
  }

  /**
   * Initialise gateway settings form fields
   */
  public function init_form_fields() {
    $this->form_fields = include 'settings-fastspring.php';
  }

  /**
   * Payment_scripts function.
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

    if ($this->option('title')) {
      echo '<p>' . sprintf(__('Thank you for your order, please click the button below to pay using %s.', 'woocommerce'), $this->option('title')) . '</p>';
    }

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
