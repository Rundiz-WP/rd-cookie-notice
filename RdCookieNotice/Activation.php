<?php
/**
 * @license http://opensource.org/licenses/MIT MIT
 * @package rd-cookie-notice
 */


namespace RdCookieNotice;


/**
 * Rd Cookie notice activation.
 * 
 * @since 0.1.1
 */
class Activation
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
     * Activate the plugin.
     */
   public function activation() {
        add_option('cookie_notice_options', $this->RdCookieNotice->defaults['general'], '', 'no');
        add_option('cookie_notice_version', $this->RdCookieNotice->defaults['version'], '', 'no');
    }// activation


    /**
     * Deactivate the plugin.
     */
   public function deactivation()
    {
        if ($this->RdCookieNotice->options['general']['deactivation_delete'] === true) {
            delete_option('cookie_notice_options');
            delete_option('cookie_notice_version');
        }

        // remove WP Super Cache cookie
        $this->RdCookieNotice->WPSC->deleteCookie();
    }// deactivation


    /**
     * Add links to settings page.
     * 
     * @param array $links
     * @param string $file
     * @return array
     */
    public function pluginActionLinks($links, $file)
    {
        if (!current_user_can(apply_filters('cn_manage_cookie_notice_cap', 'manage_options'))) {
            return $links;
        }

        if ($file == plugin_basename(RDCN_PLUGINFILE)) {
            if (is_array($links)) {
                array_unshift($links, sprintf('<a href="%s">%s</a>', admin_url('options-general.php?page=cookie-notice'), __('Settings', 'cookie-notice')));
            }
        }

        return $links;
    }// pluginActionLinks


    /**
     * Update DB version if older.
     * 
     * This is not in update plugin process because this is not listed in WordPress.org plugin directory. It can be manually install or update.
     * So, place it here to let this plugin update if necessary.
     * 
     * @return void
     */
    public function updateDBVersion()
    {
        if (!current_user_can('install_plugins')) {
            return;
        }

        // get current database version
        $current_db_version = get_option('cookie_notice_version', RDCNDB_VERSION);

        // new version?
        if (version_compare($current_db_version, $this->RdCookieNotice->defaults['version'], '<')) {
            // updates plugin version
            update_option('cookie_notice_version', $this->RdCookieNotice->defaults['version'], false);
        }
    }// updateDBVersion


}
