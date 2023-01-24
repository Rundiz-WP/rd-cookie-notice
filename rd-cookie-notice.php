<?php
/**
 * Plugin Name: Rundiz Cookie Notice
 * Description: Rd Cookie Notice allows you to elegantly inform users that your site uses cookies and helps you comply with the EU GDPR cookie law and CCPA regulations.
 * Version: 0.2.5
 * Requires at least: 4.0
 * Requires PHP: 7.0
 * Author: Vee W.
 * Author URI: https://rundiz.com/
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 * Text Domain: rd-cookie-notice
 * Domain Path: /languages
 * Update URI: false
 *
 * @package rd-cookie-notice
*/

/**
 * This plugin was copied from its original "Cookie Notice" by "dFactory" and then modified by "ZigPress".
 */

// exit if accessed directly
if (!defined('ABSPATH')) {
    exit();
}

if (!defined('RDCNDB_VERSION')) {
    define('RDCNDB_VERSION', '1.0.0');// define DB version here.
}

// register autoload namespace for this plugin only.
require 'RdCookieNotice/Autoload.php';
$Autoload = new \RdCookieNotice\Psr4AutoloaderClass();
$Autoload->register();
$Autoload->addNamespace('RdCookieNotice', __DIR__ . DIRECTORY_SEPARATOR . 'RdCookieNotice');
unset($Autoload);

require_once 'RdCookieNotice.php';

if (!defined('RDCN_PLUGINFILE')) {
    define('RDCN_PLUGINFILE', __FILE__);
}

if (!defined('RDCN_VERSION')) {
    $pluginData = (function_exists('get_file_data') ? get_file_data(__FILE__, ['Version' => 'Version']) : null);
    $pluginVersion = (isset($pluginData['Version']) ? $pluginData['Version'] : gmdate('Ym'));
    unset($pluginData);
    define('RDCN_VERSION', $pluginVersion);
    unset($pluginVersion);
}


/**
 * Initialize Rundiz Cookie Notice.
 */
function runRdCookieNotice() 
{
    static $instance;

    // first call to instance() initializes the plugin
    if (!$instance instanceof \RdCookieNotice\RdCookieNotice) {
        $instance = \RdCookieNotice\RdCookieNotice::instance();
    }

    return $instance;
}

$rdCookieNotice = runRdCookieNotice();
