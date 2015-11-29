<?php
// check for rights
if(!current_user_can('edit_posts')) die;

$sponsors_slideshow_widget = new SponsorsSlideshowWidget();

global $wpdb;
?>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title><?php _e('Slideshow', 'sponsors-slideshow') ?></title>
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
	<script language="javascript" type="text/javascript" src="<?php echo includes_url(); ?>js/tinymce/tiny_mce_popup.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo includes_url(); ?>js/tinymce/utils/mctabs.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo includes_url(); ?>js/tinymce/utils/form_utils.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo SPONSORS_SLIDESHOW_URL; ?>tinymce/tinymce.js"></script>
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
	<div class="panel_wrapper" style="height: 330px;">
		
		<!-- slideshow panel -->
		<div id="slideshow_panel" class="panel current">
		<table style="border: 0;">
		<tr>
			<td><label for="source"><?php _e("Source", 'sponsors-slideshow'); ?></label></td>
			<td>
				<?php echo $sponsors_slideshow_widget->sources("", "category", "category") ?>
				<?php echo $sponsors_slideshow_widget->order("", "order", "order") ?>
			</td>
		</tr>
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
				<?php echo $sponsors_slideshow_widget->fadeEffects("", "fade", "fade"); ?>
				<?php echo $sponsors_slideshow_widget->easingEffects("", "easing", "easing"); ?>
			</td>
		</tr>
		<tr>
			<td><label for="speed"><?php _e('Show', 'sponsors-slideshow') ?></label></td>
			<td><input type="text" name="carousel_num_slides" id="carousel_num_slides" size="2" /> <?php _e('slides in Carousel', 'sponsors-slideshow') ?></td>
		</tr>
		<tr>
			<td><label for="navigation_arrows"><?php _e('Navigation Arrows', 'sponsors-slideshow') ?></label></td>
			<td>
				<input type="checkbox" checked="checked" value="1" name="navigation_arrows" id="navigation_arrows" />
			</td>
		</tr>
		<tr>
			<td><label for="navigation_pager_none"><?php _e('Pager', 'sponsors-slideshow') ?></label></td>
			<td>
				<input type="radio" value="none" name="navigation_pager" id="navigation_pager_none" />
				<label for ="navigation_pager_none"><?php _e('Hide', 'sponsors-slideshow') ?></label>
				<input type="radio" checked="checked" value="buttons" name="navigation_pager" id="navigation_pager_buttons" />
				<label for ="navigation_pager_buttons"><?php _e('Buttons', 'sponsors-slideshow') ?></label>
				<input type="radio" value="thumbs" name="navigation_pager" id="navigation_pager_tumbs" />
				<label for ="navigation_pager_tumbs"><?php _e('Thumbnails', 'sponsors-slideshow') ?></label>
			</td>
		</tr>
		<tr>
			<td><label for="bounding_box"><?php _e('Bounding Box', 'sponsors-slideshow') ?></label></td>
			<td><input type="checkbox" checked="checked" value="1" name="bounding_box" id="bounding_box" /></td>
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
		<tr>
			<td><label for="post_excerpt_length"><?php _e('Slide Overlay', 'sponsors-slideshow') ?></label></td>
			<td>
				<?php echo $sponsors_slideshow_widget->overlayDisplay("", "overlay_display", "overlay_display") ?>
				<?php echo $sponsors_slideshow_widget->overlayEffects("", "overlay_effects", "overlay_effects") ?>
				<?php echo $sponsors_slideshow_widget->overlayAnimate("", "overlay_animate", "overlay_animate") ?>
				<?php echo $sponsors_slideshow_widget->overlayStyles("", "overlay_style", "overlay_style") ?>
			</td>
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
