# WordPress Arabic Posts Styling

A plugin to add custom style and fonts for Arabic posts

## Usage ##
* Upload `arabic-post-style` folder to your `plugins` directory
* Go to WordPress Dashboard > Plugins > Activate the plugin
* Go to a post or page to manage the new settings

## Snapshots ###
- [Post Settings](http://nabeel.molham.me/blog/wp-content/uploads/2014/06/post-settings.png)
- [Fonts list](http://nabeel.molham.me/blog/wp-content/uploads/2014/06/fonts-list.png)
- [Changes Example](http://nabeel.molham.me/blog/wp-content/uploads/2014/06/example.png)
- [CSS Output Example](http://nabeel.molham.me/blog/wp-content/uploads/2014/06/css-example.png)

## WP Hooks ##

### Actions ###
- `arabic_post_style_loaded`
	- plugin loaded

### Filters ###
- `arps_fonts_list`
	- parameters: `$list`
    - Array if fonts list
- `arps_posts_styles`
	- parameters: `$final_styles`
    - The final css added to the page header using `wp_head` action hook
- `arps_meta_box_post_types`
	- parameters: `$post_types`
    - List of post types to add Arabic settings meta box to, default `[ 'page', 'post' ]`
- `arps_new_settings`
	- parameters: `$new_settings` , `$post_id`
    - Styling settings for that `$post_id` to save
    - if this filter returned `(boolean) false` settings won't be saved
- `arps_post_settings`
	- parameters: `$settings` , `$post_id`
    - Retrieved styling settings for that `$post_id`
- `arps_is_arabic_post`
	- parameters: `$is_arabic` , `$post_id`
	- If this post `$post_id` is Arabic post `true` or not `false`

**Contact if there are any problems**

Hope you find it helpful :)

License: GNU General Public License, version 2, http://www.gnu.org/licenses/gpl-2.0.html
