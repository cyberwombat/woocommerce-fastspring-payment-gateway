<?php
if (!defined('ABSPATH')) {
  exit;
}

/**
 * WC_Gateway_FastSpring_Builder class.
 *
 * Povides FS builder payload building functionality
 *
 */
class WC_Gateway_FastSpring_Builder {

  /**
   * Fetch plugin option
   *
   * @param $o Option key
   * @return Option value
   */
  protected static function get_option($o) {
    $options = get_option('woocommerce_fastspring_settings', array());
    return $options[$o];
  }

  /**
   * Calculates discounted item price based on overall discount share
   *
   * @return float
   */
  public function get_discount_item_amount($amount) {
    $total = WC()->cart->subtotal;
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
            get_woocommerce_currency() => self::get_discount_item_amount($amount),
          ],
        ],
        // customer-visible product display name or title
        'display' => [
          'en' => $product->get_name(),
        ],
        'description' => [
          'summary' => [
            'en' => $product->get_short_description(),
          ],
          'full' => [
            'en' => $product->get_short_description(),
          ],
        ],
        'image' => self::get_image($product->get_image_id()),
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

    if (!$order_id) {
      return [];
    }

    // Set another session var that wont get erased - we need that for receipt
    // We sometimes get a race condition
    WC()->session->set('current_order', $order_id);

    $order = wc_get_order($order_id);

    return [

      'email' => $order->get_billing_email(),
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
   * Returns order ID as tag array for FS reference
   *
   * @return array
   */
  public function get_order_tags() {
    return array("store_order_id" => absint(WC()->session->get('order_awaiting_payment')));
  }

  /**
   * Builds JSON payload
   *
   * @return object
   */
  public function get_json_payload() {
    return array(
      'tags' => self::get_order_tags(),
      'contact' => self::get_cart_customer_details(),
      'items' => self::get_cart_items(),
    );
  }

  /**
   * Builds encrypted JSON payload
   *
   * @return object
   */
  public function get_secure_json_payload() {

    $debug = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG;

    //$debug = false;
    $aes_key = self::aes_key_generate();
    $payload = self::get_json_payload();
    $encypted = self::encrypt_payload($aes_key, $payload);
    $key = self::encrypt_key($aes_key);

    return $debug ? [
      'payload' => $payload,
      'key' => '',

    ] : [
      'payload' => $encypted,
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
    $private_key = openssl_pkey_get_private(self::get_option('private_key'));
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

}
