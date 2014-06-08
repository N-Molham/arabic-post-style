<?php
/*
Plugin Name: Arabic Posts Styling
Plugin URI: http://nabeel.molham.me/wp-plugins/arabic-post-style
Description: Add custom style for Arabic posts
Version: 1.0
Author: Nabeel Molham
Author URI: http://nabeel.molham.me
Text Domain: arabic-post-style
Domain Path: /languages
License: GNU General Public License, version 2, http://www.gnu.org/licenses/gpl-2.0.html
*/

/**
 * Library physical path
 */
define( 'ARPS_DIR', plugin_dir_path( __FILE__ ) );

/**
 * library URI
 */
define( 'ARPS_URI', plugin_dir_url( __FILE__ ) );

/**
 * language text domain
 */
define( 'ARPS_TEXT_DOMAIN', 'arabic-post-style' );

/**
 * language files directory
 */
define( 'ARPS_LANG_DIR', ARPS_DIR . 'languages/' );

class Arabic_Post_Style
{
	/**
	 * Arabic fonts list
	 * 
	 * @var array
	 */
	protected $fonts;

	/**
	 * Settings meta key name
	 * 
	 * @var string
	 */
	const SETTINGS_META_KEY = '_arps_settings';

	/**
	 * Cached post settings
	 * 
	 * @var array
	 */
	protected static $post_settings = array();

	/**
	 * Cached styling key
	 * 
	 * @var string
	 */
	const CHACHE_KEY = 'arps';

	/**
	 * Constructor
	 * 
	 * @return void
	 */
	function __construct()
	{
		// plug-in loaded
		do_action( 'arabic_post_style_loaded' );

		// Initialization
		add_action( 'init', array( &$this, 'init' ) );

		// Language file loading hook
		add_action( 'plugins_loaded', array( &$this, 'load_language' ) );
	}

	/**
	 * Initialization
	 * 
	 * @return void
	 */
	public function init()
	{
		// setup fonts list
		$this->fonts = apply_filters( 'arps_fonts_list', array ( 
				'jozoor' => array ( 
						'name' => 'Jozoor',
						'url' => 'http://fonts.jozoor.com/jozoor-font/css/font.css',
						'family' => "'AraJozoor-Regular', Sans-Serif",
						'license' => 'Copyrights <a href="http://jozoor.com/" target="_blank">Jozoor Team</a>, License <a target="_blank" href="http://creativecommons.org/licenses/by-sa/3.0/">Creative Commons &mdash; Attribution-ShareAlike 3.0 Unported</a>',
				),
				'jf-flat' => array ( 
						'name' => 'JF Flat',
						'url' => ARPS_URI .'css/jf-flat.css',
						'family' => "'JF Flat', Arial, sans-serif",
						'license' => 'Copyrights <a target="_blank" href="http://jozoor.com/">Jozoor Team</a>, License <a target="_blank" href="http://scripts.sil.org/OFL">OFL (SIL Open Font License)</a>',
				),
				'amiri' => array ( 
						'name' => 'Amiri',
						'url' => '//fonts.googleapis.com/earlyaccess/amiri.css',
						'family' => "'Amiri', serif",
						'license' => '<a target="_blank" href="http://themes.googleusercontent.com/static/fonts/earlyaccess/amiri/OFL.txt">SIL Open Font License, 1.1</a>',
				),
				'droidarabickufi' => array ( 
						'name' => 'Droid Arabic Kufi',
						'url' => '//fonts.googleapis.com/earlyaccess/droidarabickufi.css',
						'family' => "'Droid Arabic Kufi', serif",
						'license' => '<a target="_blank" href="http://themes.googleusercontent.com/static/fonts/earlyaccess/droidarabickufi/LICENSE.txt">Apache License, version 2.0</a>',
				),
				'droidarabicnaskh' => array ( 
						'name' => 'Droid Arabic Naskh',
						'url' => '//fonts.googleapis.com/earlyaccess/droidarabicnaskh.css',
						'family' => "'Droid Arabic Naskh', serif",
						'license' => '<a target="_blank" target="_blank" href="http://themes.googleusercontent.com/static/fonts/earlyaccess/droidarabicnaskh/LICENSE.txt">Apache License, version 2.0</a>',
				),
				'lateef' => array ( 
						'name' => 'Lateef',
						'url' => '//fonts.googleapis.com/earlyaccess/lateef.css',
						'family' => "'Lateef', serif",
						'license' => '<a target="_blank" href="http://themes.googleusercontent.com/static/fonts/earlyaccess/lateef/OFL.txt">SIL Open Font License, 1.1</a>',
				),
				'thabit' => array ( 
						'name' => 'Thabit',
						'url' => '//fonts.googleapis.com/earlyaccess/thabit.css',
						'family' => "'Thabit', serif",
						'license' => '<a target="_blank" href="http://themes.googleusercontent.com/static/fonts/earlyaccess/thabit/OFL.txt">SIL Open Font License, 1.1</a>',
				),
				'scheherazade' => array ( 
						'name' => 'Scheherazade',
						'url' => 'http://openfontlibrary.org/face/scheherazade',
						'family' => "'Scheherazade', sans-serif",
						'license' => '<a target="_blank" href="OFL (SIL Open Font License)">OFL (SIL Open Font License)</a>',
				),
		) );

		// meta box hook
		add_action( 'add_meta_boxes', array( &$this, 'register_meta_box' ) );

		// after saving post
		add_action( 'save_post', array( &$this, 'save_styling_settings' ) );

		// language attributes filter hook
		add_filter( 'language_attributes', array( &$this, 'change_doc_lang_attrs' ) );

		// theme header hook
		add_action( 'wp_head', array( &$this, 'post_styling_load' ), 15 );

		// post article wrapper class
		add_filter( 'post_class', array( &$this, 'post_class_filter' ), 10, 3 );
	}

	/**
	 * Override HTML lang attribute
	 * 
	 * @param string $org_attributes
	 * @return string
	 */
	public function change_doc_lang_attrs( $org_attributes )
	{
		$attributes = array();

		if ( is_singular() && self::is_arabic_post() ) {
			$attributes[] = 'lang="ar-EG"';

			if ( function_exists( 'is_rtl' ) && is_rtl() )
				$attributes[] = 'dir="rtl"';
		}

		return empty( $attributes ) ? $org_attributes : implode( ' ', $attributes );
	}

	/**
	 * Load post styling if it is a Arabic post
	 * 
	 * @return void
	 */
	public function post_styling_load()
	{
		global $wp_query;

		// check posts
		if ( empty( $wp_query->posts ) )
			return;

		// specific posts styling
		$posts_styling = '';

		for ( $i = 0, $len = count( $wp_query->posts ); $i < $len; $i++ )
		{
			if ( !isset( $wp_query->posts[$i] ) )
				continue;

			$post = &$wp_query->posts[$i];

			// get settings
			$settings = self::get_post_settings( $post->ID );

			// check if Arabic post
			if ( !$settings['is_arabic'] )
				continue;

			$font = $this->fonts[ $settings['font'] ];

			// enqueue font files
			if ( isset( $this->fonts[ $settings['font'] ] ) )
				wp_enqueue_style( 'arps-font-'. $settings['font'], $font['url'] );

			// override post title and content font family & extra once
			$posts_styling .= '#post-'. $post->ID .' .entry-title, #post-'. $post->ID .' .entry-content { font-family: '. $font['family'] .'; }' . "\n";
			$posts_styling .= $settings['extra'] ."\n";
		}

		// styles start
		$final_styles = '<style type="text/css" media="screen">' ."\n";

		// global
		$final_styles .= '.arabic-post .entry-title, .arabic-post .entry-content { direction: rtl; }' ."\n";

		// each post styling
		$final_styles .= $posts_styling;

		// styles end
		$final_styles .= '</style>';

		echo apply_filters( 'arps_posts_styles', $final_styles );
	}

	/**
	 * Override post class list if it is a Arabic post
	 * 
	 * @param array $classes
	 * @param string $class
	 * @param integer $post_id
	 * @return array
	 */
	public function post_class_filter( $classes, $class, $post_id )
	{
		// check if Arabic post
		if ( self::is_arabic_post( $post_id ) && !in_array( '', $classes ) )
		{
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
	public function register_meta_box( $post_type )
	{
		$allowed_post_types = apply_filters( 'arps_meta_box_post_types', array( 'page', 'post' ) );

		if ( in_array( $post_type, $allowed_post_types ) )
			add_meta_box( 'arps_style', __( 'Arabic Styling', ARPS_TEXT_DOMAIN ), array( &$this, 'styling_meta_box' ), $post_type, 'normal', 'high' );
	}

	/**
	 * Save styling settings
	 * 
	 * @param integer $post_id
	 * @return void
	 */
	public function save_styling_settings( $post_id )
	{
		if ( !current_user_can( 'publish_posts' ) || !isset( $_POST['arps'] ) || !is_array( $_POST['arps'] ) )
			return;

		// sanitize values
		$new_settings = array_map( array( &$this, 'sanitize_text_field' ), $_POST['arps'] );

		// Arabic post
		$new_settings['is_arabic'] = isset( $new_settings['is_arabic'] ) && 'yes' === $new_settings['is_arabic'];

		// font family
		$new_settings['font'] = isset( $this->fonts[ $new_settings['font'] ] ) ? $new_settings['font'] : '';

		// filtered
		$new_settings = apply_filters( 'arps_new_settings', $new_settings, $post_id );
		if ( false === $new_settings )
			return;

		// save data
		update_post_meta( $post_id, self::SETTINGS_META_KEY, $new_settings );
	}

	/**
	 * Styling options meta box
	 * 
	 * @param WP_Post $post
	 * @param array $meta_box
	 * @return void
	 */
	public function styling_meta_box( $post, $meta_box )
	{
		$settings = self::get_post_settings( $post->ID );

		?>
<table class="form-table">
	<tbody>
		<tr>
			<th scope="row"><label for="arps_arabic"><?php _e( 'Is Arabic Post', ARPS_TEXT_DOMAIN ); ?></label></th>
			<td>
				<fieldset>
					<legend class="screen-reader-text"><span><?php _e( 'Arabic Post', ARPS_TEXT_DOMAIN ); ?></span></legend>
					<label for="arps_arabic">
						<input name="arps[is_arabic]" type="checkbox" id="arps_arabic" value="yes" <?php checked( $settings['is_arabic'] ); ?>/>
						<?php _e( 'Yes', ARPS_TEXT_DOMAIN ); ?>
					</label>
				</fieldset>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="arps_font"><?php _e( 'Font Family', ARPS_TEXT_DOMAIN ); ?></label></th>
			<td>
				<select name="arps[font]" id="arps_font"><option value=''>-</option><?php 
				foreach ( $this->fonts as $font_name => $font_info )
				{
					echo '<option value="', $font_name ,'"';
					echo $font_name === $settings['font'] ? ' selected' : '';
					echo '>', $font_info['name'] ,'</option>';
				}
				?>
				</select>
				<span id="utc-time"><abbr><?php _e( 'Copyrights &amp; License' ); ?></abbr> <code>-</code></span>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="arps_extra_css"><?php _e( 'Additional CSS', ARPS_TEXT_DOMAIN ); ?></label></th>
			<td>
				<textarea name="arps[extra]" id="arps_extra_css" cols="30" rows="12" class="large-text code" style="resize: none;"><?php echo $settings['extra']; ?></textarea>
				<p class="description"><?php printf( __( 'You can target this post title with <code>%s</code> selector and content with <code>%s</code> selector', ARPS_TEXT_DOMAIN ), '#post-'. $post->ID .' .entry-title', '#post-'. $post->ID .' .entry-content' ); ?></p>
			</td>
		</tr>
	</tbody>
</table>
		<?php

		// enqueues
		wp_enqueue_script( 'arps-post-settings', ARPS_URI .'js/post.js', array( 'jquery' ), false, true );
		wp_localize_script( 'arps-post-settings', 'arps', array ( 
				'fonts' => $this->fonts, 
		) );
	}

	/**
	 * Get post styling settings
	 * 
	 * @param integer $post_id
	 * @return array
	 */
	public static function get_post_settings( $post_id )
	{
		// check cached
		if ( isset( self::$post_settings[$post_id] ) )
			return apply_filters( 'arps_post_settings', self::$post_settings[$post_id], $post_id );

		// defaults
		$settings = wp_parse_args( get_post_meta( $post_id, self::SETTINGS_META_KEY, true ), array (
				'is_arabic' => false,
				'font' => '',
				'extra' => "#post-{$post_id} .entry-title {  }\n#post-{$post_id} .entry-content {  }",
		) );

		// cache settings
		self::$post_settings[$post_id] = $settings;

		// return filtered
		return apply_filters( 'arps_post_settings', $settings, $post_id );
	}

	/**
	 * Check is post is Arabic
	 * 
	 * @param integer $post_id
	 * @return boolean
	 */
	public static function is_arabic_post( $post_id = '' )
	{
		if ( '' === $post_id || empty( $post_id ) )
			$post_id = get_post()->ID;

		$settings = self::get_post_settings( $post_id );

		// return filtered
		return apply_filters( 'arps_is_arabic_post', $settings['is_arabic'], $post_id );
	}

	/**
	 * Modified version of WP's
	 *
	 * check for invalid UTF-8,
	 * Convert single < characters to entity,
	 * strip all tags,
	 * remove line breaks, tabs and extra white space,
	 * strip octets.
	 *
	 * @since 2.9.0
	 *
	 * @param string $str
	 * @return string
	 */
	public function sanitize_text_field( $str ) 
	{
		$filtered = wp_check_invalid_utf8( $str );
	
		if ( strpos( $filtered, '<' ) !== false ) {
			$filtered = wp_pre_kses_less_than( $filtered );
			// This will strip extra whitespace for us.
			$filtered = wp_strip_all_tags( $filtered, true );
		}
	
		$found = false;
		while ( preg_match( '/%[a-f0-9]{2}/i', $filtered, $match ) ) {
			$filtered = str_replace($match[0], '', $filtered);
			$found = true;
		}
	
		if ( $found ) {
			// Strip out the whitespace that may now exist after removing the octets.
			$filtered = trim( preg_replace('/ +/', ' ', $filtered) );
		}
	
		/**
		 * Filter a sanitized text field string.
		 *
		 * @since 2.9.0
		 *
		 * @param string $filtered The sanitized string.
		 * @param string $str      The string prior to being sanitized.
		 */
		return apply_filters( 'sanitize_text_field', $filtered, $str );
	}

	/**
	 * Load language file
	 * 
	 * @return void
	 */
	public function load_language()
	{
		load_plugin_textdomain( ARPS_TEXT_DOMAIN, false, ARPS_LANG_DIR );
	}
}

$arps = new Arabic_Post_Style();
