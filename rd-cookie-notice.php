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

if (!defined('RDCN_PLUGINFILE')) {
    define('RDCN_PLUGINFILE', __FILE__);
}

if (!defined('RDCN_VERSION')) {
    $pluginData = (function_exists('get_file_data') ? get_file_data(__FILE__, ['Version' => 'Version']) : null);
    $pluginVersion = (isset($pluginData['Version']) ? $pluginData['Version'] : date('Ym'));
    unset($pluginData);
    define('RDCN_VERSION', $pluginVersion);
    unset($pluginVersion);
}


/**
 * Rd Cookie Notice class.
 * 
 * @property-read \RdCookieNotice\HTML $HTML HTML related class.
 * @property-read \RdCookieNotice\SettingsProcess $SettingsProcess Settings process class.
 * @property-read \RdCookieNotice\WPSC $WPSC WP Super Cache class.
 */
class RdCookieNotice
{


    /**
     * @var $defaults
     */
    private $defaults = [
        'general' => [
            'position' => 'bottom',
            'message_text' => '',
            'css_style' => 'bootstrap',
            'css_class' => '',
            'accept_text' => '',
            'refuse_text' => '',
            'refuse_opt' => false,
            'refuse_code' => '',
            'refuse_code_head' => '',
            'revoke_cookies' => false,
            'revoke_cookies_opt' => 'automatic',
            'revoke_message_text' => '',
            'revoke_text' => '',
            'redirection' => false,
            'see_more' => false,
            'link_target' => '_blank',
            'link_position' => 'banner',
            'time' => 'month',
            'time_rejected' => 'month',
            'hide_effect' => 'fade',
            'on_scroll' => false,
            'on_scroll_offset' => 100,
            'on_click' => false,
            'colors' => [
                'text' => '#ffffff',
                'bar' => '#000000',
                'bar_opacity' => 100
            ],
            'see_more_opt' => [
                'text' => '',
                'link_type' => 'page',
                'id' => 0,
                'link' => '',
                'sync' => false
            ],
            'script_placement' => 'header',
            'translate' => true,
            'deactivation_delete' => false,
        ],
        'version' => RDCN_VERSION,
    ];
    private $positions = [];
    private $revoke_opts = [];
    private $styles = [];
    private $links = [];
    private $link_targets = [];
    private $link_positions = [];
    private $colors = [];
    private $options = [];
    private $effects = [];
    private $times = [];
    private $script_placements = [];
    private static $_instance;

    private $HTML;
    private $SettingsProcess;
    private $WPSC;

    public function __clone() {}
    public function __wakeup() {}


    /**
     * Magic get.
     * 
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }
    }// __get


    /**
     * Magic set.
     * 
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        if ($name === 'options') {
            $this->{$name} = $value;
        }
    }// __set


    /**
     * Main plugin instance.
     * 
     * @return object
     */
    public static function instance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();

            add_action('plugins_loaded', [self::$_instance, 'loadTextdomain']);

            self::$_instance->includes();
        }

        return self::$_instance;
    }// instance


    /**
     * Constructor.
     */
    public function __construct() {
        $this->HTML = new \RdCookieNotice\HTML($this);
        $this->SettingsProcess = new \RdCookieNotice\SettingsProcess($this);
        $this->WPSC = new \RdCookieNotice\WPSC();

        if (is_admin()) {
            $RDCNActivation = new \RdCookieNotice\Activation($this);
            register_activation_hook(__FILE__, [$RDCNActivation, 'activation']);
            register_deactivation_hook(__FILE__, [$RDCNActivation, 'deactivation']);
            add_action('admin_init', [$RDCNActivation, 'updateDBVersion']);
            add_filter('plugin_action_links', [$RDCNActivation, 'pluginActionLinks'], 10, 2);
            unset($RDCNActivation);
        }

        // get options
        $options = get_option('cookie_notice_options', $this->defaults['general']);

        // check legacy parameters
        $options = $this->SettingsProcess->checkLegacyParams($options, ['refuse_opt', 'on_scroll', 'on_click', 'deactivation_delete', 'see_more']);

        // merge old options with new ones
        $ArrayHelper = new \RdCookieNotice\Libraries\ArrayHelper();
        $this->options = [
            'general' => $ArrayHelper->multiArrayMerge($this->defaults['general'], $options)
        ];
        unset($ArrayHelper);

        if (!isset($this->options['general']['see_more_opt']['sync'])) {
            $this->options['general']['see_more_opt']['sync'] = $this->defaults['general']['see_more_opt']['sync'];
        }

        // enqueue scripts (and styles) for front and admin.
        $ClientScripts = new \RdCookieNotice\ClientScripts($this);
        add_action('admin_enqueue_scripts', [$ClientScripts, 'adminEnqueueScripts']);
        add_action('wp_enqueue_scripts', [$ClientScripts, 'frontEnqueueScripts']);
        unset($ClientScripts);

        // add shortcodes.
        $Shortcode = new \RdCookieNotice\Shortcode($this);
        $Shortcode->addShortcodes();
        unset($Shortcode);

        add_action('init', [$this->WPSC, 'addCookie']);

        $SettingsPage = new \RdCookieNotice\SettingsPage($this);
        add_action('admin_init', [$SettingsPage, 'registerSettings']);
        add_action('admin_menu', [$SettingsPage, 'addAdminMenu']);
        unset($SettingsPage);

        add_action('after_setup_theme', [$this, 'loadDefaults']);

        add_action('wp_head', [$this->HTML, 'printHeaderScripts']);
        add_action('wp_footer', [$this->HTML, 'displayCookieNotice'], 1000);
        add_action('wp_print_footer_scripts', [$this->HTML, 'printFooterScripts']);
        add_filter('body_class', [$this->HTML, 'changeBodyClasses']);
    }// __construct


    /**
     * Include required files
     *
     * @return void
     */
    private function includes()
    {
        include_once(plugin_dir_path(RDCN_PLUGINFILE) . 'includes/functions.php');
    }// includes


    /**
     * Load plugin defaults
     */
    public function loadDefaults()
    {
        $this->positions = [
            'top' => __('Top', 'rd-cookie-notice'),
            'bottom' => __('Bottom', 'rd-cookie-notice')
        ];

        $this->styles = [
            'none' => __('None', 'rd-cookie-notice'),
            'wp-default' => __('Light', 'rd-cookie-notice'),
            'bootstrap' => __('Dark', 'rd-cookie-notice')
        ];

        $this->revoke_opts = [
            'automatic' => __('Automatic', 'rd-cookie-notice'),
            'manual' => __('Manual', 'rd-cookie-notice')
        ];

        $this->links = [
            'page' => __('Page link', 'rd-cookie-notice'),
            'custom' => __('Custom link', 'rd-cookie-notice')
        ];

        $this->link_targets = [
            '_blank',
            '_self'
        ];

        $this->link_positions = [
            'banner' => __('Banner', 'rd-cookie-notice'),
            'message' => __('Message', 'rd-cookie-notice')
        ];

        $this->colors = [
            'text' => __('Text color', 'rd-cookie-notice'),
            'bar' => __('Bar color', 'rd-cookie-notice'),
        ];

        $this->times = apply_filters(
            'cn_cookie_expiry',
            [
                'day' => [__('1 day', 'rd-cookie-notice'), 86400],
                'week' => [__('1 week', 'rd-cookie-notice'), 604800],
                'month' => [__('1 month', 'rd-cookie-notice'), 2592000],
                '3months' => [__('3 months', 'rd-cookie-notice'), 7862400],
                '6months' => [__('6 months', 'rd-cookie-notice'), 15811200],
                'year' => [__('1 year', 'rd-cookie-notice'), 31536000],
                'infinity' => [__('infinity', 'rd-cookie-notice'), 2147483647]
            ]
        );

        $this->effects = [
            'none' => __('None', 'rd-cookie-notice'),
            'fade' => __('Fade', 'rd-cookie-notice'),
            'slide' => __('Slide', 'rd-cookie-notice')
        ];

        $this->script_placements = [
            'header' => __('Header', 'rd-cookie-notice'),
            'footer' => __('Footer', 'rd-cookie-notice'),
        ];

        // set default text strings
        $this->defaults['general']['message_text'] = __('We use cookies to ensure that we give you the best experience on our website. If you continue to use this site we will assume that you are happy with it.', 'rd-cookie-notice');
        $this->defaults['general']['accept_text'] = __('Ok', 'rd-cookie-notice');
        $this->defaults['general']['refuse_text'] = __('No', 'rd-cookie-notice');
        $this->defaults['general']['revoke_message_text'] = __('You can revoke your consent any time using the Revoke consent button.', 'rd-cookie-notice');
        $this->defaults['general']['revoke_text'] = __('Revoke consent', 'rd-cookie-notice');
        $this->defaults['general']['see_more_opt']['text'] = __('Privacy policy', 'rd-cookie-notice');

        // set translation strings on plugin activation
        if ($this->options['general']['translate'] === true) {
            $this->options['general']['translate'] = false;
            $this->options['general']['message_text'] = $this->defaults['general']['message_text'];
            $this->options['general']['accept_text'] = $this->defaults['general']['accept_text'];
            $this->options['general']['refuse_text'] = $this->defaults['general']['refuse_text'];
            $this->options['general']['revoke_message_text'] = $this->defaults['general']['revoke_message_text'];
            $this->options['general']['revoke_text'] = $this->defaults['general']['revoke_text'];
            $this->options['general']['see_more_opt']['text'] = $this->defaults['general']['see_more_opt']['text'];

            update_option('cookie_notice_options', $this->options['general']);
        }

        $WPML = new \RdCookieNotice\WPML($this);
        $WPML->registerStrings();
        unset($WPML);
    }// loadDefaults


    /**
     * Load text domain.
     */
    public function loadTextdomain()
    {
        load_plugin_textdomain('rd-cookie-notice', false, dirname(plugin_basename(RDCN_PLUGINFILE)) . '/languages/');
    }// loadTextdomain


    /**
     * Check if cookies are accepted.
     * 
     * @return bool
     */
    public static function cookies_accepted() {
        return apply_filters(
            'cn_is_cookie_accepted', 
            (isset($_COOKIE['cookie_notice_accepted']) && strtolower($_COOKIE['cookie_notice_accepted']) === 'true')
        );
    }// cookies_accepted


    /**
     * Check if cookies are set.
     *
     * @return boolean Whether cookies are set
     */
    public function cookies_set() {
        return apply_filters('cn_is_cookie_set', isset($_COOKIE['cookie_notice_accepted']));
    }// cookies_set


    /**
     * Indicate if current page is the Cookie Policy page
     *
     * @return bool
     */
    public function is_cookie_policy_page()
    {
        if (!is_page()) {
            return false;
        }

        $see_more = $this->options['general']['see_more_opt'];

        if ($see_more['link_type'] !== 'page') {
            return false;
        }

        $cp_id = $see_more['id'];
        $cp_slug = get_post_field('post_name', $cp_id);

        $post = get_post();

        return ($post->post_name === $cp_slug || $post->ID === $cp_id);
    }// is_cookie_policy_page


}// RdCookieNotice


/**
 * Initialize Rundiz Cookie Notice.
 */
function runRdCookieNotice() {
    static $instance;

    // first call to instance() initializes the plugin
    if (!$instance instanceof RdCookieNotice) {
        $instance = RdCookieNotice::instance();
    }

    return $instance;
}

$rdCookieNotice = runRdCookieNotice();
