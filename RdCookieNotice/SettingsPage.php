<?php
/**
 * @license http://opensource.org/licenses/MIT MIT
 * @package rd-cookie-notice
 */


namespace RdCookieNotice;


/**
 * Settings page class.
 * 
 * @since 0.2.0
 */
class SettingsPage
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
     * Add admin sub menu.
     * 
     * @link https://developer.wordpress.org/reference/hooks/admin_menu/ `admin_menu` hook reference.
     * @link https://developer.wordpress.org/reference/functions/add_options_page/ `add_options_page()` reference.
     * @param string $context
     */
    public function addAdminMenu($context = '')
    {
        add_options_page(
            __('Rundiz Cookie Notice', 'rd-cookie-notice'), 
            __('Rundiz Cookie Notice', 'rd-cookie-notice'),
            apply_filters('cn_manage_cookie_notice_cap', 'manage_options'),
            'rd-cookie-notice-settings', 
            [$this, 'optionsPage']
        );
    }// addAdminMenu


    /**
     * Accept on click anywhere option.
     */
    public function formFieldAcceptOnClickAny()
    {
        echo '
        <fieldset>
            <label>' .
                '<input id="cn_on_click" type="checkbox" name="cookie_notice_options[on_click]" value="1" ' . checked(true, $this->RdCookieNotice->options['general']['on_click'], false) . ' />' . 
                __('Enable to accept the notice on any click on the page.', 'rd-cookie-notice') . 
            '</label>
        </fieldset>';
    }// formFieldAcceptOnClickAny


    /**
     * Accept cookie on scroll option.
     */
    public function formFieldAcceptOnScroll()
    {
        echo '
        <fieldset>
            <label>' .
                '<input id="cn_on_scroll" type="checkbox" name="cookie_notice_options[on_scroll]" value="1" ' . checked(true, $this->RdCookieNotice->options['general']['on_scroll'], false) . ' />' . 
                __('Enable to accept the notice when user scrolls.', 'rd-cookie-notice') . 
            '</label>
            <div id="cn_on_scroll_offset"' . ($this->RdCookieNotice->options['general']['on_scroll'] === false || $this->RdCookieNotice->options['general']['on_scroll'] == false ? ' style="display: none;"' : '') . '>
                <input type="text" class="text" name="cookie_notice_options[on_scroll_offset]" value="' . esc_attr($this->RdCookieNotice->options['general']['on_scroll_offset']) . '" /> <span>px</span>
                <p class="description">' . __('Number of pixels user has to scroll to accept the notice and make it disappear.', 'rd-cookie-notice') . '</p>
            </div>
        </fieldset>';
    }// formFieldAcceptOnScroll


    /**
     * Accept cookie label option.
     */
    public function formFieldAcceptText()
    {
        echo '<fieldset>
            <div id="cn_accept_text">
                <input type="text" class="regular-text" name="cookie_notice_options[accept_text]" value="' . esc_attr($this->RdCookieNotice->options['general']['accept_text']) . '" />
                <p class="description">' . __('The text of the option to accept the notice and make it disappear.', 'rd-cookie-notice') . '</p>
            </div>
        </fieldset>';
    }// formFieldAcceptText


    /**
     * Button class option.
     */
    public function formFieldButtonClass()
    {
        echo '
        <fieldset>
            <div id="cn_css_class">
                <input type="text" class="regular-text" name="cookie_notice_options[css_class]" value="' . esc_attr($this->RdCookieNotice->options['general']['css_class']) . '" />
                <p class="description">' . __('Enter additional button CSS classes separated by spaces.', 'rd-cookie-notice') . '</p>
            </div>
        </fieldset>';
    }// formFieldButtonClass


    /**
     * Button style option.
     */
    public function formFieldButtonStyle()
    {
        echo '
        <fieldset>
            <div id="cn_css_style">';

        foreach ($this->RdCookieNotice->styles as $value => $label) {
            $value = esc_attr($value);

            echo '
                <label>' .
                    '<input id="cn_css_style-' . $value . '" type="radio" name="cookie_notice_options[css_style]" value="' . $value . '" ' . checked($value, $this->RdCookieNotice->options['general']['css_style'], false) . ' />' . 
                    esc_html($label) . 
                '</label>';
        }// endforeach;
        unset($label, $value);

        echo '
                <p class="description">' . __('Select the buttons style.', 'rd-cookie-notice') . '</p>
            </div>
        </fieldset>';
    }// formFieldButtonStyle


    /**
     * Colors option.
     */
    public function formFieldColors()
    {
        echo '
        <fieldset>';

        foreach ($this->RdCookieNotice->colors as $value => $label) {
            $value = esc_attr($value);

            echo '
            <div id="cn_colors-' . $value . '"><label>' . esc_html($label) . '</label><br />
                <input class="cn_color" type="text" name="cookie_notice_options[colors][' . $value . ']" value="' . esc_attr($this->RdCookieNotice->options['general']['colors'][$value]) . '" />' .
            '</div>';
        }// endforeach;
        unset($label, $value);

        echo '
            <div id="cn_colors-bar_opacity"><label>' . __('Bar opacity', 'rd-cookie-notice') . '</label><br />
                <div>' .
                    '<input id="cn_colors_bar_opacity_range" class="cn_range" type="range" min="50" max="100" step="1" name="cookie_notice_options[colors][bar_opacity]" value="' . absint($this->RdCookieNotice->options['general']['colors']['bar_opacity']) . '" onchange="cn_colors_bar_opacity_text.value = cn_colors_bar_opacity_range.value" />' .
                    '<input id="cn_colors_bar_opacity_text" class="small-text" type="number" onchange="cn_colors_bar_opacity_range.value = cn_colors_bar_opacity_text.value" min="50" max="100" value="' . absint($this->RdCookieNotice->options['general']['colors']['bar_opacity']) . '" />' .
                '</div>' .
        '</div>';

        echo '
        </fieldset>';
    }// formFieldColors


    /**
     * Delete plugin data on deactivation.
     */
    public function formFieldDeactivationDelete()
    {
        echo '
        <fieldset>
            <label>' .
                '<input id="cn_deactivation_delete" type="checkbox" name="cookie_notice_options[deactivation_delete]" value="1" ' . checked(true, $this->RdCookieNotice->options['general']['deactivation_delete'], false) . '/>' . 
                __('Enable if you want all plugin data to be deleted on deactivation.', 'rd-cookie-notice') . 
            '</label>
        </fieldset>';
    }// formFieldDeactivationDelete


    /**
     * Expiration time on accept option.
     */
    public function formFieldExpireTimeAccept()
    {
        echo '
        <fieldset>
            <div id="cn_time">
                <select name="cookie_notice_options[time]">';

        foreach ($this->RdCookieNotice->times as $time => $arr) {
            $time = esc_attr($time);

            echo '
                    <option value="' . $time . '" ' . selected($time, $this->RdCookieNotice->options['general']['time']) . '>' . esc_html($arr[0]) . '</option>' . PHP_EOL;
        }// endforeach;
        unset($arr, $time);

        echo '
                </select>
                <p class="description">' . __('The amount of time that the cookie should be stored for when user accepts the notice.', 'rd-cookie-notice') . '</p>
            </div>
        </fieldset>';
    }// formFieldExpireTimeAccept


    /**
     * Expiration time on reject option.
     */
    public function formFieldExpireTimeReject()
    {
        echo '
        <fieldset>
            <div id="cn_time_rejected">
                <select name="cookie_notice_options[time_rejected]">';

        foreach ($this->RdCookieNotice->times as $time => $arr) {
            $time = esc_attr($time);

            echo '
                    <option value="' . $time . '" ' . selected($time, $this->RdCookieNotice->options['general']['time_rejected']) . '>' . esc_html($arr[0]) . '</option>';
        }// endforeach;
        unset($arr, $time);

        echo '
                </select>
                <p class="description">' . __('The amount of time that the cookie should be stored for when the user doesn\'t accept the notice.', 'rd-cookie-notice') . '</p>
            </div>
        </fieldset>';
    }// formFieldExpireTimeReject


    /**
     * Animation effect on hide option.
     */
    public function formFieldHideEffect()
    {
        echo '
        <fieldset>
            <div id="cn_hide_effect">';

        foreach ($this->RdCookieNotice->effects as $value => $label) {
            $value = esc_attr($value);

            echo '
                <label>' .
                    '<input id="cn_hide_effect-' . $value . '" type="radio" name="cookie_notice_options[hide_effect]" value="' . $value . '" ' . checked($value, $this->RdCookieNotice->options['general']['hide_effect'], false) . ' />' . 
                    esc_html($label) . 
                '</label>';
        }// endforeach;
        unset($label, $value);

        echo '
                <p class="description">' . __('Select the animation style.', 'rd-cookie-notice') . '</p>
            </div>
        </fieldset>';
    }// formFieldHideEffect


    /**
     * Cookie message option.
     */
    public function formFieldMessageText()
    {
        echo '<fieldset>
            <div id="cn_message_text">
                <textarea name="cookie_notice_options[message_text]" class="large-text" cols="50" rows="5">' . esc_textarea($this->RdCookieNotice->options['general']['message_text']) . '</textarea>
                <p class="description">' . __('Enter the cookie notice message.', 'rd-cookie-notice') . '</p>
            </div>
        </fieldset>';
    }// formFieldMessageText


    /**
     * Notice position option.
     */
    public function formFieldNoticePosition()
    {
        echo '
        <fieldset>
            <div id="cn_position">';

        foreach ($this->RdCookieNotice->positions as $value => $label) {
            $value = esc_attr($value);

            echo '
                <label>' .
                    '<input id="cn_position-' . $value . '" type="radio" name="cookie_notice_options[position]" value="' . $value . '" ' . checked($value, $this->RdCookieNotice->options['general']['position'], false) . ' />' . 
                    esc_html($label) . 
                '</label>';
        }// endforeach;
        unset($label, $value);

        echo '
                <p class="description">' . __('Select location for the notice.', 'rd-cookie-notice') . '</p>
            </div>
        </fieldset>';
    }// formFieldNoticePosition


    /**
     * Privacy policy link option.
     */
    public function formFieldPrivacyPolicy()
    {
        $pages = get_pages(
            [
                'sort_order' => 'ASC',
                'sort_column' => 'post_title',
                'hierarchical' => 0,
                'child_of' => 0,
                'parent' => -1,
                'offset' => 0,
                'post_type' => 'page',
                'post_status' => 'publish',
            ]
        );

        echo '<fieldset>
            <label><input id="cn_see_more" type="checkbox" name="cookie_notice_options[see_more]" value="1" ' . checked(true, $this->RdCookieNotice->options['general']['see_more'], false) . ' />' . __('Enable privacy policy link.', 'rd-cookie-notice') . '</label>
            <div id="cn_see_more_opt"' . ($this->RdCookieNotice->options['general']['see_more'] === false ? ' style="display: none;"' : '') . '>
                <input type="text" class="regular-text" name="cookie_notice_options[see_more_opt][text]" value="' . esc_attr($this->RdCookieNotice->options['general']['see_more_opt']['text']) . '" />
                <p class="description">' . __('The text of the privacy policy button.', 'rd-cookie-notice') . '</p>
                <div id="cn_see_more_opt_custom_link">' . PHP_EOL;

        foreach ($this->RdCookieNotice->links as $value => $label) {
            $value = esc_attr($value);

            echo str_repeat('    ', 5);
            echo '<label>' .
                '<input id="cn_see_more_link-' . $value . '" type="radio" name="cookie_notice_options[see_more_opt][link_type]" value="' . $value . '" ' . checked($value, $this->RdCookieNotice->options['general']['see_more_opt']['link_type'], false) . ' />' . 
                esc_html($label) . 
                '</label>' . PHP_EOL;
        }// endforeach;
        unset($label, $value);

        echo '
                </div><!--#cn_see_more_opt_custom_link-->
                <p class="description">' . __('Select where to redirect user for more information.', 'rd-cookie-notice') . '</p>
                <div id="cn_see_more_opt_page"' . ($this->RdCookieNotice->options['general']['see_more_opt']['link_type'] === 'custom' ? ' style="display: none;"' : '') . '>
                    <select name="cookie_notice_options[see_more_opt][id]">
                        <option value="0" ' . selected(0, $this->RdCookieNotice->options['general']['see_more_opt']['id'], false) . '>' . __('-- select page --', 'rd-cookie-notice') . '</option>';

        if ($pages) {
            foreach ($pages as $page) {
                echo str_repeat('    ', 6);
                echo '<option value="' . $page->ID . '" ' . selected($page->ID, $this->RdCookieNotice->options['general']['see_more_opt']['id'], false) . '>' . esc_html($page->post_title) . '</option>' . PHP_EOL;
            }// endforeach;
            unset($page);
        }

        echo '
                    </select>
                    <p class="description">' . __('Select from one of your site\'s pages.', 'rd-cookie-notice') . '</p>' . PHP_EOL;

        global $wp_version;
        if (version_compare($wp_version, '4.9.6', '>=')) {
            echo str_repeat('    ', 5);
            echo '<label>' .
                '<input id="cn_see_more_opt_sync" type="checkbox" name="cookie_notice_options[see_more_opt][sync]" value="1" ' . checked(true, $this->RdCookieNotice->options['general']['see_more_opt']['sync'], false) . ' />' . 
                __('Synchronize with WordPress Privacy Policy page.', 'rd-cookie-notice') . 
                '</label>' . PHP_EOL;
        }

        echo '
                </div><!--#cn_see_more_opt_page-->
                <div id="cn_see_more_opt_link"' . ($this->RdCookieNotice->options['general']['see_more_opt']['link_type'] === 'page' ? ' style="display: none;"' : '') . '>
                    <input type="text" class="regular-text" name="cookie_notice_options[see_more_opt][link]" value="' . esc_attr($this->RdCookieNotice->options['general']['see_more_opt']['link']) . '" />
                    <p class="description">' . __('Enter the full URL starting with http(s)://', 'rd-cookie-notice') . '</p>
                </div><!--#cn_see_more_opt_link-->
                <div id="cn_see_more_link_target">' . PHP_EOL;

        foreach ($this->RdCookieNotice->link_targets as $target) {
            echo str_repeat('    ', 5);
            echo '<label>' .
                '<input id="cn_see_more_link_target-' . $target . '" type="radio" name="cookie_notice_options[link_target]" value="' . $target . '" ' . checked($target, $this->RdCookieNotice->options['general']['link_target'], false) . ' />' . 
                $target . 
                '</label>' . PHP_EOL;
        }// endforeach;
        unset($target);

        echo '
                    <p class="description">' . esc_html__('Select the privacy policy link target.', 'rd-cookie-notice') . '</p>
                </div><!--#cn_see_more_link_target-->
                <div id="cn_see_more_link_position">' . PHP_EOL;

        foreach ($this->RdCookieNotice->link_positions as $position => $label) {
            echo str_repeat('    ', 5);
            echo '<label>' .
                '<input id="cn_see_more_link_position-' . $position . '" type="radio" name="cookie_notice_options[link_position]" value="' . $position . '" ' . checked($position, $this->RdCookieNotice->options['general']['link_position'], false) . ' />' . 
                esc_html($label) . 
                '</label>' . PHP_EOL;
        }// endforeach;
        unset($label, $position);

        echo '
                    <p class="description">' . esc_html__('Select the privacy policy link position.', 'rd-cookie-notice') . '</p>
                </div><!--#cn_see_more_link_position-->
            </div><!--#cn_see_more_opt-->
        </fieldset>' . PHP_EOL;
    }// formFieldPrivacyPolicy


    /**
     * Enable/Disable third party non functional cookies option.
     */
    public function formFieldRefuseOption()
    {
        echo '
        <fieldset>
            <label><input id="cn_refuse_opt" type="checkbox" name="cookie_notice_options[refuse_opt]" value="1" ' . checked(true, $this->RdCookieNotice->options['general']['refuse_opt'], false) . ' />' . __('Enable to give to the user the possibility to refuse third party non functional cookies.', 'rd-cookie-notice') . '</label>
            <div id="cn_refuse_opt_container"' . ($this->RdCookieNotice->options['general']['refuse_opt'] === false ? ' style="display: none;"' : '') . '>
                <div id="cn_refuse_text">
                    <input type="text" class="regular-text" name="cookie_notice_options[refuse_text]" value="' . esc_attr($this->RdCookieNotice->options['general']['refuse_text']) . '" />
                    <p class="description">' . __('The text of the button to refuse the consent.', 'rd-cookie-notice') . '</p>
                </div>
            </div>
        </fieldset>';
    }// formFieldRefuseOption


    /**
     * Redirection (reload) on cookie accept.
     */
    public function formFieldReloadAccept()
    {
        echo '
        <fieldset>
            <label>' .
                '<input id="cn_redirection" type="checkbox" name="cookie_notice_options[redirection]" value="1" ' . checked(true, $this->RdCookieNotice->options['general']['redirection'], false) . ' />' . 
                __('Enable to reload the page after the notice is accepted.', 'rd-cookie-notice') . 
            '</label>
        </fieldset>';
    }// formFieldReloadAccept


    /**
     * Revoke cookies option.
     */
    public function formFieldRevokeOption()
    {
        echo '
        <fieldset>
            <label><input id="cn_revoke_cookies" type="checkbox" name="cookie_notice_options[revoke_cookies]" value="1" ' . checked(true, $this->RdCookieNotice->options['general']['revoke_cookies'], false) . ' />' . __('Enable to give to the user the possibility to revoke their consent <i>(requires "Refuse consent" option enabled)</i>.', 'rd-cookie-notice') . '</label>
            <div id="cn_revoke_opt_container"' . ($this->RdCookieNotice->options['general']['revoke_cookies'] ? '' : ' style="display: none;"') . '>
                <textarea name="cookie_notice_options[revoke_message_text]" class="large-text" cols="50" rows="2">' . esc_textarea($this->RdCookieNotice->options['general']['revoke_message_text']) . '</textarea>
                <p class="description">' . __('Enter the revoke message.', 'rd-cookie-notice') . '</p>
                <input type="text" class="regular-text" name="cookie_notice_options[revoke_text]" value="' . esc_attr($this->RdCookieNotice->options['general']['revoke_text']) . '" />
                <p class="description">' . __('The text of the button to revoke the consent.', 'rd-cookie-notice') . '</p>';

        foreach ($this->RdCookieNotice->revoke_opts as $value => $label) {
            echo '
                <label>' .
                '<input id="cn_revoke_cookies-' . $value . '" type="radio" name="cookie_notice_options[revoke_cookies_opt]" value="' . $value . '" ' . checked($value, $this->RdCookieNotice->options['general']['revoke_cookies_opt'], false) . ' />' . 
                esc_html($label) . 
                '</label>' . PHP_EOL;
        }// endforeach;
        unset($label, $value);

        echo '
                <p class="description">' . __('Select the method for displaying the revoke button - automatic (in the banner) or manual using <code>[cookies_revoke]</code> shortcode.', 'rd-cookie-notice') . '</p>
            </div>
        </fieldset>';
    }// formFieldRevokeOption


    /**
     * Non functional cookies code.
     */
    public function formFieldScriptBlock()
    {
        $allowed_html = $this->RdCookieNotice->HTML->getAllowedHTML();
        $active = (!empty($this->RdCookieNotice->options['general']['refuse_code']) && empty($this->RdCookieNotice->options['general']['refuse_code_head']) ? 'body' : 'head');

        $allowedTagsDisplay = '';
        $arrayKeys = array_keys($allowed_html);
        $lastArrayKey = array_pop($arrayKeys);
        foreach ($allowed_html as $tag => $details) {
            $allowedTagsDisplay .= '<code>' . $tag . '</code>';
            if ($tag !== $lastArrayKey) {
                $allowedTagsDisplay .= ', ';
            }
        }// endforeach;
        unset($details, $tag);
        unset($arrayKeys, $lastArrayKey);

        echo '
        <fieldset>
            <div id="cn_refuse_code">
                <div id="cn_refuse_code_fields">
                    <h2 class="nav-tab-wrapper">
                        <a id="refuse_head-tab" class="nav-tab' . ($active === 'head' ? ' nav-tab-active' : '') . '" href="#refuse_head">' . __('Head', 'rd-cookie-notice') . '</a>
                        <a id="refuse_body-tab" class="nav-tab' . ($active === 'body' ? ' nav-tab-active' : '') . '" href="#refuse_body">' . __('Body', 'rd-cookie-notice') . '</a>
                    </h2>
                    <div id="refuse_head" class="refuse-code-tab' . ($active === 'head' ? ' active' : '') . '">
                        <p class="description">' . __('The code to be used in your site header, before the closing head tag.', 'rd-cookie-notice') . '</p>
                        <textarea name="cookie_notice_options[refuse_code_head]" class="large-text" cols="50" rows="8" placeholder="&lt;script&gt;// your code&lt;/script&gt;">' . 
                            html_entity_decode(trim(wp_kses($this->RdCookieNotice->options['general']['refuse_code_head'], $allowed_html))) . 
                        '</textarea>
                    </div>
                    <div id="refuse_body" class="refuse-code-tab' . ($active === 'body' ? ' active' : '') . '">
                        <p class="description">' . __('The code to be used in your site footer, before the closing body tag.', 'rd-cookie-notice') . '</p>
                        <textarea name="cookie_notice_options[refuse_code]" class="large-text" cols="50" rows="8" placeholder="&lt;script&gt;// your code&lt;/script&gt;">' . 
                            html_entity_decode(trim(wp_kses($this->RdCookieNotice->options['general']['refuse_code'], $allowed_html))) . 
                        '</textarea>
                    </div>
                </div>
                <p class="description">' . __('Enter non functional cookies Javascript code here (for e.g. Google Analitycs) to be used after the notice is accepted.', 'rd-cookie-notice') . '</br>' . 
                    __('To get the user consent status use the <code>cn_cookies_accepted()</code> function.', 'rd-cookie-notice') . 
                '</p>
                <p class="description">' . sprintf(__('Allowed tags (%1$s).', 'rd-cookie-notice'), $allowedTagsDisplay) . 
                '</p>
            </div>
        </fieldset>';

        unset($active, $allowed_html, $allowedTagsDisplay);
    }// formFieldScriptBlock


    /**
     * Script placement option.
     */
    public function formFieldScriptPlacement()
    {
        echo '
        <fieldset>';

        foreach ($this->RdCookieNotice->script_placements as $value => $label) {
            echo '
            <label>' .
                '<input id="cn_script_placement-' . $value . '" type="radio" name="cookie_notice_options[script_placement]" value="' . esc_attr($value) . '" ' . checked($value, $this->RdCookieNotice->options['general']['script_placement'], false) . ' />' . 
                esc_html($label) . 
            '</label>';
        }// endforeach;
        unset($label, $value);

        echo '
            <p class="description">' . __('Select where all the plugin scripts should be placed.', 'rd-cookie-notice') . '</p>
        </fieldset>';
    }// formFieldScriptPlacement


    /**
     * Display options page.
     */
    public function optionsPage()
    {
        echo '<div class="wrap">
            <h2>' . __('Rundiz Cookie Notice', 'rd-cookie-notice') . '</h2>
            <div class="cookie-notice-settings">
                <form action="options.php" method="post">';

        settings_fields('cookie_notice_options');
        do_settings_sections('cookie_notice_options');

        echo '  <p class="submit">';
        submit_button('', 'primary', 'save_cookie_notice_options', false);
        echo ' ';
        submit_button(__('Reset to defaults', 'rd-cookie-notice'), 'secondary', 'reset_cookie_notice_options', false);
        echo '  </p>
                </form>
            </div>
            <div class="clear"></div>
        </div>';
    }// optionsPage


    /**
     * Register plugin settings.
     */
    public function registerSettings()
    {
        register_setting('cookie_notice_options', 'cookie_notice_options', [$this->RdCookieNotice->SettingsProcess, 'validateOptions']);

        // configuration
        add_settings_section('cookie_notice_configuration', __('Configuration', 'rd-cookie-notice'), [$this, 'sectionConfig'], 'cookie_notice_options');
        add_settings_field('cn_message_text', __('Message', 'rd-cookie-notice'), [$this, 'formFieldMessageText'], 'cookie_notice_options', 'cookie_notice_configuration');
        add_settings_field('cn_accept_text', __('Button text', 'rd-cookie-notice'), [$this, 'formFieldAcceptText'], 'cookie_notice_options', 'cookie_notice_configuration');
        add_settings_field('cn_see_more', __('Privacy policy', 'rd-cookie-notice'), [$this, 'formFieldPrivacyPolicy'], 'cookie_notice_options', 'cookie_notice_configuration');
        add_settings_field('cn_refuse_opt', __('Refuse consent', 'rd-cookie-notice'), [$this, 'formFieldRefuseOption'], 'cookie_notice_options', 'cookie_notice_configuration');
        add_settings_field('cn_revoke_opt', __('Revoke consent', 'rd-cookie-notice'), [$this, 'formFieldRevokeOption'], 'cookie_notice_options', 'cookie_notice_configuration');
        add_settings_field('cn_refuse_code', __('Script blocking', 'rd-cookie-notice'), [$this, 'formFieldScriptBlock'], 'cookie_notice_options', 'cookie_notice_configuration');
        add_settings_field('cn_redirection', __('Reloading', 'rd-cookie-notice'), [$this, 'formFieldReloadAccept'], 'cookie_notice_options', 'cookie_notice_configuration');
        add_settings_field('cn_on_scroll', __('On scroll', 'rd-cookie-notice'), [$this, 'formFieldAcceptOnScroll'], 'cookie_notice_options', 'cookie_notice_configuration');
        add_settings_field('cn_on_click', __('On click', 'rd-cookie-notice'), [$this, 'formFieldAcceptOnClickAny'], 'cookie_notice_options', 'cookie_notice_configuration');
        add_settings_field('cn_time', __('Accepted expiry', 'rd-cookie-notice'), [$this, 'formFieldExpireTimeAccept'], 'cookie_notice_options', 'cookie_notice_configuration');
        add_settings_field('cn_time_rejected', __('Rejected expiry', 'rd-cookie-notice'), [$this, 'formFieldExpireTimeReject'], 'cookie_notice_options', 'cookie_notice_configuration');
        add_settings_field('cn_script_placement', __('Script placement', 'rd-cookie-notice'), [$this, 'formFieldScriptPlacement'], 'cookie_notice_options', 'cookie_notice_configuration');
        add_settings_field('cn_deactivation_delete', __('Deactivation', 'rd-cookie-notice'), [$this, 'formFieldDeactivationDelete'], 'cookie_notice_options', 'cookie_notice_configuration');

        // design
        add_settings_section('cookie_notice_design', __('Design', 'rd-cookie-notice'), [$this, 'sectionDesign'], 'cookie_notice_options');
        add_settings_field('cn_position', __('Position', 'rd-cookie-notice'), [$this, 'formFieldNoticePosition'], 'cookie_notice_options', 'cookie_notice_design');
        add_settings_field('cn_hide_effect', __('Animation', 'rd-cookie-notice'), [$this, 'formFieldHideEffect'], 'cookie_notice_options', 'cookie_notice_design');
        add_settings_field('cn_css_style', __('Button style', 'rd-cookie-notice'), [$this, 'formFieldButtonStyle'], 'cookie_notice_options', 'cookie_notice_design');
        add_settings_field('cn_css_class', __('Button class', 'rd-cookie-notice'), [$this, 'formFieldButtonClass'], 'cookie_notice_options', 'cookie_notice_design');
        add_settings_field('cn_colors', __('Colors', 'rd-cookie-notice'), [$this, 'formFieldColors'], 'cookie_notice_options', 'cookie_notice_design');
    }// registerSettings


    /**
     * Section configuration.
     * 
     * Section callback: fix for WP < 3.3
     */
    public function sectionConfig() {}


    /**
     * Section design.
     * 
     * Section callback: fix for WP < 3.3
     */
    public function sectionDesign() {}


}
