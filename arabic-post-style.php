<?php
/*
Plugin Name: Arabic Posts Styling
Plugin URI: http://nabeel.molham.me/wp-plugins/arabic-post-style
Description: Add custom style for Arabic posts
Version: 1.1.0
Author: Nabeel Molham
Author URI: http://nabeel.molham.me
Text Domain: arabic-post-style
Domain Path: /languages
License: GNU General Public License, version 2, http://www.gnu.org/licenses/gpl-2.0.html
GitHub Plugin URI: N-Molham/arabic-post-style
GitHub Plugin URI: https://github.com/N-Molham/arabic-post-style
*/

/**
 * Library physical path
 */
define('ARPS_DIR', plugin_dir_path(__FILE__));

/**
 * library URI
 */
define('ARPS_URI', plugin_dir_url(__FILE__));

/**
 * language text domain
 */
const ARPS_TEXT_DOMAIN = 'arabic-post-style';

/**
 * language files directory
 */
const ARPS_LANG_DIR = ARPS_DIR.'languages/';

class Arabic_Post_Style
{
    /**
     * Arabic fonts list
     *
     * @var array
     */
    protected array $fonts;

    /**
     * Settings meta key name
     *
     * @var string
     */
    public const SETTINGS_META_KEY = '_arps_settings';

    /**
     * Cached post settings
     *
     * @var array
     */
    protected static array $postSettings = [];

    protected static ?Arabic_Post_Style $instance = null;

    /**
     * Constructor
     *
     * @return void
     */
    private function __construct()
    {
        // plug-in loaded
        do_action('arabic_post_style_loaded');

        // Initialization
        add_action('init', [$this, 'init']);

        // Language file loading hook
        add_action('plugins_loaded', [$this, 'loadLanguage']);
    }

    /**
     * Initialization
     *
     * @return void
     */
    public function init() : void
    {
        // setup fonts list
        $this->fonts = apply_filters('arps_fonts_list', [
            'jozoor'           => [
                'name'    => 'Jozoor',
                'url'     => 'https://fonts.jozoor.com/jozoor-font/css/font.css',
                'family'  => "'AraJozoor-Regular', Sans-Serif",
                'license' => 'Copyrights <a href="https://jozoor.com/" target="_blank">Jozoor Team</a>, License <a target="_blank" href="https://creativecommons.org/licenses/by-sa/3.0/">Creative Commons &mdash; Attribution-ShareAlike 3.0 Unported</a>',
            ],
            'jf-flat'          => [
                'name'    => 'JF Flat',
                'url'     => ARPS_URI.'css/jf-flat.css',
                'family'  => "'JF Flat', Arial, sans-serif",
                'license' => 'Copyrights <a target="_blank" href="https://jozoor.com/">Jozoor Team</a>, License <a target="_blank" href="https://scripts.sil.org/OFL">OFL (SIL Open Font License)</a>',
            ],
            'amiri'            => [
                'name'    => 'Amiri',
                'url'     => 'https://fonts.googleapis.com/earlyaccess/amiri.css',
                'family'  => "'Amiri', serif",
                'license' => '<a target="_blank" href="https://themes.googleusercontent.com/static/fonts/earlyaccess/amiri/OFL.txt">SIL Open Font License, 1.1</a>',
            ],
            'droidarabickufi'  => [
                'name'    => 'Droid Arabic Kufi',
                'url'     => 'https://fonts.googleapis.com/earlyaccess/droidarabickufi.css',
                'family'  => "'Droid Arabic Kufi', serif",
                'license' => '<a target="_blank" href="https://themes.googleusercontent.com/static/fonts/earlyaccess/droidarabickufi/LICENSE.txt">Apache License, version 2.0</a>',
            ],
            'droidarabicnaskh' => [
                'name'    => 'Droid Arabic Naskh',
                'url'     => 'https://fonts.googleapis.com/earlyaccess/droidarabicnaskh.css',
                'family'  => "'Droid Arabic Naskh', serif",
                'license' => '<a target="_blank" href="https://themes.googleusercontent.com/static/fonts/earlyaccess/droidarabicnaskh/LICENSE.txt">Apache License, version 2.0</a>',
            ],
            'lateef'           => [
                'name'    => 'Lateef',
                'url'     => 'https://fonts.googleapis.com/earlyaccess/lateef.css',
                'family'  => "'Lateef', serif",
                'license' => '<a target="_blank" href="https://themes.googleusercontent.com/static/fonts/earlyaccess/lateef/OFL.txt">SIL Open Font License, 1.1</a>',
            ],
            'thabit'           => [
                'name'    => 'Thabit',
                'url'     => 'https://fonts.googleapis.com/earlyaccess/thabit.css',
                'family'  => "'Thabit', serif",
                'license' => '<a target="_blank" href="https://themes.googleusercontent.com/static/fonts/earlyaccess/thabit/OFL.txt">SIL Open Font License, 1.1</a>',
            ],
            'scheherazade'     => [
                'name'    => 'Scheherazade',
                'url'     => 'https://openfontlibrary.org/face/scheherazade',
                'family'  => "'Scheherazade', sans-serif",
                'license' => 'OFL (SIL Open Font License)',
            ],
        ]);

        // meta box hook
        add_action('add_meta_boxes', [$this, 'registerMetaBox']);

        // after saving post
        add_action('save_post', [$this, 'saveStylingSettings']);

        // language attributes filter hook
        add_filter('language_attributes', [$this, 'changeDocLangAttrs']);

        // theme header hook
        add_action('wp_head', [$this, 'loadPostStyling'], 15);

        // post article wrapper class
        add_filter('body_class', [$this, 'bodyCssClasses'], 20);
    }

    /**
     * Override HTML lang attribute
     *
     * @param string $orgAttributes
     * @return string
     */
    public function changeDocLangAttrs(string $orgAttributes) : string
    {
        $attributes = [];

        if (is_singular() && self::isArabicPost()) {
            $attributes[] = 'lang="ar-EG"';

            if (function_exists('is_rtl') && is_rtl()) {
                $attributes[] = 'dir="rtl"';
            }
        }

        return empty($attributes) ? $orgAttributes : implode(' ', $attributes);
    }

    /**
     * Load post styling if it is a Arabic post
     *
     * @return void
     */
    public function loadPostStyling() : void
    {
        global $wp_query;

        // check posts
        if (empty($wp_query->posts)) {
            return;
        }

        // specific posts styling
        $postStyling = '';

        foreach ($wp_query->posts as $post) {
            // get settings
            $settings = self::getSettings($post->ID);

            // check if Arabic post
            if (! ($settings['is_arabic'] ?? false)) {
                continue;
            }

            if (! $font = $this->fonts[$settings['font']] ?? null) {
                continue;
            }

            wp_enqueue_style('arps-font-'.$settings['font'], $font['url']);

            $postStyling .= "#post-$post->ID .entry-title, 
            .postid-$post->ID .wp-block-post-title, 
            #post-$post->ID .entry-content,
            .postid-$post->ID .wp-block-post-content { font-family: {$font['family']} !important; }\n";

            $postStyling .= $settings['extra']."\n";
        }

        // styles start
        $finalStyles = '<style media="screen">'."\n";

        // global
        $finalStyles .= '.arabic-post .entry-title, .arabic-post .wp-block-post-title, .arabic-post .entry-content { direction: rtl; }'."\n";

        // each post styling
        $finalStyles .= $postStyling;

        // styles end
        $finalStyles .= '</style>';

        echo apply_filters('arps_posts_styles', $finalStyles);
    }

    /**
     * Override post class list if it is a Arabic post
     *
     * @param string[] $classes
     * @return array
     */
    public function bodyCssClasses(array $classes) : array
    {
        // check if Arabic post
        if (self::isArabicPost() && ! in_array('arabic-post', $classes)) {
            // add target class
            $classes[] = 'arabic-post';
        }

        return $classes;
    }

    /**
     * Register meta boxe(s)
     *
     * @param string $post_type
     * @return void
     */
    public function registerMetaBox(string $post_type) : void
    {
        $allowed_post_types = apply_filters('arps_meta_box_post_types', ['page', 'post']);

        if (in_array($post_type, $allowed_post_types, true)) {
            add_meta_box('arps_style', __('Arabic Styling', ARPS_TEXT_DOMAIN), [$this, 'stylingMetaBox'], $post_type, 'normal', 'high');
        }
    }

    /**
     * Save styling settings
     *
     * @param integer $postId
     * @return void
     */
    public function saveStylingSettings(int $postId) : void
    {
        if (! isset($_POST['arps']) || ! is_array($_POST['arps']) || ! current_user_can('publish_posts')) {
            return;
        }

        // sanitize values
        $newSettings = array_map('sanitize_text_field', $_POST['arps']);

        // Arabic post
        $newSettings['is_arabic'] = isset($newSettings['is_arabic']) && 'yes' === $newSettings['is_arabic'];

        // font family
        $newSettings['font'] = isset($this->fonts[$newSettings['font']]) ? $newSettings['font'] : '';

        // filtered
        if (! $newSettings = apply_filters('arps_new_settings', $newSettings, $postId)) {
            return;
        }

        // save data
        update_post_meta($postId, self::SETTINGS_META_KEY, $newSettings);
    }

    /**
     * Styling options meta box
     *
     * @param WP_Post $post
     * @return void
     */
    public function stylingMetaBox(WP_Post $post) : void
    {
        $settings = self::getSettings($post->ID);

        ?>
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row"><label for="arps_arabic"><?php _e('Is Arabic Post', ARPS_TEXT_DOMAIN); ?></label></th>
                <td>
                    <fieldset>
                        <legend class="screen-reader-text"><span><?php _e('Arabic Post', ARPS_TEXT_DOMAIN); ?></span></legend>
                        <label for="arps_arabic">
                            <input name="arps[is_arabic]" type="checkbox" id="arps_arabic" value="yes" <?php checked($settings['is_arabic']); ?>/>
                            <?php _e('Yes', ARPS_TEXT_DOMAIN); ?>
                        </label>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="arps_font"><?php _e('Font Family', ARPS_TEXT_DOMAIN); ?></label></th>
                <td>
                    <select name="arps[font]" id="arps_font">
                        <option value=''>-</option><?php
                        foreach ($this->fonts as $font_name => $font_info) {
                            echo '<option value="', $font_name, '"';
                            echo $font_name === $settings['font'] ? ' selected' : '';
                            echo '>', $font_info['name'], '</option>';
                        }
                        ?>
                    </select>
                    <span id="utc-time"><abbr><?php _e('Copyrights &amp; License'); ?></abbr> <code>-</code></span>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="arps_extra_css"><?php _e('Additional CSS', ARPS_TEXT_DOMAIN); ?></label></th>
                <td>
                    <textarea name="arps[extra]" id="arps_extra_css" cols="30" rows="12" class="large-text code" style="resize: none;"><?php echo $settings['extra']; ?></textarea>
                    <p class="description"><?php printf(__('You can target this post title with <code>%s</code> selector and content with <code>%s</code> selector', ARPS_TEXT_DOMAIN), '#post-'.$post->ID.' .entry-title', '#post-'.$post->ID.' .entry-content'); ?></p>
                </td>
            </tr>
            </tbody>
        </table>
        <?php

        // enqueues
        wp_enqueue_script('arps-post-settings', ARPS_URI.'js/post.js', ['jquery'], false, true);
        wp_localize_script('arps-post-settings', 'arps', [
            'fonts' => $this->fonts,
        ]);
    }

    /**
     * Get post styling settings
     *
     * @param integer $postId
     * @return array
     */
    public static function getSettings(int $postId) : array
    {
        // check cached
        if (isset(self::$postSettings[$postId])) {
            return apply_filters('arps_post_settings', self::$postSettings[$postId], $postId);
        }

        // defaults
        $settings = wp_parse_args(get_post_meta($postId, self::SETTINGS_META_KEY, true), [
            'is_arabic' => false,
            'font'      => '',
            'extra'     => "#post-{$postId} .entry-title, .postid-$postId .wp-block-post-title {  }
            .postid-$postId .wp-block-post-content, #post-$postId .entry-content {  }",
        ]);

        // cache settings
        self::$postSettings[$postId] = $settings;

        // return filtered
        return apply_filters('arps_post_settings', $settings, $postId);
    }

    /**
     * Check is post is Arabic
     *
     * @param integer|string $postId
     * @return boolean
     */
    public static function isArabicPost(int|string $postId = '') : bool
    {
        if (empty($postId)) {
            $postId = get_post()->ID;
        }

        $settings = self::getSettings($postId);

        // return filtered
        return apply_filters('arps_is_arabic_post', $settings['is_arabic'], $postId);
    }

    /**
     * Load language file
     *
     * @return void
     */
    public function loadLanguage() : void
    {
        load_plugin_textdomain(ARPS_TEXT_DOMAIN, false, ARPS_LANG_DIR);
    }

    public static function instance() : self
    {
        return self::$instance ??= new self();
    }
}

Arabic_Post_Style::instance();
