<?php
/**
 * @license http://opensource.org/licenses/MIT MIT
 * @package rd-cookie-notice
 */


namespace RdCookieNotice;


/**
 * Client scripts class.
 * 
 * @since 0.2.0
 */
class ClientScripts
{


    /**
     * @var \RdCookieNotice\RdCookieNotice
     */
    protected $RdCookieNotice;


    /**
     * Class constructor.
     * 
     * @param \RdCookieNotice\RdCookieNotice $RdCookieNotice
     */
    public function __construct(\RdCookieNotice\RdCookieNotice $RdCookieNotice)
    {
        $this->RdCookieNotice = $RdCookieNotice;
    }// __construct


    /**
     * Load scripts and styles - admin.
     * 
     * @param string $hook_suffix
     * @return void
     */
    public function adminEnqueueScripts(string $hook_suffix)
    {
        if ('settings_page_rd-cookie-notice-settings' !== $hook_suffix) {
            return;
        }

        wp_enqueue_script(
            'cookie-notice-admin', 
            plugins_url('js/admin.js', RDCN_PLUGINFILE), 
            ['jquery', 'wp-color-picker'], 
            RDCN_VERSION,
            true
        );

        wp_localize_script(
            'cookie-notice-admin', 
            'cnArgs', 
            [
                'resetToDefaults' => __('Are you sure you want to reset these settings to defaults?', 'rd-cookie-notice'),
            ]
        );

        wp_enqueue_style('wp-color-picker');
        wp_enqueue_style('cookie-notice-admin', plugins_url('css/admin.css', RDCN_PLUGINFILE));
    }// adminEnqueueScripts


    /**
     * Load scripts and styles for front end.
     */
    public function frontEnqueueScripts()
    {
        wp_enqueue_script(
            'cookie-notice-front', 
            plugins_url('js/front.js', RDCN_PLUGINFILE), 
            [], 
            RDCN_VERSION, 
            isset($this->RdCookieNotice->options['general']['script_placement']) && 'footer' === $this->RdCookieNotice->options['general']['script_placement']
        );

        wp_localize_script(
            'cookie-notice-front',
            'cnArgs',
            [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('cn_save_cases'),
                'hideEffect' => $this->RdCookieNotice->options['general']['hide_effect'],
                'position' => $this->RdCookieNotice->options['general']['position'],
                'onScroll' => (int) $this->RdCookieNotice->options['general']['on_scroll'],
                'onScrollOffset' => (int) $this->RdCookieNotice->options['general']['on_scroll_offset'],
                'onClick' => (int) $this->RdCookieNotice->options['general']['on_click'],
                'cookieName' => 'cookie_notice_accepted',
                'cookieTime' => $this->RdCookieNotice->times[$this->RdCookieNotice->options['general']['time']][1],
                'cookieTimeRejected' => $this->RdCookieNotice->times[$this->RdCookieNotice->options['general']['time_rejected']][1],
                'cookiePath' => (defined('COOKIEPATH') ? (string) COOKIEPATH : ''),
                'cookieDomain' => (defined('COOKIE_DOMAIN') ? (string) COOKIE_DOMAIN : ''),
                'redirection' => (int) $this->RdCookieNotice->options['general']['redirection'],
                'cache' => (int) (defined('WP_CACHE') && WP_CACHE),
                'refuse' => (int) $this->RdCookieNotice->options['general']['refuse_opt'],
                'revokeCookies' => (int) $this->RdCookieNotice->options['general']['revoke_cookies'],
                'revokeCookiesOpt' => $this->RdCookieNotice->options['general']['revoke_cookies_opt'],
                'secure' => (int) is_ssl(),
            ]
        );

        wp_enqueue_style('cookie-notice-front', plugins_url('css/front.css', RDCN_PLUGINFILE), [], RDCN_VERSION);
    }// frontEnqueueScripts


}
