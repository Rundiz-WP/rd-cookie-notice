<?php
/**
 * @license http://opensource.org/licenses/MIT MIT
 * @package rd-cookie-notice
 * @since 0.2.6
 * @deprecated since 0.2.6 This class will be removed in the future, please update your class called from `\RdCookieNotice` to `\RdCookieNotice\RdCookieNotice`.
 */


/**
 * The mirror (extends) class of `\RdCookieNotice\RdCookieNotice()` for let some plugin/theme had a called to `RdCookieNotice()` still working.
 * 
 * @deprecated since 0.2.6 This class will be removed in the future, please update your class called from `\RdCookieNotice` to `\RdCookieNotice\RdCookieNotice`.
 */
class RdCookieNotice extends \RdCookieNotice\RdCookieNotice
{


    /**
     * {@inheritDoc}
     */
    public static function instance()
    {
        _doing_it_wrong(__CLASS__ . '::' . __FUNCTION__ . '()', 'The class RdCookieNotice() is now moved to \RdCookieNotice\RdCookieNotice(). Use \RdCookieNotice\RdCookieNotice::instance() instead.', '0.2.6');

        return parent::instance();
    }// instance


}
