<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  something wrong there please check your wordpress.';
	exit;
}

require_once ( ABSPATH . WPINC . '/pluggable.php' );
require_once (dirname(__FILE__) . "/compatibility.php");
require_once (dirname(__FILE__) . "/lib/Saml2/Constants.php");
require_once (dirname(__FILE__) . "/extlib/xmlseclibs/xmlseclibs.php");
require_once (dirname(__FILE__) . "/api.php");

$api = new opSAMLapiCall;

/* Check if there is post for site logo or background upload request */
if (array_key_exists('upload_site_logo', $_POST))
{

  check_admin_referer('op_upload_logo', 'op_submit_logo');

  if (empty(sanitize_text_field($_POST['logo_url']))) {
    pass;
  } else {
    update_option('opensocial_saml_site_logo', sanitize_text_field($_POST['logo_url']));  
    $post_data = array(
      'identity'  => esc_url(get_site_url()),
      'site_logo' => sanitize_text_field($_POST['logo_url'])
    );
    $data = json_encode($post_data);
    $msg = $api->updateData('subscriber', $data);
  }
}

if (array_key_exists('upload_site_background', $_POST))
{

  check_admin_referer('op_upload_bg', 'op_submit_bg');
  
  if (empty(sanitize_text_field($_POST['bg_url']))) {
    pass;
  } else {
    update_option('opensocial_saml_site_background', sanitize_text_field($_POST['bg_url']));
    $post_data = array(
      'identity'  => esc_url(get_site_url()),
      'site_bg' => sanitize_text_field($_POST['bg_url'])
    );
    $data = json_encode($post_data);
    $msg = $api->updateData('subscriber', $data);
  }
}

function plugin_section_status_text() {
  echo "<p>".__("Use this flag for enable or disable the SAML support.", 'opensocial-saml-sso')."</p>";
}

function plugin_setting_boolean_opensocial_saml_enabled() {
  $value = get_option('opensocial_saml_enabled');
  echo '<input type="checkbox" name="opensocial_saml_enabled" id="opensocial_saml_enabled"
      '.($value ? 'checked="checked"': '').'>'.
      '<p class="description">'.__("Check it in order to enable the SAML plugin.", 'opensocial-saml-sso').'</p>';
}

function plugin_setting_boolean_opensocial_social_auth() {

  $options = get_option( 'opensocial_social_auth' );
  $email = get_option( 'opensocial_email_login' );

  $linkedin = in_array('linkedin', $options['social_login']) ? 'checked' : '';
  $google = in_array('google', $options['social_login']) ? 'checked' : '';
  $facebook = in_array('facebook', $options['social_login']) ? 'checked' : '';
  $github = in_array('github', $options['social_login']) ? 'checked' : '';
  $twitter = in_array('twitter', $options['social_login']) ? 'checked' : '';
  
  echo '<i class="fa fa-linkedin op-social-font"></i> &nbsp;<input type="checkbox" name="opensocial_social_auth[social_login][]" value="linkedin" '.$linkedin.'>&nbsp; &nbsp;';
  echo '<i class="fa fa-google op-social-font"></i> &nbsp;<input type="checkbox" name="opensocial_social_auth[social_login][]" value="google" '.$google.'>&nbsp; &nbsp;';
  echo '<i class="fa fa-facebook op-social-font"></i> &nbsp;<input type="checkbox" name="opensocial_social_auth[social_login][]" value="facebook" '.$facebook.'>&nbsp; &nbsp;';
  echo '<i class="fa fa-github op-social-font"></i> &nbsp;<input type="checkbox" name="opensocial_social_auth[social_login][]" value="github" '.$github.'>&nbsp; &nbsp;';
  echo '<i class="fa fa-twitter op-social-font"></i> &nbsp;<input type="checkbox" name="opensocial_social_auth[social_login][]" value="twitter" '.$twitter.'>&nbsp; &nbsp;';
  echo 'Email &nbsp;<input type="checkbox" name="opensocial_email_login" value="email" '.($email ? 'checked': '').'>';
  
}

function plugin_setting_boolean_opensocial_saml_keep_local_login() {
  $value = get_option('opensocial_saml_keep_local_login');
  echo '<input type="checkbox" name="opensocial_saml_keep_local_login" id="opensocial_saml_keep_local_login"
      '.($value ? 'checked="checked"': '').'>'.
      '<p class="description">'.__('Enable/disable the normal login form. If disabled, instead of the WordPress login form, WordPress will excecute the SP-initiated SSO flow. If enabled the normal login form is displayed and a link to initiate that flow is displayed.<p class="description">If you do not want to enable local login then you can also bypass SSO and get the login page using '.esc_url(get_site_url()).'/wp-login.php?normal</p>', 'opensocial-saml-sso').'</p>';
}

function plugin_section_options_text() {
  echo "<p>".__("This section customizes the behavior of the plugin.", 'opensocial-saml-sso')."</p>";
}

function plugin_permission_text() {
  echo "<p>".__("This section customizes the permission behavior of your site.", 'opensocial-saml-sso')."</p>";
}

function plugin_branding_text() {
  echo "<p>".__("Enter terms of use and privacy url which will display on login page.", 'opensocial-saml-sso')."</p>";
}

function social_login_text() {
  echo "<p>".__("Select social login for authentication", 'opensocial-saml-sso')."</p>";
}

function plugin_setting_boolean_opensocial_permission_enabled() {
  ?>
  Open: <input type="radio" name="opensocial_permission_enabled" id="opensocial_permission_enabled" value="Open" <?php checked('Open', get_option('opensocial_permission_enabled'), true); ?>> &nbsp;
  Closed: <input type="radio" name="opensocial_permission_enabled" id="opensocial_permission_enabled" value="Closed" <?php checked('Closed', get_option('opensocial_permission_enabled'), true);?> >
  <p class="description"><?php echo __("Select the <strong>(Open)</strong> option if you want to let any one login on your site.", 'opensocial-saml-sso');?></p>
  <?php
}

function plugin_setting_boolean_opensocial_closed_enabled() {
  echo '<div class="op_closed_msg">';
  echo '<textarea rows="4" cols="50" name="opensocial_closed_message" id="opensocial_closed_message">'.esc_attr(get_option('opensocial_closed_message')).'</textarea>';
  echo '<p class="description">'.__("Put the site closed message.", "opensocial-saml-sso").'</p>';
  echo '</div>';
}

function plugin_setting_boolean_opensocial_terms_enabled() {
  echo '<input type="text" required="" name="opensocial_terms_enabled" id="opensocial_terms_enabled"  value= "'.esc_attr(get_option('opensocial_terms_enabled')).'" size="80">';
}

function plugin_setting_boolean_opensocial_privacy_enabled() {
  echo '<input type="text" required="" name="opensocial_privacy_enabled" id="opensocial_privacy_enabled" value= "'.esc_attr(get_option('opensocial_privacy_enabled')).'" size="80">';
}

function opensocial_announce_message_title() {
  echo '<input type="text" name="opensocial_announce_message_title" id="opensocial_announce_message_title" value= "'.esc_attr(get_option('opensocial_announce_message_title')).'" size="52" style="margin-bottom: 0px;">';
}

function opensocial_announce_message() {
  echo '<textarea rows="4" cols="50" name="opensocial_announce_message" id="opensocial_announce_message">'.esc_attr(get_option('opensocial_announce_message')).'</textarea>';
  echo '<p class="description">'.__("Put the announcement message here which display on login page.", "opensocial-saml-sso").'</p>';
}


function plugin_setting_boolean_opensocial_help_enabled() {
  echo '<input type="text" required="" name="opensocial_help_enabled" id="opensocial_help_enabled" value= "'.esc_attr(get_option('opensocial_help_enabled')).'" size="80">';
  echo '<p class="description">'.__("Define the email address so users can send an email in case of login issues.", "opensocial-saml-sso").'</p>';
}

function opensocial_saml_configuration_render() {
  require_once "api.php";
  $api = new opSAMLapiCall;
  $title = __("SSO/SAML Settings", 'opensocial-saml-sso');
  ?>
    <div class="wrap">
      <div class="alignleft">
        <a href="http://www.opensocial.me"><img style="width: 190px;" src="<?php echo esc_url( plugins_url('opensocial.png', dirname(__FILE__)) );?>"></a>
      </div>
      <div class="alignright">
        <a href="<?php echo esc_url( get_site_url().'/wp-login.php?saml_metadata' ); ?>" target="blank"><?php echo __("Go to the metadata of this SP", 'opensocial-saml-sso');?></a><br>
      </div>
      <div style="clear:both"></div>
      <h2><?php echo esc_html( $title ); ?></h2>
      <form action="options.php" method="post" name="op_configuration" id="op_configuration">

        <?php settings_fields('opensocial_saml_configuration'); ?>
        <?php do_settings_sections('opensocial_saml_configuration'); ?>

        <p class="submit">
          <input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
        </p>

        <!-- Update privacy and terms of url in opensocial databaes -->
        <?php
          $post_data = array(
            'identity'  => esc_url(get_site_url()),
            'terms_of_use' => esc_attr(get_option('opensocial_terms_enabled')),
            'privacy_statement' => esc_attr(get_option('opensocial_privacy_enabled')),
            'need_help' => esc_attr(get_option('opensocial_help_enabled')),
            'site_mode' => esc_attr(get_option('opensocial_permission_enabled')),
            'message_title' => esc_attr(get_option('opensocial_announce_message_title')),
            'message' => esc_attr(get_option('opensocial_announce_message')),
            'closed_message' => esc_attr(get_option('opensocial_closed_message')),
            'auth_options' => get_option('opensocial_social_auth'),
            'email_login' => esc_attr(get_option('opensocial_email_login'))
          );
          $data = json_encode($post_data);
          $msg = $api->updateData('subscriber', $data);
        ?>

      </form>

        <?php
          /* Include Site logo & background upload function */
          require_once (dirname(__FILE__) . "/site_logo_upload.php");
          require_once (dirname(__FILE__) . "/background_upload.php");
        ?>
      
    <div style="margin-top: 30px;"><strong>Note:</strong> 
      <p>You can use <strong>[opensocial_login_button]</strong> shortcode to display OpenSocial login button anywhere on your site.</p>
      <p>You can use <strong>[opensocial_member_count]</strong> shortcode to display total number of registered users.</p>
    </div>

    </div>
  <?php
}

function opensocial_saml_configuration() {
  
  $current_screen = add_submenu_page( 'options-general.php', 'OpenSocial SSO/Settings', 'OpenSocial SSO/Settings', 'manage_options', 'opensocial_saml_configuration', 'opensocial_saml_configuration_render');

  $helpText = '<p>' . __('OpenSocial Wordpress Plugin is a plugin allowing your users to easily authenticate into your Wordpress site. OpenSocial is a SSO one-click service allowing users to authenticate with their Google, Facebook, Twitter, LinkedIn, Github or OpenSocial accounts. The OpenSocial Wordpress Plugin is backed by the OpenSocial SSO service', 'opensocial-saml-sso') . '</p>' .
    '<p><strong>' . __('For more information', 'opensocial-saml-sso') . '</strong> '.__("access to the", 'opensocial-saml-sso').' <a href="https://www.opensocial.me" target="_blank">'.__("Plugin Info", 'opensocial-saml-sso').'</a> ' .
    __("or visit", 'opensocial-saml-sso') . ' <a href="http://www.opensocial.me/" target="_blank">OpenSocial.me</a>' . '</p>';

  $current_screen = convert_to_screen($current_screen);
  WP_Screen::add_old_compat_help($current_screen, $helpText);

  $option_group = 'opensocial_saml_configuration';

  /* Status */
  add_settings_section('status', __('STATUS', 'opensocial-saml-sso'), 'plugin_section_status_text', $option_group);
  register_setting($option_group, 'opensocial_saml_enabled');
  add_settings_field('opensocial_saml_enabled', __('Enable', 'opensocial-saml-sso'), "plugin_setting_boolean_opensocial_saml_enabled", $option_group, 'status');

  /* Keep local login */
  add_settings_section('options', __('OPTIONS', 'opensocial-saml-sso'), 'plugin_section_options_text', $option_group);
  register_setting($option_group, 'opensocial_saml_keep_local_login');
  add_settings_field('opensocial_saml_keep_local_login', __('Keep Local login', 'opensocial-saml-sso'), "plugin_setting_boolean_opensocial_saml_keep_local_login", $option_group, 'options');

  /* Permissions */
  add_settings_section('permissions', __('Permissions', 'opensocial-saml-sso'), 'plugin_permission_text', $option_group);
  register_setting($option_group, 'opensocial_permission_enabled');
  add_settings_field('opensocial_permission_enabled', __('Permission Type', 'opensocial-saml-sso'), "plugin_setting_boolean_opensocial_permission_enabled", $option_group, 'permissions');
  register_setting($option_group, 'opensocial_closed_message', 'osl_op_sanitize_text');
  add_settings_field('opensocial_closed_message', __('', 'opensocial-saml-sso'), "plugin_setting_boolean_opensocial_closed_enabled", $option_group, 'permissions');

  /* Terms of use */
  add_settings_section('site_branding', __('Site Branding', 'opensocial-saml-sso'), 'plugin_branding_text', $option_group);
  register_setting($option_group, 'opensocial_terms_enabled', 'osl_op_sanitize_text');
  add_settings_field('opensocial_terms_enabled', __('Terms of use URL', 'opensocial-saml-sso'), "plugin_setting_boolean_opensocial_terms_enabled", $option_group, 'site_branding');
  register_setting($option_group, 'opensocial_privacy_enabled', 'osl_op_sanitize_text');
  add_settings_field('opensocial_privacy_enabled', __('Privacy URL', 'opensocial-saml-sso'), "plugin_setting_boolean_opensocial_privacy_enabled", $option_group, 'site_branding');
  register_setting($option_group, 'opensocial_announce_message_title', 'osl_op_sanitize_text');
  add_settings_field('opensocial_announce_message_title', __('Message Title', 'opensocial-saml-sso'), "opensocial_announce_message_title", $option_group, 'site_branding');
  register_setting($option_group, 'opensocial_announce_message', 'osl_op_sanitize_text');
  add_settings_field('opensocial_announce_message', __('Message Details', 'opensocial-saml-sso'), "opensocial_announce_message", $option_group, 'site_branding');
  register_setting($option_group, 'opensocial_help_enabled', 'osl_op_sanitize_text');
  add_settings_field('opensocial_help_enabled', __('Need Help?', 'opensocial-saml-sso'), "plugin_setting_boolean_opensocial_help_enabled", $option_group, 'site_branding');

  /* Social Logins */
  add_settings_section('social_login', __('Authentication Options', 'opensocial-saml-sso'), 'social_login_text', $option_group);
  register_setting($option_group, 'opensocial_social_auth');
  register_setting($option_group, 'opensocial_email_login');
  add_settings_field('opensocial_social_auth', __('Choose Login Type:', 'opensocial-saml-sso'), "plugin_setting_boolean_opensocial_social_auth", $option_group, 'social_login');


  
}

add_action( 'admin_footer', 'display_closed_message_box' );
function display_closed_message_box() {
	?><script type='text/javascript'>
  var sitemode = '<?php echo get_option('opensocial_permission_enabled'); ?>';
  if ( sitemode === 'Closed') {
    jQuery('.op_closed_msg').show();
  } else {
    jQuery('.op_closed_msg').hide();
  }

  jQuery('#op_configuration :radio').change(function (event) {
    var permission = jQuery(this).val();
    if ( permission == 'Closed') {
      jQuery('.op_closed_msg').show();
    } else {
      jQuery('.op_closed_msg').hide();
    }
  });

  </script><?php
} 
?>