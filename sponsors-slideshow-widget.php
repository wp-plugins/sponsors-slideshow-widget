<?php
/*
Plugin Name: Fancy Slideshows
Plugin URI: http://www.wordpress.org/extend/plugins/sponsors-slideshow-widget
Description: Generate fancy slideshows in an instance
Version: 2.3.1
Author: Kolja Schleich

Copyright 2007-2015  Kolja Schleich  (email : kolja [dot] schleich [at] googlemail [dot] com)

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
	var $version = '2.3.1';
	
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
		// define plugin url and path
		$this->plugin_url = esc_url(plugin_dir_url(__FILE__));
		$this->plugin_path = dirname(__FILE__);
		
		// define plugin url and path as constants
		if ( !defined( 'SPONSORS_SLIDESHOW_URL' ) )
			define ( 'SPONSORS_SLIDESHOW_URL', $this->plugin_url );
		if ( !defined( 'SPONSORS_SLIDESHOW_PATH' ) )
			define ( 'SPONSORS_SLIDESHOW_PATH', $this->plugin_path );
		
		// register installation/deinstallation functions
		register_activation_hook(__FILE__, array(&$this, 'install'));
		register_uninstall_hook(__FILE__, array('SponsorsSlideshowWidget', 'uninstall'));

		// Load plugin translations
		load_plugin_textdomain( 'sponsors-slideshow', false, basename(__FILE__, '.php').'/languages' );

		// add stylesheet and scripts to website and admin panel
		add_action( 'wp_enqueue_scripts', array(&$this, 'addScripts'), 5 );
		add_action( 'admin_enqueue_scripts', array(&$this, 'addStyles') );
		
		// add new gallery taxonomy
		add_action( 'init', array(&$this, 'addTaxonomies') );
		
		// filter posts query
		add_action( 'pre_get_posts', array(&$this, 'exclude_posts') );
		
		// enable featured post image
		add_theme_support( 'post-thumbnails' ); 
		
		// filter links and categories
		add_filter( 'widget_links_args', array($this, 'widget_links_args') );
		add_filter( "widget_categories_args", array(&$this, "widget_categories_arg") );
		
		// add shortcode and TinyMCE Button
		add_shortcode( 'slideshow', array(&$this, 'shortcode') );
		add_action( 'init', array(&$this, 'addTinyMCEButton') );
		add_filter( 'tiny_mce_version', array(&$this, 'changeTinyMCEVersion') );
		
		// re-activate links management
		add_filter( 'pre_option_link_manager_enabled', '__return_true' );
		
		// register AJAX action to show TinyMCE Window
		add_action( 'wp_ajax_sponsors-slideshow_tinymce_window', array(&$this, 'showTinyMCEWindow') );
		
		// Add custom meta box to posts and pages for optional title and description of slides
		add_action( 'add_meta_boxes_post', array(&$this, 'addMetaboxPost') );
		add_action( 'add_meta_boxes_page', array(&$this, 'addMetaboxPage') );
		// Add actions to modify custom post meta upon publishing and editing post
		add_action( 'publish_post', array(&$this, 'editPostMeta') );
		add_action( 'edit_post', array(&$this, 'editPostMeta') );
		
		$widget_ops = array('classname' => 'sponsors_slideshow_widget', 'description' => __('Generate fancy slideshows', 'sponsors-slideshow') );
		parent::__construct('sponsors-slideshow', __('Slideshow', 'sponsors-slideshow'), $widget_ops);
	}
	function SponsorsSlideshowWidget()
	{
		$this->__construct();
	}
	
	
	/**
	 * add new gallery taxonomy for grouping images
	 *
	 * @param none
	 */
	function addTaxonomies()
	{
		$labels = array(
			'name'              => __('Galleries', 'sponsors-slideshow'),
			'singular_name'     => __('Gallery', 'sponsors-slideshow'),
			'search_items'      => __('Search Galleries', 'sponsors-slideshow'),
			'all_items'         => __('All Galleries', 'sponsors-slideshow'),
			'parent_item'       => __('Parent Gallery', 'sponsors-slideshow'),
			'parent_item_colon' => __('Parent Gallery:', 'sponsors-slideshow'),
			'edit_item'         => __('Edit Gallery', 'sponsors-slideshow'),
			'update_item'       => __('Update Gallery', 'sponsors-slideshow'),
			'add_new_item'      => __('Add New Gallery', 'sponsors-slideshow'),
			'new_item_name'     => __('New Gallery Name', 'sponsors-slideshow'),
			'menu_name'         => __('Galleries', 'sponsors-slideshow')
		);

		$args = array(
			'labels' => $labels,
			'hierarchical' => true,
			'query_var' => 'true',
			'rewrite' => 'true',
			'show_admin_column' => 'true',
		);

		register_taxonomy( 'gallery', 'attachment', $args );
		
		$labels = array(
			'name'              => __('Categories', 'sponsors-slideshow'),
			'singular_name'     => __('Category', 'sponsors-slideshow'),
			'search_items'      => __('Search Categories', 'sponsors-slideshow'),
			'all_items'         => __('All Categories', 'sponsors-slideshow'),
			'parent_item'       => __('Parent Category', 'sponsors-slideshow'),
			'parent_item_colon' => __('Parent Category:', 'sponsors-slideshow'),
			'edit_item'         => __('Edit Category', 'sponsors-slideshow'),
			'update_item'       => __('Update Category', 'sponsors-slideshow'),
			'add_new_item'      => __('Add New Category', 'sponsors-slideshow'),
			'new_item_name'     => __('New Category Name', 'sponsors-slideshow'),
			'menu_name'         => __('Categories', 'sponsors-slideshow')
		);

		$args = array(
			'labels' => $labels,
			'hierarchical' => true,
			'query_var' => 'true',
			'rewrite' => 'true',
			'show_admin_column' => 'true',
		);

		register_taxonomy( 'page_category', 'page', $args );
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
		$instance['source'] = $cat[0];
		if ( $instance['source'] == 'links' ) {
			$slides = get_bookmarks( array('category' => intval($cat[3])) );
		} elseif ( $instance['source'] == 'posts' ){
			// Get either n latest posts or posts from specific category
			if ($cat[1] == 'latest') {
				$query = new WP_Query( array('posts_per_page' => intval($cat[2]), 'orderby' => 'date', 'order' => 'DESC') );
			} else {
				$query = new WP_Query( array('posts_per_page' => -1, 'cat' => intval($cat[2]), 'orderby' => 'date', 'order' => 'DESC') );
			}
			$slides = $query->posts;
		} elseif ( $instance['source'] == 'images' ) {
			$query = new WP_Query(array(
				'posts_per_page' => -1,
				'post_type' => 'attachment',
				'post_status' => 'inherit',
				'tax_query' => array(
					array(
						'taxonomy' => 'gallery',
						'field' => 'term_id',
						'terms' => intval($cat[2])
					)
				)
			));
			$slides = $query->posts;
		} elseif ( $instance['source'] == 'pages' ) {
			$query = new WP_Query(array(
				'posts_per_page' => -1,
				'post_type' => 'page',
				'post_status' => 'published',
				'tax_query' => array(
					array(
						'taxonomy' => 'page_category',
						'field' => 'term_id',
						'terms' => intval($cat[3])
					)
				)
			));
			$slides = $query->posts;
		} else {
			// get slides from external source
			$slides = apply_filters( 'fancy_slideshow_get_slides_'.$instance['source'], $cat );
			
			/*
			 * Enforce some security policy
			 *
			 * 1) Disallow images on different host
			 * 2) Disallow external links to other hosts
			 */
			$s = 0;
			$myhost = $this->getBaseURL( get_option('siteurl') );
			foreach ( $slides AS $slide ) {
				// Remove images from external host
				if ( $this->getBaseURL( $slide->image ) != $myhost )
					$slides[$s]->image = "";
				
				// Remove external links
				if ( $this->getBaseURL( $slide->url ) != $myhost ) {
					$slides[$s]->url = "";
					$slides[$s]->url_target = "";
				}
				
				$s++;
			}
		}
		
		if ( $slides ) {
			$num_slides = count($slides);
			
			$out = $before_widget;

			if (!isset($instance['title'])) $instance['title'] = '';
			
			if ( !empty($instance['title']) )
				$out .= $before_title . stripslashes($instance['title']) . $after_title;
				
			$out .= '<div id="fancy-slideshow-'.$number.'-container" class="fancy-slideshow-container">';
			
			if (isset($instance['show_navigation_arrows']) && $instance['show_navigation_arrows'] == 1)
			$out .= '<a href="#" class="prev" id="fancy-slideshow-'.$number.'-prev"><span>&lt;</span></a>';
			
			$fx = explode("_", $instance['fade']);
			$fade = $fx[0];
			$options = "";
			if ( in_array($fade, array('tileSlide', 'tileBlind')) ) {
				// horizontal tile
				if ( isset($fx[1]) )
					$options .= " data-cycle-tile-vertical=false";
				
				$options .= " data-cycle-tile-count=10";
			} elseif ( $fade == "carousel" ) {
				if ( !isset($instance['carousel_num_slides']) || (isset($instance['carousel_num_slides']) && intval($instance['carousel_num_slides']) == 0) )
					$instance['carousel_num_slides'] = 3;
				
				$options .=  " data-cycle-carousel-visible=".$instance['carousel_num_slides']." data-cycle-carousel-fluid=true";
			}
			
			// Setup pager template
			$pager_template = "<a href='#'></a>";
			if ( $instance['navigation_pager'] == 'thumbs' ) {
				if ( $num_slides == 2 ) $mar_thumbs = 1;
				if ( $num_slides > 2 ) $mar_thumbs = $num_slides/(($num_slides-1)*2);
				
				$pager_template = "<a href='#' style='width: ".(100/$num_slides)."%;' class='{{API.customGetImageClass}}'><img src='{{API.customGetImageSrc}}' style='width: ".((100/$num_slides)-1)."%; margin: 0 ".$mar_thumbs."%;' /></a>";
			}
			
			// Setup Slide Overlay
			$overlay_template = "";
			if ( $instance['overlay']['display'] != "none" ) {
				if ( $instance['overlay']['display'] == "title" )
					$overlay_template = "<span class='title'>{{title}}</span>";
				
				if ( $instance['overlay']['display'] == "description" )
					$overlay_template = "<span class='description'>{{desc}}</span>";
				
				if ( $instance['overlay']['display'] == "all" )
					$overlay_template = "<span class='title'>{{title}}</span><span class='description'>{{desc}}</span>";
			
				// Setup overlay effects selector			
				if ( $instance['overlay']['animate'] == "content" )
					$options .= " data-cycle-overlay-fx-sel='>span'";
				
				// Setup overlay animation effects
				if ( $instance['overlay']['effect'] != "none" ) {
					// activate cycle-caption-plugin caption2 for animated captions and overlays
					$options .= " data-cycle-caption-plugin=caption2";
					
					// set overlay animation to slide up & down (default: fade)
					if ( $instance['overlay']['effect'] == "slide_up_down" )
						$options .= " data-cycle-overlay-fx-out=slideUp data-cycle-overlay-fx-in=slideDown";
				}	
			}
			
			// Set Easing effect
			if ( $instance['easing'] != 'none' ) {
				$options .= " data-cycle-easing=".$instance['easing'];
			}
			
			
			$out .= '<ul id="fancy-slideshow-'.$number.'" class="fancy-slideshow slides cycle-slideshow '.$instance['source'].'"
				data-cycle-fx="'.$fade.'"
				data-cycle-slides="> li"
				data-cycle-pause-on-hover="true"
				data-cycle-speed="'.((float)$instance['speed'] * 1000).'"
				data-cycle-timeout="'.((float)$instance['timeout'] * 1000).'"
				data-cycle-next="#fancy-slideshow-'.$number.'-next"
				data-cycle-prev="#fancy-slideshow-'.$number.'-prev"
				data-cycle-pager="#fancy-slideshow-nav-'.$number.'"
				data-cycle-pager-template="'.$pager_template.'"
				data-cycle-random="'.intval($instance['order']).'"
				data-cycle-overlay-template="'.$overlay_template.'"
				'.$options.'
			>';
			
			// Only show overlay div if we want to have it
			if ( $instance['overlay']['display'] != "none" )
				$out .= '<div class="cycle-overlay '.$instance['overlay']['style'].'"></div>';
			
			$i = 0;
			foreach ( $slides AS $slide ) {
				$i++;
				
				if ( $instance['source'] == 'links' ) {
					$slide->name = $slide->link_name;
					$slide->image = $slide->link_image;
					$slide->url = $slide->link_url;
					$slide->url_target = $slide->link_target;
					$slide->link_class = '';
					$slide->link_rel = 'nofollow';
					$slide->title = $slide->name;
					
					$slide->slide_title = "";
					$slide->slide_desc = "";
				}
				if ( in_array($instance['source'], array('posts', 'pages')) ) {
					$thumb_size = array(intval($instance['height']), intval($instance['width']));
					
					// determine thumbnail sizes
					if ($thumb_size[0] == 0 && $thumb_size[1] > 0)
						$thumb_size[0] = $thumb_size[1];
					if ($thumb_size[0] > 0 && $thumb_size[1] == 0)
						$thumb_size[1] = $thumb_size[0];
					
					if ($thumb_size[0] == 0 && $thumb_size[1] == 0)
						$thumb_size = 'full';
					
					$slide->name = $slide->post_title;
					$slide->image = wp_get_attachment_url( get_post_thumbnail_id($slide->ID, $thumb_size) );
					$slide->url = get_permalink($slide->ID);
					$slide->url_target = '';
					$slide->link_class = '';
					$slide->link_rel = '';
					$slide->title = $slide->name;
					
					// First get custom post meta data
					$slide->slide_title = stripslashes(get_post_meta( $slide->ID, 'fancy_slideshow_overlay_title', true ));
					$slide->slide_desc = stripslashes(get_post_meta( $slide->ID, 'fancy_slideshow_overlay_description', true ));
			
					// Fallback to default slide overlay if custom metadata is empty
					if ( $slide->slide_title == "" ) $slide->slide_title = get_the_title($slide->ID);
					if ( $slide->slide_desc == "" ) $slide->slide_desc = $this->getPostExcerpt($slide->ID, $instance['post_excerpt_length']);
					
					$slide->slide_desc .= sprintf( "<span class='continue'><a href='%s'>%s</a></span>", $slide->url, __('Continue Reading', 'sponsors-slideshow') );
				}
				if ( $instance['source'] == 'images' ) {
					$slide->name = $slide->post_title;
					$slide->image = $slide->guid;
					$slide->url = $slide->guid;
					$slide->url_target = '';
					$slide->link_class = 'thickbox';
					//$slide->link_rel = sprintf("slideshow-%d", $number);
					$slide->link_rel = '';
					
					if ( $slide->post_content != "" )
						$slide->title = stripslashes(htmlspecialchars($slide->post_content));
					elseif ( $slide->post_excerpt != "" )
						$slide->title = stripslashes(htmlspecialchars($slide->post_excerpt));
					else
						$slide->title = htmlspecialchars($slide->name);
					
					$slide->slide_title = stripslashes(htmlspecialchars($slide->post_excerpt));
					$slide->slide_desc = "";
				}
				
				if ( $slide->image != "" )
					$text = sprintf('<img src="%s" alt="%s" title="%s" />', esc_url($slide->image), htmlspecialchars($slide->name), $slide->title);
				else
					$text = $slide->name;
				
				$slideshow_class = '';
				if ( $i == 1 ) $slideshow_class = ' first-slide';
				if ( $i == count($slides) ) $slideshow_class = ' last-slide';
				$out .= '<li id="slideshow-'.$number.'-slide-'.$i.'" class="slide'.$slideshow_class.'" data-cycle-title="'.$slide->slide_title.'" data-cycle-desc="'.$slide->slide_desc.'">';
				
				if ( $slide->url != '' ) {
					$target = ($slide->url_target != "") ? 'target="'.$slide->url_target.'"' : '';
					$out .= sprintf('<a class="%s" href="%s" %s title="%s" rel="%s">%s</a>', $slide->link_class, esc_url($slide->url), $target, $slide->title, $slide->link_rel, $text);
				} else {
					$out .= $text;
				}
				
				// Add Featured Post Layer containing title and excerpt 
				/*if ( $instance['source'] == 'posts' ) {
					$out .= sprintf('<a class="%s" href="%s" %s title="%s" rel="%s">', $slide->link_class, esc_url($slide->url), $target, $slide->title, $slide->link_rel);
					$out .= "<div class='featured-post'>";
					$out .= "<h2 class='featured-post-title'>".get_the_title($slide->ID).'</h2>';
					$out .= "<p class='featured-post-excerpt'>".$this->getPostExcerpt($slide->ID, $instance['post_excerpt_length'])."</p>";
					$out .= "</div>";
					$out .= "</a>";
				}
				
				// Add Image Caption Layer
				if ( $fade != "carousel" && $instance['source'] == 'images' && $slide->post_excerpt != "" ) {
					$out .= "<div class='image-caption'>";
					$out .= "<p>".stripslashes(htmlspecialchars($slide->post_excerpt))."</p>";
					$out .= "</div>";
				}*/
				$out .= '</li>';
			}
			$out .= '</ul>';
			
			
			
			if (isset($instance['show_navigation_arrows']) && $instance['show_navigation_arrows'] == 1)
			$out .= '<a href="#" class="next" id="fancy-slideshow-'.$number.'-next"><span>&gt;</span></a>';
		
			$out .= '</div>';
			
			// Slideshow Button/Thumbnail Navigation
			if (isset($instance['navigation_pager']) && in_array($instance['navigation_pager'], array("buttons", "thumbs")) ) {
				$out .= '<div class="fancy-slideshow-nav-container '.$instance['source'].'"><nav id="fancy-slideshow-nav-'.$number.'" class="fancy-slideshow-nav '.$instance['navigation_pager'].'"></nav></div>';
			}
			$out .= "\n".$this->getSlideshowJavascript( $number, $instance, count($slides) );
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
			'category' => '',
			'width' => "",
			'height' => "",
			'fade' => 'scrollHorz',
			'timeout' => 3,
			'speed' => 3,
			'post_excerpt_length' => 100,
			'show_navigation_arrows' => 1,
			'navigation_pager' => 'buttons',
			'align' => 'aligncenter',
			'box' => 'true',
			'random' => 0,
			'overlay' => 'all',
			'overlay_fx_sel' => 'content',
			'overlay_fade' => 'fade',
			'overlay_style' => 'default',
			'easing' => 'none',
			'carousel_num_slides' => 4
		), $atts ));

		// generate unique ID for shortcode
		$number = uniqid(rand());
		
		$class = array( $align );
		$class[] = ($box == 'true') ? "bounding-box" : "";
		
		// widget parameters
		$args = array(
			'before_widget' => '<div id="fancy-slideshow-shortcode-'.$number.'" class="fancy-slideshow-shortcode '.implode(" ", $class).'">',
			'after_widget' => '</div>',
			'before_title' => '',
			'after_title' => '',
			'number' => $number,
		);
		
		// slideshow parameters
		$instance = array( 'shortcode' => true, 'title' => '', 'category' => htmlspecialchars($category), 'width' => intval($width), 'height' => intval($height), 'fade' => htmlspecialchars($fade), 'timeout' => intval($timeout), 'speed' => intval($speed), 'order' => intval($random), 'post_excerpt_length' => intval($post_excerpt_length), 'show_navigation_arrows' => $show_navigation_arrows, 'navigation_pager' => $navigation_pager, 'overlay' => array('display' => $overlay, 'animate' => $overlay_fx_sel, 'effect' => $overlay_fade, 'style' => $overlay_style), 'easing' => $easing, 'carousel_num_slides' => $carousel_num_slides );
		
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
		if ( !isset($instance['category']) || empty($instance['category']) ) {
			$instance = array( 'source' => 'links', 'category' => '', 'show_navigation_arrows' => 1, 'navigation_pager' => 'buttons', 'post_excerpt_length' => 0,  'num_latest_posts' => 0, 'title' => '', 'width' => '', 'height' => '', 'timeout' => '', 'speed' => '', 'fade' => '', 'easing' => '', 'order' => 0, 'overlay' => array('display' => 'all', 'animate' => 'content', 'effect' => 'slide_up_down', 'style' => 'default'), 'carousel_num_slides' => 4 );
		}
		
		echo '<div class="fancy-slideshow-control">';
		echo '<p><label for="'.$this->get_field_id('source').'">'.__( 'Source', 'sponsors-slideshow' ).'</label>'.$this->sources($instance['category']).'</p>';
		echo '<p><label for="'.$this->get_field_id('title').'">'.__('Title', 'sponsors-slideshow').'</label><input type="text" size="15" name="'.$this->get_field_name('title').'" id="'.$this->get_field_id('title').'" value="'.stripslashes($instance['title']).'" /></p>';
		echo '<p><label for="'.$this->get_field_id('width').'">'.__( 'Width', 'sponsors-slideshow' ).'</label><input type="text" size="3" name="'.$this->get_field_name('width').'" id="'.$this->get_field_id('width').'" value="'.intval($instance['width']).'" /> px</p>';
		echo '<p><label for="'.$this->get_field_id('height').'">'.__( 'Height', 'sponsors-slideshow' ).'</label><input type="text" size="3" name="'.$this->get_field_name('height').'" id="'.$this->get_field_id('height').'" value="'.intval($instance['height']).'" /> px</p>';
		echo '<p><label for="'.$this->get_field_id('timeout').'">'.__( 'Timeout', 'sponsors-slideshow' ).'</label><input type="text" name="'.$this->get_field_name('timeout').'" id="'.$this->get_field_id('timeout').'" size="3" value="'.(float)$instance['timeout'].'" /> '.__( 'seconds','sponsors-slideshow').'</p>';
		echo '<p><label for="'.$this->get_field_id('speed').'">'.__( 'Speed', 'sponsors-slideshow' ).'</label><input type="text" name="'.$this->get_field_name('speed').'" id="'.$this->get_field_id('speed').'" size="3" value="'.(float)$instance['speed'].'" /> '.__( 'seconds', 'sponsors-slideshow').'</p>';
		echo '<p><label for="'.$this->get_field_id('fade').'">'.__( 'Fade Effect', 'sponsors-slideshow' ).'</label>'.$this->fadeEffects($instance['fade']).'</p>';
		if ( $instance['fade'] == "carousel" ) {
			echo '<p><label for="'.$this->get_field_id('carousel_num_slides').'">'.__( 'Show', 'sponsors-slideshow' ).'</label><input type="text" name="'.$this->get_field_name('carousel_num_slides').'" id="'.$this->get_field_id('carousel_num_slides').'" size="3" value="'.$instance['carousel_num_slides'].'" /> '.__('slides', 'sponsors-slideshow').'</p>';
		} else {
			echo '<input type="hidden" name="'.$this->get_field_name('carousel_num_slides').'" id="'.$this->get_field_id('carousel_num_slides').'" value="'.$instance['carousel_num_slides'].'" />';
		}
		echo '<p><label for="'.$this->get_field_id('easing').'">'.__( 'Easing Effect', 'sponsors-slideshow' ).'</label>'.$this->easingEffects($instance['easing']).'</p>';
		echo '<p><label for="'.$this->get_field_id('order').'">'.__('Order','sponsors-slideshow').'</label>'.$this->order($instance['order']).'</p>';
		$checked_arrows = (isset($instance['show_navigation_arrows']) && $instance['show_navigation_arrows'] == 1) ? ' checked="checked"' : '';
		echo '<p><label class="checkbox" for="'.$this->get_field_id('show_navigation_arrows').'">'.__('Navigation Arrows','sponsors-slideshow').'</label><input type="checkbox" name="'.$this->get_field_name('show_navigation_arrows').'" id="'.$this->get_field_id('show_navigation_arrows').'" value="1"'.$checked_arrows.' /></p>';
		$checked_pager_none = (isset($instance['navigation_pager']) && $instance['navigation_pager'] == 'none') ? ' checked="checked"' : '';
		$checked_pager_buttons = (isset($instance['navigation_pager']) && $instance['navigation_pager'] == 'buttons') ? ' checked="checked"' : '';
		$checked_pager_thumbs = (isset($instance['navigation_pager']) && $instance['navigation_pager'] == 'thumbs') ? ' checked="checked"' : '';
		echo '<label class="radio" for="'.$this->get_field_id('navigation_pager_none').'">'.__('Pager','sponsors-slideshow').'</label><ul class="radio"><li><input type="radio" name="'.$this->get_field_name('navigation_pager').'" id="'.$this->get_field_id('navigation_pager_none').'" value="none"'.$checked_pager_none.' /><label class="right" for="'.$this->get_field_id('navigation_pager_none').'">'.__('Hide','sponsors-slideshow').'</label></li><li class="left"><input type="radio" name="'.$this->get_field_name('navigation_pager').'" id="'.$this->get_field_id('navigation_pager_buttons').'" value="buttons"'.$checked_pager_buttons.' /><label class="right" for="'.$this->get_field_id('navigation_pager_buttons').'">'.__('Buttons','sponsors-slideshow').'</label></li><li class="left"><input type="radio" name="'.$this->get_field_name('navigation_pager').'" id="'.$this->get_field_id('navigation_pager_thumbs').'" value="thumbs"'.$checked_pager_thumbs.' /><label class="right" for="'.$this->get_field_id('navigation_pager_thumbs').'">'.__('Thumbnails','sponsors-slideshow').'</label></li></ul></p>';
		echo '<p><label for="'.$this->get_field_id('post_excerpt_length').'">'.__( 'Post Excerpt', 'sponsors-slideshow' ).'</label><input type="text" name="'.$this->get_field_name('post_excerpt_length').'" id="'.$this->get_field_id('post_excerpt_length').'" value="'.intval($instance['post_excerpt_length']).'" size="5" /> '.__('words', 'sponsors-slideshow').'</p>';
		echo '<h4 class="slide-overlay">'.__( 'Slide Overlay', 'sponsors-slideshow' ).'</h4>';
		echo '<p><label for="'.$this->get_field_id('overlay_display').'">'.__('Display','sponsors-slideshow').'</label>'.$this->overlayDisplay($instance['overlay']['display']).'</p>';
		echo '<p><label for="'.$this->get_field_id('overlay_effect').'">'.__('Fade Effect','sponsors-slideshow').'</label>'.$this->overlayEffects($instance['overlay']['effect']).'</p>';
		echo '<p><label for="'.$this->get_field_id('overlay_animate').'">'.__('Animate','sponsors-slideshow').'</label>'.$this->overlayAnimate($instance['overlay']['animate']).'</p>';
		echo '<p><label for="'.$this->get_field_id('overlay_style').'">'.__('Style','sponsors-slideshow').'</label>'.$this->overlayStyles($instance['overlay']['style']).'</p>';
		echo '</div>';
	}


	/**
	 * display sources as drop down list
	 *
	 * @param string $selected selected category
	 * @param string $field_name
	 * @param string $field_id
	 * @return select element of categories
	 */
	function sources( $selected, $field_name = "", $field_id = "" )
	{
		if ( $field_name == "" ) $field_name = $this->get_field_name("category");
		if ( $field_id == "") $field_id = $this->get_field_id("category");
		
		$terms = array( "link_category" => array("label" => "Links", "source" => "links"), "category" => array("label" => "Posts", "source" => "posts"), "page_category" => array("label" => "Pages", "source" => "pages"), "gallery" => array("label" => "Images", "source" => "images"), "latest_posts" => array("label" => "Latest Posts", "source" => "posts") );
		
		$categories = array();
		foreach ($terms AS $term => $data) {
			if ( $term == "latest_posts" ) {
				// Add special category for latest posts
				$categories[$term] = array( "title" => __($data["label"], 'sponsors-slideshow'), "options" => array() );
				for ($i = 1; $i <= 15; $i++) {
					$categories[$term]["options"][] = array( "value" => $data["source"]."_latest_".$i, "label" => sprintf(__('Latest %d posts', 'sponsors-slideshow'), $i) );
				}
			} else {
				$cat = get_terms($term, 'orderby=name&hide_empty=0');
				if (!empty($cat)) {
					$categories[$term] = array( "title" => __($data["label"], 'sponsors-slideshow'), "options" => array() );
					foreach ( $cat as $category ) {
						$cat_id = $category->term_id;
						$categories[$term]["options"][] = array( "value" => $data["source"]."_".$term."_".$cat_id, "label" => htmlspecialchars( apply_filters('the_category', $category->name)) );
					}
				}
			}
		}
		
		$categories = apply_filters( 'fancy_slideshow_sources', $categories );
		
		$out = '<select size="1" name="'.$field_name.'" id="'.$field_id.'">';
		if ( count($categories) ) {
			foreach ( $categories AS $term => $category ) {
				$out .= '<optgroup label="'.$category['title'].'">';
				foreach ( $category["options"] AS $option ) {
					$sel = ( $option["value"] == $selected ) ? ' selected="selected"' : '';
					$out .= '<option value="'.$option["value"].'"'.$sel.'>'.$option["label"]. '</option>';
				}
				$out .= '</optgroup>';
			}
		}
		$out .= '</select>';
	
		return $out;
	}
	
	
	/**
	 * drop down list of order possibilities
	 *
	 * @param string $selected current order
	 * @param string $field_name
	 * @param string $field_id
	 * @return order selection
	 */
	function order( $selected, $field_name = "", $field_id = "" )
	{
		if ( $field_name == "" ) $field_name = $this->get_field_name("order");
		if ( $field_id == "") $field_id = $this->get_field_id("order");
		
		$order = array(__('Ordered','sponsors-slideshow') => '0', __('Random','sponsors-slideshow') => '1');
		$out = '<select size="1" name="'.$field_name.'" id="'.$field_id.'">';
		foreach ( $order AS $name => $value ) {
			$checked =  ( $selected == $value ) ? " selected='selected'" : '';
			$out .= '<option value="'.$value.'"'.$checked.'>'.$name.'</option>';
		}
		$out .= '</select>';
		return $out;
	}

	
	/**
	 * drop down list of overlay display
	 *
	 * @param string $selected current display
	 * @param string $field_name
	 * @param string $field_id
	 * @return order selection
	 */
	function overlayDisplay( $selected, $field_name = "", $field_id = "" )
	{
		if ( $field_name == "" ) $field_name = $this->get_field_name("overlay][display");
		if ( $field_id == "") $field_id = $this->get_field_id("overlay_display");
	
		$display = array( 'none' => __('No Overlay','sponsors-slideshow'), 'title' => __('Title','sponsors-slideshow'), 'all' => __('Title & Description', 'sponsors-slideshow') );
		$out = '<select size="1" name="'.$field_name.'" id="'.$field_id.'">';
		foreach ( $display AS $value => $name ) {
			$checked =  ( $selected == $value ) ? " selected='selected'" : '';
			$out .= '<option value="'.$value.'"'.$checked.'>'.$name.'</option>';
		}
		$out .= '</select>';
		return $out;
	}
	
	
	/**
	 * drop down list of overlay display
	 *
	 * @param string $selected current animation
	 * @param string $field_name
	 * @param string $field_id
	 * @return order selection
	 */
	function overlayAnimate( $selected, $field_name = "", $field_id = "" )
	{
		if ( $field_name == "" ) $field_name = $this->get_field_name("overlay][animate");
		if ( $field_id == "") $field_id = $this->get_field_id("overlay_animate");
	
		$animations = array( 'overlay' => __('Overlay Box','sponsors-slideshow'), 'content' => __('Overlay Content','sponsors-slideshow') );
		$out = '<select size="1" name="'.$field_name.'" id="'.$field_id.'">';
		foreach ( $animations AS $value => $name ) {
			$checked =  ( $selected == $value ) ? " selected='selected'" : '';
			$out .= '<option value="'.$value.'"'.$checked.'>'.$name.'</option>';
		}
		$out .= '</select>';
		return $out;
	}
	
	
	/**
	 * drop down list of overlay display
	 *
	 * @param string $selected current effect
	 * @param string $field_name
	 * @param string $field_id
	 * @return order selection
	 */
	function overlayEffects( $selected, $field_name = "", $field_id = "" )
	{
		if ( $field_name == "" ) $field_name = $this->get_field_name("overlay][effect");
		if ( $field_id == "") $field_id = $this->get_field_id("overlay_effect");
	
		$effects = array( 'none' => __('None','sponsors-slideshow'), 'fade' => __('Fade','sponsors-slideshow'), 'slide_up_down' => __('Slide Up & Down', 'sponsors-slideshow') );
		$out = '<select size="1" name="'.$field_name.'" id="'.$field_id.'">';
		foreach ( $effects AS $value => $name ) {
			$checked =  ( $selected == $value ) ? " selected='selected'" : '';
			$out .= '<option value="'.$value.'"'.$checked.'>'.$name.'</option>';
		}
		$out .= '</select>';
		return $out;
	}
	
	
	/**
	 * drop down list of overlay display styles
	 *
	 * @param string $selected current style
	 * @param string $field_name
	 * @param string $field_id
	 * @return order selection
	 */
	function overlayStyles( $selected, $field_name = "", $field_id = "" )
	{
		if ( $field_name == "" ) $field_name = $this->get_field_name("overlay][style");
		if ( $field_id == "") $field_id = $this->get_field_id("overlay_style");
	
		$styles = array( 'default' => __('Default','sponsors-slideshow'), 'fancy' => __('Fancy','sponsors-slideshow') );
		$styles = apply_filters( 'fancy_slideshow_overlay_styles', $styles );
		
		$out = '<select size="1" name="'.$field_name.'" id="'.$field_id.'">';
		foreach ( $styles AS $value => $name ) {
			$checked =  ( $selected == $value ) ? " selected='selected'" : '';
			$out .= '<option value="'.$value.'"'.$checked.'>'.$name.'</option>';
		}
		$out .= '</select>';
		return $out;
	}
	
	
	/**
	* drop down list of available fade effects
	*
	* @param string $field_name
	* @param string $field_id
	* @return order selection
	*/
	function fadeEffects( $selected, $field_name = "", $field_id = "" )
	{
		if ( $field_name == "" ) $field_name = $this->get_field_name("fade");
		if ( $field_id == "") $field_id = $this->get_field_id("fade");
		
		$effects = array(
			'fade' => __('Fade','sponsors-slideshow'),
			'fadeout' => __('Fadeout', 'sponsors-slideshow'),
			'scrollHorz' => __('Scroll Horizontal', 'sponsors-slideshow'),
			'scrollVert' => __('Scroll Vertical', 'sponsors-slideshow'),
			'flipHorz' => __('Flip Horizontal', 'sponsors-slideshow'),
			'flipVert' => __('Flip Vertical', 'sponsors-slideshow'),
			'shuffle' => __('Shuffle','sponsors-slideshow'),
			'tileSlide' => __('Tile Slide', 'sponsors-slideshow'),
			'tileSlide_horz' => __('Tile Slide Horizontal', 'sponsors-slideshow'),
			'tileBlind' => __('Tile Blind', 'sponsors-slideshow'),
			'tileBlind_horz' => __('Tile Blind Horizontal', 'sponsors-slideshow'),
			'carousel' => __('Carousel', 'sponsors-slideshow')
		);
		
		$out = '<select size="1" name="'.$field_name.'" id="'.$field_id.'">';
		foreach ( $effects AS $effect => $name ) {
			$checked =  ( $selected == $effect ) ? " selected='selected'" : '';
			$out .= '<option value="'.$effect.'"'.$checked.'>'.$name.'</option>';
		}
		$out .= '</select>';
		return $out;
	}

	
	/**
	 * get easing effects
	 *
	 * @param string $field_name
	 * @param string $field_id
	 * @return order selection
	 */
	function easingEffects( $selected, $field_name = "", $field_id = "" )
	{
		if ( $field_name == "" ) $field_name = $this->get_field_name("easing");
		if ( $field_id == "") $field_id = $this->get_field_id("easing");
		
		$effects = array(
			'none' => __( 'None', 'sponsors-slideshow' ),
			'swing' => __( 'Swing', 'sponsors-slideshow' ),
			'easeInQuad' => __( 'Ease In Quad', 'sponsors-slideshow' ),
			'easeOutQuad' => __( 'Ease Out Quad', 'sponsors-slideshow' ),
			'easeInOutQuad' => __( 'Ease In Out Quad', 'sponsors-slideshow' ),
			'easeInCubic' => __( 'Ease In Cubic', 'sponsors-slideshow' ),
			'easeOutCubic' => __( 'Ease Out Cubic', 'sponsors-slideshow' ),
			'easeInOutCubic' => __( 'Ease In Out Cubic', 'sponsors-slideshow' ),
			'easeInQuart' => __( 'Ease In Quart', 'sponsors-slideshow' ),
			'easeOutQuart' => __( 'Ease Out Quart', 'sponsors-slideshow' ),
			'easeInOutQuart' => __( 'Ease In Out Quart', 'sponsors-slideshow' ),
			'easeInQuint' => __( 'Ease In Quint', 'sponsors-slideshow' ),
			'easeOutQuint' => __( 'Ease Out Quint', 'sponsors-slideshow' ),
			'easeInOutQuint' => __( 'Ease In Out Quint', 'sponsors-slideshow' ),
			'easeInSine' => __( 'Ease In Sine', 'sponsors-slideshow' ),
			'easeOutSine' => __( 'Ease Out Sine', 'sponsors-slideshow' ),
			'easeInOutSine' => __( 'Ease In Out Sine', 'sponsors-slideshow' ),
			'easeInExpo' => __( 'Ease In Expo', 'sponsors-slideshow' ),
			'easeOutExpo' => __( 'Ease Out Expo', 'sponsors-slideshow' ),
			'easeInOutExpo' => __( 'Ease In Out Expo', 'sponsors-slideshow' ),
			'easeInCirc' => __( 'Ease In Circ', 'sponsors-slideshow' ),
			'easeOutCirc' => __( 'Ease Out Circ', 'sponsors-slideshow' ),
			'easeInOutCirc' => __( 'Ease In Out Circ', 'sponsors-slideshow' ),
			'easeInElastic' => __( 'Ease In Elastic', 'sponsors-slideshow' ),
			'easeOutElastic' => __( 'Ease Out Elastic', 'sponsors-slideshow' ),
			'easeInOutElastic' => __( 'Ease In Out Elastic', 'sponsors-slideshow' ),
			'easeInBack' => __( 'Ease In Back', 'sponsors-slideshow' ),
			'easeOutBack' => __( 'Ease Out Back', 'sponsors-slideshow' ),
			'easeInOutBack' => __( 'Ease In Out Back', 'sponsors-slideshow' ),
			'easeInBounce' => __( 'Ease In Bounce', 'sponsors-slideshow' ),
			'easeOutBounce' => __( 'Ease Out Bounce', 'sponsors-slideshow' ),
			'easeInOutBounce' => __( 'Ease In Out Bounce', 'sponsors-slideshow' )
		);
		
		$out = '<select size="1" name="'.$field_name.'" id="'.$field_id.'">';
		foreach ( $effects AS $effect => $name ) {
			$checked =  ( $selected == $effect ) ? " selected='selected'" : '';
			$out .= '<option value="'.$effect.'"'.$checked.'>'.$name.'</option>';
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
		wp_enqueue_style( 'fancy-slideshow', $this->plugin_url.'style.css', array(), $this->version, 'all' );
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
		
		wp_enqueue_style( 'fancy-slideshow', $this->plugin_url.'style.css', array(), $this->version, 'all' );
		//wp_enqueue_script( 'jquery_slideshow', $this->plugin_url.'js/jquery.cycle.all.js', array('jquery', 'thickbox'), '2.65' );
		wp_enqueue_script( 'jquery_cycle2', $this->plugin_url.'js/jquery.cycle2.min.js', array('jquery', 'thickbox'), '2.65' );
		wp_enqueue_script( 'jquery_cycle2_carousel', $this->plugin_url.'js/jquery.cycle2.carousel.min.js', array('jquery_cycle2'), '2.65' );
		wp_enqueue_script( 'jquery_cycle2_flip', $this->plugin_url.'js/jquery.cycle2.flip.min.js', array('jquery_cycle2'), '2.65' );
		wp_enqueue_script( 'jquery_cycle2_scrollVert', $this->plugin_url.'js/jquery.cycle2.scrollVert.min.js', array('jquery_cycle2'), '2.65' );
		wp_enqueue_script( 'jquery_cycle2_shuffle', $this->plugin_url.'js/jquery.cycle2.shuffle.min.js', array('jquery_cycle2'), '2.65' );
		wp_enqueue_script( 'jquery_cycle2_tile', $this->plugin_url.'js/jquery.cycle2.tile.min.js', array('jquery_cycle2'), '2.65' );
		wp_enqueue_script( 'jquery_cycle2_caption2', $this->plugin_url.'js/jquery.cycle2.caption2.min.js', array('jquery_cycle2'), '2.65' );
		wp_enqueue_script( 'jquery_easing', $this->plugin_url.'js/jquery.easing.1.3.js', array('jquery_cycle2', 'jquery_cycle2_shuffle'), '2.65' );

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
			$css .= "#fancy-slideshow-".$number."-container, #fancy-slideshow-".$number.", #fancy-slideshow-".$number."-container img, #fancy-slideshow-shortcode-".$number." { ";
			if (intval($instance['height']) > 0) {
				$css .= "height: ".intval($instance['height'])."px;";
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
				//$css .= "#fancy-slideshow-".$number." .fancy-slideshow-container .next, #fancy-slideshow-".$number." .fancy-slideshow-container .prev {";
				//$css .= "top: ".intval($instance['height']-20)/2 ."px;";
				//$css .= "}";
			}
		}
		return $css;
	}
	
	
	/**
	 * get slideshow Javascript code
	 *
	 * @param int $number
	 * @param array instance
	 * @param int $num_slides
	 */
	function getSlideshowJavascript( $number, $instance, $num_slides )
	{
		if ( $num_slides == 2 ) $mar_thumbs = 1;
		if ( $num_slides > 2 ) $mar_thumbs = $num_slides/(($num_slides-1)*2);
		ob_start();
		?>
		<script type='text/javascript'>
				var $jq = jQuery.noConflict();
				$jq(document).ready(function() {
					// Make overflow of slideshow container visible
					$jq("#fancy-slideshow-<?php echo $number ?>-container").css("overflow", "visible");
					// Show navigation pager
					$jq("#fancy-slideshow-nav-<?php echo $number ?>").css("display", "inline-block");
					
					// fade-in navigation arrows on hover of slideshow container
					$jq("#fancy-slideshow-<?php echo $number ?>-container").hover(
						function() {
							$jq("#fancy-slideshow-<?php echo $number ?>-next").fadeIn("slow");
							$jq("#fancy-slideshow-<?php echo $number ?>-prev").fadeIn("slow");
						}
					);
					
					// fade-out navigation arrows when mouse leaves slideshow container
					$jq("#fancy-slideshow-<?php echo $number ?>-container").mouseleave(
						function() {
							$jq("#fancy-slideshow-<?php echo $number ?>-next").fadeOut("slow");
							$jq("#fancy-slideshow-<?php echo $number ?>-prev").fadeOut("slow");
						}
					);
				});

				
				jQuery('.cycle-slideshow').on('cycle-bootstrap', function(e, opts, API) {
					// add a new method to the C2 API:
					API.customGetImageSrc = function( slideOpts, opts, slideEl ) {
						return jQuery( slideEl ).find('img').attr('src');
					},
					// add a new method to the C2 API:
					API.customGetImageClass = function( slideOpts, opts, slideEl ) {
						return jQuery( slideEl ).attr('class');
					}
				});
				
				// Run Slideshow
				/*
				jQuery('#fancy-slideshow-<?php echo $number ?>').cycle({
					fx: '<?php echo $instance['fade']; ?>',
					timeout: <?php echo (float)$instance['timeout'] * 1000; ?>,
					next: '#fancy-slideshow-<?php echo $number ?>-next',
					prev: '#fancy-slideshow-<?php echo $number ?>-prev',
					speed: <?php echo (float)$instance['speed'] * 1000; ?>,
					random: <?php echo intval($instance['order']); ?>,
					pager: '#fancy-slideshow-nav-<?php echo $number ?>',
					pause: 1,
					resume: 1,
					// callback fn that creates a thumbnail to use as pager anchor 
					pagerAnchorBuilder: function(idx, slide) {
						<?php if ( $instance['navigation_pager'] == "thumbs" ) : ?>
						return '<a href="#" style="width: <?php echo 100/$num_slides ?>%;" class="' + jQuery(slide).attr('class') + '"><img src="' + jQuery(slide).find('img').attr('src') + '" style="width: <?php echo (100/$num_slides)-1 ?>%; margin: 0 <?php echo $mar_thumbs ?>%;" /></a>';
						<?php endif; ?>
						
						<?php if ( $instance['navigation_pager'] == "buttons" ) : ?>
						return '<a href="#"></a>';
						<?php endif; ?>
					},
				});
				*/
			</script>
			<?php
			$out = ob_get_contents();
			ob_end_clean();
			return $out;
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
				$cat = explode("_", $option['category']);
				$option["source"] = $cat[0];
				// exclude only categories from links source
				if ( $option['source'] == 'links' ) {
					$excludes[] = $cat[3];
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
				$cat = explode("_", $option['category']);
				$option["source"] = $cat[0];
				// exclude categories from widget only if source is images
				if ($option['source'] == 'images') {
					if ( isset($cat[2]) && !in_array($cat[2], $excludes) )
						$excludes[] = $cat[2];
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
				$cat = explode("_", $option['category']);
				$option["source"] = $cat[0];
				if ($option['source'] == 'posts') {
					// Exclude n latest posts or posts from selected category
					if ($cat[1] == 'latest') {
						$num[] = intval($cat[2]);
					} else {
						$cat_ids[] = "-".$cat[2];
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
	 * retrieve base url from string
	 *
	 * @param string $url
	 * @return string
	 */
	function getBaseURL( $url )
	{
		preg_match("/^https?:\/\/(.+?)\/.+/", $url, $matches);
		
		if ( isset($matches[1]) )
			return $matches[1];
		
		return false;
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
		$plugin_array['SponsorsSlideshow'] = $this->plugin_url.'tinymce/editor_plugin.js';
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
	
	/**
	 * Display the TinyMCE Window.
	 *
	 */
	function showTinyMCEWindow() {
		require_once( $this->plugin_path . '/tinymce/window.php' );
		exit;
	}
	
	/**
	 * add post meta box
	 *
	 */
	function addMetaboxPost()
	{
		add_meta_box( 'fancy-slideshow', __('Slideshow Overlay','sponsors-slideshow'), array(&$this, 'displayMetabox'), 'post' );
	}
	/**
	 * add page meta box
	 *
	 */
	function addMetaboxPage()
	{
		add_meta_box( 'fancy-slideshow', __('Slideshow Overlay','sponsors-slideshow'), array(&$this, 'displayMetabox'), 'page' );
	}
	
	/**
	 * diplay post/page meta box
	 *
	 * @param object $post
	 */
	function displayMetabox( $post )
	{
		global $post_ID;
		
		if ( $post->ID != 0 ) {
			$slide_title = stripslashes(get_post_meta( $post->ID, 'fancy_slideshow_overlay_title', true ));
			$slide_description = stripslashes(get_post_meta( $post->ID, 'fancy_slideshow_overlay_description', true ));
		} else {
			$slide_title = "";
			$slide_description = "";
		}
		
		echo "<div class='fancy-slideshow-post-meta'>";
		echo "<p><label for='fancy_slideshow_overlay_title'>".__( 'Title', 'sponsors-slideshow' )."</label><input type='text' name='fancy_slideshow_overlay_title' id='fancy_slideshow_overlay_title' value='".$slide_title."' /></p>";
		echo "<p><label for='fancy_slideshow_overlay_description'>".__( 'Description', 'sponsors-slideshow' )."</label><textarea rows='4' name='fancy_slideshow_overlay_description' id='fancy_slideshow_overlay_description'>".$slide_description."</textarea></p>";
		echo "<p>".__( 'These slideshow overlay settings are optional. If empty, the post/page title and excerpt will be used as overlay', 'sponsors-slideshow' )."</p>";
		echo "</div>";
	}
	
	
	/**
	 * edit post/page meta data
	 *
	 * @param
	 */
	function editPostMeta()
	{
		if (isset($_POST['post_ID'])) {
			$post_ID = intval($_POST['post_ID']);
			$slide_title = htmlspecialchars(strip_shortcodes(strip_tags($_POST['fancy_slideshow_overlay_title'])));
			$slide_description = htmlspecialchars(strip_shortcodes(strip_tags($_POST['fancy_slideshow_overlay_description'])));
			
			update_post_meta( $post_ID, 'fancy_slideshow_overlay_title', $slide_title );
			update_post_meta( $post_ID, 'fancy_slideshow_overlay_description', $slide_description );
		}
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
 * @param array $args basic arguments including before_widget, after_widget, before_title, after_title and number (unique widget number)
 * @param array $instance settings for this instance. See list below for parameters
 *
 * This function can be used to display Sponsors Slideshow Widget in a Non-widgetized Theme.
 * Below is a list of needed arguments passed as an associative Array in $instance
 *
 * - source: slideshow source, "links", "posts", "images"
 * - category: term and category, e.g. link_category_ID, category_ID, latest_N, where ID or N are the category ID or number of latest posts
 * - title: Widget title, if left empty no title will be displayed
 * - fade: Fade effect, see http://malsup.com/jquery/cycle/begin.html for a list of available effects
 * - timeout: Time in seconds between images
 * - speed: slideshow speed in seconds
 * - width: width in px of the Slideshow
 * - height: height in px  of the Slideshow
 * - order: 0 for sequential, 1 for random ordering of links
 * - show_navigation_arrows: 0 or 1 to control display of navigation arrows
 * - show_pager: 0 or 1 to control display of pager navigation
 */
function sponsors_slideshow_widget_display( $args = array(), $instance = array() ) {
	$sponsors_slideshow_widget = new SponsorsSlideshowWidget();
	$sponsors_slideshow_widget->widget( $args, $instance );
}
function fancy_slideshow( $args = array(), $instance = array() ) {
	$sponsors_slideshow_widget = new SponsorsSlideshowWidget();
	$sponsors_slideshow_widget->widget( $args, $instance );
}

?>