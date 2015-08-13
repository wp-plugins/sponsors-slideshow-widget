=== Sponsors Slideshow Widget ===
Contributors: Kolja Schleich
Tags: plugin, sidebar, widget, sponsor links, slideshow
Requires at least: 2.8
Tested up to: 4.2.4
Stable tag: 2.1.2

Widget to display a certain link category with images as slide show.

== Description ==

Display a slide show of certain link categories in widgets. 
Since Wordpress 3.5 the standard Link Manager has been deactivated. The official plugin [Link Manager](https://wordpress.org/plugins/link-manager/) is required for this plugin to work.

It can display a certain link category as slide show in the sidebar, using the [jQuery Cycle Plugin](http://malsup.com/jquery/cycle). It automatically excludes the chosen slideshow category from the Wordpress internal links widget. Below is a list of options.

* set width an height of the slide show
* specify time out between transitions and time of transition
* choose from several different fade effects
* random or ordered slide show
* multiple widget support

== Installation ==

To install the plugin to the following steps

1. Install and activiate the plugin via the admin plugin page.
2. Install the plugin [Link Manager](https://wordpress.org/plugins/link-manager/) to bring back the pre-WP 3.5 Link System
3. Go to the widget page and add it to your sidebar.
4. You can choose as source "Links" or "Posts" and choose a certain category
5. If Source is "Posts" you need to setup custom fields in the post page and insert respective names holding image url target page url (see Screenshots)

== Screenshots ==
1. Slideshow widget settings. The URL and Image Fields (below category dropdown) are only required if Source is Posts
2. Custom fields for posts containing image url and target page url
3. Link Management

== ChangeLog ==

= 2.1.2 = 
* BUGFIX: fixed stripslashes in widget title
* BUGFIX: fixed link category exclusion from link list

= 2.1.1 =
* BUGFIX: small notice in widget control panel

= 2.1 =
* NEW: Widget Control panel without Javascript

= 2.0 =
* Compatible with Wordpress 4.0.1
* Since Wordpress 3.5 the Link Manager is deactivated. The official plugin [Link Manager](https://wordpress.org/plugins/link-manager/) is required for the plugin to work

= 1.9.3 =
* BUGFIX: css links with underline

= 1.9.2 =
* BUGFIX: misspellings in translation

= 1.9.1 =
* BUGFIX: link category not displayed in widget control

= 1.9 =
* NEW: option to set time of transition (Speed) besides timout between each transition (Timeout)

= 1.8 =
* NEW: links or posts as source for slideshow. Posts requires post meta fields for image and url

= 1.7.6 =
* CHANGED: show plugin version in style.css load instead of WP Version

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
