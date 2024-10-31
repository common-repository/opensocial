<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

$libDir = dirname(__FILE__) . '/lib/Saml2/';
$extlibDir = dirname(__FILE__) . '/extlib/';

// Load first external libs
require_once($extlibDir . 'xmlseclibs/xmlseclibs.php');

$folderInfo = scandir($libDir);

foreach ($folderInfo as $element) {
    if (is_file($libDir.$element) && (substr($element, -4) === '.php')) {
        require_once($libDir.$element);
    }
}
