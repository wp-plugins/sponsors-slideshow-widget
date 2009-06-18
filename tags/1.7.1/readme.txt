=== Sponsors Slideshow Widget ===
Contributors: Kolja Schleich
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=2329191
Tags: plugin, sidebar, widget, sponsor links, slideshow
Requires at least: 2.8
Tested up to: 2.8
Stable tag: 1.7.1

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

V1.7.1 - June 18, 2009
- CHANGED: insert <br style="clear: both;"> if title is "N/A"

V1.7 - June 18, 2009
- New WP 2.8 Widgets API

V1.6.1 - June 9, 2009
- BUGFIX: <br/> with clear both if no title present (IE fix)

V1.6 - May 26, 2009

* BUGFIX: exclusion of link categories from link widget
* BUGFIX: deletion of options if widget is deleted

V1.5 - May 12, 2009

* CHANGED: switched to jQuery Cycle Plugin. Hopefully fixes IE bug
* CHANGED: input title manually so no title is possible
* BUGFIX: display function to enable static display

V.1.4 - April 8, 2009

* NEW: multiple widget support
* BUGFIX: centering of slideshow
