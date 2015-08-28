=== Sponsors Slideshow Widget ===
Contributors: Kolja Schleich
Tags: plugin, sidebar, widget, sponsor links, slideshow
Requires at least: 2.8
Tested up to: 4.3
Stable tag: 2.1.6

Widget to display a certain link category with images as slide show.

== Description ==

Include [jQuery](http://malsup.com/jquery/cycle) slideshows on your website in an instance

* multiple sources for slideshows including links, images or posts
* enable featured images for posts (post-thumbnail)
* enable categories for images
* set width an height of the slideshow
* specify time out between transitions and time of transition
* choose from several different fade effects
* random or ordered slide show
* include manual navigation
* multiple widget support
* exclude the chosen slideshow links category from the Wordpress internal links widget

Since Wordpress 3.5 the standard Link Manager has been deactivated. The official plugin [Link Manager](https://wordpress.org/plugins/link-manager/) is required for this plugin to work.

== Installation ==

To install the plugin to the following steps

1. Install and activiate the plugin via the admin plugin page.
2. Install the plugin [Link Manager](https://wordpress.org/plugins/link-manager/) to bring back the pre-WP 3.5 Link System
3. Go to the widget page and add it to your sidebar.
4. You can choose as source "Links" or "Posts" and choose a certain category
5. If Source is "Posts" you need to setup custom fields in the post page and insert respective names holding image url target page url (see Screenshots)

== Screenshots ==
1. Slideshow widget settings
2. Set featured images in posts
3. Link Management

== HowTo ==
Include jQuery slideshows on your website in an instance. The plugin offers different sources for generating slideshows including links, images or posts

=== Links ===
1. First install the plugin [Link Manager](https://wordpress.org/plugins/link-manager/) to re-enable the WordPress link system
2. Create at least one category and add links including Image Address
3. Choose Links as source and corresponding links category in the widget settings page

=== Images ===
1. The plugin enables categorization of media
2. Upload images and add the desired images to a category
3. Choose Images as source and corresponding images/posts category in the widget settings page

=== Posts ===
1. Add featured images to posts
2. Choose Posts as source and corresponding images/posts category in the widget settings page
3. In addition to the featured image, post title and an excerpt are displayed above linking to the post


== ChangeLog ==

= 2.1.6 =
* NEW: enable featured image in posts
* NEW: show featured post slideshow including featured image (post-thumbnail), post title and adjustable post excerpt 
* NEW: enable categories in attachments and include attachments (images) as source
* NEW: include previous/next navigation in slideshow, which can be shown or hidden through widget control panel
* BUGFIX: fixed function to manually display widget
* BUGFIX: some styling fixes

= 2.1.5 =
* BUGFIX: fixed not loaded CSS stylesheet in admin panel
* BUGFIX: fixed undefined index errors in widget control panel

= 2.1.4 =
* BUGFIX: corrected jquery slideshow handle to load script

= 2.1.3 =
* BUGFIX: correctly load stylesheet and scripts

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
