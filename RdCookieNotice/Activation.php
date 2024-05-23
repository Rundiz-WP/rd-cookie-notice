<?php
/**
 * @license http://opensource.org/licenses/MIT MIT
 * @package rd-cookie-notice
 */


namespace RdCookieNotice;


/**
 * Rd Cookie notice activation.
 * 
 * @since 0.2.0
 */
class Activation
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
     * Activate the plugin.
     */
    public function activation()
    {
        add_option('cookie_notice_options', $this->RdCookieNotice->defaults['general'], '', 'no');
        add_option('cookie_notice_version', RDCN_VERSION, '', 'no');
    }// activation


    /**
     * Deactivate the plugin.
     */
    public function deactivation()
    {
        if (true === $this->RdCookieNotice->options['general']['deactivation_delete']) {
            delete_option('cookie_notice_options');
            delete_option('cookie_notice_version');
        }

        // remove WP Super Cache cookie
        $this->RdCookieNotice->WPSC->deleteCookie();
    }// deactivation


    /**
     * Add links to settings page.
     * 
     * @link https://developer.wordpress.org/reference/hooks/plugin_action_links/ Reference.
     * @param array $links An array of plugin action links. By default this can include 'activate', 'deactivate', and 'delete'. With Multisite active this can also include 'network_active' and 'network_only' items.
     * @param string $file Path to the plugin file relative to the plugins directory.
     * @return array
     */
    public function pluginActionLinks($links, string $file)
    {
        if (!current_user_can(apply_filters('cn_manage_cookie_notice_cap', 'manage_options'))) {
            return $links;
        }

        if (plugin_basename(RDCN_PLUGINFILE) === $file) {
            if (is_array($links)) {
                array_unshift($links, sprintf('<a href="%s">%s</a>', admin_url('options-general.php?page=rd-cookie-notice-settings'), __('Settings', 'rd-cookie-notice')));
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

        if (version_compare($current_db_version, RDCN_VERSION, '<')) {
            // if current version is older
            // updates plugin version
            update_option('cookie_notice_version', RDCN_VERSION, false);
        }

        unset($current_db_version);
    }// updateDBVersion


}
