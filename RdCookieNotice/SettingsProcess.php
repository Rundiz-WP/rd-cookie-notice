<?php
/**
 * @license http://opensource.org/licenses/MIT MIT
 * @package rd-cookie-notice
 */


namespace RdCookieNotice;


/**
 * Settings process class.
 * 
 * @since 0.2.0
 */
class SettingsProcess
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
     * Check legacy parameters that were yes/no strings.
     *
     * @param array $options
     * @param array $params
     * @return array
     */
    public function checkLegacyParams(array $options, array $params): array
    {
        foreach ($params as $param) {
            if (array_key_exists($param, $options) && !is_bool($options[$param])) {
                $options[$param] = $options[$param] === 'yes';
            }
        }
        unset($param);

        return $options;
    }// checkLegacyParams


    /**
     * Validate options.
     * 
     * @param array $input
     * @return array
     */
    public function validateOptions(array $input): array
    {
        if (!current_user_can(apply_filters('cn_manage_cookie_notice_cap', 'manage_options'))) {
            return $input;
        }

        if (isset($_POST['save_cookie_notice_options'])) {
            // position
            $input['position'] = sanitize_text_field(isset($input['position']) && in_array($input['position'], array_keys($this->RdCookieNotice->positions)) ? $input['position'] : $this->RdCookieNotice->defaults['general']['position']);

            // colors
            $input['colors']['text'] = sanitize_text_field(isset($input['colors']['text']) && $input['colors']['text'] !== '' && preg_match('/^#[a-f0-9]{6}$/', $input['colors']['text']) === 1 ? $input['colors']['text'] : $this->RdCookieNotice->defaults['general']['colors']['text']);
            $input['colors']['bar'] = sanitize_text_field(isset($input['colors']['bar']) && $input['colors']['bar'] !== '' && preg_match('/^#[a-f0-9]{6}$/', $input['colors']['bar']) === 1 ? $input['colors']['bar'] : $this->RdCookieNotice->defaults['general']['colors']['bar']);
            $input['colors']['bar_opacity'] = absint(isset($input['colors']['bar_opacity']) && $input['colors']['bar_opacity'] >= 50 ? $input['colors']['bar_opacity'] : $this->RdCookieNotice->defaults['general']['colors']['bar_opacity']);

            // texts
            $input['message_text'] = wp_kses_post(isset($input['message_text']) && $input['message_text'] !== '' ? $input['message_text'] : $this->RdCookieNotice->defaults['general']['message_text']);
            $input['accept_text'] = sanitize_text_field(isset($input['accept_text']) && $input['accept_text'] !== '' ? $input['accept_text'] : $this->RdCookieNotice->defaults['general']['accept_text']);
            $input['refuse_text'] = sanitize_text_field(isset($input['refuse_text']) && $input['refuse_text'] !== '' ? $input['refuse_text'] : $this->RdCookieNotice->defaults['general']['refuse_text']);
            $input['revoke_message_text'] = wp_kses_post(isset($input['revoke_message_text']) && $input['revoke_message_text'] !== '' ? $input['revoke_message_text'] : $this->RdCookieNotice->defaults['general']['revoke_message_text']);
            $input['revoke_text'] = sanitize_text_field(isset($input['revoke_text']) && $input['revoke_text'] !== '' ? $input['revoke_text'] : $this->RdCookieNotice->defaults['general']['revoke_text']);
            $input['refuse_opt'] = (bool) isset($input['refuse_opt']);
            $input['revoke_cookies'] = isset($input['revoke_cookies']);
            $input['revoke_cookies_opt'] = isset($input['revoke_cookies_opt']) && array_key_exists($input['revoke_cookies_opt'], $this->RdCookieNotice->revoke_opts) ? $input['revoke_cookies_opt'] : $this->RdCookieNotice->defaults['general']['revoke_cookies_opt'];

            // get allowed HTML
            $allowed_html = $this->RdCookieNotice->HTML->getAllowedHTML();

            // body refuse code
            $input['refuse_code'] = wp_kses(isset($input['refuse_code']) && $input['refuse_code'] !== '' ? $input['refuse_code'] : $this->RdCookieNotice->defaults['general']['refuse_code'], $allowed_html);
            // head refuse code
            $input['refuse_code_head'] = wp_kses(isset($input['refuse_code_head']) && $input['refuse_code_head'] !== '' ? $input['refuse_code_head'] : $this->RdCookieNotice->defaults['general']['refuse_code_head'], $allowed_html);
            // css button style
            $input['css_style'] = sanitize_text_field(isset($input['css_style']) && in_array($input['css_style'], array_keys($this->RdCookieNotice->styles)) ? $input['css_style'] : $this->RdCookieNotice->defaults['general']['css_style']);
            // css button class
            $input['css_class'] = sanitize_text_field(isset($input['css_class']) ? $input['css_class'] : $this->RdCookieNotice->defaults['general']['css_class']);
            // link target
            $input['link_target'] = sanitize_text_field(isset($input['link_target']) && in_array($input['link_target'], array_keys($this->RdCookieNotice->link_targets)) ? $input['link_target'] : $this->RdCookieNotice->defaults['general']['link_target']);
            // time
            $input['time'] = sanitize_text_field(isset($input['time']) && in_array($input['time'], array_keys($this->RdCookieNotice->times)) ? $input['time'] : $this->RdCookieNotice->defaults['general']['time']);
            $input['time_rejected'] = sanitize_text_field(isset($input['time_rejected']) && in_array($input['time_rejected'], array_keys($this->RdCookieNotice->times)) ? $input['time_rejected'] : $this->RdCookieNotice->defaults['general']['time_rejected']);
            // script placement
            $input['script_placement'] = sanitize_text_field(isset($input['script_placement']) && in_array($input['script_placement'], array_keys($this->RdCookieNotice->script_placements)) ? $input['script_placement'] : $this->RdCookieNotice->defaults['general']['script_placement']);
            // hide effect
            $input['hide_effect'] = sanitize_text_field(isset($input['hide_effect']) && in_array($input['hide_effect'], array_keys($this->RdCookieNotice->effects)) ? $input['hide_effect'] : $this->RdCookieNotice->defaults['general']['hide_effect']);
            // redirection
            $input['redirection'] = isset($input['redirection']);
            // on scroll
            $input['on_scroll'] = isset($input['on_scroll']);
            // on scroll offset
            $input['on_scroll_offset'] = absint(isset($input['on_scroll_offset']) && $input['on_scroll_offset'] !== '' ? $input['on_scroll_offset'] : $this->RdCookieNotice->defaults['general']['on_scroll_offset']);
            // on click
            $input['on_click'] = isset($input['on_click']);
            // deactivation
            $input['deactivation_delete'] = isset($input['deactivation_delete']);

            // privacy policy
            $input['see_more'] = isset($input['see_more']);
            $input['see_more_opt']['text'] = sanitize_text_field(isset($input['see_more_opt']['text']) && $input['see_more_opt']['text'] !== '' ? $input['see_more_opt']['text'] : $this->RdCookieNotice->defaults['general']['see_more_opt']['text']);
            $input['see_more_opt']['link_type'] = sanitize_text_field(isset($input['see_more_opt']['link_type']) && in_array($input['see_more_opt']['link_type'], array_keys($this->RdCookieNotice->links)) ? $input['see_more_opt']['link_type'] : $this->RdCookieNotice->defaults['general']['see_more_opt']['link_type']);

            if ($input['see_more_opt']['link_type'] === 'custom') {
                $input['see_more_opt']['link'] = ($input['see_more'] === true ? esc_url($input['see_more_opt']['link']) : 'empty');
            } elseif ($input['see_more_opt']['link_type'] === 'page') {
                $input['see_more_opt']['id'] = ($input['see_more'] === true ? (int) $input['see_more_opt']['id'] : 0);
                $input['see_more_opt']['sync'] = isset($input['see_more_opt']['sync']);

                if ($input['see_more_opt']['sync']) {
                    update_option('wp_page_for_privacy_policy', $input['see_more_opt']['id']);
                }
            }

            // policy link position
            $input['link_position'] = sanitize_text_field(isset($input['link_position']) && in_array($input['link_position'], array_keys($this->RdCookieNotice->link_positions)) ? $input['link_position'] : $this->RdCookieNotice->defaults['general']['link_position']);
            // message link position?
            if ($input['see_more'] === true && $input['link_position'] === 'message' && strpos($input['message_text'], '[cookies_policy_link') === false) {
                $input['message_text'] .= ' [cookies_policy_link]';
            }

            $input['translate'] = false;

            $WPML = new \RdCookieNotice\WPML($this->RdCookieNotice);
            $WPML->updateWPMLStrings($input);
            unset($WPML);
        } elseif (isset($_POST['reset_cookie_notice_options'])) {
            $input = $this->RdCookieNotice->defaults['general'];
            add_settings_error('reset_cookie_notice_options', 'reset_cookie_notice_options', __('Settings restored to defaults.', 'rd-cookie-notice'), 'updated');
        }

        return $input;
    }// validateOptions


}
