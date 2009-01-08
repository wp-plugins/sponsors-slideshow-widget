<?php
/*
Plugin Name: Sponsors Slideshow Widget
Plugin URI: http://wordpress.org/extend/plugins/sponsors-slideshow-widget
Description: Display certain link category as slideshow in sidebar
Version: 1.0
Author: Kolja Schleich

Copyright 2007-2008  Kolja Schleich  (email : kolja.schleich@googlemail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class SponsorsSlideshowWidget
{
	/**
	 * Plugin Version
	 *
	 * @var string
	 */
	private $version = '1.0';
	
	/**
	 * path to the plugin
	 *
	 * @var string
	 */
	private $plugin_url;

	 
	/**
	 * Initialize class
	 *
	 * @param none
	 * @return void
	 */
	public function __construct()
	{
		if ( !defined( 'WP_CONTENT_URL' ) )
			define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
		if ( !defined( 'WP_PLUGIN_URL' ) )
			define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
		
		$this->plugin_url = WP_PLUGIN_URL.'/'.basename(__FILE__, '.php');

		return;
	}

	
	/**
	 * display() - displays Sponsors Slideshow Widget
	 *
	 * Usually this function is invoked by the Wordpress widget system.
	 * However it can also be called manually via sponsors_slideshow_widget_display().
	 *
	 * @param array/string $args
	 * @return null
	 */
	public function display($args)
	{
		$options = get_option( 'sponsors_slideshow_widget' );

		$defaults = array(
			'before_widget' => '<li id="sponsors_slideshow_widget" class="widget '.get_class($this).'_'.__FUNCTION__.'">',
			'after_widget' => '</li>',
			'before_title' => '<h2 class="widgettitle">',
			'after_title' => '</h2>',
			'widget_title' => $options['title'],
			'category' => $options['category']
		);
		
		$args = array_merge( $defaults, $args );
		extract( $args );
			
		$links = get_bookmarks( array('category' => $category) );
		if ( $links ) {
			echo $before_widget . $before_title . $widget_title . $after_title;
			echo '<div id="sponsors_slideshow">';
			foreach ( $links AS $link ) {
				echo '<a href="'.$link->link_url.'" target="_blank" title="'.$link->link_name.'"><img src="'.$link->link_image.'" alt="'.$link->link_name.'" /></a>';
			}
			echo '</div>';
			echo $after_widget;
		}
	}

	
	/**
	 * control() - displays control panel for the widget
	 *
	 * @param none
	 * @return void
	 */
	public function control( )
	{
		global $wpdb;
		$options = get_option( 'sponsors_slideshow_widget' );
		if ( $_POST['sponsors-slideshow-submit'] ) {
			$categories = $wpdb->get_results( "SELECT name FROM $wpdb->terms WHERE `term_id` = {$_POST['sponsors_slideshow_category']}" );
			$options['title'] = wp_specialchars($categories[0]->name);
			$options['category'] = $_POST['sponsors_slideshow_category'];
			$options['width'] = $_POST['sponsors_slideshow_width'];
			$options['height'] = $_POST['sponsors_slideshow_height'];
			$options['time'] = $_POST['sponsors_slideshow_time'];
			$options['fade'] = $_POST['sponsors_slideshow_fade'];
			update_option( 'sponsors_slideshow_widget', $options );
		}
		
		echo '<div id="sponsors_slideshow_control">';
		echo '<p><label for="sponsors_slideshow_category">'.__( 'Links', 'sponsors-slideshow' ).'</label> '.$this->linkCategories($options['category']).'</p>';
		echo '<p><label for="sponsors_slideshow_width">'.__( 'Width', 'sponsors-slideshow' ).'</label><input type="text" size="3" name="sponsors_slideshow_width" id="sponsors_slideshow_width" value="'.$options['width'].'" /> px</p>';
		echo '<p><label for="sponsors_slideshow_height">'.__( 'Height', 'sponsors-slideshow' ).'</label><input type="text" size="3" name="sponsors_slideshow_height" id="sponsors_slideshow_height" value="'.$options['height'].'" /> px</p>';
		echo '<p><label for="sponsors_slideshow_time">'.__( 'Time', 'sponsors-slideshow' ).'</label><input type="text" name="sponsors_slideshow_time" id="sponsors_slideshow_time" size="1" value="'.$options['time'].'" /> sec</p>';
		echo '<p><label for="sponsors_slideshow_fade">'.__( 'Fade Effect', 'sponsors-slideshow' ).'</label>'.$this->fadeEffects($options['fade']).'</p>';
		echo '<input type="hidden" name="sponsors-slideshow-submit" id="sponsors-slideshow-submit" value="1" />';
		echo '</div>';
		
		return;
	}

	
	/**
	 * linkCategories() - display link categories as dropdown list
	 *
	 * @param int $selected
	 * @return string
	 */
	private function linkCategories( $selected )
	{
		$categories = get_terms('link_category', 'orderby=name&hide_empty=0');
	
		if ( empty($categories) )
			return;
	
		$out = '<select size="1" name="sponsors_slideshow_category" id="sponsors_slideshow_category">';
		foreach ( $categories as $category ) {
			$cat_id = $category->term_id;
			$name = wp_specialchars( apply_filters('the_category', $category->name));
			$checked = ( $selected == $cat_id ) ? ' selected="selected"' : '';
			$out .= '<option value="'.$cat_id.'"'.$checked.'> '. $name. '</option>';
		}
		$out .= '</select>';
	
		return $out;
	}
	

	/**
	* fadeEffects() - dropdown list
	*
	* @param string $selected
	* @return string
	*/
	private function fadeEffects( $selected )
	{
		$effects = array(__('Fade','sponsors-slideshow') => 'fade', __('Zoom Fade','sponsors-slideshow') => 'zoomFade', __('Scroll Up','sponsors-slideshow') => 'scrollUp', __('Scroll Left','sponsors-slideshow') => 'scrollLeft', __('Scroll Right','sponsors-slideshow') => 'scrollRight', __('Scroll Down','sponsors-slideshow') => 'scrollDown', __( 'Zoom','sponsors-slideshow') => 'zoom', __('Grow X','sponsors-slideshow') => 'growX', __('Grow Y','sponsors-slideshow') => 'growY', __('Zoom BR','sponsors-slideshow') => 'zoomBR', __('Zoom TL','sponsors-slideshow') => 'zoomTL', __('Random','sponsors-slideshow') => 'random');
		
		$out = '<select size="1" name="sponsors_slideshow_fade" id="sponsors_slideshow_fade">';
		foreach ( $effects AS $name => $effect ) {
			$checked =  ( $selected == $effect ) ? " selected='selected'" : '';
			$out .= '<option value="'.$effect.'"'.$checked.'>'.$name.'</option>';
		}
		$out .= '</select>';
		return $out;
	}
	
	
	/**
	 * register() - registers widget
	 *
	 * @param none
	 * @return void
	 */
	public function register()
	{
		if ( !function_exists("register_sidebar_widget") )
			return;

		register_sidebar_widget( 'Sponsors Slideshow', array(&$this, 'display') );
		register_widget_control( 'Sponsors Slideshow', array(&$this, 'control'), 250, 100 );
		return;
	}
	
	
	/**
	 * activate() - Activate plugin
	 *
	 * @param none
	 * @return void
	 */
	public function activate()
	{		
		$options = array();
		$options['title'] = '';
		$options['version'] = $this->version;
		$options['width'] = 140;
		$options['height'] = 70;
		$options['time'] = 3;
		$options['fade'] = 'fade';
		
		add_option( 'sponsors_slideshow_widget', $options, 'Sponsors Slideshow Widget Options', 'yes' );
		
		return;
	}


	/**
	 * uninstall() - uninstall Sponsors Slideshow Widget
	 *
	 * @param none
	 * @return void
	 */
	public function uninstall()
	{
		delete_option( 'sponsors_slideshow_widget' );
	}


	/**
	 * addHeaderCode() - adds code to Wordpress head
	 *
	 * @param none
	 * @return void
	 */
	public function addHeaderCode()
	{
		$options = get_option('sponsors_slideshow_widget');
		
		echo "<link rel='stylesheet' href='".$this->plugin_url."/style.css' type='text/css' />\n";
		wp_register_script( 'jquery_slideshow', $this->plugin_url.'/js/jquery.aslideshow.js', array('jquery'), '0.5.3' );
		wp_print_scripts( 'jquery_slideshow' );
		
		?>
		<style type="text/css">
			div#sponsors_slideshow {
				width: <?php echo $options['width'] ?>px;
				height: <?php echo $options['height']; ?>px;
			}
		</style>
		<script type='text/javascript'>
		//<![CDATA[
		jQuery(document).ready(function(){
			jQuery('#sponsors_slideshow').slideshow({
				width: <?php echo $options['width'] ?>,
				height:<?php echo $options['height']; ?>,
				time: <?php echo $options['time']*1000; ?>,
				title:false,
				panel:false,
				loop:true,
				play:true,
				imgresize:true,
				playframe: false,
				effect: '<?php echo $options['fade'] ?>',
			});
		});
		//]]>
		</script>
		<?php
	}
	
	
	/**
	 * redefine Links Widget Arguments
	 *
	 * @param $args
	 * @return array
	 */
	 public function widget_links_args( $args )
	 {
		$options = get_option('sponsors_slideshow_widget');
		$args['exclude_category'] = $options['category'];
		return $args;
	 }
}

$sponsors_slideshow_widget = new SponsorsSlideshowWidget();

register_activation_hook(__FILE__, array(&$sponsors_slideshow_widget, 'activate') );
//load_plugin_textdomain( 'sponsors-slideshow', false, basename(__FILE__, '.php').'/languages' );

add_action( 'widgets_init', array(&$sponsors_slideshow_widget, 'register') );
add_action( 'admin_head', array(&$sponsors_slideshow_widget, 'addHeaderCode') );
add_action( 'wp_head', array(&$sponsors_slideshow_widget, 'addHeaderCode') );

add_filter( 'widget_links_args', array($sponsors_slideshow_widget, 'widget_links_args') );

if ( function_exists('register_uninstall_hook') )
	register_uninstall_hook(__FILE__, array(&$sponsors_slideshow_widget, 'uninstall'));


/**
 * Wrapper function to display Sponsors Slideshow Widget statically
 *
 * @param string/array $args
 */
function sponsors_slideshow_widget_display( $args = array() ) {
	global $sponsors_slideshow_widget;
	$sponsors_slideshow_widget->display( $args );
 }
