<?php
/**
 * @license http://opensource.org/licenses/MIT MIT
 * @package rd-cookie-notice
 */


namespace RdCookieNotice;


/**
 * HTML class.
 * 
 * @since 0.2.0
 */
class HTML
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
     * Add new body classes.
     * 
     * @param string|array $classes Space-separated string or array of class names to add to the class list.
     * @return mixed
     */
    public function changeBodyClasses($classes)
    {
        if (is_admin()) {
            return $classes;
        }

        $newClassValues = [];
        $newClassValues[] = 'rd-cookie-notice';
        if ($this->RdCookieNotice->cookies_set()) {
            $newClassValues[] = 'cookies-set';

            if ($this->RdCookieNotice->cookies_accepted()) {
                $newClassValues[] = 'cookies-accepted';
            } else {
                $newClassValues[] = 'cookies-refused';
            }
        } else {
                $newClassValues[] = 'cookies-not-set';
        }

        if (is_array($classes)) {
            array_push($classes, ...$newClassValues);
        } else {
            $classes .= ' ' . implode(' ', $newClassValues);
        }
        unset($newClassValues);

        return $classes;
    }// changeBodyClasses


    /**
     * Display cookie notice element.
     */
    public function displayCookieNotice()
    {
        if (is_admin() || is_blog_admin() || is_network_admin() ||
            (
                defined('IFRAME_REQUEST') &&
                IFRAME_REQUEST === true
            )
        ) {
            return null;
        }

        $WPML = new WPML($this->RdCookieNotice);
        $WPML->setOptionsDisplayCookies();
        unset($WPML);

        // get cookie container args
        $options = apply_filters('cn_cookie_notice_args', [
            'position' => $this->RdCookieNotice->options['general']['position'],
            'css_style' => $this->RdCookieNotice->options['general']['css_style'],
            'css_class' => $this->RdCookieNotice->options['general']['css_class'],
            'button_class' => 'cn-button',
            'colors' => $this->RdCookieNotice->options['general']['colors'],
            'message_text' => $this->RdCookieNotice->options['general']['message_text'],
            'accept_text' => $this->RdCookieNotice->options['general']['accept_text'],
            'refuse_text' => $this->RdCookieNotice->options['general']['refuse_text'],
            'revoke_message_text' => $this->RdCookieNotice->options['general']['revoke_message_text'],
            'revoke_text' => $this->RdCookieNotice->options['general']['revoke_text'],
            'refuse_opt' => $this->RdCookieNotice->options['general']['refuse_opt'],
            'revoke_cookies' => $this->RdCookieNotice->options['general']['revoke_cookies'],
            'see_more' => $this->RdCookieNotice->options['general']['see_more'],
            'see_more_opt' => $this->RdCookieNotice->options['general']['see_more_opt'],
            'link_target' => $this->RdCookieNotice->options['general']['link_target'],
            'link_position' => $this->RdCookieNotice->options['general']['link_position'],
            'aria_label' => __('Rd Cookie Notice', 'rd-cookie-notice')
        ]);

        // check legacy parameters
        $options = $this->RdCookieNotice->SettingsProcess->checkLegacyParams($options, ['refuse_opt', 'see_more']);

        if ($options['see_more'] === true) {
            $options['message_text'] = do_shortcode(wp_kses_post($options['message_text']));
        } else {
            $options['message_text'] = wp_kses_post($options['message_text']);
        }

        $options['css_class'] = esc_attr($options['css_class']);

        // message output
        $output = '
        <!-- Rd Cookie Notice plugin v' . RDCN_VERSION . ' https://github.com/Rundiz-WP/rd-cookie-notice -->
        <div id="cookie-notice" role="banner" class="cookie-notice-hidden cookie-revoke-hidden cn-position-' . $options['position'] . '" aria-label="' . $options['aria_label'] . '" data-nosnippet="data-nosnippet" style="background-color: rgba(' . implode(',', $this->hex2rgb($options['colors']['bar'])) . ',' . $options['colors']['bar_opacity'] * 0.01 . ');">'
                . '<div class="cookie-notice-container" style="color: ' . $options['colors']['text'] . ';">'
                . '<span id="cn-notice-text" class="cn-text-container">' . $options['message_text'] . '</span>'
                . '<span id="cn-notice-buttons" class="cn-buttons-container"><a href="#" id="cn-accept-cookie" data-cookie-set="accept" class="cn-set-cookie ' . $options['button_class'] . ($options['css_style'] !== 'none' ? ' ' . $options['css_style'] : '') . ($options['css_class'] !== '' ? ' ' . $options['css_class'] : '') . '" aria-label="' . $options['accept_text'] . '">' . $options['accept_text'] . '</a>'
                . ($options['refuse_opt'] === true ? '<a href="#" id="cn-refuse-cookie" data-cookie-set="refuse" class="cn-set-cookie ' . $options['button_class'] . ($options['css_style'] !== 'none' ? ' ' . $options['css_style'] : '') . ($options['css_class'] !== '' ? ' ' . $options['css_class'] : '') . '" aria-label="' . $options['refuse_text'] . '">' . $options['refuse_text'] . '</a>' : '')
                . ($options['see_more'] === true && $options['link_position'] === 'banner' ? '<a href="' . ($options['see_more_opt']['link_type'] === 'custom' ? $options['see_more_opt']['link'] : get_permalink($options['see_more_opt']['id'])) . '" target="' . $options['link_target'] . '" id="cn-more-info" class="cn-more-info ' . $options['button_class'] . ($options['css_style'] !== 'none' ? ' ' . $options['css_style'] : '') . ($options['css_class'] !== '' ? ' ' . $options['css_class'] : '') . '" aria-label="' . $options['see_more_opt']['text'] . '">' . $options['see_more_opt']['text'] . '</a>' : '')
                . '</span><a href="javascript:void(0);" id="cn-close-notice" data-cookie-set="accept" class="cn-close-icon" aria-label="' . $options['accept_text'] . '"></a>'
                . '</div>
            ' . ( $options['refuse_opt'] === true && $options['revoke_cookies'] == true ?
                '<div class="cookie-revoke-container" style="color: ' . $options['colors']['text'] . ';">'
                . (!empty($options['revoke_message_text']) ? '<span id="cn-revoke-text" class="cn-text-container">' . $options['revoke_message_text'] . '</span>' : '' )
                . '<span id="cn-revoke-buttons" class="cn-buttons-container"><a href="#" class="cn-revoke-cookie ' . $options['button_class'] . ($options['css_style'] !== 'none' ? ' ' . $options['css_style'] : '') . ($options['css_class'] !== '' ? ' ' . $options['css_class'] : '') . '" aria-label="' . $options['revoke_text'] . '">' . esc_html($options['revoke_text']) . '</a></span>
            </div>' : '' ) . '
        </div>
        <!-- / Rd Cookie Notice plugin -->';

        echo apply_filters('cn_cookie_notice_output', $output, $options);
        unset($options, $output);
    }// displayCookieNotice


    /**
     * Get allowed script blocking HTML.
     * 
     * @return array
     */
    public function getAllowedHTML(): array
    {
        return apply_filters(
            'cn_refuse_code_allowed_html',
            array_merge(
                wp_kses_allowed_html('post'),
                [
                    'script' => [
                        'type' => [],
                        'src' => [],
                        'charset' => [],
                        'async' => []
                    ],
                    'noscript' => [],
                    'style' => [
                        'type' => []
                    ],
                    'iframe' => [
                        'src' => [],
                        'height' => [],
                        'width' => [],
                        'frameborder' => [],
                        'allowfullscreen' => []
                    ]
                ]
            )
        );
    }// getAllowedHTML


    /**
     * Helper: convert hex color to RGB color.
     * 
     * @param string $color Input hex color.
     * @return array Return indexed array where 0 is Red, 1 is Green, 2 is Blue.
     */
    public function hex2rgb(string $color): array
    {
        if ($color[0] == '#') {
            $color = substr($color, 1);
        }

        if (strlen($color) == 6) {
            list($r, $g, $b) = [$color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5]];
        } elseif (strlen($color) == 3) {
            list($r, $g, $b) = [$color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2]];
        } else {
            return false;
        }

        $r = hexdec($r);
        $g = hexdec($g);
        $b = hexdec($b);

        return [$r, $g, $b];
    }// hex2rgb


    /**
     * Print non functional JavaScript in body.
     * 
     * Runs only user accepted cookie consent.
     */
    public function printFooterScripts()
    {
        if (is_admin()) {
            return null;
        }

        if ($this->RdCookieNotice->cookies_accepted()) {
            $scripts = apply_filters(
                'cn_refuse_code_scripts_html', 
                html_entity_decode(
                    trim(
                        wp_kses(
                            $this->RdCookieNotice->options['general']['refuse_code'], 
                            $this->getAllowedHTML()
                        )
                    )
                )
            );

            if (!empty($scripts)) {
                echo $scripts;
            }

            unset($scripts);
        }
    }// printFooterScripts


    /**
     * Print non functional JavaScript in header.
     * 
     * Runs only user accepted cookie consent.
     */
    public function printHeaderScripts()
    {
        if (is_admin()) {
            return null;
        }

        if ($this->RdCookieNotice->cookies_accepted()) {
            $scripts = apply_filters(
                'cn_refuse_code_scripts_html', 
                html_entity_decode(
                    trim(
                        wp_kses(
                            $this->RdCookieNotice->options['general']['refuse_code_head'], 
                            $this->getAllowedHTML()
                        )
                    )
                )
            );

            if (!empty($scripts)) {
                echo $scripts;
            }

            unset($scripts);
        }
    }// printHeaderScripts


}
