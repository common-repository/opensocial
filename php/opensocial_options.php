<?php

	if (!defined('ABSPATH')) exit; // Exit if accessed directly

  // Make sure we don't expose any info if called directly
  if ( !function_exists( 'add_action' ) ) {
    echo 'Hi there!  something wrong there please check your wordpress.';
    exit;
  }

  // $urlparts = parse_url(site_url());
  // $domain = $urlparts[host];

  function osl_op_set_options() 
  {
   
    update_option('opensocial_saml_enabled', '');
    update_option('opensocial_saml_idp_entityid', 'https://sso.opensocial.me/simplesaml/saml2/idp/metadata.php');
    update_option('opensocial_saml_idp_sso', 'https://sso.opensocial.me/simplesaml/saml2/idp/SSOService.php');
    update_option('opensocial_saml_idp_slo', 'https://sso.opensocial.me/simplesaml/saml2/idp/SingleLogoutService.php');
    update_option('opensocial_saml_idp_x509cert', '-----BEGIN CERTIFICATE-----
MIID7TCCAtWgAwIBAgIJALIl3gQ3Y1YdMA0GCSqGSIb3DQEBCwUAMIGMMQswCQYD
VQQGEwJVUzEQMA4GA1UECAwHR29lcmdpYTEPMA0GA1UEBwwGU3ltcm5hMRAwDgYD
VQQKDAdMYWJseW54MQswCQYDVQQLDAJJVDEZMBcGA1UEAwwQc3NvLnNjaWNsb3Vk
Lm5ldDEgMB4GCSqGSIb3DQEJARYRdWRyYXpAbGFibHlueC5jb20wHhcNMTgxMDE4
MDYxOTMwWhcNMjgxMDE3MDYxOTMwWjCBjDELMAkGA1UEBhMCVVMxEDAOBgNVBAgM
B0dvZXJnaWExDzANBgNVBAcMBlN5bXJuYTEQMA4GA1UECgwHTGFibHlueDELMAkG
A1UECwwCSVQxGTAXBgNVBAMMEHNzby5zY2ljbG91ZC5uZXQxIDAeBgkqhkiG9w0B
CQEWEXVkcmF6QGxhYmx5bnguY29tMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIB
CgKCAQEApf1lizoLWD3iWiBFyHIDX4vnmrqmSUDhTPYHKbHJiVtdEjp71yEqZoZM
HO9h4yH713+pAWJC9zX2Q7aqiRTw3EVNr26qHwV4fT/jazvk3sdUCEQIBY6wCAKn
sCbgu/2CPW4pmZMPEV1YPQZL6huf2E+UAL99RsNf2hjFmxRd6ImUtwJ6d1PkmjJ1
PhRXLmDPGvC7vlPXnSGzhXZFcU11nRK0GoB7cH5rddZ+8zMnDlvu66A3oZc41gk/
KJsDoJuclltGxzUtYSjpJxf/yHHRbFDndHUFyS8sYbj9JoyPMX5glfJbIbD/uVyd
FV8URZGFL3dDgGu4oGyqR+qhdP2P+wIDAQABo1AwTjAdBgNVHQ4EFgQUx9y8MoIF
hOroxukZbxOwOvJd9wgwHwYDVR0jBBgwFoAUx9y8MoIFhOroxukZbxOwOvJd9wgw
DAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQsFAAOCAQEALCaeGLmrzHON+/djTI38
xr7iJeBWooKz+7wvyhGMNATpPUkqIiRIOjjyIfczA62ENiwgyU/DmS/B9Gr7pLoL
9PeR3lkH+VAa0HiLzsDkBydjedfMF6ETHcteYZrwmi0FSEmiXtFblQqX3X3jAnYO
D5iKDgosTKtcYx5U/m/AUtmD8mBQlD0LnUY2zSKGUyK4kGnemJjNHnYWwLgbJydU
DsujVEqaUlMpeYsDIQ5cvaWVOL5vggi66CnrA4F+j3ITuTu6KDIEympN9zcuvypJ
y0uEKPm+1TfrGnTwSKQzutdqEvnMHo4vx1VPszag4GA0HRgetN00UbG8j/9J1iLo
jQ==
-----END CERTIFICATE-----
');

    update_option('opensocial_saml_keep_local_login', '');
    update_option('opensocial_saml_forcelogin', '');
    update_option('opensocial_saml_autocreate', 'on');
    update_option('opensocial_saml_updateuser', 'on');
    update_option('opensocial_saml_slo', 'on');
    update_option('opensocial_saml_account_matcher', 'email');
    update_option('opensocial_saml_attr_mapping_username', 'email');
    update_option('opensocial_saml_attr_mapping_mail', 'email');
    update_option('opensocial_saml_attr_mapping_firstname', 'firstname');
    update_option('opensocial_saml_attr_mapping_lastname', 'lastname');
    update_option('opensocial_saml_role_mapping_administrator', 'Administrator');
    update_option('opensocial_saml_role_mapping_subscriber', 'Subscriber');
    update_option('opensocial_saml_customize_action_prevent_reset_password', 'on');
    update_option('opensocial_saml_customize_action_prevent_change_password', 'on');
    update_option('opensocial_saml_customize_action_prevent_change_mail', 'on');
    update_option('opensocial_saml_advanced_signaturealgorithm', 'http://www.w3.org/2000/09/xmldsig#rsa-sha1');
    update_option('opensocial_saml_advanced_digestalgorithm', 'http://www.w3.org/2000/09/xmldsig#rsa-sha1');
    update_option('opensocial_saml_advanced_nameidformat', 'emailAddress');
    update_option('opensocial_saml_advanced_settings_sp_entity_id',  esc_url(get_site_url()));
    update_option('opensocial_permission_enabled',  'Open');
    update_option('opensocial_social_auth', array('social_login' => array('linkedin','google','facebook','github','twitter')));
    update_option('opensocial_email_login', 'email');

    return true;

  }


  function osl_op_del_options() 
  {

    delete_option('opensocial_saml_enabled');
    delete_option('opensocial_saml_idp_entityid');
    delete_option('opensocial_saml_idp_sso');
    delete_option('opensocial_saml_idp_slo');
    delete_option('opensocial_saml_idp_x509cert');
    delete_option('opensocial_saml_forcelogin');
    delete_option('opensocial_saml_autocreate');
    delete_option('opensocial_saml_updateuser');
    delete_option('opensocial_saml_slo');
    delete_option('opensocial_saml_account_matcher');
    delete_option('opensocial_saml_attr_mapping_username');
    delete_option('opensocial_saml_attr_mapping_mail');
    delete_option('opensocial_saml_attr_mapping_firstname');
    delete_option('opensocial_saml_attr_mapping_lastname');
    delete_option('opensocial_saml_role_mapping_administrator');
    delete_option('opensocial_saml_role_mapping_subscriber');
    delete_option('opensocial_saml_customize_action_prevent_reset_password');
    delete_option('opensocial_saml_customize_action_prevent_change_password');
    delete_option('opensocial_saml_customize_action_prevent_change_mail');
    delete_option('opensocial_saml_advanced_signaturealgorithm');
    delete_option('opensocial_saml_advanced_digestalgorithm');
    delete_option('opensocial_saml_advanced_nameidformat');
    delete_option('opensocial_saml_advanced_settings_sp_entity_id');
    delete_option('opensocial_permission_enabled');
    delete_option('opensocial_saml_keep_local_login');
    delete_option('opensocial_terms_enabled');
    delete_option('opensocial_privacy_enabled');
    delete_option('opensocial_saml_site_logo');
    delete_option('opensocial_saml_site_background');
    delete_option('opensocial_help_enabled');
    delete_option('opensocial_announce_message_title');
    delete_option('opensocial_announce_message');
    delete_option('opensocial_closed_message');
    delete_option('opensocial_social_auth');
    delete_option('opensocial_email_login');
    return true;

  }


?>