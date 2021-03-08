=== FastSpring for WooCommerce ===
Contributors: Enradia
Tags: WooCommerce, Payment Gateway
Version: 1.2.3
Requires PHP: 5.0.0
Requires at least: 3.0
Tested up to: 4.9.6
Contributor: cyberwombat
Stable tag: trunk
License: MIT
License URI: https://opensource.org/licenses/MIT
Contributors: cyberwombat
Donate link: https://www.paypal.com/donate/?token=SiMqCFR8nI8ciqqKR8EpxBhGrBTAt6ye5kevdwvLF5MGjGTAO_oN7o-vDlWvRiBrZopSw0&country.x=US&locale.x=US

FastSpring For Woocommerce integrates your FastSpring account with your wordpress site

== Description ==

FastSpring For Woocommerce integrates your [FastSpring[(http://fastspring.com) account with your WordPress site. It provides support for both the hosted and popup version of FastSpring and provides webhook and API support for order validation as well as subscription support.

== Installation ==

View installation instructions [here](https://github.com/cyberwombat/woocommerce-fastspring-payment-gateway/wiki).

== Screenshots ==
 
1. FastSpring admin dashboard.
2. FastSpring payment popup option.

== Upgrade Notice == 

N/A

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

= 1.1.0 =
* Preliminary subscription support.

= 1.1.1 =
* Improved subscription support.

= 1.1.2 =
* Bug fix.

= 1.1.3 =
* Pricing bug fixed and improvements with coupons.

= 1.1.4 =
* Subscription pricing bug fixed.

= 1.1.5 =
* Upgrade FastSpring JavasScript to 0.7.6.

= 1.1.6 =
* Added FS debug functions

= 1.1.7 =
* Update quantity handling for locked behavior

= 1.1.8 =
* Test mode cleanup

= 1.1.9 =
* Better storefront path test handling

= 1.2.0 =
* Checkbox setting issue fix

= 1.2.1 =
* Refactor settings code

= 1.2.2 =
* Fix typo

= 1.2.3 =
* Support for payemnt icons, transaction URL

= 1.2.4 =
* Upgrade FastSpring JavasScript to 0.8.3

= 1.2.5 =
* Fix typo causing subscription activate issues