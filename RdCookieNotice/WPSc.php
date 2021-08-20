<?php
/**
 * @license http://opensource.org/licenses/MIT MIT
 * @package rd-cookie-notice
 */


namespace RdCookieNotice;


/**
 * WP Super Cache class.
 * 
 * @since 0.2.0
 */
class WPSC
{


    /**
     * Add WP Super Cache cookie.
     */
    public function addCookie()
    {
        do_action('wpsc_add_cookie', 'cookie_notice_accepted');
    }// addCookie


    /**
     * Delete WP Super Cache cookie.
     */
    public function deleteCookie()
    {
        do_action('wpsc_delete_cookie', 'cookie_notice_accepted');
    }// deleteCookie


}
