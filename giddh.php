<?php 
/* 
Plugin Name: Accounting Software By Giddh
Description: Giddh ~ Accounting at its Rough! Bookkeeping and Accounting Software
Version: 1.1 
Author: Giddh
Author URI: https://giddh.com/ 
License: GPLv2 or later
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

ini_set("serialize_precision", -1);

define('GIDDH_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('GIDDH_SITE_URL', plugins_url("accounting-software-by-giddh"));
define('GIDDH_PLUGIN_URL', "admin.php?page=giddh");
define('GIDDH_VERSION', "1.1");
define('GIDDH_MINIMUM_WP_VERSION', '4.0');

require(GIDDH_PLUGIN_PATH."config/constants.php");
require(GIDDH_PLUGIN_PATH."helpers/product.php");
require(GIDDH_PLUGIN_PATH."helpers/webhooks.php");
require(GIDDH_PLUGIN_PATH."helpers/general.php");
require(GIDDH_PLUGIN_PATH."classes/giddhapi.php");
require(GIDDH_PLUGIN_PATH."models/payments.php");
require(GIDDH_PLUGIN_PATH."models/products.php");
require(GIDDH_PLUGIN_PATH."models/customers.php");
require(GIDDH_PLUGIN_PATH."models/categories.php");
require(GIDDH_PLUGIN_PATH."models/invoice.php");
require(GIDDH_PLUGIN_PATH."helpers/shop.php");
require(GIDDH_PLUGIN_PATH."helpers/email.php");
require(GIDDH_PLUGIN_PATH."helpers/giddh.php");
require(GIDDH_PLUGIN_PATH."helpers/setup.php");
require(GIDDH_PLUGIN_PATH."helpers/ajax.php");
require(GIDDH_PLUGIN_PATH."helpers/controller.php");
require(GIDDH_PLUGIN_PATH."helpers/api.php");

function giddhActivation() {
    if (version_compare($GLOBALS['wp_version'], GIDDH_MINIMUM_WP_VERSION, '<')) {
        $message = sprintf(esc_html__( 'Giddh %s requires WordPress %s or higher.' , 'giddh'), GIDDH_VERSION, GIDDH_MINIMUM_WP_VERSION);

        giddhActivationError($message);
        exit;
    } else {
        global $wpdb;

        if(!class_exists('WooCommerce')) {
            giddhActivationError('WooCommerce is required to use this plugin. Please install WooCommerce first.');
            exit;
        }

        $wpdb->hide_errors();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta(giddhGetSchema());

        $currentUser = wp_get_current_user();

        update_option('giddh_version', GIDDH_VERSION);
        update_option('giddh_notification_email', esc_html($currentUser->user_email));

        $currentUser = wp_get_current_user();
        $customerName = ($currentUser->display_name) ? $currentUser->display_name : $currentUser->user_login;

        $template = giddhGetWelcomeTemplate(array("customerName" => $customerName));
        giddhSendMail(array("to" => $currentUser->user_email, "toName" => $customerName, "subject" => GIDDH_WELCOME_EMAIL_SUBJECT, "message" => $template));
    }
}

function giddhDeactivation() {
    global $wpdb;

    if(get_option('giddh_company_unique_name') && get_option('giddh_shop_unique_name')) {
        $giddhApi = new GiddhApi();
        $giddhApi->disconnectAccount(get_option('giddh_company_unique_name'), get_option('giddh_shop_unique_name'), array("source" => "wordpress"));
    }

    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}asg_woocommerce_products");
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}asg_available_payment_gateways");
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}asg_payment_gateways");
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}asg_giddh_products");
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}asg_categories");
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}asg_customers");
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}asg_invoices");
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}asg_woocommerce_temp_products");

    $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE 'giddh\_%';");

    $currentUser = wp_get_current_user();
    $customerName = ($currentUser->display_name) ? $currentUser->display_name : $currentUser->user_login;

    $template = giddhGetUninstallTemplate(array("customerName" => $customerName));
    giddhSendMail(array("to" => $currentUser->user_email, "toName" => $customerName, "subject" => GIDDH_UNINSTALL_EMAIL_SUBJECT, "message" => $template));
}

function giddhGetSchema() {
	global $wpdb;

	$collate = "";

	if($wpdb->has_cap('collation')) {
		$collate = $wpdb->get_charset_collate();
	}

$tables = "
CREATE TABLE {$wpdb->prefix}asg_woocommerce_products (
  id INT NOT NULL auto_increment,
  product_id varchar(20) NOT NULL,
  variant_id varchar(20) NOT NULL,
  category_id varchar(20) NULL DEFAULT NULL,
  title varchar(255) NOT NULL,
  amount varchar(20) NOT NULL,
  quantity varchar(20) NULL DEFAULT NULL,
  sku varchar(20) NULL DEFAULT NULL,
  stock_unit_code varchar(10) NULL DEFAULT NULL,
  is_saved TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY  (id)
) $collate;
CREATE TABLE {$wpdb->prefix}asg_giddh_products (
  id INT NOT NULL auto_increment,
  unique_name varchar(255) NOT NULL,
  title varchar(255) NOT NULL,
  amount varchar(20) NOT NULL,
  quantity varchar(20) NULL DEFAULT NULL,
  sku varchar(50) NULL DEFAULT NULL,
  hsn varchar(50) NULL DEFAULT NULL,
  stock_group varchar(100) NULL DEFAULT NULL,
  stock_unit_code varchar(10) NULL DEFAULT NULL,
  is_saved TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY  (id)
) $collate;
CREATE TABLE {$wpdb->prefix}asg_categories (
  id INT NOT NULL auto_increment,
  woocommerce_category_id INT NOT NULL,
  woocommerce_category_name varchar(255) NOT NULL,
  giddh_stock_group_id varchar(100) NOT NULL,
  giddh_stock_group_name varchar(255) NOT NULL,
  PRIMARY KEY  (id)
) $collate;
CREATE TABLE {$wpdb->prefix}asg_customers (
  id INT NOT NULL auto_increment,
  woocommerce_customer_id varchar(100) NOT NULL,
  giddh_account_id varchar(100) NOT NULL,
  PRIMARY KEY  (id)
) $collate;
CREATE TABLE {$wpdb->prefix}asg_invoices (
  id INT NOT NULL auto_increment,
  woocommerce_order_id varchar(100) NOT NULL,
  giddh_invoice_id varchar(100) NOT NULL,
  giddh_voucher_number varchar(100) NOT NULL,
  invoice_type varchar(20) NOT NULL,
  date_created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY  (id)
) $collate;
CREATE TABLE {$wpdb->prefix}asg_woocommerce_temp_products (
  id INT NOT NULL auto_increment,
  product_sku varchar(100) NOT NULL,
  tags varchar(20) NOT NULL,
  PRIMARY KEY  (id)
) $collate;
CREATE TABLE {$wpdb->prefix}asg_available_payment_gateways (
  id INT NOT NULL auto_increment,
  name varchar(100) NOT NULL,
  code varchar(100) NOT NULL,
  PRIMARY KEY  (id)
) $collate;
CREATE TABLE {$wpdb->prefix}asg_payment_gateways (
  id INT NOT NULL auto_increment,
  woocommerce_payment_id varchar(100) NOT NULL,
  giddh_account_id varchar(100) NOT NULL,
  PRIMARY KEY  (id)
) $collate;";

	return $tables;
}

/* THIS WILL REGISTER THE PLUGIN ACTIVATION/DEACTIVATION WEBHOOKS */
register_activation_hook(__FILE__, 'giddhActivation');
register_deactivation_hook(__FILE__, 'giddhDeactivation');
/* THIS WILL REGISTER THE PLUGIN ACTIVATION/DEACTIVATION WEBHOOKS */

/* THIS WILL ADD THE PLUGIN IN LEFT MENU */
add_action('admin_menu', 'giddhPluginSettings');
/* THIS WILL ADD THE PLUGIN IN LEFT MENU */

/* WOOCOMMERCE HOOKS */
add_action('woocommerce_new_product', 'giddhSaveProduct');
add_action('woocommerce_update_product', 'giddhUpdateProduct');
add_action('wp_trash_post', 'giddhTrashPost');
add_action('untrashed_post', 'giddhRestorePost');
add_action('woocommerce_new_order', 'giddhSaveOrder');
add_action('woocommerce_update_order', 'giddhUpdateOrder');
// add_action('woocommerce_order_refunded', 'giddhRefundOrder');
add_action('woocommerce_payment_complete', 'giddhPaidOrder');
add_action('woocommerce_order_status_changed', 'giddhChangeOrderStatus');
add_action('woocommerce_admin_product_cat_updated', 'giddhSaveCategory');
add_action('edit_term', function($term_id, $tt_id = '', $taxonomy = '') {
    if($taxonomy === 'product_cat') {
        do_action('woocommerce_admin_product_cat_updated', $term_id);
    }
}, 10, 3);

add_action('add_term_meta', function($term_id, $tt_id = '', $taxonomy = '') {
    do_action('woocommerce_admin_product_cat_updated', $term_id);
}, 10, 3);
/* WOOCOMMERCE HOOKS */

/* SETUP HOOKS */
add_action('wp_ajax_giddh_save_woocommerce_categories', 'giddhSaveWoocommerceCategories');
add_action('wp_ajax_giddh_save_woocommerce_payment_gateways', 'giddhSaveWoocommercePaymentGateways');
add_action('wp_ajax_giddh_save_woocommerce_products', 'giddhSaveWoocommerceProducts');
add_action('wp_ajax_giddh_save_giddh_products', 'giddhSaveGiddhProducts');
add_action('wp_ajax_giddh_create_giddh_unmatched_to_woocommerce', 'giddhCreateGiddhUnmatchedToWoocommerce');
add_action('wp_ajax_giddh_create_woocommerce_unmatched_to_giddh', 'giddhCreateWoocommerceUnmatchedToGiddh');
/* AJAX HOOKS */

/* CONTROLLER HOOKS */
add_action('wp_ajax_giddh_connect', 'giddhConnect');
add_action('wp_ajax_giddh_settings_invoice', 'giddhSettingsInvoice');
add_action('wp_ajax_giddh_settings_category', 'giddhSettingsCategory');
add_action('wp_ajax_giddh_settings_inventory', 'giddhSettingsInventory');
add_action('wp_ajax_giddh_settings_account', 'giddhSettingsAccount');
add_action('wp_ajax_giddh_settings_payment', 'giddhSettingsPayment');
add_action('wp_ajax_giddh_settings_service', 'giddhSettingsService');
/* CONTROLLER HOOKS */

/* AJAX HOOKS */
add_action('wp_ajax_giddh_get_products', 'giddhGetProducts');
add_action('wp_ajax_giddh_get_categories', 'giddhGetCategories');
add_action('wp_ajax_giddh_get_sales_accounts', 'giddhGetSalesAccounts');
add_action('wp_ajax_giddh_get_stock_groups', 'giddhGetStockGroups');
add_action('wp_ajax_giddh_get_stocks', 'giddhGetStocks');
add_action('wp_ajax_giddh_get_woocommerce_stocks', 'giddhGetWoocommerceStocks');
/* AJAX HOOKS */

/* ENQUEUE SCRIPT HOOKS */
if(isset($_GET['page']) && $_GET['page'] == 'giddh') {
    add_action('admin_enqueue_scripts', 'giddhLoadScripts');
}
/* ENQUEUE SCRIPT HOOKS */

/* API HOOKS */
add_action('rest_api_init', function () {
    register_rest_route('giddh/api', '/bulk-product-create-notification', array(
      'methods' => 'POST',
      'callback' => 'giddhBulkProductCreateNotification',
    ));

    register_rest_route('giddh/api', '/inventory-update', array(
        'methods' => 'POST',
        'callback' => 'giddhInventoryUpdate',
    ));
});
/* API HOOKS */

function giddhPluginSettings() {
    add_menu_page('Giddh', 'Giddh', 'manage_options', 'giddh', 'giddhDisplaySettings', GIDDH_SITE_URL.'/assets/images/menu-icon.png');
}

function giddhDisplaySettings() {
	if(get_option('giddh_company_unique_name') && get_option('giddh_company_auth_key') && get_option('giddh_shop_unique_name') && (!$_GET["view"] || $_GET["view"] == "settings")) {
		include(GIDDH_PLUGIN_PATH."templates/settings.php");
	} else if($_GET["view"] == "connect") {
		include(GIDDH_PLUGIN_PATH."templates/connect.php");
	} else if($_GET["view"] == "onboarding") {
		include(GIDDH_PLUGIN_PATH."templates/onboarding.php");
	} else if($_GET["view"] == "setup") {
		include(GIDDH_PLUGIN_PATH."templates/setup.php");
	} else if($_GET["view"] == "faq") {
		include(GIDDH_PLUGIN_PATH."templates/faq.php");
	} else {
		include(GIDDH_PLUGIN_PATH."templates/onboarding.php");
    }
}

function giddhInitSetup() {
    update_option('giddh_save_woocommerce_categories', 'pending');
    update_option('giddh_save_woocommerce_products', 'pending');
    update_option('giddh_save_giddh_products', 'pending');
    update_option('giddh_save_woocommerce_payment_gateways', 'pending');
}
?>