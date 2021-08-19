<?php
/**
 * Plugin Name: Rundiz Cookie Notice
 * Description: Rd Cookie Notice allows you to elegantly inform users that your site uses cookies and helps you comply with the EU GDPR cookie law and CCPA regulations.
 * Version: 0.1.0
 * Author: Vee W.
 * Author URI: https://rundiz.com/
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 * Text Domain: rd-cookie-notice
 * Domain Path: /languages
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


/**
 * Rd Cookie Notice class.
 * 
 * @property-read \RdCookieNotice\HTML $HTML HTML related class.
 * @property-read \RdCookieNotice\WPSc $WPSC WP Super Cache class.
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
                'text' => '#fff',
                'bar' => '#000',
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
        'version' => '0.1.1'
    ];
    private $positions = [];
    private $styles = [];
    private $choices = [];
    private $links = [];
    private $link_targets = [];
    private $link_positions = [];
    private $colors = [];
    private $options = [];
    private $effects = [];
    private $times = [];
    private $notices = [];
    private $script_placements = [];
    private static $_instance;

    private $HTML;
    private $WPSC;

    public function __clone() {}
    public function __wakeup() {}

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }
    }// __get

    /**
     * Main plugin instance.
     * 
     * @return object
     */
    public static function instance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();

            add_action('plugins_loaded', array(self::$_instance, 'load_textdomain'));

            self::$_instance->includes();
        }

        return self::$_instance;
    }// instance


    /**
     * Constructor.
     */
    public function __construct() {
        $this->HTML = new RdCookieNotice\HTML($this);
        $this->WPSC = new RdCookieNotice\WPSc();

        if (is_admin()) {
            $RDCNActivation = new \RdCookieNotice\Activation($this);
            register_activation_hook(__FILE__, [$RDCNActivation, 'activation']);
            register_deactivation_hook(__FILE__, [$RDCNActivation, 'deactivation']);
            add_action('admin_init', array($RDCNActivation, 'updateDBVersion'));
            add_filter('plugin_action_links', array($RDCNActivation, 'pluginActionLinks'), 10, 2);
            unset($RDCNActivation);
        }

        // get options
        $options = get_option('cookie_notice_options', $this->defaults['general']);

        // check legacy parameters
        $options = $this->check_legacy_params( $options, array( 'refuse_opt', 'on_scroll', 'on_click', 'deactivation_delete', 'see_more' ) );

        // merge old options with new ones
        $this->options = array(
            'general' => $this->multi_array_merge( $this->defaults['general'], $options )
        );

        if (!isset($this->options['general']['see_more_opt']['sync'])) {
            $this->options['general']['see_more_opt']['sync'] = $this->defaults['general']['see_more_opt']['sync'];
        }

        // enqueue scripts (and styles) for front and admin.
        $ClientScripts = new RdCookieNotice\ClientScripts($this);
        add_action('admin_enqueue_scripts', array($ClientScripts, 'adminEnqueueScripts'));
        add_action('wp_enqueue_scripts', array($ClientScripts, 'frontEnqueueScripts'));
        unset($ClientScripts);

        // add shortcodes.
        $Shortcode = new RdCookieNotice\Shortcode($this);
        $Shortcode->addShortcodes();
        unset($Shortcode);

        add_action('init', [$this->WPSC, 'addCookie']);

        // actions
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_menu', array($this, 'admin_menu_options'));
        add_action('after_setup_theme', array($this, 'load_defaults'));

        add_action('wp_head', array($this->HTML, 'printHeaderScripts'));
        add_action('wp_footer', array($this->HTML, 'displayCookieNotice'), 1000);
        add_action('wp_print_footer_scripts', array($this->HTML, 'printFooterScripts'));
        add_filter('body_class', array($this->HTML, 'changeBodyClasses'));
    }// __construct


    /**
     * Include required files
     *
     * @return void
     */
    private function includes()
    {
        include_once(plugin_dir_path(__FILE__) . 'includes/functions.php');
    }// includes


    /**
     * Load plugin defaults
     */
    public function load_defaults()
    {
        $this->positions = array(
            'top' => __('Top', 'cookie-notice'),
            'bottom' => __('Bottom', 'cookie-notice')
        );

        $this->styles = array(
            'none' => __('None', 'cookie-notice'),
            'wp-default' => __('Light', 'cookie-notice'),
            'bootstrap' => __('Dark', 'cookie-notice')
        );

        $this->revoke_opts = array(
            'automatic' => __('Automatic', 'cookie-notice'),
            'manual' => __('Manual', 'cookie-notice')
        );

        $this->links = array(
            'page' => __('Page link', 'cookie-notice'),
            'custom' => __('Custom link', 'cookie-notice')
        );

        $this->link_targets = array(
            '_blank',
            '_self'
        );

        $this->link_positions = array(
            'banner' => __('Banner', 'cookie-notice'),
            'message' => __('Message', 'cookie-notice')
        );

        $this->colors = array(
            'text' => __('Text color', 'cookie-notice'),
            'bar' => __('Bar color', 'cookie-notice'),
        );

        $this->times = apply_filters(
                'cn_cookie_expiry',
                array(
                    /* 'hour' => array( __( 'An hour', 'cookie-notice' ), 3600 ), */
                    'day' => array(__('1 day', 'cookie-notice'), 86400),
                    'week' => array(__('1 week', 'cookie-notice'), 604800),
                    'month' => array(__('1 month', 'cookie-notice'), 2592000),
                    '3months' => array(__('3 months', 'cookie-notice'), 7862400),
                    '6months' => array(__('6 months', 'cookie-notice'), 15811200),
                    'year' => array(__('1 year', 'cookie-notice'), 31536000),
                    'infinity' => array(__('infinity', 'cookie-notice'), 2147483647)
                )
        );

        $this->effects = array(
            'none' => __('None', 'cookie-notice'),
            'fade' => __('Fade', 'cookie-notice'),
            'slide' => __('Slide', 'cookie-notice')
        );

        $this->script_placements = array(
            'header' => __('Header', 'cookie-notice'),
            'footer' => __('Footer', 'cookie-notice'),
        );

        // set default text strings
        $this->defaults['general']['message_text'] = __('We use cookies to ensure that we give you the best experience on our website. If you continue to use this site we will assume that you are happy with it.', 'cookie-notice');
        $this->defaults['general']['accept_text'] = __('Ok', 'cookie-notice');
        $this->defaults['general']['refuse_text'] = __('No', 'cookie-notice');
        $this->defaults['general']['revoke_message_text'] = __('You can revoke your consent any time using the Revoke consent button.', 'cookie-notice');
        $this->defaults['general']['revoke_text'] = __('Revoke consent', 'cookie-notice');
        $this->defaults['general']['see_more_opt']['text'] = __('Privacy policy', 'cookie-notice');

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

        // WPML >= 3.2
        if (defined('ICL_SITEPRESS_VERSION') && version_compare(ICL_SITEPRESS_VERSION, '3.2', '>=')) {
            $this->register_wpml_strings();
            // WPML and Polylang compatibility
        } elseif (function_exists('icl_register_string')) {
            icl_register_string('Rd Cookie Notice', 'Message in the notice', $this->options['general']['message_text']);
            icl_register_string('Rd Cookie Notice', 'Button text', $this->options['general']['accept_text']);
            icl_register_string('Rd Cookie Notice', 'Refuse button text', $this->options['general']['refuse_text']);
            icl_register_string('Rd Cookie Notice', 'Revoke message text', $this->options['general']['revoke_message_text']);
            icl_register_string('Rd Cookie Notice', 'Revoke button text', $this->options['general']['revoke_text']);
            icl_register_string('Rd Cookie Notice', 'Privacy policy text', $this->options['general']['see_more_opt']['text']);
            icl_register_string('Rd Cookie Notice', 'Custom link', $this->options['general']['see_more_opt']['link']);
        }
    }// load_defaults


	/**
	 * Register WPML (>= 3.2) strings if needed.
	 *
	 * @return	void
	 */
	private function register_wpml_strings() {
		global $wpdb;

		// prepare strings
		$strings = array(
			'Message in the notice'	=> $this->options['general']['message_text'],
			'Button text'			=> $this->options['general']['accept_text'],
			'Refuse button text'	=> $this->options['general']['refuse_text'],
			'Revoke message text'	=> $this->options['general']['revoke_message_text'],
			'Revoke button text'	=> $this->options['general']['revoke_text'],
			'Privacy policy text'	=> $this->options['general']['see_more_opt']['text'],
			'Custom link'			=> $this->options['general']['see_more_opt']['link']
		);
		

		// get query results
		$results = $wpdb->get_col( $wpdb->prepare( "SELECT name FROM " . $wpdb->prefix . "icl_strings WHERE context = %s", 'Rd Cookie Notice' ) );

		// check results
		foreach( $strings as $string => $value ) {
			// string does not exist?
			if ( ! in_array( $string, $results, true ) ) {
				// register string
				do_action( 'wpml_register_single_string', 'Rd Cookie Notice', $string, $value );
			}
		}
	}

	/**
	 * Load textdomain.
	 */
	public function load_textdomain() {
		# In the plugin folder: wp i18n make-pot . languages/rd-cookie-notice.pot
		load_plugin_textdomain( 'rd-cookie-notice', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Add submenu.
	 */
	public function admin_menu_options() {
		add_options_page( __( 'Rundiz Cookie Notice', 'cookie-notice' ), __( 'Rundiz Cookie Notice', 'cookie-notice' ), apply_filters( 'cn_manage_cookie_notice_cap', 'manage_options' ), 'cookie-notice', array( $this, 'options_page' ) );
	}

	/**
	 * Options page output.
	 * 
	 * @return mixed
	 */
	public function options_page() {
		echo '
		<div class="wrap">
			<h2>' . __( 'Rundiz Cookie Notice', 'cookie-notice' ) . '</h2>
			<div class="cookie-notice-settings">
				<form action="options.php" method="post">';

		settings_fields( 'cookie_notice_options' );
		do_settings_sections( 'cookie_notice_options' );
		
		echo '
				<p class="submit">';
		submit_button( '', 'primary', 'save_cookie_notice_options', false );
		echo ' ';
		submit_button( __( 'Reset to defaults', 'cookie-notice' ), 'secondary', 'reset_cookie_notice_options', false );
		echo '
				</p>
				</form>
			</div>
			<div class="clear"></div>
		</div>';
	}

	/**
	 * Regiseter plugin settings.
	 */
	public function register_settings() {
		register_setting( 'cookie_notice_options', 'cookie_notice_options', array( $this, 'validate_options' ) );

		// configuration
		add_settings_section( 'cookie_notice_configuration', __( 'Configuration', 'cookie-notice' ), array( $this, 'cn_section_configuration' ), 'cookie_notice_options' );
		add_settings_field( 'cn_message_text', __( 'Message', 'cookie-notice' ), array( $this, 'cn_message_text' ), 'cookie_notice_options', 'cookie_notice_configuration' );
		add_settings_field( 'cn_accept_text', __( 'Button text', 'cookie-notice' ), array( $this, 'cn_accept_text' ), 'cookie_notice_options', 'cookie_notice_configuration' );
		add_settings_field( 'cn_see_more', __( 'Privacy policy', 'cookie-notice' ), array( $this, 'cn_see_more' ), 'cookie_notice_options', 'cookie_notice_configuration' );
		add_settings_field( 'cn_refuse_opt', __( 'Refuse consent', 'cookie-notice' ), array( $this, 'cn_refuse_opt' ), 'cookie_notice_options', 'cookie_notice_configuration' );
		add_settings_field( 'cn_revoke_opt', __( 'Revoke consent', 'cookie-notice' ), array( $this, 'cn_revoke_opt' ), 'cookie_notice_options', 'cookie_notice_configuration' );
		add_settings_field( 'cn_refuse_code', __( 'Script blocking', 'cookie-notice' ), array( $this, 'cn_refuse_code' ), 'cookie_notice_options', 'cookie_notice_configuration' );
		add_settings_field( 'cn_redirection', __( 'Reloading', 'cookie-notice' ), array( $this, 'cn_redirection' ), 'cookie_notice_options', 'cookie_notice_configuration' );
		add_settings_field( 'cn_on_scroll', __( 'On scroll', 'cookie-notice' ), array( $this, 'cn_on_scroll' ), 'cookie_notice_options', 'cookie_notice_configuration' );
		add_settings_field( 'cn_on_click', __( 'On click', 'cookie-notice' ), array( $this, 'cn_on_click' ), 'cookie_notice_options', 'cookie_notice_configuration' );
		add_settings_field( 'cn_time', __( 'Accepted expiry', 'cookie-notice' ), array( $this, 'cn_time' ), 'cookie_notice_options', 'cookie_notice_configuration' );
		add_settings_field( 'cn_time_rejected', __( 'Rejected expiry', 'cookie-notice' ), array( $this, 'cn_time_rejected' ), 'cookie_notice_options', 'cookie_notice_configuration' );
		add_settings_field( 'cn_script_placement', __( 'Script placement', 'cookie-notice' ), array( $this, 'cn_script_placement' ), 'cookie_notice_options', 'cookie_notice_configuration' );
		add_settings_field( 'cn_deactivation_delete', __( 'Deactivation', 'cookie-notice' ), array( $this, 'cn_deactivation_delete' ), 'cookie_notice_options', 'cookie_notice_configuration' );

	
		// design
		add_settings_section( 'cookie_notice_design', __( 'Design', 'cookie-notice' ), array( $this, 'cn_section_design' ), 'cookie_notice_options' );
		add_settings_field( 'cn_position', __( 'Position', 'cookie-notice' ), array( $this, 'cn_position' ), 'cookie_notice_options', 'cookie_notice_design' );
		add_settings_field( 'cn_hide_effect', __( 'Animation', 'cookie-notice' ), array( $this, 'cn_hide_effect' ), 'cookie_notice_options', 'cookie_notice_design' );
		add_settings_field( 'cn_css_style', __( 'Button style', 'cookie-notice' ), array( $this, 'cn_css_style' ), 'cookie_notice_options', 'cookie_notice_design' );
		add_settings_field( 'cn_css_class', __( 'Button class', 'cookie-notice' ), array( $this, 'cn_css_class' ), 'cookie_notice_options', 'cookie_notice_design' );
		add_settings_field( 'cn_colors', __( 'Colors', 'cookie-notice' ), array( $this, 'cn_colors' ), 'cookie_notice_options', 'cookie_notice_design' );
	}

	/**
	 * Section callback: fix for WP < 3.3
	 */
	public function cn_section_configuration() {}
	public function cn_section_design() {}
	
	

	/**
	 * Cookie message option.
	 */
	public function cn_message_text() {
		echo '
		<fieldset>
			<div id="cn_message_text">
				<textarea name="cookie_notice_options[message_text]" class="large-text" cols="50" rows="5">' . esc_textarea( $this->options['general']['message_text'] ) . '</textarea>
				<p class="description">' . __( 'Enter the cookie notice message.', 'cookie-notice' ) . '</p>
			</div>
		</fieldset>';
	}

	/**
	 * Accept cookie label option.
	 */
	public function cn_accept_text() {
		echo '
		<fieldset>
			<div id="cn_accept_text">
				<input type="text" class="regular-text" name="cookie_notice_options[accept_text]" value="' . esc_attr( $this->options['general']['accept_text'] ) . '" />
			<p class="description">' . __( 'The text of the option to accept the notice and make it disappear.', 'cookie-notice' ) . '</p>
			</div>
		</fieldset>';
	}

	/**
	 * Enable/Disable third party non functional cookies option.
	 */
	public function cn_refuse_opt() {
		echo '
		<fieldset>
			<label><input id="cn_refuse_opt" type="checkbox" name="cookie_notice_options[refuse_opt]" value="1" ' . checked( true, $this->options['general']['refuse_opt'], false ) . ' />' . __( 'Enable to give to the user the possibility to refuse third party non functional cookies.', 'cookie-notice' ) . '</label>
			<div id="cn_refuse_opt_container"' . ( $this->options['general']['refuse_opt'] === false ? ' style="display: none;"' : '' ) . '>
				<div id="cn_refuse_text">
					<input type="text" class="regular-text" name="cookie_notice_options[refuse_text]" value="' . esc_attr( $this->options['general']['refuse_text'] ) . '" />
					<p class="description">' . __( 'The text of the button to refuse the consent.', 'cookie-notice' ) . '</p>
				</div>
			</div>
		</fieldset>';
	}

	/**
	 * Non functional cookies code.
	 */
	public function cn_refuse_code() {
		$allowed_html = $this->HTML->getAllowedHTML();
		$active = ! empty( $this->options['general']['refuse_code'] ) && empty( $this->options['general']['refuse_code_head'] ) ? 'body' : 'head';

		echo '
		<fieldset>
			<div id="cn_refuse_code">
				<div id="cn_refuse_code_fields">
					<h2 class="nav-tab-wrapper">
						<a id="refuse_head-tab" class="nav-tab' . ( $active === 'head' ? ' nav-tab-active' : '' ) . '" href="#refuse_head">' . __( 'Head', 'cookie-notice' ) . '</a>
						<a id="refuse_body-tab" class="nav-tab' . ( $active === 'body' ? ' nav-tab-active' : '' ) . '" href="#refuse_body">' . __( 'Body', 'cookie-notice' ) . '</a>
					</h2>
					<div id="refuse_head" class="refuse-code-tab' . ( $active === 'head' ? ' active' : '' ) . '">
						<p class="description">' . __( 'The code to be used in your site header, before the closing head tag.', 'cookie-notice' ) . '</p>
						<textarea name="cookie_notice_options[refuse_code_head]" class="large-text" cols="50" rows="8">' . html_entity_decode( trim( wp_kses( $this->options['general']['refuse_code_head'], $allowed_html ) ) ) . '</textarea>
					</div>
					<div id="refuse_body" class="refuse-code-tab' . ( $active === 'body' ? ' active' : '' ) . '">
						<p class="description">' . __( 'The code to be used in your site footer, before the closing body tag.', 'cookie-notice' ) . '</p>
						<textarea name="cookie_notice_options[refuse_code]" class="large-text" cols="50" rows="8">' . html_entity_decode( trim( wp_kses( $this->options['general']['refuse_code'], $allowed_html ) ) ) . '</textarea>
					</div>
				</div>
				<p class="description">' . __( 'Enter non functional cookies Javascript code here (for e.g. Google Analitycs) to be used after the notice is accepted.', 'cookie-notice' ) . '</br>' . __( 'To get the user consent status use the <code>cn_cookies_accepted()</code> function.', 'cookie-notice' ) . '</p>
			</div>
		</fieldset>';
	}

	/**
	 * Revoke cookies option.
	 */
	public function cn_revoke_opt() {
		echo '
		<fieldset>
			<label><input id="cn_revoke_cookies" type="checkbox" name="cookie_notice_options[revoke_cookies]" value="1" ' . checked( true, $this->options['general']['revoke_cookies'], false ) . ' />' . __( 'Enable to give to the user the possibility to revoke their consent <i>(requires "Refuse consent" option enabled)</i>.', 'cookie-notice' ) . '</label>
			<div id="cn_revoke_opt_container"' . ( $this->options['general']['revoke_cookies'] ? '' : ' style="display: none;"' ) . '>
				<textarea name="cookie_notice_options[revoke_message_text]" class="large-text" cols="50" rows="2">' . esc_textarea( $this->options['general']['revoke_message_text'] ) . '</textarea>
				<p class="description">' . __( 'Enter the revoke message.', 'cookie-notice' ) . '</p>
				<input type="text" class="regular-text" name="cookie_notice_options[revoke_text]" value="' . esc_attr( $this->options['general']['revoke_text'] ) . '" />
				<p class="description">' . __( 'The text of the button to revoke the consent.', 'cookie-notice' ) . '</p>';

		foreach ( $this->revoke_opts as $value => $label ) {
			echo '
				<label><input id="cn_revoke_cookies-' . $value . '" type="radio" name="cookie_notice_options[revoke_cookies_opt]" value="' . $value . '" ' . checked( $value, $this->options['general']['revoke_cookies_opt'], false ) . ' />' . esc_html( $label ) . '</label>';
		}

		echo '
				<p class="description">' . __( 'Select the method for displaying the revoke button - automatic (in the banner) or manual using <code>[cookies_revoke]</code> shortcode.', 'cookie-notice' ) . '</p>
			</div>
		</fieldset>';
	}

	/**
	 * Redirection on cookie accept.
	 */
	public function cn_redirection() {
		echo '
		<fieldset>
			<label><input id="cn_redirection" type="checkbox" name="cookie_notice_options[redirection]" value="1" ' . checked( true, $this->options['general']['redirection'], false ) . ' />' . __( 'Enable to reload the page after the notice is accepted.', 'cookie-notice' ) . '</label>
		</fieldset>';
	}

	/**
	 * Privacy policy link option.
	 */
	public function cn_see_more() {
		$pages = get_pages(
			array(
				'sort_order'	=> 'ASC',
				'sort_column'	=> 'post_title',
				'hierarchical'	=> 0,
				'child_of'		=> 0,
				'parent'		=> -1,
				'offset'		=> 0,
				'post_type'		=> 'page',
				'post_status'	=> 'publish'
			)
		);

		echo '
		<fieldset>
			<label><input id="cn_see_more" type="checkbox" name="cookie_notice_options[see_more]" value="1" ' . checked( true, $this->options['general']['see_more'], false ) . ' />' . __( 'Enable privacy policy link.', 'cookie-notice' ) . '</label>
			<div id="cn_see_more_opt"' . ($this->options['general']['see_more'] === false ? ' style="display: none;"' : '') . '>
				<input type="text" class="regular-text" name="cookie_notice_options[see_more_opt][text]" value="' . esc_attr( $this->options['general']['see_more_opt']['text'] ) . '" />
				<p class="description">' . __( 'The text of the privacy policy button.', 'cookie-notice' ) . '</p>
				<div id="cn_see_more_opt_custom_link">';

		foreach ( $this->links as $value => $label ) {
			$value = esc_attr( $value );

			echo '
					<label><input id="cn_see_more_link-' . $value . '" type="radio" name="cookie_notice_options[see_more_opt][link_type]" value="' . $value . '" ' . checked( $value, $this->options['general']['see_more_opt']['link_type'], false ) . ' />' . esc_html( $label ) . '</label>';
		}

		echo '
				</div>
				<p class="description">' . __( 'Select where to redirect user for more information.', 'cookie-notice' ) . '</p>
				<div id="cn_see_more_opt_page"' . ($this->options['general']['see_more_opt']['link_type'] === 'custom' ? ' style="display: none;"' : '') . '>
					<select name="cookie_notice_options[see_more_opt][id]">
						<option value="0" ' . selected( 0, $this->options['general']['see_more_opt']['id'], false ) . '>' . __( '-- select page --', 'cookie-notice' ) . '</option>';

		if ( $pages ) {
			foreach ( $pages as $page ) {
				echo '
						<option value="' . $page->ID . '" ' . selected( $page->ID, $this->options['general']['see_more_opt']['id'], false ) . '>' . esc_html( $page->post_title ) . '</option>';
			}
		}

		echo '
					</select>
					<p class="description">' . __( 'Select from one of your site\'s pages.', 'cookie-notice' ) . '</p>';

		global $wp_version;

		if ( version_compare( $wp_version, '4.9.6', '>=' ) ) {
			echo '
						<label><input id="cn_see_more_opt_sync" type="checkbox" name="cookie_notice_options[see_more_opt][sync]" value="1" ' . checked( true, $this->options['general']['see_more_opt']['sync'], false ) . ' />' . __( 'Synchronize with WordPress Privacy Policy page.', 'cookie-notice' ) . '</label>';
		}

		echo '
				</div>
				<div id="cn_see_more_opt_link"' . ($this->options['general']['see_more_opt']['link_type'] === 'page' ? ' style="display: none;"' : '') . '>
					<input type="text" class="regular-text" name="cookie_notice_options[see_more_opt][link]" value="' . esc_attr( $this->options['general']['see_more_opt']['link'] ) . '" />
					<p class="description">' . __( 'Enter the full URL starting with http(s)://', 'cookie-notice' ) . '</p>
				</div>
				<div id="cn_see_more_link_target">';

		foreach ( $this->link_targets as $target ) {
			echo '
					<label><input id="cn_see_more_link_target-' . $target . '" type="radio" name="cookie_notice_options[link_target]" value="' . $target . '" ' . checked( $target, $this->options['general']['link_target'], false ) . ' />' . $target . '</label>';
		}

		echo '
					<p class="description">' . esc_html__( 'Select the privacy policy link target.', 'cookie-notice' ) . '</p>
				</div>
				<div id="cn_see_more_link_position">';

		foreach ( $this->link_positions as $position => $label ) {
			echo '
					<label><input id="cn_see_more_link_position-' . $position . '" type="radio" name="cookie_notice_options[link_position]" value="' . $position . '" ' . checked( $position, $this->options['general']['link_position'], false ) . ' />' . esc_html( $label ) . '</label>';
		}

		echo '
					<p class="description">' . esc_html__( 'Select the privacy policy link position.', 'cookie-notice' ) . '</p>
				</div></div>
		</fieldset>';
	}

	/**
	 * Expiration time option.
	 */
	public function cn_time() {
		echo '
		<fieldset>
			<div id="cn_time">
				<select name="cookie_notice_options[time]">';

		foreach ( $this->times as $time => $arr ) {
			$time = esc_attr( $time );

			echo '
					<option value="' . $time . '" ' . selected( $time, $this->options['general']['time'] ) . '>' . esc_html( $arr[0] ) . '</option>';
		}

		echo '
				</select>
				<p class="description">' . __( 'The amount of time that the cookie should be stored for when user accepts the notice.', 'cookie-notice' ) . '</p>
			</div>
		</fieldset>';
	}

	/**
	 * Expiration time option.
	 */
	public function cn_time_rejected() {
		echo '
		<fieldset>
			<div id="cn_time_rejected">
				<select name="cookie_notice_options[time_rejected]">';

		foreach ( $this->times as $time => $arr ) {
			$time = esc_attr( $time );

			echo '
					<option value="' . $time . '" ' . selected( $time, $this->options['general']['time_rejected'] ) . '>' . esc_html( $arr[0] ) . '</option>';
		}

		echo '
				</select>
				<p class="description">' . __( 'The amount of time that the cookie should be stored for when the user doesn\'t accept the notice.', 'cookie-notice' ) . '</p>
			</div>
		</fieldset>';
	}

	/**
	 * Script placement option.
	 */
	public function cn_script_placement() {
		echo '
		<fieldset>';

		foreach ( $this->script_placements as $value => $label ) {
			echo '
			<label><input id="cn_script_placement-' . $value . '" type="radio" name="cookie_notice_options[script_placement]" value="' . esc_attr( $value ) . '" ' . checked( $value, $this->options['general']['script_placement'], false ) . ' />' . esc_html( $label ) . '</label>';
		}

		echo '
			<p class="description">' . __( 'Select where all the plugin scripts should be placed.', 'cookie-notice' ) . '</p>
		</fieldset>';
	}

	/**
	 * Position option.
	 */
	public function cn_position() {
		echo '
		<fieldset>
			<div id="cn_position">';

		foreach ( $this->positions as $value => $label ) {
			$value = esc_attr( $value );

			echo '
				<label><input id="cn_position-' . $value . '" type="radio" name="cookie_notice_options[position]" value="' . $value . '" ' . checked( $value, $this->options['general']['position'], false ) . ' />' . esc_html( $label ) . '</label>';
		}

		echo '
				<p class="description">' . __( 'Select location for the notice.', 'cookie-notice' ) . '</p>
			</div>
		</fieldset>';
	}

	/**
	 * Animation effect option.
	 */
	public function cn_hide_effect() {
		echo '
		<fieldset>
			<div id="cn_hide_effect">';

		foreach ( $this->effects as $value => $label ) {
			$value = esc_attr( $value );

			echo '
				<label><input id="cn_hide_effect-' . $value . '" type="radio" name="cookie_notice_options[hide_effect]" value="' . $value . '" ' . checked( $value, $this->options['general']['hide_effect'], false ) . ' />' . esc_html( $label ) . '</label>';
		}

		echo '
				<p class="description">' . __( 'Select the animation style.', 'cookie-notice' ) . '</p>
			</div>
		</fieldset>';
	}

	/**
	 * On scroll option.
	 */
	public function cn_on_scroll() {
		echo '
		<fieldset>
			<label><input id="cn_on_scroll" type="checkbox" name="cookie_notice_options[on_scroll]" value="1" ' . checked( true, $this->options['general']['on_scroll'], false ) . ' />' . __( 'Enable to accept the notice when user scrolls.', 'cookie-notice' ) . '</label>
			<div id="cn_on_scroll_offset"' . ( $this->options['general']['on_scroll'] === false || $this->options['general']['on_scroll'] == false ? ' style="display: none;"' : '' ) . '>
				<input type="text" class="text" name="cookie_notice_options[on_scroll_offset]" value="' . esc_attr( $this->options['general']['on_scroll_offset'] ) . '" /> <span>px</span>
				<p class="description">' . __( 'Number of pixels user has to scroll to accept the notice and make it disappear.', 'cookie-notice' ) . '</p>
			</div>
		</fieldset>';
	}
	
	/**
	 * On click option.
	 */
	public function cn_on_click() {
		echo '
		<fieldset>
			<label><input id="cn_on_click" type="checkbox" name="cookie_notice_options[on_click]" value="1" ' . checked( true, $this->options['general']['on_click'], false ) . ' />' . __( 'Enable to accept the notice on any click on the page.', 'cookie-notice' ) . '</label>
		</fieldset>';
	}
	
	/**
	 * Delete plugin data on deactivation.
	 */
	public function cn_deactivation_delete() {
		echo '
		<fieldset>
			<label><input id="cn_deactivation_delete" type="checkbox" name="cookie_notice_options[deactivation_delete]" value="1" ' . checked( true, $this->options['general']['deactivation_delete'], false ) . '/>' . __( 'Enable if you want all plugin data to be deleted on deactivation.', 'cookie-notice' ) . '</label>
		</fieldset>';
	}

	/**
	 * CSS style option.
	 */
	public function cn_css_style() {
		echo '
		<fieldset>
			<div id="cn_css_style">';

		foreach ( $this->styles as $value => $label ) {
			$value = esc_attr( $value );

			echo '
				<label><input id="cn_css_style-' . $value . '" type="radio" name="cookie_notice_options[css_style]" value="' . $value . '" ' . checked( $value, $this->options['general']['css_style'], false ) . ' />' . esc_html( $label ) . '</label>';
		}

		echo '
				<p class="description">' . __( 'Select the buttons style.', 'cookie-notice' ) . '</p>
			</div>
		</fieldset>';
	}

	/**
	 * CSS style option.
	 */
	public function cn_css_class() {
		echo '
		<fieldset>
			<div id="cn_css_class">
				<input type="text" class="regular-text" name="cookie_notice_options[css_class]" value="' . esc_attr( $this->options['general']['css_class'] ) . '" />
				<p class="description">' . __( 'Enter additional button CSS classes separated by spaces.', 'cookie-notice' ) . '</p>
			</div>
		</fieldset>';
	}

	/**
	 * Colors option.
	 */
	public function cn_colors() {
		echo '
		<fieldset>';
		
		foreach ( $this->colors as $value => $label ) {
			$value = esc_attr( $value );

			echo '
			<div id="cn_colors-' . $value . '"><label>' . esc_html( $label ) . '</label><br />
				<input class="cn_color" type="text" name="cookie_notice_options[colors][' . $value . ']" value="' . esc_attr( $this->options['general']['colors'][$value] ) . '" />' .
			'</div>';
		}
		
		echo '
			<div id="cn_colors-bar_opacity"><label>' . __( 'Bar opacity', 'cookie-notice' ) . '</label><br />
				<div><input id="cn_colors_bar_opacity_range" class="cn_range" type="range" min="50" max="100" step="1" name="cookie_notice_options[colors][bar_opacity]" value="' . absint( $this->options['general']['colors']['bar_opacity'] ) . '" onchange="cn_colors_bar_opacity_text.value = cn_colors_bar_opacity_range.value" /><input id="cn_colors_bar_opacity_text" class="small-text" type="number" onchange="cn_colors_bar_opacity_range.value = cn_colors_bar_opacity_text.value" min="50" max="100" value="' . absint( $this->options['general']['colors']['bar_opacity'] ) . '" /></div>' .
			'</div>';
		
		echo '
		</fieldset>';
	}

	/**
	 * Validate options.
	 * 
	 * @param array $input
	 * @return array
	 */
	public function validate_options( $input ) {
		if ( ! current_user_can( apply_filters( 'cn_manage_cookie_notice_cap', 'manage_options' ) ) ) {
			return $input;
		}

		if ( isset( $_POST['save_cookie_notice_options'] ) ) {
			// position
			$input['position'] = sanitize_text_field( isset( $input['position'] ) && in_array( $input['position'], array_keys( $this->positions ) ) ? $input['position'] : $this->defaults['general']['position'] );

			// colors
			$input['colors']['text'] = sanitize_text_field( isset( $input['colors']['text'] ) && $input['colors']['text'] !== '' && preg_match( '/^#[a-f0-9]{6}$/', $input['colors']['text'] ) === 1 ? $input['colors']['text'] : $this->defaults['general']['colors']['text'] );
			$input['colors']['bar'] = sanitize_text_field( isset( $input['colors']['bar'] ) && $input['colors']['bar'] !== '' && preg_match( '/^#[a-f0-9]{6}$/', $input['colors']['bar'] ) === 1 ? $input['colors']['bar'] : $this->defaults['general']['colors']['bar'] );
			$input['colors']['bar_opacity'] = absint( isset( $input['colors']['bar_opacity'] ) && $input['colors']['bar_opacity'] >= 50 ? $input['colors']['bar_opacity'] : $this->defaults['general']['colors']['bar_opacity'] );

			// texts
			$input['message_text'] = wp_kses_post( isset( $input['message_text'] ) && $input['message_text'] !== '' ? $input['message_text'] : $this->defaults['general']['message_text'] );
			$input['accept_text'] = sanitize_text_field( isset( $input['accept_text'] ) && $input['accept_text'] !== '' ? $input['accept_text'] : $this->defaults['general']['accept_text'] );
			$input['refuse_text'] = sanitize_text_field( isset( $input['refuse_text'] ) && $input['refuse_text'] !== '' ? $input['refuse_text'] : $this->defaults['general']['refuse_text'] );
			$input['revoke_message_text'] = wp_kses_post( isset( $input['revoke_message_text'] ) && $input['revoke_message_text'] !== '' ? $input['revoke_message_text'] : $this->defaults['general']['revoke_message_text'] );
			$input['revoke_text'] = sanitize_text_field( isset( $input['revoke_text'] ) && $input['revoke_text'] !== '' ? $input['revoke_text'] : $this->defaults['general']['revoke_text'] );
			$input['refuse_opt'] = (bool) isset( $input['refuse_opt'] );
			$input['revoke_cookies'] = isset( $input['revoke_cookies'] );
			$input['revoke_cookies_opt'] = isset( $input['revoke_cookies_opt'] ) && array_key_exists( $input['revoke_cookies_opt'], $this->revoke_opts ) ? $input['revoke_cookies_opt'] : $this->defaults['general']['revoke_cookies_opt'];

			// get allowed HTML
			$allowed_html = $this->HTML->getAllowedHTML();

			// body refuse code
			$input['refuse_code'] = wp_kses( isset( $input['refuse_code'] ) && $input['refuse_code'] !== '' ? $input['refuse_code'] : $this->defaults['general']['refuse_code'], $allowed_html );

			// head refuse code
			$input['refuse_code_head'] = wp_kses( isset( $input['refuse_code_head'] ) && $input['refuse_code_head'] !== '' ? $input['refuse_code_head'] : $this->defaults['general']['refuse_code_head'], $allowed_html );

			// css button style
			$input['css_style'] = sanitize_text_field( isset( $input['css_style'] ) && in_array( $input['css_style'], array_keys( $this->styles ) ) ? $input['css_style'] : $this->defaults['general']['css_style'] );

			// css button class
			$input['css_class'] = sanitize_text_field( isset( $input['css_class'] ) ? $input['css_class'] : $this->defaults['general']['css_class'] );

			// link target
			$input['link_target'] = sanitize_text_field( isset( $input['link_target'] ) && in_array( $input['link_target'], array_keys( $this->link_targets ) ) ? $input['link_target'] : $this->defaults['general']['link_target'] );

			// time
			$input['time'] = sanitize_text_field( isset( $input['time'] ) && in_array( $input['time'], array_keys( $this->times ) ) ? $input['time'] : $this->defaults['general']['time'] );
			$input['time_rejected'] = sanitize_text_field( isset( $input['time_rejected'] ) && in_array( $input['time_rejected'], array_keys( $this->times ) ) ? $input['time_rejected'] : $this->defaults['general']['time_rejected'] );

			// script placement
			$input['script_placement'] = sanitize_text_field( isset( $input['script_placement'] ) && in_array( $input['script_placement'], array_keys( $this->script_placements ) ) ? $input['script_placement'] : $this->defaults['general']['script_placement'] );

			// hide effect
			$input['hide_effect'] = sanitize_text_field( isset( $input['hide_effect'] ) && in_array( $input['hide_effect'], array_keys( $this->effects ) ) ? $input['hide_effect'] : $this->defaults['general']['hide_effect'] );
			
			// redirection
			$input['redirection'] = isset( $input['redirection'] );
			
			// on scroll
			$input['on_scroll'] = isset( $input['on_scroll'] );

			// on scroll offset
			$input['on_scroll_offset'] = absint( isset( $input['on_scroll_offset'] ) && $input['on_scroll_offset'] !== '' ? $input['on_scroll_offset'] : $this->defaults['general']['on_scroll_offset'] );
			
			// on click
			$input['on_click'] = isset( $input['on_click'] );

			// deactivation
			$input['deactivation_delete'] = isset( $input['deactivation_delete'] );

			// privacy policy
			$input['see_more'] = isset( $input['see_more'] );
			$input['see_more_opt']['text'] = sanitize_text_field( isset( $input['see_more_opt']['text'] ) && $input['see_more_opt']['text'] !== '' ? $input['see_more_opt']['text'] : $this->defaults['general']['see_more_opt']['text'] );
			$input['see_more_opt']['link_type'] = sanitize_text_field( isset( $input['see_more_opt']['link_type'] ) && in_array( $input['see_more_opt']['link_type'], array_keys( $this->links ) ) ? $input['see_more_opt']['link_type'] : $this->defaults['general']['see_more_opt']['link_type'] );

			if ( $input['see_more_opt']['link_type'] === 'custom' )
				$input['see_more_opt']['link'] = ( $input['see_more'] === true ? esc_url( $input['see_more_opt']['link'] ) : 'empty' );
			elseif ( $input['see_more_opt']['link_type'] === 'page' ) {
				$input['see_more_opt']['id'] = ( $input['see_more'] === true ? (int) $input['see_more_opt']['id'] : 0 );
				$input['see_more_opt']['sync'] = isset( $input['see_more_opt']['sync'] );

				if ( $input['see_more_opt']['sync'] )
					update_option( 'wp_page_for_privacy_policy', $input['see_more_opt']['id'] );
			}
			
			// policy link position
			$input['link_position'] = sanitize_text_field( isset( $input['link_position'] ) && in_array( $input['link_position'], array_keys( $this->link_positions ) ) ? $input['link_position'] : $this->defaults['general']['link_position'] );

			// message link position?
			if ( $input['see_more'] === true && $input['link_position'] === 'message' && strpos( $input['message_text'], '[cookies_policy_link' ) === false )
				$input['message_text'] .= ' [cookies_policy_link]';
			
			
			$input['translate'] = false;

			// WPML >= 3.2
			if ( defined( 'ICL_SITEPRESS_VERSION' ) && version_compare( ICL_SITEPRESS_VERSION, '3.2', '>=' ) ) {
				do_action( 'wpml_register_single_string', 'Rd Cookie Notice', 'Message in the notice', $input['message_text'] );
				do_action( 'wpml_register_single_string', 'Rd Cookie Notice', 'Button text', $input['accept_text'] );
				do_action( 'wpml_register_single_string', 'Rd Cookie Notice', 'Refuse button text', $input['refuse_text'] );
				do_action( 'wpml_register_single_string', 'Rd Cookie Notice', 'Revoke message text', $input['revoke_message_text'] );
				do_action( 'wpml_register_single_string', 'Rd Cookie Notice', 'Revoke button text', $input['revoke_text'] );
				do_action( 'wpml_register_single_string', 'Rd Cookie Notice', 'Privacy policy text', $input['see_more_opt']['text'] );

				if ( $input['see_more_opt']['link_type'] === 'custom' )
					do_action( 'wpml_register_single_string', 'Rd Cookie Notice', 'Custom link', $input['see_more_opt']['link'] );
			}
		} elseif ( isset( $_POST['reset_cookie_notice_options'] ) ) {
			
			$input = $this->defaults['general'];

			add_settings_error( 'reset_cookie_notice_options', 'reset_cookie_notice_options', __( 'Settings restored to defaults.', 'cookie-notice' ), 'updated' );
			
		}

		return $input;
	}


	/**
	 * Check if cookies are accepted.
	 * 
	 * @return bool
	 */
	public static function cookies_accepted() {
		return apply_filters( 'cn_is_cookie_accepted', isset( $_COOKIE['cookie_notice_accepted'] ) && $_COOKIE['cookie_notice_accepted'] === 'true' );
	}

	/**
	 * Check if cookies are set.
	 *
	 * @return boolean Whether cookies are set
	 */
	public function cookies_set() {
		return apply_filters( 'cn_is_cookie_set', isset( $_COOKIE['cookie_notice_accepted'] ) );
	}
		
	/**
     * Get default settings.
     */
    public function get_defaults() {
        return $this->defaults;
    }
	
	
	/**
	 * Check legacy parameters that were yes/no strings.
	 *
	 * @param array $options
	 * @param array $params
	 * @return array
	 */
	public function check_legacy_params( $options, $params ) {
		foreach ( $params as $param ) {
			if ( array_key_exists( $param, $options ) && ! is_bool( $options[$param] ) )
				$options[$param] = $options[$param] === 'yes';
		}

		return $options;
	}

	/**
	 * Merge multidimensional associative arrays.
	 * Works only with strings, integers and arrays as keys. Values can be any type but they have to have same type to be kept in the final array.
	 * Every array should have the same type of elements. Only keys from $defaults array will be kept in the final array unless $siblings are not empty.
	 * $siblings examples: array( '=>', 'only_first_level', 'first_level=>second_level', 'first_key=>next_key=>sibling' ) and so on.
	 * Single '=>' means that all siblings of the highest level will be kept in the final array.
	 *
	 * @param array	$default Array with defaults values
	 * @param array	$array Array to merge
	 * @param boolean|array	$siblings Whether to allow "string" siblings to copy from $array if they do not exist in $defaults, false otherwise
	 * @return array Merged arrays
	 */
	public function multi_array_merge( $defaults, $array, $siblings = false ) {
		// make a copy for better performance and to prevent $default override in foreach
		$copy = $defaults;

		// prepare siblings for recursive deeper level
		$new_siblings = [];

		// allow siblings?
		if ( ! empty( $siblings ) && is_array( $siblings ) ) {
			foreach ( $siblings as $sibling ) {
				// highest level siblings
				if ( $sibling === '=>' ) {
					// copy all non-existent string siblings
					foreach( $array as $key => $value ) {
						if ( is_string( $key ) && ! array_key_exists( $key, $defaults ) ) {
							$defaults[$key] = null;
						}
					}
				// sublevel siblings
				} else {
					// explode siblings
					$ex = explode( '=>', $sibling );

					// copy all non-existent siblings
					foreach ( array_keys( $array[$ex[0]] ) as $key ) {
						if ( ! array_key_exists( $key, $defaults[$ex[0]] ) )
							$defaults[$ex[0]][$key] = null;
					}

					// more than one sibling child?
					if ( count( $ex ) > 1 )
						$new_siblings[$ex[0]] = array( substr_replace( $sibling, '', 0, strlen( $ex[0] . '=>' ) ) );
					// no more sibling children
					else
						$new_siblings[$ex[0]] = false;
				}
			}
		}

		// loop through first array
		foreach ( $defaults as $key => $value ) {
			// integer key?
			if ( is_int( $key ) ) {
				$copy = array_unique( array_merge( $defaults, $array ), SORT_REGULAR );

				break;
			// string key?
			} elseif ( is_string( $key ) && isset( $array[$key] ) ) {
				// string, boolean, integer or null values?
				if ( ( is_string( $value ) && is_string( $array[$key] ) ) || ( is_bool( $value ) && is_bool( $array[$key] ) ) || ( is_int( $value ) && is_int( $array[$key] ) ) || is_null( $value ) )
					$copy[$key] = $array[$key];
				// arrays
				elseif ( is_array( $value ) && isset( $array[$key] ) && is_array( $array[$key] ) ) {
					if ( empty( $value ) )
						$copy[$key] = $array[$key];
					else
						$copy[$key] = $this->multi_array_merge( $defaults[$key], $array[$key], ( isset( $new_siblings[$key] ) ? $new_siblings[$key] : false ) );
				}
			}
		}

		return $copy;
	}

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
    }


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
