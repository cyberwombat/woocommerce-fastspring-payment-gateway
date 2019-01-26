<?php
/*
 * Plugin Name: WooCommerce FastSpring Gateway
 * Description: Accept credit card, PayPal, Amazon Pay and other payments on your store using FastSpring.
 * Author: Enradia
 * Author URI: https://enradia.com/
 * Version: 1.2.2
 * Requires at least: 4.4
 * Tested up to: 4.9.6
 * WC requires at least: 3.0
 * WC tested up to: 3.4
 * Text Domain: woocommerce-gateway-fastspring
 *
 */

if (!defined('ABSPATH')) {
    exit;
}


/**
 * Required minimums and constants
 */
define('WC_FASTSPRING_VERSION', '1.2.2');
define('WC_FASTSPRING_SCRIPT', 'https://d1f8f9xcsvx3ha.cloudfront.net/sbl/0.7.6/fastspring-builder.min.js');
define('WC_FASTSPRING_MIN_PHP_VER', '5.6.0');
define('WC_FASTSPRING_MIN_WC_VER', '3.0.0');
define('WC_FASTSPRING_MAIN_FILE', __FILE__);
define('WC_FASTSPRING_PLUGIN_URL', plugins_url( '', __FILE__ ));

if (!class_exists('WC_FastSpring')):

  class WC_FastSpring
  {

    /**
     * @var Singleton The reference the *Singleton* instance of this class
     */
      private static $instance;

      /**
       * @var Reference to logging class.
       */
      private static $log;

      /**
       * @var array Plugin $settings
       */
      private static $settings;

      /**
       * Returns the *Singleton* instance of this class.
       *
       * @return Singleton The *Singleton* instance.
       */
      public static function get_instance()
      {
          if (null === self::$instance) {
              self::$instance = new self();
          }
          return self::$instance;
      }

      /**
       * Private clone method to prevent cloning of the instance of the
       * *Singleton* instance.
       *
       * @return void
       */
      private function __clone()
      {
      }

      /**
       * Private unserialize method to prevent unserializing of the *Singleton*
       * instance.
       *
       * @return void
       */
      private function __wakeup()
      {
      }

      /**
       * Notices (array)
       * @var array
       */
      public $notices = array();

      /**
       * Protected constructor to prevent creating a new instance of the
       * *Singleton* via the `new` operator from outside of this class.
       */
      protected function __construct()
      {
          add_action('admin_init', array($this, 'check_environment'));
          add_action('admin_notices', array($this, 'admin_notices'), 15);
          add_action('plugins_loaded', array($this, 'init'));
          self::set_settings();
      }

      /**
       * Fetch plugin option
       *
       * @param $o Option key
       * @return mixed option value
       */
      public static function get_setting($o)
      {
          return isset(self::$settings[$o]) ? (self::$settings[$o] === 'yes' ? true : (self::$settings[$o] === 'no' ? false : self::$settings[$o])) : null;
      }

      /**
       * Set plugin option
       */
      public static function set_settings()
      {
          self::$settings  = get_option('woocommerce_fastspring_settings', array());
      }

      /**
       * Fetch storefront path based on live/test mode
       *
       * @return string storefront path
       */
      protected function get_storefront_path()
      {
          $path = self::get_setting('storefront_path');
          return self::get_setting('testmode')
      ? (strrpos($path, 'test.onfastspring.com')
        ? $path
        : str_replace('onfastspring.com', 'test.onfastspring.com', $path))
      : str_replace('test.onfastspring.com', 'onfastspring.com', $path);
      }

      /**
       * Load scripts hook handler
       *
       * @param $tag script tag
       * @param $tahandleg script handle
       * @return string tag
       */
      public function modify_loading_scripts($tag, $handle)
      {
          if ('fastspring' === $handle) {
              $debug = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? 'true' : 'false';
              return str_replace(' src', ' id="fsc-api" data-storefront="' . $this->get_storefront_path() . '" data-before-requests-callback="fastspringBeforeRequestHandler" data-access-key="' . self::get_setting('access_key') . '" '. ($debug ? 'data-debug="true" data-test="' . (self::get_setting('testmode') ? 'yes' : 'no') . '" data-version="' . WC_FASTSPRING_VERSION .'" data-data-callback="dataCallbackFunction" data-error-callback="errorCallback"' : '') . ' data-popup-closed="fastspringPopupCloseHandler" src', $tag);
          }
          return $tag;

          // Possible FastSpring script tag values
          /*
            id="fsc-api"
            src="https://d1f8f9xcsvx3ha.cloudfront.net/sbl/0.7.3/fastspring-builder.min.js" type="text/javascript"
              data-storefront="vendor.test.onfastspring.com"
            data-data-callback="dataCallbackFunction"
            data-error-callback="errorCallback"
            data-before-requests-callback="beforeRequestsCallbackFunction"
            data-after-requests-callback="afterRequestsCallbackFunction"
            data-before-markup-callback="beforeMarkupCallbackFunction"
            data-after-markup-callback="afterMarkupCallbackFunction"
            data-decorate-callback="decorateURLFunction"
            data-popup-event-received="popupEventReceived"
            data-popup-webhook-received="popupWebhookReceived"
            data-popup-closed="onPopupClose"
            data-access-key=".. access key .."
            data-debug="true"
            data-continuous="true"
          */
      }

      /**
       * Init the plugin after plugins_loaded so environment variables are set.
       */
      public function init()
      {
          // Don't hook anything else in the plugin if we're in an incompatible environment
          if (self::get_environment_warning()) {
              return;
          }

          if (!class_exists('WC_Payment_Gateway')) {
              return;
          } else {
              include_once dirname(__FILE__) . '/includes/class-wc-gateway-fastspring.php';
          }

          load_plugin_textdomain('woocommerce-gateway-fastspring', false, plugin_basename(dirname(__FILE__)) . '/languages');

          add_filter('woocommerce_checkout_fields', array($this, 'override_checkout_fields'), 20, 1);
          add_filter('woocommerce_endpoint_order-pay_title', array($this, 'title_order_pending'), 10, 2);
          add_filter('woocommerce_payment_gateways', array($this, 'add_gateways'));
          add_filter('script_loader_tag', array($this, 'modify_loading_scripts'), 20, 2);
          add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'plugin_action_links'));
          include_once dirname(__FILE__) . '/includes/class-wc-gateway-fastspring-handler.php';
      }

      /**
       * Remove billing address fields
       */
      public function override_checkout_fields($fields)
      {
          if (self::get_setting('billing_address')) {
              unset($fields['billing']['billing_address_1']);
              unset($fields['billing']['billing_address_2']);
              unset($fields['billing']['billing_city']);
              unset($fields['billing']['billing_postcode']);
              unset($fields['billing']['billing_country']);
              unset($fields['billing']['billing_state']);
              unset($fields['billing']['billing_company']);
              //unset($fields['billing']['billing_phone']);
          }
          return $fields;
      }

      /**
       * Change title of payment page
       *
       * @param string current title
       * @param string endpoint triggered
       * @return string
       */
      public function title_order_pending($title, $endpoint)
      {
          return __("Enter Payment Info on Next Page", 'woocommerce-gateway-fastspring');
      }

      /**
       * Allow this class and other classes to add slug keyed notices (to avoid duplication)
       *
       * @param string slug
       * @param string class
       * @param string message
       */
      public function add_admin_notice($slug, $class, $message)
      {
          $this->notices[$slug] = array(
        'class' => $class,
        'message' => $message,
      );
      }

      /**
       * The backup sanity check, in case the plugin is activated in a weird way,
       * or the environment changes after activation. Also handles upgrade routines.
       */
      public function check_environment()
      {
          if (!defined('IFRAME_REQUEST') && (WC_FASTSPRING_VERSION !== get_option('wc_fastspring_version'))) {
              $this->install();

              do_action('woocommerce_fastspring_updated');
          }

          $environment_warning = self::get_environment_warning();

          if ($environment_warning && is_plugin_active(plugin_basename(__FILE__))) {
              $this->add_admin_notice('bad_environment', 'error', $environment_warning);
          }

          $bad = !self::get_setting('access_key') || !self::get_setting('storefront_path') || !self::get_setting('private_key');

          if ($bad && !(isset($_GET['page'], $_GET['section']) && 'wc-settings' === $_GET['page'] && 'fastspring' === $_GET['section'])) {
              $setting_link = self::get_setting_link();
              $this->add_admin_notice('prompt_connect', 'notice notice-warning', sprintf(__('FastSpring is almost ready. To get started, <a href="%s">set your FastSpring credentials</a>.', 'woocommerce-gateway-fastspring'), $setting_link));
          }
      }

      /**
       * Updates the plugin version in db
       *
       * @return bool
       */
      private static function _update_plugin_version()
      {
          delete_option('wc_fastspring_version');
          update_option('wc_fastspring_version', WC_FASTSPRING_VERSION);

          return true;
      }

      /**
       * Handles upgrade routines.
       */
      public function install()
      {
          if (!defined('WC_FASTSPRING_INSTALLING')) {
              define('WC_FASTSPRING_INSTALLING', true);
          }

          $this->_update_plugin_version();
      }

      /**
       * Checks the environment for compatibility problems.  Returns a string with the first incompatibility
       * found or false if the environment has no problems.
       */
      public static function get_environment_warning()
      {
          if (version_compare(phpversion(), WC_FASTSPRING_MIN_PHP_VER, '<')) {
              $message = __('WooCommerce FastSpring - The minimum PHP version required for this plugin is %1$s. You are running %2$s.', 'woocommerce-gateway-fastspring');

              return sprintf($message, WC_FASTSPRING_MIN_PHP_VER, phpversion());
          }

          if (!defined('WC_VERSION')) {
              return __('WooCommerce FastSpring requires WooCommerce to be activated to work.', 'woocommerce-gateway-fastspring');
          }

          if (version_compare(WC_VERSION, WC_FASTSPRING_MIN_WC_VER, '<')) {
              $message = __('WooCommerce FastSpring - The minimum WooCommerce version required for this plugin is %1$s. You are running %2$s.', 'woocommerce-gateway-fastspring');

              return sprintf($message, WC_FASTSPRING_MIN_WC_VER, WC_VERSION);
          }

          return false;
      }

      /**
       * Adds plugin action links
       */
      public function plugin_action_links($links)
      {
          $setting_link = self::get_setting_link();

          $plugin_links = array(
        '<a href="' . $setting_link . '">' . __('Settings', 'woocommerce-gateway-fastspring') . '</a>',
        '<a href="https://docs.fastspring.com">' . __('Docs', 'woocommerce-gateway-fastspring') . '</a>',
      );
          return array_merge($plugin_links, $links);
      }

      /**
       * Get plugin setting link.
       *
       * @return string Setting link
       */
      public function get_setting_link()
      {
          return admin_url('admin.php?page=wc-settings&tab=checkout&section=fastspring');
      }

      /**
       * Display admin notices and warnings
       */
      public function admin_notices()
      {
          foreach ((array) $this->notices as $notice_key => $notice) {
              echo "<div class='" . esc_attr($notice['class']) . "'><p>";
              echo wp_kses($notice['message'], array('a' => array('href' => array())));
              echo '</p></div>';
          }
      }

      /**
       * Add the gateways to WooCommerce
       */
      public function add_gateways($methods)
      {
          $methods[] = 'WC_Gateway_FastSpring';
          return $methods;
      }

      /**
       * What rolls down stairs
       * alone or in pairs,
       * and over your neighbor's dog?
       * What's great for a snack,
       * And fits on your back?
       * It's log, log, log
       */
      public static function log($message)
      {

          // Static function so we need to get options another way
          $settings = self::get_setting('woocommerce_fastspring_settings', array());

          if ($settings['logging'] || defined('WP_DEBUG') && WP_DEBUG) {
              if (empty(self::$log)) {
                  self::$log = new WC_Logger();
              }
              self::$log->add('woocommerce-gateway-fastspring', $message);
          }
      }
  }

  $GLOBALS['wc_fastspring'] = WC_FastSpring::get_instance();

endif;
