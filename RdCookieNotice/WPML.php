<?php
/**
 * @license http://opensource.org/licenses/MIT MIT
 * @package rd-cookie-notice
 */


namespace RdCookieNotice;


/**
 * WPML (and Polylang) class.
 * 
 * @since 0.2.0
 */
class WPML
{


    /**
     * @var \RdCookieNotice
     */
    protected $RdCookieNotice;


    /**
     * Class constructor.
     * 
     * @param \RdCookieNotice $RdCookieNotice
     */
    public function __construct(\RdCookieNotice $RdCookieNotice)
    {
        $this->RdCookieNotice = $RdCookieNotice;
    }// __construct


    /**
     * Register strings
     *
     * @return void
     */
    public function registerStrings()
    {
        if (defined('ICL_SITEPRESS_VERSION') && version_compare(ICL_SITEPRESS_VERSION, '3.2', '>=')) {
            // if WPML >= 3.2
            /* @var $wpdb \wpdb */
            global $wpdb;

            // prepare strings
            $strings = [
                'Message in the notice' => $this->RdCookieNotice->options['general']['message_text'],
                'Button text' => $this->RdCookieNotice->options['general']['accept_text'],
                'Refuse button text' => $this->RdCookieNotice->options['general']['refuse_text'],
                'Revoke message text' => $this->RdCookieNotice->options['general']['revoke_message_text'],
                'Revoke button text' => $this->RdCookieNotice->options['general']['revoke_text'],
                'Privacy policy text' => $this->RdCookieNotice->options['general']['see_more_opt']['text'],
                'Custom link' => $this->RdCookieNotice->options['general']['see_more_opt']['link']
            ];

            // get query results
            $results = $wpdb->get_col($wpdb->prepare('SELECT name FROM ' . $wpdb->prefix . 'icl_strings WHERE context = %s', 'Rd Cookie Notice'));

            // check results
            foreach ($strings as $string => $value) {
                // string does not exist?
                if (!in_array($string, $results, true)) {
                    // register string
                    do_action('wpml_register_single_string', 'Rd Cookie Notice', $string, $value);
                }
            }
            unset($string, $value);

            unset($results, $strings);
        } elseif (function_exists('icl_register_string')) {
            // if WPML and Polylang compatibility
            icl_register_string('Rd Cookie Notice', 'Message in the notice', $this->options['general']['message_text']);
            icl_register_string('Rd Cookie Notice', 'Button text', $this->options['general']['accept_text']);
            icl_register_string('Rd Cookie Notice', 'Refuse button text', $this->options['general']['refuse_text']);
            icl_register_string('Rd Cookie Notice', 'Revoke message text', $this->options['general']['revoke_message_text']);
            icl_register_string('Rd Cookie Notice', 'Revoke button text', $this->options['general']['revoke_text']);
            icl_register_string('Rd Cookie Notice', 'Privacy policy text', $this->options['general']['see_more_opt']['text']);
            icl_register_string('Rd Cookie Notice', 'Custom link', $this->options['general']['see_more_opt']['link']);
        }
    }// registerStrings


    /**
     * Set options for `HTML->displayCookieNotice()` method.
     */
    public function setOptionsDisplayCookies()
    {
        if (defined('ICL_SITEPRESS_VERSION') && version_compare(ICL_SITEPRESS_VERSION, '3.2', '>=')) {
            // if WPML >= 3.2
            $this->RdCookieNotice->options['general']['message_text'] = apply_filters('wpml_translate_single_string', $this->RdCookieNotice->options['general']['message_text'], 'Rd Cookie Notice', 'Message in the notice');
            $this->RdCookieNotice->options['general']['accept_text'] = apply_filters('wpml_translate_single_string', $this->RdCookieNotice->options['general']['accept_text'], 'Rd Cookie Notice', 'Button text');
            $this->RdCookieNotice->options['general']['refuse_text'] = apply_filters('wpml_translate_single_string', $this->RdCookieNotice->options['general']['refuse_text'], 'Rd Cookie Notice', 'Refuse button text');
            $this->RdCookieNotice->options['general']['revoke_message_text'] = apply_filters('wpml_translate_single_string', $this->RdCookieNotice->options['general']['revoke_message_text'], 'Rd Cookie Notice', 'Revoke message text');
            $this->RdCookieNotice->options['general']['revoke_text'] = apply_filters('wpml_translate_single_string', $this->RdCookieNotice->options['general']['revoke_text'], 'Rd Cookie Notice', 'Revoke button text');
            $this->RdCookieNotice->options['general']['see_more_opt']['text'] = apply_filters('wpml_translate_single_string', $this->RdCookieNotice->options['general']['see_more_opt']['text'], 'Rd Cookie Notice', 'Privacy policy text');
            $this->RdCookieNotice->options['general']['see_more_opt']['link'] = apply_filters('wpml_translate_single_string', $this->RdCookieNotice->options['general']['see_more_opt']['link'], 'Rd Cookie Notice', 'Custom link');
        } elseif (function_exists('icl_t')) {
            // if WPML and Polylang compatibility
            $this->RdCookieNotice->options['general']['message_text'] = icl_t('Rd Cookie Notice', 'Message in the notice', $this->RdCookieNotice->options['general']['message_text']);
            $this->RdCookieNotice->options['general']['accept_text'] = icl_t('Rd Cookie Notice', 'Button text', $this->RdCookieNotice->options['general']['accept_text']);
            $this->RdCookieNotice->options['general']['refuse_text'] = icl_t('Rd Cookie Notice', 'Refuse button text', $this->RdCookieNotice->options['general']['refuse_text']);
            $this->RdCookieNotice->options['general']['revoke_message_text'] = icl_t('Rd Cookie Notice', 'Revoke message text', $this->RdCookieNotice->options['general']['revoke_message_text']);
            $this->RdCookieNotice->options['general']['revoke_text'] = icl_t('Rd Cookie Notice', 'Revoke button text', $this->RdCookieNotice->options['general']['revoke_text']);
            $this->RdCookieNotice->options['general']['see_more_opt']['text'] = icl_t('Rd Cookie Notice', 'Privacy policy text', $this->RdCookieNotice->options['general']['see_more_opt']['text']);
            $this->RdCookieNotice->options['general']['see_more_opt']['link'] = icl_t('Rd Cookie Notice', 'Custom link', $this->RdCookieNotice->options['general']['see_more_opt']['link']);
        }

        if (function_exists('icl_object_id')) {
            $this->RdCookieNotice->options['general']['see_more_opt']['id'] = icl_object_id($this->RdCookieNotice->options['general']['see_more_opt']['id'], 'page', true);
        }
    }// setOptionsDisplayCookies


    /**
     * Update WPML strings.
     * 
     * @param array $input
     */
    public function updateWPMLStrings(array $input)
    {
        if (defined('ICL_SITEPRESS_VERSION') && version_compare(ICL_SITEPRESS_VERSION, '3.2', '>=')) {
            do_action('wpml_register_single_string', 'Rd Cookie Notice', 'Message in the notice', $input['message_text']);
            do_action('wpml_register_single_string', 'Rd Cookie Notice', 'Button text', $input['accept_text']);
            do_action('wpml_register_single_string', 'Rd Cookie Notice', 'Refuse button text', $input['refuse_text']);
            do_action('wpml_register_single_string', 'Rd Cookie Notice', 'Revoke message text', $input['revoke_message_text']);
            do_action('wpml_register_single_string', 'Rd Cookie Notice', 'Revoke button text', $input['revoke_text']);
            do_action('wpml_register_single_string', 'Rd Cookie Notice', 'Privacy policy text', $input['see_more_opt']['text']);

            if ($input['see_more_opt']['link_type'] === 'custom') {
                do_action('wpml_register_single_string', 'Rd Cookie Notice', 'Custom link', $input['see_more_opt']['link']);
            }
        }
    }// updateWPMLStrings


}
