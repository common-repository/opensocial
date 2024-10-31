<?php

	if (!defined('ABSPATH')) exit; // Exit if accessed directly

  class opSAMLapiCall {

    var $api_url = 'https://signup.opensocial.me/api/';

    function postData ($url, $data)
    {

      $headers =  array( 
        'Content-type' => 'application/json',
        'Referer' => esc_url(get_site_url())
      );

      $result = wp_remote_post($this->api_url.$url, array(
        'method' => 'POST',
        'timeout' => 45,
        'redirection' => 5,
        'httpversion' => '1.0',
        'blocking' => true,
        'headers' => $headers,
        'body' => $data,
        'cookies' => array()
        )
      );

      return json_decode('ok', true);

    }

    function updateData ($url, $data)
    {
      
      $headers =  array( 
        'Content-type' => 'application/json',
        'Referer' => esc_url(get_site_url())
      );

      $result = wp_remote_post($this->api_url.$url, array(
        'method' => 'PATCH',
        'timeout' => 45,
        'redirection' => 5,
        'httpversion' => '1.0',
        'blocking' => true,
        'headers' => $headers,
        'body' => $data,
        'cookies' => array()
        )
      );
  
      return json_decode('ok', true);

    }

    function delData ($url, $data)
    {

      $headers =  array( 
        'Content-type' => 'application/json',
        'Referer' => esc_url(get_site_url())
      );

      $result = wp_remote_post($this->api_url.$url, array(
        'method' => 'DELETE',
        'timeout' => 45,
        'redirection' => 5,
        'httpversion' => '1.0',
        'blocking' => true,
        'headers' => $headers,
        'body' => $data,
        'cookies' => array()
        )
      );
  
      return json_decode('ok', true);

    }

  }

?>