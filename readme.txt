=== FastSpring for WooCommerce ===
Contributors: Enradia
Tags: WooCommerce, Payment Gateway
Requires PHP: 5.0.0
Requires at least: 3.0
Tested up to: 4.8
Stable tag: 1.0.0
Contributor: cyberwombat
License: MIT
License URI: https://opensource.org/licenses/MIT
Contributors: cyberwombat

FastSpring For Woocommerce integrates your FastSpring account with your wordpress site

== Description ==

FastSpring For Woocommerce integrates your [FastSpring[(http://fastspring.com) account with your wordpress site. It uses the popup version of FastSpring and provides webhook support for order validation.

== Installation ==

After activating the plugin you will need to login to your FastSpring account and generate a public and private key as well as create a webhook secret.  In the WooCommerce > Checkout > FastSpring dashboard, update the fields with your webhook secret and your public key. See [docs](http://docs.fastspring.com/integrating-with-fastspring/store-builder-library/passing-sensitive-data-with-secure-requests).

This plugin provides support only for the popup version of FastSpring. You will need to create your popup store in the FS dashboard and update your WP setting with the path of your popup store.

Add your WP webhook url as found in the FS settings to the webhook section in the FS Integrations section.

== Screenshots ==
 
1. FastSpring admin dashboard.

== Changelog ==
 
= 1.0.2 =
* Pass order ID as FS tag to avoid race conditions experienced with using transaction ID.

= 1.0.3 =
* Adjustement to discount calculation.


