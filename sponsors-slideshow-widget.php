<?php
/*
Plugin Name: Sponsors Slideshow Widget
Plugin URI: http://www.wordpress.org/extend/plugins/sponsors-slideshow-widget
Description: Display certain link category as slideshow in sidebar
Version: 2.2.2
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
	var $version = '2.2.2';
	
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
		$this->plugin_url = esc_url(WP_PLUGIN_URL.'/'.basename(__FILE__, '.php'));
		$this->plugin_path = dirname(__FILE__);
		
		// register installation/deinstallation functions
		register_activation_hook(__FILE__, array(&$this, 'install'));
		register_uninstall_hook(__FILE__, array('SponsorsSlideshowWidget', 'uninstall'));

		// Load plugin translations
		load_plugin_textdomain( 'sponsors-slideshow', false, basename(__FILE__, '.php').'/languages' );

		// add stylesheet and scripts to website and admin panel
		add_action( 'wp_enqueue_scripts', array(&$this, 'addScripts'), 5 );
		add_action( 'admin_enqueue_scripts', array(&$this, 'addStyles') );
		
		// enable categories for attachments/media
		add_action( 'init' , array(&$this, 'addCategoriesToAttachments') );
		
		// filter posts query
		add_action( 'pre_get_posts', array(&$this, 'exclude_posts') );
		
		// enable featured post image
		add_theme_support( 'post-thumbnails' ); 
		
		// filter links and categories
		add_filter( 'widget_links_args', array($this, 'widget_links_args') );
		add_filter("widget_categories_args", array(&$this, "widget_categories_arg"));
		
		// add shortcode and TinyMCE Button
		add_shortcode( 'slideshow', array(&$this, 'shortcode') );
		add_action( 'init', array(&$this, 'addTinyMCEButton') );
		add_filter( 'tiny_mce_version', array(&$this, 'changeTinyMCEVersion') );
		
		// re-activate links management
		add_filter( 'pre_option_link_manager_enabled', '__return_true' );
		
		$widget_ops = array('classname' => 'sponsors_slideshow_widget', 'description' => __('Display specific link category as image slide show', 'sponsors-slideshow') );
		parent::__construct('sponsors-slideshow', __('Slideshow', 'sponsors-slideshow'), $widget_ops);
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
			'before_widget' => '<li id="sponsors-slideshow-widget-'.$this->number.'" class="widget sponsors_slideshow_widget">',
			'after_widget' => '</li>',
			'before_title' => '<h2 class="widgettitle">',
			'after_title' => '</h2>',
			'number' => $this->number,
		);
		
		$args = array_merge( $defaults, $args );
		extract( $args, EXTR_SKIP );
		
		$cat = explode("_", $instance['category']);
		$term = $cat[0];
		$term_id = intval($cat[1]);
		if ( $instance['source'] == 'links' ) {
			$term_id = intval($cat[2]);
			$results = get_bookmarks( array('category' => $term_id) );
		} elseif ( $instance['source'] == 'posts' ){
			// Get either n latest posts or posts from specific category
			if ($term == 'latest') {
				$query = new WP_Query( array('posts_per_page' => $term_id, 'orderby' => 'date', 'order' => 'DESC') );
			} else {
				$query = new WP_Query( array('posts_per_page' => -1, 'cat' => $term_id, 'orderby' => 'date', 'order' => 'DESC') );
			}
			$results = $query->posts;
		} elseif ( $instance['source'] == 'images' ) {
			$query = new WP_Query(array('posts_per_page' => -1, 'post_type' => 'attachment', 'post_status' => 'inherit', 'cat' => $term_id));
			$results = $query->posts;
		} else {
			$results = false;
		}
		
		if ( $results ) {
			?>
			<script type='text/javascript'>
			//<![CDATA[
				// Make overflow of slideshow container hidden
				var $jq = jQuery.noConflict();
				$jq(document).ready(function() {
					$jq("#fancy-slideshow-<?php echo $number ?>-container").css("overflow", "hidden");
				});
				
				jQuery('#fancy-slideshow-<?php echo $number ?>').cycle({
					fx: '<?php echo $instance['fade']; ?>',
					timeout: <?php echo (float)$instance['timeout'] * 1000; ?>,
					next: '#fancy-slideshow-<?php echo $number ?>-next',
					prev: '#fancy-slideshow-<?php echo $number ?>-prev',
					speed: <?php echo (float)$instance['speed'] * 1000; ?>,
					random: <?php echo intval($instance['order']); ?>,
					pager: '#fancy-slideshow-nav-<?php echo $number ?>',
					pause: 1,
					resume: 1
				});
			//});
			//]]>
			</script>
			<?php
			$out = $before_widget;

			if (!isset($instance['title'])) $instance['title'] = '';
			
			if ( !empty($instance['title']) )
				$out .= $before_title . stripslashes($instance['title']) . $after_title;
			/*elseif ( $instance['title'] == 'N/A' )
				echo "<br style='clear: both;' />"; // Fix for IE*/
				
			$out .= '<div id="fancy-slideshow-'.$number.'-container" class="fancy-slideshow-container">';
			
			if (isset($instance['show_navigation_arrows']) && $instance['show_navigation_arrows'] == 1)
			$out .= '<a href="#" class="prev" id="fancy-slideshow-'.$number.'-prev"><span>&laquo;</span></a>';
			
			$out .= '<div id="fancy-slideshow-'.$number.'" class="fancy-slideshow">';
			
			$i = 0;
			foreach ( $results AS $item ) {
				$i++;
				
				if ( $instance['source'] == 'links' ) {
					$item->name = $item->link_name;
					$item->image = $item->link_image;
					$item->url = $item->link_url;
					$item->url_target = $item->link_target;
				}
				if ( $instance['source'] == 'posts' ) {
					$thumb_size = array(intval($instance['height']), intval($instance['width']));
					
					// determine thumbnail sizes
					if ($thumb_size[0] == 0 && $thumb_size[1] > 0)
						$thumb_size[0] = $thumb_size[1];
					if ($thumb_size[0] > 0 && $thumb_size[1] == 0)
						$thumb_size[1] = $thumb_size[0];
					
					if ($thumb_size[0] == 0 && $thumb_size[1] == 0)
						$thumb_size = 'full';
					
					$item->name = $item->post_title;
					$item->image = wp_get_attachment_url( get_post_thumbnail_id($item->ID, $thumb_size) );
					$item->url = get_permalink($item->ID);
					$item->url_target = '';
				}
				if ( $instance['source'] == 'images' ) {
					$item->name = $item->post_title;
					$item->image = $item->guid;
					$item->url = '';
					$item->url_target = '';
				}

				
				if ( $item->image != "" )
					$text = sprintf('<img src="%s" alt="%s" />', esc_url($item->image), $item->name);
				else
					$text = $item->name;
				
				$out .= '<div id="slideshow-'.$number.'-item-'.$i.'" class="slideshow-content">';
				
				if ( $item->url != '' ) {
					$target = ($item->url_target != "") ? 'target="'.$item->url_target.'"' : '';
					$out .= sprintf('<a href="%s" %s title="%s">%s</a>', esc_url($item->url), $target, $item->name, $text);
				} else {
					$out .= $text;
				}
				
				if ( $instance['source'] == 'posts' ) {
					$out .= "<div class='featured-post'>";
					$out .= "<h2 class='featured-post-title'>".get_the_title($item->ID).'</h2>';
					$out .= "<p class='featured-post-excerpt'>".$this->getPostExcerpt($item->ID, $instance['post_excerpt_length'])."</p>";
					$out .= "</div>";
				}
				
				$out .= '</div>';
			}
			$out .= '</div>';
			
			// Slideshow Button Navigation
			if (isset($instance['show_pager']) && $instance['show_pager'] == 1) {
				// Each link is 30px in width with 5px margin left and right
				$max_width = 40 * count($results);
				$class = ( $instance['source'] == 'posts' ) ? 'posts' : '';
				$out .= '<div class="fancy-slideshow-nav-container '.$class.'"><nav id="fancy-slideshow-nav-'.$number.'" class="fancy-slideshow-nav" style="max-width: '.$max_width.'px"></nav></div>';
			}
			
			if (isset($instance['show_navigation_arrows']) && $instance['show_navigation_arrows'] == 1)
			$out .= '<a href="#" class="next" id="fancy-slideshow-'.$number.'-next"><span>&raquo</span></a>';
		
			$out .= '</div>';
			$out .= $after_widget;
			
			if ( isset($instance['shortcode']) && $instance['shortcode'] )
				return $out;
			else
				echo $out;
		}
	}


	/**
	 * display slideshow with shortcode
	 *
	 * @param array $atts
	 */
	function shortcode( $atts )
	{
		extract(shortcode_atts(array(
			'source' => '',
			'category' => '',
			'width' => '',
			'height' => '',
			'fade' => 'scrollHorz',
			'timeout' => 3,
			'speed' => 3,
			'post_excerpt_length' => 100,
			'show_navigation_arrows' => 1,
			'show_pager' => 1,
			'align' => 'aligncenter',
			'box' => 'true',
			'random' => 0
		), $atts ));

		// generate unique ID for shortcode
		$number = uniqid(rand());
		
		$class = array( $align );
		$class[] = ($box == 'true') ? "bounding-box" : "";
		
		// widget parameters
		$args = array(
			'before_widget' => '<div class="slideshow-shortcode '.implode(" ", $class).'">',
			'after_widget' => '</div>',
			'before_title' => '',
			'after_title' => '',
			'number' => $number,
		);
		
		// slideshow parameters
		$instance = array( 'shortcode' => true, 'title' => '', 'source' => htmlspecialchars($source), 'category' => htmlspecialchars($category), 'width' => intval($width), 'height' => intval($height), 'fade' => htmlspecialchars($fade), 'timeout' => intval($timeout), 'speed' => intval($speed), 'order' => intval($random), 'post_excerpt_length' => intval($post_excerpt_length), 'show_navigation_arrows' => $show_navigation_arrows, 'show_pager' => $show_pager );
		
		// add slideshow CSS
		$out = "<style type='text/css'>\n";
		$out .= $this->getSlideshowCSS($number, $instance);
		$out .= "</style>\n";
		
		// display slideshow
		$out .= $this->widget($args, $instance);
		return $out;
	}
	
	
	/**
	 * get post excerpt
	 *
	 * @param int $post_id
	 * @param int $length: excerpt length in words
	 * @return string
	 */
	function getPostExcerpt( $post_id, $length = 100 )
	{
		$post = get_post(intval($post_id));
		$post_content = $post->post_content; //Gets post_content to be used as a basis for the excerpt
		$post_content = strip_tags(strip_shortcodes($post_content)); //Strips tags and images
		
		$words = explode(' ', $post_content, $length + 1);

		if(count($words) > $length) {
			array_pop($words);
			array_push($words, '[...]');
			$post_content = implode(' ', $words);
		}
		
		return $post_content;
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
		if ( !isset($instance['source']) || empty($instance['source']) ) {
			$instance = array('source' => 'links', 'category' => '', 'show_navigation_arrows' => 1, 'show_pager' => 1, 'post_excerpt_length' => 0,  'num_latest_posts' => 0, 'title' => '', 'width' => '', 'height' => '', 'timeout' => '', 'speed' => '', 'fade' => '', 'order' => 0);
		}
		
		echo '<div class="fancy-slideshow-control">';
		echo '<p><label for="'.$this->get_field_id('source').'">'.__( 'Source', 'sponsors-slideshow' ).'</label>'.$this->sources($instance['source']).'</p>';
		echo '<p><label for="'.$this->get_field_id('post_category').'">'.__( 'Category', 'sponsors-slideshow' ).'</label> '.$this->categories($instance['category']).'</p>';
		echo '<p><label for="'.$this->get_field_id('title').'">'.__('Title', 'sponsors-slideshow').'</label><input type="text" size="15" name="'.$this->get_field_name('title').'" id="'.$this->get_field_id('title').'" value="'.stripslashes($instance['title']).'" /></p>';
		echo '<p><label for="'.$this->get_field_id('width').'">'.__( 'Width', 'sponsors-slideshow' ).'</label><input type="text" size="3" name="'.$this->get_field_name('width').'" id="'.$this->get_field_id('width').'" value="'.intval($instance['width']).'" /> px</p>';
		echo '<p><label for="'.$this->get_field_id('height').'">'.__( 'Height', 'sponsors-slideshow' ).'</label><input type="text" size="3" name="'.$this->get_field_name('height').'" id="'.$this->get_field_id('height').'" value="'.intval($instance['height']).'" /> px</p>';
		echo '<p><label for="'.$this->get_field_id('timeout').'">'.__( 'Timeout', 'sponsors-slideshow' ).'</label><input type="text" name="'.$this->get_field_name('timeout').'" id="'.$this->get_field_id('timeout').'" size="3" value="'.(float)$instance['timeout'].'" /> '.__( 'seconds','sponsors-slideshow').'</p>';
		echo '<p><label for="'.$this->get_field_id('speed').'">'.__( 'Speed', 'sponsors-slideshow' ).'</label><input type="text" name="'.$this->get_field_name('speed').'" id="'.$this->get_field_id('speed').'" size="3" value="'.(float)$instance['speed'].'" /> '.__( 'seconds', 'sponsors-slideshow').'</p>';
		echo '<p><label for="'.$this->get_field_id('fade').'">'.__( 'Fade Effect', 'sponsors-slideshow' ).'</label>'.$this->fadeEffects($instance['fade']).'</p>';
		echo '<p><label for="'.$this->get_field_id('order').'">'.__('Order','sponsors-slideshow').'</label>'.$this->order($instance['order']).'</p>';
		$checked_arrows = (isset($instance['show_navigation_arrows']) && $instance['show_navigation_arrows'] == 1) ? ' checked="checked"' : '';
		//echo '<p><label for="'.$this->get_field_id('show_navigation_arrows').'">'.__('Navigation Arrows','sponsors-slideshow').'</label><input type="checkbox" name="'.$this->get_field_name('show_navigation_arrows').'" id="'.$this->get_field_id('show_navigation_arrows').'" value="1"'.$checked_arrows.' /><br style="clear: both;" /></p>';
		$checked_pager = (isset($instance['show_pager']) && $instance['show_pager'] == 1) ? ' checked="checked"' : '';
		//echo '<p><label for="'.$this->get_field_id('show_pager').'">'.__('Navigation Pager','sponsors-slideshow').'</label><input type="checkbox" name="'.$this->get_field_name('show_pager').'" id="'.$this->get_field_id('show_pager').'" value="1"'.$checked_pager.' /></p>';
		echo '<p><label class="checkbox" for="'.$this->get_field_id('show_navigation_arrows').'">'.__('Navigation','sponsors-slideshow').'</label><input type="checkbox" name="'.$this->get_field_name('show_navigation_arrows').'" id="'.$this->get_field_id('show_navigation_arrows').'" value="1"'.$checked_arrows.' /><label class="right" for="'.$this->get_field_id('show_navigation_arrows').'">'.__('Arrows','sponsors-slideshow').'</label><input type="checkbox" name="'.$this->get_field_name('show_pager').'" id="'.$this->get_field_id('show_pager').'" value="1"'.$checked_pager.' /><label class="right" for="'.$this->get_field_id('show_pager').'">'.__('Pager','sponsors-slideshow').'</label></p>';
		echo '<p><label for="'.$this->get_field_id('post_excerpt_length').'">'.__( 'Post Excerpt', 'sponsors-slideshow' ).'</label><input type="text" name="'.$this->get_field_name('post_excerpt_length').'" id="'.$this->get_field_id('post_excerpt_length').'" value="'.intval($instance['post_excerpt_length']).'" size="5" /> '.__('words', 'sponsors-slideshow').'</p>';
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
		$sources = array( 'links' => __('Links', 'sponsors-slideshow'), 'images' => __('Images', 'sponsors-slideshow'), 'posts' => __('Posts', 'sponsors-slideshow') );
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
		$terms = array("Links" => "link_category", "Posts or Images" => "category");
		
		foreach ($terms AS $label => $term) {
			$categories = get_terms($term, 'orderby=name&hide_empty=0');
			$out .= '<optgroup label="'.__($label, 'sponsors-slideshow').'">';
			if (!empty($categories)) {
				foreach ( $categories as $category ) {
					$cat_id = $category->term_id;
					$name = htmlspecialchars( apply_filters('the_category', $category->name));
					$checked = ( $selected == $term."_".$cat_id ) ? ' selected="selected"' : '';
					$out .= '<option value="'.$term."_".$cat_id.'"'.$checked.'>'.$name. '</option>';
				}
			}
			$out .= '</optgroup>';
		}
		
		// Add special category for latest posts
		$out .= '<optgroup label="'.__('Latest Posts', 'sponsors-slideshow').'">';
		for ($i = 1; $i <= 15; $i++) {
			$checked = ( $selected == sprintf('latest_%d', $i) ) ? ' selected="selected"' : '';
			$out .= '<option value="latest_'.$i.'"'.$checked.'>'.sprintf(__('Latest %d posts', 'sponsors-slideshow'), $i).'</option>';
		}
		$out .= '</optgroup>';
		
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
		$effects = array( __('Blind X','sponsors-slideshow') => 'blindX', __('Blind Y','sponsors-slideshow') => 'blindY', __('Blind Z','sponsors-slideshow') => 'blindZ', __('Cover','sponsors-slideshow') => 'cover', __('Curtain X','sponsors-slideshow') => 'curtainX', __('Curtain Y','sponsors-slideshow') => 'curtain>', __('Fade','sponsors-slideshow') => 'fade', __('Fade Zoom','sponsors-slideshow') => 'fadeZoom', __('Scroll Up','sponsors-slideshow') => 'scrollUp', __('Scroll Left','sponsors-slideshow') => 'scrollLeft', __('Scroll Right','sponsors-slideshow') => 'scrollRight', __('Scroll Down','sponsors-slideshow') => 'scrollDown', __('Scroll Horizontal', 'sponsors-slideshow') => 'scrollHorz', __('Scroll Vertical', 'sponsors-slideshow') => 'scrotllVert', __('Shuffle','sponsors-slideshow') => 'shuffle', __('Slide X','sponsors-slideshow') => 'slideX', __('Slide Y','sponsors-slideshow') => 'slideY', __('Toss','sponsors-slideshow') => 'toss', __('Turn Up','sponsors-slideshow') => 'turnUp', __('Turn Down','sponsors-slideshow') => 'turnDown', __('Turn Left','sponsors-slideshow') => 'turnLeft', __('Turn Right','sponsors-slideshow') => 'turnRight', __('Uncover','sponsors-slideshow') => 'uncover', __('Wipe','sponsors-slideshow') => 'wipe', __( 'Zoom','sponsors-slideshow') => 'zoom', __('Grow X','sponsors-slideshow') => 'growX', __('Grow Y','sponsors-slideshow') => 'growY', __('Random','sponsors-slideshow') => 'all');
		
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
	 * add stylesheet
	 *
	 * @param none
	 * @return void
	 */
	function addStyles()
	{
		wp_enqueue_style( 'fancy-slideshow', $this->plugin_url.'/style.css', array(), $this->version, 'all' );
	}
	
	
	/**
	 * add scripts
	 *
	 * @param none
	 * @return void
	 */
	function addScripts()
	{
		$options = get_option('widget_sponsors-slideshow');
		unset($options['_multiwidget']);
		
		wp_enqueue_style( 'fancy-slideshow', $this->plugin_url.'/style.css', array(), $this->version, 'all' );
		wp_enqueue_script( 'jquery_slideshow', $this->plugin_url.'/js/jquery.cycle.all.js', array('jquery'), '2.65' );

		// add inline CSS for each slideshow widget
		foreach ($options AS $number => $instance)
			wp_add_inline_style( 'fancy-slideshow', $this->getSlideshowCSS($number, $instance) );
	}
	
	
	/**
	 * get CSS styles for individual slideshow
	 *
	 * @param string $number
	 * @param array $instance
	 * @return string
	 */
	function getSlideshowCSS( $number, $instance )
	{
		$css = "";
		if (intval($instance['height']) > 0 || intval($instance['width']) > 0) {
			$css .= "#fancy-slideshow-".$number."-container, #fancy-slideshow-".$number."-container img { ";
			if (intval($instance['height']) > 0) {
				//$css .= "height: ".intval($instance['height'])."px;";
				$css .= "max-height: ".intval($instance['height'])."px;";
			}
			if (intval($instance['width']) > 0) {
				//$css .= "width: ".intval($instance['width'])."px;";
				$css .= "max-width: ".intval($instance['width'])."px;";
			}

			$css .= " }";
				
			if (intval($instance['height']) > 0) {
				$css .= "\n#fancy-slideshow-".$number."-container .featured-post {";
				//$css .= "height: ".intval($instance['height'])/3 . "px !important;";
				$css .= "max-height: ".intval($instance['height'])/3 ."px !important; }\n";
				$css .= "#fancy-slideshow-".$number." .fancy-slideshow-container .next, #fancy-slideshow-".$number." .fancy-slideshow-container .prev {";
				$css .= "top: ".intval($instance['height']-30)/2 ."px;";
				$css .= "}";
			}
		}
		return $css;
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
		unset($options['_multiwidget']);
		$excludes = array();
		if (count($options) > 0) {
			foreach ( (array)$options AS $option ) {
				// exclude only categories from links source
				if ( $option['source'] == 'links' ) {
					$cat = explode("_", $option['category']);
					$excludes[] = $cat[2];
				}
			}
			
			$exclude = implode(',', $excludes);
			$args['exclude_category'] = $exclude;
		}
		return $args;
	 }
	 
	
	/**
	 * Exclude categories, which are active in widget
	 *
	 * @param array $args
	 * @return void
	 */
	function widget_categories_arg( $args ){
		$options = get_option('widget_sponsors-slideshow');
		unset($options['_multiwidget']);
		$excludes = array();
		if (count($options) > 0) {
			foreach ( (array)$options AS $option ) {
				// exclude categories from widget only if source is images
				if ($option['source'] == 'images') {
					$cat = explode("_", $option['category']);
					if ( isset($cat[1]) && !in_array($cat[1], $excludes) )
						$excludes[] = $cat[1];
				}
			}
			
			$exclude = implode(',', $excludes);
			$args["exclude"] = $exclude;
		}
		return $args;
	}
	
	
	/**
	 * Exclude posts from main query
	 *
	 * @param array $args
	 * @return void
	 */
	function exclude_posts( $query ){
		$options = get_option('widget_sponsors-slideshow');
		unset($options['_multiwidget']);
		$cat_ids = array();
		$num = array();
		if (count($options) > 0) {
			foreach ($options AS $option) {
				if ($option['source'] == 'posts') {
					$cat = explode("_", $option['category']);
					// Exclude n latest posts or posts from selected category
					if ($cat[0] == 'latest') {
						$num[] = intval($cat[1]);
					} else {
						$cat_ids[] = "-".$cat[1];
					}
				}
			}
			$cat = implode(",", $cat_ids);

			if ( $query->is_home() && $query->is_main_query() ) {
				if (count($cat_ids) > 0)
					$query->set( 'cat', $cat );
			
				foreach ($num AS $n)
					$query->set( 'offset', $n );			
			}
		}
	}
	
	
	/**
	  * Enable categories in attachments
	  *
	  * @param none
	  * @return void
	  */
	function addCategoriesToAttachments() {
		 register_taxonomy_for_object_type( 'category', 'attachment' );  
	}
	
	
	/**
	 * add TinyMCE Button
	 *
	 * @param none
	 * @return void
	 */
	function addTinyMCEButton()
	{
		// Don't bother doing this stuff if the current user lacks permissions
		if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') ) return;
		
		// Add only in Rich Editor mode
		if ( get_user_option('rich_editing') == 'true') {
			add_filter("mce_external_plugins", array(&$this, 'addTinyMCEPlugin'));
			add_filter('mce_buttons', array(&$this, 'registerTinyMCEButton'));
		}
	}
	function addTinyMCEPlugin( $plugin_array )
	{
		$plugin_array['SponsorsSlideshow'] = $this->plugin_url.'/tinymce/editor_plugin.js';
		return $plugin_array;
	}
	function registerTinyMCEButton( $buttons )
	{
		array_push($buttons, "separator", "SponsorsSlideshow");
		return $buttons;
	}
	function changeTinyMCEVersion( $version )
	{
		return ++$version;
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
	$sponsors_slideshow_widget = new SponsorsSlideshowWidget();
	$sponsors_slideshow_widget->widget( $args, $instance );
}

?>