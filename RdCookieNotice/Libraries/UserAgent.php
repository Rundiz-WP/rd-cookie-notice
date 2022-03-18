<?php
/**
 * @license http://opensource.org/licenses/MIT MIT
 */


namespace RdCookieNotice\Libraries;


/**
 * User agent class.
 * 
 * @since 0.2.4
 */
class UserAgent
{


    /**
     * Check if this user agent is search engine or not.
     * 
     * @link https://stackoverflow.com/a/15047834/128761 Original source code.
     * @return bool Return `true` if it is, `false` for otherwise.
     */
    public function isSearchEngine(): bool
    {
        if (
            isset($_SERVER['HTTP_USER_AGENT']) &&
            preg_match('/bot|crawl|slurp|spider|mediapartners/i', $_SERVER['HTTP_USER_AGENT'])
        ) {
            return true;
        }
        return false;
    }// isSearchEngine


}
