<?php
// main model containing general config and UI functions
class Konnichiwa {
   static function install($update = false) {
   	global $wpdb;	
   	$wpdb -> show_errors();
   	
   	if(!$update) self::init();
	  
	   // subscription plans
   	if($wpdb->get_var("SHOW TABLES LIKE '".KONN_PLANS."'") != KONN_PLANS) {        
			$sql = "CREATE TABLE `" . KONN_PLANS . "` (
				  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				  `name` VARCHAR(255) NOT NULL DEFAULT '',
				  `description` TEXT,
				  `price` DECIMAL(8,2) NOT NULL DEFAULT '0.00',
				  `duration` INT UNSIGNED NOT NULL DEFAULT 0,
				  `duration_unit` VARCHAR(100) NOT NULL DEFAULT 'day'				  
				) DEFAULT CHARSET=utf8;";
			
			$wpdb->query($sql);
	  }
	  
	  // user subscriptions
     if($wpdb->get_var("SHOW TABLES LIKE '".KONN_SUBS."'") != KONN_SUBS) {        
			$sql = "CREATE TABLE `" . KONN_SUBS . "` (
				  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				  `user_id` INT UNSIGNED NOT NULL DEFAULT 0,
				  `plan_id` INT UNSIGNED NOT NULL DEFAULT 0,
				  `date` DATE,
				  `expires` DATE,
				  `status` TINYINT UNSIGNED NOT NULL DEFAULT 0,
				  `amt_paid` DECIMAL(10,2) NOT NULL DEFAULT '0.00'				 				  
				) DEFAULT CHARSET=utf8;";
			
			$wpdb->query($sql);
	  }
	  
	  // usage (visits) per user, plan and day
     if($wpdb->get_var("SHOW TABLES LIKE '".KONN_USAGE."'") != KONN_USAGE) {        
			$sql = "CREATE TABLE `" . KONN_USAGE . "` (
				  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				  `user_id` INT UNSIGNED NOT NULL DEFAULT 0,
				  `plan_id` INT UNSIGNED NOT NULL DEFAULT 0,
				  `date` DATE,
				  `pageviews` INT UNSIGNED NOT NULL DEFAULT 0		  
				) DEFAULT CHARSET=utf8;";
			
			$wpdb->query($sql);
	  }		 
	  
	  // protected content settings 
     if($wpdb->get_var("SHOW TABLES LIKE '".KONN_CONTENT."'") != KONN_CONTENT) {        
			$sql = "CREATE TABLE `" . KONN_CONTENT . "` (
				  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				  `content_type` VARCHAR(100) NOT NULL DEFAULT 'post',
				  `content_category` INT UNSIGNED NOT NULL DEFAULT 0, /* further specify by Wordpress category */
				  `protection_type` VARCHAR(100) NOT NULL DEFAULT 'none' /* none, registered, plans (list of subscription plans) */		  
				) DEFAULT CHARSET=utf8;";
			
			$wpdb->query($sql);
	  } 
	  
	  // protected files
     if($wpdb->get_var("SHOW TABLES LIKE '".KONN_FILES."'") != KONN_FILES) {        
			$sql = "CREATE TABLE `" . KONN_FILES . "` (
				  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				  `filename` VARCHAR(255) NOT NULL DEFAULT '',
				  `filetype` VARCHAR(100) NOT NULL DEFAULT '', /* file extension */
				  `filesize` INT UNSIGNED NOT NULL DEFAULT 0, /* size in KB */		  
				  `protection_type` VARCHAR(100) NOT NULL DEFAULT 'none', /* none, registered, plans (list of subscription plans) */
				  `filecontents` LONGBLOB,
				  `downloads` INT UNSIGNED NOT NULL DEFAULT 0
				) DEFAULT CHARSET=utf8;";
			
			$wpdb->query($sql);
	  } 
	  
	  
	  // payments made
     if($wpdb->get_var("SHOW TABLES LIKE '".KONN_PAYMENTS."'") != KONN_PAYMENTS) {        
			$sql = "CREATE TABLE `" . KONN_PAYMENTS . "` (
				  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				  `user_id` INT UNSIGNED NOT NULL DEFAULT 0,
				  `plan_id` INT UNSIGNED NOT NULL DEFAULT 0,
				  `sub_id` INT UNSIGNED NOT NULL DEFAULT 0, /* subscription ID */
				  `date` DATE,
				  `status` VARCHAR(100) NOT NULL DEFAULT 'pending',
				  `method` VARCHAR(100) NOT NULL DEFAULT 'paypal',
				  `payment_key` VARCHAR(100) NOT NULL DEFAULT '', /* paypal txn_id etc */ 	
				  `amount` DECIMAL(10,2) NOT NULL DEFAULT '0.00'				 			  		  
				) DEFAULT CHARSET=utf8;";
			
			$wpdb->query($sql);
	  } 
	  
	  	konnichiwa_add_db_fields(array(
			array("name" => 'subscribe_on_signup', "type" => "TINYINT UNSIGNED NOT NULL DEFAULT 0"),
			array("name" => 'woo_product_id', "type" => "INT UNSIGNED NOT NULL DEFAULT 0"),
		 ),
		 KONN_PLANS);  	  
	  
	  // set default currency
	  $currency = get_option('konnichiwa_currency');
	  if(empty($currency)) update_option('konnichiwa_currency', 'USD');
	  
	  update_option('konnichiwa_version', 0.73);
	  
	  update_option('konnichiwa_admin_notice', sprintf(__('<b>Thank you for activating Konnichiwa!</b> Please check our <a href="%s" target="_blank">Quick getting started guide</a> and the <a href="%s">Help</a> page to get started!', 'konnichiwa'), 'http://namaste-lms.org/konnichiwa.php#quick-start', 'admin.php?page=konnichiwa_help'));	
	  // exit;
   }
   
   // main menu
   static function menu() {
   	add_menu_page(__('Konnichiwa!', 'konnichiwa'), __('Konnichiwa', 'konnichiwa'), "manage_options", "konnichiwa", 
   		array('Konnichiwa', "options"));
   	
   	add_submenu_page('konnichiwa', __('Settings', 'konnichiwa'), __('Settings', 'konnichiwa'), "manage_options", "konnichiwa", 
   		array('Konnichiwa', "options"));
   	add_submenu_page('konnichiwa', __('Subscription Plans', 'konnichiwa'), __('Subscription Plans', 'konnichiwa'), 'manage_options', 
   		'konnichiwa_plans', array('KonnichiwaPlans','manage'));	
   	add_submenu_page('konnichiwa', __('Content Access', 'konnichiwa'), __('Content Access', 'konnichiwa'), 'manage_options', 
   		'konnichiwa_content', array('KonnichiwaContents','manage'));
   	add_submenu_page('konnichiwa', __('Protected Files', 'konnichiwa'), __('Protected Files', 'konnichiwa'), 'manage_options', 
   		'konnichiwa_files', array('KonnichiwaFiles','manage'));
   	add_submenu_page('konnichiwa', __('Subscriptions', 'konnichiwa'), __('Subscriptions', 'konnichiwa'), 'manage_options', 
   		'konnichiwa_subs', array('KonnichiwaSubs','manage'));	
   	add_submenu_page('konnichiwa', __('Help', 'konnichiwa'), __('Help', 'konnichiwa'), 'manage_options', 
   		'konnichiwa_help', array('Konnichiwa','help'));	
	}
	
	// CSS and JS
	static function scripts() {
		// CSS
		//wp_register_style( 'konnichiwa-css', KONN_URL.'css/main.css?v=1');
	  //wp_enqueue_style( 'konnichiwa-css' );
   
   	wp_enqueue_script('jquery');
	   
	   // konnichiwa's own Javascript
		wp_register_script(
				'konnichiwa-common',
				KONN_URL.'js/main.js',
				false,
				'0.1.0',
				false
		);
		wp_enqueue_script("konnichiwa-common");
	}
	
	// admin-only CSS
	static function admin_css() {
	  wp_register_style( 'konnichiwa-admin-css', KONN_URL.'css/admin.css?v=1');
	  wp_enqueue_style( 'konnichiwa-admin-css' );
	}
	
	// initialization
	static function init() {
		global $wpdb;
		load_plugin_textdomain( 'konnichiwa', false, KONN_RELATIVE_PATH."/languages/" );
		if (!session_id()) @session_start();
		
		// define table names 
		define('KONN_PLANS', $wpdb->prefix.'konnichiwa_plans');
		define('KONN_SUBS', $wpdb->prefix.'konnichiwa_subscriptions');
		define('KONN_USAGE', $wpdb->prefix.'konnichiwa_usage');
		define('KONN_CONTENT', $wpdb->prefix.'konnichiwa_content');
		define('KONN_PAYMENTS', $wpdb->prefix.'konnichiwa_payments');
		define('KONN_FILES', $wpdb->prefix.'konnichiwa_files');
		
		define( 'KONN_VERSION', get_option('konnichiwa_version'));
		$currency = get_option('konnichiwa_currency');
		$currency = empty($currency) ? 'USD' : $currency;
		if(!defined('KONN_CURRENCY')) define('KONN_CURRENCY', $currency);		
		
		// meta boxes
		add_action( 'add_meta_boxes', array('KonnichiwaContents', 'meta_box') );
		add_filter( 'the_content', array('KonnichiwaContents', 'access_filter') );
				
		// shortcodes
		add_shortcode('konnichiwa-plans', array('KonnichiwaShortcodes', 'plans'));
		add_shortcode('konnichiwa-subscribe', array('KonnichiwaShortcodes', 'subscribe'));
		add_shortcode('konnichiwa-protect', array('KonnichiwaShortcodes', 'protect'));
		add_shortcode('konnichiwa-mysubs', array('KonnichiwaShortcodes', 'my_subs'));
		
		// actions
		add_action('template_redirect', array('KonnichiwaSubs', 'template_redirect'));
		add_action('template_redirect', array('KonnichiwaFiles', 'download'));
		add_action( 'user_register', array('KonnichiwaPlans', 'auto_subscribe'), 10, 1 );
		add_action('woocommerce_order_status_completed', ['KonnichiwaWoo', 'order_complete']);
		
		// Paypal IPN
		add_filter('query_vars', array(__CLASS__, "query_vars"));
		add_action('parse_request', array("KonnichiwaPayment", "parse_request"));
		
		// ajax
		add_action('wp_ajax_konnichiwa_ajax', 'konnichiwa_ajax');
		add_action('wp_ajax_nopriv_konnichiwa_ajax', 'konnichiwa_ajax');
		
		// run activate
		$version = get_option('konnichiwa_version');
		if(version_compare($version, '0.73') == -1) self :: install(true);
		
		add_action('admin_notices', array(__CLASS__, 'admin_notice'));	
		
		// wp_loaded actions
		add_action('wp_loaded', array(__CLASS__, "wp_loaded"));
	}
	
	// handle Konnichiwa vars in the request
	static function query_vars($vars) {
		$new_vars = array('konnichiwa');
		$vars = array_merge($new_vars, $vars);
	   return $vars;
	} 	
	
	static function admin_notice() {
		$notice = get_option('konnichiwa_admin_notice');
		if(!empty($notice)) {
			echo "<div class='updated'><p>".stripslashes($notice)."</p></div>";
		}
		// once shown, cleanup
		update_option('konnichiwa_admin_notice', '');
	}
			
	// manage general options
	static function options() {
		if(!empty($_POST['konnichiwa_payment_options']) and check_admin_referer('konnichiwa_options')) {
			update_option('konnichiwa_accept_other_payment_methods', empty($_POST['accept_other_payment_methods']) ? 0 : 1);
			update_option('konnichiwa_other_payment_methods', konnichiwa_strip_tags($_POST['other_payment_methods']));
			if(empty($_POST['currency'])) $_POST['currency'] = sanitize_text_field($_POST['custom_currency']);
			update_option('konnichiwa_currency', sanitize_text_field($_POST['currency']));
			update_option('konnichiwa_accept_paypal', empty($_POST['accept_paypal']) ? 0 : 1);
			update_option('konnichiwa_paypal_id', sanitize_text_field($_POST['paypal_id']));
			update_option('konnichiwa_paypal_sandbox', (empty($_POST['paypal_sandbox']) ? 0 : 1));
			update_option('konnichiwa_use_pdt', (empty($_POST['use_pdt']) ? 0 : 1));
			update_option('konnichiwa_pdt_token', sanitize_text_field($_POST['pdt_token']));
			update_option('konnichiwa_accept_woo', (empty($_POST['accept_woo']) ? 0 : 1));
			
			update_option('konnichiwa_accept_stripe', empty($_POST['accept_stripe']) ? 0 : 1);
			update_option('konnichiwa_stripe_public', sanitize_text_field($_POST['stripe_public']));
			update_option('konnichiwa_stripe_secret', sanitize_text_field($_POST['stripe_secret']));
			
			update_option('konnichiwa_accept_moolamojo', (empty($_POST['accept_moolamojo']) ? 0 : 1));
			update_option('konnichiwa_moolamojo_price', intval($_POST['moolamojo_price']));
			update_option('konnichiwa_moolamojo_button', konnichiwa_strip_tags($_POST['moolamojo_button']));
		}		
			
		$accept_other_payment_methods = get_option('konnichiwa_accept_other_payment_methods');
		$accept_paypal = get_option('konnichiwa_accept_paypal');
		$accept_stripe = get_option('konnichiwa_accept_stripe');
		$accept_woo = get_option('konnichiwa_accept_woo');
		
		$currency = get_option('konnichiwa_currency');
		$currencies=array('USD'=>'$', "EUR"=>"&euro;", "GBP"=>"&pound;", "JPY"=>"&yen;", "AUD"=>"AUD",
	   "CAD"=>"CAD", "CHF"=>"CHF", "CZK"=>"CZK", "DKK"=>"DKK", "HKD"=>"HKD", "HUF"=>"HUF",
	   "ILS"=>"ILS", "INR"=>"INR", "MXN"=>"MXN", "NOK"=>"NOK", "NZD"=>"NZD", "PLN"=>"PLN", "SEK"=>"SEK",
	   "SGD"=>"SGD", "ZAR"=>"ZAR");		
	   $currency_keys = array_keys($currencies);  		
	   
	   $moolamojo_button = get_option('konnichiwa_moolamojo_button');	
	   if(empty($moolamojo_button)) $moolamojo_button = "<p align='center'>".__('You can also buy this plan with {{{credits}}} virtual credits from your balance. You currently have [moolamojo-balance] credits total.', 'konnichiwa')."</p><p align='center'>{{{button}}}</p>";
	   $accept_moolamojo = get_option('konnichiwa_accept_moolamojo');
	   
	   $use_pdt = get_option('konnichiwa_use_pdt');
			
		require(KONN_PATH."/views/options.html.php");
	}	
	
	static function help() {
		require(KONN_PATH."/views/help.html.php");
	}	
	
	// call actions on WP loaded
	static function wp_loaded() {
	   if(!empty($_GET['konnichiwa_pdt'])) KonnichiwaPayment::paypal_ipn();	   
	}	
}