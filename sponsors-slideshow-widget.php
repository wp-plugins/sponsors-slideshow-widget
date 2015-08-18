<?php
/*
Plugin Name: Sponsors Slideshow Widget
Plugin URI: http://www.wordpress.org/extend/plugins/sponsors-slideshow-widget
Description: Display certain link category as slideshow in sidebar
Version: 2.1.3
Author: Kolja Schleich

Copyright 2007-2015  Kolja Schleich  (email : kolja [dot] schleich [at] googlemail.com)

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

class SponsorsSlideshowWidget extends WP_Widget
{
	/**
	 * Plugin Version
	 *
	 * @var string
	 */
	var $version = '2.1.3';
	
	/**
	 * url to the plugin
	 *
	 * @var string
	 */
	var $plugin_url;

	
	/**
	 * path to plugin
	 *
	 * @var string
	 */
	var $plugin_path;
	
	
	/**
	 * Class Constructor
	 *
	 * @param none
	 * @return void
	 */
	function __construct()
	{
		// define constants
		if ( !defined( 'WP_CONTENT_URL' ) )
			define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
		if ( !defined( 'WP_PLUGIN_URL' ) )
			define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
			
		// define plugin url and path
		$this->plugin_url = WP_PLUGIN_URL.'/'.basename(__FILE__, '.php');
		$this->plugin_path = dirname(__FILE__);
		
		// register installation/deinstallation functions
		register_activation_hook(__FILE__, array(&$this, 'install'));
		register_uninstall_hook(__FILE__, array('SponsorsSlideshowWidget', 'uninstall'));

		// Load plugin translations
		load_plugin_textdomain( 'sponsors-slideshow', false, basename(__FILE__, '.php').'/languages' );

		// add stylesheet and scripts
		add_action( 'wp_enqueue_scripts', array(&$this, 'addScripts'), 1 );
		// filter links
		add_filter( 'widget_links_args', array($this, 'widget_links_args') );
		
		$widget_ops = array('classname' => 'sponsors_slideshow_widget', 'description' => __('Display specific link category as image slide show', 'sponsors-slideshow') );
		parent::__construct('sponsors-slideshow', __('Sponsors Slideshow', 'sponsors-slideshow'), $widget_ops);
	}
	function SponsorsSlideshowWidget()
	{
		$this->__construct();
	}
	
	
	/**
	 * displays Sponsors Slideshow Widget
	 *
	 * Usually this function is invoked by the Wordpress widget system.
	 * However it can also be called manually via sponsors_slideshow_widget_display().
	 *
	 * @param array $args display arguments
	 * @param array $instance Settings for particular instance
	 * @return void
	 */
	function widget( $args, $instance )
	{
		$defaults = array(
			'before_widget' => '<li id="sponsors-slideshow-widget-'.$this->number.'" class="widget '.get_class($this).'_'.__FUNCTION__.'">',
			'after_widget' => '</li>',
			'before_title' => '<h2 class="widgettitle">',
			'after_title' => '</h2>',
			'number' => $this->number,
		);
		
		$args = array_merge( $defaults, $args );
		extract( $args, EXTR_SKIP );
		
		$cat_id = preg_replace('/.+_(\d+)/', '$1', $instance['category']);
		if ( $instance['source'] == 'links' )
			$links = get_bookmarks( array('category' => $cat_id) );
		elseif ( $instance['source'] == 'posts' )
			$links = query_posts("cat=".$cat_id."&orderby=date&order=DESC");
		else
			$links = false;

		if ( $links ) {
			?>
			<div>
			<script type='text/javascript'>
			//<![CDATA[
			//jQuery(document).ready(function() {
				jQuery('#links_slideshow_<?php echo $number ?>').cycle({
					fx: '<?php echo $instance['fade']; ?>',
					timeout: <?php echo intval($instance['timeout'])*1000; ?>,
					speed: <?php echo intval($instance['speed'])*1000; ?>,
					random: <?php echo intval($instance['order']); ?>,
					pause: 1
				});
			//});
			//]]>
			</script>
			<style type="text/css">
				div#links_slideshow_<?php echo $number ?> div, div#links_slideshow_<?php echo $number ?> img {
					width: <?php echo intval($instance['width']); ?>px;
					height: <?php echo intval($instance['height']); ?>px;
				}
			</style>
			</div>
			<?php
			echo $before_widget;

			if ( !empty($instance['title']) )
				echo $before_title . stripslashes($instance['title']) . $after_title;
			elseif ( $instance['title'] == 'N/A' )
				echo "<br style='clear: both;' />"; // Fix for IE

			echo '<div id="links_slideshow_'.$this->number.'" class="links_slideshow">';
			foreach ( $links AS $link ) {
				if ( $instance['source'] == 'posts' ) {
					$link->link_name = $link->post_title;
					$link->link_image = get_post_meta($link->ID, $instance['post_img_meta'], true);
					$link->link_url = get_post_meta($link->ID, $instance['post_url_meta'], true);
					if ($instance['post_url_meta'] != "") $link->link_target = ( substr($link->link_url,7,strlen($_SERVER['HTTP_HOST'])) == $_SERVER['HTTP_HOST'] ) ? '' : '_blank';
					else $link->link_target = '';
				}

				if ( !empty($link->link_url) ) {
					$target = !empty($link->link_target) ? 'target="'.$link->link_target.'"' : '';
					echo '<div><a href="'.$link->link_url.'" '.$target.' title="'.$link->link_name.'">';
					if ( !empty($link->link_image) )
						echo '<img src="'.$link->link_image.'" alt="'.$link->link_name.'" />';
					else
						echo $link->link_name;
					echo '</a></div>';
				}
			}
			echo '</div>';
			echo $after_widget;
		}
	}


	/**
	 * save instance settings
	 *
	 * @param array $new_instance
	 * @param array $old_instance
	 * @return array
	 */
	function update( $new_instance, $old_instance )
	{
		return $new_instance;
	}


	/**
	 * displays control panel for the widget
	 *
	 * @param array $args
	 * @return void
	*/
	function form( $instance )
	{
		if ( !isset($instance['source']) || empty($instance['source']) )
			$instance['source'] == 'links';

		echo '<div class="links_slideshow_control">';
		echo '<p><label for="'.$this->get_field_id('source').'">'.__( 'Source', 'sponsors-slideshow' ).'</label>'.$this->sources($instance['source']).'</p>';
		echo '<p><label for="'.$this->get_field_id('post_category').'">'.__( 'Category', 'sponsors-slideshow' ).'</label> '.$this->categories($instance['category']).'</p>';
		echo '<p><label for="'.$this->get_field_id('post_url_meta').'">'.__( 'URL Field', 'sponsors-slideshow' ).'</label><input type="text" name="'.$this->get_field_name('post_url_meta').'" id="'.$this->get_field_id('post_url_meta').'" value="'.$instance['post_url_meta'].'" size="10" /> '.__('Post Meta-Field for Link URL', 'sponsors-slideshow').'</p>';
		echo '<p><label for="'.$this->get_field_id('post_img_meta').'">'.__( 'Image Field', 'sponsors-slideshow' ).'</label><input type="text" name="'.$this->get_field_name('post_img_meta').'" id="'.$this->get_field_id('post_img_meta').'" value="'.$instance['post_img_meta'].'" size="10" /> '.__('Post Meta-Field for Image URL', 'sponsors-slideshow').'</p>';
		echo '<p><label for="'.$this->get_field_id('title').'">'.__('Title', 'sponsors-slideshow').'</label><input type="text" size="15" name="'.$this->get_field_name('title').'" id="'.$this->get_field_id('title').'" value="'.stripslashes($instance['title']).'" /></p>';
		echo '<p><label for="'.$this->get_field_id('width').'">'.__( 'Width', 'sponsors-slideshow' ).'</label><input type="text" size="3" name="'.$this->get_field_name('width').'" id="'.$this->get_field_id('width').'" value="'.intval($instance['width']).'" /> px</p>';
		echo '<p><label for="'.$this->get_field_id('height').'">'.__( 'Height', 'sponsors-slideshow' ).'</label><input type="text" size="3" name="'.$this->get_field_name('height').'" id="'.$this->get_field_id('height').'" value="'.intval($instance['height']).'" /> px</p>';
		echo '<p><label for="'.$this->get_field_id('timeout').'">'.__( 'Timeout', 'sponsors-slideshow' ).'</label><input type="text" name="'.$this->get_field_name('timeout').'" id="'.$this->get_field_id('timeout').'" size="1" value="'.intval($instance['timeout']).'" /> '.__( 'seconds','sponsors-slideshow').'</p>';
		echo '<p><label for="'.$this->get_field_id('speed').'">'.__( 'Speed', 'sponsors-slideshow' ).'</label><input type="text" name="'.$this->get_field_name('speed').'" id="'.$this->get_field_id('speed').'" size="3" value="'.intval($instance['speed']).'" /> '.__( 'seconds', 'sponsors-slideshow').'</p>';
		echo '<p><label for="'.$this->get_field_id('fade').'">'.__( 'Fade Effect', 'sponsors-slideshow' ).'</label>'.$this->fadeEffects($instance['fade']).'</p>';
		echo '<p><label for="'.$this->get_field_id('order').'">'.__('Order','sponsors-slideshow').'</label>'.$this->order($instance['order']).'</p>';
		echo '</div>';
	}

	
	/**
	 * drop down list of link sources
	 *
	 * @param string $selected current order
	 * @return order selection
	 */
	function sources( $selected )
	{
		$sources = array( 'links' => __('Links', 'sponsors-slideshow'), 'posts' => __('Posts', 'sponsors-slideshow') );
		$out = "<select size='1' name='".$this->get_field_name('source')."' id='".$this->get_field_id('source')."'>";
		foreach ( $sources AS $source => $name ) {
			$checked =  ( $selected == $source ) ? " selected='selected'" : '';
			$out .= '<option value="'.$source.'"'.$checked.'>'.$name.'</option>';
		}
		$out .= '</select>';
		return $out;
	}


	/**
	 * display categories as drop down list
	 *
	 * @param string $term name of term
	 * @param string $name field name
	 * @param int $selected ID of selected category
	 * @return select element of categories
	 */
	function categories( $selected )
	{
		$out = '<select size="1" name="'.$this->get_field_name("category").'" id="'.$this->get_field_id("category").'">';
		$terms = array("Links" => "link_category", "Posts" => "category");
		
		foreach ($terms AS $label => $term) {
			$categories = get_terms($term, 'orderby=name&hide_empty=0');
			$out .= '<optgroup label="'.__($label, 'sponsors-slideshow').'">';
			if (!empty($categories)) {
				foreach ( $categories as $category ) {
					$cat_id = $category->term_id;
					$name = htmlspecialchars( apply_filters('the_category', $category->name));
					$checked = ( $selected == $term."_".$cat_id ) ? ' selected="selected"' : '';
					$out .= '<option value="'.$term."_".$cat_id.'"'.$checked.'> '. $name. '</option>';
				}
			}
			$out .= '</optgroup>';
		}
		$out .= '</select>';
	
		return $out;
	}
	

	/**
	* drop down list of available fade effects
	*
	* @param string $selected current effect
	* @return select element of fade effects
	*/
	function fadeEffects( $selected )
	{
		$effects = array(__('Blind X','sponsors-slideshow') => 'blindX', __('Blind Y','sponsors-slideshow') => 'blindY', __('Blind Z','sponsors-slideshow') => 'blindZ', __('Cover','sponsors-slideshow') => 'cover', __('Curtain X','sponsors-slideshow') => 'curtainX', __('Curtain Y','sponsors-slideshow') => 'curtain>', __('Fade','sponsors-slideshow') => 'fade', __('Fade Zoom','sponsors-slideshow') => 'fadeZoom', __('Scroll Up','sponsors-slideshow') => 'scrollUp', __('Scroll Left','sponsors-slideshow') => 'scrollLeft', __('Scroll Right','sponsors-slideshow') => 'scrollRight', __('Scroll Down','sponsors-slideshow') => 'scrollDown', __('Scroll Horizontal', 'sponsors-slideshow') => 'scrollHorz', __('Scroll Vertical', 'sponsors-slideshow') => 'scrotllVert', __('Shuffle','sponsors-slideshow') => 'shuffle', __('Slide X','sponsors-slideshow') => 'slideX', __('Slide Y','sponsors-slideshow') => 'slideY', __('Toss','sponsors-slideshow') => 'toss', __('Turn Up','sponsors-slideshow') => 'turnUp', __('Turn Down','sponsors-slideshow') => 'turnDown', __('Turn Left','sponsors-slideshow') => 'turnLeft', __('Turn Right','sponsors-slideshow') => 'turnRight', __('Uncover','sponsors-slideshow') => 'uncover', __('Wipe','sponsors-slideshow') => 'wipe', __( 'Zoom','sponsors-slideshow') => 'zoom', __('Grow X','sponsors-slideshow') => 'growX', __('Grow Y','sponsors-slideshow') => 'growY', __('Random','sponsors-slideshow') => 'all');
		
		$out = '<select size="1" name="'.$this->get_field_name('fade').'" id="'.$this->get_field_id('fade').'">';
		foreach ( $effects AS $name => $effect ) {
			$checked =  ( $selected == $effect ) ? " selected='selected'" : '';
			$out .= '<option value="'.$effect.'"'.$checked.'>'.$name.'</option>';
		}
		$out .= '</select>';
		return $out;
	}
	
	
	/**
	 * drop down list of order possibilities
	 *
	 * @param string $selected current order
	 * @return order selection
	 */
	function order( $selected )
	{
		$order = array(__('Ordered','sponsors-slideshow') => '0', __('Random','sponsors-slideshow') => '1');
		$out = '<select size="1" name="'.$this->get_field_name('order').'" id="'.$this->get_field_id('order').'">';
		foreach ( $order AS $name => $value ) {
			$checked =  ( $selected == $value ) ? " selected='selected'" : '';
			$out .= '<option value="'.$value.'"'.$checked.'>'.$name.'</option>';
		}
		$out .= '</select>';
		return $out;
	}

	
	/**
	 * install plugin
	 *
	 * @param none
	 * @return void
	 */
	function install()
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
	static function uninstall()
	{
		delete_option( 'sponsors_slideshow_widget' );
	}


	/**
	 * add stylesheet and scripts
	 *
	 * @param none
	 * @return void
	 */
	function addScripts()
	{
		wp_enqueue_style( 'sponsors-slideshow', $this->plugin_url.'/style.css', array(), $this->version, 'all' );
		wp_enqueue_script( 'sponsors-slideshow-jquery', $this->plugin_url.'/js/jquery.cycle.all.js', array('jquery'), '2.65' );
	}
	
	
	/**
	 * redefine Links widget arguments to exclude chosen link category
	 *
	 * @param $args
	 * @return array
	 */
	 function widget_links_args( $args )
	 {
		$options = get_option('widget_sponsors-slideshow');
		unset($options['version']);
		$excludes = array();
		foreach ( (array)$options AS $option ) {
			$cat = explode("_", $option['category']);
			$excludes[] = $cat[2];
		}
		
		$exclude = implode(',', $excludes);
		$args['exclude_category'] = $exclude;
		return $args;
	 }
}
// Run SponsorsSlideshowWidget
function sponsors_slideshow_widget_init() {
	register_widget("SponsorsSlideshowWidget");
}
add_action('widgets_init', 'sponsors_slideshow_widget_init');


/**
 * Wrapper function to display Sponsors Slideshow Widget statically
 *
 * @param array $args basic arguments including before_widget, after_widget, before_title, after_title and number
 * @param array $instance settings for this instance. See list below for parameters
 *
 * This function can be used to display Sponsors Slideshow Widget in a Non-widgetized Theme.
 * Below is a list of needed arguments passed as an associative Array in $instance
 *
 * - category: ID of Link category to display
 * - widget_title: Widget title, if left empty no title will be displayed
 * - fade: Fade effect, see http://malsup.com/jquery/cycle/begin.html for a list of available effects
 * - time: Time in seconds between images
 * - width: width in px of the Slideshow
 * - height: height in px  of the Slideshow
 * - order: 0 for sequential, 1 for random ordering of links
 */
function sponsors_slideshow_widget_display( $args = array(), $instance = array() ) {
	SponsorsSlideshowWidget::widget( $args, $instance );
}

?>
