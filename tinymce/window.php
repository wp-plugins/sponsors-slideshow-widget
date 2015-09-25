<?php

$root = dirname(dirname(dirname(dirname(dirname(__FILE__)))));

if (file_exists($root.'/wp-load.php')) {
	// WP 2.6
	require_once($root.'/wp-load.php');
} else {
	// Before 2.6
	if (!file_exists($root.'/wp-config.php'))  {
		echo "Could not find wp-config.php";	
		die;	
	}// stop when wp-config is not there
	require_once($root.'/wp-config.php');
}

require_once(ABSPATH.'/wp-admin/admin.php');

load_plugin_textdomain( 'sponsors-slideshow', false, ABSPATH.'/wp-content/plugins/sponsors-slideshow-widget/languages' );

$sources = array( 'links' => __('Links', 'sponsors-slideshow'), 'images' => __('Images', 'sponsors-slideshow'), 'posts' => __('Posts', 'sponsors-slideshow') );
$terms = array("Links" => "link_category", "Posts" => "category", "Images" => "gallery");
$effects = array( __('Blind X','sponsors-slideshow') => 'blindX', __('Blind Y','sponsors-slideshow') => 'blindY', __('Blind Z','sponsors-slideshow') => 'blindZ', __('Cover','sponsors-slideshow') => 'cover', __('Curtain X','sponsors-slideshow') => 'curtainX', __('Curtain Y','sponsors-slideshow') => 'curtain>', __('Fade','sponsors-slideshow') => 'fade', __('Fade Zoom','sponsors-slideshow') => 'fadeZoom', __('Scroll Up','sponsors-slideshow') => 'scrollUp', __('Scroll Left','sponsors-slideshow') => 'scrollLeft', __('Scroll Right','sponsors-slideshow') => 'scrollRight', __('Scroll Down','sponsors-slideshow') => 'scrollDown', __('Scroll Horizontal', 'sponsors-slideshow') => 'scrollHorz', __('Scroll Vertical', 'sponsors-slideshow') => 'scrotllVert', __('Shuffle','sponsors-slideshow') => 'shuffle', __('Slide X','sponsors-slideshow') => 'slideX', __('Slide Y','sponsors-slideshow') => 'slideY', __('Toss','sponsors-slideshow') => 'toss', __('Turn Up','sponsors-slideshow') => 'turnUp', __('Turn Down','sponsors-slideshow') => 'turnDown', __('Turn Left','sponsors-slideshow') => 'turnLeft', __('Turn Right','sponsors-slideshow') => 'turnRight', __('Uncover','sponsors-slideshow') => 'uncover', __('Wipe','sponsors-slideshow') => 'wipe', __( 'Zoom','sponsors-slideshow') => 'zoom', __('Grow X','sponsors-slideshow') => 'growX', __('Grow Y','sponsors-slideshow') => 'growY', __('Random','sponsors-slideshow') => 'all');
$order = array(__('Ordered','sponsors-slideshow') => '0', __('Random','sponsors-slideshow') => '1');


// check for rights
if(!current_user_can('edit_posts')) die;

global $wpdb;

?>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title><?php _e('Slideshow', 'sponsors-slideshow') ?></title>
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/utils/mctabs.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/utils/form_utils.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-content/plugins/sponsors-slideshow-widget/tinymce/tinymce.js"></script>
	<base target="_self" />
	
</head>
<body id="link" onload="tinyMCEPopup.executeOnLoad('init();');document.body.style.display='';" style="display: none">
<!-- <form onsubmit="insertLink();return false;" action="#"> -->
	<form name="SponsorsSlideshowTinyMCE" action="#">
	<div class="tabs">
		<!--<ul>
			<li id="slideshow_tab" class="current"><span><a href="javascript:mcTabs.displayTab('slideshow_tab', 'slideshow_panel');" onmouseover="return false;"><?php _e( 'Slideshow', 'projectmanager' ); ?></a></span></li>
		</ul>-->
	</div>
	<div class="panel_wrapper" style="height: 210px;">
		
		<!-- slideshow panel -->
		<div id="slideshow_panel" class="panel current">
		<table style="border: 0;">
		<tr>
			<td><label for="source"><?php _e("Source", 'sponsors-slideshow'); ?></label></td>
			<td>
				<select size='1' name='source' id='source'>
				<?php foreach ( $sources AS $source => $name ) : ?>
					<option value="<?php echo $source ?>"><?php echo $name ?></option>';
				<?php endforeach; ?>
				</select>
				
				<select size='1' name='category' id='category'>
					<?php foreach ($terms AS $label => $term) : $categories = get_terms($term, 'orderby=name&hide_empty=0'); ?>
					<optgroup label="<?php _e($label, 'sponsors-slideshow') ?>">
					<?php if (!empty($categories)) : ?>
						<?php foreach ( $categories as $category ) : 
								$cat_id = $category->term_id; 
								$name = htmlspecialchars( apply_filters('the_category', $category->name));
						?>
						<option value="<?php echo $term."_".$cat_id ?>"><?php echo $name ?></option>
						<?php endforeach; ?>
					<?php endif; ?>
					</optgroup>
					<?php endforeach; ?>
				
					<optgroup label="<?php _e('Latest Posts', 'sponsors-slideshow') ?>">
					<?php for ($i = 1; $i <= 15; $i++) : ?>
						<option value="<?php echo 'latest_'.$i ?>"><?php printf(__('Latest %d posts', 'sponsors-slideshow'), $i) ?></option>
					<?php endfor; ?>
					</optgroup>
				</select>
				
				<select size="1" name="order" id="order">
				<?php foreach ( $order AS $name => $value ) : ?>
					<option value="<?php echo $value ?>"><?php echo $name ?></option>
				<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<!--<tr>
			<td><label for="category"><?php _e("Category", 'sponsors-slideshow'); ?></label></td>
			<td>
				<select size='1' name='category' id='category'>
					<?php foreach ($terms AS $label => $term) : $categories = get_terms($term, 'orderby=name&hide_empty=0'); ?>
					<optgroup label="<?php _e($label, 'sponsors-slideshow') ?>">
					<?php if (!empty($categories)) : ?>
						<?php foreach ( $categories as $category ) : 
								$cat_id = $category->term_id; 
								$name = htmlspecialchars( apply_filters('the_category', $category->name));
						?>
						<option value="<?php echo $term."_".$cat_id ?>"><?php echo $name ?></option>
						<?php endforeach; ?>
					<?php endif; ?>
					</optgroup>
					<?php endforeach; ?>
			
					<optgroup label="<?php _e('Latest Posts', 'sponsors-slideshow') ?>">
					<?php for ($i = 1; $i <= 15; $i++) : ?>
						<option value="<?php echo 'latest_'.$i ?>"><?php printf(__('Latest %d posts', 'sponsors-slideshow'), $i) ?></option>
					<?php endfor; ?>
					</optgroup>
				</select>
				<select size="1" name="order" id="order">
				<?php foreach ( $order AS $name => $value ) : ?>
					<option value="<?php echo $value ?>"><?php echo $name ?></option>
				<?php endforeach; ?>
				</select>
			</td>
		</tr>-->
		<tr>
			<td><label for="width"><?php _e('Width', 'sponsors-slideshow') ?> x <?php _e('Height', 'sponsors-slideshow') ?></label></td>
			<td><input type="text" name="width" id="width" size="4" /> x <input type="text" name="height" id="height" size="4" /> px</td>
		</tr>
		<tr>
			<td><label for="timeout"><?php _e('Timeout', 'sponsors-slideshow') ?></label></td>
			<td><input type="text" name="timeout" id="timeout" size="4" /> <?php _e('seconds', 'sponsors-slideshow') ?></td>
		</tr>
		<tr>
			<td><label for="speed"><?php _e('Speed', 'sponsors-slideshow') ?></label></td>
			<td><input type="text" name="speed" id="speed" size="4" /> <?php _e('seconds', 'sponsors-slideshow') ?></td>
		</tr>
		<tr>
			<td><label for="fade"><?php _e('Fade Effect', 'sponsors-slideshow') ?></label></td>
			<td>
				<select size="1" name="fade" id="fade">
				<?php foreach ( $effects AS $name => $effect ) : ?>
					<option value="<?php echo $effect ?>"><?php echo $name ?></option>
				<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<!--<tr>
			<td><label for="order"><?php _e('Order', 'sponsors-slideshow') ?></label></td>
			<td>
				<select size="1" name="order" id="order">
				<?php foreach ( $order AS $name => $value ) : ?>
					<option value="<?php echo $value ?>"><?php echo $name ?></option>
				<?php endforeach; ?>
				</select>
			</td>
		</tr>-->
		<tr>
			<td><label for="navigation_arrows"><?php _e('Navigation Arrows', 'sponsors-slideshow') ?></label></td>
			<td>
				<input type="checkbox" checked="checked" value="1" name="navigation_arrows" id="navigation_arrows" />
				<label for="navigation_pager"><?php _e('Pager', 'sponsors-slideshow') ?></label>
				<input type="checkbox" checked="checked" value="1" name="navigation_pager" id="navigation_pager" />
				<label for="bounding_box"><?php _e('Bounding Box', 'sponsors-slideshow') ?></label>
				<input type="checkbox" checked="checked" value="1" name="bounding_box" id="bounding_box" />
			</td>
		</tr>
		<tr>
			<td><label for="alignment"><?php _e('Alignment','sponsors-slideshow') ?></td>
			<td>
				<select size="1" name="alignment" id="alignment">
					<option value="alignleft"><?php _e('Floating Left', 'sponsors-slideshow') ?></option>
					<option value="aligncenter" selected="selected"><?php _e('Centered', 'sponsors-slideshow') ?></option>
					<option value="alignright"><?php _e('Floating Right', 'sponsors-slideshow') ?></option>
				</select>
			</td>
		</tr>
		<tr>
			<td><label for="post_excerpt_length"><?php _e('Post Excerpt', 'sponsors-slideshow') ?></label></td>
			<td><input type="text" name="post_excerpt_length" id="post_excerpt_length" size="4" /> <?php _e('words','sponsors-slideshow') ?></td>
		</tr>
		</table>
		</div>
			
	</div>
	
	<br style="clear: both;" />
	<div class="mceActionPanel" style="margin-top: 0.5em;">
		<div style="float: left">
			<input type="button" id="cancel" name="cancel" value="<?php _e("Cancel", 'sponsors-slideshow'); ?>" onclick="tinyMCEPopup.close();" />
		</div>

		<div style="float: right">
			<input type="submit" id="insert" name="insert" value="<?php _e("Insert", 'sponsors-slideshow'); ?>" onclick="SponsorsSlideshowInsertLink();" />
		</div>
	</div>
</form>
</body>
</html>
