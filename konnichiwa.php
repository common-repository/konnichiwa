<?php
/*
Plugin Name: Konnichiwa! Membership
Plugin URI: http://namaste-lms.org/konnichiwa.php
Description: Flexible membership plugin. Start selling access to premium content in your site in minutes.
Author: Kiboko Labs
Version: 0.8.4
Author URI: http://calendarscripts.info/
License: GPLv2 or later
*/

define( 'KONN_PATH', dirname( __FILE__ ) );
define( 'KONN_RELATIVE_PATH', dirname( plugin_basename( __FILE__ )));
define( 'KONN_URL', plugin_dir_url( __FILE__ ));

// require controllers and models
require_once(KONN_PATH.'/models/basic.php');
require_once(KONN_PATH.'/models/plan.php');
require_once(KONN_PATH.'/models/content.php');
require_once(KONN_PATH.'/models/sub.php');
require_once(KONN_PATH.'/models/payment.php');
require_once(KONN_PATH.'/controllers/plans.php');
require_once(KONN_PATH.'/controllers/contents.php');
require_once(KONN_PATH.'/controllers/subs.php');
require_once(KONN_PATH.'/controllers/shortcodes.php');
include_once(KONN_PATH.'/controllers/files.php');
require_once(KONN_PATH.'/helpers/htmlhelper.php');
require_once(KONN_PATH.'/controllers/ajax.php');
require_once(KONN_PATH.'/controllers/woocommerce.php');

add_action('init', array("Konnichiwa", "init"));

register_activation_hook(__FILE__, array("Konnichiwa", "install"));
add_action('admin_menu', array("Konnichiwa", "menu"));
add_action('admin_enqueue_scripts', array("Konnichiwa", "scripts"));
add_action('admin_enqueue_scripts', array("Konnichiwa", "admin_css"));
add_action('save_post', array('KonnichiwaContents', 'save_meta'));

// show the things on the front-end
add_action( 'wp_enqueue_scripts', array("Konnichiwa", "scripts"));

// other actions
add_action('wp_ajax_konnichiwa_ajax', 'konnichiwa_ajax');
add_action('wp_ajax_nopriv_konnichiwa_ajax', 'konnichiwa_ajax');