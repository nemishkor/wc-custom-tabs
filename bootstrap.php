<?php
/**
 * Plugin Name: WooCommerce custom tabs
 * Description: Add custom tabs to each WooCommerce product
 * Author: Vitalii Koreniev
 * Version: 1.0.0
 * Text Domain: nemishkor-wc-custom-tabs
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( defined('NEMISHKOR_WC_CUSTOM_TABS_TEXT_DOMAIN') ){
    echo "NEMISHKOR_WC_CUSTOM_TABS_TEXT_DOMAIN already defined!";
} else {
    define( 'NEMISHKOR_WC_CUSTOM_TABS_TEXT_DOMAIN', 'nemishkor-wc-custom-tabs' );
}

include_once 'Tab.php';
include_once 'WCCustomTabs.php';

$wCCustomTabs = new Nemishkor\NemishkorWCCustomTabs(__FILE__, 'nemishkor-wc-custom-tabs');