<?php
/**
 * Plugin Name: Latest Post Shortcode Slider Extension
 * Plugin URI: http://iuliacazan.ro/latest-post-shortcode-slider-extension/
 * Description: This extension allows you to output the latest posts selection into a dynamic responsive slider (the plugin requires the Latest Post Shortcode plugin version <strong>7.0</strong> or higher).
 * Version: 2.2
 * Author: Iulia Cazan
 * Author URI: https://profiles.wordpress.org/iulia-cazan
 * Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=JJA37EHZXWUTJ
 * License: GPL2
 *
 * Copyright (C) 2015-2017 Iulia Cazan
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

// Define the plugin version.
define( 'LPSS_PLUGIN_VERSION', '2.2' );
define( 'LPSS_PLUGIN_EXTEND_MIN_VER', '7.0' );

/**
 * Class for Latest Post Shortcode Slider.
 */
class Latest_Post_Shortcode_Slider {
	private static $instance;
	var $tile_pattern;
	var $tile_pattern_links;
	var $tile_pattern_nolinks;

	/**
	 * Get active object instance.
	 *
	 * @access public
	 * @static
	 * @return object
	 */
	public static function get_instance() {

		if ( ! self::$instance ) {
			self::$instance = new Latest_Post_Shortcode_Slider();
		}
		return self::$instance;
	}

	/**
	 * Class constructor. Includes constants, includes and init method.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Run action and filter hooks.
	 *
	 * @access private
	 * @return void
	 */
	private function init() {
		if ( is_admin() ) {
			add_action( 'latest_selected_content_slider_configuration', array( $this, 'output_slider_configuration' ) );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
			add_action( 'admin_head', array( $this, 'replace_parent_admin_assets' ), 0 );
		} else {
			add_action( 'latest_selected_content_slider', array( $this, 'output_slider' ), 10, 2 );
			add_action( 'wp_head', array( $this, 'load_assets' ) );
		}
	}

	/**
	 * Latest_Post_Shortcode_Slider::activate_plugin()
	 * The actions to be executed when the plugin is activated
	 */
	function activate_plugin() {
		if ( ! class_exists( 'Latest_Post_Shortcode' ) ) {
			wp_die( 'Please install the Latest Post Shortcode (requied minimum version ' . LPSS_PLUGIN_EXTEND_MIN_VER . ') plugin first, this is an extension.' );
		} else {
			$file = plugin_dir_path( dirname( __FILE__ ) ) . '/latest-post-shortcode/latest-post-shortcode.php';
			$data = get_plugin_data( $file, true, true );
			if (
				empty( $data['Version'] )
				|| ( ! empty( $data['Version'] ) && floatval( LPSS_PLUGIN_EXTEND_MIN_VER ) > floatval( $data['Version'] ) )
			) {
				wp_die( 'The Latest Post Shortcode Slider extension is compatible with Latest Post Shortcode ' . LPSS_PLUGIN_EXTEND_MIN_VER . ' or higher.<br />Your installed version (' . $data['Version'] . ') is not compatible.' );
			}
		}
	}

	/**
	 * Latest_Post_Shortcode_Slider::output_slider_shortcode()
	 */
	function output_slider_configuration() {

		echo '
		<table width="100%" cellspacing="0" cellpadding="2">
			<tr>
				<td>
					<h3>' . __( 'Output', 'lps' ) . '</h3>
				</td>
				<td valign="middle">
					<select name="lps_output" id="lps_output" onchange="lps_preview_configures_shortcode()">
						<option value="">tiles</option>
						<option value="slider">slider</option>
					</select>
				</td>
			</tr>
		</table>
		<hr>
		<div id="lps_display_slider">
			<h3>' . __( 'Slider Settings', 'lps' ) . '</h3>
			<table width="100%" cellspacing="0" cellpadding="2">
				<tr>
					<td class="lps_title_td">' . __( 'Mode', 'lps' ) . '</td>
					<td>
						<select name="lps_slidermode" id="lps_slidermode" onchange="lps_preview_configures_shortcode()">
							<option value="horizontal">horizontal</option>
							<option value="vertical">vertical</option>
							<option value="fade">fade</option>
						</select>
					</td>
				</tr>

				<tr>
					<td class="lps_title_td">' . __( 'Slider Width', 'lps' ) . '</td>
					<td>
						<input type="text" name="lps_sliderwidth" id="lps_sliderwidth" value="" onchange="lps_preview_configures_shortcode()" size="5" /> px (' . __( 'leave empty for auto', 'lps' ) . ')
					</td>
				</tr>

				<tr>
					<td class="lps_title_td">' . __( 'Height', 'lps' ) . '</td>
					<td>
						<select name="lps_sliderheight" id="lps_sliderheight" onchange="lps_preview_configures_shortcode()">
							<option value="true">Adaptive</option>
							<option value="false">Fixed</option>
						</select>
						(' . __( 'this depends on the image size you select', 'lps' ) . ')
					</td>
				</tr>

				<tr>
					<td class="lps_title_td">' . __( 'Controls', 'lps' ) . '</td>
					<td>
						<select name="lps_slidercontrols" id="lps_slidercontrols" onchange="lps_preview_configures_shortcode()">
							<option value="true">true</option>
							<option value="false">false</option>
						</select>
					</td>
				</tr>

				<tr>
					<td class="lps_title_td">' . __( 'Pager', 'lps' ) . '</td>
					<td>
						<select name="lps_sliderpager" id="lps_sliderpager" onchange="lps_preview_configures_shortcode()">
							<option value="true">true</option>
							<option value="false">false</option>
						</select>
					</td>
				</tr>

				<tr>
					<td class="lps_title_td">' . __( 'Auto', 'lps' ) . '</td>
					<td>
						<select name="lps_sliderauto" id="lps_sliderauto" onchange="lps_preview_configures_shortcode()">
							<option value="true">true</option>
							<option value="false">false</option>
						</select>
					</td>
				</tr>

				<tr>
					<td class="lps_title_td">' . __( 'Pause', 'lps' ) . '</td>
					<td>
						<input type="text" name="lps_sliderpause" id="lps_sliderpause" value="" onchange="lps_preview_configures_shortcode()" size="4000" />
						<br />(the amount of time in milliseconds between each auto transition)
					</td>
				</tr>

			</table>
		</div>
		';
	}

	/**
	 * Latest_Post_Shortcode_Slider::output_slider()
	 */
	function output_slider( $posts, $args ) {
		$shortcode_id = md5( serialize( $args ) . microtime() );
		?>
		<div class="latest-post-selection-slider">
			<ul id="bxslider-<?php echo esc_attr( $shortcode_id ); ?>" class="latest-post-selection latest-post-selection-slider">
			<?php
			$linkurl        = ( ! empty( $args['url'] ) && ( 'yes' == $args['url'] || 'yes_blank' == $args['url'] ) ) ? true : false;
			$extra_display  = ( ! empty( $args['display'] ) ) ? explode( ',', $args['display'] ) : array( 'title' );
			$chrlimit       = ( ! empty( $args['chrlimit'] ) ) ? intval( $args['chrlimit'] ) : 120;
			$slidermode     = ( ! empty( $args['slidermode'] ) ) ? $args['slidermode'] : 'horizontal';
			$slidermode     = ( ! empty( $args['slidermode'] ) ) ? $args['slidermode'] : 'horizontal';
			$sliderheight   = ( ! empty( $args['sliderheight'] ) && 'true' == $args['sliderheight'] ) ? 'true' : 'false';
			$slidercontrols = ( ! empty( $args['slidercontrols'] ) && 'true' == $args['slidercontrols'] ) ? 'true' : 'false';
			$sliderpager    = ( ! empty( $args['sliderpager'] ) && 'true' == $args['sliderpager'] ) ? 'true' : 'false';
			$sliderauto     = ( ! empty( $args['sliderauto'] ) && 'true' == $args['sliderauto'] ) ? 'true' : 'false';
			$sliderwidth    = ( ! empty( $args['sliderwidth'] ) ) ? (int) $args['sliderwidth'] : 0;
			$sliderpause    = ( ! empty( $args['sliderpause'] ) ) ? (int) $args['sliderpause'] : 4000;

			$latest_post_phortcode = new Latest_Post_Shortcode();
			foreach ( $posts as $post ) :
				if ( ! empty( $args['image'] ) ) :
					$image = wp_get_attachment_image_src( get_post_thumbnail_id( intval( $post->ID ) ), $args['image'] );
					if ( ! empty( $image[0] ) ) :
						$a_start = '';
						$a_end   = '';
						if ( $linkurl ) {
							$link_target = ( 'yes_blank' == $args['url'] ) ? ' target="_blank"' : '';
							$a_start     = '<a href="' . get_permalink( $post->ID ) . '"' . $link_target . '>';
							$a_end       = '</a>';
						}
						?>
						<li>
							<?php echo $a_start; ?>

							<img src="<?php echo esc_url( $image[0] ); ?>" />
							<div class="overlay">
								<?php if ( in_array( 'title', $extra_display ) ) : ?>
									<h1><?php echo esc_html( $post->post_title ); ?></h1>
								<?php endif; ?>
								<?php
								$text = '';
								if ( in_array( 'excerpt', $extra_display ) || in_array( 'content', $extra_display ) || in_array( 'content-small', $extra_display ) || in_array( 'excerpt-small', $extra_display ) ) :
									if ( in_array( 'excerpt', $extra_display ) ) {
										$text = apply_filters( 'the_excerpt', strip_shortcodes( $post->post_excerpt ) );
									} elseif ( in_array( 'excerpt-small', $extra_display ) ) {
										$text = $latest_post_phortcode->get_short_text( $post->post_excerpt, $chrlimit, true );
									} elseif ( in_array( 'content', $extra_display, true ) ) {
										$text = apply_filters( 'the_content', $post->post_content );
									} elseif ( in_array( 'content-small', $extra_display, true ) ) {
										$text = $latest_post_phortcode->get_short_text( $post->post_content, $chrlimit, false );
									}
									echo $text;
								endif;
								?>
							</div>

							<?php echo $a_end; ?>
						</li>
						<?php
					endif;
				endif;
			endforeach;
			?>
			</ul>
		</div>
		<script>
		jQuery(document).ready(function(){
			var slider<?php echo esc_attr( $shortcode_id ); ?> = jQuery('#bxslider-<?php echo esc_attr( $shortcode_id ); ?>').bxSlider({
				'adaptiveHeight': <?php echo esc_attr( $sliderheight ); ?>,
				'touchEnabled': true,
				'oneToOneTouch': true,
				'controls': <?php echo esc_attr( $slidercontrols ); ?>,
				'pager': <?php echo esc_attr( $sliderpager ); ?>,
				'auto': <?php echo esc_attr( $sliderauto ); ?>,
				'autoStart': <?php echo esc_attr( $sliderauto ); ?>,
				'autoHover': true,
				<?php if ( 'true' == $sliderauto ) : ?>
				'onSlideAfter': function() {
					slider<?php echo esc_attr( $shortcode_id ); ?>.stopAuto();
					slider<?php echo esc_attr( $shortcode_id ); ?>.startAuto();
				},
				<?php endif; ?>
				<?php if ( ! empty( $sliderwidth ) ) : ?>
				'slideWidth': <?php echo esc_attr( $sliderwidth ); ?>,
				<?php endif; ?>
				'pause': <?php echo esc_attr( $sliderpause ); ?>,
				'mode': '<?php echo esc_attr( $slidermode ); ?>',
				'infiniteLoop': true,
			});
		});
		</script>
		<?php
	}

	/**
	 * Latest_Post_Shortcode_Slider::load_assets() Load the front assets
	 */
	function load_assets() {
		wp_enqueue_style( 'lpss-bxslider-css',
			plugins_url( '/assets/third-party/jquery.bxslider/jquery.bxslider.css', __FILE__ ),
			array(),
			LPSS_PLUGIN_VERSION,
			false
		);
		wp_enqueue_style( 'lpss-css',
			plugins_url( '/assets/css/styles.css', __FILE__ ),
			array(),
			LPSS_PLUGIN_VERSION,
			false
		);

		wp_enqueue_script(
			'lpss-bxslider-js',
			plugins_url( '/assets/third-party/jquery.bxslider/jquery.bxslider.min.js', __FILE__ ),
			array( 'jquery' ),
			LPSS_PLUGIN_VERSION,
			true
		);
	}

	/**
	 * Latest_Post_Shortcode_Slider::replace_parent_admin_assets() Load the admin assets
	 */
	function replace_parent_admin_assets() {
		wp_deregister_script( 'lps-admin-shortcode-button' );

		wp_enqueue_script( 'jquery-ui-tabs' );
		wp_enqueue_script(
			'lps-admin-shortcode-button',
			plugins_url( '/assets/js/custom.js', __FILE__ ),
			array( 'jquery', 'jquery-ui-tabs' ),
			LPSS_PLUGIN_VERSION,
			true
		);
	}

	/**
	 * Latest_Post_Shortcode_Slider::plugin_action_links()
	 */
	function plugin_action_links( $links ) {
		$all   = array();
		$all[] = '<a href="http://iuliacazan.ro/latest-post-shortcode-slider">Plugin URL</a>';
		$all   = array_merge( $all, $links );
		return $all;
	}

}

$latest_post_phortcode_slider = Latest_Post_Shortcode_Slider::get_instance();
register_activation_hook( __FILE__, array( $latest_post_phortcode_slider, 'activate_plugin' ) );

/** Allow the text widget to render the Latest Post Shortcode Slider */
add_filter( 'widget_text', 'do_shortcode', 11 );
