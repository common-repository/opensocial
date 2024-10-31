<?php

  if (!defined('ABSPATH')) exit; // Exit if accessed directly

  // Make sure we don't expose any info if called directly
  if ( !function_exists( 'add_action' ) ) {
    echo 'Hi there! something wrong there please check your wordpress.';
    exit;
  }

  require_once (dirname(__FILE__) . "/functions.php");
  require_once (dirname(__FILE__) . "/api.php");

  function osl_op_register_site()
  {
    $api = new opSAMLapiCall;

    $identity = esc_url(get_site_url());
    $acs = $identity.'/wp-login.php?saml_acs';
    $sls = $identity.'/wp-login.php?saml_sls';

    $post_data = array(
      'identity' => $identity,
      'domain' => osl_op_get_domain_name(),
      'acs' => $acs,
      'sls' => $sls
    );

    $data = json_encode($post_data);
    $msg = $api->postData('subscriber', $data);

    return true;
  }

  function osl_op_unsub_site()
  {

    $api = new opSAMLapiCall;

    $post_data = array(
      'identity' => esc_url(get_site_url()),
    );

    $data = json_encode($post_data);
    $msg = $api->delData('subscriber', $data);

    return true;
  }

?>