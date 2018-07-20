<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}
 
delete_option('woocommerce_fastspring_settings');
