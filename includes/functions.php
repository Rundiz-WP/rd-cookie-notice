<?php
/**
 * Functions to use globally if this plugin was activated.
 * 
 * Keep the same function name and prefix to make it continuous use without modify the code if switch the plugin from that to this.
 * 
 * @package rd-cookie-notice
 */

// exit if accessed directly
if (!defined('ABSPATH')) {
    exit();
}


if (!function_exists('cn_cookies_accepted')) {
    /**
     * Check if cookies are accepted.
     * 
     * @return bool Return whether cookies are accepted
     */
    function cn_cookies_accepted()
    {
        return (bool) \RdCookieNotice\RdCookieNotice::cookies_accepted();
    }
}// endif;


if (!function_exists('rdcn_cookiesAccepted')) {
    /**
     * Check if cookies are accepted.
     * 
     * This is new function name that use our plugin prefix.
     * 
     * @author Vee W.
     * @return bool Return whether cookies are accepted
     */
    function rdcn_cookiesAccepted()
    {
        return (bool) \RdCookieNotice\RdCookieNotice::cookies_accepted();
    }// rdcn_cookiesAccepted
}// endif;


if (!function_exists('cn_cookies_set')) {
    /**
     * Check if cookies are set.
     * 
     * @return bool Return whether cookies are set
     */
    function cn_cookies_set()
    {
        $RdCookieNotice = \RdCookieNotice\RdCookieNotice::instance();
        return (bool) $RdCookieNotice->cookies_set();
    }
}// endif;


if (!function_exists('rdcn_cookiesSet')) {
    /**
     * Check if cookies are set.
     * 
     * This is new function name that use our plugin prefix.
     * 
     * @author Vee W.
     * @return bool Return whether cookies are set
     */
    function rdcn_cookiesSet()
    {
        $RdCookieNotice = \RdCookieNotice\RdCookieNotice::instance();
        return (bool) $RdCookieNotice->cookies_set();
    }// rdcn_cookiesSet
}// endif;
