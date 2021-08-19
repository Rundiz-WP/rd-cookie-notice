<?php
/**
 * @license http://opensource.org/licenses/MIT MIT
 * @package rd-cookie-notice
 */


namespace RdCookieNotice;


/**
 * Shortcode class.
 * 
 * @since 0.1.1
 */
class Shortcode
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
     * Add multiple shortcodes at once on front-end pages.
     */
    public function addShortcodes()
    {
        if (!is_admin()) {
            add_shortcode('cookies_accepted', array($this, 'cookies_accepted_shortcode'));
            add_shortcode('cookies_revoke', array($this, 'cookies_revoke_shortcode'));
            add_shortcode('cookies_policy_link', array($this, 'cookies_policy_link_shortcode'));
        }
    }// addShortcodes


    /**
     * Register cookies accepted shortcode.
     * 
     * Usage: `[cookies_accepted_shortcode]Hooray you accepted cookie![/cookies_accepted_shortcode]`.
     *
     * @param array $args
     * @param mixed $content
     * @return mixed
     */
   public function cookies_accepted_shortcode($args, $content) {
        if ($this->RdCookieNotice->cookies_accepted()) {
            $scripts = html_entity_decode(trim(wp_kses($content, $this->RdCookieNotice->HTML->getAllowedHTML())));

            if (!empty($scripts)) {
                if (preg_match_all('/' . get_shortcode_regex() . '/', $content)) {
                    $scripts = do_shortcode($scripts);
                }
                return $scripts;
            }
        }

        return '';
   }// cookies_accepted_shortcode


    /**
     * Register cookies policy link shortcode.
     * 
     * Usage: `[cookies_policy_link_shortcode]`.
     *
     * @param array $args
     * @param string $content
     * @return string
     */
   public function cookies_policy_link_shortcode($args, $content) {
        // get options
        $options = $this->RdCookieNotice->options['general'];

        // defaults
        $defaults = [
            'title' => esc_html($options['see_more_opt']['text'] !== '' ? $options['see_more_opt']['text'] : '&#x279c;'),
            'link' => ($options['see_more_opt']['link_type'] === 'custom' ? $options['see_more_opt']['link'] : get_permalink($options['see_more_opt']['id'])),
            'class' => $options['css_class'],
        ];

        // combine shortcode arguments
        $args = shortcode_atts($defaults, $args);

        $shortcode = '<a id="cn-more-info"' .
            ' class="cn-privacy-policy-link cn-link' . ($args['class'] !== '' ? ' ' . $args['class'] : '') . '"' .
            ' href="' . $args['link'] . '"' .
            ' target="' . $options['link_target'] . '"' .
            '>' .
            esc_html($args['title']) .
            '</a>';

        return $shortcode;
    }// cookies_policy_link_shortcode


   /**
     * Register cookies accepted shortcode.
    * 
    * Usage: `[cookies_revoke_shortcode]`.
     *
     * @param array $args
     * @param mixed $content
     * @return mixed
     */
   public function cookies_revoke_shortcode($args, $content) {
        // get options
        $options = $this->RdCookieNotice->options['general'];

        // defaults
        $defaults = array(
            'title' => $options['revoke_text'],
            'class' => $options['css_class']
        );

        // combine shortcode arguments
        $args = shortcode_atts($defaults, $args);

        // escape class(es)
        $args['class'] = esc_attr($args['class']);

        $shortcode = '<a href="#" class="cn-revoke-cookie cn-button cn-revoke-inline' . 
            ($options['css_style'] !== 'none' ? ' ' . $options['css_style'] : '') . 
            ($args['class'] !== '' ? ' ' . $args['class'] : '') . 
            '"' .
            ' title="' . esc_html($args['title']) . '"' .
            '>' . 
            esc_html($args['title']) . 
            '</a>';

        return $shortcode;
    }// cookies_revoke_shortcode


}
