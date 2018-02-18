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

FastSpring For Woocommerce integrates your [FastSpring[(http://fastspring.com) account with your WordPress site. It provides support for both the hosted and popup version of FastSpring and provides webhook and API support for order validation.

== Installation ==

After activating the plugin you will need to setup your access key and SSL certificate to encrypt orders to FastSpring. Additionally, you will need to setup either the Webhook or API method of validating orders depending on your storefront type.

For popups you can use eiher the webhook or API method. FastSpring hosted storefronts, on the other hand, must use the webhook method. Instructions for each are found in the admin settings for this plugin under WooCommerce > Checkout > FastSpring dashboard, 

FastSpring sends its own receipts and other user notifiations. You may want to disable this if you are using the existing WooCommerce notification system to prevent duplicate receipts.

== Screenshots ==
 
1. FastSpring admin dashboard.
2. FastSpring payment popup option.

== Changelog ==
 
= 1.0.2 =
* Pass order ID as FS tag to avoid race conditions experienced with using transaction ID.

= 1.0.3 =
* Adjustement to discount calculation.

= 1.0.4 =
* Removed interim order confirm page - FS lauches right from checkout
* Option to use hosted page or popup

= 1.0.5 =
* Option for hosted storefront.
* Removed interim page - FS launches from checkout directly.

