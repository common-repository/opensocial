<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there! something wrong there please check your wordpress.';
	exit;
}

require_once plugin_dir_path(__FILE__).'compatibility.php';

function osl_query_params() {

	if ($_SERVER['REQUEST_URI'] == '/')
	  return '/wp-login.php';
	else
	  return '/wp-login.php?redirect_to='.$_SERVER['REQUEST_URI'];
}

function osl_show_op_button() 
{
	return '<a class="opensocial_login_button" title="Sign-up / Sign-in" href="'.osl_query_params().'"><span class="opensocial_login_button_text"><span class="op-mb-icon"><svg class="svg-mbp-fa" width="20" height="20" aria-hidden="true" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 496 512"><path fill="currentColor" d="M248 8C111 8 0 119 0 256s111 248 248 248 248-111 248-248S385 8 248 8zm0 96c48.6 0 88 39.4 88 88s-39.4 88-88 88-88-39.4-88-88 39.4-88 88-88zm0 344c-58.7 0-111.3-26.6-146.5-68.2 18.8-35.4 55.6-59.8 98.5-59.8 2.4 0 4.8.4 7.1 1.1 13 4.2 26.6 6.9 40.9 6.9 14.3 0 28-2.7 40.9-6.9 2.3-.7 4.7-1.1 7.1-1.1 42.9 0 79.7 24.4 98.5 59.8C359.3 421.4 306.7 448 248 448z"></path></svg></span>Sign-up / Sign-in</span></a>';
}

function osl_op_member_count ()
{
	$result = count_users();
	return 'Join our '. $result['total_users']. ' members:';
}

function osl_op_get_domain_name () {
	$identity = esc_url(get_site_url());
	$urlparts = parse_url($identity);
	return $urlparts[host];
}

function osl_saml_lostpassword() {
	$target = get_option('opensocial_saml_customize_links_lost_password');
	if (!empty($target)) {
		wp_redirect($target);
		exit;
	}
}

function osl_saml_checker() {
	if (isset($_GET['saml_acs'])) {
		if (empty($_POST['SAMLResponse'])) {
			echo "That ACS endpoint expects a SAMLResponse value sent using HTTP-POST binding. Nothing was found";
			exit();
		}
		osl_saml_acs();
	}
	else if (isset($_GET['saml_sls'])) {
		osl_saml_sls();
	} else if (isset($_GET['saml_metadata'])) {
		osl_saml_metadata();
	} else if (isset($_GET['saml_validate_config'])) {
		osl_saml_validate_config();
	}
}

function osl_is_saml_enabled() {
	$saml_enabled = get_option('opensocial_saml_enabled', 'not defined');
	if ($saml_enabled == 'not defined') {
		if (get_option('opensocial_saml_idp_entityid', 'not defined') == 'not defined') {
			$saml_enabled = false;
		} else {
			$saml_enabled = true;
		}
	} else {
		$saml_enabled = $saml_enabled == 'on'? true : false;
	}
	return $saml_enabled;
}

function osl_initialize_saml() {
	require_once plugin_dir_path(__FILE__).'_toolkit_loader.php';
	require plugin_dir_path(__FILE__).'settings.php';

	if (!osl_is_saml_enabled()) {
		return false;
	}

	try {
		$auth = new OpenSocial_Saml2_Auth($settings);
	} catch (Exception $e) {
		echo '<br>'.__("The OpenSocial SSO/SAML plugin is not correctly configured.", 'opensocial-saml-sso').'<br>';
		echo esc_html($e->getMessage());
		echo '<br>'.__("If you are the administrator", 'opensocial-saml-sso').', <a href="'.esc_url( get_site_url().'/wp-login.php?normal').'">'.__("access using your wordpress credentials", 'opensocial-saml-sso').'</a> '.__("and fix the problem", 'opensocial-saml-sso');
		exit();
	}

	return $auth;
}

function osl_saml_metadata() {
	require_once plugin_dir_path(__FILE__).'_toolkit_loader.php';
	require plugin_dir_path(__FILE__).'settings.php';

	$samlSettings = new OpenSocial_Saml2_Settings($settings, true);
	$metadata = $samlSettings->getSPMetadata();

	header('Content-Type: text/xml');
	echo ent2ncr($metadata);
	exit();
}

function osl_saml_validate_config() {
	saml_load_translations();
	require_once plugin_dir_path(__FILE__).'_toolkit_loader.php';
	require plugin_dir_path(__FILE__).'settings.php';
	require_once plugin_dir_path(__FILE__)."validate.php";
	exit();
}

function osl_saml_sso() {
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		return true;
	}
	
	if (is_user_logged_in()) {
		return true;
	}
	$auth = osl_initialize_saml();
	if ($auth == false) {
		wp_redirect(home_url());
		exit();
	}
	if (isset($_SERVER['REQUEST_URI']) && !isset($_GET['saml_sso'])) {
		$auth->login($_SERVER['REQUEST_URI']);
	} else {
		$auth->login();
	}
	exit();
}

function osl_saml_slo() {
	$slo = get_option('opensocial_saml_slo');

	if (isset($_GET['action']) && $_GET['action']  == 'logout') {
		if (!$slo) {
			wp_logout();
			return false;
		} else {
			$nameId = null;
			$sessionIndex = null;
			$nameIdFormat = null;

			if (isset($_COOKIE[OSL_SAML_NAMEID_COOKIE])) {
				$nameId = $_COOKIE[OSL_SAML_NAMEID_COOKIE];
			}
			if (isset($_COOKIE[OSL_SAML_SESSIONINDEX_COOKIE])) {
				$sessionIndex = $_COOKIE[OSL_SAML_SESSIONINDEX_COOKIE];
			}
			if (isset($_COOKIE[OSL_SAML_NAMEID_FORMAT_COOKIE])) {
				$nameIdFormat = $_COOKIE[OSL_SAML_NAMEID_FORMAT_COOKIE];
			}

			$auth = osl_initialize_saml();
			if ($auth == false) {
				wp_redirect(home_url());
				exit();
			}
			$auth->logout(home_url(), array(), $nameId, $sessionIndex, false, $nameIdFormat);
			return false;
		}
	}
}

function osl_saml_acs() {

	$auth = osl_initialize_saml();
	if ($auth == false) {
		wp_redirect(home_url());
		exit();
	}

	/* Get persmission from wordpress options */
	$permission = get_option('opensocial_permission_enabled');

	$auth->processResponse();

	$errors = $auth->getErrors();
	if (!empty($errors)) {
		echo '<br>'.__("There was at least one error processing the SAML Response").': ';
		foreach($errors as $error) {
			echo esc_html($error).'<br>';
		}
		echo __("Contact the system administrator");
		exit();
	}

	setcookie(OSL_SAML_NAMEID_COOKIE, $auth->getNameId(), time() + YEAR_IN_SECONDS, SITECOOKIEPATH );
	setcookie(OSL_SAML_SESSIONINDEX_COOKIE, $auth->getSessionIndex(), time() + YEAR_IN_SECONDS, SITECOOKIEPATH );
	setcookie(OSL_SAML_NAMEID_FORMAT_COOKIE, $auth->getNameIdFormat(), time() + YEAR_IN_SECONDS, SITECOOKIEPATH );

	$attrs = $auth->getAttributes();

	if (empty($attrs)) {
		$nameid = $auth->getNameId();
		if (empty($nameid)) {
			echo __("The SAMLResponse may contain NameID or AttributeStatement");
			exit();
		}
		$username = $nameid;
		$email = $username;
	} else {
		$usernameMapping = get_option('opensocial_saml_attr_mapping_username');
		$mailMapping =  get_option('opensocial_saml_attr_mapping_mail');

		if (!empty($usernameMapping) && isset($attrs[$usernameMapping]) && !empty($attrs[$usernameMapping][0])){
			$username = $attrs[$usernameMapping][0];
		}
		if (!empty($mailMapping) && isset($attrs[$mailMapping])  && !empty($attrs[$mailMapping][0])){
			$email = $attrs[$mailMapping][0];
		}
	}

	if (empty($username)) {
		echo __("The username could not be retrieved from the IdP and is required");
		exit();
	}
	else if (empty($email)) {
		echo __("The email could not be retrieved from the IdP and is required");
		exit();	
	} else {
		$userdata = array();
		$userdata['user_login'] = wp_slash($username);
		$userdata['user_email'] = wp_slash($email);
	}

	if (!empty($attrs)) {
		$firstNameMapping = get_option('opensocial_saml_attr_mapping_firstname');
		$lastNameMapping = get_option('opensocial_saml_attr_mapping_lastname');
		
		if (!empty($firstNameMapping) && isset($attrs[$firstNameMapping]) && !empty($attrs[$firstNameMapping][0])){
			$userdata['first_name'] = $attrs[$firstNameMapping][0];
		}

		if (!empty($lastNameMapping) && isset($attrs[$lastNameMapping])  && !empty($attrs[$lastNameMapping][0])){
			$userdata['last_name'] = $attrs[$lastNameMapping][0];
		}

		$userdata['role'] = get_option('default_role');
	}

	$matcher = get_option('opensocial_saml_account_matcher');

	if (empty($matcher) || $matcher == 'username') {
		$matcherValue = $userdata['user_login'];
		$user_id = username_exists($matcherValue);
	} else {
		$matcherValue = $userdata['user_email'];
		$user_id = email_exists($matcherValue);
	}

	if ($user_id) {

		$user_meta = get_userdata($user_id);
		$user_role = $user_meta->roles;
		
		if (is_multisite() && !is_user_member_of_blog($user_id, $blog_id)) {
			if (get_option('opensocial_saml_autocreate')) {
				//Exist's but is not user to the current blog id
				$blog_id = get_current_blog_id();
				$result = add_user_to_blog($blog_id, $user_id, $userdata['role']);
			} else {
				$user_id = null;
				echo __("User provided by the IdP "). ' "'. esc_attr($matcherValue). '" '. __("does not exist in this wordpress site and auto-provisioning is disabled.");
				exit();
			}
		}
		if (get_option('opensocial_saml_updateuser')) {
			$userdata['ID'] = $user_id;
			$userdata['role'] = $user_role[0];
			unset($userdata['$user_pass']);
			// Prevent to change the role to the superuser (id=1)
			if ($user_id == 1 && isset($userdata['role'])) {
				unset($userdata['role']);
			}
			$user_id = wp_update_user($userdata);
		}
	} else if ($permission == 'Closed') {
		header("Location: http://signup.opensocial.me/siteclosed?identity=".osl_op_get_domain_name());
		//echo __("<br ><center>User registration is cloased by site administrator...<br ><br ><a href='/'>Go Back</a></center>");
		exit();
	}	else if (get_option('opensocial_saml_autocreate')) {
		if (!validate_username($username)) {
			echo __("The username provided by the IdP"). ' "'. esc_attr($username). '" '. __("is not valid and can't create the user at wordpress");
			exit();
		}
		$userdata['user_pass'] = wp_generate_password();
		$user_id = wp_insert_user($userdata);
	} else {
		echo __("User provided by the IdP "). ' "'. esc_attr($matcherValue). '" '. __("does not exist in wordpress and auto-provisioning is disabled.");
		exit();
	}

	if (is_a($user_id, 'WP_Error')) {
		$errors = $user_id->get_error_messages();
		foreach($errors as $error) {
			echo esc_html($error).'<br>';
		}
		exit();
	} else if ($user_id) {
		wp_set_current_user($user_id);
		$rememberme = false;
		$remembermeMapping = get_option('opensocial_saml_attr_mapping_rememberme');
		if (!empty($remembermeMapping) && isset($attrs[$remembermeMapping]) && !empty($attrs[$remembermeMapping][0])) {
			$rememberme = in_array($attrs[$remembermeMapping][0], array(1, true, '1', 'yes', 'on')) ? true : false;
		}
		wp_set_auth_cookie($user_id, $rememberme);
		setcookie(OSL_SAML_LOGIN_COOKIE, 1, time() + YEAR_IN_SECONDS, SITECOOKIEPATH );
	}

	do_action( 'opensocial_saml_attrs', $attrs, wp_get_current_user(), get_current_user_id() );
	
	if (isset($_REQUEST['RelayState'])) {
		if (!empty($_REQUEST['RelayState']) && ((substr($_REQUEST['RelayState'], -strlen('/wp-login.php')) === '/wp-login.php') || (substr($_REQUEST['RelayState'], -strlen('/alternative_acs.php')) === '/alternative_acs.php'))) {
			wp_redirect(home_url());
		} else {
			if (strpos($_REQUEST['RelayState'], 'redirect_to') !== false) {
				$query = wp_parse_url($_REQUEST['RelayState'], PHP_URL_QUERY);
				//parse_str( $query, $parameters );
				//wp_redirect(urldecode($parameters['redirect_to']));
				wp_redirect(urldecode('/'.explode("redirect_to=/",$query)[1]));
			}  else {
				wp_redirect($_REQUEST['RelayState']);
			}
		}
	} else {
		wp_redirect(home_url());
	}
	exit();
}

function osl_saml_sls() {
	
	$auth = osl_initialize_saml();
	if ($auth == false) {
		wp_redirect(home_url());
		exit();
	}
	
	$retrieve_parameters_from_server = get_option('opensocial_saml_advanced_settings_retrieve_parameters_from_server', false);
	
	if (isset($_GET) && isset($_GET['SAMLRequest'])) {
		$auth->processSLO(false, null, $retrieve_parameters_from_server, 'wp_logout');
	} else {
		$auth->processSLO(false, null, $retrieve_parameters_from_server);
	}
	
	$errors = $auth->getErrors();
	
	if (empty($errors)) {

		wp_logout();
		setcookie(OSL_SAML_LOGIN_COOKIE, 0, time() - 3600, SITECOOKIEPATH );
		setcookie(OSL_SAML_NAMEID_COOKIE, null, time() - 3600, SITECOOKIEPATH );
		setcookie(OSL_SAML_SESSIONINDEX_COOKIE, null, time() - 3600, SITECOOKIEPATH );
		setcookie(OSL_SAML_NAMEID_FORMAT_COOKIE, null, time() - 3600, SITECOOKIEPATH );

		if (get_option('opensocial_saml_forcelogin') && get_option('opensocial_saml_customize_stay_in_wordpress_after_slo')) {
			wp_redirect(home_url().'/wp-login.php?loggedout=true');
		} else {
			if (isset($_REQUEST['RelayState'])) {
				wp_redirect($_REQUEST['RelayState']);
			} else {
				wp_redirect(home_url());
			}
		}
		exit();
	} else {
		echo __("SLS endpoint found an error.");
		foreach($errors as $error) {
			echo esc_html($error).'<br>';
		}
		exit();
	}
}

function osl_saml_custom_login_footer() {
	$saml_login_message = get_option('opensocial_saml_customize_links_saml_login');
	if (empty($saml_login_message)) {
		$saml_login_message = "OpenSocial SAML Login";
	}
    echo '<div style="font-size: 110%;padding:8px;background: #fff;text-align: center;"><a href="'.esc_url( get_site_url().'/wp-login.php?saml_sso') .'">'.esc_html($saml_login_message).'</a></div>';
}

function osl_op_sanitize_text($data)
{
	return sanitize_text_field($data);
}

?>