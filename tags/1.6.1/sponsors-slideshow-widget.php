<?php
/*
Plugin Name: Sponsors Slideshow Widget
Author URI: http://kolja.galerie-neander.de/
Plugin URI: http://kolja.galerie-neander.de/plugins/#sponsors-slideshow-widget
Description: Display certain link category as slideshow in sidebar
Version: 1.6.1
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
	var $version = '1.6.1';
	
	/**
	 * path to the plugin
	 *
	 * @var string
	 */
	var $plugin_url;


	/**
	 * prefix of widget
	 * 
	 * @var string
	 */
	var $prefix = 'sponsors-slideshow-widget';
	
	
	/**
	 * Class Constructor
	 *
	 * @param none
	 * @return void
	 */
	function __construct()
	{
		if ( !defined( 'WP_CONTENT_URL' ) )
			define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
		if ( !defined( 'WP_PLUGIN_URL' ) )
			define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
			
		register_activation_hook(__FILE__, array(&$this, 'activate') );
		load_plugin_textdomain( 'sponsors-slideshow', false, basename(__FILE__, '.php').'/languages' );

		add_action( 'widgets_init', array(&$this, 'register') );
		add_action( 'admin_head', array(&$this, 'addHeaderCode') );
		add_action( 'wp_head', array(&$this, 'addHeaderCode') );

		add_filter( 'widget_links_args', array($this, 'widget_links_args') );

		if ( function_exists('register_uninstall_hook') )
			register_uninstall_hook(__FILE__, array(&$this, 'uninstall'));
			
		$this->plugin_url = WP_PLUGIN_URL.'/'.basename(__FILE__, '.php');
	}
	function SponsorsSlideshowWidget()
	{
		$this->__construct();
	}
	
	
	/**
	 * registers widget
	 *
	 * @param none
	 * @return void
	 */
	function register()
	{
		if ( !function_exists("wp_register_sidebar_widget") )
			return;

		$options = get_option('sponsors_slideshow_widget');
		unset($options['version']);

		$name = __('Sponsors Slideshow', 'sponsors-slideshow');
		$widget_ops = array('classname' => 'sponsors_slideshow_widget', 'description' => __('Display specific link category as image slide show', 'sponsors-slideshow') );
		$control_ops = array('width' => 200, 'height' => 200, 'id_base' => $this->prefix);
		
		
		if ( !empty($options)) {
			foreach(array_keys($options) AS $widget_number) {
				wp_register_sidebar_widget($this->prefix.'-'.$widget_number, $name, array(&$this, 'display'), $widget_ops, array('number' => $widget_number));
				wp_register_widget_control($this->prefix.'-'.$widget_number, $name, array(&$this, 'control'), $control_ops, array('number' => $widget_number));
			}
		} else {
			wp_register_sidebar_widget($this->prefix.'-1', $name, array(&$this, 'display'), $widget_ops, array('number' => -1));
			wp_register_widget_control($this->prefix.'-1', $name, array(&$this, 'control'), $control_ops, array('number' => -1));
		}
	}
	
	
	/**
	 * displays Sponsors Slideshow Widget
	 *
	 * Usually this function is invoked by the Wordpress widget system.
	 * However it can also be called manually via sponsors_slideshow_widget_display().
	 *
	 * @param array $args
	 * @param array|int $widget_args Widget number
	 * @return void
	 */
	function display( $args, $widget_args = 1 )
	{
		if ( is_numeric($widget_args) )
			$widget_args = array( 'number' => $widget_args );
		$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
		extract($widget_args, EXTR_SKIP);

		$options = get_option( 'sponsors_slideshow_widget' );
		$options = $options[$number];
		
		$defaults = array(
			'before_widget' => '<li id="sponsors-slideshow-widget-'.$number.'" class="widget '.get_class($this).'_'.__FUNCTION__.'">',
			'after_widget' => '</li>',
			'before_title' => '<h2 class="widgettitle">',
			'after_title' => '</h2>',
			'widget_title' => $options['title'],
			'category' => $options['category'],
			'number'  => $number,
			'fade' => $options['fade'],
			'time' => $options['time'],
			'order' => $options['order'],
			'width' => $options['width'],
			'height' => $options['height']
		);
		
		$args = array_merge( $defaults, $args );
		extract( $args, EXTR_SKIP );
			
		$links = get_bookmarks( array('category' => $category) );
		if ( $links ) {
			?>
			<script type='text/javascript'>
			//<![CDATA[
			jQuery(document).ready(function() {
				jQuery('#sponsors_slideshow_<?php echo $number ?>').cycle({
					fx: '<?php echo $fade; ?>',
					timeout: <?php echo $time*1000; ?>,
					random: <?php echo $order; ?>,
					pause: 1
				});
			});
			//]]>
			</script>
			<style type="text/css">
				div#sponsors_slideshow_<?php echo $number ?> div {
					width: <?php echo $width; ?>px;
					height: <?php echo $height; ?>px;
				}
			</style>
			<?php
			echo $before_widget;

			if ( !empty($widget_title) )
				echo $before_title . $widget_title . $after_title;
			else
				echo "<br style='clear: both;' />"; // Fix for IE

			echo '<div id="sponsors_slideshow_'.$number.'" class="sponsors_slideshow">';
			foreach ( $links AS $link ) {
				echo '<div><a href="'.$link->link_url.'" target="_blank" title="'.$link->link_name.'">';
				if ( !empty($link->link_image) ) echo '<img src="'.$link->link_image.'" alt="'.$link->link_name.'" />';
				else
					echo $link->link_name;
				echo '</a></div>';
			}
			echo '</div>';
			echo $after_widget;
		}
	}

	
	/**
	 * displays control panel for the widget
	 *
	 * @param array $args
	 * @return void
	 */
	function control( $widget_args = 1 )
	{
		global $wp_registered_widgets;
		static $updated = false;
		
		if ( is_numeric($widget_args) )
			$widget_args = array( 'number' => $widget_args );
		$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
		extract($widget_args, EXTR_SKIP);
		
		$options = get_option( 'sponsors_slideshow_widget' );
		unset($options['version']);
		if(empty($options)) $options = array();
		
		if( !$updated && !empty($_POST['sidebar']) ) {
			// Tells us what sidebar to put the data in
			$sidebar = (string) $_POST['sidebar'];

			$sidebars_widgets = wp_get_sidebars_widgets();
			if ( isset($sidebars_widgets[$sidebar]) )
				$this_sidebar =& $sidebars_widgets[$sidebar];
			else
				$this_sidebar = array();

			// search unused options
			foreach ( $this_sidebar as $_widget_id ) {
				if(preg_match('/'.$this->prefix.'-([0-9]+)/i', $_widget_id, $match)){
					$widget_number = $match[1];
 
					// $_POST['widget-id'] contain current widgets set for current sidebar
					// $this_sidebar is not updated yet, so we can determine which was deleted
					if(!in_array($match[0], $_POST['widget-id']))
						unset($options[$widget_number]);
				}
			}


			foreach($_POST[$this->prefix] as $widget_number => $values){
				if(empty($values) && isset($options[$widget_number])) // user clicked cancel
					continue;
			
				$options[$widget_number] = $values;	
			}
			$options['version'] = $this->version;
			update_option('sponsors_slideshow_widget', $options);
			$updated = true;
		}

		/* $number - is dynamic number for multi widget, given by WP
		 * by default $number = -1 (if no widgets activated). In this case we should use %i% for inputs
		 * to allow WP generate number automatically
		 */
		if ( $number == -1 ) $number = '%i%';
 
		// now we can output control
		$opts = @$options[$number];
		
		echo '<div id="sponsors_slideshow_control_'.$number.'" class="sponsors_slideshow_control">';
		echo '<p><label for="'.$this->prefix.'_'.$number.'_category">'.__( 'Links', 'sponsors-slideshow' ).'</label> '.$this->linkCategories($opts['category'], $number).'</p>';
		echo '<p><label for="'.$this->prefix.'_'.$number.'_title">'.__('Title', 'sponsors-slideshow').'</label><input type="text" size="15" name="'.$this->prefix.'['.$number.'][title]" id="'.$this->prefix.'_'.$number.'_title" value="'.$opts['title'].'" /></p>';
		echo '<p><label for="'.$this->prefix.'_'.$number.'_width">'.__( 'Width', 'sponsors-slideshow' ).'</label><input type="text" size="3" name="'.$this->prefix.'['.$number.'][width]" id="'.$this->prefix.'_'.$number.'_width" value="'.$opts['width'].'" /> px</p>';
		echo '<p><label for="'.$this->prefix.'_'.$number.'_height">'.__( 'Height', 'sponsors-slideshow' ).'</label><input type="text" size="3" name="'.$this->prefix.'['.$number.'][height]" id="'.$this->prefix.'_'.$number.'_height" value="'.$opts['height'].'" /> px</p>';
		echo '<p><label for="'.$this->prefix.'_'.$number.'_time">'.__( 'Time', 'sponsors-slideshow' ).'</label><input type="text" name="'.$this->prefix.'['.$number.'][time]" id="'.$this->prefix.'_'.$number.'_time" size="1" value="'.$opts['time'].'" /> '.__( 'seconds','sponsors-slideshow').'</p>';
		echo '<p><label for="'.$this->prefix.'_'.$number.'_fade">'.__( 'Fade Effect', 'sponsors-slideshow' ).'</label>'.$this->fadeEffects($opts['fade'], $number).'</p>';
		echo '<p><label for="'.$this->prefix.'_'.$number.'_order">'.__('Order','sponsors-slideshow').'</label>'.$this->order($opts['order'], $number).'</p>';
		echo '</div>';
		
		return;
	}

	
	/**
	 * display link categories as dropdown list
	 *
	 * @param int $selected ID of selected category
	 * @param int $number widget number
	 * @return select element of categories
	 */
	function linkCategories( $selected, $number )
	{
		$categories = get_terms('link_category', 'orderby=name&hide_empty=0');
	
		if ( empty($categories) )
			return;
	
		$out = '<select size="1" name="'.$this->prefix.'['.$number.'][category]" id="'.$this->prefix.'_'.$number.'_category">';
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
	* dropdown list of available fade effects
	*
	* @param string $selected current effect
	* @param int $number widget number
	* @return select element of fade effects
	*/
	function fadeEffects( $selected, $number )
	{
		$effects = array(__('Blind X','sponsors-slideshow') => 'blindX', __('Blind Y','sponsors-slideshow') => 'blindY', __('Blind Z','sponsors-slideshow') => 'blindZ', __('Cover','sponsors-slideshow') => 'cover', __('Curtain X','sponsors-slideshow') => 'curtainX', __('Curtain Y','sponsors-slideshow') => 'curtain>', __('Fade','sponsors-slideshow') => 'fade', __('Fade Zoom','sponsors-slideshow') => 'fadeZoom', __('Scroll Up','sponsors-slideshow') => 'scrollUp', __('Scroll Left','sponsors-slideshow') => 'scrollLeft', __('Scroll Right','sponsors-slideshow') => 'scrollRight', __('Scroll Down','sponsors-slideshow') => 'scrollDown', __('Scroll Horizontal', 'sponsors-slideshow') => 'scrollHorz', __('Scroll Vertical', 'sponsors-slideshow') => 'scrotllVert', __('Shuffle','sponsors-slideshow') => 'shuffle', __('Slide X','sponsors-slideshow') => 'slideX', __('Slide Y','sponsors-slideshow') => 'slideY', __('Toss','sponsors-slideshow') => 'toss', __('Turn Up','sponsors-slideshow') => 'turnUp', __('Turn Down','sponsors-slideshow') => 'turnDown', __('Turn Left','sponsors-slideshow') => 'turnLeft', __('Turn Right','sponsors-slideshow') => 'turnRight', __('Uncover','sponsors-slideshow') => 'uncover', __('Wipe','sponsors-slideshow') => 'wipe', __( 'Zoom','sponsors-slideshow') => 'zoom', __('Grow X','sponsors-slideshow') => 'growX', __('Grow Y','sponsors-slideshow') => 'growY', __('Random','sponsors-slideshow') => 'all');
		
		$out = '<select size="1" name="'.$this->prefix.'['.$number.'][fade]" id="'.$this->prefix.'_'.$number.'_fade">';
		foreach ( $effects AS $name => $effect ) {
			$checked =  ( $selected == $effect ) ? " selected='selected'" : '';
			$out .= '<option value="'.$effect.'"'.$checked.'>'.$name.'</option>';
		}
		$out .= '</select>';
		return $out;
	}
	
	
	/**
	 * dropdown list of Order possibilites
	 *
	 * @param string $selected current order
	 * @param int $number widget number
	 * @return order selection
	 */
	function order( $selected, $number )
	{
		$order = array(__('Ordered','sponsors-slideshow') => '0', __('Random','sponsors-slideshow') => '1');
		$out = '<select size="1" name="'.$this->prefix.'['.$number.'][order]" id="'.$this->prefix.'_'.$number.'_order">';
		foreach ( $order AS $name => $value ) {
			$checked =  ( $selected == $value ) ? " selected='selected'" : '';
			$out .= '<option value="'.$value.'"'.$checked.'>'.$name.'</option>';
		}
		$out .= '</select>';
		return $out;
	}

	
	/**
	 * Activate plugin
	 *
	 * @param none
	 * @return void
	 */
	function activate()
	{		
		$options = array();
		$options['version'] = $this->version;
		
		add_option( 'sponsors_slideshow_widget', $options, 'Sponsors Slideshow Widget Options', 'yes' );
		
		return;
	}


	/**
	 * uninstall Sponsors Slideshow Widget
	 *
	 * @param none
	 * @return void
	 */
	function uninstall()
	{
		delete_option( 'sponsors_slideshow_widget' );
	}


	/**
	 * adds code to Wordpress head
	 *
	 * @param none
	 * @return void
	 */
	function addHeaderCode()
	{
		echo "<link rel='stylesheet' href='".$this->plugin_url."/style.css' type='text/css' />\n";
		wp_register_script( 'jquery_slideshow', $this->plugin_url.'/js/jquery.cycle.all.js', array('jquery'), '2.65' );
		wp_print_scripts( 'jquery_slideshow' );
	}
	
	
	/**
	 * redefine Links Widget Arguments to exclude chosen link category
	 *
	 * @param $args
	 * @return array
	 */
	 function widget_links_args( $args )
	 {
		$options = get_option('sponsors_slideshow_widget');
		unset($options['version']);
		$excludes = array();
		foreach ( $options AS $option )
			$excludes[] = $option['category'];

		$exclude = implode(',', $excludes);
		$args['exclude_category'] = $exclude;
		return $args;
	 }
}
// Rund SponsorsSlideshowWidget
$sponsors_slideshow_widget = new SponsorsSlideshowWidget();

/**
 * Wrapper function to display Sponsors Slideshow Widget statically
 *
 * @param array $args
 * @param int $number what widget do we have, needed for numerous widgets
 *
 * This function can be used to display Sponsors Slideshow Widget in a Non-widgetized Theme.
 * Below is a list of needed arguments passed as an assoziative Array
 *
 * - number: uniqute integer to identify widget for use of numerous widgets
 * - category: ID of Link category to display
 * - widget_title: Widget title, if left empty no title will be displayed
 * - fade: Fade effect, see http://malsup.com/jquery/cycle/begin.html for a list of available effects
 * - time: Time in seconds between images
 * - width: width in px of the Slideshow
 * - height: height in px  of the Slideshow
 * - order: 0 for sequential, 1 for random ordering of images
 */
function sponsors_slideshow_widget_display( $args = array(), $number = 1 ) {
	global $sponsors_slideshow_widget;
	$sponsors_slideshow_widget->display( $args, $number );
}

?>
