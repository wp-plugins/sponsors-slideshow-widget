<?php
/*
Plugin Name: Sponsors Slideshow Widget
Author URI: http://kolja.galerie-neander.de/
Plugin URI: http://kolja.galerie-neander.de/plugins/#sponsors-slideshow-widget
Description: Display certain link category as slideshow in sidebar
Version: 1.4
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
	var $version = '1.4';
	
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

		add_action( 'init', array(&$this, 'register') );
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

		$name = __('Sponsors Slideshow', 'sponsors-slideshow');
		$widget_ops = array('classname' => 'sponsors_slideshow_widget', 'description' => __('Display specific link category as image slide show', 'sponsors-slideshow') );
		$control_ops = array('width' => 200, 'height' => 200, 'id_base' => $this->prefix);
		
		$options = get_option('sponsors_slideshow_widget');
		unset($options['version']);
		if (isset($options[0])) unset($options[0]);
		
		if ( !empty($options)) {
			foreach(array_keys($options) AS $widget_number) {
				wp_register_sidebar_widget($this->prefix.'-'.$widget_number, $name, array(&$this, 'display'), $widget_ops, array('number' => $widget_number));
				wp_register_widget_control($this->prefix.'-'.$widget_number, $name, array(&$this, 'control'), $control_ops, array('number' => $widget_number));
			}
		} else {
			$options = array();
			$widget_number = 1;
			wp_register_sidebar_widget($this->prefix.'-'.$widget_number, $name, array(&$this, 'display'), $widget_ops, array('number' => $widget_number));
			wp_register_widget_control($this->prefix.'-'.$widget_number, $name, array(&$this, 'control'), $control_ops, array('number' => $widget_number));
		}
	}
	
	
	/**
	 * displays Sponsors Slideshow Widget
	 *
	 * Usually this function is invoked by the Wordpress widget system.
	 * However it can also be called manually via sponsors_slideshow_widget_display().
	 *
	 * @param array $args
	 * @param array $args1
	 * @return void
	 */
	function display($args, $args1)
	{
		$options = get_option( 'sponsors_slideshow_widget' );
		$options = $options[$args1['number']];
		
		$defaults = array(
			'before_widget' => '<li id="sponsors_slideshow_widget" class="widget '.get_class($this).'_'.__FUNCTION__.'">',
			'after_widget' => '</li>',
			'before_title' => '<h2 class="widgettitle">',
			'after_title' => '</h2>',
			'widget_title' => $options['title'],
			'category' => $options['category'],
		);
		
		$args = array_merge( $defaults, $args );
		extract( $args );
			
		$links = get_bookmarks( array('category' => $category) );
		if ( $links ) {
			?>
			<script type='text/javascript'>
			//<![CDATA[
			jQuery(document).ready(function(){
				jQuery('#sponsors_slideshow_<?php echo $args1['number'] ?>').slideshow({
					width: <?php echo $options['width'] ?>,
					height:<?php echo $options['height']; ?>,
					time: <?php echo $options['time']*1000; ?>,
					title:false,
					panel:false,
					loop:true,
					play:true,
					playframe: false,
					effect: '<?php echo $options['fade'] ?>',
					random: <?php echo $options['order'] ?>
				});
			});
			//]]>
			</script>
			<?php
			echo $before_widget . $before_title . $widget_title . $after_title;
			echo '<div id="sponsors_slideshow_'.$args1['number'].'" class="sponsors_slideshow">';
			foreach ( $links AS $link ) {
				echo '<a href="'.$link->link_url.'" target="_blank" title="'.$link->link_name.'"><img src="'.$link->link_image.'" alt="'.$link->link_name.'" /></a>';
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
	function control($args)
	{
		global $wpdb;
		
		$options = get_option( 'sponsors_slideshow_widget' );
		if(empty($options)) $options = array();
		if(isset($options[0])) unset($options[0]);
		
		if(isset($_POST) && !empty($_POST[$this->prefix]) && is_array($_POST)) {
			foreach($_POST[$this->prefix] as $widget_number => $values){
				if(empty($values) && isset($options[$widget_number])) // user clicked cancel
					continue;
			
				if(!isset($options[$widget_number]) && $args['number'] == -1){
					$args['number'] = $widget_number;
					$options['last_number'] = $widget_number;
				}
				$categories = $wpdb->get_results( "SELECT name FROM $wpdb->terms WHERE `term_id` = {$values['category']}" );
				$values['title'] = wp_specialchars($categories[0]->name);
				$options[$widget_number] = $values;	
			}
			// update number
			if($args['number'] == -1 && !empty($options['last_number'])){
				$args['number'] = $options['last_number'];
			}
			// clear unused options and update options in DB. return actual options array
			$options = $this->updateOptions($this->prefix, $options, $_POST[$this->prefix], $_POST['sidebar'], 'sponsors_slideshow_widget');
		}
		/* $number - is dynamic number for multi widget, given by WP
		 * by default $number = -1 (if no widgets activated). In this case we should use %i% for inputs
		 * to allow WP generate number automatically
		 */
		$number = ($args['number'] == -1)? '%i%' : $args['number'];
 
		// now we can output control
		$opts = @$options[$number];
		
		echo '<div id="sponsors_slideshow_control_'.$number.'" class="sponsors_slideshow_control">';
		echo '<p><label for="'.$this->prefix.'_'.$number.'_category">'.__( 'Links', 'sponsors-slideshow' ).'</label> '.$this->linkCategories($opts['category'], $number).'</p>';
		echo '<p><label for="'.$this->prefix.'_'.$number.'_width">'.__( 'Width', 'sponsors-slideshow' ).'</label><input type="text" size="3" name="'.$this->prefix.'['.$number.'][width]" id="'.$this->prefix.'_'.$number.'_width" value="'.$opts['width'].'" /> px</p>';
		echo '<p><label for="'.$this->prefix.'_'.$number.'_height">'.__( 'Height', 'sponsors-slideshow' ).'</label><input type="text" size="3" name="'.$this->prefix.'['.$number.'][height]" id="'.$this->prefix.'_'.$number.'_height" value="'.$opts['height'].'" /> px</p>';
		echo '<p><label for="'.$this->prefix.'_'.$number.'_time">'.__( 'Time', 'sponsors-slideshow' ).'</label><input type="text" name="'.$this->prefix.'['.$number.'][time]" id="'.$this->prefix.'_'.$number.'_time" size="1" value="'.$opts['time'].'" /> '.__( 'seconds','sponsors-slideshow').'</p>';
		echo '<p><label for="'.$this->prefix.'_'.$number.'_fade">'.__( 'Fade Effect', 'sponsors-slideshow' ).'</label>'.$this->fadeEffects($opts['fade'], $number).'</p>';
		echo '<p><label for="'.$this->prefix.'_'.$number.'_order">'.__('Order','sponsors-slideshow').'</label>'.$this->order($opts['random'], $number).'</p>';
		echo '</div>';
		
		return;
	}

	
	/**
	 * Universal update helper
	 *
	 */
	function updateOptions($id_prefix, $options, $post, $sidebar, $option_name = '')
	{
		global $wp_registered_widgets;
		static $updated = false;
		
		// get active sidebar
		$sidebars_widgets = wp_get_sidebars_widgets();
		if ( isset($sidebars_widgets[$sidebar]) )
			$this_sidebar =& $sidebars_widgets[$sidebar];
		else
			$this_sidebar = array();

		// search unused options
		foreach ( $this_sidebar as $_widget_id ) {
			if(preg_match('/'.$id_prefix.'-([0-9]+)/i', $_widget_id, $match)){
				$widget_number = $match[1];
 
				// $_POST['widget-id'] contain current widgets set for current sidebar
				// $this_sidebar is not updated yet, so we can determine which was deleted
				if(!in_array($match[0], $_POST['widget-id'])){
					unset($options[$widget_number]);
				}
			}
		}
			
		// update database
		if(!empty($option_name)){
			$options['version'] = $this->version;
			update_option($option_name, $options);
			$updated = true;
		}
		
		// return updated array
		return $options;
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
		$effects = array(__('Fade','sponsors-slideshow') => 'fade', __('Zoom Fade','sponsors-slideshow') => 'zoomFade', __('Scroll Up','sponsors-slideshow') => 'scrollUp', __('Scroll Left','sponsors-slideshow') => 'scrollLeft', __('Scroll Right','sponsors-slideshow') => 'scrollRight', __('Scroll Down','sponsors-slideshow') => 'scrollDown', __( 'Zoom','sponsors-slideshow') => 'zoom', __('Grow X','sponsors-slideshow') => 'growX', __('Grow Y','sponsors-slideshow') => 'growY', __('Zoom BR','sponsors-slideshow') => 'zoomBR', __('Zoom TL','sponsors-slideshow') => 'zoomTL', __('Random','sponsors-slideshow') => 'random');
		
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
		$order = array(__('Ordered','sponsors-slideshow') => 'false', __('Random','sponsors-slideshow') => 'true');
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
		wp_register_script( 'jquery_slideshow', $this->plugin_url.'/js/jquery.aslideshow.js', array('jquery'), '0.5.3' );
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
		$args['exclude_category'] = $options['category'];
		return $args;
	 }
}
// Rund SponsorsSlideshowWidget
$sponsors_slideshow_widget = new SponsorsSlideshowWidget();

/**
 * Wrapper function to display Sponsors Slideshow Widget statically
 *
 * @param string/array $args
 */
function sponsors_slideshow_widget_display( $args = array() ) {
	global $sponsors_slideshow_widget;
	$sponsors_slideshow_widget->display( $args );
}
