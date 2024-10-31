<?php
/*
Plugin Name: OpenSocial Login
Plugin URI: https://github.com/OpenSocialCSN/opensocial-wordpress-plugin
Description: OpenSocial Wordpress Plugin is a plugin allowing your users to easily authenticate into your Wordpress site. OpenSocial is a SSO one-click service allowing users to authenticate with their Google, Facebook, Twitter, LinkedIn, Github or OpenSocial accounts. The OpenSocial Wordpress Plugin is backed by the OpenSocial SSO service.
Author: LabLynx, Inc.
Version: 0.1.5
Author URI: https://www.lablynx.com
*/

if ( ! defined( 'WPINC' ) ) {
	die;
}

function osl_op_register_script() {
	wp_register_style('opensocial_login_style', plugins_url('/css/opensocial_login_style.css', __FILE__), false, '1.0.10', 'all');
	wp_register_style('opensocial_font_awesome', 'https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css', false, NULL, 'all');

}

function osl_op_enqueue_style(){
	wp_enqueue_style( 'opensocial_login_style' );
	wp_enqueue_style( 'opensocial_font_awesome' );
	wp_enqueue_media();
}

// use the registered jquery and style above
add_filter('https_ssl_verify', '__return_false');
add_action('init', 'osl_op_register_script');
add_action('admin_enqueue_scripts', 'osl_op_enqueue_style');
add_action('wp_enqueue_scripts', 'osl_op_enqueue_style');

// Allow cookie name overriding by defining following constants prior this point. Eg.: in wp-config.php.
if ( false === defined( 'OSL_SAML_LOGIN_COOKIE' ) ) {
	define( 'OSL_SAML_LOGIN_COOKIE', 'saml_login' );
}
if ( false === defined( 'OSL_SAML_NAMEID_COOKIE' ) ) {
	define( 'OSL_SAML_NAMEID_COOKIE', 'saml_nameid' );
}
if ( false === defined( 'OSL_SAML_SESSIONINDEX_COOKIE' ) ) {
	define( 'OSL_SAML_SESSIONINDEX_COOKIE', 'saml_sessionindex' );
}
if ( false === defined( 'OSL_SAML_NAMEID_FORMAT_COOKIE' ) ) {
	define( 'OSL_SAML_NAMEID_FORMAT_COOKIE', 'saml_nameid_format' );
}

require_once plugin_dir_path(__FILE__)."php/functions.php";
require_once plugin_dir_path(__FILE__)."php/configuration.php";
require_once plugin_dir_path(__FILE__)."php/opensocial_options.php";
require_once plugin_dir_path(__FILE__)."php/register_site.php";

// add shortcode
add_shortcode('opensocial_login_button', 'osl_show_op_button');
add_shortcode('opensocial_query_params', 'osl_query_params');
add_shortcode('opensocial_member_count', 'osl_op_member_count');

// add menu option for configuration
add_action('admin_menu', 'opensocial_saml_configuration');

// On plugin activate load default options and subscribe site on OpenSocial SSO network
register_activation_hook( __FILE__, 'osl_op_set_options' );
register_activation_hook( __FILE__, 'osl_op_register_site' );

// On plugin deactivate remove default option and unsubscribe site on OpenSocial SSO network
register_deactivation_hook( __FILE__, 'osl_op_del_options' );
register_deactivation_hook( __FILE__, 'osl_op_unsub_site' );

// Check if exists SAML Messages
add_action('init', 'osl_saml_checker', 1);

if (!osl_is_saml_enabled()) {
	return;
}

$prevent_reset_password = get_option('opensocial_saml_customize_action_prevent_reset_password', false);
if ($prevent_reset_password) {
	add_filter ('allow_password_reset', 'osl_disable_password_reset' );
	function osl_disable_password_reset() { return false; }
} else {
	add_action('lost_password', 'osl_saml_lostpassword', 1);
	add_action('retrieve_password', 'osl_saml_lostpassword' , 1);
	add_action('password_reset', 'osl_saml_lostpassword', 1);
}

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'login';

// Handle SLO
if (isset($_COOKIE[OSL_SAML_LOGIN_COOKIE]) && get_option('opensocial_saml_slo')) {
	add_action('init', 'osl_saml_slo', 1);
}

// Handle SSO
if (isset($_GET['saml_sso'])) {

  add_action('init', 'osl_saml_sso', 1);

} else {

	$execute_sso = false;
	$saml_actions = isset($_GET['saml_metadata']) || (strpos($_SERVER['SCRIPT_NAME'], 'alternative_acs.php') !== FALSE);
	$wp_login_page = (strpos($_SERVER['SCRIPT_NAME'], 'wp-login.php') !== FALSE) && $action == 'login' && !isset($_GET['loggedout']);
	$want_to_local_login = isset($_GET['normal']) || (isset($_POST['log']) && isset($_POST['pwd']));
	$want_to_reset = $action == 'lostpassword' || $action == 'rp' || $action == 'resetpass' || (isset($_GET['checkemail']) &&  $_GET['checkemail'] == 'confirm');

	$local_wp_actions = $want_to_local_login || $want_to_reset;

	// plugin hooks into authenticator system
	if (!$local_wp_actions) {
		if ($wp_login_page) {
			$execute_sso = True;
		} else if (!$saml_actions && !isset($_GET['loggedout'])) {
			if (get_option('opensocial_saml_forcelogin')) {
				add_action('init', 'osl_saml_sso', 1);
			}
		}
	} else if ($local_wp_actions) {
		$prevent_local_login = get_option('opensocial_saml_customize_action_prevent_local_login', false);

		if (($want_to_local_login && $prevent_local_login) || ($want_to_reset && $prevent_reset_password)) {		
			$execute_sso = True;
		}
	}

  $keep_local_login_form = get_option('opensocial_saml_keep_local_login', false);
  
	if ($execute_sso && !$keep_local_login_form) {
		add_action('init', 'osl_saml_sso', 1);
	} else {
		add_filter('login_message', 'osl_saml_custom_login_footer');
	}
}

/* EOF */