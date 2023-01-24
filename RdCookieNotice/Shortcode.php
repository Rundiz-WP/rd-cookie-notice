<?php
/**
 * @license http://opensource.org/licenses/MIT MIT
 * @package rd-cookie-notice
 */


namespace RdCookieNotice;


/**
 * Shortcode class.
 * 
 * @since 0.2.0
 */
class Shortcode
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
     * Add multiple shortcodes at once on front-end pages.
     */
    public function addShortcodes()
    {
        if (!is_admin()) {
            add_shortcode('cookies_accepted', [$this, 'cookiesAcceptedShortcode']);
            add_shortcode('cookies_revoke', [$this, 'cookiesRevokeShortcode']);
            add_shortcode('cookies_policy_link', [$this, 'cookiesPolicyLinkShortcode']);
        }
    }// addShortcodes


    /**
     * Register cookies accepted shortcode.
     * 
     * Usage: `[cookies_accepted]Hooray! you accepted cookie.[/cookies_accepted]`.
     *
     * @param array $args
     * @param mixed $content
     * @return string
     */
    public function cookiesAcceptedShortcode($args, $content): string
    {
        if ($this->RdCookieNotice->cookies_accepted()) {
            $scripts = html_entity_decode(trim(wp_kses($content, $this->RdCookieNotice->HTML->getAllowedHTML())));

            if (!empty($scripts)) {
                if (preg_match_all('/' . get_shortcode_regex() . '/', $content)) {
                    $scripts = do_shortcode($scripts);
                }
                return $scripts;
            }
            unset($scripts);
        }

        return '';
    }// cookiesAcceptedShortcode


    /**
     * Register cookies policy link shortcode.
     * 
     * Usage: `[cookies_policy_link class="myclass (optional)" target="mytarget (optional)" link="mylink (optional)"]`.
     *
     * @param array $args
     * @param string $content
     * @return string
     */
    public function cookiesPolicyLinkShortcode($args, $content): string
    {
        // get options
        $options = $this->RdCookieNotice->options['general'];

        // defaults
        $defaults = [
            'title' => esc_html('' !== $options['see_more_opt']['text'] ? $options['see_more_opt']['text'] : '&#x279c;'),
            'link' => ('custom' === $options['see_more_opt']['link_type'] ? $options['see_more_opt']['link'] : get_permalink($options['see_more_opt']['id'])),
            'class' => $options['css_class'],
        ];

        // combine shortcode arguments
        $args = shortcode_atts($defaults, $args);

        $shortcode = '<a id="cn-more-info"' .
                ' class="cn-privacy-policy-link cn-link' . ('' !== $args['class'] ? ' ' . $args['class'] : '') . '"' .
                ' href="' . $args['link'] . '"' .
                ' target="' . $options['link_target'] . '"' .
                '>' .
                esc_html($args['title']) .
                '</a>';

        unset($defaults, $options);
        return $shortcode;
    }// cookiesPolicyLinkShortcode


    /**
     * Register cookies accepted shortcode.
     * 
     * Usage: `[cookies_revoke title="mytitle (optional)" class="myclass (optional)"]`.
     *
     * @param array $args
     * @param mixed $content
     * @return string
     */
    public function cookiesRevokeShortcode($args, $content): string
    {
        // get options
        $options = $this->RdCookieNotice->options['general'];

        // defaults
        $defaults = [
            'title' => $options['revoke_text'],
            'class' => $options['css_class']
        ];

        // combine shortcode arguments
        $args = shortcode_atts($defaults, $args);

        // escape class(es)
        $args['class'] = esc_attr($args['class']);

        $shortcode = '<a href="#" class="cn-revoke-cookie cn-button cn-revoke-inline' . 
            ('none' !== $options['css_style'] ? ' ' . $options['css_style'] : '') . 
            ('' !== $args['class'] ? ' ' . $args['class'] : '') . 
            '"' .
            ' title="' . esc_html($args['title']) . '"' .
            '>' . 
            esc_html($args['title']) . 
            '</a>';

        unset($defaults, $options);
        return $shortcode;
    }// cookiesRevokeShortcode


}
