=== Sponsors Slideshow Widget ===
Contributors: Kolja Schleich
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=2329191
Tags: plugin, sidebar, widget, sponsor links, slideshow
Requires at least: 2.8
Tested up to: 2.8.4
Stable tag: 1.7.5

Widget to display a certain link category with images as slide show.

== Description ==

This plugin is designed to be used as sponsors slide show widget. It can display a certain link category as slide show in the sidebar, using the [jQuery Cycle Plugin](http://malsup.com/jquery/cycle). It automatically excludes the chosen slideshow category from the Wordpress internal links widget. Below is a list of options.

* set width an height of the slideshow
* specify time of each image on display
* choose from several different fade effects
* random or ordered slideshow
* multiple widget support

== Installation ==

To install the plugin to the following steps

1. Unzip the zip-file and upload the content to your Wordpress Plugin directory.
2. Activiate the plugin via the admin plugin page.
3. Go to the widget page and add it to your sidebar.

== ChangeLog ==

= 1.7.5 =
* CHANGED: renamed classes to avoid Ad Blocker issues

= 1.7.4 =
* BUGFIX: removed document.ready part in slideshow function call

= 1.7.3 =
* NEW: use link target from link settings

= 1.7.2 =
* BUGFIX: static function to display widget

= 1.7.1 =
* CHANGED: insert <br style="clear: both;"> if title is "N/A"

= 1.7 =
* New WP 2.8 Widgets API

= 1.6.1 =
* BUGFIX: <br/> with clear both if no title present (IE fix)

= 1.6 =
* BUGFIX: exclusion of link categories from link widget
* BUGFIX: deletion of options if widget is deleted

= 1.5 =
* CHANGED: switched to jQuery Cycle Plugin. Hopefully fixes IE bug
* CHANGED: input title manually so no title is possible
* BUGFIX: display function to enable static display

= 1.4 =
* NEW: multiple widget support
* BUGFIX: centering of slideshow

= 1.0 =
* Initial release
